<!-- Begin page -->
<div class="accountbg" style="background: url('<?= Yii::app()->theme->baseUrl ?>/vertical/assets/images/login.jpg') no-repeat;background-position: left center;background-size: contain !important;"></div>

<div class="wrapper-page account-page-full login-page">
  <div class="card">
    <div class="card-block">
      <div class="account-box">
        <div class="card-box p-5">
          <h2 class="text-uppercase text-center pb-4">
            <a href="/" class="text-success">
              <span><img style="width: 200px;" src="<?= Yii::app()->theme->baseUrl ?>/vertical/assets/images/logo_ogc.png" alt=""></span>
            </a>
          </h2>
          <?php $form = $this->beginWidget('CActiveForm', array(
            'id' => 'login-form',
            'enableClientValidation' => true,
            'clientOptions' => array(
              'validateOnSubmit' => true,
            ),
          )); ?>
          <div class="form-group m-b-20 row">
            <?php echo $form->label($model, 'email'); ?>
            <?php echo $form->textField($model, 'email', array('maxlength' => 45, 'class' => 'form-control', 'placeholder' => 'Email')); ?>
            <?php echo $form->error($model, 'email'); ?>
          </div>
          <div class="form-group m-b-20 row">
            <?php echo $form->label($model, 'password'); ?>
            <?php echo $form->passwordField($model, 'password', array('maxlength' => 45, 'class' => 'form-control', 'placeholder' => 'Mật khẩu')); ?>
            <?php echo $form->error($model, 'password'); ?>
          </div>
          <div class="form-group row m-b-20">
            <div class="col-12">

              <div class="checkbox checkbox-custom">
                <input id="remember" type="checkbox" checked="">
                <label for="remember">
                  Ghi nhớ đăng nhập
                </label>
              </div>

            </div>
          </div>

          <div class="form-group m-b-20 row text-center">
            <button type="submit" class="btn btn-block btn-custom waves-effect waves-light">Đăng nhập
            </button>
          </div>
          <?php $this->endWidget(); ?>
        </div>
      </div>

    </div>
  </div>

  <div class="m-t-40 text-center">
    <p class="account-copyright">2020 ©</p>
  </div>

</div>