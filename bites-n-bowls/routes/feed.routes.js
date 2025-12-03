// ============================================
// routes/feed.routes.js
// ============================================
const express2 = require('express');
const router2 = express2.Router();
const { asyncHandler: asyncHandler2 } = require('../middleware/errorHandler');
const { authenticate: authenticate2 } = require('../middleware/auth');
const { validateBody: validateBody2, schemas: schemas2 } = require('../middleware/validateRequest');
const db2 = require('../config/database');
const serverConfig = require('../config/server');

// POST /api/feed/dispense - Manual feed
router2.post('/dispense', authenticate2, validateBody2(schemas2.feed), asyncHandler2(async (req, res) => {
    const { rounds, type, weightDispensed } = req.body;

    // Get current weight
    const weightResult = await db2.query('SELECT setting_value FROM device_settings WHERE setting_key = ?', ['current_weight']);
    const currentWeight = parseInt(weightResult[0]?.setting_value || 0);

    // Check if enough feed
    if (currentWeight < weightDispensed) {
        const now = new Date();
        const date = now.toISOString().split('T')[0];
        const time = now.toTimeString().split(' ')[0];

        await db2.query(
            'INSERT INTO history (feed_date, feed_time, rounds, type, status) VALUES (?, ?, ?, ?, ?)',
            [date, time, rounds, type, 'Failed (Low Feed)']
        );

        await db2.query(
            'INSERT INTO alerts (alert_type, message, is_read) VALUES (?, ?, ?)',
            ['Error', `Dispense failed: Insufficient feed for ${rounds} rounds.`, 0]
        );

        return res.status(400).json({
            success: false,
            message: `Insufficient feed. Only ${currentWeight}g remaining.`,
            currentWeight
        });
    }

    // Update weight
    const newWeight = currentWeight - weightDispensed;
    await db2.query('UPDATE device_settings SET setting_value = ? WHERE setting_key = ?', [newWeight.toString(), 'current_weight']);

    // Add to history
    const now = new Date();
    const date = now.toISOString().split('T')[0];
    const time = now.toTimeString().split(' ')[0];

    await db2.query(
        'INSERT INTO history (feed_date, feed_time, rounds, type, status) VALUES (?, ?, ?, ?, ?)',
        [date, time, rounds, type, 'Success']
    );

    await db2.query(
        'INSERT INTO alerts (alert_type, message, is_read) VALUES (?, ?, ?)',
        ['Info', `Successfully dispensed ${rounds} rounds.`, 0]
    );

    // Low feed warning
    if (newWeight < serverConfig.lowFeedWarningThreshold) {
        await db2.query(
            'INSERT INTO alerts (alert_type, message, is_read) VALUES (?, ?, ?)',
            ['Warning', 'Feed supply critically low (<100g).', 0]
        );
    }

    res.json({
        success: true,
        message: `Successfully dispensed ${rounds} rounds (${weightDispensed}g).`,
        currentWeight: newWeight
    });
}));

// POST /api/feed/recalibrate - Recalibrate sensor
router2.post('/recalibrate', authenticate2, asyncHandler2(async (req, res) => {
    const now = new Date().toISOString().slice(0, 19).replace('T', ' ');

    await db2.query('UPDATE device_settings SET setting_value = ? WHERE setting_key = ?', ['1000', 'current_weight']);
    await db2.query('UPDATE device_settings SET setting_value = ?, updated_at = ? WHERE setting_key = ?', [now, now, 'last_calibration']);

    const date = new Date().toISOString().split('T')[0];
    const time = new Date().toTimeString().split(' ')[0];

    await db2.query(
        'INSERT INTO history (feed_date, feed_time, rounds, type, status) VALUES (?, ?, ?, ?, ?)',
        [date, time, 0, 'Recalibrate', 'Success']
    );

    await db2.query(
        'INSERT INTO alerts (alert_type, message, is_read) VALUES (?, ?, ?)',
        ['Info', 'Sensor recalibrated. Weight reset to 1000g.', 0]
    );

    res.json({
        success: true,
        currentWeight: 1000
    });
}));

module.exports = router2;