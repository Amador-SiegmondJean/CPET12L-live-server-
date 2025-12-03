/**
 * Request Validation Middleware
 * Validates and sanitizes user input
 */

const Joi = require('joi');

/**
 * Validate request body against a Joi schema
 */
function validateBody(schema) {
    return (req, res, next) => {
        const { error, value } = schema.validate(req.body, {
            abortEarly: false,
            stripUnknown: true
        });

        if (error) {
            const errors = error.details.map(detail => ({
                field: detail.path.join('.'),
                message: detail.message
            }));

            return res.status(400).json({
                success: false,
                message: 'Validation failed',
                errors: errors
            });
        }

        // Replace body with validated and sanitized value
        req.body = value;
        next();
    };
}

/**
 * Validate request query parameters
 */
function validateQuery(schema) {
    return (req, res, next) => {
        const { error, value } = schema.validate(req.query, {
            abortEarly: false,
            stripUnknown: true
        });

        if (error) {
            const errors = error.details.map(detail => ({
                field: detail.path.join('.'),
                message: detail.message
            }));

            return res.status(400).json({
                success: false,
                message: 'Invalid query parameters',
                errors: errors
            });
        }

        req.query = value;
        next();
    };
}

/**
 * Validate request params
 */
function validateParams(schema) {
    return (req, res, next) => {
        const { error, value } = schema.validate(req.params, {
            abortEarly: false,
            stripUnknown: true
        });

        if (error) {
            const errors = error.details.map(detail => ({
                field: detail.path.join('.'),
                message: detail.message
            }));

            return res.status(400).json({
                success: false,
                message: 'Invalid URL parameters',
                errors: errors
            });
        }

        req.params = value;
        next();
    };
}

// Common validation schemas
const schemas = {
    login: Joi.object({
        username: Joi.string().min(3).max(50).required(),
        password: Joi.string().min(1).required()
    }),

    changePassword: Joi.object({
        oldPassword: Joi.string().required(),
        newPassword: Joi.string().min(8).required(),
        confirmPassword: Joi.string().valid(Joi.ref('newPassword')).required()
    }),

    schedule: Joi.object({
        id: Joi.number().integer().optional(),
        interval: Joi.string().valid('2h', '3h', '4h', '6h', '8h', '10h', '12h', 'free').required(),
        time: Joi.string().pattern(/^([01]\d|2[0-3]):([0-5]\d)$/).required(),
        rounds: Joi.number().integer().min(-1).required(),
        frequency: Joi.string().valid('daily', 'weekdays', 'weekends', 'custom').required(),
        customDays: Joi.string().allow('').optional()
    }),

    feed: Joi.object({
        rounds: Joi.number().integer().min(1).max(50).required(),
        type: Joi.string().valid('Manual', 'Scheduled').default('Manual'),
        weightDispensed: Joi.number().integer().min(0).required()
    }),

    hardwareUpdate: Joi.object({
        weight: Joi.number().integer().min(0).optional(),
        battery: Joi.number().integer().min(0).max(100).optional(),
        dispensed: Joi.number().integer().min(0).optional(),
        type: Joi.string().valid('Scheduled', 'Manual').optional()
    }),

    searchQuery: Joi.object({
        search: Joi.string().max(100).optional(),
        limit: Joi.number().integer().min(1).max(1000).optional()
    }),

    idParam: Joi.object({
        id: Joi.number().integer().positive().required()
    })
};

module.exports = {
    validateBody,
    validateQuery,
    validateParams,
    schemas
};
