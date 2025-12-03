// ============================================
// routes/schedule.routes.js
// ============================================
const express = require('express');
const router = express.Router();
const { asyncHandler } = require('../middleware/errorHandler');
const { authenticate } = require('../middleware/auth');
const { authenticateHardware } = require('../middleware/auth');
const { validateBody, validateParams, schemas } = require('../middleware/validateRequest');
const db = require('../config/database');

// GET /api/schedules - Get all active schedules
router.get('/', authenticate, asyncHandler(async (req, res) => {
    const sql = 'SELECT * FROM schedules WHERE is_active = 1 ORDER BY created_at DESC';
    const schedules = await db.query(sql);

    res.json({
        success: true,
        schedules: schedules.map(s => ({
            id: s.id,
            interval: s.interval_type,
            time: s.start_time,
            rounds: s.rounds,
            frequency: s.frequency,
            customDays: s.custom_days
        }))
    });
}));

// GET /api/schedules/active - Get active schedules for hardware
router.get('/active', authenticateHardware, asyncHandler(async (req, res) => {
    const currentDay = new Date().toLocaleDateString('en-US', { weekday: 'short' });
    const currentTime = new Date().toTimeString().split(' ')[0];

    const sql = 'SELECT * FROM schedules WHERE is_active = 1';
    const schedules = await db.query(sql);

    const activeSchedules = schedules.filter(row => {
        let shouldDispense = false;

        switch(row.frequency) {
            case 'daily':
                shouldDispense = true;
                break;
            case 'weekdays':
                shouldDispense = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'].includes(currentDay);
                break;
            case 'weekends':
                shouldDispense = ['Sat', 'Sun'].includes(currentDay);
                break;
            case 'custom':
                const customDays = row.custom_days.split(',').map(d => d.trim());
                shouldDispense = customDays.includes(currentDay);
                break;
        }

        return shouldDispense;
    }).map(row => ({
        id: row.id,
        interval: row.interval_type,
        start_time: row.start_time,
        rounds: row.rounds
    }));

    res.json({
        success: true,
        schedules: activeSchedules,
        current_time: currentTime
    });
}));

// POST /api/schedules - Create new schedule
router.post('/', authenticate, validateBody(schemas.schedule), asyncHandler(async (req, res) => {
    const { interval, time, rounds, frequency, customDays } = req.body;

    const sql = `INSERT INTO schedules (interval_type, start_time, rounds, frequency, custom_days, is_active) 
                 VALUES (?, ?, ?, ?, ?, 1)`;
    
    const result = await db.query(sql, [interval, time, rounds, frequency, customDays || '']);

    res.status(201).json({
        success: true,
        message: 'Schedule created successfully',
        id: result.insertId
    });
}));

// PUT /api/schedules/:id - Update schedule
router.put('/:id', authenticate, validateParams(schemas.idParam), validateBody(schemas.schedule), asyncHandler(async (req, res) => {
    const { id } = req.params;
    const { interval, time, rounds, frequency, customDays } = req.body;

    const sql = `UPDATE schedules SET 
                 interval_type = ?, start_time = ?, rounds = ?, 
                 frequency = ?, custom_days = ? 
                 WHERE id = ?`;
    
    await db.query(sql, [interval, time, rounds, frequency, customDays || '', id]);

    res.json({
        success: true,
        message: 'Schedule updated successfully'
    });
}));

// DELETE /api/schedules/:id - Delete schedule
router.delete('/:id', authenticate, validateParams(schemas.idParam), asyncHandler(async (req, res) => {
    const { id } = req.params;

    const sql = 'DELETE FROM schedules WHERE id = ?';
    await db.query(sql, [id]);

    res.json({
        success: true,
        message: 'Schedule deleted successfully'
    });
}));

module.exports = router;