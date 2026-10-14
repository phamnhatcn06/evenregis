<?php
/** @var array $finalists */
/** @var array $gate */
/** @var string $googleClientId */
/** @var int $maxVotes */

$this->pageTitle = 'Bình chọn Miss - Vòng chung kết';
$voteUrl = $this->createUrl('/frontend/binhchon/vote');
$statusUrl = $this->createUrl('/frontend/binhchon/status');
$configured = $googleClientId && strpos($googleClientId, 'YOUR_GOOGLE_CLIENT_ID') === false;
$isOpen = !isset($gate['is_open']) || $gate['is_open'];
?>

<div class="text-center mb-4">
    <h2 class="fw-bold">Bình chọn Miss - Vòng chung kết</h2>
    <p class="text-muted mb-1">Đăng nhập bằng tài khoản Google để bình chọn cho thí sinh bạn yêu thích.</p>
    <p class="text-muted">Mỗi tài khoản được tối đa <strong><?php echo (int) $maxVotes; ?> lượt</strong>, mỗi thí sinh chỉ bình chọn 1 lần.</p>
</div>

<?php if (!$configured): ?>
    <div class="alert alert-warning text-center">
        <i class="fa fa-exclamation-triangle me-1"></i>
        Chưa cấu hình <strong>Google Client ID</strong>. Vui lòng khai báo trong
        <code>protected/config/params.php</code> → <code>googleSignIn.client_id</code> để bật đăng nhập Google.
    </div>
<?php endif; ?>

<!-- Khu vực đăng nhập / thông tin người bình chọn -->
<div id="voter-box"
     class="card shadow-sm mb-4"
     data-vote-url="<?php echo $voteUrl; ?>"
     data-status-url="<?php echo $statusUrl; ?>"
     data-client-id="<?php echo CHtml::encode($googleClientId); ?>"
     data-max-votes="<?php echo (int) $maxVotes; ?>">
    <div class="card-body text-center">
        <div id="signin-area">
            <p class="mb-2">Vui lòng đăng nhập để bình chọn:</p>
            <div id="g_id_signin" class="d-inline-block"></div>
        </div>
        <div id="signed-in-info" class="d-none">
            <div>Xin chào, <strong id="voter-name"></strong></div>
            <div class="text-muted">Bạn còn <span id="remaining-votes" class="fw-bold text-primary"><?php echo (int) $maxVotes; ?></span>/<?php echo (int) $maxVotes; ?> lượt bình chọn</div>
        </div>
    </div>
</div>

<!-- Danh sách thí sinh -->
<div class="row g-3" id="finalist-grid">
    <?php foreach ($finalists as $f): ?>
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm contestant-card">
                <img src="<?php echo CHtml::encode($f['photo']); ?>" class="card-img-top"
                     alt="<?php echo CHtml::encode($f['name']); ?>"
                     style="aspect-ratio:4/5;object-fit:cover;">
                <div class="card-body d-flex flex-column">
                    <?php if (!empty($f['number'])): ?>
                        <span class="badge bg-primary align-self-start mb-1"><?php echo CHtml::encode($f['number']); ?></span>
                    <?php endif; ?>
                    <h6 class="fw-bold mb-1"><?php echo CHtml::encode($f['name']); ?></h6>
                    <?php if (!empty($f['unit'])): ?>
                        <small class="text-muted mb-2"><?php echo CHtml::encode($f['unit']); ?></small>
                    <?php endif; ?>
                    <div class="mt-auto">
                        <div class="text-muted small mb-2">
                            <i class="fa fa-heart text-danger me-1"></i>
                            <span class="vote-count" data-contestant="<?php echo (int) $f['contestant_id']; ?>"><?php echo (int) $f['vote_count']; ?></span> phiếu
                        </div>
                        <button type="button"
                                class="btn btn-sm w-100 btn-vote btn-outline-primary"
                                data-contestant="<?php echo (int) $f['contestant_id']; ?>">
                            <i class="fa fa-heart me-1"></i>Bình chọn
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
