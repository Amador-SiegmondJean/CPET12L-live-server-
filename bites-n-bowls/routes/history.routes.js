// ============================================
// routes/history.routes.js
// ============================================
const express3 = require('express');
const router3 = express3.Router();
const { asyncHandler: asyncHandler3 } = require('../middleware/errorHandler');
const { authenticate: authenticate3 } = require('../middleware/auth');
const { validateQuery } = require('../middleware/validateRequest');
const db3 = require('../config/database');

// GET /api/history - Get feeding history
router3.get('/', authenticate3, asyncHandler3(async (req, res) => {
    const search = req.query.search || '';

    let sql = `SELECT * FROM history WHERE 
               feed_date LIKE ? OR 
               feed_time LIKE ? OR 
               type LIKE ? OR 
               status LIKE ? 
               ORDER BY created_at DESC`;
    
    const searchParam = `%${search}%`;
    const history = await db3.query(sql, [searchParam, searchParam, searchParam, searchParam]);

    res.json({
        success: true,
        history: history.map(h => ({
            id: h.id,
            date: h.feed_date,
            time: h.feed_time,
            rounds: h.rounds,
            type: h.type,
            status: h.status
        }))
    });
}));

module.exports = router3;
