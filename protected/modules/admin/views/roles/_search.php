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
		<?php echo $form->label($model, 'event_id'); ?>
		<?php echo $form->dropDownList($model, 'event_id', GxHtml::listDataEx(Events::model()->findAllAttributes(null, true)), array('prompt' => Yii::t('app', 'All'))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'name'); ?>
		<?php echo $form->textFieldGroup($model, 'name', array('maxlength' => 100, 'widgetOptions' => array('htmlOptions' => array(
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
		<?php echo $form->label($model, 'color'); ?>
		<?php echo $form->textFieldGroup($model, 'color', array('maxlength' => 50, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'icon'); ?>
		<?php Yii::app()->clientScript->registerScript("image", "
                $('#selectedImg').click(function () {
                    var finder = new CKFinder();
                    finder.selectActionFunction = function (fileUrl) {
                        $('#Roles_icon').val(fileUrl);
                        $('#imgView').attr('src', fileUrl);
                    };
                    finder.popup();
                });
                ");
                $tmp = '<input id="selectedImg" value="+ Selected Photos" id="SelectImg" type="button" class="btn btn-primary btn-xs" style="font-size: 9px;padding: 0px;" >';
                $tmp .= '<input value="View Image" style="font-size: 9px;padding: 0px;" type="button" class="btn btn-success btn-xs" data-toggle="modal" data-target="#myModal">';
                echo $form->textFieldGroup($model, 'icon', array('maxlength' => 255, 'prepend' => $tmp,));; ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'sort_order'); ?>
		<?php echo $form->textFieldGroup($model, 'sort_order', array(
    'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    ))
  )); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'description'); ?>
		<?php echo $form->ckEditorGroup($model,'description',
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
