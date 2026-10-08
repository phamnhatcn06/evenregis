<?php
/**
 * Modal chọn Size áo Fun Run (BẮT BUỘC). Tự bật ngay sau khi đăng ký cự ly thành
 * công; không cho bỏ qua cho tới khi người chạy chọn size. Cũng dùng lại cho việc
 * chỉnh sửa size sau này.
 * Tham số:
 *   $runMine   mảng đăng ký hiện tại (để lấy shirt_size đã chọn nếu có)
 *   $saveUrl   URL AJAX lưu size
 *   $guideImg  URL ảnh bảng hướng dẫn chọn size
 *   $mandatory bool — true: bắt buộc (không nút đóng, backdrop tĩnh)
 */
$currentSize = isset($runMine['shirt_size']) ? $runMine['shirt_size'] : '';
$mandatory = !empty($mandatory);

// Danh sách size kèm gợi ý chiều cao & cân nặng (theo bảng ước lượng Fun Run).
$sizeOptions = array(
    '7XS' => 'Trẻ em nhỏ — dưới 100 cm',
    '6XS' => 'Trẻ em nhỏ — 100-110 cm',
    '5XS' => 'Trẻ em — 110-120 cm',
    '4XS' => 'Trẻ em lớn — 120-130 cm',
    '3XS' => '30-38 kg — 1m30-1m40',
    'XXS' => '38-45 kg — 1m40-1m50',
    'XS'  => '45-52 kg — 1m50-1m60',
    'S'   => '53-60 kg — 1m60-1m67',
    'M'   => '61-68 kg — 1m65-1m72',
    'L'   => '69-76 kg — 1m70-1m77',
    'XL'  => '77-85 kg — 1m75-1m80',
    'XXL' => '86-92 kg — 1m78-1m85',
    '3XL' => '93-100 kg — 1m80-1m88',
    '4XL' => '101-110 kg — trên 1m85',
    '5XL' => 'Trên 110 kg — trên 1m85',
);
?>
<div class="modal fade" id="modalShirtSize" tabindex="-1" aria-labelledby="modalShirtSizeLabel"
     aria-hidden="true"<?php echo $mandatory ? ' data-bs-backdrop="static" data-bs-keyboard="false"' : ''; ?>>
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form id="form-shirt-size" action="<?php echo $saveUrl; ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalShirtSizeLabel">
                        <i class="bi bi-person-arms-up text-primary me-1"></i> Chọn size áo Fun Run
                    </h5>
                    <?php if (!$mandatory): ?>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    <?php endif; ?>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info d-flex align-items-start py-2 px-3 mb-3" role="alert">
                        <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                        <div class="small">
                            Đây là <strong>áo dành riêng cho nội dung Fun Run</strong>. Vui lòng chọn
                            size phù hợp — thông tin này <strong>bắt buộc</strong> và dùng để chuẩn bị áo cho bạn.
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label mb-0 fw-semibold" for="shirt_size">
                                Size áo <span class="text-danger">*</span>
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="collapse" data-bs-target="#sizeGuideCollapse"
                                    aria-expanded="false" aria-controls="sizeGuideCollapse">
                                <i class="bi bi-rulers me-1"></i> Hướng dẫn chọn size
                            </button>
                        </div>
                        <select class="form-select" id="shirt_size" name="shirt_size" required>
                            <option value="">-- Chọn size áo --</option>
                            <?php foreach ($sizeOptions as $code => $hint): ?>
                                <option value="<?php echo CHtml::encode($code); ?>"
                                    <?php echo ($currentSize === $code) ? 'selected' : ''; ?>>
                                    <?php echo CHtml::encode($code . ' (' . $hint . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">
                            <i class="bi bi-lightbulb me-1"></i>
                            Chưa chắc size? Bấm <strong>"Hướng dẫn chọn size"</strong> để xem bảng ước lượng theo chiều cao &amp; cân nặng.
                        </div>
                    </div>

                    <div class="collapse" id="sizeGuideCollapse">
                        <div class="border rounded p-2 bg-light text-center">
                            <div class="fw-semibold small mb-2">
                                <i class="bi bi-table me-1"></i> Bảng ước lượng size theo chiều cao &amp; cân nặng
                            </div>
                            <img src="<?php echo $guideImg; ?>" alt="Hướng dẫn chọn size áo Fun Run"
                                 class="img-fluid rounded" style="max-height: 70vh;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php if (!$mandatory): ?>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Đóng</button>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary" id="btn-submit-shirt-size">
                        <i class="bi bi-check-circle me-1"></i> Xác nhận size
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
