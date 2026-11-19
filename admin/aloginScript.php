<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once('../sql.php');

$error = '';

if (isset($_POST['adminlogin'])) {
    $adminName = isset($_POST['admin_name']) && is_string($_POST['admin_name'])
        ? trim($_POST['admin_name'])
        : '';
    $adminPassword = isset($_POST['admin_password']) && is_string($_POST['admin_password'])
        ? $_POST['admin_password']
        : '';

    if ($adminName === '' || $adminPassword === '') {
        $error = 'Admin ID and password are required.';
    } elseif (!$conn) {
        $error = 'Unable to log in right now. Please try again later.';
    } else {
        $stmt = $conn->prepare('SELECT admin_id, admin_name, admin_password FROM admin WHERE admin_name = ? LIMIT 1');
        if (!$stmt) {
            $error = 'Unable to log in right now. Please try again later.';
        } else {
            $stmt->bind_param('s', $adminName);
            if (!$stmt->execute()) {
                $stmt->close();
                $error = 'Unable to log in right now. Please try again later.';
            } else {
                $stmt->bind_result($adminId, $storedName, $storedPassword);
                $found = $stmt->fetch();
                $stmt->close();

                if (!$found) {
                    $error = 'Admin ID or password is invalid.';
                } else {
                    $legacyMatch = hash_equals((string)$storedPassword, (string)$adminPassword);
                    if (!password_verify($adminPassword, $storedPassword) && !$legacyMatch) {
                        $error = 'Admin ID or password is invalid.';
                    } else {
                        if (!password_get_info($storedPassword)['algo']) {
                            $newHash = password_hash($adminPassword, PASSWORD_DEFAULT);
                            if ($newHash !== false) {
                                $update = $conn->prepare('UPDATE admin SET admin_password = ? WHERE admin_id = ?');
                                if ($update) {
                                    $update->bind_param('si', $newHash, $adminId);
                                    $update->execute();
                                    $update->close();
                                }
                            }
                        }

                        session_regenerate_id(true);
                        $_SESSION['admin_login_user'] = $storedName;
                        header('Location: aprofile.php');
                        exit();
                    }
                }
            }
        }
    }
}
?>
