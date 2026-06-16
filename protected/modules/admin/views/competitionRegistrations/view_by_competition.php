<?php
$this->breadcrumbs = array(
    'Thi nghiệp vụ' => array('admin'),
    'Xem theo nghiệp vụ',
);

$this->menu = array(
    array(
        'label' => 'Tổng quan',
        'url' => $this->createUrl('overview'),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
    ),
);
$this->Tabletitle = 'Thí sinh thi nghiệp vụ: ' . CHtml::encode($competitionName);
?>

<?php $this->renderPartial('_view_by_competition', array(
    'competitionName' => $competitionName,
    'eventName' => $eventName,
    'eventId' => $eventId,
    'competitionId' => $competitionId,
    'contestantsByRegion' => $contestantsByRegion,
    'regionList' => $regionList,
    'positionList' => $positionList,
    'competitionsList' => $competitionsList,
)); ?>
