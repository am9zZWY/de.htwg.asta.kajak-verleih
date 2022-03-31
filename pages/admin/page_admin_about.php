<?php
create_header('How to Admin');
?>
<div class="container">
    <div class="row">
        <button class="accordion"><b>Stornieren</b></button>
        <div class="panel">
            <p>Lorem ipsum..</p>
        </div>
        <button class="accordion"><b>Zeitslot blockieren</b></button>
        <div class="panel">
            <p>test ipsum..</p>
        </div>
        <button class="accordion"><b>Zeitslots ändern</b></button>
        <div class="panel">
            <p>test ipsum..</p>
        </div>
        <button class="accordion"><b>Sonstiges</b></button>
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