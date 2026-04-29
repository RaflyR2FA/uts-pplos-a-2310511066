require('dotenv').config();
const express = require('express');
const cors = require('cors');
const jwt = require('jsonwebtoken');
const { OAuth2Client } = require('google-auth-library');
const db = require('./db');

const app = express();
const PORT = process.env.PORT;

app.use(cors());
app.use(express.json());

const googleClient = new OAuth2Client(
    process.env.GOOGLE_CLIENT_ID,
    process.env.GOOGLE_CLIENT_SECRET,
    process.env.GOOGLE_REDIRECT_URI
);

app.get('/auth/status', (req, res) => {
    res.json({ message: 'Auth Service is running smoothly!' });
});

app.post('/auth/login', async (req, res) => {
    const { email, password } = req.body;
    try {
        const [rows] = await db.query('SELECT * FROM users WHERE email = ? AND password = ?', [email, password]);
        const user = rows[0];
        if (!user) {
            return res.status(401).json({ error: 'Invalid email or password.' });
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
        console.error(error);
        res.status(500).json({ error: 'Internal server error.' });
    }
});

app.post('/auth/refresh', (req, res) => {
    res.status(501).json({ message: '???' });
});

app.get('/auth/google/url', (req, res) => {
    const url = googleClient.generateAuthUrl({
        access_type: 'offline',
        prompt: 'consent',
        scope: [
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/userinfo.email'
        ]
    });
    res.json({ url });
});

app.get('/auth/google/callback', async (req, res) => {
    const code = req.query.code;
    if (!code) {
        return res.status(400).json({ error: 'Authorization code is missing.' });
    }
    try {
        const { tokens } = await googleClient.getToken(code);
        const ticket = await googleClient.verifyIdToken({
            idToken: tokens.id_token,
            audience: process.env.GOOGLE_CLIENT_ID,
        });
        const payload = ticket.getPayload();
        const { email, name, picture: photo } = payload;
        const [rows] = await db.query('SELECT * FROM users WHERE email = ?', [email]);
        let user = rows[0];
        if (!user) {
            const [result] = await db.query(
                'INSERT INTO users (name, email, photo, oauth_provider) VALUES (?, ?, ?, ?)',
                [name, email, photo, 'google']
            );
            user = { id: result.insertId, name, email, photo, oauth_provider: 'google' };
        } else if (user.oauth_provider !== 'google') {
             await db.query('UPDATE users SET oauth_provider = ?, photo = ? WHERE email = ?', ['google', photo, email]);
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
            message: 'Google OAuth login successful.',
            user: {
                name: user.name,
                email: user.email,
                photo: user.photo
            },
            access_token: accessToken,
            refresh_token: refreshToken
        });
    } catch (error) {
        console.error('Google OAuth Error:', error);
        res.status(500).json({ error: 'Failed to authenticate with Google.' });
    }
});

app.post('/auth/logout', (req, res) => {
    res.status(501).json({ message: '???' });
});

app.listen(PORT, () => {
    console.log(`Auth Service berjalan di http://localhost:${PORT}`);
});