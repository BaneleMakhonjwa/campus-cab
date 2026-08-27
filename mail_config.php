<?php
// mail_config.php - UFH Email Settings

// Load PHPMailer
require_once __DIR__ . '/src/PHPMailer.php';
require_once __DIR__ . '/src/SMTP.php';
require_once __DIR__ . '/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendVerificationEmail($email, $name, $token) {
    $mail = new PHPMailer(true);

    try {
        // Server settings for UFH
        $mail->SMTPDebug = 2; // Shows client/server messages
        $mail->Debugoutput = 'html'; // Makes the output readable in a browser                      // 0 = no debug, 1 = errors, 2 = full
        $mail->isSMTP();
        $mail->Host       = 'smtp.ufh.ac.za';     // UFH SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'YOUR_UFH_EMAIL@ufh.ac.za';  // Your UFH email
        $mail->Password   = 'YOUR_UFH_PASSWORD';         // Your UFH email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // or 'tls'
        $mail->Port       = 587;                   // UFH SMTP port (check with IT)

        // Recipients
        $mail->setFrom('YOUR_UFH_EMAIL@ufh.ac.za', 'Campus Cab');
        $mail->addAddress($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Campus Cab Account';

        $verification_link = "http://localhost/campus-cab/verify_email.php?token=" . urlencode($token);

        $mail->Body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #2c3e50; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; border: 1px solid #ddd; }
                .button { display: inline-block; padding: 12px 24px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; }
                .footer { text-align: center; color: #666; font-size: 12px; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚗 Campus Cab</h1>
                </div>
                <div class='content'>
                    <h2>Welcome $name!</h2>
                    <p>Thank you for registering with Campus Cab.</p>
                    <p>Please click the button below to verify your email address:</p>
                    <p style='text-align: center;'>
                        <a href='$verification_link' class='button'>Verify Email</a>
                    </p>
                    <p>Or copy and paste this link in your browser:</p>
                    <p><a href='$verification_link'>$verification_link</a></p>
                    <p>This link will expire in 24 hours.</p>
                </div>
                <div class='footer'>
                    <p>© 2024 Campus Cab. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->AltBody = "Welcome $name!\n\nPlease verify your email by clicking this link:\n$verification_link\n\nThis link will expire in 24 hours.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}