// ============================================
// routes/settings.routes.js
// ============================================
const express5 = require('express');
const router5 = express5.Router();
const { asyncHandler: asyncHandler5 } = require('../middleware/errorHandler');
const { authenticate: authenticate5 } = require('../middleware/auth');
const db5 = require('../config/database');

// GET /api/settings - Get device settings
router5.get('/', authenticate5, asyncHandler5(async (req, res) => {
    const sql = 'SELECT * FROM device_settings';
    const settings = await db5.query(sql);

    const settingsObj = {};
    settings.forEach(row => {
        settingsObj[row.setting_key] = row.setting_value;
    });

    res.json({
        success: true,
        settings: settingsObj
    });
}));

// GET /api/settings/status - Get device status
router5.get('/status', authenticate5, asyncHandler5(async (req, res) => {
    const sql = 'SELECT * FROM device_settings';
    const settings = await db5.query(sql);

    const status = {
        online: false,
        weight: 0,
        battery: 0,
        wifi_signal: 0,
        last_heartbeat: null
    };

    settings.forEach(row => {
        switch(row.setting_key) {
            case 'is_connected':
                status.online = row.setting_value === '1';
                break;
            case 'current_weight':
                status.weight = parseInt(row.setting_value);
                break;
            case 'battery_level':
                status.battery = parseInt(row.setting_value);
                break;
            case 'last_heartbeat':
                status.last_heartbeat = row.setting_value;
                break;
        }
    });

    // Check if last heartbeat is within 30 seconds
    if (status.last_heartbeat) {
        const lastBeat = new Date(status.last_heartbeat).getTime();
        const now = Date.now();
        if ((now - lastBeat) > 30000) {
            status.online = false;
        }
    }

    res.json({
        success: true,
        status
    });
}));

// POST /api/settings/factory-reset - Factory reset
router5.post('/factory-reset', authenticate5, asyncHandler5(async (req, res) => {
    await db5.query('TRUNCATE TABLE schedules');
    await db5.query('TRUNCATE TABLE history');
    await db5.query('TRUNCATE TABLE alerts');
    await db5.query("UPDATE device_settings SET setting_value = '0' WHERE setting_key = 'current_weight'");
    await db5.query("UPDATE device_settings SET setting_value = 'Not Set' WHERE setting_key = 'wifi_ssid'");
    await db5.query("UPDATE device_settings SET setting_value = '0' WHERE setting_key = 'battery_level'");
    await db5.query("UPDATE device_settings SET setting_value = '0' WHERE setting_key = 'is_connected'");
    await db5.query("UPDATE users SET password = '1234' WHERE username = 'admin'");

    res.json({
        success: true,
        message: 'Factory reset complete'
    });
}));

module.exports = router5;