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
    array(
        'label' => 'Xuất Excel',
        'url' => $this->createUrl('exportBySport', array('event_id' => $eventId, 'sport_id' => $sportId)),
        'color' => 'success',
        'icon' => 'fa-file-excel-o',
    ),
);
$this->Tabletitle = 'Đội thể thao theo bộ môn: ' . CHtml::encode($sportName);
?>

<?php $this->renderPartial('_view_by_sport', array(
    'sportName' => $sportName,
    'eventName' => $eventName,
    'eventId' => $eventId,
    'sportId' => $sportId,
    'teamsByRegion' => $teamsByRegion,
    'regionList' => $regionList,
    'groupedSports' => $groupedSports,
)); ?>
