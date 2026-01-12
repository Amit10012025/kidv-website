<?php
// JSON રિસ્પોન્સ હેડર
header('Content-Type: application/json; charset=utf-8');

// ૧. જો રિક્વેસ્ટ POST ન હોય તો અટકાવો
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// ૨. ડેટા મેળવો
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$solution = trim($_POST['solution'] ?? '');
$message = trim($_POST['message'] ?? '');
$hp = trim($_POST['hp_field'] ?? ''); // Honeypot spam check

// ૩. સ્પામ ચેક
if ($hp !== '') {
    echo json_encode(['success' => false, 'message' => 'Spam detected.']);
    exit;
}

// ૪. વેલિડેશન
if ($name === '' || $email === '' || $message === '') {
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
    exit;
}

// ૫. ઈમેલ વિગતો
$to = "amit@kidvtech.com"; // તમારો ઈમેલ
$subject = "New Inquiry from $name - $solution";

$email_body = "You have received a new inquiry from your website contact form.\n\n";
$email_body .= "Name: $name\n";
$email_body .= "Email: $email\n";
$email_body .= "Service: $solution\n\n";
$email_body .= "Message:\n$message\n";
$email_body .= "\n---\nSent from KIDV Tech Website";

// ૬. હેડર્સ (Headers)
$headers = "From: noreply@kidvtech.com\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// ૭. ઈમેલ મોકલો
if (mail($to, $subject, $email_body, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Thank you! Your message has been sent successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Server error: Could not send email. Please check hosting settings.']);
}
?>
