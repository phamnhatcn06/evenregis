<?php
$this->breadcrumbs = array('Bữa ăn');
$this->menu = array(
    array(
        'label' => 'Thêm bữa ăn',
        'url' => $this->createUrl('/admin/meals/create', array('event_id' => $eventId)),
        'color' => 'primary',
        'icon' => 'fa-plus',
        'id' => 'btn_add_meal',
    ),
    array(
        'label' => 'Trạm chấm ăn',
        'url' => $this->createUrl('/admin/mealStations/admin', array('event_id' => $eventId)),
        'color' => 'info',
        'icon' => 'fa-desktop',
        'id' => 'btn_stations',
    ),
);
$this->Tabletitle = 'Danh sách bữa ăn (chấm ăn)';
?>
<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <form method="get" id="meal-filter-form">
                    <label class="form-label">Sự kiện</label>
                    <select name="event_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Tất cả sự kiện --</option>
                        <?php foreach ($eventList as $id => $name): ?>
                            <option value="<?php echo $id; ?>" <?php echo ($eventId == $id) ? 'selected' : ''; ?>>
                                <?php echo CHtml::encode($name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
            <div class="col-md-7">
                <?php if ($eventId): ?>
                    <form method="post" action="<?php echo $this->createUrl('syncFromAgenda'); ?>">
                        <input type="hidden" name="event_id" value="<?php echo (int) $eventId; ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-refresh me-1"></i>Đồng bộ bữa ăn từ chương trình (sáng/trưa/tối)
                        </button>
                    </form>
                <?php else: ?>
                    <span class="text-muted"><i class="fa fa-info-circle me-1"></i>Chọn sự kiện để đồng bộ bữa ăn từ agenda.</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php
        $this->widget('ext.edatatables.EDataTables', array(
            'id' => 'meals-grid',
            'dataProvider' => $dataProvider,
            'language' => 'vi',
            'filter' => true,
            'columns' => array(
                array('name' => 'id', 'header' => 'ID', 'width' => '60px', 'filter' => false),
                array('name' => 'name', 'header' => 'Tên bữa ăn'),
                array(
                    'header' => 'Loại',
                    'type' => 'raw',
                    'value' => function ($data) {
                        return Meals::getTypeLabel($data->meal_type);
                    },
                ),
                array('name' => 'meal_date', 'header' => 'Ngày', 'width' => '110px'),
                array(
                    'header' => 'Khung giờ chấm',
                    'type' => 'raw',
                    'value' => function ($data) {
                        $fmt = function ($v) {
                            if (!$v) return '--';
                            $ts = strtotime($v);
                            return $ts ? date('d/m H:i', $ts) : '--';
                        };
                        if (!$data->checkin_start && !$data->checkin_end) {
                            return '<span class="text-muted">Chưa cấu hình</span>';
                        }
                        return $fmt($data->checkin_start) . ' → ' . $fmt($data->checkin_end);
                    },
                ),
                array('name' => 'location', 'header' => 'Địa điểm'),
                array(
                    'header' => 'Trạng thái',
                    'type' => 'raw',
                    'width' => '150px',
                    'filter' => false,
                    'value' => function ($data) {
                        $active = (int) $data->is_active === 1;
                        $badge = $active
                            ? '<span class="badge bg-success">Đang bật</span>'
                            : '<span class="badge bg-secondary">Đang tắt</span>';
                        $formId = 'toggle_meal_' . $data->id;
                        $url = Yii::app()->createUrl('/admin/meals/toggle', array('id' => $data->id));
                        $label = $active ? 'Tắt' : 'Bật';
                        $btnCls = $active ? 'btn-outline-secondary' : 'btn-outline-success';
                        return $badge
                            . ' <form method="post" action="' . $url . '" class="d-inline" id="' . $formId . '">'
                            . '<button type="submit" class="btn btn-sm ' . $btnCls . ' ms-1">' . $label . '</button>'
                            . '</form>';
                    },
                ),
                array(
                    'header' => 'Thao tác',
                    'width' => '110px',
                    'type' => 'raw',
                    'filter' => false,
                    'sortable' => false,
                    'value' => function ($data) {
                        return IconHelper::actionButtons($data, array('update', 'delete'), '/admin/meals');
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
