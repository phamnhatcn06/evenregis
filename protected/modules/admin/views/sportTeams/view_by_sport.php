<?php
$this->breadcrumbs = array(
    'Đội thể thao' => array('admin'),
    'Xem theo bộ môn',
);

$this->menu = array(
    array(
        'label' => 'Tổng quan',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
    ),
);
$this->Tabletitle = 'Đội thể thao theo bộ môn: ' . CHtml::encode($sportName);
?>

<?php $this->renderPartial('_view_by_sport', array(
    'sportName' => $sportName,
    'eventName' => $eventName,
    'teamsByProperty' => $teamsByProperty,
)); ?>
