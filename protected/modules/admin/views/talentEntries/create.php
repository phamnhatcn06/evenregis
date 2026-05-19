<?php
$this->breadcrumbs = array(
    'Tiết mục văn nghệ' => array('admin'),
    'Đăng ký mới',
);

$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
);
$this->Tabletitle = 'Đăng ký tiết mục văn nghệ';
?>

<div class="card">
    <div class="card-body">
        <?php echo $this->renderPartial('_form', array(
            'model' => $model,
            'shows' => $shows,
            'categories' => $categories,
            'properties' => $properties,
        )); ?>
    </div>
</div>
