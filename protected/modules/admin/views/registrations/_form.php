<?php
$eventList = array();
foreach ($events as $e) {
    $eId = isset($e['id']) ? $e['id'] : (isset($e->id) ? $e->id : null);
    $eName = isset($e['name']) ? $e['name'] : (isset($e->name) ? $e->name : '');
    if ($eId) {
        $eventList[$eId] = $eName;
    }
}

$propertyList = array();
foreach ($properties as $p) {
    $pId = isset($p['id']) ? $p['id'] : (isset($p->id) ? $p->id : null);
    $pName = isset($p['name']) ? $p['name'] : (isset($p->name) ? $p->name : '');
    if ($pId) {
        $propertyList[$pId] = $pName;
    }
}

$relationPropertyList = array();
if (isset($relationProperties)) {
    foreach ($relationProperties as $p) {
        $pId = isset($p['id']) ? $p['id'] : (isset($p->id) ? $p->id : null);
        $pName = isset($p['name']) ? $p['name'] : (isset($p->name) ? $p->name : '');
        if ($pId && $pId != $model->property_id) {
            $relationPropertyList[$pId] = $pName;
        }
    }
}

$isAdmin = isset($isAdmin) ? $isAdmin : false;
?>

<div class="form-wrap">
    <?php $form = $this->beginWidget('CActiveForm', array(
        'id' => 'registrations-form',
        'htmlOptions' => array('enctype' => 'multipart/form-data'),
        'enableClientValidation' => false,
    )); ?>

    <?php echo $form->errorSummary($model); ?>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'event_id'); ?>
                <?php echo $form->dropDownList($model, 'event_id', $eventList, array(
                    'class' => 'form-select',
                    'prompt' => '-- Chọn sự kiện --',
                )); ?>
                <?php echo $form->error($model, 'event_id'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'period_id'); ?>
                <?php echo $form->dropDownList($model, 'period_id', $periods, array(
                    'class' => 'form-select',
                    'prompt' => '-- Chọn đợt đăng ký --',
                )); ?>
                <?php echo $form->error($model, 'period_id'); ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'property_id'); ?>
                <?php echo $form->dropDownList($model, 'property_id', $propertyList, array(
                    'class' => 'form-select',
                    'prompt' => '-- Chọn đơn vị --',
                )); ?>
                <?php echo $form->error($model, 'property_id'); ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group mb-3">
                <?php echo $form->labelEx($model, 'relation_property_id'); ?>
                <?php echo $form->dropDownList($model, 'relation_property_id', $propertyList, array(
                    'class' => 'form-select',
                    'prompt' => '-- Không có (liên quân) --',
                )); ?>
                <?php echo $form->error($model, 'relation_property_id'); ?>
                <small class="text-muted">Chọn đơn vị liên quân nếu có</small>
            </div>
        </div>
    </div>

    <div class="form-group mb-3">
        <?php echo $form->labelEx($model, 'document'); ?>
        <?php echo $form->textField($model, 'document', array(
            'class' => 'form-control',
            'maxlength' => 500,
            'placeholder' => 'Link tài liệu đính kèm (công văn)',
        )); ?>
        <?php echo $form->error($model, 'document'); ?>
    </div>

    <div class="form-group mb-3">
        <?php echo $form->labelEx($model, 'note'); ?>
        <?php echo $form->textArea($model, 'note', array(
            'class' => 'form-control',
            'rows' => 3,
            'placeholder' => 'Ghi chú thêm',
        )); ?>
        <?php echo $form->error($model, 'note'); ?>
    </div>

    <hr />
    <div class="footer-action">
        <button id="btn-submit" type="submit" class="btn btn-sm btn-primary">
            <i class="fa fa-save me-1"></i>Lưu lại
        </button>
        <a href="<?php echo $this->createUrl('admin'); ?>" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left me-1"></i>Quay lại
        </a>
    </div>

    <?php $this->endWidget(); ?>
</div>
