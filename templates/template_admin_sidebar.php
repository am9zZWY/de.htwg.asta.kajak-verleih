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
<div id="mySidenav" class="sidenav">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <a href="/reservations">Reservierungen</a>
    <a href="/how_to_admin">How To Admin</a>
    <form method="POST">
        <button type="submit" name="logout">Abmelden</button>
    </form>
</div>
<div id="burger" class="sidenav-burger">
    <span style="font-size:30px; background-color: transparent; cursor:pointer" onclick="openNav()">&#9776; AStA</span>
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
    }
</script>