require('dotenv').config();
const express = require('express');
const axios = require('axios');
const cors = require('cors');
const db = require('./db');

const app = express();
const PORT = process.env.PORT; 
const employeeService = process.env.EMPLOYEE_SERVICE_URL;

app.use(cors());
app.use(express.json());

app.get('/reports/status', (req, res) => {
    res.json({ message: 'Report Service is running smoothly!' });
});

app.post('/reports/generate', async (req, res) => {
    const month = req.body.month || new Date().getMonth() + 1;
    const year = req.body.year || new Date().getFullYear();
    try {
        const empRes = await axios.get(`${employeeService}/employees`);
        const employees = empRes.data.data.data;
        const attRes = await axios.get(`${employeeService}/attendances?month=${month}&year=${year}`);
        const attendances = attRes.data.data;
        const summary = employees.map(emp => {
            const empAttendances = attendances.filter(a => a.employee_id === emp.id);
            return {
                employee_id: emp.id,
                name: emp.full_name,
                total_present: empAttendances.filter(a => a.status === 'present').length,
                total_late: empAttendances.filter(a => a.status === 'late').length,
                total_absent: empAttendances.filter(a => a.status === 'absent').length,
                total_leave: empAttendances.filter(a => a.status === 'leave').length
            };
        });
        const summaryJson = JSON.stringify(summary);
        await db.query(
            `INSERT INTO monthly_reports (report_month, report_year, summary_data) 
             VALUES (?, ?, ?) 
             ON DUPLICATE KEY UPDATE summary_data = ?`,
            [month, year, summaryJson, summaryJson]
        );
        res.status(201).json({
            message: 'Report generated and saved successfully.',
            period: `${month}-${year}`,
            data: summary
        });
    } catch (error) {
        console.error('Error generating report:', error.message);
        res.status(500).json({ error: 'Failed to generate report. Ensure Employee Service is running.' });
    }
});

app.get('/reports/attendance-summary', async (req, res) => {
    const month = req.query.month || new Date().getMonth() + 1;
    const year = req.query.year || new Date().getFullYear();
    try {
        const [rows] = await db.query(
            'SELECT * FROM monthly_reports WHERE report_month = ? AND report_year = ?',
            [month, year]
        );
        if (rows.length === 0) {
            return res.status(404).json({ error: 'Report not found for this period. Please generate it first.' });
        }
        res.json({
            message: 'Report retrieved successfully.',
            period: `${month}-${year}`,
            data: rows[0].summary_data
        });
    } catch (error) {
        res.status(500).json({ error: 'Database error.' });
    }
});

app.listen(PORT, () => {
    console.log(`Report Service is running on http://localhost:${PORT}`);
});