<?php
/**
 * Khối đăng ký Chạy bộ (Fun Run) — Giao diện chuyên nghiệp, tích hợp icon & Race BIB Card.
 * Tham số: $runMine, $runEvents, $fullName (optional), $registrationWindowText (optional)
 */
$registrationWindowText = isset($registrationWindowText) ? $registrationWindowText : '';
?>
<div class="activity-card h-100">
    <div class="activity-card-header-run d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 text-white fw-bold d-flex align-items-center">
                <i class="bi bi-trophy-fill text-warning me-2"></i> Chạy bộ (Fun Run)
            </h5>
            <small class="text-white-50">Chinh phục đường đua Vòng Chung Kết</small>
        </div>
        <span class="activity-badge">
            <i class="bi bi-check-circle me-1"></i> Tối đa 1 cự ly
        </span>
    </div>

    <?php if ($registrationWindowText !== ''): ?>
        <div class="d-flex align-items-start gap-2 px-3 px-md-4 py-2 border-bottom bg-light text-secondary small">
            <i class="bi bi-calendar-event text-success mt-1"></i>
            <span><?php echo CHtml::encode($registrationWindowText); ?></span>
        </div>
    <?php endif; ?>

    <div class="p-3 p-md-4">
        <?php if (!empty($runMine)): ?>
            <?php
            $isPending = (isset($runMine['status']) && $runMine['status'] === 'cancel_requested');
            $regTime = !empty($runMine['registered_at'])
                ? (is_numeric($runMine['registered_at']) ? date('H:i d/m/Y', (int)$runMine['registered_at']) : CHtml::encode($runMine['registered_at']))
                : date('d/m/Y');
            $bib = isset($runMine['bib_number']) ? $runMine['bib_number'] : '';
            ?>
            <!-- Thẻ Vận Động Viên (Race BIB Card) -->
            <div class="race-bib-ticket">
                <div class="race-bib-strip"></div>
                
                <div class="race-bib-header">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-award-fill text-primary fs-5 me-2"></i>
                        <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">Thẻ VĐV Chính Thức</span>
                    </div>
                    <?php if ($isPending): ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Chờ duyệt hủy</span>
                    <?php else: ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-shield-check me-1"></i> Đã xác nhận</span>
                    <?php endif; ?>
                </div>

                <div class="p-3">
                    <div class="bib-number-box">
                        <div class="text-white-50 text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.12em;">Số BIB Của Bạn</div>
                        <div class="bib-number-text my-1"><?php echo CHtml::encode($bib ?: 'N/A'); ?></div>
                        <div class="text-white-50 small">
                            <i class="bi bi-qr-code me-1"></i> Mã VĐV: <strong><?php echo CHtml::encode(!empty($runMine['lucky_number']) ? $runMine['lucky_number'] : $bib); ?></strong>
                        </div>
                    </div>

                    <table class="bib-info-table">
                        <tr>
                            <td><i class="bi bi-flag-fill text-primary me-2"></i> Cự ly thi đấu:</td>
                            <td><span class="fw-bold text-primary"><?php echo CHtml::encode(isset($runMine['run_event_name']) ? $runMine['run_event_name'] : ''); ?></span></td>
                        </tr>
                        <?php if (!empty($runMine['age_group_label'])): ?>
                        <tr>
                            <td><i class="bi bi-people-fill text-primary me-2"></i> Nội dung:</td>
                            <td><span class="fw-bold text-primary"><?php echo CHtml::encode($runMine['age_group_label']); ?></span></td>
                        </tr>
                        <?php endif; ?>
                        <?php if (!empty($runMine['full_name'])): ?>
                            <tr>
                                <td><i class="bi bi-person-fill text-secondary me-2"></i> Vận động viên:</td>
                                <td><?php echo CHtml::encode($runMine['full_name']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($runMine['unit_label'])): ?>
                            <tr>
                                <td><i class="bi bi-building text-secondary me-2"></i> Đơn vị:</td>
                                <td><?php echo CHtml::encode($runMine['unit_label']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($runMine['birth_year'])): ?>
                            <tr>
                                <td><i class="bi bi-calendar3 text-secondary me-2"></i> Năm sinh:</td>
                                <td><?php echo CHtml::encode($runMine['birth_year']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (isset($runMine['gender']) && $runMine['gender'] !== null && $runMine['gender'] !== ''): ?>
                            <tr>
                                <td><i class="bi bi-gender-ambiguous text-secondary me-2"></i> Giới tính:</td>
                                <td><?php echo ((int) $runMine['gender'] === 1) ? 'Nam' : 'Nữ'; ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td><i class="bi bi-calendar-check text-success me-2"></i> Đăng ký lúc:</td>
                            <td><span class="text-muted"><?php echo $regTime; ?></span></td>
                        </tr>
                    </table>

                    <?php $shirtSize = isset($runMine['shirt_size']) ? $runMine['shirt_size'] : ''; ?>
                    <div class="shirt-size-box mt-3 p-3 rounded border bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark small">
                                <i class="bi bi-person-arms-up text-primary me-1"></i> Size áo Fun Run
                            </span>
                            <?php if ($shirtSize !== ''): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#modalShirtSize">
                                    <i class="bi bi-pencil-square me-1"></i> Đổi size
                                </button>
                            <?php endif; ?>
                        </div>
                        <?php if ($shirtSize !== ''): ?>
                            <div class="mt-2">
                                <span class="badge bg-primary fs-6 px-3 py-2"><?php echo CHtml::encode($shirtSize); ?></span>
                                <span class="text-muted small ms-2">Áo dành riêng cho nội dung Fun Run</span>
                            </div>
                        <?php else: ?>
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal" data-bs-target="#modalShirtSize">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Bạn chưa chọn size áo — bấm để chọn
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php
                    $emName  = isset($runMine['emergency_contact_name']) ? $runMine['emergency_contact_name'] : '';
                    $emPhone = isset($runMine['emergency_contact_phone']) ? $runMine['emergency_contact_phone'] : '';
                    $emCond  = isset($runMine['medical_conditions']) ? $runMine['medical_conditions'] : '';
                    $emMed   = isset($runMine['medications']) ? $runMine['medications'] : '';
                    $hasEmergency = ($emName !== '' || $emPhone !== '' || $emCond !== '' || $emMed !== '');
                    ?>
                    <div class="emergency-info-box mt-3 p-3 rounded border bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark small">
                                <i class="bi bi-heart-pulse-fill text-danger me-1"></i> Thông tin khẩn cấp
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                    data-bs-toggle="modal" data-bs-target="#modalEmergency">
                                <i class="bi bi-pencil-square me-1"></i> <?php echo $hasEmergency ? 'Chỉnh sửa' : 'Bổ sung'; ?>
                            </button>
                        </div>
                        <?php if ($hasEmergency): ?>
                            <table class="bib-info-table mb-0">
                                <tr>
                                    <td><i class="bi bi-person-lines-fill text-secondary me-2"></i> Người liên hệ:</td>
                                    <td><?php echo $emName !== '' ? CHtml::encode($emName) : '<span class="text-muted">Chưa cung cấp</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-telephone-fill text-secondary me-2"></i> Số điện thoại:</td>
                                    <td><?php echo $emPhone !== '' ? CHtml::encode($emPhone) : '<span class="text-muted">Chưa cung cấp</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-clipboard2-pulse text-secondary me-2"></i> Bệnh nền:</td>
                                    <td><?php echo $emCond !== '' ? nl2br(CHtml::encode($emCond)) : '<span class="text-muted">Không có / chưa cung cấp</span>'; ?></td>
                                </tr>
                                <tr>
                                    <td><i class="bi bi-capsule text-secondary me-2"></i> Thuốc đang dùng:</td>
                                    <td><?php echo $emMed !== '' ? nl2br(CHtml::encode($emMed)) : '<span class="text-muted">Không có / chưa cung cấp</span>'; ?></td>
                                </tr>
                            </table>
                        <?php else: ?>
                            <div class="text-muted small mb-0">
                                Bạn chưa cung cấp thông tin khẩn cấp. Thông tin này không bắt buộc nhưng giúp Ban tổ chức hỗ trợ bạn khi cần.
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($isPending): ?>
                        <div class="alert alert-warning d-flex align-items-center mb-0 p-2 small">
                            <i class="bi bi-hourglass-split fs-5 me-2 text-warning"></i>
                            <div>Yêu cầu hủy cự ly đang được Ban tổ chức xem xét duyệt.</div>
                        </div>
                    <?php elseif (!empty($runMine['can_request_cancel'])): ?>
                        <button type="button" class="btn btn-outline-danger w-100 btn-cancel-request mt-2" data-bs-toggle="modal" data-bs-target="#modalCancelRun">
                            <i class="bi bi-x-circle me-1"></i> Xin hủy đăng ký cự ly này
                        </button>
                        <small class="text-muted text-center d-block mt-1" style="font-size: 0.76rem;">
                            <i class="bi bi-info-circle me-1"></i> Sau khi BTC duyệt hủy, bạn có thể chọn đăng ký cự ly khác nếu còn chỉ tiêu và trong thời hạn đăng ký.
                        </small>
                    <?php else: ?>
                        <div class="alert alert-secondary d-flex align-items-center mb-0 py-2 px-3 small border-0 bg-light">
                            <i class="bi bi-lock-fill text-secondary me-2 fs-6"></i>
                            <span class="text-muted">Suất thi đấu đã được khóa chính thức — không thể thay đổi.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif (empty($runEvents)): ?>
            <div class="text-center py-5">
                <div class="category-icon-tag tag-run mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.75rem;">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h6 class="text-dark fw-bold mb-1">Chưa mở đăng ký</h6>
                <p class="text-muted small mb-0">Hiện chưa có cự ly chạy nào mở đăng ký hoặc cổng đang tạm đóng.</p>
            </div>

        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small">
                    <i class="bi bi-hand-index-thumb me-1 text-primary"></i> Chọn <strong>1 cự ly thi đấu</strong>:
                </span>
                <span class="badge bg-light text-secondary border">
                    <?php echo count($runEvents); ?> cự ly
                </span>
            </div>

            <div class="run-events-list">
                <?php foreach ($runEvents as $e):
                    $quota = (int) $e['quota'];
                    $registered = (int) $e['registered_count'];
                    $remaining = (int) $e['remaining'];
                    $percent = ($quota > 0) ? min(100, round(($registered / $quota) * 100)) : 0;
                    $isFull = ($remaining <= 0);
                    $isNotStarted = !empty($e['not_started']);
                    $isEnded = !empty($e['ended']);
                    $isDisabled = ($isFull || $isNotStarted || $isEnded);

                    // Badge màu sắc số chỗ còn lại
                    if ($isFull) {
                        $badgeCls = 'bg-danger-subtle text-danger border border-danger-subtle';
                        $statusText = 'Hết chỗ';
                    } elseif ($remaining <= 5) {
                        $badgeCls = 'bg-warning-subtle text-dark border border-warning-subtle';
                        $statusText = 'Còn ' . $remaining . ' chỗ';
                    } else {
                        $badgeCls = 'bg-success-subtle text-success border border-success-subtle';
                        $statusText = 'Còn ' . $remaining . ' chỗ';
                    }

                    // Progress bar color
                    $barCls = $percent >= 100 ? 'bg-danger' : ($percent >= 85 ? 'bg-warning' : 'bg-primary');

                    // Icon cự ly: tách số km từ code ("5K" -> 5) hoặc tên ("Chạy 15km" -> 15).
                    $distNum = preg_replace('/\D/', '', !empty($e['code']) ? $e['code'] : $e['name']);
                    $distIcon = ($distNum !== '' ? $distNum : '•') . 'K';
                ?>
                    <div class="portal-option-card <?php echo $isDisabled ? 'option-disabled' : ''; ?>">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="d-flex align-items-start gap-2 flex-grow-1">
                                <div class="category-icon-tag tag-run fw-bold" style="font-size: 0.85rem;">
                                    <?php echo $distIcon; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                        <?php echo CHtml::encode($e['name']); ?>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                        <span class="badge <?php echo $badgeCls; ?> px-2 py-1" style="font-size: 0.75rem;">
                                            <i class="bi <?php echo $isFull ? 'bi-x-circle' : 'bi-check2'; ?> me-1"></i><?php echo $statusText; ?>
                                        </span>
                                        <span class="text-muted small" style="font-size: 0.75rem;">
                                            (<?php echo $registered; ?>/<?php echo $quota; ?> đã đăng ký)
                                        </span>
                                    </div>

                                    <div class="slot-progress-bar" title="Đã đăng ký <?php echo $percent; ?>%">
                                        <div class="slot-progress-fill <?php echo $barCls; ?>" style="width: <?php echo $percent; ?>%;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex-shrink-0 ms-2 text-end">
                                <?php if ($isNotStarted): ?>
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="bi bi-hourglass me-1"></i> Chưa mở
                                    </button>
                                <?php elseif ($isEnded): ?>
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="bi bi-clock-history me-1"></i> Hết hạn
                                    </button>
                                <?php elseif ($isFull): ?>
                                    <button class="btn btn-sm btn-secondary" disabled>
                                        <i class="bi bi-slash-circle me-1"></i> Hết chỗ
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-sm btn-primary btn-portal-register"
                                            data-type="run" data-id="<?php echo (int) $e['id']; ?>"
                                            data-name="<?php echo CHtml::encode($e['name']); ?>">
                                        <i class="bi bi-check-circle me-1"></i> Đăng ký
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
