<?php
$this->pageTitle = 'Đăng ký - ' . Yii::app()->name;
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow">
            <div class="card-header bg-success text-white">
                <h4 class="mb-0">Đăng ký tài khoản</h4>
            </div>
            <div class="card-body">
                <?php $form = $this->beginWidget('CActiveForm', array(
                    'id' => 'register-form',
                    'enableClientValidation' => true,
                    'htmlOptions' => array('class' => 'form'),
                )); ?>

                <div class="mb-3">
                    <?php echo $form->labelEx($model, 'username'); ?>
                    <?php echo $form->textField($model, 'username', array('class' => 'form-control')); ?>
                    <?php echo $form->error($model, 'username', array('class' => 'text-danger')); ?>
                </div>

                <div class="mb-3">
                    <?php echo $form->labelEx($model, 'email'); ?>
                    <?php echo $form->emailField($model, 'email', array('class' => 'form-control')); ?>
                    <?php echo $form->error($model, 'email', array('class' => 'text-danger')); ?>
                </div>

                <div class="mb-3">
                    <?php echo $form->labelEx($model, 'password'); ?>
                    <?php echo $form->passwordField($model, 'password', array('class' => 'form-control')); ?>
                    <?php echo $form->error($model, 'password', array('class' => 'text-danger')); ?>
                </div>

                <div class="mb-3">
                    <?php echo $form->labelEx($model, 'password_repeat'); ?>
                    <?php echo $form->passwordField($model, 'password_repeat', array('class' => 'form-control')); ?>
                    <?php echo $form->error($model, 'password_repeat', array('class' => 'text-danger')); ?>
                </div>

                <div class="d-grid">
                    <?php echo CHtml::submitButton('Đăng ký', array('class' => 'btn btn-success')); ?>
                </div>

                <?php $this->endWidget(); ?>

                <hr>
                <p class="text-center mb-0">
                    Đã có tài khoản?
                    <a href="<?php echo $this->createUrl('login'); ?>">Đăng nhập</a>
                </p>
            </div>
        </div>
    </div>
</div>
