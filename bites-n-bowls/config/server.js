/**
 * Bites 'n Bowls - Main Server
 * A production-ready local server for smart pet feeder management
 */

require('dotenv').config();
const express = require('express');
const session = require('express-session');
const cookieParser = require('cookie-parser');
const cors = require('cors');
const helmet = require('helmet');
const morgan = require('morgan');
const path = require('path');
const os = require('os');

// Import configurations
const serverConfig = require('./config/server');
const db = require('./config/database');

// Import middleware
const errorHandler = require('./middleware/errorHandler');
const { authenticate } = require('./middleware/auth');

// Import routes
const authRoutes = require('./routes/auth.routes');
const scheduleRoutes = require('./routes/schedule.routes');
const feedRoutes = require('./routes/feed.routes');
const historyRoutes = require('./routes/history.routes');
const alertRoutes = require('./routes/alert.routes');
const settingsRoutes = require('./routes/settings.routes');
const hardwareRoutes = require('./routes/hardware.routes');

// Initialize Express app
const app = express();

// ================================
// Security Middleware
// ================================
app.use(helmet({
    contentSecurityPolicy: {
        directives: {
            defaultSrc: ["'self'"],
            styleSrc: ["'self'", "'unsafe-inline'", "https://fonts.googleapis.com", "https://cdn.jsdelivr.net"],
            fontSrc: ["'self'", "https://fonts.gstatic.com", "https://cdn.jsdelivr.net"],
            scriptSrc: ["'self'", "'unsafe-inline'"],
            imgSrc: ["'self'", "data:", "https:"],
        },
    },
}));

// CORS Configuration
app.use(cors({
    origin: function(origin, callback) {
        // Allow requests with no origin (mobile apps, Postman, etc.)
        if (!origin) return callback(null, true);
        
        // Allow same network access
        const allowedOrigins = [
            'http://localhost:3000',
            `http://${serverConfig.localIP}:${serverConfig.port}`
        ];
        
        if (allowedOrigins.indexOf(origin) !== -1) {
            callback(null, true);
        } else {
            callback(null, true); // Allow all for local network
        }
    },
    credentials: true
}));

// ================================
// Body Parsing Middleware
// ================================
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));
app.use(cookieParser());

// ================================
// Logging Middleware
// ================================
if (process.env.NODE_ENV === 'development') {
    app.use(morgan('dev'));
} else {
    app.use(morgan('combined'));
}

// ================================
// Session Configuration
// ================================
app.use(session({
    secret: process.env.SESSION_SECRET || 'bites-n-bowls-secret-key',
    resave: false,
    saveUninitialized: false,
    cookie: {
        secure: process.env.NODE_ENV === 'production',
        httpOnly: true,
        maxAge: parseInt(process.env.SESSION_MAX_AGE) || 86400000, // 24 hours
        sameSite: 'lax'
    }
}));

// ================================
// Static Files
// ================================
app.use('/css', express.static(path.join(__dirname, 'public/css')));
app.use('/js', express.static(path.join(__dirname, 'public/js')));
app.use('/images', express.static(path.join(__dirname, 'public/images')));

// ================================
// Routes
// ================================

// Root route - Login page
app.get('/', (req, res) => {
    if (req.session.userId) {
        return res.redirect('/dashboard');
    }
    res.sendFile(path.join(__dirname, 'views/index.html'));
});

// Dashboard route (protected)
app.get('/dashboard', authenticate, (req, res) => {
    res.sendFile(path.join(__dirname, 'views/dashboard.html'));
});

// Logout route
app.get('/logout', (req, res) => {
    req.session.destroy((err) => {
        if (err) {
            console.error('Session destruction error:', err);
        }
        res.redirect('/');
    });
});

// API Routes
app.use('/api/auth', authRoutes);
app.use('/api/schedules', scheduleRoutes);
app.use('/api/feed', feedRoutes);
app.use('/api/history', historyRoutes);
app.use('/api/alerts', alertRoutes);
app.use('/api/settings', settingsRoutes);
app.use('/api/hardware', hardwareRoutes);

// Health check endpoint
app.get('/health', (req, res) => {
    res.json({
        status: 'healthy',
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        environment: process.env.NODE_ENV
    });
});

// 404 Handler
app.use((req, res) => {
    res.status(404).json({
        success: false,
        message: 'Endpoint not found'
    });
});

// ================================
// Error Handler
// ================================
app.use(errorHandler);

// ================================
// Database Connection Test
// ================================
db.getConnection()
    .then(connection => {
        console.log('✅ Database connection successful');
        connection.release();
    })
    .catch(err => {
        console.error('❌ Database connection failed:', err.message);
        process.exit(1);
    });

// ================================
// Server Startup
// ================================
const PORT = process.env.PORT || 3000;
const HOST = process.env.HOST || '0.0.0.0';

const server = app.listen(PORT, HOST, () => {
    console.log('\n' + '='.repeat(60));
    console.log('🍖 Bites \'n Bowls Smart Pet Feeder Server');
    console.log('='.repeat(60));
    console.log(`📡 Environment: ${process.env.NODE_ENV || 'development'}`);
    console.log(`🚀 Server running on port: ${PORT}`);
    console.log(`\n📍 Access URLs:`);
    console.log(`   - Local:   http://localhost:${PORT}`);
    console.log(`   - Network: http://${serverConfig.localIP}:${PORT}`);
    console.log(`\n💡 Press CTRL+C to stop the server`);
    console.log('='.repeat(60) + '\n');
});

// ================================
// Graceful Shutdown
// ================================
process.on('SIGTERM', gracefulShutdown);
process.on('SIGINT', gracefulShutdown);

function gracefulShutdown() {
    console.log('\n🛑 Shutting down gracefully...');
    
    server.close(() => {
        console.log('✅ HTTP server closed');
        
        db.end()
            .then(() => {
                console.log('✅ Database connections closed');
                process.exit(0);
            })
            .catch(err => {
                console.error('❌ Error closing database:', err);
                process.exit(1);
            });
    });
    
    // Force close after 10 seconds
    setTimeout(() => {
        console.error('⚠️  Forced shutdown after timeout');
        process.exit(1);
    }, 10000);
}

// ================================
// Unhandled Errors
// ================================
process.on('unhandledRejection', (reason, promise) => {
    console.error('❌ Unhandled Rejection at:', promise, 'reason:', reason);
});

process.on('uncaughtException', (error) => {
    console.error('❌ Uncaught Exception:', error);
    gracefulShutdown();
});

module.exports = app;
    
