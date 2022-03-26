<?php
include '../../templates/head.php';
require __DIR__ . '/../../scripts/login.php';
require __DIR__ . '/../../scripts/helpers.php';

?>
<html lang="de" xmlns="http://www.w3.org/1999/html">
<body>
<?php include '../../templates/sidebar.php' ?>
<div class="section" id="booking">
    <div class="container-fluid">
        <div class="row align-items-center">
            <h1 class="primary">Are u even Admin?</h1>
        </div>

        <div class="row align-items-center">
            <div class="col m-auto">
                <form class="animate" method="post">
                    <div class="imgcontainer">
                        <img src="/resources/images/login.PNG" alt="Avatar" class="avatar">
                    </div>

                    <div class="container">
                        <div class="form-floating mb-3">
                            <input name="name" type="text" id="name"
                                   class="form-control"
                                   required>
                            <label for="name">
                                Name
                            </label>
                        </div>
                        <div class="form-floating mb-3">
                            <input name="password" type="password" id="password" class="form-control" required/>
                            <label for="password">
                                Password
                            </label>
                        </div>
                        <button type="submit">Login</button>
                        <?php
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $logged_in = login(clean_string($_POST['name']), clean_string($_POST['password']));
                            if (!$logged_in) {
                                ?>
                                <h3>Einloggen fehlgeschlagen</h3>
                                <?php
                            } else {
                                ?>

                                <h3>Einloggen erfolgreich</h3>
                                <?php
                            }
                        }
                        ?>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>