<?php
/**
 * Màn cài đặt Quay số may mắn.
 *
 * @var LuckyDrawController $this
 * @var array $prizes     Danh sách giải (kèm awarded, eligible_types)
 * @var array $settings   key-value cấu hình
 * @var array $types      Danh sách attendee_type có trong roster nguồn
 * @var array $eventList  Dropdown sự kiện
 * @var bool  $canUpdate
 * @var bool  $canDelete
 */

$this->breadcrumbs = array('Quay số may mắn');
$this->Tabletitle  = 'Cài đặt Quay số may mắn';

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/toast.js',
    CClientScript::POS_END
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/luckydraw-settings.js?v=1.0',
    CClientScript::POS_END
);

$get = function ($key, $default = '') use ($settings) {
    return isset($settings[$key]) ? $settings[$key] : $default;
};
$excludeCols = array(
    'attendee_type'  => 'Loại người tham dự (type)',
    'is_btc'         => 'Ban tổ chức (is_btc)',
    'property_code'  => 'Mã đơn vị (property_code)',
    'division_code'  => 'Mã khối (division_code)',
    'department_code'=> 'Mã phòng (department_code)',
    'position_code'  => 'Mã chức danh (position_code)',
    'unit_label'     => 'Tên đơn vị (unit_label)',
);
$excludeCurrent = json_decode($get('exclude_columns', '[]'), true);
if (!is_array($excludeCurrent)) {
    $excludeCurrent = array();
}
?>

<div id="lucky-settings"
     data-save-prize-url="<?php echo $this->createUrl('savePrize'); ?>"
     data-delete-prize-url="<?php echo $this->createUrl('deletePrize', array('id' => '__ID__')); ?>"
     data-save-settings-url="<?php echo $this->createUrl('saveSettings'); ?>"
     data-reset-url="<?php echo $this->createUrl('resetWinners'); ?>">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="fa fa-gift me-2"></i>Cài đặt Quay số may mắn</h4>
        <div>
            <a href="<?php echo $this->createUrl('show'); ?>" target="_blank" class="btn btn-success">
                <i class="fa fa-play me-1"></i>Mở màn quay số
            </a>
            <a href="<?php echo $this->createUrl('remote'); ?>" target="_blank" class="btn btn-outline-primary">
                <i class="fa fa-gamepad me-1"></i>Điều khiển từ xa
            </a>
        </div>
    </div>

    <!-- Cấu hình chung -->
    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">Cấu hình chung</h5></div>
        <div class="card-body">
            <form id="form-settings">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Tiêu đề sự kiện</label>
                        <input type="text" name="event_title" class="form-control" value="<?php echo CHtml::encode($get('event_title', 'Quay số may mắn')); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nguồn dữ liệu (sự kiện)</label>
                        <select name="source_event_id" class="form-select">
                            <option value="">-- Tất cả sự kiện --</option>
                            <?php foreach ($eventList as $id => $name): ?>
                                <option value="<?php echo $id; ?>" <?php echo ((string) $get('source_event_id') === (string) $id) ? 'selected' : ''; ?>>
                                    <?php echo CHtml::encode($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Ứng viên lấy từ <b>final_attendee_rosters</b> của sự kiện này.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mã truy cập (access code)</label>
                        <input type="text" name="access_code" class="form-control" value="<?php echo CHtml::encode($get('access_code', 'YEP6868')); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Ảnh nền (URL)</label>
                        <input type="text" name="background_path" class="form-control" value="<?php echo CHtml::encode($get('background_path')); ?>" placeholder="để trống dùng nền mặc định">
                    </div>
                </div>

                <!-- Điều kiện loại trừ toàn cục -->
                <hr>
                <label class="form-label fw-bold">Điều kiện loại trừ (áp dụng cho mọi giải)</label>
                <p class="text-muted small mb-2">Người có cột bằng giá trị chỉ định sẽ KHÔNG được quay. Ví dụ: cột "Ban tổ chức" = 1.</p>
                <div id="exclude-rows">
                    <?php foreach ($excludeCurrent as $cond): ?>
                        <div class="row g-2 mb-2 exclude-row">
                            <div class="col-md-5">
                                <select class="form-select exclude-col">
                                    <?php foreach ($excludeCols as $col => $label): ?>
                                        <option value="<?php echo $col; ?>" <?php echo (isset($cond['column']) && $cond['column'] === $col) ? 'selected' : ''; ?>><?php echo CHtml::encode($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <input type="text" class="form-control exclude-val" placeholder="giá trị loại trừ" value="<?php echo CHtml::encode(isset($cond['value']) ? $cond['value'] : ''); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 btn-remove-exclude"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <template id="exclude-row-template">
                    <div class="row g-2 mb-2 exclude-row">
                        <div class="col-md-5">
                            <select class="form-select exclude-col">
                                <?php foreach ($excludeCols as $col => $label): ?>
                                    <option value="<?php echo $col; ?>"><?php echo CHtml::encode($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <input type="text" class="form-control exclude-val" placeholder="giá trị loại trừ">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger w-100 btn-remove-exclude"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </template>
                <button type="button" id="btn-add-exclude" class="btn btn-sm btn-outline-secondary mb-3"><i class="fa fa-plus me-1"></i>Thêm điều kiện</button>

                <?php if ($canUpdate): ?>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary" id="btn-save-settings"><i class="fa fa-save me-1"></i>Lưu cấu hình</button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Giải thưởng -->
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Giải thưởng</h5>
            <div>
                <?php if ($canUpdate): ?>
                    <button type="button" class="btn btn-primary btn-sm" id="btn-add-prize" data-bs-toggle="modal" data-bs-target="#modal-prize">
                        <i class="fa fa-plus me-1"></i>Thêm giải
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" id="btn-reset-winners">
                        <i class="fa fa-rotate-left me-1"></i>Reset kết quả
                    </button>
                <?php endif; ?>
            </div>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>Thứ tự</th>
                        <th>Tên giải</th>
                        <th>Mã (code)</th>
                        <th>SL</th>
                        <th>Đã trúng</th>
                        <th>Quay/lần</th>
                        <th>Áp dụng type</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($prizes)): ?>
                        <tr><td colspan="8" class="text-center text-muted">Chưa có giải nào.</td></tr>
                    <?php else: foreach ($prizes as $p): ?>
                        <tr data-prize='<?php echo CHtml::encode(json_encode($p, JSON_UNESCAPED_UNICODE)); ?>'>
                            <td><?php echo (int) $p['prize_order']; ?></td>
                            <td><?php echo CHtml::encode($p['prize_name']); ?></td>
                            <td><?php echo CHtml::encode($p['code']); ?></td>
                            <td><?php echo (int) $p['quantity']; ?></td>
                            <td><?php echo (int) $p['awarded']; ?></td>
                            <td><?php echo (int) $p['spin_count']; ?></td>
                            <td><?php echo empty($p['eligible_types']) ? '<span class="text-muted">Tất cả</span>' : CHtml::encode(implode(', ', $p['eligible_types'])); ?></td>
                            <td class="text-end">
                                <?php if ($canUpdate): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-prize"><i class="fa fa-edit"></i></button>
                                <?php endif; ?>
                                <?php if ($canDelete): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-del-prize" data-id="<?php echo (int) $p['id']; ?>"><i class="fa fa-trash"></i></button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php $this->renderPartial('_modal_prize', array('types' => $types)); ?>
</div>
