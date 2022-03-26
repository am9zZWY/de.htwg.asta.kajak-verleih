<!-- Menu Sidebar   -->
<div id="mySidenav" class="sidenav">
    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
    <a href="/">Home</a>
    <a href="/kajaks">Kajaks</a>
    <a href="/about">Info</a>
    <a href="/impressum">Impressum</a>
</div>
<div id="main">
    <span style="font-size:30px;background-color: transparent;cursor:pointer" onclick="openNav()">&#9776; AStA</span>
</div>
<script lang="javascript">
    function openNav() {
        document.getElementById("mySidenav").style.width = "250px";
        document.getElementById("main").style.marginLeft = "250px";
        document.body.style.backgroundColor = "rgba(0,0,0,0.4)";
    }

    function closeNav() {
        document.getElementById("mySidenav").style.width = "0";
        document.getElementById("main").style.marginLeft = "0";
        document.body.style.backgroundColor = "white";
    }

</script>