<?php
$this->breadcrumbs = array('Trạm chấm ăn');
$this->menu = array(
    array(
        'label' => 'Thêm trạm',
        'url' => $this->createUrl('create', array('event_id' => $eventId)),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => 'Bữa ăn',
        'url' => $this->createUrl('/admin/meals/admin', array('event_id' => $eventId)),
        'color' => 'info',
        'icon' => 'fa-utensils',
        'id' => 'btn_meals',
    ),
);
$this->Tabletitle = 'Danh sách trạm chấm ăn';
?>
<div class="card mb-3">
    <div class="card-body">
        <form method="get">
            <label class="form-label">Sự kiện</label>
            <select name="event_id" class="form-select" style="max-width:420px" onchange="this.form.submit()">
                <option value="">-- Tất cả sự kiện --</option>
                <?php foreach ($eventList as $id => $name): ?>
                    <option value="<?php echo $id; ?>" <?php echo ($eventId == $id) ? 'selected' : ''; ?>>
                        <?php echo CHtml::encode($name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'meal-stations-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'name', 'header' => 'Tên trạm / cửa'),
                array('name' => 'code', 'header' => 'Mã'),
                array('name' => 'location', 'header' => 'Địa điểm'),
                array(
                    'header' => 'Mã truy cập',
                    'type' => 'raw',
                    'width' => '120px',
                    'filter' => false,
                    'value' => function ($data) {
                        return !empty($data->has_passcode)
                            ? '<span class="badge bg-success">Đã đặt</span>'
                            : '<span class="badge bg-danger">Chưa đặt</span>';
                    },
                ),
                array(
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'width' => '110px',
                    'filter' => false,
                    'value' => function ($data) {
                        return (int) $data->is_active === 1
                            ? '<span class="badge bg-success">Đang bật</span>'
                            : '<span class="badge bg-secondary">Đang tắt</span>';
                    },
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '110px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('update', 'delete'), '/admin/mealStations');
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
