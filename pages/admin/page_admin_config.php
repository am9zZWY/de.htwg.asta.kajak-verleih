<?php
echo create_header('Konfiguration');
global $config;
?>
<div class="container">
    <div class="row content">
        <div class="content-wrapper">
            <p>Aktuelle Werte:</p>
            <ul>
                <?php
                echo "<li><strong>" . "Preise:" . "</strong><br>";
                foreach ($config->getPrices(true) as $price) {
                    echo $price->description . ': ' . $price->value . ' €' . '<br>';
                }
                echo '</li><br>';

                foreach ($config->getKajaks(true) as $kajak) {
                    echo '<li>' . "<strong>" . $kajak->name . ': ' . "</strong>" . "<br>" . "Sitze: " . $kajak->seats . "<br>" . "Anzahl: " . $kajak->amount . '</li>';
                }
                echo '<br>';

                foreach ($config->getTimeslots() as $timeslot) {
                    echo '<li><strong>' . $timeslot->name . "</strong>" . ': ' . $config->formatTimeslot($timeslot) . '</li>';
                }

                echo '<br>';
                $days = $config->getDays();
                echo "<li><strong>" . "Reservierungs-Zeitraum: " . "</strong><br>" . $days->min_days . ' - ' . $days->max_days . ' Tage' . "</li>";
                ?>
            </ul>
        </div>
    </div>
</div>


