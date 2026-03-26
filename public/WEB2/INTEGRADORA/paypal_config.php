<?php
/* paypal_config.php - Programacion Web 2 - Mtra. Patricia Torres
   Rafael Avila Sanchez - CETI 8F - 22300193
   Comunicacion con PayPal via file_get_contents (SSL verify deshabilitado)
   con fallback a cURL. Compatible con PHP 5.5. */

/* ── Cargar .env ──────────────────────────────────────── */
function load_env($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (strpos($line, '=') !== false) {
            $parts = explode('=', $line, 2);
            $key   = trim($parts[0]);
            $value = trim($parts[1]);
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

load_env(dirname(__FILE__) . '/.env');

define('PAYPAL_CLIENT_ID',     getenv('PAYPAL_CLIENT_ID')     !== false ? getenv('PAYPAL_CLIENT_ID')     : '');
define('PAYPAL_CLIENT_SECRET', getenv('PAYPAL_CLIENT_SECRET') !== false ? getenv('PAYPAL_CLIENT_SECRET') : '');
define('PAYPAL_MODE',          getenv('PAYPAL_MODE')          !== false ? getenv('PAYPAL_MODE')          : 'sandbox');
define('PAYPAL_BASE_URL',      PAYPAL_MODE === 'live' ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com');

/* ── Helper: HTTP POST ────────────────────────────────────
   Intenta primero file_get_contents (sin SSL verify, funciona
   en el servidor CETI). Si allow_url_fopen esta desactivado,
   cae a cURL con SSL_VERIFYPEER=false.
   Devuelve el body como string, o false en error.
   ────────────────────────────────────────────────────── */
function _pp_http_post($url, $headers, $body) {
    /* -- Opcion A: file_get_contents (recomendada para CETI) -- */
    if (ini_get('allow_url_fopen')) {
        $ctx = stream_context_create(array(
            'http' => array(
                'method'        => 'POST',
                'header'        => implode("\r\n", $headers),
                'content'       => $body,
                'ignore_errors' => true,
                'timeout'       => 15,
            ),
            'ssl' => array(
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ),
        ));
        $response = @file_get_contents($url, false, $ctx);
        if ($response !== false) {
            return $response;
        }
    }

    /* -- Opcion B: cURL con SSL deshabilitado -- */
    if (!function_exists('curl_init')) {
        return false;
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ));
    $response = curl_exec($ch);
    curl_close($ch);
    return ($response !== false) ? $response : false;
}

/* ── 1) Obtener Access Token ────────────────────────── */
function paypal_get_access_token() {
    $credentials = base64_encode(PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET);
    $headers = array(
        'Authorization: Basic ' . $credentials,
        'Accept: application/json',
        'Accept-Language: en_US',
        'Content-Type: application/x-www-form-urlencoded',
    );
    $response = _pp_http_post(
        PAYPAL_BASE_URL . '/v1/oauth2/token',
        $headers,
        'grant_type=client_credentials'
    );
    if ($response === false) {
        return false;
    }
    $data = json_decode($response, true);
    return (isset($data['access_token'])) ? $data['access_token'] : false;
}

/* ── 2) Crear orden ─────────────────────────────────── */
function paypal_create_order($amount_mxn) {
    $access_token = paypal_get_access_token();
    if (!$access_token) {
        return false;
    }
    $payload = json_encode(array(
        'intent'         => 'CAPTURE',
        'purchase_units' => array(
            array(
                'amount' => array(
                    'currency_code' => 'MXN',
                    'value'         => number_format((float)$amount_mxn, 2, '.', ''),
                ),
            ),
        ),
    ));
    $headers = array(
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json',
    );
    $response = _pp_http_post(
        PAYPAL_BASE_URL . '/v2/checkout/orders',
        $headers,
        $payload
    );
    if ($response === false) {
        return false;
    }
    return json_decode($response, true);
}

/* ── 3) Capturar orden ──────────────────────────────── */
function paypal_capture_order($order_id) {
    $access_token = paypal_get_access_token();
    if (!$access_token) {
        return false;
    }
    $headers = array(
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json',
    );
    $response = _pp_http_post(
        PAYPAL_BASE_URL . '/v2/checkout/orders/' . urlencode($order_id) . '/capture',
        $headers,
        '{}'
    );
    if ($response === false) {
        return false;
    }
    return json_decode($response, true);
}
?>
