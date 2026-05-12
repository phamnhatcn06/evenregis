<?php

/**
 * The following variables are available in this template:
 * - $this: the CrudCode object
 */
?>
<div class="form-wrap">
<?php $ajax = ($this->enable_ajax_validation) ? 'true' : 'false'; ?>
<?php echo '<?php '; ?>
$form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => '<?php echo $this->class2id($this->modelClass); ?>-form',
    'htmlOptions' => array('data-toggle' => 'validator', 'enctype' => 'multipart/form-data'),
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
    ),
));
<?php echo '?>'; ?>

<?php echo "<?php echo \$form->errorSummary(\$model); ?>\n"; ?>

<?php foreach ($this->tableSchema->columns as $column) : ?>
<?php if (!$column->autoIncrement && !in_array($column->name, array('created_at', 'updated_at', 'deleted_at'))) : ?>
<?php
    if ($column->dbType === 'datetime' || $column->dbType === 'timestamp') {
        // Skip datetime auto fields
        continue;
    } elseif ($column->dbType === 'date') {
        // Date picker
        echo "<?php\necho \$form->datePickerGroup(\n    \$model,\n    '{$column->name}',\n    array(\n        'widgetOptions' => array(\n            'options' => array(\n                'language' => 'vi',\n                'todayHighlight' => 'true',\n                'todayBtn' => 'linked',\n                'clearBtn' => 'true',\n                'orientation' => 'bottom right',\n                'format' => 'yyyy-mm-dd',\n            ),\n            'htmlOptions' => array(\n                'class' => 'input w-full border mt-2'\n            )\n        ),\n        'wrapperHtmlOptions' => array(\n            'class' => 'col-sm-5',\n        ),\n    )\n);\n?>\n\n";
    } elseif ($column->dbType === 'text' || (isset($column->size) && $column->size > 500)) {
        // Text area or CKEditor
        echo "<?php\necho \$form->textAreaGroup(\$model, '{$column->name}', array(\n    'widgetOptions' => array(\n        'htmlOptions' => array(\n            'class' => 'input w-full border mt-2',\n            'rows' => 5\n        )\n    )\n));\n?>\n\n";
    } else {
        // Standard text field
        echo "<?php\necho \$form->textFieldGroup(\$model, '{$column->name}', array(\n    'maxlength' => " . ($column->size ? $column->size : 255) . ",\n    'widgetOptions' => array(\n        'htmlOptions' => array(\n            'class' => 'input w-full border mt-2'\n        )\n    )\n));\n?>\n\n";
    }
?>
<?php endif; ?>
<?php endforeach; ?>

    <hr />
    <div class="footer-action">
        <button id="btn-submit" type="submit" class="btn btn-save btn-sm btn-primary">
            <?php echo "<?php echo Yii::t('app', 'Save'); ?>"; ?>
        </button>
    </div>

<?php echo "<?php \$this->endWidget(); ?>\n"; ?>
</div><!-- form -->
