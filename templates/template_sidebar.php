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
        <span style="font-size:30px;background-color: transparent;cursor:pointer" onclick="openNav()">&#9776;</span>
    </div>
</nav>
<div id="mySidenav" class="sidenav">
    <a href="javascript:void(0)" class="close-btn" onclick="closeNav()">&times;</a>
    <a href="/">Home</a>
    <a href="/kajaks">Kajaks</a>
    <a href="/about">Info</a>
    <a href="/impressum">Impressum</a>
    <?php
    if (is_logged_in()) {
        ?>
        <a href="/reservations">Reservierungen</a>
        <a href="/how_to_admin">How To Admin</a>
        <a href="/config">Konfiguration</a>
        <form method="POST">
            <button type="submit" name="logout">Abmelden</button>
        </form>
        <?php
    }
    ?>
</div>
<script lang="javascript">
    function openNav() {
        document.getElementById("mySidenav").style.width = "250px";
        document.getElementById("burger").style.marginLeft = "250px";
        document.body.style.backgroundColor = "rgba(0,0,0,0.4)";
    }

    function closeNav() {
        document.getElementById("mySidenav").style.width = "0";
        document.getElementById("burger").style.marginLeft = "0";
        document.body.style.backgroundColor = "white";
    }
</script>