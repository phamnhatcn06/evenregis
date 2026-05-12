<div class="wide form">

<?php $form = $this->beginWidget('GxActiveForm', array(
	'action' => Yii::app()->createUrl($this->route),
	'method' => 'get',
)); ?>

	<div class="row">
		<?php echo $form->label($model, 'id'); ?>
		<?php echo $form->textFieldGroup($model, 'id', array('maxlength' => 20, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'property_code'); ?>
		<?php echo $form->textFieldGroup($model, 'property_code', array('maxlength' => 50, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'unique_code'); ?>
		<?php echo $form->textFieldGroup($model, 'unique_code', array('maxlength' => 100, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'code'); ?>
		<?php echo $form->textFieldGroup($model, 'code', array('maxlength' => 50, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'name'); ?>
		<?php echo $form->textFieldGroup($model, 'name', array('maxlength' => 255, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'total_staff'); ?>
		<?php echo $form->textFieldGroup($model, 'total_staff', array(
    'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    ))
  )); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'notes'); ?>
		<?php echo $form->ckEditorGroup($model,'notes',
				array(
					'wrapperHtmlOptions' => array(
						/* 'class' => 'col-sm-5', */
					),
					'widgetOptions' => array(
						'editorOptions' => array(
							'fullpage' => 'js:true',
							/* 'width' => '640', */
							/* 'resize_maxWidth' => '640', */
							/* 'resize_minWidth' => '320'*/
						)
					)
				)
			   ); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'status'); ?>
		<?php echo $form->switchGroup($model, 'status',
				array(
					'widgetOptions' => array(
						'events'=>array(
							'switchChange'=>'js:function(event, state) {
								  console.log(this); // DOM element
								  console.log(event); // jQuery event
								  console.log(state); // true | false
							}'
						),
						'options' => array(
                        'size' => 'large', //null, 'mini', 'small', 'normal', 'large
                        'onColor' => 'primary', // 'primary', 'info', 'success', 'warning', 'danger', 'default'
                        'offColor' => 'danger',  // 'primary', 'info', 'success', 'warning', 'danger', 'default'
                    ),
					)
				)); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'created_at'); ?>
		<?php echo $form->textFieldGroup($model, 'created_at', array(
    'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    ))
  )); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'updated_at'); ?>
		<?php echo $form->textFieldGroup($model, 'updated_at', array(
    'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    ))
  )); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'deleted_at'); ?>
		<?php echo $form->textFieldGroup($model, 'deleted_at', array(
    'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    ))
  )); ?>
	</div>

	<div class="row buttons">
		<?php echo GxHtml::submitButton(Yii::t('app', 'Search')); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- search-form -->
