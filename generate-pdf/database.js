const mysql = require('mysql2');
require('dotenv').config();

// Create a connection pool (you can change to `createConnection` for a single connection)
const pool = mysql.createPool({
    host: process.env.DB_HOST,     // Replace with your MySQL host
    user: process.env.DB_USER,          // Replace with your MySQL username
    password: process.env.DB_PASSWORD,  // Replace with your MySQL password
    database: process.env.DB_NAME,   // Replace with your database name
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

module.exports = pool.promise();  // Use the promise-based API for async queries
