<?php

use JetBrains\PhpStorm\Pure;


class Config
{
    private string $xml;

    /**
     * Constructor for Config class.
     */
    public function __construct()
    {
        $loaded_xml = file_get_contents('config.xml');
        if ($loaded_xml === false) {
            return;
        }

        $this->xml = $loaded_xml;
    }

    private function getSimpleXMLConfig(): ?SimpleXMLElement
    {
        try {
            return new SimpleXMLElement($this->xml);
        } catch (Exception) {
            return null;
        }
    }

    /**
     * Get list of all kajaks.
     * @param bool $asList
     * @return array
     */
    public function getKajaks(bool $asList = false): array
    {
        $kajaks = $asList ? array() : new stdClass();
        $xml = $this->getSimpleXMLConfig();
        if ($xml === null) {
            return $kajaks;
        }

        foreach ($xml->kajaks->children() as $child) {
            $kajak = new stdClass();
            $name = (string)$child->attributes()->name;
            $kajak->name = $name;
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
     * Get object with amount of kajaks
     * @return array
     */
    public function getAmountKajaks(): array
    {
        $kajaks = $this->getKajaks(true);
        $amount = array();
        foreach ($kajaks as $kajak) {
            $amount[$kajak->intName] = (int)$kajak->amount;
        }
        return $amount;
    }

    public function kajakToString($kajak): string
    {
        return "Name: " . $kajak->name . "<br>" .
            "Sitze: " . $kajak->seats . "<br>" .
            "Anzahl: " . $kajak->amount . "<br>" .
            "------------------------------" . "<br>";
    }

    /**
     * Return prices as JSON like
     * "single": {
     *   "name": "single",
     *   "price": "5"
     * }, ...
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

        foreach ($xml->prices->children() as $child) {
            $price = new stdClass();
            $name = (string)$child->attributes()->name;
            $price->name = $name;
            $price->price = (string)$child;

            /* decide between array or object */
            if ($asList) {
                $prices[] = $price;
            } else {
                $prices->$name = $price;
            }
        }
        return $prices;
    }

    /**
     * Calculate price depending on timeslots and amount of kajaks
     * @param array $timeslots
     * @param array $kajaks
     * @return int
     */
    public function calculatePrice(array $timeslots, array $kajaks): int
    {
        /* prepare amount/factor */
        $amount_kajaks = count($kajaks[0]) + count($kajaks[1]);

        /* prepare prices */
        $prices = $this->getPrices();
        $price_single = $prices["single"];
        $price_double = $prices["double"];
        $price_kaution = $prices["kaution"];

        /* if one timeslot */
        if (count($timeslots) === 1) {
            return ($amount_kajaks * $price_single) + $price_kaution;
        }

        /* if two timeslots */
        if (count($timeslots) === 2) {
            return ($amount_kajaks * $price_double) + $price_kaution;
        }

        return 0;
    }

    public function pricesToString($prices): string
    {
        return "Timeslot: " . $prices->name . "<br>" .
            "Preis: " . $prices->price . "<br>" .
            "------------------------------" . "<br>";
    }

    /**
     * Get all timeslots from config.
     * @return array
     */
    #[Pure] public function getTimeslot(): array
    {
        $timeslots = array();
        $xml = $this->getSimpleXMLConfig();
        if ($xml === null) {
            return $timeslots;
        }

        foreach ($xml->timeslots->children() as $child) {
            $timeslot = new stdClass();
            $timeslot->start = (string)$child->start;
            $timeslot->end = (string)$child->ende;
            $timeslots[] = $timeslot;
        }
        return $timeslots;
    }

    public function timeslotToString($timeslots): string
    {
        return "Timeslot: " . $timeslots->name . "<br>" .
            "Start: " . $timeslots->start . "<br>" .
            "Ende: " . $timeslots->ende . "<br>" .
            "------------------------------" . "<br>";
    }

    /**
     * Formats multiple timeslots from e.g. [9:00, 13:00] to 9:00 - 13:00
     * @param $timeslots
     * @return array
     */
    public function formatTimeslot($timeslots): array
    {
        return array_map(static function ($timeslot) {
            return date('H:i', strtotime($timeslot->start)) . " - " . date('H:i', strtotime($timeslot->end));
        }, $timeslots);
    }

    /**
     * Get formatted timeslots
     * @return array
     */
    public function getFormattedTimeslots(): array
    {
        return $this->formatTimeslot($this->getTimeslot());
    }

    /**
     * Get config for days.
     * @return stdClass
     */
    #[Pure] public function getDayConfig(): stdClass
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

    /**
     * Returns the next max_days weekdays in a string.
     * @return array<string>
     */
    public function getDays(): array
    {
        $config = $this->getDayConfig();
        $min_days = $config->min_days;
        $max_days = $config->max_days;
        $weekdays = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");

        /* Create starting date */
        $date = date_create();
        if ($date === false) {
            return [''];
        }
        date_add($date, new DateInterval("P${min_days}D"));

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
}
