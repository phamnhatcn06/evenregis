<?php
$this->breadcrumbs = array(
    'Cuộc thi nghiệp vụ',
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
$this->Tabletitle = 'Danh sách cuộc thi nghiệp vụ';
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'competitions-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'name', 'header' => 'Tên cuộc thi'),
                array('name' => 'candidate_number_prefix', 'header' => 'Tiền tố SBD', 'width' => '100px'),
                array(
                    'name' => 'max_per_org',
                    'header' => 'Giới hạn/ĐV',
                    'width' => '100px',
                    'type' => 'raw',
                    'value' => function ($data) {
                        return $data->max_per_org ? $data->max_per_org : '<span class="text-muted">Không giới hạn</span>';
                    }
                ),
                array(
                    'name' => 'has_qualification',
                    'header' => 'Vòng loại',
                    'width' => '90px',
                    'type' => 'raw',
                    'filter' => array('1' => 'Có', '0' => 'Không'),
                    'value' => function ($data) {
                        return $data->has_qualification ? '<span class="badge bg-info">Có</span>' : '<span class="badge bg-secondary">Không</span>';
                    }
                ),
                array(
                    'name' => 'is_active',
                    'header' => 'Trạng thái',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => array('1' => 'Hoạt động', '0' => 'Không hoạt động'),
                    'value' => function ($data) {
                        return Competitions::getStatusLabel($data->is_active);
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/competitions');
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
