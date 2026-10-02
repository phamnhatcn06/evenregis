<?php
$this->breadcrumbs = array('Nội dung chạy');
$this->menu = array(
    array(
        'label' => 'Thêm nội dung',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => 'Danh sách đăng ký',
        'url' => $this->createUrl('/admin/runRegistrations/admin'),
        'color' => 'info',
        'icon' => 'fa-list',
        'id' => 'btn_regs',
    ),
);
$this->Tabletitle = 'Danh sách nội dung chạy';
?>
<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'run-events-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'name', 'header' => 'Tên nội dung'),
                array('name' => 'code', 'header' => 'Mã'),
                array(
                    'header' => 'Giới hạn',
                    'type' => 'raw',
                    'value' => function ($data) {
                        $remaining = isset($data->remaining) ? (int) $data->remaining : max(0, (int) $data->quota - (int) $data->registered_count);
                        $cls = $remaining <= 0 ? 'bg-danger' : ($remaining <= 5 ? 'bg-warning text-dark' : 'bg-success');
                        return (int) $data->registered_count . ' / ' . (int) $data->quota
                            . ' <span class="badge ' . $cls . '">còn ' . $remaining . '</span>';
                    },
                ),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'filter' => array('open' => 'Đang mở', 'closed' => 'Đã đóng'),
                    'value' => function ($data) {
                        return RunEvents::getStatusLabel($data->status);
                    },
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '120px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/runEvents');
                    },
                ),
            ),
            'options' => array(
                'pageLength' => 50,
                'responsive' => true,
                'scrollX' => true,
            ),
        ));
        ?>
    </div>
</div>
