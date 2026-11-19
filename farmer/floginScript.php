<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once('../sql.php');

$error = '';
$success = isset($_GET['registered']) && $_GET['registered'] === '1'
    ? 'Account created successfully. Please log in.'
    : '';

function verify_farmer_password($conn, $farmerId, $storedPassword, $password, &$upgradeFailed)
{
    $upgradeFailed = false;
    $legacyPasswordMatches = hash_equals($storedPassword, $password);

    if (password_verify($password, $storedPassword)) {
        if (!password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            return true;
        }
    } elseif (!$legacyPasswordMatches) {
        return false;
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    if ($passwordHash === false) {
        $upgradeFailed = true;
        return false;
    }

    $stmt = $conn->prepare('UPDATE farmerlogin SET password = ? WHERE farmer_id = ? AND password = ?');
    if (!$stmt) {
        $upgradeFailed = true;
        return false;
    }

    $stmt->bind_param('sis', $passwordHash, $farmerId, $storedPassword);
    $updated = $stmt->execute() && $stmt->affected_rows === 1;
    $stmt->close();
    $upgradeFailed = !$updated;
    return $updated;
}

if (isset($_POST['farmerlogin'])) {
    $email = isset($_POST['farmer_email']) && is_string($_POST['farmer_email'])
        ? strtolower(trim($_POST['farmer_email']))
        : '';
    $password = isset($_POST['farmer_password']) && is_string($_POST['farmer_password'])
        ? $_POST['farmer_password']
        : '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } elseif (!$conn) {
        $error = 'Unable to log in right now. Please try again later.';
    } else {
        $stmt = $conn->prepare('SELECT farmer_id, email, password FROM farmerlogin WHERE email = ? LIMIT 1');
        if (!$stmt) {
            $error = 'Unable to log in right now. Please try again later.';
        } else {
            $stmt->bind_param('s', $email);
            if (!$stmt->execute()) {
                $error = 'Unable to log in right now. Please try again later.';
                $stmt->close();
            } else {
                $stmt->bind_result($farmerId, $storedEmail, $storedPassword);
                $found = $stmt->fetch();
                $stmt->close();

                if (!$found) {
                    $error = 'Email or password is invalid.';
                } else {
                    $upgradeFailed = false;
                    if (verify_farmer_password($conn, $farmerId, $storedPassword, $password, $upgradeFailed)) {
                        session_regenerate_id(true);
                        $_SESSION['farmer_login_user'] = $storedEmail;
                        header('Location: ftradecrops.php');
                        exit();
                    }

                    $error = $upgradeFailed
                        ? 'Unable to securely update your password. Please try again.'
                        : 'Email or password is invalid.';
                }
            }
        }
    }
}
?>
