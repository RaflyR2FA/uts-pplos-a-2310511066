require('dotenv').config();
const express = require('express');
const { createProxyMiddleware } = require('http-proxy-middleware');
const rateLimit = require('express-rate-limit');
const jwt = require('jsonwebtoken');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT;

app.use(cors());

const limiter = rateLimit({
    windowMs: 1 * 60 * 1000,
    max: 60,
    message: { error: 'Too many requests. Please try again later.' }
});
app.use(limiter);

const verifyJWT = (req, res, next) => {
    if (req.path.startsWith('/auth')) return next();
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1];
    if (!token) return res.status(401).json({ error: 'Access denied. Token not found.' });
    jwt.verify(token, process.env.JWT_SECRET || 'secret-key-sementara', (err, decoded) => {
        if (err) return res.status(401).json({ error: 'Invalid token or expired.' });
        req.user = decoded;
        next();
    });
};

app.use(verifyJWT);

app.use('/auth', createProxyMiddleware({ 
    target: 'http://localhost:3001', 
    changeOrigin: true 
}));

app.use('/api', createProxyMiddleware({ 
    target: 'http://localhost:8000', 
    changeOrigin: true 
}));

app.use('/reports', createProxyMiddleware({ 
    target: 'http://localhost:3002', 
    changeOrigin: true 
}));

app.listen(PORT, () => {
    console.log(`API Gateway berjalan di http://localhost:${PORT}`);
});