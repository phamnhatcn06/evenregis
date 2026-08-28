<?php
$this->breadcrumbs = array(
    'Tin tức',
    'Danh sách',
);

$this->menu = array(
    array(
        'label' => 'Thêm tin tức',
        'labelIcon' => 'Thêm tin tức',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => 'Danh mục tin',
        'labelIcon' => 'Danh mục tin',
        'url' => $this->createUrl('/admin/newsCategories/admin'),
        'color' => 'info',
        'icon' => 'fa-tags',
        'id' => 'btn_categories',
    ),
);
$this->Tabletitle = 'Danh sách tin tức';
?>
<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'news-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'title', 'header' => 'Tiêu đề'),
                array(
                    'name' => 'category',
                    'header' => 'Loại tin',
                    'type' => 'raw',
                    'value' => function ($data) use ($categoryOptions) {
                        $cat = is_object($data) ? $data->category : $data['category'];
                        return isset($categoryOptions[$cat]) ? CHtml::encode($categoryOptions[$cat]) : CHtml::encode($cat);
                    },
                ),
                array(
                    'name' => 'is_featured',
                    'header' => 'Nổi bật',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        return News::getFeaturedLabel(is_object($data) ? $data->is_featured : $data['is_featured']);
                    },
                ),
                array(
                    'name' => 'is_published',
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'filter' => false,
                    'value' => function ($data) {
                        return News::getPublishedLabel(is_object($data) ? $data->is_published : $data['is_published']);
                    },
                ),
                array('name' => 'view_count', 'header' => 'Lượt xem', 'width' => '90px', 'filter' => false),
                array('name' => 'published_at', 'header' => 'Ngày xuất bản', 'filter' => false),
                array(
                    'header' => 'Thao tác',
                    'width' => '130px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/news');
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
                'responsive' => true,
                'scrollX' => true,
                'order' => array(array(0, 'desc')),
            ),
        ));
        ?>
    </div>
</div>
