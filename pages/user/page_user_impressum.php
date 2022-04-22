<?php
echo create_header('Impressum')
?>
<div class="container">
    <div class="row">
        <div class="content">
            <div>
                <h3>AStA</h3>
                <strong>Hochschule Konstanz</strong>
                <br>
                Alfred-Wachtelstraße 8 <br>
                78462 Konstanz<br>
                Gebäude D<br><br><br>
                <strong>Kontaktaufnahme</strong><br>
                Tel.: 07531 206-431/-122<br>
                E-Mail: asta@htwg-konstanz.de
            </div>
            <div class="row" style="text-align: justify;">
                <button class="accordion"><strong>Urheberrecht</strong></button>
                <div class="panel">
                    <p>
                    <strong>Die auf unseren Internetseiten enthaltene Werke und INhalte unterstehen dem Urheberrecht.</strong><br>
                    <br>
                        Ohne schriftliche Genehminung des jeweiligen Ersteller oder Autors dürfen die Werke bzw. Inhalte weder vervielfältigt, bearbeitet, noch verbreitet werden.
                        Das Herunterladen und Kopieren unseres Angebots ist ausschließlich für den privaten, nicht kommerziellen Gebrauch erlaubt.
                    <br>
                    </p>
                </div>
                <button class="accordion"><strong>Haftung für Inhalte</strong></button>
                <div class="panel">
                    <p>
                        <strong>Sämtliche Inhalte unserer Internetseite sind mit größtmöglicher Sorgfalt erstellt worden.</strong>
                        Dennoch können wir keine Gewähr für deren Richtigkeit, Vollständigkeit und Aktualität übernehmen.
                        Unsere Haftung beginnt jedoch erst in dem Moment, in welchem wir von einer konkreten Rechtsverletzung Kenntis erlangen.
                        Bei entsprechender Kenntnis werden wir die ebtroffen Inhalte unverzüglich entfernen.

                    </p>
                </div>
                <?php echo create_accordion() ?>
            </div>
        </div>
    </div>
</div>
