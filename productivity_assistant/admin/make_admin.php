<?php

require_once "../config/db.php";

// ======================================
// ADMIN LOGIN DETAILS
// ======================================

$new_email = "YOUR_ADMIN_EMAIL@gmail.com";
$new_password = "YOUR_NEW_PASSWORD";

// ======================================
// SECURE PASSWORD
// ======================================

$password_hash = password_hash(
    $new_password,
    PASSWORD_DEFAULT
);

// ======================================
// FIND USER BY EMAIL
// ======================================

$stmt = $conn->prepare(
    "UPDATE users
     SET password = ?,
         subscription_status = 'active',
         plan = 'admin'
     WHERE email = ?"
);

$stmt->bind_param(
    "ss",
    $password_hash,
    $new_email
);

if ($stmt->execute()) {

    if ($stmt->affected_rows > 0) {

        echo "ADMIN ACCOUNT UPDATED SUCCESSFULLY.";

    } else {

        echo "USER NOT FOUND OR ALREADY UPDATED.";

    }

} else {

    echo "ERROR: " . $stmt->error;

}

$stmt->close();
$conn->close();

?>