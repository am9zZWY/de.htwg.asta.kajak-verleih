<?php
global $config, $URL;

$PARSED_URL = parse_url($URL, PHP_URL_QUERY);
if ($PARSED_URL !== NULL) {
    parse_str($PARSED_URL, $params);
}

/* calculate prices and return value */
if (isset($params['price'], $params['payload_price'])) {
    $decoded_payload = json_decode(base64_decode(clean_string($params['payload_price']), $strict = false), true);
    $timeslots = $decoded_payload['timeslots'];
    $amount_kajaks_per_kind = $decoded_payload['amount_kajaks'];
    echo $config->calculatePrice($timeslots, $amount_kajaks_per_kind);
}
