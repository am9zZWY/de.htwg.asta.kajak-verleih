<!-- Menu Sidebar -->
<?php
if (($_SERVER['REQUEST_METHOD'] === 'POST') && isset($_POST['logout'])) {
    logout();
    ?>
    <script>
        window.location.href = "/";
    </script>
    <?php
}
?>

<nav class="fixed-top">
    <div id="burger" class="sidenav-burger">
        <span onclick="openNav()">&#9776;</span>
    </div>
</nav>
<div id="sidenav" class="sidenav">
    <a href="javascript:void(0)" class="close-btn" onclick="closeNav()">&times;</a>
    <a href="/">Startseite</a>
    <a href="/about">Info</a>
    <a href="/impressum">Impressum</a>
    <?php
    if (is_logged_in()) {
        ?>
        <a href="/admin">Dashboard</a>
        <a href="/config">Konfiguration</a>
        <a href="/how_to_admin">Anleitung</a>
        <form method="POST">
            <button type="submit" name="logout">Abmelden</button>
        </form>
        <?php
    }
    ?>
</div>
<script lang="javascript">
    function openNav() {
        document.getElementById("sidenav").style.width = "250px";
        document.getElementById("burger").style.marginLeft = "250px";
        document.body.style.backgroundColor = "rgba(0,0,0,0.4)";
    }

    function closeNav() {
        document.getElementById("sidenav").style.width = "0";
        document.getElementById("burger").style.marginLeft = "0";
        document.body.style.backgroundColor = "white";
    }
</script>