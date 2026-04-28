require('dotenv').config();
const express = require('express');
const cors = require('cors');
const jwt = require('jsonwebtoken');
const db = require('./db');

const app = express();
const PORT = process.env.PORT;

app.use(cors());
app.use(express.json());

app.get('/auth/status', (req, res) => {
    res.json({ message: 'Auth Service is running smoothly!' });
});

// Authentication Endpoints 
app.post('/auth/login', (req, res) => {
    const { email, password } = req.body;
    try {
        const [rows] = await db.query('SELECT * FROM users WHERE email = ? AND password = ?', [email, password]);
        const user = rows[0];
        if (!user) {
            return res.status(401).json({ error: 'Email or password is incorrect.' });
        }
        const accessToken = jwt.sign(
            { id: user.id, email: user.email, name: user.name },
            process.env.JWT_SECRET,
            { expiresIn: '15m' }
        );
        const refreshToken = jwt.sign(
            { id: user.id },
            process.env.JWT_REFRESH_SECRET,
            { expiresIn: '7d' }
        );
        res.json({
            message: 'Login successful',
            access_token: accessToken,
            refresh_token: refreshToken
        });
    } catch (error) {
        res.status(500).json({ error: 'An error occurred on the server.' });
    }
});

app.post('/auth/refresh', (req, res) => {
    res.status(501).json({ message: '???' });
});

app.post('/auth/google', (req, res) => {
    res.status(501).json({ message: '???' });
});

app.post('/auth/logout', (req, res) => {
    res.status(501).json({ message: '???' });
});

app.listen(PORT, () => {
    console.log(`Auth Service berjalan di http://localhost:${PORT}`);
});