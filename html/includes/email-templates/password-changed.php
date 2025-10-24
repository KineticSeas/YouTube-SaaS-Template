<?php
/**
 * Email Template: Password Changed
 * Sent when user changes their password
 */

// Variables that should be passed in:
// $recipientName - User's full name
// $changeTime - When the password was changed
// $ipAddress - IP address that made the change
// $securityPageLink - Link to security settings

$templateContent = <<<EOT
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Changed - TodoTracker</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #198754 0%, #198754 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .button {
            display: inline-block;
            background-color: #0d6efd;
            color: white;
            padding: 12px 30px;
            border-radius: 4px;
            text-decoration: none;
            margin: 20px 0;
            text-align: center;
        }
        .button:hover {
            background-color: #0b5ed7;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #198754;
            padding: 15px;
            margin: 20px 0;
        }
        .warning {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
        }
        h1 { margin: 0; }
        p { line-height: 1.6; color: #333; }
        .highlight { color: #198754; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Password Changed Successfully</h1>
        </div>
        <div class="content">
            <p>Hello <span class="highlight">{$recipientName}</span>,</p>

            <p>Your password for your TodoTracker account was successfully changed.</p>

            <div class="info-box">
                <strong>Change Details:</strong><br>
                Changed on: {$changeTime}<br>
                From IP: {$ipAddress}
            </div>

            <p><strong>Did you make this change?</strong></p>
            <p>If you recognize the IP address and made this change, no action is needed. Your account is secure.</p>

            <div class="warning">
                <strong>⚠️ Didn't Make This Change?</strong><br>
                If you didn't change your password, your account may have been compromised.
                <a href="{$securityPageLink}" style="color: #721c24; font-weight: bold;">Click here to secure your account</a>
                immediately.
            </div>

            <p>For your security, we recommend:</p>
            <ul>
                <li>Using a strong, unique password</li>
                <li>Never sharing your password with anyone</li>
                <li>Enabling two-factor authentication (coming soon)</li>
                <li>Reviewing your active sessions regularly</li>
            </ul>

            <div style="text-align: center;">
                <a href="{$securityPageLink}" class="button">View Security Settings</a>
            </div>

            <p>Best regards,<br>
            The TodoTracker Team</p>
        </div>
        <div class="footer">
            <p>© 2025 TodoTracker. All rights reserved.</p>
            <p>You received this email because your password was changed in your TodoTracker account.</p>
        </div>
    </div>
</body>
</html>
EOT;

return $templateContent;
?>
