<?php

function sslCreatePayment(float $amount, string $refCode, string $customerEmail, string $customerMobile): array {
    $storeId = SSL_STORE_ID;
    $password = SSL_PASSWORD;
    $mode = SSL_MODE === 'sandbox' ? '1' : '0';
    
    $url = $mode === '1' 
        ? 'https://sandbox.sslcommerz.com/gwprocess/validator.php'
        : 'https://securepay.sslcommerz.com/gwprocess/validator.php';
    
    $postData = [
        'store_id' => $storeId,
        'store_passwd' => $password,
        'total_amount' => $amount,
        'currency' => 'BDT',
        'tran_id' => $refCode,
        'success_url' => SITE_URL . '/payment_success.php',
        'fail_url' => SITE_URL . '/payment_fail.php',
        'cancel_url' => SITE_URL . '/payment_cancel.php',
        'emi_installment' => '0',
        'cus_name' => '',
        'cus_email' => $customerEmail,
        'cus_phone' => $customerMobile,
        'product_category' => 'Service Booking',
        'product_profile' => 'general',
        'shipping_method' => 'no',
        'num_of_item' => '1',
        'product_name' => 'Service Booking',
        'product_amount' => $amount,
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if (isset($result['GatewayPageURL']) && $result['GatewayPageURL']) {
        return ['status' => 'success', 'url' => $result['GatewayPageURL']];
    }
    
    return ['status' => 'error', 'message' => $result['failedreason'] ?? 'Payment initiation failed'];
}

function sslVerifyPayment(string $refCode): array {
    $storeId = SSL_STORE_ID;
    $password = SSL_PASSWORD;
    $mode = SSL_MODE === 'sandbox' ? '1' : '0';
    
    $url = $mode === '1'
        ? 'https://sandbox.sslcommerz.com/validator/api/verifier.php'
        : 'https://securepay.sslcommerz.com/validator/api/verifier.php';
    
    $postData = [
        'store_id' => $storeId,
        'store_passwd' => $password,
        'tran_id' => $refCode,
        'format' => 'json',
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $result = json_decode($response, true);
    
    if ($result['status'] === 'VALID' && $result['tran_id'] === $refCode) {
        return ['status' => 'valid', 'verified' => true];
    }
    
    return ['status' => 'invalid', 'verified' => false];
}