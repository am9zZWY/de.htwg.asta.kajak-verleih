<?php
global $config, $URL;

$PARSED_URL = parse_url($URL, PHP_URL_QUERY);
if ($PARSED_URL !== NULL) {
    parse_str($PARSED_URL, $params);
}

/* calculate prices and return value */
if (isset($params['amount_timeslots'], $params['amount_kajaks'], $params['price'])) {
    $amount_timeslots = (int)clean_string($params['amount_timeslots']);
    $amount_kajaks = (int)clean_string($params['amount_kajaks']);
    echo $config->calculatePrice($amount_timeslots, $amount_kajaks);
}
