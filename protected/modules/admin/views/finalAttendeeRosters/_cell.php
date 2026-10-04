<?php
/**
 * Một ô dữ liệu trong bảng tổng hợp, có đánh dấu khi trường đã bị HO sửa thủ công.
 *
 * Ô đã sửa tay: viền trái cam + icon ✎ + tooltip hiện giá trị gốc trước khi sửa.
 * Hỗ trợ inline edit tại chỗ khi có biến $isEditable = true và $canUpdate = true.
 *
 * @var FinalAttendeeRosters $row
 * @var string $field       tên trường (khớp với overridden_fields ở BE)
 * @var mixed  $value       giá trị đang hiển thị
 * @var array  $overridden  danh sách trường đã sửa tay của dòng này
 * @var bool   $canUpdate   quyền sửa
 * @var bool   $isEditable  bật chế độ inline edit cho ô này
 */

$isOverridden = in_array($field, $overridden, true);
$display      = trim((string) $value) !== '' ? $value : '-';
$rawVal       = trim((string) $value) !== '' ? $value : '';
$canRestore   = isset($canUpdate) ? $canUpdate : false;
$editable     = !empty($isEditable) && $canRestore;

$original   = $row->originalValue($field);
$hasOrigin  = $row->hasOriginalValue($field);
$tooltip    = $isOverridden ? ('Đã sửa tay — Gốc: ' . (trim((string) $original) !== '' ? $original : '(để trống)')) : '';

if ($editable) {
?>
<div class="far-inline-editable-wrapper js-inline-editable"
     data-field="<?php echo CHtml::encode($field); ?>"
     data-roster-id="<?php echo (int) $row->id; ?>"
     data-value="<?php echo CHtml::encode($rawVal); ?>"
     data-original="<?php echo CHtml::encode((string) $original); ?>"
     data-has-origin="<?php echo $hasOrigin ? '1' : '0'; ?>">

    <!-- Chế độ xem -->
    <div class="far-inline-view js-inline-view <?php echo $isOverridden ? 'far-cell-overridden' : ''; ?>"
         title="<?php echo $isOverridden ? CHtml::encode($tooltip) : 'Bấm để sửa nhanh chức danh'; ?>">
        <span class="far-inline-text js-inline-text"><?php echo CHtml::encode($display); ?></span>
        <button type="button" class="far-inline-edit-btn js-inline-trigger" title="Sửa nhanh chức danh">
            <i class="fa fa-pencil"></i>
        </button>
        <?php if ($canRestore && $hasOrigin && $isOverridden): ?>
            <button type="button" class="far-cell-reset-btn js-cell-reset"
                    data-field="<?php echo CHtml::encode($field); ?>"
                    data-roster-id="<?php echo (int) $row->id; ?>"
                    data-field-label="<?php echo CHtml::encode($field); ?>"
                    title="Khôi phục về giá trị gốc: <?php echo CHtml::encode($original); ?>">
                <i class="fa fa-undo"></i>
            </button>
        <?php endif; ?>
    </div>

    <!-- Chế độ sửa tại chỗ (ẩn mặc định) -->
    <div class="far-inline-editor js-inline-editor d-none">
        <div class="input-group input-group-sm">
            <input type="text" class="form-control form-control-sm far-inline-input js-inline-input"
                   value="<?php echo CHtml::encode($rawVal); ?>"
                   placeholder="Nhập chức danh...">
            <button type="button" class="btn btn-primary far-inline-save-btn js-inline-save" title="Lưu (Enter)">
                <i class="fa fa-check"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary far-inline-cancel-btn js-inline-cancel" title="Hủy (Esc)">
                <i class="fa fa-times"></i>
            </button>
        </div>
    </div>
</div>
<?php
    return;
}

if (!$isOverridden) {
    echo '<span class="js-cell" data-field="' . CHtml::encode($field) . '" data-roster-id="' . (int) $row->id . '">'
        . CHtml::encode($display) . '</span>';
    return;
}
?>
<span class="js-cell far-cell-overridden"
      data-field="<?php echo CHtml::encode($field); ?>"
      data-roster-id="<?php echo (int) $row->id; ?>"
      data-original="<?php echo CHtml::encode((string) $original); ?>"
      title="<?php echo CHtml::encode($tooltip); ?>">
    <span><?php echo CHtml::encode($display); ?></span>
    <i class="fa fa-pencil text-warning ms-1" style="font-size: 11px;"></i>
    <?php if ($canRestore && $hasOrigin): ?>
        <button type="button" class="far-cell-reset-btn js-cell-reset"
                data-field="<?php echo CHtml::encode($field); ?>"
                data-roster-id="<?php echo (int) $row->id; ?>"
                data-field-label="<?php echo CHtml::encode($field); ?>"
                title="Khôi phục về giá trị gốc: <?php echo CHtml::encode($original); ?>">
            <i class="fa fa-undo"></i>
        </button>
    <?php endif; ?>
</span>
