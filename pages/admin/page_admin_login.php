<?php
create_header('Login');
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-6">
            <div class="custom-form">
                <form class="animate" method="post" class="needs-validation">
                    <div class="img-container">
                        <img src="/resources/images/login.PNG" alt="Avatar" class="img-avatar">
                    </div>

                    <div class="form-floating mb-3">
                        <input name="name" type="text" placeholder="Max Musterfrau" id="name"
                               class="form-control"
                               required>
                        <label for="name">
                            Name
                        </label>
                    </div>

                    <div class="form-floating mb-3">
                        <input name="password" type="password" placeholder="123" id="password"
                               class="form-control"
                               required>
                        <label for="password">
                            Passwort
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary submit-btn">Login</button>
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
                </form>
            </div>
        </div>
    </div>
</div>