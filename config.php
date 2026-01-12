<?php
// config.php - put this file outside webroot if possible and update values

// ---------- MySQL settings ----------
define('DB_HOST', 'localhost');        // usually localhost
define('DB_NAME', 'your_database');    // change to your DB name
define('DB_USER', 'your_db_user');     // change to your DB user
define('DB_PASS', 'your_db_password'); // change to your DB password
define('DB_CHARSET', 'utf8mb4');

// ---------- Gmail SMTP settings ----------
/*
  Notes:
  - Use an App Password if the account has 2-Step Verification enabled:
    https://support.google.com/accounts/answer/185833
  - For Google Workspace accounts an admin may need to allow SMTP or create an app password.
*/
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_ENCRYPTION', 'tls'); // use 'tls' for STARTTLS
define('SMTP_USERNAME', 'amit@kidvtech.com'); // your Gmail/Workspace email
define('SMTP_PASSWORD', 'your_app_password_here'); // APP PASSWORD (recommended)

// FROM/TO addresses
define('MAIL_FROM', 'amit@kidvtech.com');       // sender address (should match SMTP user for best deliverability)
define('MAIL_FROM_NAME', 'KIDV Tech');
define('MAIL_TO', 'amit@kidvtech.com');         // where enquiry emails are sent

// Admin basic auth (optional, for admin.php if used)
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'ChangeThisPassword!');

// Logging
define('MAIL_LOG_FILE', __DIR__ . '/mail_errors.log'); // errors will be appended here
