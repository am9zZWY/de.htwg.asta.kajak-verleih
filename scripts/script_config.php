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
    private $days;
    private $timeslots;


    function __construct()
    {
        $this->xml = simplexml_load_file("config.xml");

    }

    function getKajaks()
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

    function kajakToString($kajak)
    {
        return "Name: " . $kajak->name . "<br>" .
            "Sitze: " . $kajak->seats . "<br>" .
            "Anzahl: " . $kajak->amount . "<br>" .
            "------------------------------" . "<br>";

    }

    function getPrice()
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

    function pricesToString($prices)
    {
        return "Timeslot: " . $prices->name . "<br>" .
            "Preis: " . $prices->price . "<br>" .
            "------------------------------" . "<br>";
    }


    function getKaution()
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

    function kautionToString($kautionen)
    {
        return "Kaution: " . $kautionen->value . "<br>" .
            "------------------------------" . "<br>";
    }

    function getTimeslot()
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

    function timeslotToString($timeslots)
    {
        return "Timeslot: " . $timeslots->name . "<br>" .
            "Start: " . $timeslots->start . "<br>" .
            "Ende: " . $timeslots->ende . "<br>" .
            "------------------------------" . "<br>";
    }

    function addTimeslot($timeslot)
    {
        $timeslot->setId(count($this->timeslots) + 1);
        $this->timeslots[] = $timeslot;
    }

    function setTimeslot($timeslot)
    {
        $this->timeslots[$timeslot->getId()] = $timeslot;
    }



    function formatTimeslot($formatTimeslot){
        
        $timeslots = array(array("09:00:00", "13:00:00"), array("13:00:00", "18:00:00"));

        $timeslots_formatted = array_map(static function ($array) {
            $timeslot = array_map(static function ($time) {
                return date('H:i', strtotime($time));
            }, $array);
            return implode(' - ', $timeslot);
        }, $timeslots);

    }


    function getDays()
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
