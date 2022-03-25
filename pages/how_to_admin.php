<?php
include '../templates/head.php'
?>
<html lang="de" xmlns="http://www.w3.org/1999/html">
<body>
<?php include '../templates/admin_sidebar.php' ?>

<div class="section" id="booking">
    <div class="section-center">
        <div class="booking-cta">
            <div class="container">
                <div class="row">
                <h2>How To´s</h2>
                    <button class="accordion">Stornieren</button>
                    <div class="panel">
                        <p>Lorem ipsum..</p>
                    </div>
                    <button class="accordion">Zeitslot blockieren</button>
                    <div class="panel">
                        <p>test ipsum..</p>
                    </div>
                    <button class="accordion">Zeitslots ändern</button>
                    <div class="panel">
                        <p>test ipsum..</p>
                    </div>
                    <button class="accordion">Sonstiges</button>
                    <div class="panel">
                        <h1>Du bist Dumm!</h1>
                    </div>
                <script>
                    var acc = document.getElementsByClassName("accordion");
                    var i;

                    for (i = 0; i < acc.length; i++) {
                        acc[i].addEventListener("click", function() {
                            this.classList.toggle("active");
                            var panel = this.nextElementSibling;
                            if (panel.style.maxHeight) {
                                panel.style.maxHeight = null;
                            } else {
                                panel.style.maxHeight = panel.scrollHeight + "px";
                            }
                        });
                    }
                </script>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>