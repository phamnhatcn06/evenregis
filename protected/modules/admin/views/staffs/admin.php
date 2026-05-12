<?php
$this->breadcrumbs = array(
    Staffs::label(2),
    Yii::t('app', 'Admin'),
);

$this->menu = array(
    array(
        'label' => Yii::t('app', 'Create') . ' ',
        'labelIcon' => Yii::t('app', 'Create'),
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
);
$this->Tabletitle = Yii::t('app', 'List') . ' ' . $model->label();
?>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Lọc theo đơn vị</label>
                <select name="property_code" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Tất cả đơn vị --</option>
                    <?php foreach ($properties as $code => $name): ?>
                        <option value="<?php echo CHtml::encode($code); ?>" <?php echo $selectedProperty == $code ? 'selected' : ''; ?>>
                            <?php echo CHtml::encode($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($selectedProperty): ?>
            <div class="col-auto">
                <a href="<?php echo $this->createUrl('admin'); ?>" class="btn btn-secondary">Xóa lọc</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'staffs-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false, 'sortable' => false),
                array('name' => 'code', 'header' => 'Mã NV', 'sortable' => false),
                array('name' => 'full_name', 'header' => 'Họ tên', 'sortable' => false),
                array('name' => 'property_name', 'header' => 'Khách sạn', 'sortable' => false),
                array('name' => 'division_name', 'header' => 'Bộ phận', 'sortable' => false),
                array('name' => 'position_name', 'header' => 'Chức danh', 'sortable' => false),
                array('name' => 'gender', 'header' => 'Giới tính', 'sortable' => false, 'value' => function ($data) {
                    return $data->gender == 1 ? 'Nam' : 'Nữ';
                }),
                array(
                    'name' => 'status',
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'sortable' => false,
                    'filter' => array(
                        'Hoạt động' => 'Hoạt động',
                        'Không hoạt động' => 'Không hoạt động',
                    ),
                    'value' => function ($data) {
                        return $data->status == 1
                            ? '<span class="badge bg-success">Hoạt động</span>'
                            : '<span class="badge bg-secondary">Không hoạt động</span>';
                    }
                ),
            ),
            'options' => array(
                'pageLength' => 25,
                'order' => array(array(0, 'desc')),
            ),
        ));
        ?>
    </div>
</div>
