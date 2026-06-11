<?php
$this->breadcrumbs = array(
    'Đăng ký thi nghiệp vụ',
    'Quản lý',
);

$this->menu = array(
    array(
        'label' => 'Tổng quan',
        'url' => $this->createUrl('overview'),
        'color' => 'info',
        'icon' => 'fa-th-large',
    ),
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
                        if (isset($data->competition_name)) {
                            return CHtml::encode($data->competition_name);
                        }
                        if (isset($data->competition)) {
                            if (is_array($data->competition)) {
                                return CHtml::encode($data->competition['name']);
                            }
                            return CHtml::encode($data->competition->name);
                        }
                        return $data->competition_id;
                    }
                ),
                array(
                    'name' => 'attendee_id',
                    'header' => 'Thí sinh',
                    'type' => 'raw',
                    'value' => function ($data) {
                        if (isset($data->attendee_name)) {
                            return CHtml::encode($data->attendee_name);
                        }
                        if (isset($data->attendee)) {
                            if (is_array($data->attendee)) {
                                return CHtml::encode($data->attendee['full_name']);
                            }
                            return CHtml::encode($data->attendee->full_name);
                        }
                        return $data->attendee_id;
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
