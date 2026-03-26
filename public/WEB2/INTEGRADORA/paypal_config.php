<?php
// PayPal Sandbox Credentials
define('PAYPAL_CLIENT_ID',     'YOUR_SANDBOX_CLIENT_ID');
define('PAYPAL_CLIENT_SECRET', 'YOUR_SANDBOX_CLIENT_SECRET');
define('PAYPAL_BASE_URL',      'https://api-m.sandbox.paypal.com');

/**
 * Obtiene un access token de PayPal usando las credenciales sandbox.
 * @return string|false Access token o false en caso de error.
 */
function paypal_get_access_token() {
    $ch = curl_init(PAYPAL_BASE_URL . '/v1/oauth2/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
        CURLOPT_USERPWD        => PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'Accept-Language: en_US',
        ],
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $http_code !== 200) {
        return false;
    }

    $data = json_decode($response, true);
    return isset($data['access_token']) ? $data['access_token'] : false;
}

/**
 * Crea una orden de PayPal por el monto indicado en MXN.
 * @param float $amount_mxn Monto total en pesos mexicanos.
 * @return array|false Respuesta decodificada de la API o false en error.
 */
function paypal_create_order($amount_mxn) {
    $access_token = paypal_get_access_token();
    if (!$access_token) {
        return false;
    }

    $payload = [
        'intent'         => 'CAPTURE',
        'purchase_units' => [
            [
                'amount' => [
                    'currency_code' => 'MXN',
                    'value'         => number_format((float)$amount_mxn, 2, '.', ''),
                ],
            ],
        ],
    ];

    $ch = curl_init(PAYPAL_BASE_URL . '/v2/checkout/orders');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
        ],
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return false;
    }

    return json_decode($response, true);
}

/**
 * Captura una orden de PayPal previamente aprobada.
 * @param string $order_id ID de la orden de PayPal.
 * @return array|false Respuesta decodificada de la API o false en error.
 */
function paypal_capture_order($order_id) {
    $access_token = paypal_get_access_token();
    if (!$access_token) {
        return false;
    }

    $ch = curl_init(PAYPAL_BASE_URL . '/v2/checkout/orders/' . $order_id . '/capture');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => '{}',
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token,
        ],
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if ($response === false) {
        return false;
    }

    return json_decode($response, true);
}
?>
