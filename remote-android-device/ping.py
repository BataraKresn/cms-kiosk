from fastapi import FastAPI, HTTPException
from fastapi.responses import StreamingResponse
from concurrent.futures import ThreadPoolExecutor, as_completed
import pymysql
import requests
import time
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)

app = FastAPI()

# ThreadPoolExecutor for parallel HTTP requests
executor = ThreadPoolExecutor(max_workers=20)

# Helper function to get a database connection
def get_db_connection():
    try:
        return pymysql.connect(
            host="46.202.164.64",
            user="platform_user",
            password="SorryThisIsSuperSecret!!!_DPR",
            database="platform",
            cursorclass=pymysql.cursors.DictCursor,
        )
    except pymysql.MySQLError as e:
        logging.error(f"Error connecting to the database: {str(e)}")
        raise


# Helper function to fetch device status
def fetch_device_status(url: str) -> str:
    try:
        response = requests.get(url, timeout=5)
        if response.status_code == 200:
            return "Connected"
        return "Disconnected"
    except requests.RequestException:
        return "Disconnected"


# Generator for SSE responses
def status_stream():
    while True:
        connection = None
        try:
            # Establish database connection
            connection = get_db_connection()
            with connection.cursor() as cursor:
                query = """SELECT id, name, url, DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') as created_at FROM remotes"""
                cursor.execute(query)
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
                    logging.info(f"Device ID: {data['id']} Name: {data['name']} Status: {status}")

                    # Update status in database
                    with connection.cursor() as cursor:
                        update = "UPDATE `remotes` SET `status` = %s WHERE `id` = %s"
                        cursor.execute(update, (status, data["id"]))
                        connection.commit()

                    yield f"data: {{\"id\": {data['id']}, \"name\": \"{data['name']}\", \"url\": \"{data['url']}\", \"created_at\": \"{data['created_at']}\", \"status\": \"{status}\"}}\n\n"
                except Exception as e:
                    logging.error(f"Error processing device ID {data['id']}: {str(e)}")
                    yield f'data: {{"id": {data["id"]}, "error": "{str(e)}"}}\n\n'

            # Delay before the next status check
            time.sleep(3)

        except Exception as e:
            logging.error(f"Error in status stream: {str(e)}")
            yield f'data: {{"error": "{str(e)}"}}\n\n'

        finally:
            # Ensure cleanup of resources
            if connection:
                connection.close()


@app.get("/status_device")
async def status_device():
    try:
        return StreamingResponse(
            status_stream(),
            media_type="text/event-stream",
        )
    except pymysql.MySQLError as db_err:
        logging.error(f"Database error: {str(db_err)}")
        raise HTTPException(
            status_code=500,
            detail=f"Database error: {str(db_err)}",
        )
    except Exception as e:
        logging.error(f"Internal server error: {str(e)}")
        raise HTTPException(
            status_code=500,
            detail=f"Internal server error: {str(e)}",
        )


if __name__ == "__main__":
    import uvicorn

    uvicorn.run("ping:app", host="0.0.0.0", port=3001, reload=True, workers=10)
