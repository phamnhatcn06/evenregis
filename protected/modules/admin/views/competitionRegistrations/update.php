<?php
$this->breadcrumbs = array(
    'Đăng ký thi nghiệp vụ' => array('admin'),
    $model->candidate_number ?: 'ID: ' . $model->id => array('view', 'id' => $model->id),
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
$this->Tabletitle = 'Cập nhật đăng ký: ' . ($model->candidate_number ?: 'ID: ' . $model->id);
?>

<div class="card">
    <div class="card-body">
        <?php echo $this->renderPartial('_form', array('model' => $model, 'competitions' => $competitions)); ?>
    </div>
</div>
