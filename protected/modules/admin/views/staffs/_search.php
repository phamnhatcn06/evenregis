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
		<?php echo $form->label($model, 'unique_code'); ?>
		<?php echo $form->textFieldGroup($model, 'unique_code', array('maxlength' => 100, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'department_code'); ?>
		<?php echo $form->textFieldGroup($model, 'department_code', array('maxlength' => 255, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'id_card'); ?>
		<?php echo $form->textFieldGroup($model, 'id_card', array('maxlength' => 50, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'rank_id'); ?>
		<?php echo $form->textFieldGroup($model, 'rank_id', array('maxlength' => 50, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'position_code'); ?>
		<?php echo $form->textFieldGroup($model, 'position_code', array('maxlength' => 255, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'property_code'); ?>
		<?php echo $form->textFieldGroup($model, 'property_code', array('maxlength' => 255, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'division_code'); ?>
		<?php echo $form->textFieldGroup($model, 'division_code', array('maxlength' => 255, 'widgetOptions' => array('htmlOptions' => array(
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
		<?php echo $form->label($model, 'is_lecturer'); ?>
		<?php echo $form->switchGroup($model, 'is_lecturer',
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
		<?php echo $form->label($model, 'curren_job_id'); ?>
		<?php echo $form->switchGroup($model, 'curren_job_id',
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
		<?php echo $form->label($model, 'lecturer_type'); ?>
		<?php echo $form->textFieldGroup($model, 'lecturer_type', array('maxlength' => 20, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'first_name'); ?>
		<?php echo $form->textFieldGroup($model, 'first_name', array('maxlength' => 100, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'last_name'); ?>
		<?php echo $form->textFieldGroup($model, 'last_name', array('maxlength' => 100, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'full_name'); ?>
		<?php echo $form->textFieldGroup($model, 'full_name', array('maxlength' => 255, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'email'); ?>
		<?php echo $form->textFieldGroup($model, 'email', array('maxlength' => 100, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'phone'); ?>
		<?php echo $form->textFieldGroup($model, 'phone', array('maxlength' => 100, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'birthday'); ?>
		<?php echo $form->datePickerGroup(
                $model,
                'birthday',
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
		<?php echo $form->label($model, 'terminate_date'); ?>
		<?php echo $form->datePickerGroup(
                $model,
                'terminate_date',
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
		<?php echo $form->label($model, 'join_hotel_date'); ?>
		<?php echo $form->datePickerGroup(
                $model,
                'join_hotel_date',
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
		<?php echo $form->label($model, 'end_testing_date'); ?>
		<?php echo $form->datePickerGroup(
                $model,
                'end_testing_date',
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
		<?php echo $form->label($model, 'married'); ?>
		<?php echo $form->dropDownList($model, 'married', array('0' => Yii::t('app', 'No'), '1' => Yii::t('app', 'Yes')), array('prompt' => Yii::t('app', 'All'))); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'gender'); ?>
		<?php echo $form->textFieldGroup($model, 'gender', array(
    'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    ))
  )); ?>
	</div>

	<div class="row">
		<?php echo $form->label($model, 'address'); ?>
		<?php echo $form->textFieldGroup($model, 'address', array('maxlength' => 255, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
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
		<?php echo $form->label($model, 'staff_type'); ?>
		<?php echo $form->textFieldGroup($model, 'staff_type', array('maxlength' => 255, 'widgetOptions' => array('htmlOptions' => array(
      'class' => 'input w-full border mt-2'
    )))); ?>
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
