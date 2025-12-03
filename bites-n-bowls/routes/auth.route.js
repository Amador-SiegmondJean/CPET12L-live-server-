/**
 * Authentication Routes
 * Login, logout, and password management
 */

const express = require('express');
const router = express.Router();
const { asyncHandler } = require('../middleware/errorHandler');
const { authenticate } = require('../middleware/auth');
const { validateBody, schemas } = require('../middleware/validateRequest');
const db = require('../config/database');

/**
 * POST /api/auth/login
 * User login
 */
router.post('/login', 
    validateBody(schemas.login),
    asyncHandler(async (req, res) => {
        const { username, password } = req.body;

        // Query user
        const sql = 'SELECT * FROM users WHERE username = ?';
        const users = await db.query(sql, [username]);

        if (users.length === 0) {
            return res.status(401).json({
                success: false,
                message: 'User not found'
            });
        }

        const user = users[0];

        // Verify password (plain text for now - TODO: implement bcrypt)
        if (password !== user.password) {
            return res.status(401).json({
                success: false,
                message: 'Invalid password'
            });
        }

        // Create session
        req.session.userId = user.id;
        req.session.username = user.username;

        res.json({
            success: true,
            message: 'Login successful',
            user: {
                id: user.id,
                username: user.username
            }
        });
    })
);

/**
 * POST /api/auth/logout
 * User logout
 */
router.post('/logout', asyncHandler(async (req, res) => {
    req.session.destroy((err) => {
        if (err) {
            return res.status(500).json({
                success: false,
                message: 'Logout failed'
            });
        }

        res.clearCookie('connect.sid');
        res.json({
            success: true,
            message: 'Logged out successfully'
        });
    });
}));

/**
 * POST /api/auth/change-password
 * Change user password
 */
router.post('/change-password',
    authenticate,
    validateBody(schemas.changePassword),
    asyncHandler(async (req, res) => {
        const { oldPassword, newPassword } = req.body;
        const username = req.session.username;

        // Verify old password
        const sql = 'SELECT password FROM users WHERE username = ?';
        const users = await db.query(sql, [username]);

        if (users.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }

        if (users[0].password !== oldPassword) {
            return res.status(401).json({
                success: false,
                message: 'Old password is incorrect'
            });
        }

        // Password validation rules
        if (newPassword.length < 8) {
            return res.status(400).json({
                success: false,
                message: 'Password must be at least 8 characters'
            });
        }

        const hasUppercase = /[A-Z]/.test(newPassword);
        const hasLowercase = /[a-z]/.test(newPassword);
        const hasDigit = /[0-9]/.test(newPassword);
        const hasSpecial = /[~`!@#$%^&*()_\-=+[\]{}|\\;:'"<,>./?]/.test(newPassword);

        let complexityCount = 0;
        if (hasUppercase) complexityCount++;
        if (hasLowercase) complexityCount++;
        if (hasDigit) complexityCount++;
        if (hasSpecial) complexityCount++;

        if (complexityCount < 2) {
            return res.status(400).json({
                success: false,
                message: 'Password must contain at least two of: digits, uppercase, lowercase, or special characters'
            });
        }

        // Update password
        const updateSql = 'UPDATE users SET password = ? WHERE username = ?';
        await db.query(updateSql, [newPassword, username]);

        res.json({
            success: true,
            message: 'Password updated successfully'
        });
    })
);

/**
 * GET /api/auth/session
 * Check if user is authenticated
 */
router.get('/session', (req, res) => {
    if (req.session && req.session.userId) {
        res.json({
            success: true,
            authenticated: true,
            user: {
                id: req.session.userId,
                username: req.session.username
            }
        });
    } else {
        res.json({
            success: true,
            authenticated: false
        });
    }
});

module.exports = router;