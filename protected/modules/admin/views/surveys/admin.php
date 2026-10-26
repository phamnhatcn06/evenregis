<?php
$this->breadcrumbs = array(
    'Khảo sát ý kiến',
    'Danh sách',
);
$this->menu = array(
    array(
        'label' => 'Thêm khảo sát',
        'labelIcon' => 'Thêm khảo sát',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);
$this->Tabletitle = 'Danh sách khảo sát ý kiến';

$toggleBaseUrl = $this->createUrl('toggle', array('id' => '__ID__'));
?>
<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'surveys-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'sort_order', 'header' => 'Thứ tự', 'width' => '80px'),
                array('name' => 'title', 'header' => 'Tiêu đề'),
                array(
                    'name' => 'form_url',
                    'header' => 'Link Google Form',
                    'type' => 'raw',
                    'value' => function ($data) {
                        $url = is_object($data) ? $data->form_url : $data['form_url'];
                        if (empty($url)) {
                            return '';
                        }
                        $short = mb_strlen($url, 'UTF-8') > 50 ? mb_substr($url, 0, 50, 'UTF-8') . '…' : $url;
                        return '<a href="' . CHtml::encode($url) . '" target="_blank" rel="noopener noreferrer">'
                            . CHtml::encode($short) . '</a>';
                    },
                ),
                array(
                    'name' => 'is_active',
                    'header' => 'Hiển thị',
                    'type' => 'raw',
                    'filter' => false,
                    'htmlOptions' => array('style' => 'width:110px;'),
                    'value' => function ($data) use ($toggleBaseUrl) {
                        $id = is_object($data) ? $data->id : $data['id'];
                        $v = is_object($data) ? $data->is_active : $data['is_active'];
                        $url = str_replace('__ID__', $id, $toggleBaseUrl);
                        $checked = $v ? 'checked' : '';
                        return '<div class="form-check form-switch">'
                            . '<input class="form-check-input survey-toggle" type="checkbox" role="switch" '
                            . $checked . ' data-id="' . $id . '" data-url="' . CHtml::encode($url) . '">'
                            . '</div>';
                    },
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '130px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/surveys');
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
<?php
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/surveys-admin.js',
    CClientScript::POS_END
);
?>
