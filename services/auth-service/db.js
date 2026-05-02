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
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL UNIQUE,
                password VARCHAR(255),
                photo VARCHAR(255),
                oauth_provider ENUM('local', 'google') DEFAULT 'local',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
        await connection.query(`
            INSERT IGNORE INTO users 
            (name, email, password, oauth_provider)
            VALUES
            ('Employee One', 'employee1@example.com', 'password123', 'local'),
            ('Employee Two', 'employee2@example.com', 'password456', 'local'),
            ('Admin User', 'admin@example.com', 'admin123', 'local')
        `);
        console.log('User table and dummy data initialized successfully.');
        connection.release();
    } catch (err) {
        console.error('Error initializing user table:', err);
    }
};

initDB();

module.exports = pool;