<?php

// =====================================================
// CHAT API - PHP → FASTAPI
// =====================================================

header("Content-Type: application/json");


// =====================================================
// GET JSON DATA
// =====================================================

$input = file_get_contents("php://input");

$data = json_decode($input, true);


// =====================================================
// VALIDATE REQUEST
// =====================================================

if (
    !$data ||
    !isset($data["message"]) ||
    trim($data["message"]) === "" ||
    !isset($data["user_id"]) ||
    !is_numeric($data["user_id"])
) {

    echo json_encode([
        "status" => "error",
        "reply" => "Invalid message or user ID."
    ]);

    exit;
}


$message = trim($data["message"]);

$user_id = (int) $data["user_id"];


// =====================================================
// FASTAPI URL
// =====================================================

$fastapi_url = "http://127.0.0.1:8000/chat";


// =====================================================
// DATA FOR FASTAPI
// =====================================================

$request_data = json_encode([
    "message" => $message,
    "user_id" => $user_id
]);


// =====================================================
// CURL
// =====================================================

$ch = curl_init($fastapi_url);


curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_POSTFIELDS, $request_data);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);


// =====================================================
// TIMEOUT
// =====================================================

curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

curl_setopt($ch, CURLOPT_TIMEOUT, 30);


// =====================================================
// SEND REQUEST
// =====================================================

$response = curl_exec($ch);


// =====================================================
// CURL ERROR
// =====================================================

if ($response === false) {

    $curl_error = curl_error($ch);

    curl_close($ch);

    echo json_encode([
        "status" => "error",
        "reply" => "Unable to connect to AI service: " . $curl_error,
        "error" => $curl_error
    ]);

    exit;
}


// =====================================================
// HTTP STATUS
// =====================================================

$http_code = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);


curl_close($ch);


// =====================================================
// FASTAPI RESPONSE
// =====================================================

$ai_response = json_decode(
    $response,
    true
);


// =====================================================
// INVALID RESPONSE
// =====================================================

if (!$ai_response) {

    echo json_encode([
        "status" => "error",
        "reply" => "Invalid response from AI service.",
        "http_code" => $http_code
    ]);

    exit;
}


// =====================================================
// RETURN TO JAVASCRIPT
// =====================================================

echo json_encode([
    "status" => $ai_response["status"] ?? "success",
    "reply" => $ai_response["reply"]
        ?? $ai_response["message"]
        ?? "No response received."
]);

?>