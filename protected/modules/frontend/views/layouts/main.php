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
  <!-- App favicon -->
  <!--  <link rel="shortcut icon" href="assets/images/favicon.ico">-->
  <?php echo MyHelper::renderCss(); ?>
  <script src="<?= Yii::app()->theme->baseUrl ?>/vertical/assets/js/modernizr.min.js"></script>

</head>

<body class="<?= $this->bodyClass ?>">
  <div id="wrapper">
    <?php echo $content; ?>
  </div>
  <!-- /.modal -->
  <div id="responsive-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
          <h5 class="modal-title">Nội dung</h5>
        </div>
        <div class="modal-body" id="modal-inner-content">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
        </div>
      </div>
    </div>
  </div>
  <?php echo MyHelper::renderJs($this->isLoginPage); ?>
</body>

</html>