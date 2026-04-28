require('dotenv').config();
const express = require('express');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT;

app.use(cors());
app.use(express.json());

app.get('/auth/status', (req, res) => {
    res.json({ message: 'Auth Service is running smoothly!' });
});

// Authentication Endpoints 
app.post('/auth/login', (req, res) => {
    res.status(501).json({ message: '???' });
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