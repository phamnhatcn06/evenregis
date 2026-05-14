<?php
$this->breadcrumbs = array(
    'Cuộc thi nghiệp vụ' => array('admin'),
    $model->name => array('view', 'id' => $model->id),
    'Cập nhật',
);

$this->menu = array(
    array(
        'label' => 'Quản lý',
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
    array(
        'label' => 'Xem chi tiết',
        'url' => $this->createUrl('view', array('id' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-eye',
        'id' => 'btn_view',
    ),
);
$this->Tabletitle = 'Cập nhật: ' . $model->name;
?>

<div class="card">
    <div class="card-body">
        <?php echo $this->renderPartial('_form', array('model' => $model)); ?>
    </div>
</div>
