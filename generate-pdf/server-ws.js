const express = require("express");
const axios = require("axios");
const mysql = require("mysql2/promise");
require('dotenv').config();

const app = express();
const port = 3334;

// Database connection pool
const pool = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
});

// Function to fetch device status
async function fetchDeviceStatus(url) {
    try {
        const response = await axios.get(url, { timeout: 5000 });
        return response.status === 200 ? "Connected" : "Disconnected";
    } catch {
        return "Disconnected";
    }
}

// SSE endpoint
app.get("/status_device", async (req, res) => {
    // Set headers for SSE
    res.setHeader("Content-Type", "text/event-stream");
    res.setHeader("Cache-Control", "no-cache");
    res.setHeader("Connection", "keep-alive");

    // Keep the connection alive
    res.flushHeaders();

    // Set an interval to send device statuses every 3 seconds
    const interval = setInterval(async () => {
        try {
            // Query the database for device information
            const [rows] = await pool.query(`
                SELECT id, name, url, 
                DATE_FORMAT(created_at, '%d-%m-%Y %H:%i:%s') AS created_at 
                FROM remotes
                WHERE deleted_at IS NULL
            `);

            // Process each row and fetch status
            const updates = [];
            for (const row of rows) {
                const status = await fetchDeviceStatus(row.url);

                // Update device status in the database
                await pool.query(
                    `UPDATE remotes SET status = ? WHERE id = ?`,
                    [status, row.id]
                );

                // Prepare update for SSE client
                updates.push({
                    id: row.id,
                    name: row.name,
                    url: row.url,
                    created_at: row.created_at,
                    status,
                });
            }

            // Send the updates to the client via SSE
            if (updates.length > 0) {
                console.log(updates);

                res.write(`data: ${JSON.stringify(updates)}\n\n`);
            }
        } catch (error) {
            console.error("Error in status_device:", error.message);
            res.write(`data: {"error": "${error.message}"}\n\n`);
        }
    }, 3000);

    // Handle client disconnection
    req.on("close", () => {
        console.log("Client disconnected");
        clearInterval(interval);
    });
});

app.listen(port, () => {
    console.log(`Server running on http://127.0.0.1:${port}`);
});
