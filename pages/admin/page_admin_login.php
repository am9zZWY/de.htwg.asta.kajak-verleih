<?php
echo create_header('Einloggen');

/* create csrf token */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['token'] = md5(uniqid(mt_rand(), true));
    $_SESSION['token_field'] = md5(uniqid(mt_rand(), true));
    $_SESSION['admin_username_field'] = md5(uniqid(mt_rand(), true));
    $_SESSION['admin_password_field'] = md5(uniqid(mt_rand(), true));
}
?>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-sm-6">
            <div class="kajak-form">
                <form class="animate" method="post" class="needs-validation">
                    <input type="hidden" name="<?php echo $_SESSION['token_field'] ?? '' ?>"
                           value="<?php echo $_SESSION['token'] ?? '' ?>">
                    <div class="img-container">
                        <img src="/static/img/login-avatar.png" alt="Avatar" class="img-avatar">
                    </div>

                    <div class="form-floating mb-3">
                        <input name="<?php echo $_SESSION['admin_username_field'] ?? '' ?>" type="text" placeholder="Max Musterfrau" id="<?php echo $_SESSION['admin_username_field'] ?? '' ?>"
                               class="form-control"
                               required>
                        <label for="<?php echo $_SESSION['admin_username_field'] ?? '' ?>">
                            Name
                        </label>
                    </div>

                    <div class="form-floating mb-3">
                        <input name="<?php echo $_SESSION['admin_password_field'] ?? '' ?>" type="password" placeholder="123" id="<?php echo $_SESSION['admin_password_field'] ?? '' ?>"
                               class="form-control"
                               required>
                        <label for="<?php echo $_SESSION['admin_password_field'] ?? '' ?>">
                            Passwort
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary custom-btn">Login</button>
                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        /* check if csrf token match */
                        $token = clean_string($_POST[$_SESSION['token_field']] ?? '');

                    if (!$token || $token !== $_SESSION['token']) {
                        ?>
                        <h3>
                            Es gibt ein Sicherheitsproblem!
                        </h3>
                    <?php
                    exit();
                    }


                    $logged_in = login(clean_string($_POST[$_SESSION['admin_username_field']] ?? ''), clean_string($_POST[$_SESSION['admin_password_field']] ?? ''));
                    if (!$logged_in) {
                    ?>
                        <h3>Einloggen fehlgeschlagen</h3>
                    <?php
                    } else {
                    ?>
                        <h3>Einloggen erfolgreich</h3>
                        <script>
                            window.location.href = "/admin";
                        </script>
                        <?php
                    }
                    }
                    ?>
                </form>
            </div>
        </div>
    </div>
</div>