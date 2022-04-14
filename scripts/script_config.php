<?php

class Config
{
    // construtor -> auslesen aus config.xml
    // getDays -> liefert die Wochentage als Array
    // getTimeslots -> liefert
    // addTimeslot -> fügt ein neues Timeslot hinzu
    // setTimeslot -> setzt ein Timeslot
    // ...
    // saveAsXML -> speichert die Konfiguration als XML in config.xml

    private $config;
    private array $days;
    private array $timeslots;
    private SimpleXMLElement $xml;


    public function __construct()
    {
        $loaded_xml = simplexml_load_string(file_get_contents("config.xml"));
        if ($loaded_xml === false) {
            return;
        }
        $this->xml = $loaded_xml;
    }

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

    public function getPrice(): array
    {
        $prices = array();
        foreach ($this->xml->prices->children() as $child) {
            $price = new stdClass();
            $price->name = (string)$child->attributes()->name;
            $price->price = (string)$child;
            $prices[] =$price;

        }
        return $prices;
    }

    public function pricesToString($prices): string
    {
        return "Timeslot: " . $prices->name . "<br>" .
            "Preis: " . $prices->price . "<br>" .
            "------------------------------" . "<br>";
    }


    public function getKaution(): array
    {
        $kaution = array();
        foreach ($this->xml->kautionen->children() as $child) {
            $kaution = new stdClass();
            $kaution->name = (string)$child->attributes()->name;
            $kaution->value= (string)$child;
            $kautionen[] =$kaution;

        }
        return $kautionen;
    }

    public function kautionToString($kautionen): string
    {
        return "Kaution: " . $kautionen->value . "<br>" .
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
            $timeslots[] =$timeslot;

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

    public function addTimeslot($timeslot): void
    {
        $timeslot->setId(count($this->timeslots) + 1);
        $this->timeslots[] = $timeslot;
    }

    public function setTimeslot($timeslot): void
    {
        $this->timeslots[$timeslot->getId()] = $timeslot;
    }



    public function formatTimeslot($formatTimeslot): void
    {
        
        $timeslots = array(array("09:00:00", "13:00:00"), array("13:00:00", "18:00:00"));

        $timeslots_formatted = array_map(static function ($array) {
            $timeslot = array_map(static function ($time) {
                return date('H:i', strtotime($time));
            }, $array);
            return implode(' - ', $timeslot);
        }, $timeslots);

    }


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
