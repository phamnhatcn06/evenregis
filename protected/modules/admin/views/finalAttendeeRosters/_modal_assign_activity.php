<?php
/**
 * Modal: Admin gán người vào nội dung Fun Run và/hoặc Tham quan.
 *
 * Xác nhận năm sinh + giới tính, chọn cự ly chạy và/hoặc đợt tham quan. Gán Fun Run bằng
 * tài khoản admin cấp BIB từ dải giữ chỗ (bắt đầu từ 1), không tính suất công khai.
 *
 * @var FinalAttendeeRostersController $this
 * @var array $runEventList    Mỗi phần tử: ['id','name','code']
 * @var array $tourSessionList Mỗi phần tử: ['id','name','start_time']
 */

$currentYear = (int) date('Y');
$minYear     = 1950;
?>
<div class="modal fade" id="modal_assign_activity" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden;">
            <div class="modal-header text-white" style="background: linear-gradient(135deg, #1aaa4d 0%, #118138 100%);">
                <h5 class="modal-title text-white d-flex align-items-center gap-2" style="font-size: 16px; font-weight: 700;">
                    <span class="d-inline-flex align-items-center justify-content-center bg-white text-success rounded-circle" style="width: 32px; height: 32px; font-size: 14px;">
                        <i class="fa fa-flag-checkered"></i>
                    </span>
                    <span>Gán Vào Nội Dung Fun Run / Tham Quan</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Thông tin người được gán -->
                <div class="p-3 mb-3 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small fw-semibold">Người được gán:</span>
                        <span class="fw-bold text-dark fs-6" id="assign_target_name">-</span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted small fw-semibold">Đơn vị:</span>
                        <span class="text-secondary small fw-medium" id="assign_target_unit">-</span>
                    </div>
                </div>

                <form id="form_assign_activity" onsubmit="return false;">
                    <input type="hidden" id="assign_id" value="">

                    <!-- Xác nhận năm sinh + giới tính -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="assign_birth_year" class="form-label fw-bold text-dark mb-1">
                                Năm sinh <span class="text-danger">*</span>
                            </label>
                            <select id="assign_birth_year" class="form-select">
                                <option value="">-- Chọn năm sinh --</option>
                                <?php for ($y = $currentYear; $y >= $minYear; $y--): ?>
                                    <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1">
                                Giới tính <span class="text-danger">*</span>
                            </label>
                            <div class="d-flex gap-3 pt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="assign_gender" id="assign_gender_male" value="1">
                                    <label class="form-check-label" for="assign_gender_male">Nam</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="assign_gender" id="assign_gender_female" value="0">
                                    <label class="form-check-label" for="assign_gender_female">Nữ</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small mb-3" style="border: none; background: rgba(13,110,253,0.08);">
                        <i class="fa fa-info-circle me-1"></i>
                        Năm sinh và giới tính dùng để xác định nhóm nội dung chạy. BIB Fun Run cấp bằng tài khoản admin
                        sẽ lấy từ <strong>dải số giữ chỗ (bắt đầu từ 1)</strong>, không tính vào suất công khai.
                    </div>

                    <div class="row g-3">
                        <!-- Chọn cự ly Fun Run -->
                        <?php if (!empty($runEventList)): ?>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-2">
                                <i class="fa fa-trophy text-warning me-1"></i>Cự ly Fun Run
                            </label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="assign_run_event_id" id="assign_run_none" value="" checked>
                                    <label class="form-check-label text-muted" for="assign_run_none">— Không gán Fun Run —</label>
                                </div>
                                <?php foreach ($runEventList as $ev): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="assign_run_event_id"
                                               id="assign_run_<?php echo (int) $ev['id']; ?>" value="<?php echo (int) $ev['id']; ?>">
                                        <label class="form-check-label" for="assign_run_<?php echo (int) $ev['id']; ?>">
                                            <?php echo CHtml::encode($ev['name']); ?>
                                            <?php if (!empty($ev['code'])): ?>
                                                <span class="text-muted small">(<?php echo CHtml::encode($ev['code']); ?>)</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Chọn đợt Tham quan -->
                        <?php if (!empty($tourSessionList)): ?>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-2">
                                <i class="fa fa-bus text-info me-1"></i>Đợt Tham quan
                            </label>
                            <div class="d-flex flex-column gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="assign_tour_session_id" id="assign_tour_none" value="" checked>
                                    <label class="form-check-label text-muted" for="assign_tour_none">— Không gán Tham quan —</label>
                                </div>
                                <?php foreach ($tourSessionList as $s): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="assign_tour_session_id"
                                               id="assign_tour_<?php echo (int) $s['id']; ?>" value="<?php echo (int) $s['id']; ?>">
                                        <label class="form-check-label" for="assign_tour_<?php echo (int) $s['id']; ?>">
                                            <?php echo CHtml::encode($s['name']); ?>
                                            <?php if (!empty($s['start_time'])): ?>
                                                <span class="text-muted small">(<?php echo CHtml::encode($s['start_time']); ?>)</span>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="modal-footer bg-light px-4 py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i> Đóng
                </button>
                <button type="button" class="btn btn-success px-4 fw-semibold" id="btn_submit_assign">
                    <i class="fa fa-check me-1"></i> <span id="btn_submit_assign_text">Gán nội dung</span>
                </button>
            </div>
        </div>
    </div>
</div>
