// ============================================
// routes/hardware.routes.js
// ============================================
const express6 = require('express');
const router6 = express6.Router();
const { asyncHandler: asyncHandler6 } = require('../middleware/errorHandler');
const { authenticateHardware: authenticateHardware6 } = require('../middleware/auth');
const { validateBody: validateBody6, schemas: schemas6 } = require('../middleware/validateRequest');
const db6 = require('../config/database');

// POST /api/hardware/update - Hardware status update
router6.post('/update', authenticateHardware6, validateBody6(schemas6.hardwareUpdate), asyncHandler6(async (req, res) => {
    const updates = [];

    if (req.body.weight !== undefined) {
        await db6.query('UPDATE device_settings SET setting_value = ? WHERE setting_key = ?', [req.body.weight.toString(), 'current_weight']);
        updates.push('weight');
    }

    if (req.body.battery !== undefined) {
        await db6.query('UPDATE device_settings SET setting_value = ? WHERE setting_key = ?', [req.body.battery.toString(), 'battery_level']);
        updates.push('battery');
    }

    // Update connection status
    await db6.query("UPDATE device_settings SET setting_value = '1' WHERE setting_key = 'is_connected'");

    // Update heartbeat
    const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
    await db6.query('UPDATE device_settings SET setting_value = ? WHERE setting_key = ?', [now, 'last_heartbeat']);

    // Handle dispensed food
    if (req.body.dispensed !== undefined) {
        const date = new Date().toISOString().split('T')[0];
        const time = new Date().toTimeString().split(' ')[0];
        const type = req.body.type || 'Scheduled';

        await db6.query(
            'INSERT INTO history (feed_date, feed_time, rounds, type, status) VALUES (?, ?, ?, ?, ?)',
            [date, time, req.body.dispensed, type, 'Success']
        );

        await db6.query(
            'INSERT INTO alerts (alert_type, message, is_read) VALUES (?, ?, ?)',
            ['Info', `Device dispensed ${req.body.dispensed} rounds automatically.`, 0]
        );
    }

    res.json({
        success: true,
        message: 'Hardware update received',
        updated: updates
    });
}));

module.exports = router6;