<?php
/**
 * Một ô dữ liệu trong bảng tổng hợp, có đánh dấu khi trường đã bị HO sửa thủ công.
 *
 * Ô đã sửa tay: viền trái cam + icon ✎ + tooltip hiện giá trị gốc trước khi sửa.
 * Việc sửa tại chỗ (inline edit) sẽ gắn vào chính các ô này ở bước sau.
 *
 * @var FinalAttendeeRosters $row
 * @var string $field       tên trường (khớp với overridden_fields ở BE)
 * @var mixed  $value       giá trị đang hiển thị
 * @var array  $overridden  danh sách trường đã sửa tay của dòng này
 */

$isOverridden = in_array($field, $overridden, true);
$display      = trim((string) $value) !== '' ? $value : '-';

if (!$isOverridden) {
    echo '<span class="js-cell" data-field="' . CHtml::encode($field) . '" data-roster-id="' . (int) $row->id . '">'
        . CHtml::encode($display) . '</span>';
    return;
}

$original   = $row->originalValue($field);
$hasOrigin  = $row->hasOriginalValue($field);
$tooltip    = 'Đã sửa tay — Gốc: '
    . (trim((string) $original) !== '' ? $original : '(để trống)');
$canRestore = isset($canUpdate) ? $canUpdate : false;
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
