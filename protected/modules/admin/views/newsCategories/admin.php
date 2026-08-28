<?php
$this->breadcrumbs = array(
    'Danh mục tin tức',
    'Danh sách',
);

$this->menu = array(
    array(
        'label' => 'Thêm danh mục',
        'labelIcon' => 'Thêm danh mục',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => 'Danh sách tin tức',
        'labelIcon' => 'Danh sách tin tức',
        'url' => $this->createUrl('/admin/news/admin'),
        'color' => 'info',
        'icon' => 'fa-newspaper-o',
        'id' => 'btn_news',
    ),
);
$this->Tabletitle = 'Danh mục tin tức';
?>
<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'news-categories-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'name', 'header' => 'Tên danh mục'),
                array('name' => 'slug', 'header' => 'Slug'),
                array(
                    'name' => 'icon',
                    'header' => 'Biểu tượng',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        $icon = is_object($data) ? $data->icon : $data['icon'];
                        return $icon ? '<i class="fa ' . CHtml::encode($icon) . '"></i> ' . CHtml::encode($icon) : '';
                    },
                ),
                array(
                    'name' => 'color',
                    'header' => 'Màu',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        $color = is_object($data) ? $data->color : $data['color'];
                        if (!$color) return '';
                        return '<span style="display:inline-block;width:16px;height:16px;border-radius:3px;background:' . CHtml::encode($color) . ';vertical-align:middle;"></span> ' . CHtml::encode($color);
                    },
                ),
                array('name' => 'sort_order', 'header' => 'Thứ tự', 'width' => '90px', 'filter' => false),
                array(
                    'name' => 'is_active',
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        return NewsCategories::getActiveLabel(is_object($data) ? $data->is_active : $data['is_active']);
                    },
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '130px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/newsCategories');
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
                'responsive' => true,
                'scrollX' => true,
                'order' => array(array(5, 'asc')),
            ),
        ));
        ?>
    </div>
</div>
