<?php
$this->breadcrumbs = array(
    'Đăng ký thi nghiệp vụ',
    'Quản lý',
);

$this->menu = array(
    array(
        'label' => 'Thêm mới',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);
$this->Tabletitle = 'Danh sách đăng ký thi nghiệp vụ';
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'competition-registrations-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'candidate_number', 'header' => 'Số báo danh', 'width' => '120px'),
                array(
                    'name' => 'competition_id',
                    'header' => 'Cuộc thi',
                    'type' => 'raw',
                    'filter' => $competitions,
                    'value' => function ($data) {
                        return isset($data->competition) ? CHtml::encode($data->competition->name) : $data->competition_id;
                    }
                ),
                array(
                    'name' => 'attendee_id',
                    'header' => 'Thí sinh',
                    'type' => 'raw',
                    'value' => function ($data) {
                        return isset($data->attendee) ? CHtml::encode($data->attendee->full_name) : $data->attendee_id;
                    }
                ),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'width' => '120px',
                    'type' => 'raw',
                    'filter' => CompetitionRegistrations::getStatusOptions(),
                    'value' => function ($data) {
                        return CompetitionRegistrations::getStatusLabel($data->status);
                    }
                ),
                array(
                    'name' => 'registered_at',
                    'header' => 'Ngày đăng ký',
                    'width' => '140px',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        return MyHelper::formatDateTime($data->registered_at);
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/competitionRegistrations');
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
