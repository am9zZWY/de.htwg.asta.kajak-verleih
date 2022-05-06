<?phpclass Config{    /**     * @var string     */    private $xml;    /**     * Constructor for Config class.     */    public function __construct()    {        /* read in from config.xml as string */        $loaded_xml = file_get_contents('config.xml');        if ($loaded_xml === FALSE) {            /* if it fails return */            return;        }        /* save xml string to variable */        $this->xml = $loaded_xml;    }    /**     * Get all kajak kinds.     *     * @return array     */    public function getKajakKinds(): array    {        $kajaks = $this->getKajaks();        return array_unique(array_map(static function ($kajak) {            return $kajak->kind;        }, $kajaks));    }    /**     * Get list of kajaks.     *     * @return array     */    public function getKajaks(): array    {        $kajaks = array();        $xml = $this->getSimpleXMLConfig();        if ($xml === null) {            return $kajaks;        }        /* prepare list of prices in which every price is an object with some props */        foreach ($xml->kajaks->children() as $child) {            $kajak = new stdClass();            $name = (string)$child->attributes()->name;            $kajak->name = $name;            /* get all props and put them as attributes into object */            $props = $child->xpath('.//prop');            foreach ($props as $prop) {                $kajak->{(string)$prop->attributes()->name} = (string)$prop;            }            $kajaks [] = $kajak;        }        return $kajaks;    }    /**     * Get xml string as SimpleXMLElement.     *     * @return SimpleXMLElement|null     */    private function getSimpleXMLConfig(): ?SimpleXMLElement    {        try {            return new SimpleXMLElement($this->xml);        } catch (Exception $e) {            return null;        }    }    /**     * Get seats per kajak kind.     *     * @return array     */    public function getSeatsPerKajak(): array    {        return array_reduce($this->getKajaks(), static function ($carry, $kajak) {            $carry[$kajak->kind] = (int)$kajak->seats;            return $carry;        }, array());    }    /**     * Return prices as list like     * ```json     * [{     *   "name": "single",     *   "price": "5"     * }, ...]     * ```     *     * @return array     */    public function getPrices(): array    {        $prices = array();        $xml = $this->getSimpleXMLConfig();        if ($xml === null) {            return $prices;        }        /* prepare list of prices in which every price is an object with some props */        foreach ($xml->prices->children() as $child) {            $price = array();            $name = (string)$child->attributes()->name;            $price["name"] = $name;            /* get all props and put them as attributes into object */            $props = $child->xpath('.//prop');            foreach ($props as $prop) {                $attrs = $prop->attributes();                $prop_name = (string)$attrs->name;                if ($prop_name === 'dependOn') {                    if (!array_key_exists('dependOn', $price)) {                        $price["dependOn"] = array();                    }                    $dependency = array(                        "amount" => (string)$attrs->amount,                        "name" => (string)$prop                    );                    /* add dependOn to price */                    $price["dependOn"][] = $dependency;                } else {                    $price[$prop_name] = (string)$prop;                }            }            /* decide between array or object */            $prices[] = $price;        }        return $prices;    }    /**     * Get formatted timeslots.     *     * @return array     */    public function getFormattedTimeslots(): array    {        return array_map(function ($timeslot) {            return $this->formatTimeslot($timeslot);        }, $this->getTimeslots());    }    /**     * Formats multiple timeslots from e.g. [9:00, 13:00] to 9:00 - 13:00.     *     * @param $timeslot     * @return string     */    public function formatTimeslot($timeslot): string    {        return date('H:i', strtotime($timeslot["start"])) . " - " . date('H:i', strtotime($timeslot["end"]));    }    /**     * Get all timeslots from config.     * @return array     */    public function getTimeslots(): array    {        $timeslots = array();        $xml = $this->getSimpleXMLConfig();        if ($xml === null) {            return $timeslots;        }        foreach ($xml->timeslots->children() as $child) {            $start = (string)$child->start;            $end = (string)$child->end;            $name = (string)$child->attributes()->name;            $timeslot = array();            $timeslot["start"] = $start;            $timeslot[0] = $start;            $timeslot["end"] = $end;            $timeslot[1] = $end;            $timeslot["name"] = $name;            $timeslots[] = $timeslot;        }        return $timeslots;    }    /**     * Returns the next max_days weekdays as a list of strings.     *     * @return array<string>     */    public function getFormattedDays(): array    {        $config = $this->getDays();        $min_days = $config["min_days"];        $max_days = $config["max_days"];        $weekdays = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");        /* create starting date */        $date = date_create();        if ($date === FALSE) {            return [''];        }        /* add min_days to starting date */        date_add($date, new DateInterval("P${min_days}D"));        /* create array with all weekdays */        $days = array();        for ($i = 0; $i < $max_days; $i++) {            $weekday = (int)$date->format('w');            /* ignore saturday and sunday */            if ($weekday !== 0 && $weekday !== 6) {                $days[$i] = array($weekdays[$weekday] . ' ' . $date->format('d.m.Y'), $date->format('Y-m-d'));            }            date_add($date, new DateInterval("P1D"));        }        return $days;    }    /**     * Get config for days.     *     * @return array     */    public function getDays(): array    {        $day_config = array();        $xml = $this->getSimpleXMLConfig();        if ($xml === null) {            return $day_config;        }        $days = (array)$xml->days;        $day_config["min_days"] = (int)$days["min"];        $day_config["max_days"] = (int)$days["max"];        return $day_config;    }}