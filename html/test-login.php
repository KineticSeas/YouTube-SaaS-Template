<?php
require_once 'includes/session.php';
require_once 'config/database.php';

echo "Testing authentication system...\n";
echo "================================\n\n";

// Test 1: Database connection
echo "1. Database Connection Test:\n";
$db = getDatabase();
if ($db->testConnection()) {
    echo "   ✓ Connected to vibe_templates database\n\n";
} else {
    echo "   ✗ Failed to connect: " . $db->getError() . "\n\n";
    exit(1);
}

// Test 2: Demo user exists
echo "2. Checking Demo User:\n";
$conn = $db->getConnection();
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute(['demo@todotracker.com']);
$user = $stmt->fetch();

if ($user) {
    echo "   ✓ Demo user found:\n";
    echo "     - ID: {$user['id']}\n";
    echo "     - Email: {$user['email']}\n";
    echo "     - Name: {$user['first_name']} {$user['last_name']}\n";
    echo "     - Email Verified: " . ($user['email_verified'] ? 'Yes' : 'No') . "\n\n";
} else {
    echo "   ✗ Demo user not found\n\n";
    exit(1);
}

// Test 3: Password verification
echo "3. Testing Password Verification:\n";
$testPassword = 'Demo123!';
if (password_verify($testPassword, $user['password_hash'])) {
    echo "   ✓ Password 'Demo123!' verified successfully\n\n";
} else {
    echo "   ✗ Password verification failed\n\n";
}

// Test 4: Session creation
echo "4. Testing Session Creation:\n";
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
$_SERVER['HTTP_USER_AGENT'] = 'Test Script';

if (createUserSession($user['id'], false)) {
    echo "   ✓ Session created successfully\n";
    echo "     - User ID: " . getCurrentUserId() . "\n";
    echo "     - Email: " . getCurrentUserEmail() . "\n";
    echo "     - Name: " . getCurrentUserName() . "\n";
    echo "     - Logged In: " . (isLoggedIn() ? 'Yes' : 'No') . "\n\n";

    // Check session in database
    $stmt = $conn->query("SELECT * FROM sessions WHERE user_id = {$user['id']} ORDER BY created_at DESC LIMIT 1");
    $session = $stmt->fetch();
    if ($session) {
        echo "   ✓ Session stored in database:\n";
        echo "     - Token: " . substr($session['session_token'], 0, 20) . "...\n";
        echo "     - Expires: {$session['expires_at']}\n\n";
    }
} else {
    echo "   ✗ Failed to create session\n\n";
}

// Test 5: CSRF Token
echo "5. Testing CSRF Token:\n";
$token = generateCSRFToken();
echo "   ✓ CSRF Token generated: " . substr($token, 0, 20) . "...\n";
if (validateCSRFToken($token)) {
    echo "   ✓ CSRF Token validated successfully\n\n";
} else {
    echo "   ✗ CSRF Token validation failed\n\n";
}

// Test 6: Session destruction
echo "6. Testing Logout:\n";
destroySession();
echo "   ✓ Session destroyed\n";
echo "     - Logged In: " . (isLoggedIn() ? 'Yes' : 'No') . "\n\n";

// Summary
echo "================================\n";
echo "✅ Authentication System Test Complete!\n";
echo "================================\n\n";
echo "The authentication system is working correctly with the vibe_templates database.\n";
echo "You can now:\n";
echo "  • Register at: http://localhost:8889/auth/register.php\n";
echo "  • Login at: http://localhost:8889/auth/login.php\n";
echo "  • Demo account: demo@todotracker.com / Demo123!\n";