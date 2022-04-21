<?php

use JetBrains\PhpStorm\Pure;

class ConfigHelper
{
    private string $xml;

    /**
     * Constructor for Config class.
     */
    public function __construct()
    {
        /* read in from config.xml as string */
        $loaded_xml = file_get_contents('config.xml');
        if ($loaded_xml === false) {
            /* if it fails return */
            return;
        }

        /* save xml string to variable */
        $this->xml = $loaded_xml;
    }

    /**
     * Get object with amount of kajaks
     * @return array
     */
    public function getAmountKajaks(): array
    {
        $kajaks = $this->getKajaks(true);
        $amount = array();
        foreach ($kajaks as $kajak) {
            $amount[$kajak->kind] = (int)$kajak->amount;
        }
        return $amount;
    }

    /**
     * Return kajaks as JSON object or as list.
     * @param bool $asList
     * @return array
     */
    public function getKajaks(bool $asList = false): array
    {
        /* create either array or object */
        $kajaks = $asList ? array() : new stdClass();

        $xml = $this->getSimpleXMLConfig();
        if ($xml === null) {
            return $kajaks;
        }

        /* prepare list of prices in which every price is an object with some props */
        foreach ($xml->kajaks->children() as $child) {
            $kajak = new stdClass();
            $name = (string)$child->attributes()->name;
            $kajak->name = $name;

            /* get all props and put them as attributes into object */
            $props = $child->xpath('.//prop');
            foreach ($props as $prop) {
                $kajak->{(string)$prop->attributes()->name} = (string)$prop;
            }
            if ($asList) {
                $kajaks [] = $kajak;
            } else {
                $kajaks->$name = $kajak;
            }
        }

        return $kajaks;
    }

    /**
     * Get xml string as SimpleXMLElement.
     * @return SimpleXMLElement|null
     */
    private function getSimpleXMLConfig(): ?SimpleXMLElement
    {
        try {
            return new SimpleXMLElement($this->xml);
        } catch (Exception) {
            return null;
        }
    }

    public function kajakToString($kajak): string
    {
        return "Name: " . $kajak->name . "<br>" .
            "Sitze: " . $kajak->seats . "<br>" .
            "Anzahl: " . $kajak->amount . "<br>" .
            "------------------------------" . "<br>";
    }

    /**
     * Calculate price depending on timeslots and amount of kajaks.
     * @param int $amount_timeslots
     * @param int $amount_kajaks
     * @return int
     */
    public function calculatePrice(int $amount_timeslots, int $amount_kajaks): int
    {
        global $config_timeslots;

        if ($amount_timeslots === 0 || $amount_kajaks === 0) {
            return 0;
        }

        /* prepare prices */
        $prices = $this->getPrices(true);

        /* calculate all static prices e.g. prices that don't depend on a variable */
        $static_prices = array_sum(
            array_map(static function ($price) {
                return (int)$price->value;
            },
                array_filter($prices, static function ($price) {
                    return !property_exists($price, 'dependOn');
                })));

        /* calculate all prices that are per kajak */
        $perKajak_prices = array_sum(
            array_map(static function ($price) {
                return (int)$price->value;
            },
                array_filter($prices, static function ($price) {
                    return property_exists($price, 'dependOn') && $price->dependOn === 'kajak';
                })));

        /* calculate price */
        foreach ($prices as $price) {
            if (property_exists($price, 'amountTimeslots') &&
                ((int)$price->amountTimeslots === $amount_timeslots ||
                    ($price->amountTimeslots === 'all' && $amount_timeslots === count($config_timeslots)))) {
                return $amount_kajaks * ((int)$price->value + $perKajak_prices) + $static_prices;
            }
        }

        return 0;
    }

    /**
     * Return prices as JSON object like
     * ```json
     * "single": {
     *   "name": "single",
     *   "price": "5"
     * }, ...
     * ```
     * or as list like
     * ```json
     * [{
     *   "name": "single",
     *   "price": "5"
     * }, ...]
     * ```
     *
     * @param bool $asList
     * @return stdClass|array
     */
    public function getPrices(bool $asList = false): stdClass|array
    {
        /* create either array or object */
        $prices = $asList ? array() : new stdClass();

        $xml = $this->getSimpleXMLConfig();
        if ($xml === null) {
            return $prices;
        }

        /* prepare list of prices in which every price is an object with some props */
        foreach ($xml->prices->children() as $child) {
            $price = new stdClass();
            $name = (string)$child->attributes()->name;
            $price->name = $name;

            /* get all props and put them as attributes into object */
            $props = $child->xpath('.//prop');
            foreach ($props as $prop) {
                $price->{(string)$prop->attributes()->name} = (string)$prop;
            }

            /* decide between array or object */
            if ($asList) {
                $prices[] = $price;
            } else {
                $prices->$name = $price;
            }
        }
        return $prices;
    }

    public function pricesToString($prices): string
    {
        return "Timeslot: " . $prices->name . "<br>" .
            "Preis: " . $prices->price . "<br>" .
            "------------------------------" . "<br>";
    }

    public function timeslotToString($timeslots): string
    {
        return "Timeslot: " . $timeslots->name . "<br>" .
            "Start: " . $timeslots->start . "<br>" .
            "Ende: " . $timeslots->ende . "<br>" .
            "------------------------------" . "<br>";
    }

    /**
     * Get formatted timeslots.
     * @return array
     */
    public function getFormattedTimeslots(): array
    {
        return $this->formatTimeslot($this->getTimeslots());
    }

    /**
     * Formats multiple timeslots from e.g. [9:00, 13:00] to 9:00 - 13:00.
     * @param $timeslots
     * @return array
     */
    private function formatTimeslot($timeslots): array
    {
        return array_map(static function ($timeslot) {
            return date('H:i', strtotime($timeslot->start)) . " - " . date('H:i', strtotime($timeslot->end));
        }, $timeslots);
    }

    /**
     * Get all timeslots from config.
     * @param bool $asMatrix
     * @return array
     */
    #[Pure] public function getTimeslots(bool $asMatrix = false): array
    {
        $timeslots = array();
        $xml = $this->getSimpleXMLConfig();
        if ($xml === null) {
            return $timeslots;
        }

        foreach ($xml->timeslots->children() as $child) {
            $start = (string)$child->start;
            $end = (string)$child->ende;

            if ($asMatrix === true) {
                $timeslot = array();
                $timeslot[0] = $start;
                $timeslot[1] = $end;
            } else {
                $timeslot = new stdClass();
                $timeslot->start = $start;
                $timeslot->end = $end;
            }

            $timeslots[] = $timeslot;
        }
        return $timeslots;
    }

    /**
     * Returns the next max_days weekdays as a list of strings.
     * @return array<string>
     */
    public function getFormattedDays(): array
    {
        $config = $this->getDays();
        $min_days = $config->min_days;
        $max_days = $config->max_days;
        $weekdays = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");

        /* create starting date */
        $date = date_create();
        if ($date === false) {
            return [''];
        }
        /* add min_days to starting date */
        date_add($date, new DateInterval("P${min_days}D"));

        /* create array with all weekdays */
        $days = array();
        for ($i = 0; $i < $max_days; $i++) {
            $weekday = (int)$date->format('w');
            /* ignore saturday and sunday */
            if ($weekday !== 0 && $weekday !== 6) {
                $days[$i] = array($weekdays[$weekday] . ' ' . $date->format('d.m.Y'), $date->format('Y-m-d'));
            }
            date_add($date, new DateInterval("P1D"));
        }
        return $days;
    }

    /**
     * Get config for days.
     * @return stdClass
     */
    #[Pure] public function getDays(): stdClass
    {
        $day_config = new stdClass();
        $xml = $this->getSimpleXMLConfig();
        if ($xml === null) {
            return $day_config;
        }

        $days = (array)$xml->days;
        $day_config->min_days = (int)$days["min"];
        $day_config->max_days = (int)$days["max"];
        return $day_config;
    }
}
