<?php
$this->breadcrumbs = array(
    'Slideshow',
    'Danh sách',
);
$this->menu = array(
    array(
        'label' => 'Thêm slide',
        'labelIcon' => 'Thêm slide',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);
$this->Tabletitle = 'Danh sách slide hero';
?>
<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'slideshow-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'sort_order', 'header' => 'Thứ tự', 'width' => '80px'),
                array('name' => 'title', 'header' => 'Tiêu đề'),
                array('name' => 'subtitle', 'header' => 'Dòng nhấn'),
                array(
                    'name' => 'theme',
                    'header' => 'Màu nhấn',
                    'type' => 'raw',
                    'value' => function ($data) use ($themeOptions) {
                        $t = is_object($data) ? $data->theme : $data['theme'];
                        return isset($themeOptions[$t]) ? CHtml::encode($themeOptions[$t]) : CHtml::encode($t);
                    },
                ),
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
                    'width' => '130px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/slideshow');
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
