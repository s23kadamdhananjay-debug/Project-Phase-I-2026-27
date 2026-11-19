<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once('../sql.php');

$error = '';

/**
 * Accepts a bcrypt/argon hash or a legacy plain-text password and upgrades
 * legacy rows to password_hash() after a successful login.
 */
function customer_password_matches($conn, $customerId, $storedPassword, $password, &$upgradeFailed)
{
    $upgradeFailed = false;
    $legacyMatch = hash_equals((string)$storedPassword, (string)$password);

    if (password_verify($password, $storedPassword)) {
        if (!password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            return true;
        }
    } elseif (!$legacyMatch) {
        return false;
    }

    $newHash = password_hash($password, PASSWORD_DEFAULT);
    if ($newHash === false) {
        $upgradeFailed = true;
        return false;
    }

    $stmt = $conn->prepare('UPDATE custlogin SET password = ? WHERE cust_id = ? AND password = ?');
    if (!$stmt) {
        $upgradeFailed = true;
        return false;
    }
    $stmt->bind_param('sis', $newHash, $customerId, $storedPassword);
    $updated = $stmt->execute() && $stmt->affected_rows === 1;
    $stmt->close();
    $upgradeFailed = !$updated;
    return $updated;
}

if (isset($_POST['customerlogin'])) {
    $email = isset($_POST['cust_email']) && is_string($_POST['cust_email'])
        ? strtolower(trim($_POST['cust_email']))
        : '';
    $password = isset($_POST['cust_password']) && is_string($_POST['cust_password'])
        ? $_POST['cust_password']
        : '';

    if ($email === '' || $password === '') {
        $error = 'Email and password are required.';
    } elseif (!$conn) {
        $error = 'Unable to log in right now. Please try again later.';
    } else {
        $stmt = $conn->prepare('SELECT cust_id, email, password FROM custlogin WHERE LOWER(email) = ? LIMIT 1');
        if (!$stmt) {
            $error = 'Unable to log in right now. Please try again later.';
        } else {
            $stmt->bind_param('s', $email);
            if (!$stmt->execute()) {
                $stmt->close();
                $error = 'Unable to log in right now. Please try again later.';
            } else {
                $stmt->bind_result($customerId, $storedEmail, $storedPassword);
                $found = $stmt->fetch();
                $stmt->close();

                if (!$found) {
                    $error = 'Email or password is invalid.';
                } else {
                    $upgradeFailed = false;
                    if (customer_password_matches($conn, $customerId, $storedPassword, $password, $upgradeFailed)) {
                        session_regenerate_id(true);
                        $_SESSION['customer_login_user'] = $storedEmail;
                        $_SESSION['customer_id'] = (int)$customerId;

                        $clearCart = $conn->prepare('DELETE FROM cart WHERE cust_id = ?');
                        if ($clearCart) {
                            $clearCart->bind_param('i', $customerId);
                            $clearCart->execute();
                            $clearCart->close();
                        }

                        header('Location: cbuy_crops.php');
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
