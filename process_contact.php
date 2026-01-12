<?php
// process_contact.php
// Expects: POST name, email, solution, message, optional hp_field (honeypot)
// Returns JSON: { success: bool, message: string }

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/config.php';

// Simple JSON response helper
function json_response($ok, $msg) {
    echo json_encode(['success' => $ok, 'message' => $msg]);
    exit;
}

// Basic helper to log errors to file (do not expose to users)
function log_error($msg) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    @file_put_contents(MAIL_LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

// Retrieve POST safely
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$solution = trim($_POST['solution'] ?? '');
$message = trim($_POST['message'] ?? '');
$hp = trim($_POST['hp_field'] ?? ''); // honeypot

// Honeypot check (server-side)
if ($hp !== '') {
    // silently reject spam
    json_response(false, 'Spam detected.');
}

// Validate required fields
if ($name === '' || $email === '' || $message === '') {
    json_response(false, 'Please fill all required fields (name, email, message).');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(false, 'Please enter a valid email address.');
}

// Save to DB
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    $stmt = $pdo->prepare("INSERT INTO enquiries (name, email, solution, message, ip, user_agent) VALUES (:name, :email, :solution, :message, :ip, :ua)");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':solution' => $solution,
        ':message' => $message,
        ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ':ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
    ]);
    $insertId = $pdo->lastInsertId();
} catch (Exception $e) {
    log_error("DB error: " . $e->getMessage());
    json_response(false, 'Could not save your enquiry. Please try again later.');
}

// Send email via PHPMailer (Gmail SMTP)
require_once __DIR__ . '/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    // Use PHPMailer constants if available
    if (defined('PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS')) {
        // newer PHPMailer versions
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    } else {
        $mail->SMTPSecure = SMTP_ENCRYPTION; // 'tls'
    }
    $mail->Port = SMTP_PORT;
    $mail->SMTPAutoTLS = true;
    $mail->Timeout = 30;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
    $mail->addAddress(MAIL_TO);
    $mail->addReplyTo($email, $name);

    // Content
    $mail->isHTML(true);
    $mail->Subject = "New enquiry from website: " . $name;
    $body  = "<h3>New enquiry</h3>";
    $body .= "<p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>";
    $body .= "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    $body .= "<p><strong>Solution:</strong> " . htmlspecialchars($solution) . "</p>";
    $body .= "<p><strong>Message:</strong><br>" . nl2br(htmlspecialchars($message)) . "</p>";
    $body .= "<hr><p style='font-size:12px;color:#666'>IP: " . ($_SERVER['REMOTE_ADDR'] ?? '') . " | UA: " . substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 200) . "</p>";

    $mail->Body = $body;
    $mail->AltBody = strip_tags(str_replace(["<br/>","<br>"], "\n", $body));

    $mail->send();
    // success: saved + emailed
    json_response(true, 'Thank you — your message has been sent. We will contact you soon.');
} catch (Exception $e) {
    // Log error and return success (data saved) but notify about email failure
    $err = 'Mailer Error: ' . $mail->ErrorInfo . ' | Exception: ' . $e->getMessage();
    log_error($err);
    json_response(true, 'Your enquiry was saved, but we could not send an email notification at this time. We will contact you soon.');
}
