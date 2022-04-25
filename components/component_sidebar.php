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

<nav class="navbar navbar-expand-lg bg-light fixed-top">
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav">
            <li class="sidenav-burger" id="burger">
                <span onclick="openNav()">&#9776;</span>
            </li>
        </ul>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="/privacy">
                    Datenschutz
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/impressum">
                    Impressum
                </a>
            </li>
        </ul>
    </div>
</nav>
<div id="sidenav" class="sidenav">
    <ul class="nav nav-flush flex-column mb-auto">
        <li class="navbar-toggler">
            <a href="javascript:void(0)" class="close-btn" onclick="closeNav()">&times;</a>
        </li>
        <li>
            <a class="nav-link" href="/">Startseite</a>
        </li>
        <li>
            <a class="nav-link" href="/about">Info</a>
        </li>
        <?php
        if (is_logged_in()) {
            ?>
            <li>
                <a class="nav-link" href="/admin">Dashboard</a>
            </li>
            <li>
                <a class="nav-link" href="/config">Konfiguration</a>
            </li>
            <li>
                <form method="POST">
                    <button type="submit" name="logout">Abmelden</button>
                </form>
            </li>
            <?php
        }
        ?>
    </ul>
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