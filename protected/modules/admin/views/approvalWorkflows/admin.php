<?php
$this->breadcrumbs = array(
    'Quy trình duyệt',
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
$this->Tabletitle = 'Danh sách quy trình duyệt';
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'approval-workflows-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'code', 'header' => 'Mã', 'width' => '120px'),
                array('name' => 'name', 'header' => 'Tên quy trình'),
                array(
                    'name' => 'total_steps',
                    'header' => 'Số bước',
                    'width' => '80px',
                    'filter' => false,
                    'type' => 'raw',
                    'value' => function ($data) {
                        return '<span class="badge bg-info">' . $data->total_steps . '</span>';
                    }
                ),
                array(
                    'name' => 'is_default',
                    'header' => 'Mặc định',
                    'width' => '90px',
                    'type' => 'raw',
                    'filter' => array('1' => 'Có', '0' => 'Không'),
                    'value' => function ($data) {
                        return $data->is_default ? '<span class="badge bg-success">Mặc định</span>' : '';
                    }
                ),
                array(
                    'name' => 'is_active',
                    'header' => 'Trạng thái',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => array('1' => 'Hoạt động', '0' => 'Không hoạt động'),
                    'value' => function ($data) {
                        return $data->is_active
                            ? '<span class="badge bg-success">Hoạt động</span>'
                            : '<span class="badge bg-secondary">Không hoạt động</span>';
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/approvalWorkflows');
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
