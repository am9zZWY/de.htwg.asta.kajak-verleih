<?php
create_header('How to Admin');
?>
<div class="container">
    <div class="row">
        <h2>How To´s</h2>
        <button class="accordion">Stornieren</button>
        <div class="panel">
            <p>Lorem ipsum..</p>
        </div>
        <button class="accordion">Zeitslot blockieren</button>
        <div class="panel">
            <p>test ipsum..</p>
        </div>
        <button class="accordion">Zeitslots ändern</button>
        <div class="panel">
            <p>test ipsum..</p>
        </div>
        <button class="accordion">Sonstiges</button>
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