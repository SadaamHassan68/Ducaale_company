<?php
// includes/functions.php

spl_autoload_register(function ($class_name) {
    $file = __DIR__ . '/../classes/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

function base_url($path = '') {
    $is_local = (php_sapi_name() == 'cli' || (isset($_SERVER['REMOTE_ADDR']) && ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1')));
    $prefix = $is_local ? '/Booking/' : '/';
    return $prefix . ltrim($path, '/');
}

function full_url($path = '') {
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http");
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $protocol . "://" . $host . base_url($path);
}

/**
 * Email Notification Engine
 * Supports Local Logging (Dev) and PHPMailer SMTP (Production)
 */
function sendEmailNotification($to, $subject, $message, $isHtmlTemplate = false) {

    // --- GMAIL SMTP CONFIGURATION ---
    $useRealSMTP = true; 
    $smtpHost    = 'smtp.gmail.com'; 
    $smtpUser    = 'sadamhassan688@gmail.com';   // User's Gmail address
    $smtpPass    = 'asuc qbhb ponj dekb';      // 16-character App Password provided by user
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
                $mail->isHTML(true); 
                $mail->Subject = $subject;
                $mail->Body    = $isHtmlTemplate ? $message : nl2br($message);
                $mail->AltBody = strip_tags($message);


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

/**
 * Generate a professional HTML Boarding Pass for Email
 */
function generateTicketHtml($details) {
    $pnr = $details['booking_reference'];
    $name = $details['final_name'];
    $dest = $details['destination'];
    $origin = $details['origin'] ?? 'HGA';
    $flight = $details['flight_number'] ?? 'DH-101';
    $seat = $details['seat_number'] ?? '12A';
    $class = $details['seat_class'] ?? 'Economy';
    $time = isset($details['departure_time']) ? date('H:i', strtotime($details['departure_time'])) : '08:00';
    $date = isset($details['departure_time']) ? date('d M Y', strtotime($details['departure_time'])) : date('d M Y');
    
    $ticketUrl = full_url('ticket.php?pnr=' . $pnr);

    return '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body { font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; background-color: #f4f7f9; }
            .container { max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
            .header { background-color: #0f172a; color: #ffffff; padding: 30px; text-align: center; }
            .header h1 { margin: 0; font-size: 24px; letter-spacing: 2px; }
            .header p { margin: 5px 0 0; opacity: 0.8; font-size: 14px; }
            .content { padding: 30px; }
            .ticket-box { border: 2px dashed #e2e8f0; border-radius: 10px; padding: 20px; background-color: #f8fafc; }
            .route { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
            .city { text-align: center; flex: 1; }
            .city h2 { margin: 0; font-size: 32px; color: #0f172a; }
            .city p { margin: 0; font-size: 12px; color: #64748b; text-transform: uppercase; }
            .plane { flex: 1; text-align: center; color: #3b82f6; font-size: 24px; }
            .details { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 20px; border-top: 1px solid #e2e8f0; padding-top: 20px; }
            .detail-item { margin-bottom: 10px; }
            .label { font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: bold; }
            .value { font-size: 16px; color: #0f172a; font-weight: bold; }
            .footer { padding: 30px; text-align: center; background-color: #f8fafc; }
            .btn { display: inline-block; padding: 15px 30px; background-color: #3b82f6; color: #ffffff !important; text-decoration: none; border-radius: 50px; font-weight: bold; margin-top: 10px; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2); }
            .pnr-badge { display: inline-block; padding: 5px 15px; background-color: #e0f2fe; color: #0369a1; border-radius: 5px; font-family: monospace; font-weight: bold; font-size: 18px; margin-bottom: 10px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>DUCAALE AIRLINE</h1>
                <p>Fly Elite, Travel Anywhere</p>
            </div>
            <div class="content">
                <div style="text-align: center; margin-bottom: 25px;">
                    <p style="margin: 0; color: #64748b;">Dear ' . htmlspecialchars($name) . ', your ticket is confirmed!</p>
                    <div class="pnr-badge">' . htmlspecialchars($pnr) . '</div>
                </div>
                
                <div class="ticket-box">
                    <table width="100%" cellpadding="0" cellspacing="0">
                        <tr>
                            <td align="center" width="40%">
                                <div class="city">
                                    <p>Origin</p>
                                    <h2>' . htmlspecialchars(substr($origin, 0, 3)) . '</h2>
                                    <p style="font-size: 10px;">' . htmlspecialchars($origin) . '</p>
                                </div>
                            </td>
                            <td align="center" width="20%">
                                <div class="plane">✈️</div>
                            </td>
                            <td align="center" width="40%">
                                <div class="city">
                                    <p>Destination</p>
                                    <h2>' . htmlspecialchars(substr($dest, 0, 3)) . '</h2>
                                    <p style="font-size: 10px;">' . htmlspecialchars($dest) . '</p>
                                </div>
                            </td>
                        </tr>
                    </table>
                    
                    <div style="margin-top: 25px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td width="50%" style="padding-bottom: 15px;">
                                    <div class="label">Passenger</div>
                                    <div class="value">' . htmlspecialchars($name) . '</div>
                                </td>
                                <td width="50%" style="padding-bottom: 15px;">
                                    <div class="label">Flight No</div>
                                    <div class="value">' . htmlspecialchars($flight) . '</div>
                                </td>
                            </tr>
                            <tr>
                                <td width="50%">
                                    <div class="label">Date & Time</div>
                                    <div class="value">' . $date . ' | ' . $time . '</div>
                                </td>
                                <td width="50%">
                                    <div class="label">Seat / Class</div>
                                    <div class="value">' . htmlspecialchars($seat) . ' / ' . htmlspecialchars($class) . '</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="footer">
                <p style="margin: 0 0 20px; font-size: 14px; color: #64748b;">No printer? No problem! Just show this email or a <strong>Screenshot</strong> on your phone at the check-in counter.</p>
                <a href="' . $ticketUrl . '" class="btn">VIEW DIGITAL TICKET</a>
                <p style="margin-top: 25px; font-size: 11px; color: #94a3b8;">© ' . date('Y') . ' Ducaale Airline. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ';
}

if (session_status() === PHP_SESSION_NONE) {

    session_start();
}
?>
