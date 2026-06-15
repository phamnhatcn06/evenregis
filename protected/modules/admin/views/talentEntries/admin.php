<?php
$this->breadcrumbs = array(
    'Tiết mục văn nghệ',
    'Quản lý',
);

$this->menu = array(
    array(
        'label' => 'Đăng ký tiết mục',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => 'Xuất Excel',
        'url' => '#',
        'color' => 'success',
        'icon' => 'fa-file-excel-o',
        'id' => 'btn_export_excel',
    ),
);
$this->Tabletitle = 'Danh sách tiết mục văn nghệ';
?>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'talent-entries-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'title', 'header' => 'Tên tiết mục', 'width' => '250px'),
                array(
                    'name' => 'category_id',
                    'header' => 'Thể loại',
                    'type' => 'raw',
                    'filter' => $categories,
                    'value' => function ($data) {
                        return isset($data->category_name) ? CHtml::encode($data->category_name) : $data->category_id;
                    }
                ),
                array(
                    'name' => 'property_id',
                    'header' => 'Đơn vị',
                    'type' => 'raw',
                    'filter' => $properties,
                    'value' => function ($data) {
                        return isset($data->property_name) ? CHtml::encode($data->property_name) : $data->property_id;
                    }
                ),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'width' => '120px',
                    'type' => 'raw',
                    'filter' => TalentEntries::getStatusOptions(),
                    'value' => function ($data) {
                        return TalentEntries::getStatusLabel($data->status);
                    }
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '100px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/talentEntries');
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
                'responsive' => true,
                'scrollX' => true,
            ),
        ));
        ?>
    </div>
</div>

<?php
Yii::app()->clientScript->registerScript('talent-entries-export', "
    // Prevent filter clicks from triggering column sorting
    $('#talent-entries-grid thead').on('click', 'input, select', function(e) {
        e.stopPropagation();
    });

    $('#btn_export_excel').click(function(e) {
        e.preventDefault();
        var baseUrl = '" . $this->createUrl('export') . "';
        
        var filters = {};
        $('#talent-entries-grid thead input, #talent-entries-grid thead select').each(function() {
            var name = $(this).attr('name');
            var val = $(this).val();
            if (name && val !== '' && val !== null) {
                filters[name] = val;
            }
        });
        
        var queryString = $.param(filters);
        var exportUrl = baseUrl;
        if (queryString) {
            exportUrl += (baseUrl.indexOf('?') >= 0 ? '&' : '?') + queryString;
        }
        
        window.location.href = exportUrl;
    });
", CClientScript::POS_READY);
?>
