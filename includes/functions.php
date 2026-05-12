<?php
// includes/functions.php

spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/../classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

function base_url($path = '') {
    return '/Booking/' . ltrim($path, '/');
}

/**
 * Email Notification Engine
 * Supports Local Logging (Dev) and PHPMailer SMTP (Production)
 */
function sendEmailNotification($to, $subject, $message) {
    // --- GMAIL SMTP CONFIGURATION ---
    $useRealSMTP = true; 
    $smtpHost    = 'smtp.gmail.com'; 
    $smtpUser    = 'YOUR_GMAIL_@gmail.com';   // Replace with your Gmail address
    $smtpPass    = 'YOUR_APP_PASSWORD';      // Replace with your 16-character App Password
    $smtpPort    = 587;                       // Gmail TLS Port
    // ----------------------------------------------------------------

    if ($useRealSMTP) {
        $phpMailerPath = __DIR__ . '/PHPMailer/';
        
        // Check if PHPMailer files exist before trying to include them
        if (file_exists($phpMailerPath . 'Exception.php') && 
            file_exists($phpMailerPath . 'PHPMailer.php') && 
            file_exists($phpMailerPath . 'SMTP.php')) {
            
            require_once $phpMailerPath . 'Exception.php';
            require_once $phpMailerPath . 'PHPMailer.php';
            require_once $phpMailerPath . 'SMTP.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = $smtpHost;
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtpUser;
                $mail->Password   = $smtpPass;
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $smtpPort;

                // Recipients
                $mail->setFrom($smtpUser, 'Booking System');
                $mail->addAddress($to);

                // Content
                $mail->isHTML(true); // Set to true if sending HTML
                $mail->Subject = $subject;
                $mail->Body    = nl2br($message); // Convert newlines to <br>
                $mail->AltBody = strip_tags($message); // Plain text version

                $mail->send();
                return true;
            } catch (Exception $e) {
                error_log("Email failed: " . $mail->ErrorInfo);
                return false;
            }
        } else {
            error_log("PHPMailer library missing in $phpMailerPath. Please download it.");
        }
    }

    // Fallback: Log to local file (If SMTP is off or library is missing)
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) mkdir($logDir, 0777, true);
    
    $logEntry = "[" . date('Y-m-d H:i:s') . "] (LOG ONLY) TO: $to | SUBJECT: $subject | MESSAGE: " . str_replace("\n", " ", $message) . PHP_EOL;
    file_put_contents($logDir . '/email_notifications.log', $logEntry, FILE_APPEND);
    return true; 
}

/**
 * Get simulated weather for a destination
 */
function getDestinationWeather($destination) {
    $dest = strtoupper($destination);
    $weather = ['temp' => 24, 'condition' => 'Sunny', 'icon' => 'sun-fill', 'color' => 'warning'];
    
    if (strpos($dest, 'LON') !== false || strpos($dest, 'LHR') !== false) {
        $weather = ['temp' => 16, 'condition' => 'Cloudy', 'icon' => 'cloud-fill', 'color' => 'secondary'];
    } elseif (strpos($dest, 'DXB') !== false || strpos($dest, 'DUB') !== false) {
        $weather = ['temp' => 38, 'condition' => 'Clear Sky', 'icon' => 'sun-fill', 'color' => 'warning'];
    } elseif (strpos($dest, 'NYC') !== false || strpos($dest, 'JFK') !== false) {
        $weather = ['temp' => 21, 'condition' => 'Partly Cloudy', 'icon' => 'cloud-sun-fill', 'color' => 'info'];
    } elseif (strpos($dest, 'IST') !== false) {
        $weather = ['temp' => 23, 'condition' => 'Windy', 'icon' => 'wind', 'color' => 'primary'];
    }
    
    return $weather;
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
