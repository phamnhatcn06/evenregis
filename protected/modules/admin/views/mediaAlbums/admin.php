<?php
$this->breadcrumbs = array(
    'Thư viện',
    'Album',
);
$this->menu = array(
    array(
        'label' => 'Thêm album',
        'labelIcon' => 'Thêm album',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);
$this->Tabletitle = 'Danh sách album Thư viện';
?>
<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'media-album-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'sort_order', 'header' => 'Thứ tự', 'width' => '80px'),
                array('name' => 'title', 'header' => 'Tên album'),
                array('name' => 'badge', 'header' => 'Nhãn', 'width' => '110px'),
                array(
                    'name' => 'type',
                    'header' => 'Loại',
                    'type' => 'raw',
                    'value' => function ($data) use ($typeOptions) {
                        $t = is_object($data) ? $data->type : $data['type'];
                        return isset($typeOptions[$t]) ? CHtml::encode($typeOptions[$t]) : CHtml::encode($t);
                    },
                ),
                array('name' => 'item_count', 'header' => 'Số mục', 'width' => '90px', 'filter' => false),
                array(
                    'name' => 'is_active',
                    'header' => 'Hiển thị',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        $v = is_object($data) ? $data->is_active : $data['is_active'];
                        return $v
                            ? '<span class="badge bg-success">Bật</span>'
                            : '<span class="badge bg-secondary">Tắt</span>';
                    },
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '160px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        $id = is_object($data) ? $data->id : $data['id'];
                        $buttons = IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/mediaAlbums');
                        $itemsUrl = Yii::app()->createUrl('/admin/mediaItems/admin', array('album_id' => $id));
                        $buttons .= ' <a class="btn btn-sm btn-info" href="' . $itemsUrl . '" title="Quản lý mục"><i class="fa fa-images"></i></a>';
                        return $buttons;
                    },
                ),
            ),
            'options' => array(
                'pageLength' => 25,
                'responsive' => true,
                'scrollX' => true,
                'order' => array(array(1, 'asc')),
            ),
        ));
        ?>
    </div>
</div>
