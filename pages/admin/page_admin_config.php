<?php
echo create_header('Konfiguration');
global $config;
?>
<div class="container">
    <div class="row content">
        <div class="content-wrapper">
            <h4>Aktuelle Konfiguration</h4>
            <ul>
                <!-- PRICES -->
                <li>
                    <strong>Preise:</strong>
                    <ul>
                        <?php
                        foreach ($config->getPrices() as $price) {
                            ?>
                            <li>
                                <?php echo $price->description ?>: <?php echo $price->value ?>
                            </li>
                            <?php
                        }
                        ?>
                    </ul>
                </li>

                <!-- KAJAKS -->
                <li>
                    <strong>Kajaks:</strong>
                    <ul>
                        <?php
                        foreach ($config->getKajaks(true) as $kajak) {
                            ?>
                            <li>
                                <?php echo $kajak->name ?>:
                                <ul>
                                    <li>
                                        Sitze: <?php echo $kajak->seats ?>
                                    </li>
                                    <li>
                                        Anzahl: <?php echo $kajak->amount ?>
                                    </li>
                                </ul>
                            </li>
                        <?php } ?>
                    </ul>
                </li>

                <!-- TIMESLOTS -->
                <li>
                    <strong>Timeslots:</strong>
                    <ul>
                        <?php
                        foreach ($config->getTimeslots() as $timeslot) {
                            ?>
                            <li>
                                <?php echo $timeslot->name ?>: <?php echo $config->formatTimeslot($timeslot) ?>
                            </li>
                        <?php } ?>
                    </ul>
                </li>

                <!-- UNSORTIERT -->
                <li>
                    <strong>Unsortiert:</strong>
                    <ul>
                        <li>
                            <?php $days = $config->getDays(); ?>
                            Reservierungszeitraum: <?php echo $days->min_days . ' - ' . $days->max_days ?> Tage
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>


