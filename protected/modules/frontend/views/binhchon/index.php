<?php
/** @var array $finalists */
/** @var array $gate */
/** @var string $googleClientId */
/** @var int $maxVotes */
/** @var array|null $tokenVoter */
/** @var string $tokenLoginUrl */

$this->pageTitle = 'Bình chọn Miss Mường Thanh 2026 - Vòng Chung Kết';
$baseUrl = Yii::app()->theme->baseUrl;
$themePath = Yii::getPathOfAlias('webroot') . '/themes/hope-ui';

// Nạp file CSS chuyên biệt cho trang bình chọn.
// Thêm ?v=filemtime để trình duyệt không dùng bản CSS cũ đã cache (cùng convention
// với các trang daihoi) — tránh lỗi vỡ layout khi file CSS được cập nhật.
$cssVer = @filemtime($themePath . '/assets/css/pages/binhchon.css') ?: time();
Yii::app()->clientScript->registerCssFile($baseUrl . '/assets/css/pages/binhchon.css?v=' . $cssVer);

$voteUrl = $this->createUrl('/frontend/binhchon/vote');
$statusUrl = $this->createUrl('/frontend/binhchon/status');
$configured = $googleClientId && strpos($googleClientId, 'YOUR_GOOGLE_CLIENT_ID') === false;
$isOpen = !isset($gate['is_open']) || $gate['is_open'];
$isTokenAuth = $tokenVoter !== null;

// Định dạng ngày giờ "YYYY-MM-DD HH:MM:SS" → "dd/mm/yyyy HH:MM".
$fmtDateTime = function ($value) {
    if (empty($value)) {
        return '';
    }
    $ts = strtotime($value);
    return $ts ? date('d/m/Y H:i', $ts) : $value;
};
?>

<?php $this->renderPartial('application.modules.frontend.views.daihoi._site_header'); ?>

<main class="flex-grow-1">
<div class="container bc-page-container">

    <!-- ======================================================================
         1. HERO BANNER
         ====================================================================== -->
    <section class="bc-hero">
        <div class="bc-hero-inner">
            <div class="bc-hero-badge">
                <i class="fa fa-diamond"></i> Cuộc thi Miss Mường Thanh 2026
            </div>
            <h1 class="bc-hero-title">Cổng Bình Chọn Thí Sinh Yêu Thích</h1>
            <p class="bc-hero-subtitle">
                Vòng Chung Kết • Tôn vinh nét đẹp duyên dáng, trí tuệ và sự tự tin của những đại diện xuất sắc nhất. Hãy gửi trọn yêu thương qua từng lá phiếu bình chọn!
            </p>

            <!-- Rules quick grid -->
            <div class="bc-rules-grid">
                <div class="bc-rule-card">
                    <div class="bc-rule-icon"><i class="fa fa-shield"></i></div>
                    <div class="bc-rule-text">
                        <strong>Xác thực an toàn</strong>
                        <span>Tài khoản Google / Nội bộ</span>
                    </div>
                </div>
                <div class="bc-rule-card">
                    <div class="bc-rule-icon"><i class="fa fa-heart"></i></div>
                    <div class="bc-rule-text">
                        <strong>Hạn mức bình chọn</strong>
                        <span>Tối đa <?php echo (int) $maxVotes; ?> lượt / tài khoản</span>
                    </div>
                </div>
                <div class="bc-rule-card">
                    <div class="bc-rule-icon"><i class="fa fa-star"></i></div>
                    <div class="bc-rule-text">
                        <strong>Công bằng - Minh bạch</strong>
                        <span>1 phiếu duy nhất / thí sinh</span>
                    </div>
                </div>
                <div class="bc-rule-card">
                    <div class="bc-rule-icon"><i class="fa fa-bolt"></i></div>
                    <div class="bc-rule-text">
                        <strong>Kết quả tức thì</strong>
                        <span>Cập nhật theo thời gian thực</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ======================================================================
         2. CẢNH BÁO CẤU HÌNH GOOGLE SIGN-IN
         ====================================================================== -->
    <?php if (!$isTokenAuth && !$configured): ?>
        <div class="alert alert-warning text-center shadow-sm rounded-4 mb-4">
            <i class="fa fa-exclamation-triangle me-1"></i>
            Chưa cấu hình <strong>Google Client ID</strong>. Vui lòng khai báo trong
            <code>protected/config/params.php</code> → <code>googleSignIn.client_id</code> để bật đăng nhập Google.
        </div>
    <?php endif; ?>

    <!-- ======================================================================
         3. TRẠNG THÁI CỔNG ĐÓNG / ĐẾM NGƯỢC
         ====================================================================== -->
    <?php if (!$isOpen): ?>
        <?php
        // Chưa tới giờ mở (có start_at và server_time < start_at) → đếm ngược.
        $notYet = !empty($gate['start_at']) && !empty($gate['server_time']) && $gate['server_time'] < $gate['start_at'];
        ?>
        <div class="bc-gate-card">
            <div class="bc-gate-header">
                <i class="fa fa-lock"></i>
                <span><?php echo CHtml::encode(!empty($gate['message']) ? $gate['message'] : 'Cổng bình chọn hiện đang đóng.'); ?></span>
            </div>

            <div class="bc-gate-times">
                <?php if (!empty($gate['start_at'])): ?>
                    <div class="bc-gate-time-item">
                        <i class="fa fa-clock-o text-warning"></i>
                        <span>Thời gian mở: <strong><?php echo CHtml::encode($fmtDateTime($gate['start_at'])); ?></strong></span>
                    </div>
                <?php endif; ?>
                <?php if (!empty($gate['end_at'])): ?>
                    <div class="bc-gate-time-item">
                        <i class="fa fa-hourglass-end text-danger"></i>
                        <span>Thời gian đóng: <strong><?php echo CHtml::encode($fmtDateTime($gate['end_at'])); ?></strong></span>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($notYet): ?>
                <div class="text-center mt-3">
                    <div class="small text-muted fw-semibold text-uppercase letter-spacing-1 mb-2">Cổng mở sau:</div>
                    <div id="vote-countdown"
                         class="d-none"
                         data-start="<?php echo CHtml::encode(str_replace(' ', 'T', $gate['start_at'])); ?>"
                         data-now="<?php echo CHtml::encode(str_replace(' ', 'T', $gate['server_time'])); ?>">
                    </div>
                    <div class="bc-countdown-boxes">
                        <div class="bc-countdown-box">
                            <div class="bc-countdown-num" id="countdown-days">--</div>
                            <div class="bc-countdown-lbl">Ngày</div>
                        </div>
                        <div class="bc-countdown-box">
                            <div class="bc-countdown-num" id="countdown-hours">--</div>
                            <div class="bc-countdown-lbl">Giờ</div>
                        </div>
                        <div class="bc-countdown-box">
                            <div class="bc-countdown-num" id="countdown-mins">--</div>
                            <div class="bc-countdown-lbl">Phút</div>
                        </div>
                        <div class="bc-countdown-box">
                            <div class="bc-countdown-num" id="countdown-secs">--</div>
                            <div class="bc-countdown-lbl">Giây</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- ======================================================================
         4. VOTER DASHBOARD / ĐĂNG NHẬP
         ====================================================================== -->
    <?php if ($isOpen): ?>
    <div id="voter-box"
         class="bc-voter-card"
         data-vote-url="<?php echo $voteUrl; ?>"
         data-status-url="<?php echo $statusUrl; ?>"
         data-client-id="<?php echo CHtml::encode($googleClientId); ?>"
         data-max-votes="<?php echo (int) $maxVotes; ?>"
         data-token-auth="<?php echo $isTokenAuth ? '1' : '0'; ?>">

        <!-- Trạng thái chưa đăng nhập -->
        <?php if (!$isTokenAuth): ?>
        <div id="signin-area" class="bc-signin-wrapper">
            <div class="bc-signin-icon">
                <i class="fa fa-unlock-alt"></i>
            </div>
            <h3 class="bc-signin-title">Đăng nhập để tham gia bình chọn</h3>
            <p class="bc-signin-desc">
                Sử dụng tài khoản Google hoặc tài khoản nội bộ Mường Thanh để xác thực danh tính và gửi bình chọn cho thí sinh bạn yêu thích.
            </p>

            <div class="bc-google-btn-wrapper">
                <div id="g_id_signin" class="d-inline-block"></div>
            </div>

            <div class="mt-3">
                <span class="text-muted small me-2">hoặc</span>
                <a href="<?php echo CHtml::encode($tokenLoginUrl); ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fa fa-id-badge me-1"></i>Đăng nhập bằng tài khoản nội bộ
                </a>
            </div>

            <div class="bc-signin-security-note">
                <i class="fa fa-shield"></i> Xác thực tức thì • 100% minh bạch • Bảo vệ bình chọn công bằng
            </div>
        </div>
        <?php endif; ?>

        <!-- Trạng thái đã đăng nhập -->
        <div id="signed-in-info" class="bc-signed-in-wrapper <?php echo $isTokenAuth ? '' : 'd-none'; ?>">
            <div class="bc-voter-profile">
                <div class="bc-voter-avatar">
                    <span id="voter-avatar-letter"><?php echo $isTokenAuth && !empty($tokenVoter['name']) ? mb_strtoupper(mb_substr(trim($tokenVoter['name']), 0, 1, 'UTF-8'), 'UTF-8') : 'M'; ?></span>
                </div>
                <div class="bc-voter-info-text">
                    <div class="bc-voter-greeting">Xin chào người bình chọn,</div>
                    <div class="bc-voter-name">
                        <strong id="voter-name"><?php echo $isTokenAuth ? CHtml::encode($tokenVoter['name']) : ''; ?></strong>
                        <span class="bc-verified-badge"><i class="fa fa-check-circle"></i> Đã xác thực</span>
                    </div>
                </div>
            </div>

            <div class="bc-voter-quota-box">
                <div class="bc-quota-content">
                    <div class="bc-quota-label">Lượt bình chọn còn lại</div>
                    <div>
                        <span id="remaining-votes" class="bc-quota-number"><?php echo (int) $maxVotes; ?></span>
                        <span class="bc-quota-total">/ <?php echo (int) $maxVotes; ?> lượt</span>
                    </div>
                </div>
                <div id="quota-hearts-container" class="bc-quota-hearts" title="Số lượt bình chọn khả dụng">
                    <?php for ($i = 0; $i < (int) $maxVotes; $i++): ?>
                        <i class="fa fa-heart bc-heart-token"></i>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ======================================================================
         5. TOOLBAR (TÌM KIẾM, SẮP XẾP, ĐẾM THÍ SINH)
         ====================================================================== -->
    <div class="bc-toolbar">
        <div class="bc-search-box">
            <i class="fa fa-search bc-search-icon"></i>
            <input type="text"
                   id="contestant-search"
                   class="bc-search-input"
                   placeholder="Tìm theo tên hoặc số báo danh (SBD)..."
                   autocomplete="off">
            <button type="button" id="contestant-search-clear" class="bc-search-clear" title="Xóa tìm kiếm">
                <i class="fa fa-times-circle"></i>
            </button>
        </div>

        <div class="bc-filter-actions">
            <div class="bc-sort-group" role="group" aria-label="Sắp xếp thí sinh">
                <button type="button" class="bc-sort-btn active" data-sort="default">
                    <i class="fa fa-sort-numeric-asc me-1"></i>SBD
                </button>
                <button type="button" class="bc-sort-btn" data-sort="votes">
                    <i class="fa fa-fire me-1 text-danger"></i>Nhiều phiếu nhất
                </button>
                <button type="button" class="bc-sort-btn" data-sort="name">
                    <i class="fa fa-sort-alpha-asc me-1"></i>Tên A-Z
                </button>
            </div>
            <div class="bc-count-badge">
                Tổng: <strong id="contestant-count"><?php echo count($finalists); ?></strong> thí sinh
            </div>
        </div>
    </div>

    <!-- ======================================================================
         6. DANH SÁCH THÍ SINH CHUNG KẾT
         ====================================================================== -->
    <div class="row g-3 g-md-4" id="finalist-grid">
        <?php foreach ($finalists as $f): ?>
            <div class="col-6 col-md-4 col-lg-3"
                 data-name="<?php echo CHtml::encode($f['name']); ?>"
                 data-sbd="<?php echo CHtml::encode($f['number']); ?>"
                 data-unit="<?php echo CHtml::encode($f['unit']); ?>"
                 data-votes="<?php echo (int) $f['vote_count']; ?>"
                 data-cid="<?php echo (int) $f['contestant_id']; ?>">
                <div class="card h-100 contestant-card"
                     data-name="<?php echo CHtml::encode($f['name']); ?>"
                     data-sbd="<?php echo CHtml::encode($f['number']); ?>"
                     data-unit="<?php echo CHtml::encode($f['unit']); ?>"
                     data-votes="<?php echo (int) $f['vote_count']; ?>"
                     data-cid="<?php echo (int) $f['contestant_id']; ?>">

                    <div class="bc-card-photo-wrapper" title="Nhấn để xem chi tiết ảnh">

                        <span class="bc-vote-pill">
                            <i class="fa fa-heart"></i>
                            <span class="vote-count" data-contestant="<?php echo (int) $f['contestant_id']; ?>"><?php echo (int) $f['vote_count']; ?></span>
                        </span>

                        <img src="<?php echo CHtml::encode($f['photo']); ?>"
                             class="card-img-top bc-card-photo"
                             alt="<?php echo CHtml::encode($f['name']); ?>"
                             loading="lazy">

                        <button type="button" class="bc-photo-zoom-btn" title="Xem chi tiết thí sinh">
                            <i class="fa fa-search-plus"></i>
                        </button>
                    </div>

                    <div class="card-body d-flex flex-column">
                        <h5 class="bc-contestant-name" title="<?php echo CHtml::encode($f['name']); ?>">
                            <?php echo CHtml::encode($f['name']); ?>
                        </h5>
                        <?php if (!empty($f['unit'])): ?>
                            <div class="bc-contestant-unit" title="<?php echo CHtml::encode($f['unit']); ?>">
                                <i class="fa fa-map-marker"></i> <?php echo CHtml::encode($f['unit']); ?>
                            </div>
                        <?php else: ?>
                            <div class="bc-contestant-unit text-muted small">&nbsp;</div>
                        <?php endif; ?>

                        <div class="bc-card-action">
                            <button type="button"
                                    class="btn btn-sm w-100 btn-vote btn-outline-primary"
                                    data-contestant="<?php echo (int) $f['contestant_id']; ?>"
                                    <?php echo $isOpen ? '' : 'disabled'; ?>>
                                <i class="fa fa-heart me-1"></i>Bình chọn
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Empty search state -->
    <div id="empty-search" class="bc-empty-search">
        <div class="bc-empty-icon"><i class="fa fa-search"></i></div>
        <h5 class="bc-empty-title">Không tìm thấy thí sinh phù hợp</h5>
        <p class="bc-empty-desc">Vui lòng kiểm tra lại từ khóa tên thí sinh hoặc số báo danh.</p>
    </div>

    <!-- ======================================================================
         7. PHOTO DETAIL MODAL (LIGHTBOX)
         ====================================================================== -->
    <div class="modal fade bc-modal" id="bcPhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl bc-modal-dialog">
            <div class="modal-content position-relative">
                <button type="button" class="bc-modal-close-btn" data-bs-dismiss="modal" aria-label="Đóng">
                    <i class="fa fa-times"></i>
                </button>
                <div class="bc-modal-body">
                    <div class="bc-modal-img-col">
                        <img id="modal-photo" src="" alt="Contestant Photo" class="bc-modal-img">
                    </div>
                    <div class="bc-modal-info-col">
                        <div class="mb-2">
                        </div>
                        <h3 id="modal-name" class="fw-bold mb-2 text-dark"></h3>
                        <div class="text-muted mb-4 d-flex align-items-center gap-2">
                            <i class="fa fa-map-marker text-danger"></i> <span id="modal-unit"></span>
                        </div>
                        <div class="p-3 bg-light rounded-4 mb-4 border border-light">
                            <div class="small text-muted mb-1 text-uppercase fw-semibold letter-spacing-1">Số lượt bình chọn hiện tại</div>
                            <div class="h3 fw-bold text-danger mb-0 d-flex align-items-center gap-2">
                                <i class="fa fa-heart"></i>
                                <span><span id="modal-votes">0</span> phiếu</span>
                            </div>
                        </div>
                        <div class="mt-auto">
                            <button type="button"
                                    id="modal-vote-btn"
                                    class="btn btn-vote btn-outline-primary w-100 py-3 rounded-pill"
                                    data-contestant=""
                                    <?php echo $isOpen ? '' : 'disabled'; ?>>
                                <i class="fa fa-heart me-1"></i>Bình chọn cho thí sinh
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ======================================================================
         8. MOBILE STICKY VOTER BAR
         ====================================================================== -->
    <?php if ($isOpen): ?>
    <div id="bc-sticky-bar" class="bc-sticky-bar d-md-none">
        <i class="fa fa-heart bc-sticky-heart"></i>
        <span>Còn lại: <strong id="sticky-remaining" class="bc-sticky-quota"><?php echo (int) $maxVotes; ?></strong> lượt</span>
        <button type="button" id="bc-scroll-top" class="bc-scroll-top-btn" title="Lên đầu trang">
            <i class="fa fa-arrow-up"></i>
        </button>
    </div>
    <?php endif; ?>

</div>
</main>

<?php $this->renderPartial('application.modules.frontend.views.daihoi._site_footer'); ?>
