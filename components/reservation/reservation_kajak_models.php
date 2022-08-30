<?php
$connection = $_SESSION['connection'];
$available_kajaks = get_kajak_with_real_amount($connection);
?>

<div class='content-wrapper'>
    <h3>Welche Kajak-Modelle gibt es?</h3>
    <?php
    foreach ($available_kajaks as $kajak) {
        ?>
        <div>
            <h4><?=
                $kajak->name ?></h4><br>
            Das <?= $kajak->name ?>
            hat <?= $kajak->seats . ((int)$kajak->seats === 1 ? ' Sitz' : ' Sitze') ?>. Insgesamt
            <?= ((int)$kajak->amount === 1 ? ' ist' : ' sind') ?>
            derzeit <?= $kajak->amount ?? 0 ?> Stück dieses Modells verfügbar.<br>
            <img alt="Bild von <?= $kajak->name ?>" src="<?= $kajak->img ?>" class="img-fluid"
                 style="width: 300px; height: 200px;"/>
        </div>
        <?php
    }
    ?>
</div>