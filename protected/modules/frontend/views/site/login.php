<?php
$this->pageTitle = 'Đăng nhập - ' . Yii::app()->name;
?>

<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Đăng nhập</h4>
            </div>
            <div class="card-body">
                <?php $form = $this->beginWidget('CActiveForm', array(
                    'id' => 'login-form',
                    'enableClientValidation' => true,
                    'htmlOptions' => array('class' => 'form'),
                )); ?>

                <div class="mb-3">
                    <?php echo $form->labelEx($model, 'username'); ?>
                    <?php echo $form->textField($model, 'username', array('class' => 'form-control')); ?>
                    <?php echo $form->error($model, 'username', array('class' => 'text-danger')); ?>
                </div>

                <div class="mb-3">
                    <?php echo $form->labelEx($model, 'password'); ?>
                    <?php echo $form->passwordField($model, 'password', array('class' => 'form-control')); ?>
                    <?php echo $form->error($model, 'password', array('class' => 'text-danger')); ?>
                </div>

                <div class="mb-3 form-check">
                    <?php echo $form->checkBox($model, 'rememberMe', array('class' => 'form-check-input')); ?>
                    <?php echo $form->label($model, 'rememberMe', array('class' => 'form-check-label')); ?>
                </div>

                <div class="d-grid">
                    <?php echo CHtml::submitButton('Đăng nhập', array('class' => 'btn btn-primary')); ?>
                </div>

                <?php $this->endWidget(); ?>

                <hr>
                <p class="text-center mb-0">
                    Chưa có tài khoản?
                    <a href="<?php echo $this->createUrl('register'); ?>">Đăng ký ngay</a>
                </p>
            </div>
        </div>
    </div>
</div>
