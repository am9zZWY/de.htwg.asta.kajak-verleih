<?php
global $config, $URL;

$conn = connect_to_database();

$PARSED_URL = parse_url($URL, PHP_URL_QUERY);
if ($PARSED_URL !== NULL) {
    parse_str($PARSED_URL, $params);
}

/* calculate prices and return value */
if (isset($params['price'], $params['payload_price'])) {
    $decoded_payload = json_decode(base64_decode(clean_string($params['payload_price']), $strict = FALSE), TRUE);
    $timeslots = $decoded_payload['timeslots'];
    $amount_kajaks_per_kind = $decoded_payload['amount_kajaks'];
    echo calculate_price($conn, $timeslots, $amount_kajaks_per_kind);
} elseif (isset($params['available'], $params['payload_available'])) {
    $decoded_payload = json_decode(base64_decode(clean_string($params['payload_available']), $strict = FALSE), TRUE);
    $date = $decoded_payload['date'];
    $kajak_kinds = $decoded_payload['$kajak_kinds'];
    $kajak_names = get_all_available_kajaks($conn, $date, [], $kajak_kinds, []);
    return count($kajak_names) > 0;
}