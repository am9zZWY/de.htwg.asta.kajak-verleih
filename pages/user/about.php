<?php
create_header('Informationen zur Reservierung');
?>
<div class="container-fluid">
    <div class="container" style="border: white solid;">
        <div class="row">
            <h3 class="primary">Mietsachen</3>
        </div>
        <p>Vermietet werden Kajaks, mit einem Mietvertrag festgehalten Ausrüstungsgegenstände.
            Diese werden für einen sicheren Betrieb der Kajaks benötigt.
        </p>
        <div class="row">
            <button class="accordion">Haftung</button>
            <div class="panel">
                <p>Lorem ipsum..</p>
            </div>
            <button class="accordion">Rechtliches</button>
            <div class="panel">
                <p>test ipsum..</p>
            </div>
            <?php echo create_accordion(false) ?>
        </div>
    </div>
</div>
