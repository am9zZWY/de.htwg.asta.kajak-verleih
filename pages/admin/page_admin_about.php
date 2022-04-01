<?php
create_header('How to Admin');
?>
<div class="container">
    <div class="row">
        <button class="accordion"><strong>Stornieren</strong></button>
        <div class="panel">
            <p>Lorem ipsum..</p>
        </div>
        <button class="accordion"><strong>Zeitslot blockieren</strong></button>
        <div class="panel">
            <p>test ipsum..</p>
        </div>
        <button class="accordion"><strong>Zeitslots ändern</strong></button>
        <div class="panel">
            <p>test ipsum..</p>
        </div>
        <button class="accordion"><strong>Sonstiges</strong></button>
        <div class="panel">
            <h1>Du bist Dumm!</h1>
            <button class="accordion">Wirklich Sonstiges</button>
            <div class="panel">
                <h3>Josef und Vasilij sind krass!</h3>
            </div>
        </div>
        <?php echo create_accordion(false) ?>
    </div>
</div>