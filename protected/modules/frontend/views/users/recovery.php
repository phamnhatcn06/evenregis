<!-- Begin page -->
<div class="accountbg"
     style="background: url('<?= Yii::app()->theme->baseUrl ?>/vertical/assets/images/backlogin.jpg');background-size: cover;"></div>

<div class="wrapper-page account-page-full">

  <div class="card">
    <div class="card-block">
      <div class="account-box">
        <div class="card-box p-5">
          <h2 class="text-uppercase text-center pb-4">
            <a href="index.html" class="text-success">
                            <span><img src="<?= Yii::app()->theme->baseUrl ?>/vertical/assets/images/logo_MT.png"
                                       alt=""></span>
            </a>
          </h2>
          <?php $form = $this->beginWidget('CActiveForm', array(
            'id' => 'recovery-form',
            'enableClientValidation' => true,
            'clientOptions' => array(
              'validateOnSubmit' => true,
            ),
            'htmlOptions' => array(
              'class' => 'm-t'
            ),
          )); ?>
          <?php if(isset($message) && $message != ''){ ?>
            <p style="color: #ff0000"><?= $message ?></p>
          <?php } ?>
          <?php if ($token == '') { ?>
            <div class="form-group success">
              <?php echo $form->errorSummary($model, ''); ?>
              <style>
                div#login-form_es_, div#recovery-form_es_ {
                  color: #ff0000;
                }

              </style>
              <p style="color: #ff0000;font-size: 12px;font-weight: 600;">Vui lòng cung cấp email đăng nhập để lấy lại mật khẩu</p>
              <input type="email" class="form-control" placeholder="Email lấy lại mật khẩu"
                     name="MChangePassword[email]">
              <br/>
              <button type="submit" class="btn btn-primary block full-width m-b">Lấy mật khẩu</button>
            </div>
          <?php } else { ?>
            <?php echo $form->errorSummary($model); ?>
            <fieldset>
              <div class="form-group">
                <p style="color: #ff0000;font-size: 12px;font-weight: 600;">Vui lòng điền mật khẩu mới</p>
                <?php echo $form->passwordField($model, 'password', array('maxlength' => 45, 'class' => 'form-control', 'placeholder' => 'Mật khẩu mới')); ?>
                <?php echo $form->error($model, 'password'); ?>
              </div>
              <div class="form-group">
                <p style="color: #ff0000;font-size: 12px;font-weight: 600;">Vui lòng nhập lại mật khẩu mới</p>
                <?php echo $form->passwordField($model, 'repeatPassword', array('maxlength' => 45, 'class' => 'form-control', 'placeholder' => 'Nhập lại mật khẩu mới')); ?>
                <?php echo $form->error($model, 'password'); ?>
              </div>
              <button type="submit" class="btn btn-primary block full-width m-b">Đổi mật khẩu</button>
            </fieldset>
          <?php } ?>
          <?php $this->endWidget(); ?>
        </div>
      </div>

    </div>
  </div>

  <div class="m-t-40 text-center">
    <p class="account-copyright">2018 © muongthanh.com</p>
  </div>

</div>


