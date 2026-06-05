<?php
$this->breadcrumbs = array(
    'Thí sinh Miss',
    'Quản lý',
);

$this->menu = array(
    array(
        'label' => 'Thêm thí sinh',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);
$this->Tabletitle = 'Danh sách thí sinh thi Miss';
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'beauty-contestants-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'contestant_number', 'header' => 'SBD', 'width' => '80px'),
                array(
                    'name' => 'contest_id',
                    'header' => 'Cuộc thi',
                    'type' => 'raw',
                    'filter' => $contests,
                    'value' => function ($data) {
                        return isset($data->contest_name) ? CHtml::encode($data->contest_name) : $data->contest_id;
                    }
                ),
                array(
                    'name' => 'attendee_id',
                    'header' => 'Thí sinh',
                    'type' => 'raw',
                    'value' => function ($data) {
                        if (isset($data->attendee) && isset($data->attendee['full_name'])) {
                            return CHtml::encode($data->attendee['full_name']);
                        }
                        return isset($data->attendee_name) ? CHtml::encode($data->attendee_name) : $data->attendee_id;
                    }
                ),
                array(
                    'header' => 'Mã cụm',
                    'type' => 'raw',
                    'width' => '80px',
                    'filter' => false,
                    'value' => function ($data) {
                        if (isset($data->attendee['property']['regional']['code'])) {
                            return CHtml::encode($data->attendee['property']['regional']['code']);
                        }
                        return '';
                    }
                ),
                array(
                    'header' => 'Đơn vị',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        if (isset($data->attendee) && isset($data->attendee['property']) && isset($data->attendee['property']['name'])) {
                            return CHtml::encode($data->attendee['property']['name']);
                        }
                        return isset($data->property_name) ? CHtml::encode($data->property_name) : '';
                    }
                ),
                array('name' => 'height_cm', 'header' => 'Cao (cm)', 'width' => '80px', 'filter' => false),
                array('name' => 'weight_kg', 'header' => 'Nặng (kg)', 'width' => '80px', 'filter' => false),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'width' => '120px',
                    'type' => 'raw',
                    'filter' => BeautyContestants::getStatusOptions(),
                    'value' => function ($data) {
                        return BeautyContestants::getStatusLabel($data->status);
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/beautyContestants');
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
            ),
        ));
        ?>
    </div>
</div>
