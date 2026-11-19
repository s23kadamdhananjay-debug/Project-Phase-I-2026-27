<?php

session_start(); // Starting Session

require('../sql.php'); // Includes Login Script
global $error;

// ─── Helper: clean input ───────────────────────────────────────────────────────
function clean($data) {
    return trim(strip_tags((string)$data));
}

// ─── Validate name (letters, spaces, dots – min 2 chars) ─────────────────────
function is_valid_name($name) {
    return (strlen($name) >= 2 && preg_match('/^[a-zA-Z\s.]+$/', $name));
}

// ─── Validate mobile (10 digits, starts with 6-9) ────────────────────────────
function is_valid_mobile($mobile) {
    return preg_match('/^[6-9][0-9]{9}$/', $mobile);
}

// ─── Validate pincode (6 digits) ─────────────────────────────────────────────
function is_valid_pincode($pincode) {
    return preg_match('/^[1-9][0-9]{5}$/', $pincode);
}

// ─── Validate password strength ──────────────────────────────────────────────
function is_strong_password($password) {
    // min 8 chars, at least 1 uppercase, 1 lowercase, 1 digit, 1 special char
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
}

// ─── Email uniqueness check ───────────────────────────────────────────────────
function is_valid_email($email)
{
    global $conn;
    global $error;

    $email = mysqli_real_escape_string($conn, $email);
    $slquery = "SELECT cust_id FROM custlogin WHERE email = '$email'";
    $selectresult = mysqli_query($conn, $slquery);
    $rowcount = mysqli_num_rows($selectresult);

    if ($rowcount > 0) {
        $error = '<div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                    <strong>This email is already registered.</strong>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
        return false;
    }
    return true;
}

// ─── Password match check ─────────────────────────────────────────────────────
function is_valid_passwords($password, $cpassword)
{
    global $error;

    if ($password !== $cpassword) {
        $error = '<div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                    <strong>Passwords do not match. Please type carefully.</strong>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
        return false;
    }
    return true;
}

// ─── Insert customer into DB ──────────────────────────────────────────────────
function create_user($name, $password, $email, $mobile, $statename, $city, $address, $pincode)
{
    global $conn;

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    if ($passwordHash === false) {
        return false;
    }

    $stmt = $conn->prepare(
        'INSERT INTO custlogin (cust_name, password, email, phone_no, state, city, address, pincode)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        'ssssssss',
        $name,
        $passwordHash,
        $email,
        $mobile,
        $statename,
        $city,
        $address,
        $pincode
    );
    $created = $stmt->execute();
    $stmt->close();
    return $created;
}

// ─── Main execution ───────────────────────────────────────────────────────────
if (isset($_POST['customerregister'])) {

    $name      = clean($_POST['name']            ?? '');
    $email     = clean($_POST['email']           ?? '');
    $mobile    = clean($_POST['mobile']          ?? '');
    $state     = clean($_POST['state']           ?? '');
    $city      = clean($_POST['city']            ?? '');
    $address   = clean($_POST['address']         ?? '');
    $pincode   = clean($_POST['pincode']         ?? '');
    $password  = $_POST['password']              ?? '';
    $cpassword = $_POST['confirmpassword']       ?? '';

    $errors = [];

    // ── Field-level validation ──────────────────────────────────────────────
    if (empty($name)) {
        $errors[] = 'Customer name is required.';
    } elseif (!is_valid_name($name)) {
        $errors[] = 'Name must be at least 2 characters and contain only letters and spaces.';
    }

    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($mobile)) {
        $errors[] = 'Mobile number is required.';
    } elseif (!is_valid_mobile($mobile)) {
        $errors[] = 'Mobile number must be 10 digits and start with 6, 7, 8, or 9.';
    }

    if (empty($state)) {
        $errors[] = 'Please select your state.';
    }

    if (empty($city)) {
        $errors[] = 'Please select your city.';
    }

    if (empty($address)) {
        $errors[] = 'Address is required.';
    } elseif (strlen($address) < 5) {
        $errors[] = 'Please enter a complete address (at least 5 characters).';
    }

    if (empty($pincode)) {
        $errors[] = 'Pincode is required.';
    } elseif (!is_valid_pincode($pincode)) {
        $errors[] = 'Pincode must be a valid 6-digit number.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (!is_strong_password($password)) {
        $errors[] = 'Password must be at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a special character.';
    }

    if (empty($cpassword)) {
        $errors[] = 'Please confirm your password.';
    }

    // ── Render collected errors ─────────────────────────────────────────────
    if (!empty($errors)) {
        $errorList = implode('</li><li>', array_map('htmlspecialchars', $errors));
        $error = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Please fix the following errors:</strong>
                    <ul class="mb-0 mt-1"><li>' . $errorList . '</li></ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>';
    } else {
        // ── Cross-field validation ────────────────────────────────────────
        if (is_valid_passwords($password, $cpassword) && is_valid_email($email)) {

            // Resolve state code → name
            $stateEsc  = mysqli_real_escape_string($conn, $state);
            $q5        = "SELECT StateName FROM state WHERE StCode = '$stateEsc'";
            $res5      = mysqli_query($conn, $q5);
            $row5      = mysqli_fetch_assoc($res5);
            $statename = $row5['StateName'] ?? $state;

            if (create_user($name, $password, $email, $mobile, $statename, $city, $address, $pincode)) {
                $_SESSION['customer_login_user'] = $email;
                header("location: cbuy_crops.php");
                exit();
            } else {
                $error = '<div class="alert alert-danger alert-dismissible fade show text-center" role="alert">
                            <strong>An error occurred while creating your account. Please try again.</strong>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>';
            }
        }
    }
}
?>
