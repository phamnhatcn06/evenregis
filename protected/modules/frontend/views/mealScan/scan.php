<?php
/** Màn quét fullscreen. Cấu hình truyền qua data-* để JS đọc (không inline JS). */
$this->pageTitle = 'Trạm chấm ăn - ' . $station['name'];
?>
<div class="ms-wrap"
     id="ms-root"
     data-scan-url="<?php echo $this->createUrl('doScan'); ?>"
     data-active-meals-url="<?php echo $this->createUrl('activeMeals'); ?>"
     data-sync-url="<?php echo $this->createUrl('sync'); ?>"
     data-stats-url="<?php echo $this->createUrl('stats'); ?>"
     data-logout-url="<?php echo $this->createUrl('logout'); ?>"
     data-station-id="<?php echo (int) $station['id']; ?>">

    <div class="ms-topbar">
        <div class="ms-station">
            <i class="bi bi-qr-code-scan me-1"></i><?php echo CHtml::encode($station['name']); ?>
        </div>
        <div class="ms-meal ms-meal-none" id="ms-meal">Đang tải bữa ăn...</div>
        <div class="d-flex align-items-center gap-3">
            <span class="ms-conn"><span class="ms-dot off" id="ms-conn-dot"></span><span id="ms-conn-text">Đang kết nối...</span></span>
            <a href="<?php echo $this->createUrl('logout'); ?>" class="btn btn-sm btn-outline-light">
                <i class="bi bi-box-arrow-right me-1"></i>Thoát
            </a>
        </div>
    </div>

    <div class="ms-main">
        <div class="ms-card" id="ms-card">
            <div class="ms-icon" id="ms-icon"><i class="bi bi-upc-scan"></i></div>
            <div class="ms-status" id="ms-status">Sẵn sàng quét</div>
            <div class="ms-person" id="ms-person"></div>
            <div class="ms-org" id="ms-org"></div>
            <div class="ms-sub" id="ms-sub">Đưa thẻ đại hội vào đầu đọc QR</div>
        </div>
    </div>

    <div class="ms-footer">
        <div class="ms-counter">Đã chấm bữa này: <strong id="ms-count">0</strong></div>
        <div class="d-flex align-items-center gap-3">
            <span class="ms-hint" id="ms-queue-hint"></span>
            <button type="button" class="btn btn-sm btn-outline-light" id="ms-sync-btn" style="display:none;">
                <i class="bi bi-arrow-repeat me-1"></i>Đồng bộ ngay
            </button>
        </div>
    </div>

    <input type="text" class="ms-scan-input" id="ms-scan-input" autocomplete="off" />
</div>
