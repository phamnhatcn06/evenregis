<style type="text/css">
    .be-wrapper {
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .splash-container {
        max-width: 401px;
        margin: 20px auto;
    }
</style>
<div class="be-wrapper be-login">
    <div class="be-content">
        <div class="main-content container-fluid">
            <div class="splash-container">
                <div class="panel panel-default panel-border-color panel-border-color-primary">
                    <div class="panel-body">
                        <?php if (isset($message) && $message != '') { ?>
                            <div class="col-xs-12 col-12">
                                <div class="col-xs-12 col-12 col-center-block">
                                    <p style="color: #FF0000;font-size: 18px;font-weight: 600;"><?php echo $message; ?></p>
                                    <div class="count-down-time">
                                        <a href="#" id="download" class="button"></a>
                                    </div>
                                    <script type="text/javascript">
                                        var downloadButton = document.getElementById("download");
                                        var counter = 5;
                                        var newElement = document.createElement("p");
                                        newElement.innerHTML = "<span class='count-time'> Vui lòng đợi 5 giây để trở về.</span>";
                                        var id;
                                        downloadButton.parentNode.replaceChild(newElement, downloadButton);
                                        id = setInterval(function () {
                                            counter--;
                                            if (counter < 0) {
                                                window.location.href = '<?php echo $redirect; ?>'
                                            } else {
                                                newElement.innerHTML = "<span class='count-time'> Vui lòng đợi " + counter.toString() + " giây để trở về</span>";
                                            }
                                        }, 1000);
                                    </script>
                                </div>
                                <div class="col-xs-4 col-md-4"></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


