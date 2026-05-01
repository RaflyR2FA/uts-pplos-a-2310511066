const mysql = require('mysql2/promise');
require('dotenv').config();

const pool = mysql.createPool({
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: process.env.DB_NAME,
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

const initDB = async () => {
    try {
        const connection = await pool.getConnection();
        await connection.query(`
            CREATE TABLE IF NOT EXISTS monthly_reports (
                id INT AUTO_INCREMENT PRIMARY KEY,
                report_month INT NOT NULL,
                report_year INT NOT NULL,
                summary_data JSON NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY unique_month_year (report_month, report_year)
            )
        `);
        console.log('Report database schema initialized successfully.');
        connection.release();
    } catch (err) {
        console.error('Failed to initialize Report DB:', err);
    }
};

initDB();

module.exports = pool;