<?php
include '../../templates/head.php'
?>
<body>
<?php include '../../templates/sidebar.php' ?>

<div class="bg">
    <div class="section" id="booking">
    <div class="section-center">
        <div class="booking-cta">
            <div class="container">
                <div class="row">
                    <h2 class="primary">Info </h2>
                </div>
                <div class="container" style="border: white solid;">
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
                            const acc = document.getElementsByClassName("accordion");
                            Array.from(acc).forEach((item) => {
                                item.addEventListener("click", function () {
                                    item.classList.toggle("active");
                                    const panel = item.nextElementSibling;
                                    panel.style.maxHeight = panel.style.maxHeight ? null : panel.scrollHeight + "px";
                                });
                            })
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