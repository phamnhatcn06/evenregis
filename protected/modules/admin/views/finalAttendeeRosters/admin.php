<?php
/**
 * Màn Tổng hợp danh sách Vòng Chung Kết (VCK) + mã lucky — Giao diện hiện đại.
 *
 * @var FinalAttendeeRostersController $this
 * @var int $eventId
 * @var int $periodId
 * @var array $eventList
 * @var array $periodList
 * @var ApiDataProvider|null $dataProvider
 * @var array|null $stats
 * @var array $filterOptions
 * @var int|null $lastSyncedAt
 * @var array|null $audit
 * @var array $roleList
 * @var int $pageSize
 * @var array $pageSizes
 * @var array $filters
 */

$this->breadcrumbs = array('Tổng hợp danh sách Vòng Chung Kết');
$this->Tabletitle  = 'Tổng hợp danh sách Vòng Chung Kết';

// Đăng ký CSS và JS chuyên biệt
Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/vendor/select2/css/select2.min.css'
);
Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/css/pages/finalattendeerosters-admin.css?v=2.8'
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/vendor/select2/js/select2.min.js',
    CClientScript::POS_END
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/toast.js',
    CClientScript::POS_END
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/finalattendeerosters-admin.js?v=2.7',
    CClientScript::POS_END
);

$canCreate = PermissionHelper::can('finalattendeerosters', 'create');
$canUpdate = PermissionHelper::can('finalattendeerosters', 'update');
$canDelete = PermissionHelper::can('finalattendeerosters', 'delete');

/** Dữ liệu cũ sau 24h thì nhắc HO đồng bộ lại */
$isStale = $lastSyncedAt && (time() - $lastSyncedAt) > 86400;

$flashMessages = Yii::app()->user->getFlashes();
?>

<!-- Cấu hình JS -->
<div id="final-attendee-roster-config"
     data-event-id="<?php echo (int) $eventId; ?>"
     data-period-id="<?php echo (int) $periodId; ?>"
     data-filters-url="<?php echo $this->createUrl('admin'); ?>"
     data-filter-options-url="<?php echo $this->createUrl('filterOptions'); ?>"
     data-update-field-url="<?php echo $this->createUrl('updateField'); ?>"
     data-reset-field-url="<?php echo $this->createUrl('resetField'); ?>"
     data-merge-url="<?php echo $this->createUrl('merge'); ?>"
     data-split-url="<?php echo $this->createUrl('split'); ?>"
     data-delete-url="<?php echo $this->createUrl('delete'); ?>"
     data-clear-conflict-url="<?php echo $this->createUrl('clearConflict'); ?>"
     data-set-lucky-url="<?php echo $this->createUrl('setLucky'); ?>"
     data-reset-pin-url="<?php echo $this->createUrl('resetPin'); ?>"
     data-check-lucky-url="<?php echo $this->createUrl('checkLucky'); ?>"
     data-account-units-url="<?php echo $this->createUrl('accountUnits'); ?>"
     data-send-accounts-url="<?php echo $this->createUrl('sendAccounts'); ?>"
     data-list-url="<?php echo $this->createUrl('admin'); ?>"
     data-search-url="<?php echo $this->createUrl('filterOptions'); ?>"
     data-can-update="<?php echo $canUpdate ? 1 : 0; ?>"
     data-field-labels="<?php echo CHtml::encode(CJSON::encode(FinalAttendeeRosters::editableFields())); ?>"
     data-flash="<?php echo CHtml::encode(CJSON::encode($flashMessages)); ?>"></div>

<!-- Header Card: Lựa chọn sự kiện / đợt & Nút thao tác nhanh -->
<div class="far-header-card mb-3">
    <div class="card-body p-3 p-md-4">
        <div class="row g-3 align-items-center">
            <!-- Tiêu đề & Chọn sự kiện / đợt -->
            <div class="col-xl-6 col-lg-5">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="far-title-icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold text-dark" style="font-size: 18px; letter-spacing: -0.3px;">
                            Tổng Hợp Danh Sách Vòng Chung Kết
                        </h4>
                        <div class="small text-muted">
                            Đồng bộ danh sách, cấp mã lucky, ghép BIB và bảo vệ dữ liệu VCK
                        </div>
                    </div>
                </div>

                <div class="row g-2 far-select-group">
                    <div class="col-sm-6">
                        <label class="form-label">Sự kiện <span class="text-danger">*</span></label>
                        <form method="get" action="<?php echo $this->createUrl('admin'); ?>" id="form-event">
                            <?php echo CHtml::dropDownList('event_id', $eventId, $eventList, array(
                                'class'    => 'form-select',
                                'empty'    => '-- Chọn sự kiện --',
                                'onchange' => 'this.form.submit()',
                            )); ?>
                        </form>
                    </div>

                    <div class="col-sm-6">
                        <label class="form-label">Đợt Vòng Chung Kết <span class="text-danger">*</span></label>
                        <form method="get" action="<?php echo $this->createUrl('admin'); ?>" id="form-period">
                            <input type="hidden" name="event_id" value="<?php echo (int) $eventId; ?>">
                            <?php echo CHtml::dropDownList('period_id', $periodId, $periodList, array(
                                'class'    => 'form-select',
                                'empty'    => $eventId ? '-- Chọn đợt Vòng Chung Kết --' : '-- Chọn sự kiện trước --',
                                'disabled' => $eventId ? null : 'disabled',
                                'onchange' => 'this.form.submit()',
                            )); ?>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Toolbar nút hành động -->
            <div class="col-xl-6 col-lg-7">
                <?php if ($eventId && $periodId): ?>
                    <div class="far-actions-bar">
                        <?php if ($canCreate): ?>
                            <button type="button" class="far-btn far-btn-primary"
                                    data-bs-toggle="modal" data-bs-target="#modal_sync"
                                    title="Đồng bộ danh sách từ kết quả sơ khảo">
                                <i class="fa fa-refresh"></i>
                                <span>Đồng bộ từ VCK</span>
                            </button>

                            <button type="button" class="far-btn far-btn-success"
                                    data-bs-toggle="modal" data-bs-target="#modal_add_person"
                                    title="Thêm người thủ công ngoài danh sách VCK">
                                <i class="fa fa-user-plus"></i>
                                <span>Thêm người</span>
                            </button>

                            <button type="button" class="far-btn far-btn-warning text-white"
                                    data-bs-toggle="modal" data-bs-target="#modal_gen_lucky"
                                    title="Chỉ cấp cho người chưa có mã. Mã đã cấp không bao giờ đổi.">
                                <i class="fa fa-ticket"></i>
                                <span>Cấp mã lucky</span>
                                <?php if ($stats && $stats['without_lucky'] > 0): ?>
                                    <span class="badge bg-danger ms-1 px-2 py-1 rounded-pill">
                                        <?php echo number_format($stats['without_lucky']); ?>
                                    </span>
                                <?php endif; ?>
                            </button>

                            <button type="button" class="far-btn far-btn-info text-white"
                                    data-bs-toggle="modal" data-bs-target="#modal_send_accounts"
                                    title="Gửi email thông tin tài khoản kèm PDF danh sách cho từng đơn vị đang lọc">
                                <i class="fa fa-paper-plane"></i>
                                <span>Gửi thông tin tài khoản</span>
                            </button>
                        <?php endif; ?>

                        <a href="<?php echo $this->createUrl('export', array_merge(
                            array('event_id' => $eventId, 'period_id' => $periodId),
                            array_filter($filters, function ($value) { return $value !== null && $value !== ''; })
                        )); ?>" class="far-btn far-btn-outline-excel" title="Xuất đúng những dòng đang lọc ra file Excel">
                            <i class="fa fa-file-excel-o"></i>
                            <span>Xuất Excel</span>
                        </a>

                        <a href="<?php echo $this->createUrl('exportImagesBatch', array_merge(
                            array('event_id' => $eventId, 'period_id' => $periodId),
                            array_filter($filters, function ($value) { return $value !== null && $value !== ''; })
                        )); ?>" class="far-btn far-btn-outline-primary" title="Xuất ảnh thẻ VCK (trước + sau) cho các dòng đang lọc, đóng gói ZIP theo mã đơn vị">
                            <i class="fa fa-id-card-o"></i>
                            <span>Xuất ảnh thẻ (ZIP)</span>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-lg-end text-muted small">
                        <i class="fa fa-info-circle me-1 text-primary"></i>Chọn sự kiện và đợt để kích hoạt các thao tác đồng bộ & cấp mã
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Audit Ribbon: Trạng thái dải thẻ, xung đột & đồng bộ -->
        <?php if ($eventId && $periodId): ?>
            <div class="far-info-ribbon">
                <!-- Đồng bộ lần cuối -->
                <div class="far-chip far-chip-neutral">
                    <i class="fa fa-clock-o text-muted"></i>
                    <span>
                        Đồng bộ lần cuối:
                        <strong><?php echo $lastSyncedAt ? date('d/m/Y H:i', $lastSyncedAt) : 'Chưa đồng bộ'; ?></strong>
                    </span>
                    <?php if ($isStale): ?>
                        <span class="badge bg-warning text-dark ms-1" title="Dữ liệu đồng bộ đã quá 24h, nên đồng bộ lại">
                            <i class="fa fa-exclamation-triangle me-1"></i>Có thể đã cũ
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Thẻ tham dự MT -->
                <?php if ($audit !== null && isset($audit['badge'])): ?>
                    <?php
                    $badge        = $audit['badge'];
                    $invalidCount = count($badge['invalid_format']);
                    $isBadgeWarn  = ($invalidCount > 0 || $badge['remaining'] < 50);
                    ?>
                    <div class="far-chip <?php echo $isBadgeWarn ? 'far-chip-danger' : 'far-chip-neutral'; ?>"
                         title="<?php echo $invalidCount > 0 ? ('Có ' . $invalidCount . ' số thẻ sai format!') : ('Số thẻ kế tiếp: ' . ($badge['next_badge_number'] ?: 'Hết dải')); ?>">
                        <i class="fa <?php echo $invalidCount > 0 ? 'fa-exclamation-triangle' : 'fa-id-card-o'; ?>"></i>
                        <span>
                            Số thẻ <strong><?php echo CHtml::encode($badge['prefix']); ?></strong>:
                            <?php echo (int) $badge['used']; ?>/<?php echo (int) $badge['capacity']; ?>
                        </span>
                    </div>
                <?php endif; ?>

                <!-- Xung đột mã lucky -->
                <?php if ($audit !== null && isset($audit['lucky'])): ?>
                    <?php
                    $luckyAudit    = $audit['lucky'];
                    $conflictCount = count($luckyAudit['duplicate_person_multi_lucky']);
                    $unsyncedCount = count($luckyAudit['lucky_only_on_roster']);
                    ?>
                    <div class="far-chip <?php echo $conflictCount > 0 ? 'far-chip-danger' : 'far-chip-neutral'; ?>"
                         <?php if ($conflictCount > 0): ?>
                             role="button" id="btn_show_lucky_conflicts"
                             data-conflicts="<?php echo CHtml::encode(CJSON::encode($luckyAudit['duplicate_person_multi_lucky'])); ?>"
                         <?php endif; ?>
                         title="Người giữ nhiều mã lucky — cần xử lý trước khi phát định danh">
                        <i class="fa <?php echo $conflictCount > 0 ? 'fa-exclamation-circle' : 'fa-check-circle text-success'; ?>"></i>
                        <span>Xung đột lucky: <strong><?php echo $conflictCount; ?></strong></span>
                    </div>

                    <?php if ($unsyncedCount > 0): ?>
                        <div class="far-chip far-chip-danger"
                             title="Có mã trong bảng nhưng chưa ghi ngược sang bản ghi gốc. Bấm Cấp mã lucky để tự chữa.">
                            <i class="fa fa-exclamation-triangle"></i>
                            <span>Chưa ghi ngược: <strong><?php echo $unsyncedCount; ?></strong></span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!$eventId || !$periodId): ?>
    <!-- Màn hình chào khi chưa chọn sự kiện / đợt -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-5 text-center">
            <div class="far-empty-icon mx-auto mb-3" style="width: 76px; height: 76px; font-size: 32px; background: rgba(58, 87, 232, 0.1); color: var(--far-primary);">
                <i class="fa fa-calendar-check-o"></i>
            </div>
            <h4 class="fw-bold text-dark mb-2">Chào mừng đến cổng Tổng Hợp Danh Sách VCK</h4>
            <p class="text-muted mx-auto mb-4" style="max-width: 580px;">
                Vui lòng chọn <strong>Sự kiện</strong> và <strong>Đợt Vòng Chung Kết</strong> ở thanh chọn bên trên để tải danh sách đại biểu, quản lý mã lucky và theo dõi dữ liệu đăng ký.
            </p>
            <div class="d-inline-flex gap-2 p-2 bg-light rounded-pill border px-3">
                <span class="badge bg-primary rounded-pill px-3 py-2 d-flex align-items-center">
                    <i class="fa fa-arrow-up me-2"></i>Chọn sự kiện & đợt ở thanh trên
                </span>
            </div>
        </div>
    </div>
<?php else: ?>

    <!-- 6 KPI Stat Cards -->
    <?php if ($stats !== null): ?>
        <?php
        $pctLucky = ($stats['total'] > 0) ? round(($stats['with_lucky'] / $stats['total']) * 100) : 0;
        $kpiCards = array(
            array(
                'label'       => 'Tổng số người',
                'value'       => $stats['total'],
                'icon'        => 'fa-users',
                'bubbleClass' => 'far-kpi-bubble-primary',
                'statusText'  => 'Danh sách VCK',
                'statusBadge' => 'badge-subtle-primary',
            ),
            array(
                'label'       => 'Đã có mã lucky',
                'value'       => $stats['with_lucky'],
                'icon'        => 'fa-ticket',
                'bubbleClass' => 'far-kpi-bubble-success',
                'statusText'  => $pctLucky . '% hoàn tất',
                'statusBadge' => 'badge-subtle-success',
            ),
            array(
                'label'       => 'Chưa có mã lucky',
                'value'       => $stats['without_lucky'],
                'icon'        => 'fa-hourglass-half',
                'bubbleClass' => $stats['without_lucky'] > 0 ? 'far-kpi-bubble-danger' : 'far-kpi-bubble-muted',
                'statusText'  => $stats['without_lucky'] > 0 ? 'Cần cấp ngay' : 'Đã đủ mã',
                'statusBadge' => $stats['without_lucky'] > 0 ? 'badge-subtle-danger' : 'badge-subtle-secondary',
            ),
            array(
                'label'       => 'Đã đặt mã PIN',
                'value'       => $stats['pin_set'],
                'icon'        => 'fa-shield',
                'bubbleClass' => 'far-kpi-bubble-info',
                'statusText'  => 'Bảo vệ cổng Fun Run',
                'statusBadge' => 'badge-subtle-info',
            ),
            array(
                'label'       => 'Đã sửa thủ công',
                'value'       => $stats['with_override'],
                'icon'        => 'fa-pencil',
                'bubbleClass' => 'far-kpi-bubble-warning',
                'statusText'  => 'Bảo vệ khi đồng bộ',
                'statusBadge' => 'badge-subtle-warning',
            ),
            array(
                'label'       => 'Xung đột cần soát',
                'value'       => $stats['conflicts'],
                'icon'        => 'fa-exclamation-triangle',
                'bubbleClass' => $stats['conflicts'] > 0 ? 'far-kpi-bubble-danger' : 'far-kpi-bubble-muted',
                'statusText'  => $stats['conflicts'] > 0 ? 'Cần xử lý' : '0 cảnh báo',
                'statusBadge' => $stats['conflicts'] > 0 ? 'badge-subtle-danger' : 'badge-subtle-secondary',
            ),
        );
        ?>
        <div class="row g-2 mb-3">
            <?php foreach ($kpiCards as $card): ?>
            <div class="col-xl-2 col-md-4 col-6">
                <div class="far-kpi-card">
                    <div class="far-kpi-body">
                        <div class="far-kpi-header">
                            <span class="far-kpi-label"><?php echo $card['label']; ?></span>
                            <div class="far-kpi-icon-bubble <?php echo $card['bubbleClass']; ?>">
                                <i class="fa <?php echo $card['icon']; ?>"></i>
                            </div>
                        </div>
                        <div class="far-kpi-val"><?php echo number_format($card['value']); ?></div>
                        <div class="far-kpi-footer">
                            <span class="badge <?php echo $card['statusBadge']; ?> far-kpi-status-badge">
                                <?php echo $card['statusText']; ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($stats['withdrawn'] > 0 && (string) $filters['with_trashed'] !== '1'): ?>
            <div class="alert alert-secondary py-2 px-3 small d-flex align-items-center justify-content-between mb-3 border-0 rounded-3">
                <div>
                    <i class="fa fa-user-times text-danger me-2"></i>
                    Có <strong><?php echo number_format($stats['withdrawn']); ?></strong> người đã huỷ tư cách đang bị ẩn.
                </div>
                <a href="<?php echo $this->createUrl('admin', array_merge(
                    array('event_id' => $eventId, 'period_id' => $periodId, 'with_trashed' => 1),
                    array_filter($filters, function ($v) { return $v !== null && $v !== ''; })
                )); ?>" class="btn btn-sm btn-outline-dark py-0 px-2" style="font-size: 12px;">
                    Hiện danh sách này
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Bộ lọc -->
    <?php $this->renderPartial('_filters', array(
        'eventId'       => $eventId,
        'periodId'      => $periodId,
        'filterOptions' => $filterOptions,
        'filters'       => $filters,
        'pageSize'      => $pageSize,
        'pageSizes'     => $pageSizes,
    )); ?>

    <!-- Bảng danh sách chính -->
    <div class="far-table-card">
        <div class="card-body">
            <div class="far-table-responsive">
                <table class="far-table align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">STT</th>
                            <th style="width: 140px;">Mã lucky</th>
                            <th style="min-width: 220px;">Họ và tên</th>
                            <th style="min-width: 130px;">Số điện thoại</th>
                            <th style="min-width: 160px;">Đơn vị</th>
                            <th style="min-width: 180px;">Chức danh</th>
                            <th style="min-width: 190px;">Nội dung tham gia</th>
                            <th style="width: 85px;" class="text-center">Size áo</th>
                            <th style="width: 110px;" class="text-center">Là BTC?</th>
                            <th style="width: 150px;" class="text-center">Trạng thái</th>
                            <?php if ($canUpdate): ?>
                                <th style="width: 110px;" class="text-end pe-3">Thao tác</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rows  = $dataProvider->getData();
                        $index = $dataProvider->pagination ? $dataProvider->pagination->offset : 0;

                        $avatarColors = array(
                            'linear-gradient(135deg, #3a57e8 0%, #203ac5 100%)',
                            'linear-gradient(135deg, #08b1ba 0%, #057d84 100%)',
                            'linear-gradient(135deg, #1aaa4d 0%, #118138 100%)',
                            'linear-gradient(135deg, #6c5dd3 0%, #4e3fc4 100%)',
                            'linear-gradient(135deg, #f16a1b 0%, #c44f0b 100%)',
                            'linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%)',
                        );

                        foreach ($rows as $row):
                            $index++;
                            $isWithdrawn = !empty($row->is_withdrawn);
                            $overridden  = is_array($row->overridden_fields) ? $row->overridden_fields : array();

                            // Tính initials cho avatar
                            $fullNameClean = trim((string) $row->full_name);
                            $nameParts     = preg_split('/\s+/u', $fullNameClean);
                            $initials      = '';
                            if (count($nameParts) >= 2) {
                                $firstChar = mb_substr($nameParts[0], 0, 1, 'UTF-8');
                                $lastChar  = mb_substr(end($nameParts), 0, 1, 'UTF-8');
                                $initials  = mb_strtoupper($firstChar . $lastChar, 'UTF-8');
                            } else {
                                $initials = mb_strtoupper(mb_substr($fullNameClean, 0, 2, 'UTF-8'), 'UTF-8');
                            }
                            $avatarBg = $avatarColors[((int) $row->id) % count($avatarColors)];
                        ?>
                        <tr class="<?php echo $isWithdrawn ? 'table-secondary' : ''; ?>">
                            <!-- STT -->
                            <td class="text-center text-muted small fw-semibold">
                                <?php echo $index; ?>
                            </td>

                            <!-- Mã lucky & Định danh -->
                            <td>
                                <?php if ($row->lucky_number): ?>
                                    <div class="d-flex align-items-center gap-1 mb-1">
                                        <div class="far-lucky-badge mb-0">
                                            <i class="fa fa-ticket"></i>
                                            <span><?php echo CHtml::encode($row->lucky_number); ?></span>
                                        </div>
                                        <?php if ($canUpdate && !$isWithdrawn): ?>
                                            <button type="button" class="far-lucky-edit-btn js-set-lucky"
                                                    data-roster-id="<?php echo (int) $row->id; ?>"
                                                    data-full-name="<?php echo CHtml::encode($row->full_name); ?>"
                                                    data-unit="<?php echo CHtml::encode($row->property_name); ?>"
                                                    data-lucky="<?php echo CHtml::encode($row->lucky_number); ?>"
                                                    title="Sửa / Hoán đổi mã lucky">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div class="far-login-id">
                                        <span><?php echo CHtml::encode($row->login_identifier); ?></span>
                                        <button type="button" class="far-copy-btn js-copy"
                                                data-copy="<?php echo CHtml::encode($row->login_identifier); ?>"
                                                title="Sao chép định danh đăng nhập">
                                            <i class="fa fa-copy"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="far-lucky-badge-empty">
                                            <i class="fa fa-hourglass-o me-1"></i>Chưa cấp
                                        </span>
                                        <?php if ($canUpdate && !$isWithdrawn): ?>
                                            <button type="button" class="far-lucky-edit-btn js-set-lucky"
                                                    data-roster-id="<?php echo (int) $row->id; ?>"
                                                    data-full-name="<?php echo CHtml::encode($row->full_name); ?>"
                                                    data-unit="<?php echo CHtml::encode($row->property_name); ?>"
                                                    data-lucky=""
                                                    title="Gán mã lucky thủ công">
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Họ và tên + Avatar + Bản ghi gốc -->
                            <td>
                                <div class="far-user-cell">
                                    <?php $avatarUrl = method_exists($row, 'getAvatarUrl') ? $row->getAvatarUrl() : ''; ?>
                                    <div class="far-avatar-wrap <?php echo $avatarUrl ? 'has-avatar' : ''; ?>">
                                        <?php if ($avatarUrl): ?>
                                            <a href="<?php echo CHtml::encode($avatarUrl); ?>"
                                               target="_blank"
                                               class="far-avatar-link"
                                               title="Xem ảnh chân dung: <?php echo CHtml::encode($row->full_name); ?>">
                                                <img src="<?php echo CHtml::encode($avatarUrl); ?>"
                                                     alt="<?php echo CHtml::encode($row->full_name); ?>"
                                                     class="far-avatar far-avatar-img"
                                                     loading="lazy"
                                                     onerror="var w = this.closest('.far-avatar-wrap'); if(w) w.classList.add('has-error');">
                                            </a>
                                        <?php endif; ?>
                                        <div class="far-avatar far-avatar-initials" style="background: <?php echo $avatarBg; ?>;" title="<?php echo CHtml::encode($row->full_name); ?>">
                                            <?php echo CHtml::encode($initials ?: 'VCK'); ?>
                                        </div>
                                    </div>
                                    <div class="far-user-meta">
                                        <div class="far-user-name">
                                            <?php echo $this->renderPartial('_cell', array(
                                                'row'        => $row,
                                                'field'      => 'full_name',
                                                'value'      => $row->full_name,
                                                'overridden' => $overridden,
                                                'canUpdate'  => $canUpdate,
                                            ), true); ?>
                                        </div>
                                        <div class="far-user-sub">
                                            <?php if ($row->attendee_id): ?>
                                                <a href="<?php echo $this->createUrl('/admin/attendees/view', array('id' => $row->attendee_id)); ?>"
                                                   target="_blank" title="Xem hồ sơ đăng ký gốc">
                                                    <i class="fa fa-external-link me-1"></i>Bản ghi gốc #<?php echo (int) $row->attendee_id; ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="fa fa-user-plus me-1"></i>Thêm thủ công</span>
                                            <?php endif; ?>
                                            <?php if (!empty($row->staff_code)): ?>
                                                <span class="text-muted ms-2" title="Mã nhân viên">
                                                    <i class="fa fa-id-badge me-1"></i><?php echo CHtml::encode($row->staff_code); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($overridden)): ?>
                                            <div class="mt-1">
                                                <span class="badge badge-subtle-warning" style="font-size: 10px;"
                                                      title="Các trường đã sửa tay: <?php echo CHtml::encode(implode(', ', $overridden)); ?>">
                                                    <i class="fa fa-pencil me-1"></i>Đã sửa tay (<?php echo count($overridden); ?>)
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>

                            <!-- Số điện thoại -->
                            <td>
                                <?php if (!empty($row->phone_number) || in_array('phone_number', $overridden, true)): ?>
                                    <div class="d-inline-flex align-items-center text-nowrap">
                                        <?php if (!empty($row->phone_number)): ?>
                                            <a href="tel:<?php echo CHtml::encode($row->phone_number); ?>" class="far-phone-btn me-1" title="Gọi <?php echo CHtml::encode($row->phone_number); ?>">
                                                <i class="fa fa-phone"></i>
                                            </a>
                                        <?php endif; ?>
                                        <span class="far-phone-text">
                                            <?php echo $this->renderPartial('_cell', array(
                                                'row'        => $row,
                                                'field'      => 'phone_number',
                                                'value'      => $row->phone_number,
                                                'overridden' => $overridden,
                                                'canUpdate'  => $canUpdate,
                                            ), true); ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- Đơn vị -->
                            <td>
                                <?php
                                $unitDisplay = !empty($row->badge_org_name) ? $row->badge_org_name : (!empty($row->property_name) ? $row->property_name : $row->unit_label);
                                echo $this->renderPartial('_cell', array(
                                    'row'        => $row,
                                    'field'      => 'badge_org_name',
                                    'value'      => $unitDisplay,
                                    'overridden' => $overridden,
                                    'canUpdate'  => $canUpdate,
                                ), true);
                                ?>
                            </td>

                            <!-- Chức danh (sửa trực tiếp tại chỗ) -->
                            <td>
                                <?php echo $this->renderPartial('_cell', array(
                                    'row'        => $row,
                                    'field'      => 'position',
                                    'value'      => $row->position,
                                    'overridden' => $overridden,
                                    'canUpdate'  => $canUpdate,
                                    'isEditable' => true,
                                ), true); ?>
                            </td>

                            <!-- Nội dung tham gia -->
                            <td>
                                <?php
                                $participations = is_array($row->participations) ? $row->participations : array();
                                if (empty($participations) && !empty($row->content_names) && is_array($row->content_names)) {
                                    foreach ($row->content_names as $cname) {
                                        $participations[] = array('type' => 'competition', 'name' => $cname);
                                    }
                                }
                                ?>
                                <?php if (!empty($participations)): ?>
                                    <div class="far-contents-list">
                                        <?php foreach ($participations as $part):
                                            $pType = is_array($part) && isset($part['type']) ? $part['type'] : (is_object($part) && isset($part->type) ? $part->type : 'competition');
                                            $pName = is_array($part) && isset($part['name']) ? $part['name'] : (is_object($part) && isset($part->name) ? $part->name : (string) $part);

                                            $pillClass = 'far-content-default';
                                            $iconClass = 'fa-tag';
                                            if ($pType === 'sport') {
                                                $pillClass = 'far-content-sport';
                                                $iconClass = 'fa-trophy';
                                            } elseif ($pType === 'talent') {
                                                $pillClass = 'far-content-talent';
                                                $iconClass = 'fa-music';
                                            } elseif ($pType === 'beauty') {
                                                $pillClass = 'far-content-beauty';
                                                $iconClass = 'fa-star';
                                            } elseif ($pType === 'competition') {
                                                $pillClass = 'far-content-competition';
                                                $iconClass = 'fa-flag-checkered';
                                            } elseif ($pType === 'role') {
                                                $pillClass = 'far-content-role';
                                                $iconClass = 'fa-briefcase';
                                            }
                                        ?>
                                            <span class="far-content-pill <?php echo $pillClass; ?>" title="<?php echo CHtml::encode($pName); ?>">
                                                <i class="fa <?php echo $iconClass; ?>"></i>
                                                <span><?php echo CHtml::encode($pName); ?></span>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <?php
                                    $roleText = '';
                                    if ($row->attendee_type && $row->attendee_type !== 'contestant') {
                                        $typeOpts = FinalAttendeeRosters::getTypeOptions();
                                        $roleText = isset($typeOpts[$row->attendee_type]) ? $typeOpts[$row->attendee_type] : $row->attendee_type;
                                    } elseif ($row->note) {
                                        $roleText = $row->note;
                                    }
                                    ?>
                                    <?php if ($roleText !== ''): ?>
                                        <div class="far-contents-list">
                                            <span class="far-content-pill far-content-role" title="<?php echo CHtml::encode($roleText); ?>">
                                                <i class="fa fa-user-circle-o"></i>
                                                <span><?php echo CHtml::encode($roleText); ?></span>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>

                            <!-- Size áo -->
                            <td class="text-center">
                                <?php if ($row->shirt_size): ?>
                                    <span class="far-size-pill fw-bold text-dark px-2 py-1">
                                        <?php echo $this->renderPartial('_cell', array(
                                            'row'        => $row,
                                            'field'      => 'shirt_size',
                                            'value'      => $row->shirt_size,
                                            'overridden' => $overridden,
                                            'canUpdate'  => $canUpdate,
                                        ), true); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- Là BTC? -->
                            <td class="text-center">
                                <?php $isBtc = (int) $row->is_btc === 1; ?>
                                <?php if ($canUpdate && !$isWithdrawn): ?>
                                    <button type="button"
                                            class="far-btc-toggle js-toggle-btc <?php echo $isBtc ? 'is-btc-on' : 'is-btc-off'; ?>"
                                            data-roster-id="<?php echo (int) $row->id; ?>"
                                            data-full-name="<?php echo CHtml::encode($row->full_name); ?>"
                                            data-is-btc="<?php echo $isBtc ? 1 : 0; ?>"
                                            title="<?php echo $isBtc ? 'Bấm để bỏ đánh dấu Ban tổ chức' : 'Bấm để đánh dấu là Ban tổ chức'; ?>">
                                        <i class="fa <?php echo $isBtc ? 'fa-check-circle' : 'fa-circle-o'; ?>"></i>
                                        <span><?php echo $isBtc ? 'BTC' : 'Không'; ?></span>
                                    </button>
                                <?php elseif ($isBtc): ?>
                                    <span class="far-status-pill far-status-pill-info">
                                        <i class="fa fa-star"></i> BTC
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>

                            <!-- Trạng thái -->
                            <td class="text-center">
                                <?php if ($isWithdrawn): ?>
                                    <span class="far-status-pill far-status-pill-danger">
                                        <i class="fa fa-ban"></i> Đã huỷ
                                    </span>
                                <?php elseif ($row->status == FinalAttendeeRosters::STATUS_ACTIVE): ?>
                                    <span class="far-status-pill far-status-pill-success">
                                        <i class="fa fa-check-circle"></i> Tham dự
                                    </span>
                                <?php elseif ($row->status == FinalAttendeeRosters::STATUS_MANUAL): ?>
                                    <span class="far-status-pill far-status-pill-info">
                                        <i class="fa fa-user-plus"></i> HO thêm
                                    </span>
                                <?php else: ?>
                                    <span class="far-status-pill far-status-pill-secondary">
                                        <?php echo CHtml::encode($row->status); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ($row->conflict_flag): ?>
                                    <div class="mt-1 d-flex align-items-center justify-content-center gap-1">
                                        <span class="far-conflict-pill" title="<?php echo CHtml::encode(FinalAttendeeRosters::getConflictLabel($row->conflict_flag)); ?>">
                                            <i class="fa fa-exclamation-triangle"></i><?php echo CHtml::encode(FinalAttendeeRosters::getConflictLabel($row->conflict_flag)); ?>
                                        </span>
                                        <?php if ($canUpdate): ?>
                                            <button type="button" class="btn btn-link btn-sm p-0 text-danger js-clear-conflict"
                                                    data-roster-id="<?php echo (int) $row->id; ?>"
                                                    title="Đánh dấu đã xử lý xung đột này">
                                                <i class="fa fa-check-square-o"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Thao tác -->
                            <?php if ($canUpdate): ?>
                            <td class="text-end pe-3">
                                <div class="far-action-group">
                                    <button type="button"
                                            class="far-action-btn far-action-edit js-edit-row"
                                            data-row="<?php echo CHtml::encode(CJSON::encode(array(
                                                'id'                => $row->id,
                                                'full_name'         => $row->full_name,
                                                'staff_code'        => $row->staff_code,
                                                'id_card'           => $row->id_card,
                                                'phone_number'      => $row->phone_number,
                                                'email'             => $row->email,
                                                'property_name'     => $row->property_name,
                                                'badge_org_name'    => !empty($row->badge_org_name) ? $row->badge_org_name : (!empty($row->property_name) ? $row->property_name : $row->unit_label),
                                                'unit_label'        => $row->unit_label,
                                                'division_name'     => $row->division_name,
                                                'department_name'   => $row->department_name,
                                                'position'          => $row->position,
                                                'shirt_size'        => $row->shirt_size,
                                                'attendee_type'     => $row->attendee_type,
                                                'note'              => $row->note,
                                                'overridden_fields' => $overridden,
                                                'source_snapshot'   => is_array($row->source_snapshot) ? $row->source_snapshot : array(),
                                                'lucky_number'      => $row->lucky_number,
                                                'is_btc'            => (int) $row->is_btc,
                                                'updated_by'        => $row->updated_by,
                                                'last_synced_at'    => $row->last_synced_at,
                                            ))); ?>"
                                            title="Sửa thông tin">
                                        <i class="fa fa-pencil"></i>
                                    </button>

                                    <a href="<?php echo $this->createUrl('exportImage', array('id' => (int) $row->id)); ?>"
                                       class="far-action-btn far-action-export"
                                       target="_blank" rel="noopener"
                                       title="Xuất ảnh thẻ VCK (mặt trước + mặt sau)">
                                        <i class="fa fa-id-card-o"></i>
                                    </a>

                                    <?php if (!$isWithdrawn): ?>
                                        <button type="button"
                                                class="far-action-btn far-action-lucky js-set-lucky"
                                                data-roster-id="<?php echo (int) $row->id; ?>"
                                                data-full-name="<?php echo CHtml::encode($row->full_name); ?>"
                                                data-unit="<?php echo CHtml::encode(!empty($row->badge_org_name) ? $row->badge_org_name : $row->property_name); ?>"
                                                data-lucky="<?php echo CHtml::encode($row->lucky_number); ?>"
                                                title="Gán / Hoán đổi mã lucky">
                                            <i class="fa fa-ticket"></i>
                                        </button>
                                    <?php endif; ?>

                                    <?php if (!$isWithdrawn): ?>
                                        <button type="button"
                                                class="far-action-btn far-action-merge js-merge-split"
                                                data-roster-id="<?php echo (int) $row->id; ?>"
                                                data-full-name="<?php echo CHtml::encode($row->full_name); ?>"
                                                data-lucky="<?php echo CHtml::encode($row->lucky_number); ?>"
                                                data-staff-code="<?php echo CHtml::encode($row->staff_code); ?>"
                                                data-attendee-ids="<?php echo CHtml::encode(CJSON::encode(
                                                    is_array($row->source_attendee_ids) ? $row->source_attendee_ids : array()
                                                )); ?>"
                                                title="Gộp dòng hoặc tách người">
                                            <i class="fa fa-code-fork"></i>
                                        </button>
                                    <?php endif; ?>

                                    <?php if (!$isWithdrawn && $row->lucky_number && $row->pin_is_set): ?>
                                        <button type="button"
                                                class="far-action-btn far-action-reset-pin js-reset-pin"
                                                data-roster-id="<?php echo (int) $row->id; ?>"
                                                data-full-name="<?php echo CHtml::encode($row->full_name); ?>"
                                                data-lucky="<?php echo CHtml::encode($row->lucky_number); ?>"
                                                title="Reset mã PIN đăng nhập">
                                            <i class="fa fa-key"></i>
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($canDelete && !$isWithdrawn): ?>
                                        <button type="button"
                                                class="far-action-btn far-action-withdraw js-withdraw"
                                                data-roster-id="<?php echo (int) $row->id; ?>"
                                                data-full-name="<?php echo CHtml::encode($row->full_name); ?>"
                                                data-lucky="<?php echo CHtml::encode($row->lucky_number); ?>"
                                                title="Huỷ tư cách người này">
                                            <i class="fa fa-user-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="<?php echo $canUpdate ? 11 : 10; ?>" class="far-empty-state">
                                <div class="far-empty-icon">
                                    <i class="fa fa-inbox"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">Chưa có dữ liệu danh sách</h5>
                                <p class="text-muted small mb-3">
                                    Chưa có bản ghi nào phù hợp bộ lọc hoặc sự kiện chưa được đồng bộ dữ liệu.
                                </p>
                                <?php if ($canCreate): ?>
                                    <button type="button" class="far-btn far-btn-primary" data-bs-toggle="modal" data-bs-target="#modal_sync">
                                        <i class="fa fa-refresh"></i>
                                        <span>Đồng bộ từ danh sách VCK ngay</span>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            <?php if ($dataProvider->pagination): ?>
                <?php
                $totalCount = (int) $dataProvider->getTotalItemCount();
                $pageOffset = $totalCount > 0 ? min($dataProvider->pagination->offset + 1, $totalCount) : 0;
                $pageLimit  = min($dataProvider->pagination->offset + $dataProvider->pagination->pageSize, $totalCount);
                ?>
                <div class="far-pagination-footer">
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="small text-muted">
                            Hiển thị từ <strong><?php echo number_format($pageOffset); ?></strong> đến <strong><?php echo number_format($pageLimit); ?></strong> / Tổng cộng <strong><?php echo number_format($totalCount); ?></strong> người
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-muted fw-semibold text-nowrap">Hiển thị:</span>
                            <select class="form-select form-select-sm js-per-page-select" style="width: auto; font-weight: 600;">
                                <?php foreach ($pageSizes as $size): ?>
                                    <option value="<?php echo $size; ?>" <?php echo (int) $size === (int) $pageSize ? 'selected' : ''; ?>>
                                        <?php echo $size; ?> dòng / trang
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <?php $this->widget('CLinkPager', array(
                        'pages'                => $dataProvider->pagination,
                        'htmlOptions'          => array('class' => 'pagination pagination-sm mb-0'),
                        'header'               => '',
                        'selectedPageCssClass' => 'active',
                        'hiddenPageCssClass'   => 'disabled',
                        'firstPageLabel'       => '<i class="fa fa-angle-double-left"></i>',
                        'prevPageLabel'        => '<i class="fa fa-angle-left"></i>',
                        'nextPageLabel'        => '<i class="fa fa-angle-right"></i>',
                        'lastPageLabel'        => '<i class="fa fa-angle-double-right"></i>',
                    )); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modals -->
    <?php if ($canUpdate): ?>
        <?php $this->renderPartial('_modal_edit_row'); ?>
        <?php $this->renderPartial('_modal_merge_split'); ?>
        <?php $this->renderPartial('_modal_set_lucky'); ?>
    <?php endif; ?>

    <?php if ($canCreate): ?>
        <?php $this->renderPartial('_modal_add_person', array(
            'eventId'       => $eventId,
            'periodId'      => $periodId,
            'filterOptions' => $filterOptions,
            'roleList'      => $roleList,
        )); ?>
        <?php $this->renderPartial('_modal_gen_lucky', array(
            'eventId'       => $eventId,
            'stats'         => $stats,
            'filterOptions' => $filterOptions,
        )); ?>
        <?php $this->renderPartial('_modal_sync', array(
            'eventId'       => $eventId,
            'periodId'      => $periodId,
            'filterOptions' => $filterOptions,
        )); ?>
    <?php endif; ?>
<?php endif; ?>
