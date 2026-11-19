<?php
require_once('../sql.php');

$error = '';

function farmer_registration_value($key, $trim = true)
{
    $value = isset($_POST[$key]) && is_string($_POST[$key]) ? $_POST[$key] : '';
    return $trim ? trim($value) : $value;
}

function is_valid_name($name)
{
    return strlen($name) >= 2 && preg_match('/^[a-zA-Z\s.]+$/', $name) === 1;
}

function is_valid_mobile($mobile)
{
    return preg_match('/^[6-9][0-9]{9}$/', $mobile) === 1;
}

function is_strong_password($password)
{
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password) === 1;
}

function is_valid_age($dob)
{
    $birthDate = DateTime::createFromFormat('!Y-m-d', $dob);
    $dateErrors = DateTime::getLastErrors();
    if (!$birthDate ||
        ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0)) ||
        $birthDate->format('Y-m-d') !== $dob) {
        return false;
    }

    $today = new DateTime('today');
    return $birthDate <= $today && $birthDate->diff($today)->y >= 18;
}

function farmer_email_exists($conn, $email, &$queryFailed)
{
    $queryFailed = false;
    $stmt = $conn->prepare('SELECT farmer_id FROM farmerlogin WHERE email = ? LIMIT 1');
    if (!$stmt) {
        $queryFailed = true;
        return false;
    }

    $stmt->bind_param('s', $email);
    if (!$stmt->execute()) {
        $queryFailed = true;
        $stmt->close();
        return false;
    }

    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

function farmer_state_name($conn, $stateCode, &$queryFailed)
{
    $queryFailed = false;
    $stmt = $conn->prepare('SELECT StateName FROM state WHERE StCode = ? LIMIT 1');
    if (!$stmt) {
        $queryFailed = true;
        return null;
    }

    $stmt->bind_param('i', $stateCode);
    if (!$stmt->execute()) {
        $queryFailed = true;
        $stmt->close();
        return null;
    }

    $stmt->bind_result($stateName);
    $found = $stmt->fetch();
    $stmt->close();
    return $found ? $stateName : null;
}

function farmer_district_matches_state($conn, $stateCode, $district, &$queryFailed)
{
    $queryFailed = false;
    $stmt = $conn->prepare('SELECT DistCode FROM district WHERE StCode = ? AND DistrictName = ? LIMIT 1');
    if (!$stmt) {
        $queryFailed = true;
        return false;
    }

    $stmt->bind_param('is', $stateCode, $district);
    if (!$stmt->execute()) {
        $queryFailed = true;
        $stmt->close();
        return false;
    }

    $stmt->store_result();
    $matches = $stmt->num_rows > 0;
    $stmt->close();
    return $matches;
}

function create_farmer($conn, $name, $password, $email, $mobile, $gender, $dob, $stateName, $district, $city)
{
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    if ($passwordHash === false) {
        return false;
    }

    $stmt = $conn->prepare(
        'INSERT INTO farmerlogin (farmer_name, password, email, phone_no, F_gender, F_birthday, F_State, F_District, F_Location)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        'sssssssss',
        $name,
        $passwordHash,
        $email,
        $mobile,
        $gender,
        $dob,
        $stateName,
        $district,
        $city
    );
    $created = $stmt->execute();
    $stmt->close();
    return $created;
}

if (isset($_POST['farmerregister'])) {
    $name = farmer_registration_value('name');
    $email = strtolower(farmer_registration_value('email'));
    $mobile = farmer_registration_value('mobile');
    $gender = farmer_registration_value('gender');
    $dob = farmer_registration_value('dob');
    $state = farmer_registration_value('state');
    $district = farmer_registration_value('district');
    $city = farmer_registration_value('city');
    $password = farmer_registration_value('password', false);
    $confirmPassword = farmer_registration_value('confirmpassword', false);
    $errors = array();

    if ($name === '') {
        $errors[] = 'Farmer name is required.';
    } elseif (!is_valid_name($name)) {
        $errors[] = 'Name must be at least 2 characters and contain only letters and spaces.';
    }

    if ($email === '') {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($email) > 255) {
        $errors[] = 'Email address is too long.';
    }

    if ($mobile === '') {
        $errors[] = 'Mobile number is required.';
    } elseif (!is_valid_mobile($mobile)) {
        $errors[] = 'Mobile number must be 10 digits and start with 6, 7, 8, or 9.';
    }

    if (!in_array($gender, array('Male', 'Female'), true)) {
        $errors[] = 'Please select a valid gender.';
    }
    if ($dob !== '' && !is_valid_age($dob)) {
        $errors[] = 'Date of birth must be valid and you must be at least 18 years old.';
    }
    if ($state === '' || !ctype_digit($state)) {
        $errors[] = 'Please select your state.';
    }
    if ($district === '') {
        $errors[] = 'Please select your district.';
    }
    if ($city === '') {
        $errors[] = 'City / village name is required.';
    }

    if ($password === '') {
        $errors[] = 'Password is required.';
    } elseif (!is_strong_password($password)) {
        $errors[] = 'Password must be at least 8 characters and include an uppercase letter, a lowercase letter, a number, and a special character.';
    }
    if ($confirmPassword === '') {
        $errors[] = 'Please confirm your password.';
    } elseif ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match. Please type carefully.';
    }

    if (!$conn) {
        $errors[] = 'The database is unavailable. Please try again later.';
    }

    if (!$errors && $email !== '') {
        $queryFailed = false;
        $emailExists = farmer_email_exists($conn, $email, $queryFailed);
        if ($queryFailed) {
            $errors[] = 'Unable to validate your email right now. Please try again.';
        } elseif ($emailExists) {
            $errors[] = 'This email is already registered.';
        }
    }

    $stateCode = $state !== '' && ctype_digit($state) ? (int)$state : 0;
    if (!$errors) {
        $queryFailed = false;
        $stateName = farmer_state_name($conn, $stateCode, $queryFailed);
        if ($queryFailed) {
            $errors[] = 'Unable to validate your state right now. Please try again.';
        } elseif ($stateName === null) {
            $errors[] = 'Please select a valid state.';
        }
    }

    if (!$errors) {
        $queryFailed = false;
        if (!farmer_district_matches_state($conn, $stateCode, $district, $queryFailed)) {
            $errors[] = $queryFailed
                ? 'Unable to validate your district right now. Please try again.'
                : 'Please select a valid district for the selected state.';
        }
    }

    if (!$errors && !create_farmer(
        $conn,
        $name,
        $password,
        $email,
        $mobile,
        $gender,
        $dob,
        $stateName,
        $district,
        $city
    )) {
        $errors[] = 'An error occurred while creating your account. Please try again.';
    }

    if (!$errors) {
        header('Location: flogin.php?registered=1');
        exit();
    }

    $items = array_map(function ($message) {
        return '<li>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</li>';
    }, $errors);
    $error = '<div class="alert alert-danger alert-dismissible fade show" role="alert">'
        . '<strong>Please fix the following errors:</strong><ul class="mb-0 mt-1">'
        . implode('', $items)
        . '</ul><button type="button" class="close" data-dismiss="alert" aria-label="Close">'
        . '<span aria-hidden="true">&times;</span></button></div>';
}
?>
