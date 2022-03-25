<?php
include '../templates/head.php'
?>
<html lang="de" xmlns="http://www.w3.org/1999/html">
<body>
<?php include '../templates/sidebar.php' ?>

<div class="section" id="booking">
    <div class="section-center">
        <div class="booking-cta">
            <div class="container">
                <div class="row">
                    <h1 class="primary">Are u even Admin?</h1>
                </div>
            </div>
            <center><button onclick="document.getElementById('id01').style.display='block'" style="width:auto;height: auto">Login</button></center>

            <div id="id01" class="modal">

                <form class="modal-content animate" action="/action_page.php" method="post">
                    <div class="imgcontainer">
                        <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
                        <img src="../resources/images/login.PNG" alt="Avatar" class="avatar">
                    </div>

                    <div class="container">
                        <label for="uname"><b>Username</b></label>
                        <input type="text" placeholder="Enter Username" name="uname" required>

                        <label for="psw"><b>Password</b></label>
                        <input type="password" placeholder="Enter Password" name="psw" required>

                        <button type="submit">Login</button>

                    </div>
                </form>
            </div>
            <script>
                // Get the modal
                var modal = document.getElementById('id01');

                // When the user clicks anywhere outside of the modal, close it
                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }
            </script>

        </div>
    </div>
</div>

</body>
</html>