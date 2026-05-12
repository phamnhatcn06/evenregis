<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <title>Hệ thống website Đại hội cổ đông 2021</title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta content="Hệ thống quản lý chất lượng" name="description" />
  <meta content="PVN" name="author" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <!--Start of Tawk.to Script-->
  <script type="text/javascript">
    var Tawk_API = Tawk_API || {},
      Tawk_LoadStart = new Date();
    (function() {
      var s1 = document.createElement("script"),
        s0 = document.getElementsByTagName("script")[0];
      s1.async = true;
      s1.src = 'https://embed.tawk.to/58782ee1edbcab04767ef462/default';
      s1.charset = 'UTF-8';
      s1.setAttribute('crossorigin', '*');
      s0.parentNode.insertBefore(s1, s0);
    })();
  </script>
  <?php echo MyHelper::renderCss(); ?>
</head>

<body class="<?= $this->bodyClass ?> enlarged">
  <div id="wrapper">
    <?php echo $content; ?>
  </div>

  <!-- Modal -->
  <div id="custom-modal" class="modal-demo">
    <button type="button" class="close" onclick="Custombox.close();">
      <span>&times;</span><span class="sr-only">Close</span>
    </button>
    <h4 id="title-modal" class="custom-modal-title">Modal title</h4>
    <div class="custom-modal-text" id="form-content-area">

    </div>
    <div class="modal-footer" style="text-align: center;    display: block;">
      <button type="button" id="submit-form" data-action="" class="btn btn-custom waves-light waves-effect"><span class="btn-text">&nbsp;<?= Yii::t('app', 'Save') ?></span></button>
    </div>
  </div>


  <div id="custom-modal-image" class="modal-demo">
    <button type="button" class="close" onclick="Custombox.close();">
      <span>&times;</span><span class="sr-only">Close</span>
    </button>
    <h4 id="title-modal-image" class="custom-modal-title">Hình ảnh</h4>
    <div class="custom-modal-text" id="form-content-area-image">

    </div>
  </div>

  <div id="responsive-modal-image" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h5 class="modal-title">Hình ảnh</h5>
        </div>
        <div class="modal-body">
          <div id="modal-inner-content">

          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
        </div>
      </div>
    </div>
  </div>

  <?php echo MyHelper::renderJsMod(); ?>
</body>

</html>