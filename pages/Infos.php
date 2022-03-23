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
                    <h2 class="primary">Info </h2>
                </div>
                <div class="container" style="border: white solid;bo" >
                    <div class="row">
                        <h3 class="primary">Mietsachen</3>
                    </div>
                    <p>Vermietet werden Kajaks, mit einem Mietvertrag festgehalten Ausrüstungsgegenstände.
                        Diese werden für einen sicheren Betrieb der Kajaks benötigt.
                    </p>
                    <div class="row">
                        <button class="accordion">Haftung</button>
                            <div class="panel">
                              <p>Lorem ipsum..</p>
                            </div>
                            <button class="accordion">Rechtliches</button>
                            <div class="panel">
                                <p>test ipsum..</p>
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
    </div>
</div>
</body>
</html>