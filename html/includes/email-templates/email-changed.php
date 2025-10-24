<?php
/**
 * Email Template: Email Address Changed
 * Sent when user changes their email address
 */

// Variables that should be passed in:
// $recipientName - User's full name
// $newEmail - New email address
// $verificationLink - Link to verify new email
// $revertLink - Link to revert email change (optional)

$templateContent = <<<EOT
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Address Changed - TodoTracker</title>
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
            background: linear-gradient(135deg, #0d6efd 0%, #0d6efd 100%);
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
        .warning {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
        }
        h1 { margin: 0; }
        p { line-height: 1.6; color: #333; }
        .highlight { color: #0d6efd; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Email Address Changed</h1>
        </div>
        <div class="content">
            <p>Hello <span class="highlight">{$recipientName}</span>,</p>

            <p>Your email address on TodoTracker has been changed to <span class="highlight">{$newEmail}</span>.</p>

            <p><strong>Next Step:</strong> We need to verify this new email address. Please click the button below to confirm your email:</p>

            <div style="text-align: center;">
                <a href="{$verificationLink}" class="button">Verify Email Address</a>
            </div>

            <p>Or copy and paste this link in your browser:</p>
            <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px;">
                {$verificationLink}
            </p>

            <div class="warning">
                <strong>⚠️ Security Notice:</strong> If you didn't make this change, please contact us immediately or use your old email to revert this change.
            </div>

            <p>This verification link will expire in 24 hours.</p>

            <p>Best regards,<br>
            The TodoTracker Team</p>
        </div>
        <div class="footer">
            <p>© 2025 TodoTracker. All rights reserved.</p>
            <p>You received this email because your email address was changed in your TodoTracker account.</p>
        </div>
    </div>
</body>
</html>
EOT;

return $templateContent;
?>
