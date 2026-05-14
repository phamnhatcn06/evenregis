<?php
$this->breadcrumbs = array(
    'Cuộc thi nghiệp vụ' => array('admin'),
    'Thêm mới',
);

$this->menu = array(
    array(
        'label' => 'Quản lý',
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
);
$this->Tabletitle = 'Thêm cuộc thi nghiệp vụ';
?>

<div class="card">
    <div class="card-body">
        <?php echo $this->renderPartial('_form', array('model' => $model)); ?>
    </div>
</div>
