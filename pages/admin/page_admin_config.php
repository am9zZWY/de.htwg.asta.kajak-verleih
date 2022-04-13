<?php
create_header('Konfiguration');

$xml = simplexml_load_file("config.xml")
or die("Error: Cannot create object");
?>
<div class="container">
    <div class="row content">
        <div class="header-wrapper">
            <p>Aktuelle Werte:</p>
            <?php

            $config = $_SESSION['config'];
            echo $config->kajakToString($config->getKajaks()[0]);
            echo $config->kajakToString($config->getKajaks()[1]);
            echo $config->pricesToString($config->getPrice()[0]);
            echo $config->pricesToString($config->getPrice()[1]);
            echo $config->kautionToString($config->getKaution()[0]);
            echo $config->timeslotToString($config->getTimeslot()[0]);
            echo $config->timeslotToString($config->getTimeslot()[1]);
                ?>
        </div>
    </div>
</div>
</div>

