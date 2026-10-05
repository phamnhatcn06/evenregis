<?php
$this->breadcrumbs = array(
    'Thư viện' => $this->createUrl('/admin/mediaAlbums/admin'),
    CHtml::encode($album->title) => $this->createUrl('/admin/mediaAlbums/view', array('id' => $album->id)),
    'Các mục',
);
$this->menu = array(
    array(
        'label' => 'Thêm mục',
        'labelIcon' => 'Thêm mục',
        'url' => $this->createUrl('create', array('album_id' => $album->id)),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => 'Về album',
        'labelIcon' => 'Về album',
        'url' => $this->createUrl('/admin/mediaAlbums/view', array('id' => $album->id)),
        'color' => 'info',
        'icon' => 'fa-arrow-left',
        'id' => 'btn_back',
    ),
);
$this->Tabletitle = 'Mục trong album: ' . CHtml::encode($album->title);
$albumId = $album->id;
?>
<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'media-item-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'sort_order', 'header' => 'Thứ tự', 'width' => '80px'),
                array(
                    'header' => 'Xem trước',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'width' => '90px',
                    'value' => function ($data) {
                        $type = is_object($data) ? $data->type : $data['type'];
                        $url = is_object($data) ? $data->url : $data['url'];
                        $thumb = is_object($data) ? $data->thumbnail : (isset($data['thumbnail']) ? $data['thumbnail'] : '');
                        $src = $thumb !== '' ? $thumb : $url;
                        if ($type === 'image' && $src) {
                            return '<img src="' . CHtml::encode($src) . '" style="width:64px;height:40px;object-fit:cover;border-radius:4px;">';
                        }
                        return '<i class="fa fa-film"></i>';
                    },
                ),
                array('name' => 'type', 'header' => 'Loại', 'width' => '90px'),
                array('name' => 'title', 'header' => 'Tiêu đề'),
                array(
                    'name' => 'is_active',
                    'header' => 'Hiển thị',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        $v = is_object($data) ? $data->is_active : $data['is_active'];
                        return $v ? '<span class="badge bg-success">Bật</span>' : '<span class="badge bg-secondary">Tắt</span>';
                    },
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '130px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/mediaItems');
                    },
                ),
            ),
            'options' => array(
                'pageLength' => 50,
                'responsive' => true,
                'scrollX' => true,
                'order' => array(array(1, 'asc')),
            ),
        ));
        ?>
    </div>
</div>
