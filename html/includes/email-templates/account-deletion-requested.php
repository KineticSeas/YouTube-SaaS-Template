<?php
/**
 * Email Template: Account Deletion Requested
 * Sent when user requests account deletion
 */

// Variables that should be passed in:
// $recipientName - User's full name
// $cancellationLink - Link to cancel account deletion
// $deletionDate - When account will be permanently deleted

$templateContent = <<<EOT
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Deletion Initiated - TodoTracker</title>
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
            background: linear-gradient(135deg, #dc3545 0%, #dc3545 100%);
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
        .cancel-button {
            background-color: #198754;
        }
        .cancel-button:hover {
            background-color: #157347;
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
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #721c24;
        }
        .countdown {
            background-color: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
            color: #856404;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        h1 { margin: 0; }
        p { line-height: 1.6; color: #333; }
        .highlight { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Account Deletion Initiated</h1>
        </div>
        <div class="content">
            <p>Hello <span class="highlight">{$recipientName}</span>,</p>

            <p>Your request to delete your TodoTracker account has been received and processed.</p>

            <div class="countdown">
                Your account will be permanently deleted on<br>
                <strong>{$deletionDate}</strong>
            </div>

            <p>During this 30-day grace period, you can cancel the deletion and keep your account.</p>

            <div class="warning">
                <strong>⚠️ Important:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>All your tasks, categories, and preferences will be deleted</li>
                    <li>This action cannot be undone after 30 days</li>
                    <li>You will be logged out of all sessions immediately</li>
                    <li>Access to your data will be permanently lost</li>
                </ul>
            </div>

            <p style="text-align: center; font-weight: bold; color: #198754;">
                Want to keep your account?
            </p>

            <div style="text-align: center;">
                <a href="{$cancellationLink}" class="button cancel-button">Cancel Account Deletion</a>
            </div>

            <p>Or copy and paste this link in your browser:</p>
            <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px;">
                {$cancellationLink}
            </p>

            <p>If you have any questions or need help, please contact our support team.</p>

            <p>Best regards,<br>
            The TodoTracker Team</p>
        </div>
        <div class="footer">
            <p>© 2025 TodoTracker. All rights reserved.</p>
            <p>You received this email because an account deletion was requested for your TodoTracker account.</p>
        </div>
    </div>
</body>
</html>
EOT;

return $templateContent;
?>
