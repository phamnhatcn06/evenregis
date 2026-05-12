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
		<?php echo $form->label($model, 'attendee_id'); ?>
		<?php echo $form->dropDownList($model, 'attendee_id', GxHtml::listDataEx(Attendees::model()->findAllAttributes(null, true)), array('prompt' => Yii::t('app', 'All'))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'role_id'); ?>
		<?php echo $form->dropDownList($model, 'role_id', GxHtml::listDataEx(Roles::model()->findAllAttributes(null, true)), array('prompt' => Yii::t('app', 'All'))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'assigned_by'); ?>
		<?php echo $form->textFieldGroup($model, 'assigned_by', array('maxlength' => 20, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'assigned_at'); ?>
		<?php echo $form->datePickerGroup(
                $model,
                'assigned_at',
                array(
                'widgetOptions' => array(
                    'options' => array(
                        'language' => 'vi',
                        'calendarWeeks' => 'true',
                        'todayHighlight' => 'true',
                        'todayBtn' => 'linked',
                        'clearBtn' => 'true',
                        'orientation' => 'bottom right',
                        'format' => 'yyyy-mm-dd',
                    ),
                    'htmlOptions' => array(
                        'class' => 'input w-full border mt-2'
                    )
                ),
                'wrapperHtmlOptions' => array(
                   'class' => 'col-sm-5',
                ),
                'prepend' => '<i class="glyphicon glyphicon-calendar"></i>'
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
