<?php
global $config, $URL;

$connection = connect_to_database();

$PARSED_URL = parse_url($URL, PHP_URL_QUERY);
if ($PARSED_URL !== NULL) {
    parse_str($PARSED_URL, $params);
}

/* calculate prices and return value */
if (isset($params['payload'])) {
    $decoded_payload = json_decode(base64_decode(clean_string($params['payload']), $strict = FALSE), TRUE);
    if ($decoded_payload === NULL) {
        header('400 Bad Request', TRUE, 400);
        exit(1);
    }
    $token = clean_string($decoded_payload[$_SESSION['token_field'] ?? ''] ?? '');
    if (!$token || $token !== $_SESSION['token']) {
        header('400 Bad Request', TRUE, 400);
        exit(1);
    }

    if (isset($params['price'])) {
        $timeslots = $decoded_payload['timeslots'];
        $amount_kajaks_per_kind = $decoded_payload['amount_kajaks'];
        echo calculate_price($connection, $timeslots, $amount_kajaks_per_kind) . '€';
    }
} else {
    header('400 Bad Request', TRUE, 400);
    exit(1);
}