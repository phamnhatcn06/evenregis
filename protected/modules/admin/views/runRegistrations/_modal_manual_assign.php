<?php
/**
 * Modal: Admin gán thủ công một người vào một cự ly Fun Run với mã BIB tự điền.
 *
 * Luồng: tìm & chọn người -> xác nhận năm sinh + giới tính (prefill nếu đã biết) ->
 * chọn cự ly -> nhập BIB. Backend kiểm tra BIB trùng trước khi lưu.
 *
 * @var RunRegistrationsController $this
 * @var array $runEventList Mỗi phần tử: ['id','name','code']
 */

$currentYear = (int) date('Y');
$minYear     = 1950;
?>
<div class="modal fade" id="modal_manual_assign" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
                <h5 class="modal-title text-white d-flex align-items-center gap-2" style="font-size: 16px; font-weight: 700;">
                    <i class="fa fa-user-plus"></i>
                    <span>Gán thủ công vào nội dung chạy</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body p-4">
                <form id="form_manual_assign" onsubmit="return false;">
                    <input type="hidden" id="manual_attendee_id" value="">

                    <!-- Bước 1: Tìm & chọn người -->
                    <div class="mb-3 position-relative">
                        <label for="manual_search" class="form-label fw-bold text-dark mb-1">
                            Tìm người <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="manual_search" class="form-control" autocomplete="off"
                               placeholder="Nhập tên / mã nhân viên / mã định danh...">
                        <div id="manual_search_results" class="list-group position-absolute w-100 shadow-sm"
                             style="z-index: 1080; max-height: 260px; overflow-y: auto; display: none;"></div>
                    </div>

                    <div class="p-3 mb-3 rounded-3" id="manual_selected_box" style="background: #f8fafc; border: 1px solid #e2e8f0; display: none;">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="text-muted small fw-semibold">Người được gán:</span>
                            <span class="fw-bold text-dark fs-6" id="manual_target_name">-</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted small fw-semibold">Đơn vị:</span>
                            <span class="text-secondary small fw-medium" id="manual_target_unit">-</span>
                        </div>
                    </div>

                    <!-- Bước 2: Năm sinh + giới tính -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="manual_birth_year" class="form-label fw-bold text-dark mb-1">
                                Năm sinh <span class="text-danger">*</span>
                            </label>
                            <select id="manual_birth_year" class="form-select">
                                <option value="">-- Chọn năm sinh --</option>
                                <?php for ($y = $currentYear; $y >= $minYear; $y--): ?>
                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                            <div class="form-text text-danger fw-semibold mt-1" id="manual_birth_year_note" style="display: none;">
                                <i class="fa fa-exclamation-triangle me-1"></i>Không thấy dữ liệu năm sinh — vui lòng chọn.
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">
                                Giới tính <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex gap-3 pt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="manual_gender" id="manual_gender_male" value="1">
                                    <label class="form-check-label" for="manual_gender_male">Nam</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="manual_gender" id="manual_gender_female" value="0">
                                    <label class="form-check-label" for="manual_gender_female">Nữ</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small mb-3" style="border: none; background: rgba(13,110,253,0.08);">
                        <i class="fa fa-info-circle me-1"></i>
                        Năm sinh và giới tính dùng để tự xác định nhóm nội dung chạy theo cự ly đã chọn.
                    </div>

                    <!-- Bước 3: Cự ly + mã BIB -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-2">
                                <i class="fa fa-trophy text-warning me-1"></i>Cự ly <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($runEventList as $ev): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="manual_run_event_id"
                                               id="manual_run_<?php echo (int) $ev['id']; ?>" value="<?php echo (int) $ev['id']; ?>">
                                        <label class="form-check-label" for="manual_run_<?php echo (int) $ev['id']; ?>">
                                            <?php echo CHtml::encode($ev['name']); ?>
                                            <?php if (!empty($ev['code'])): ?>
                                                <span class="text-muted small">(<?php echo CHtml::encode($ev['code']); ?>)</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="manual_bib" class="form-label fw-bold text-dark mb-2">
                                <i class="fa fa-hashtag text-secondary me-1"></i>Mã BIB
                                <span class="text-muted small fw-normal">(tùy chọn)</span>
                            </label>
                            <input type="text" id="manual_bib" class="form-control" autocomplete="off"
                                   placeholder="Để trống để tự cấp (vd 5010)">
                            <div class="form-text">Nhập mã thì kiểm tra trùng trước khi lưu; để trống thì hệ thống tự cấp theo quy luật.</div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i> Đóng
                </button>
                <button type="button" class="btn btn-primary px-4 fw-semibold" id="btn_submit_manual_assign">
                    <i class="fa fa-check me-1"></i> Gán vào nội dung
                </button>
            </div>
        </div>
    </div>
</div>
