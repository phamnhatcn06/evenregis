<?php
$this->pageTitle = 'Đăng nhập Admin';
?>

<div class="row justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="col-md-4">
        <div class="card shadow">
            <div class="card-header bg-dark text-white text-center">
                <h4 class="mb-0"><i class="bi bi-shield-lock"></i> Admin Login</h4>
            </div>
            <div class="card-body">
                <?php $form = $this->beginWidget('CActiveForm', array(
                    'id' => 'login-form',
                    'enableClientValidation' => true,
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
                    <?php echo CHtml::submitButton('Đăng nhập', array('class' => 'btn btn-dark')); ?>
                </div>

                <?php $this->endWidget(); ?>
            </div>
        </div>
    </div>
</div>
