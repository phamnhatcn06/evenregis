<?php
$this->breadcrumbs = array(
    'Thí sinh Miss' => array('admin'),
    'Thêm mới',
);

$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
);
$this->Tabletitle = 'Đăng ký thí sinh thi Miss';
?>

<div class="card">
    <div class="card-body">
        <?php echo $this->renderPartial('_form', array(
            'model' => $model,
            'contests' => $contests,
            'properties' => $properties,
        )); ?>
    </div>
</div>
