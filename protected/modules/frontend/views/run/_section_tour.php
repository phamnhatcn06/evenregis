<?php
/**
 * Khối đăng ký Đi tham quan (Tour) — Giao diện chuyên nghiệp, tích hợp icon & Tour Boarding Pass.
 * Tham số: $tourMine, $tourSessions, $fullName (optional), $window (optional)
 */
$window = isset($window) ? $window : array('state' => 'open', 'target' => 0, 'label' => '');
$regOpen = ($window['state'] === 'open');
$tourImg = Yii::app()->theme->baseUrl . '/assets/images/tour-trang-an.jpg';
?>
<div class="activity-card h-100">
    <div class="activity-card-header-tour d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="mb-0 text-white fw-bold d-flex align-items-center">
                <i class="bi bi-bus-front-fill me-2"></i> Đi tham quan
            </h5>
            <small class="text-white-50">Khám phá danh thắng & trải nghiệm Đại hội</small>
        </div>
        <?php $this->renderPartial('_card_countdown', array('window' => $window)); ?>
        <span class="activity-badge">
            <i class="bi bi-check-circle me-1"></i> Tối đa 1 đợt
        </span>
    </div>

    <!-- Ảnh giới thiệu tour Di sản Tràng An -->
    <div class="tour-hero-banner">
        <img src="<?php echo $tourImg; ?>" alt="Tour tham quan Di sản Tràng An" loading="lazy">
        <div class="tour-hero-overlay">
            <span class="tour-hero-badge"><i class="bi bi-geo-alt-fill me-1"></i> Di sản Tràng An</span>
            <div class="tour-hero-title">Tour tham quan 1/2 ngày · Miễn phí</div>
        </div>
    </div>

    <div class="p-3 p-md-4">
        <?php if (!empty($tourMine)): ?>
            <?php
            $isPending = (isset($tourMine['status']) && $tourMine['status'] === 'cancel_requested');
            $regTime = !empty($tourMine['registered_at'])
                ? (is_numeric($tourMine['registered_at']) ? date('H:i d/m/Y', (int)$tourMine['registered_at']) : CHtml::encode($tourMine['registered_at']))
                : date('d/m/Y');
            ?>
            <!-- Thẻ Vé Tham Quan (Tour Boarding Pass) -->
            <div class="tour-boarding-pass">
                <div class="tour-pass-strip"></div>
                
                <div class="race-bib-header" style="background: #ecfdf5; border-bottom: 1px dashed #a7f3d0;">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-ticket-perforated-fill text-success fs-5 me-2"></i>
                        <span class="fw-bold text-dark small text-uppercase" style="letter-spacing: 0.05em;">Phiếu Tham Quan Hợp Lệ</span>
                    </div>
                    <?php if ($isPending): ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split me-1"></i> Chờ duyệt hủy</span>
                    <?php else: ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-shield-check me-1"></i> Đã giữ chỗ</span>
                    <?php endif; ?>
                </div>

                <div class="p-3">
                    <div class="tour-time-highlight">
                        <div class="text-white-50 text-uppercase fw-semibold" style="font-size: 0.72rem; letter-spacing: 0.12em;">Khung Giờ Khởi Hành</div>
                        <div class="tour-time-text my-1">
                            <i class="bi bi-clock-history me-1 text-warning"></i> <?php echo CHtml::encode(!empty($tourMine['start_time']) ? $tourMine['start_time'] : 'Theo lịch BTC'); ?>
                        </div>
                        <div class="text-white small fw-semibold mt-1">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo CHtml::encode(isset($tourMine['tour_session_name']) ? $tourMine['tour_session_name'] : ''); ?>
                        </div>
                    </div>

                    <table class="bib-info-table">
                        <tr>
                            <td><i class="bi bi-signpost-2-fill text-success me-2"></i> Đợt tham quan:</td>
                            <td><span class="fw-bold text-success"><?php echo CHtml::encode(isset($tourMine['tour_session_name']) ? $tourMine['tour_session_name'] : ''); ?></span></td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-clock text-secondary me-2"></i> Thời gian:</td>
                            <td><?php echo CHtml::encode(isset($tourMine['start_time']) ? $tourMine['start_time'] : ''); ?></td>
                        </tr>
                        <?php if (!empty($tourMine['full_name'])): ?>
                            <tr>
                                <td><i class="bi bi-person-fill text-secondary me-2"></i> Người tham gia:</td>
                                <td><?php echo CHtml::encode($tourMine['full_name']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <?php if (!empty($tourMine['unit_label'])): ?>
                            <tr>
                                <td><i class="bi bi-building text-secondary me-2"></i> Đơn vị:</td>
                                <td><?php echo CHtml::encode($tourMine['unit_label']); ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td><i class="bi bi-calendar-check text-success me-2"></i> Đăng ký lúc:</td>
                            <td><span class="text-muted"><?php echo $regTime; ?></span></td>
                        </tr>
                    </table>

                    <?php if ($isPending): ?>
                        <div class="alert alert-warning d-flex align-items-center mb-0 p-2 small">
                            <i class="bi bi-hourglass-split fs-5 me-2 text-warning"></i>
                            <div>Yêu cầu hủy đợt tham quan đang được Ban tổ chức xem xét duyệt.</div>
                        </div>
                    <?php elseif (!empty($tourMine['can_request_cancel'])): ?>
                        <button type="button" class="btn btn-outline-danger w-100 btn-cancel-request mt-2" data-bs-toggle="modal" data-bs-target="#modalCancelTour">
                            <i class="bi bi-x-circle me-1"></i> Xin hủy đăng ký đợt này
                        </button>
                        <small class="text-muted text-center d-block mt-1" style="font-size: 0.76rem;">
                            <i class="bi bi-info-circle me-1"></i> Sau khi BTC duyệt hủy, bạn có thể chọn đăng ký đợt tham quan khác.
                        </small>
                    <?php else: ?>
                        <div class="alert alert-secondary d-flex align-items-center mb-0 py-2 px-3 small border-0 bg-light">
                            <i class="bi bi-lock-fill text-secondary me-2 fs-6"></i>
                            <span class="text-muted">Suất tham quan đã được khóa — không thể thay đổi.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif (empty($tourSessions)): ?>
            <div class="text-center py-5">
                <div class="category-icon-tag tag-tour mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.75rem;">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h6 class="text-dark fw-bold mb-1">Chưa mở đăng ký</h6>
                <p class="text-muted small mb-0">Hiện chưa có đợt tham quan nào mở đăng ký hoặc cổng đang tạm đóng.</p>
            </div>

        <?php elseif (!$regOpen): ?>
            <div class="text-center py-5">
                <div class="category-icon-tag tag-tour mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.75rem;">
                    <i class="bi <?php echo $window['state'] === 'before' ? 'bi-hourglass-split' : 'bi-lock-fill'; ?>"></i>
                </div>
                <h6 class="text-dark fw-bold mb-1">
                    <?php echo $window['state'] === 'before' ? 'Chưa tới giờ mở đăng ký' : 'Đã hết thời gian đăng ký'; ?>
                </h6>
                <p class="text-muted small mb-0">
                    <?php if ($window['state'] === 'before'): ?>
                        Cổng đăng ký tham quan sẽ mở theo đồng hồ đếm ngược phía trên. Vui lòng quay lại khi cổng mở.
                    <?php else: ?>
                        Cổng đăng ký tham quan đã đóng. Cảm ơn bạn đã quan tâm.
                    <?php endif; ?>
                </p>
            </div>

        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small">
                    <i class="bi bi-hand-index-thumb me-1 text-success"></i> Chọn <strong>1 đợt tham quan</strong>:
                </span>
                <span class="badge bg-light text-secondary border">
                    <?php echo count($tourSessions); ?> đợt
                </span>
            </div>

            <div class="tour-sessions-list">
                <?php foreach ($tourSessions as $s):
                    $quota = (int) $s['quota'];
                    $registered = (int) $s['registered_count'];
                    $remaining = (int) $s['remaining'];
                    $percent = ($quota > 0) ? min(100, round(($registered / $quota) * 100)) : 0;
                    $isFull = ($remaining <= 0);
                    $isNotStarted = !empty($s['not_started']);
                    $isEnded = !empty($s['ended']);
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
                    $barCls = $percent >= 100 ? 'bg-danger' : ($percent >= 85 ? 'bg-warning' : 'bg-success');
                ?>
                    <div class="portal-option-card tour-option <?php echo $isDisabled ? 'option-disabled' : ''; ?>">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div class="d-flex align-items-start gap-2 flex-grow-1">
                                <div class="category-icon-tag tag-tour" style="font-size: 1.1rem;">
                                    <i class="bi bi-bus-front"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                        <?php echo CHtml::encode($s['name']); ?>
                                    </div>
                                    <?php if (!empty($s['start_time'])): ?>
                                        <div class="text-success small fw-semibold mt-1">
                                            <i class="bi bi-clock-fill me-1"></i> <?php echo CHtml::encode($s['start_time']); ?>
                                        </div>
                                    <?php endif; ?>
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
                                    <button type="button" class="btn btn-sm btn-success btn-portal-register"
                                            data-type="tour" data-id="<?php echo (int) $s['id']; ?>"
                                            data-name="<?php echo CHtml::encode($s['name']); ?>">
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
