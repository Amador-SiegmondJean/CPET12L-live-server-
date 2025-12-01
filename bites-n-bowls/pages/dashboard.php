<?php
session_start();
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bites 'n Bowls Control Hub</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/images/favicon-32x32.png">
</head>
<body>

<div class="header-main">
    
    <div class="header-branding">
        <img src="../assets/images/logo.png" alt="Bites 'n Bowls Logo" class="header-logo-image">
        <h1 class="header-product-name">Bites 'n Bowls</h1>
    </div>

    <ul class="nav-tabs" id="main-nav-tabs" role="tablist">
        <li>
            <a class="nav-link-custom active" id="feed-control-tab-link" href="#feed-control-pane" role="tab" aria-controls="feed-control-pane" aria-selected="true">
                FEED CONTROL
            </a>
        </li>
        <li>
            <a class="nav-link-custom" id="system-history-tab-link" href="#system-history-pane" role="tab" aria-controls="system-history-pane" aria-selected="false">
                SYSTEM & HISTORY
            </a>
        </li>
        <li>
            <a class="nav-link-custom" id="account-settings-tab-link" href="#account-settings-tab" role="tab" aria-controls="account-settings-tab" aria-selected="false">
                ACCOUNT SETTINGS
            </a>
        </li>
        <li>
            <a class="nav-link-custom" id="factory-reset-tab-link" href="#factory-reset-tab" role="tab" aria-controls="factory-reset-tab" aria-selected="false">
                FACTORY RESET
            </a>
        </li>
        <li>
            <a class="nav-link-custom" href="../logout.php">
                LOGOUT
            </a>
        </li>
    </ul>

</div>
    
<div class="device-status-wrapper">
    <div class="header-status" id="header-status-wrapper">
        <span id="device-status-indicator" class="status-dot"></span> 
        <span class="status-text">Device Status: Online</span>
    </div>
</div>
    
<div class="tab-content">

    <div class="tab-pane active" id="feed-control-pane" role="tabpanel">
        <div class="card-grid">

            <div class="card" id="quick-feed-card">
                <div class="card-header-inner">Quick Feed</div>
                <p class="card-note-text">Dispense food immediately. Rounds control the portion size.</p>

                <div id="feed-feedback"></div>

                <div class="btn-action-group quick-feed-actions">
                    <button class="btn btn-primary quick-feed-btn" data-rounds="1">1 Round</button>
                    <button class="btn btn-primary quick-feed-btn" data-rounds="2">2 Rounds</button>
                    <button class="btn btn-primary quick-feed-btn" data-rounds="5">5 Rounds</button>
                </div>
                <input type="number" id="custom-rounds-input" class="input-text" placeholder="Enter custom rounds (e.g., 3)..." min="1">
                <button class="btn btn-secondary" id="dispense-now-btn">Dispense Now</button>
            </div>

            <div class="card" id="schedule-card">
                <div class="card-header-inner">Active Feeding Schedule</div>
                <div class="schedule-header-controls">
                    <h6 class="schedule-manage-title">Manage Scheduled Feedings:</h6>
                    <button class="btn btn-primary btn-small-action" id="add-schedule-btn">Add New Schedule</button>
                </div>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Interval</th>
                                <th>Time</th>
                                <th>Rounds</th>
                                <th>Days</th>
                                <th class="column-actions">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="schedule-list">
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-secondary" id="view-schedules-btn">View All Schedules</button>    
            </div>

        </div>
    </div>

    <div class="tab-pane" id="system-history-pane" role="tabpanel">
        <div class="card-grid system-grid">
            
            <div class="card" id="weight-status-card">
                <div class="card-header-inner">Weight & Supply Status</div>
                <div class="weight-display">
                    <h3 id="current-weight">500g</h3>
                    <p>Stored Feed Remaining</p>
                </div>
                <ul class="info-list">
                    <li class="info-list-item">
                        <span>Low Feed Warning:</span>
                        <strong id="low-feed-warning" class="status-success">None</strong>
                    </li>
                </ul>
                <p class="text-note calibration-note">
                    To ensure accurate measurement, empty the feed container before starting calibration.
                </p>
                <button class="btn btn-primary" id="recalibrate-btn">Recalibrate Sensor</button>
            </div>

            <div class="card" id="settings-status-card">
                <div class="card-header-inner">Device & Network Status</div>
                <ul class="info-list">
                    <li class="info-list-item">
                        <span>Wi-Fi Status:</span>
                        <strong class="status-success">Connected</strong>
                    </li>
                    <li class="info-list-item">
                        <span>Device Status:</span>
                        <strong class="status-success">Normal</strong>
                    </li>
                    <li class="info-list-item">
                        <span>Current Device Time:</span>
                        <strong id="current-device-time"></strong>
                    </li>
                    <li class="info-list-item">
                        <span>Firmware Version:</span>
                        <strong>v2.1.0</strong>
                    </li>
                </ul>
                <button class="btn btn-secondary" id="view-settings-btn">View Settings</button>
            </div>

            <div class="card" id="alerts-card">
                <div class="card-header-inner">System Alerts & Notifications</div>
                <ul id="alert-list" class="info-list alert-scroll-area">
                    <li id="no-alerts-placeholder" class="info-list-item placeholder-item">
                        No active warnings or alerts.
                    </li>
                </ul>
                <button class="btn btn-primary" id="view-all-alerts-btn">View All Alerts</button>
            </div>
            
            <div class="card">
                <div class="card-header-inner">Recent Feeding History</div>

                <div class="history-search-group">
                    <input type="text" id="history-search-input" class="input-text" placeholder="Search by Date, Type, or Status...">
                    <button class="btn btn-primary search-button" id="history-search-btn">Search</button>
                </div>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Rounds</th>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="history-list">
                        </tbody>
                    </table>
                </div>
                <button class="btn btn-secondary" id="view-history-btn">View All History</button>    
            </div>

        </div>
    </div>

    <div class="tab-pane" id="account-settings-tab" role="tabpanel">
        <div class="account-container">    
            <div class="card account-card-max-width">
                <div class="card-header-inner">Account Management</div>
                <div class="card-body">
                    <p>On this page, you can change the password of the current login user to ensure security and make it easy to remember.</p>
                    
                    <div class="account-form-grid">
                        <div class="form-fields">
                            <div class="form-group row">
                                <label for="username" class="col-sm-4 col-form-label">User Name:</label>
                                <div class="col-sm-8">
                                    <span id="username" class="form-control-plaintext">Admin</span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="old-password" class="col-sm-4 col-form-label">Old Password:</label>
                                <div class="col-sm-8">
                                    <input type="password" class="input-text" id="old-password">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="new-password" class="col-sm-4 col-form-label">New Password:</label>
                                <div class="col-sm-8">
                                    <input type="password" class="input-text" id="new-password">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="confirm-password" class="col-sm-4 col-form-label">Confirm Password:</label>
                                <div class="col-sm-8">
                                    <input type="password" class="input-text" id="confirm-password">
                                </div>
                            </div>
                        </div>
                        
                        <div class="password-rules">
                            <h3>Password Rules</h3>
                            <ol>
                                <li>The password must contain at least 8 characters.</li>
                                <li>The password must contain at least two of the following combinations: digits, uppercase letters, lowercase letters, and special characters.</li>
                                <li>The password cannot be your old password, your username, or your username in reverse.</li>
                            </ol>
                        </div>
                    </div>
                    
                    <div class="btn-action-group mt-3 apply-cancel-group">
                        <button class="btn btn-primary" id="apply-password-change">Apply</button>
                        <button class="btn btn-secondary" id="cancel-password-change">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane" id="factory-reset-tab" role="tabpanel">
        <div class="account-container">    
            <div class="card account-card-max-width">
                <div class="card-header-inner">Factory Reset</div>
                <div class="card-body">
                    <h3 class="warning-title">⚠️ Warning: System Reset</h3>
                    <p>Initiating a Factory Reset will restore the device to its original, default settings. This action is irreversible and will permanently erase all custom configurations, user data, history logs, and calibration settings.</p>
                    
                    <div class="reset-confirmation-box">
                        <p class="reset-confirmation-text"><strong>Please confirm you want to proceed. All current settings will be lost.</strong></p>
                    </div>
                    
                    <p>Before proceeding, ensure you have backed up any critical configuration data if necessary.</p>
                    
                    <div class="btn-action-group mt-4 reset-cancel-group">
                        <button class="btn btn-danger" id="reset-system-btn">Reset</button>
                        <button class="btn btn-secondary" id="cancel-reset-btn">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>    

<!-- MODALS (keeping all modal HTML from original) -->
<div id="schedule-modal" class="modal-base">
    <div class="modal-content-container">
        <div class="modal-header">
            <h2 id="schedule-modal-title">Add New Feeding Schedule</h2>
            <button class="btn-close-modal" onclick="document.getElementById('schedule-modal').style.display='none';">&times;</button>
        </div>
        <div class="modal-body">
            <form id="schedule-form">
                <input type="hidden" id="editing-schedule-id" value="">
                
                <div class="form-group">
                    <label for="schedule-interval">Feeding Interval:</label>
                    <select id="schedule-interval" class="input-select" required>
                        <option value="">-- Select Interval --</option>
                        <option value="2h">Every 2 Hours</option>
                        <option value="3h">Every 3 Hours</option>
                        <option value="4h">Every 4 Hours</option>
                        <option value="6h">Every 6 Hours</option>
                        <option value="8h">Every 8 Hours</option>
                        <option value="10h">Every 10 Hours</option>
                        <option value="12h">Every 12 Hours</option>
                        <option value="free">Free Feed (Continuous)</option>
                    </select>
                    <div id="rounds-display-container" class="calculated-rounds-display"></div>
                    <input type="hidden" id="schedule-rounds">
                </div>

                <div class="form-group">
                    <label for="schedule-time">Start Time (24-Hour):</label>
                    <input type="time" id="schedule-time" class="input-text" required>
                </div>

                <div class="form-group">
                    <label for="schedule-frequency">Frequency:</label>
                    <select id="schedule-frequency" class="input-select" required>
                        <option value="daily">Daily (Every Day)</option>
                        <option value="weekdays">Weekdays (Mon-Fri)</option>
                        <option value="weekends">Weekends (Sat-Sun)</option>
                        <option value="custom">Custom Days</option>
                    </select>
                </div>
                
                <div id="custom-days-container" class="form-group" style="display: none;">
                    <label for="custom-days-input">Custom Days (e.g., Mon, Wed, Fri):</label>
                    <input type="text" id="custom-days-input" class="input-text" placeholder="e.g., Mon, Wed, Fri">
                </div>

                <div class="btn-action-group modal-actions mt-3">
                    <button type="submit" class="btn btn-primary" id="save-schedule-btn">Save Schedule</button>
                    <button type="button" class="btn btn-secondary" id="cancel-schedule-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal-base" id="all-alerts-modal">
    <div class="modal-content-container">
        <div class="modal-header">
            <h2>All System Alerts & Notifications</h2>
            <button class="btn-close-modal" id="close-all-alerts-btn">&times;</button>
        </div>
        <div class="modal-body">
            <ul id="all-alerts-list" class="info-list full-list-scroll-area"></ul>
        </div>
    </div>
</div>

<div id="settings-modal" class="modal-base">
    <div class="modal-content-container settings-modal-content">
        <div class="modal-header">
            <h2>Device Configuration Settings</h2>
            <button class="btn-close-modal" id="settings-cancel-btn">&times;</button>
        </div>
        <div class="modal-body">
            <ul class="info-list">
                <li class="info-list-item">
                    <span>Wi-Fi SSID:</span>
                    <strong id="setting-status-ssid">Bites_n_Bowls</strong>
                </li>
                <li class="info-list-item">
                    <span>Timezone:</span>
                    <strong id="setting-status-timezone">PST (UTC-8)</strong>
                </li>
                <li class="info-list-item">
                    <span>Battery Level:</span>
                    <strong id="setting-status-battery">85%</strong>
                </li>
                <li class="info-list-item">
                    <span>Last Calibration:</span>
                    <strong id="setting-status-lastUpdated">N/A</strong>
                </li>
            </ul>
            <p class="text-note">
                Note: Advanced network and device settings require direct access to the device's setup interface.
            </p>
        </div>
    </div>
</div>

<div id="all-schedules-modal" class="modal-base">
    <div class="modal-content-container full-list-modal-content">
        <div class="modal-header">
            <h2>All Feeding Schedules</h2>
            <button class="btn-close-modal" id="close-all-schedules-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Interval</th>
                            <th>Time</th>
                            <th>Rounds</th>
                            <th>Days</th>
                            <th class="column-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="all-schedules-list"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="all-history-modal" class="modal-base">
    <div class="modal-content-container full-list-modal-content">
        <div class="modal-header">
            <h2>Full Feeding History Log</h2>
            <button class="btn-close-modal" id="close-all-history-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div class="history-search-group">
                <input type="text" id="modal-history-search-input" class="input-text" placeholder="Search entire log...">
            </div>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Rounds</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="all-history-list"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="footer-content">
        <p class="footer-disclaimer">
            The Bites 'n Bowls Smart Pet Feeder is a final project requirement for CPET12L and CPET11L. Developed by Amador, Dorongon, Padasay, Palacios, Santos R., Tapa
        </p>
        <p class="footer-copyright">
            &copy; 2025 Bites 'n Bowls Smart Pet Feeder | Project Website
        </p>
    </div>
</footer>
<script src="../assets/js/script.js"></script>
</body>
</html>