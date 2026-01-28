from fastapi import FastAPI, HTTPException, status
from fastapi.responses import JSONResponse, StreamingResponse
from fastapi.middleware.cors import CORSMiddleware
import logging
import os
from concurrent.futures import ThreadPoolExecutor, as_completed
import pymysql.cursors
import requests
import time
import threading
import asyncio

# Database connection settings
DB_HOST = os.getenv("DB_HOST", "127.0.0.1")
DB_PORT = int(os.getenv("DB_PORT", "3306"))
DB_USER = os.getenv("DB_USER", "platform_user")
DB_PASSWORD = os.getenv("DB_PASSWORD", "SorryThisIsSuperSecret!!!_DPR")
DB_NAME = os.getenv("DB_NAME", "platform")
STATUS_INTERVAL = int(os.getenv("STATUS_INTERVAL_SECONDS", "3"))
MAX_WORKERS = int(os.getenv("STATUS_WORKERS", "20"))
UVICORN_PORT = int(os.getenv("PORT", "3001"))
UVICORN_HOST = os.getenv("HOST", "0.0.0.0")

# FastAPI app initialization
app = FastAPI()

# Allow all origins, methods, and headers
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# Set up logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# ThreadPoolExecutor for parallel processing
executor = ThreadPoolExecutor(max_workers=MAX_WORKERS)

# Flag to control background task
background_task_running = False


def update_device_statuses_background():
    """Background task to continuously update device statuses."""
    global background_task_running
    logger.info("Background device status updater started")
    
    while background_task_running:
        connection = None
        try:
            connection = pymysql.connect(
                host=DB_HOST,
                port=DB_PORT,
                user=DB_USER,
                password=DB_PASSWORD,
                database=DB_NAME,
                cursorclass=pymysql.cursors.DictCursor,
            )
            
            with connection.cursor() as cursor:
                cursor.execute(
                    """
                    SELECT id, name, url
                    FROM remotes
                    WHERE deleted_at IS NULL
                    """
                )
                fetch_data = cursor.fetchall()

            # Fetch statuses in parallel
            futures = {
                executor.submit(fetch_device_status, data["url"]): data
                for data in fetch_data
            }

            for future in as_completed(futures):
                data = futures[future]
                try:
                    device_status = future.result()
                    logger.info(f"Device ID: {data['id']} Name: {data['name']} Status: {device_status}")

                    with connection.cursor() as cursor:
                        # Update status, last_checked_at always, last_seen only if Connected
                        if device_status == "Connected":
                            cursor.execute(
                                "UPDATE remotes SET status = %s, last_seen = NOW(), last_checked_at = NOW() WHERE id = %s",
                                (device_status, data["id"]),
                            )
                        else:
                            cursor.execute(
                                "UPDATE remotes SET status = %s, last_checked_at = NOW() WHERE id = %s",
                                (device_status, data["id"]),
                            )
                        connection.commit()

                except Exception as e:
                    logger.error(f"Error processing device ID {data['id']}: {str(e)}")

            time.sleep(STATUS_INTERVAL)

        except Exception as e:
            logger.error(f"Error in background updater: {str(e)}")
            time.sleep(STATUS_INTERVAL)
        finally:
            if connection:
                connection.close()


@app.on_event("startup")
async def startup_event():
    """Start background task when app starts."""
    global background_task_running
    background_task_running = True
    thread = threading.Thread(target=update_device_statuses_background, daemon=True)
    thread.start()
    logger.info("Application startup complete - background device monitor running")


@app.on_event("shutdown")
async def shutdown_event():
    """Stop background task when app shuts down."""
    global background_task_running
    background_task_running = False
    logger.info("Application shutdown - stopping background device monitor")


@app.get("/health")
async def health_check():
    """Health check endpoint for monitoring."""
    return JSONResponse(
        status_code=200,
        content={
            "status": "healthy",
            "service": "remote-android",
            "timestamp": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime())
        }
    )


def get_db_connection():
    """Create a new database connection for each request."""
    try:
        connection = pymysql.connect(
            host=DB_HOST,
            port=DB_PORT,
            user=DB_USER,
            password=DB_PASSWORD,
            database=DB_NAME,
            cursorclass=pymysql.cursors.DictCursor,
        )
        return connection
    except pymysql.MySQLError as e:
        logger.error(f"Database connection error: {str(e)}")
        raise HTTPException(status_code=500, detail="Database connection failed")


@app.get("/graph_playlist")
async def graph_playlist():
    """Fetch playlist data and generate graph metrics."""
    connection = get_db_connection()
    try:
        with connection.cursor() as cursor:
            # Query to fetch all playlists
            cursor.execute("""
                SELECT 
                    d.name, 
                    p.id AS playlist_id, 
                    COUNT(pl.id) AS total
                FROM 
                    displays d
                INNER JOIN 
                    schedules s ON s.id = d.schedule_id
                INNER JOIN 
                    schedule_playlists sp ON sp.schedule_id = s.id
                INNER JOIN 
                    playlists p ON p.id = sp.playlist_id
                INNER JOIN 
                    playlist_layouts pl ON pl.playlist_id = p.id
                WHERE d.deleted_at IS NULL 
                GROUP BY 
                    d.name, p.id;
                """)
            fetch_data = cursor.fetchall()

        result_name = []
        result_total = []

        for data in fetch_data:
            result_name.append(data["name"])
            result_total.append(data["total"])

        return JSONResponse(
            {
                "message": "Graph Playlist",
                "status": status.HTTP_200_OK,
                "name": result_name,
                "total": result_total,
            },
            status_code=status.HTTP_200_OK,
        )
    except Exception as e:
        logger.error(f"Error in /graph_playlist: {str(e)}")
        return JSONResponse(
            {"message": "Internal server error", "status": 500, "error": str(e)},
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
        )
    finally:
        connection.close()


@app.get("/status_service")
async def status_service():
    """Fetch the connection status of devices."""
    connection = get_db_connection()
    try:
        with connection.cursor() as cursor:
            # Query device statuses
            cursor.execute("SELECT status FROM remotes WHERE deleted_at IS NULL")
            fetch_data = cursor.fetchall()

        total_connect = sum(1 for data in fetch_data if data["status"] == "Connected")
        total_disconnect = sum(
            1 for data in fetch_data if data["status"] == "Disconnected"
        )

        return JSONResponse(
            {
                "message": "Status Device",
                "status": status.HTTP_200_OK,
                "connect": total_connect,
                "disconnect": total_disconnect,
            },
            status_code=status.HTTP_200_OK,
        )
    except Exception as e:
        logger.error(f"Error in /status_service: {str(e)}")
        return JSONResponse(
            {"message": "Internal server error", "status": 500, "error": str(e)},
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
        )
    finally:
        connection.close()


def fetch_device_status(url: str) -> str:
    """Fetch the status of a device by its URL."""
    try:
        response = requests.get(url, timeout=5)
        return "Connected" if response.status_code == 200 else "Disconnected"
    except requests.RequestException:
        return "Disconnected"


def status_stream():
    """Generate a real-time status stream for devices."""
    while True:
        connection = None
        try:
            connection = get_db_connection()
            with connection.cursor() as cursor:
                # Query all device data
                cursor.execute(
                    """
                    SELECT id, name, url, 
                    DATE_FORMAT(created_at, '%%d-%%m-%%Y %%H:%%i:%%s') AS created_at 
                    FROM remotes
                    WHERE deleted_at IS NULL
                    """
                )
                fetch_data = cursor.fetchall()

            # Fetch statuses in parallel
            futures = {
                executor.submit(fetch_device_status, data["url"]): data
                for data in fetch_data
            }

            for future in as_completed(futures):
                data = futures[future]
                try:
                    status = future.result()
                    logger.info(
                        f"Device ID: {data['id']} Name: {data['name']} Status: {status}"
                    )

                    with connection.cursor() as cursor:
                        # Update status, last_checked_at always, last_seen only if Connected
                        if status == "Connected":
                            cursor.execute(
                                "UPDATE remotes SET status = %s, last_seen = NOW(), last_checked_at = NOW() WHERE id = %s",
                                (status, data["id"]),
                            )
                        else:
                            cursor.execute(
                                "UPDATE remotes SET status = %s, last_checked_at = NOW() WHERE id = %s",
                                (status, data["id"]),
                            )
                        connection.commit()

                    yield f"data: {{\"id\": {data['id']}, \"name\": \"{data['name']}\", \"url\": \"{data['url']}\", \"created_at\": \"{data['created_at']}\", \"status\": \"{status}\"}}\n\n"
                except Exception as e:
                    logger.error(f"Error processing device ID {data['id']}: {str(e)}")
                    yield f'data: {{"id": {data["id"]}, "error": "{str(e)}"}}\n\n'

            time.sleep(STATUS_INTERVAL)

        except Exception as e:
            logger.error(f"Error in status_stream: {str(e)}")
            yield f'data: {{"error": "{str(e)}"}}\n\n'
        finally:
            if connection:
                connection.close()


@app.get("/status_device")
async def status_device():
    """Stream the real-time status of devices."""
    try:
        return StreamingResponse(status_stream(), media_type="text/event-stream")
    except Exception as e:
        logger.error(f"Error in /status_device: {str(e)}")
        raise HTTPException(status_code=500, detail="Internal server error")


if __name__ == "__main__":
    import uvicorn

    uvicorn.run("app:app", host=UVICORN_HOST, port=UVICORN_PORT, reload=True, workers=10)
