<?php

class Config
{
    private array $days;
    private array $timeslots;
    private SimpleXMLElement $xml;

    /**
     * Constructor for Config class
     */
    public function __construct()
    {
        $loaded_xml = simplexml_load_string(file_get_contents("config.xml"));
        if ($loaded_xml === false) {
            return;
        }
        $this->xml = $loaded_xml;
    }

    /**
     * Get list of all kajaks
     * @return array
     */
    public function getKajaks()
    {
        $kajaks = array();
        foreach ($this->xml->kajaks->children() as $child) {
            $kajak = new stdClass();
            $kajak->name = (string)$child->attributes()->name;
            $kajak->seats = (string)$child->seats;
            $kajak->amount = (string)$child->amount;
            $kajaks[] = $kajak;
        }
        return $kajaks;
    }

    public function kajakToString($kajak)
    {
        return "Name: " . $kajak->name . "<br>" .
            "Sitze: " . $kajak->seats . "<br>" .
            "Anzahl: " . $kajak->amount . "<br>" .
            "------------------------------" . "<br>";
    }

    public function getPrices(): array
    {
        $prices = array();
        foreach ($this->xml->prices->children() as $child) {
            $price = new stdClass();
            $price->name = (string)$child->attributes()->name;
            $price->price = (string)$child;
            $prices[] = $price;
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

    public function getTimeslot(): array
    {
        $timeslots = array();
        foreach ($this->xml->timeslots->children() as $child) {
            $timeslot = new stdClass();
            $timeslot->name = (string)$child->attributes()->name;
            $timeslot->start = (string)$child->start;
            $timeslot->ende = (string)$child->ende;
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
     * @param $timeslot
     * @return void
     * @deprecated
     */
    public function addTimeslot($timeslot): void
    {
        $timeslot->setId(count($this->timeslots) + 1);
        $this->timeslots[] = $timeslot;
    }

    /**
     * @param $timeslot
     * @return void
     * @deprecated
     */
    public function setTimeslot($timeslot): void
    {
        $this->timeslots[$timeslot->getId()] = $timeslot;
    }

    /**
     * Formats multiple timeslots from e.g. [9:00, 13:00] to 9:00 - 13:00
     * @param $timeslots
     * @return array
     */
    public function formatTimeslot($timeslots): array
    {
        return array_map(static function ($array) {
            $timeslot = array_map(static function ($time) {
                return date('H:i', strtotime($time));
            }, $array);
            return implode(' - ', $timeslot);
        }, $timeslots);

    }

    /**
     * Get all weekdays
     * @return array
     */
    public function getDays(): array
    {
        $days = array();
        foreach ($this->xml->days->children() as $child) {
            $day = new Day();
            $day->setId($child->attributes()->id);
            $day->setName($child->name);
            $days[] = $day;
        }
        return $days;
    }
}
