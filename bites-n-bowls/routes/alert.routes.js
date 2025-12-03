// ============================================
// routes/alert.routes.js
// ============================================
const express4 = require('express');
const router4 = express4.Router();
const { asyncHandler: asyncHandler4 } = require('../middleware/errorHandler');
const { authenticate: authenticate4 } = require('../middleware/auth');
const db4 = require('../config/database');

// GET /api/alerts - Get all alerts
router4.get('/', authenticate4, asyncHandler4(async (req, res) => {
    const sql = 'SELECT * FROM alerts ORDER BY created_at DESC LIMIT 50';
    const alerts = await db4.query(sql);

    res.json({
        success: true,
        alerts: alerts.map(a => ({
            id: a.id,
            type: a.alert_type,
            message: a.message,
            timestamp: a.created_at
        }))
    });
}));

module.exports = router4;