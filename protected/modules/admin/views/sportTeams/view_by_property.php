<?php
$this->breadcrumbs = array(
    'Đội thể thao' => array('admin'),
    'Xem theo đơn vị',
);

$this->menu = array(
    array(
        'label' => 'Tổng quan',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
    ),
);
$this->Tabletitle = 'Đội thể thao theo đơn vị: ' . CHtml::encode($propertyName);
?>

<?php $this->renderPartial('_view_by_property', array(
    'propertyName' => $propertyName,
    'eventName' => $eventName,
    'teamsBySport' => $teamsBySport,
)); ?>
