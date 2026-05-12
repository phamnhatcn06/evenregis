
<?php
$this->breadcrumbs = array(
    AttendeeRoles::label(2),
    Yii::t('app', 'Admin'),
);

$this->menu = array(
    array(
        'label' => Yii::t('app', 'Create') . ' ',
        'labelIcon' => Yii::t('app', 'Create'),
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);
$this->Tabletitle = Yii::t('app', 'List') . ' ' . $model->label();
?>
<?php
$this->widget('ext.edatatables.EDataTables', array(
    'id' => 'attendee-roles-grid',
    'dataProvider' => $model->search(),
    'language' => 'vi',
    'filter' => true,
    'columns' => array(
        array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
        array('name' => 'attendee_id', 'header' => 'Attendee Id'),
        array('name' => 'role_id', 'header' => 'Role Id'),
        array('name' => 'assigned_by', 'header' => 'Assigned By'),
        array('name' => 'assigned_at', 'header' => 'Assigned At', 'type' => 'datetime'),
        array(
            'header' => 'Thao tác',
            'width' => '100px',
            'type' => 'raw',
            'filter' => false,
            'sortable' => false,
            'value' => function ($data) {
                return IconHelper::actionButtons($data, array('view', 'update'), '/admin/attendeeRoles');
            }
        ),
    ),
    'options' => array(
        'pageLength' => 25,
        'order' => array(array(0, 'desc')),
    ),
));
?>