<?php
/** Đăng nhập trạm chấm ăn: chọn trạm + nhập mã truy cập. */
$this->pageTitle = 'Đăng nhập trạm chấm ăn';
?>
<div class="row justify-content-center">
    <div class="col-12 col-sm-10 col-md-6 col-lg-5">
        <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="bi bi-qr-code-scan" style="font-size:3rem;color:#0d6efd;"></i>
                    </div>
                    <h4 class="fw-bold mb-1">Trạm chấm ăn</h4>
                    <p class="text-muted small mb-0">Chọn cửa/trạm và nhập mã truy cập để bắt đầu quét QR.</p>
                </div>

                <form method="post" action="<?php echo $this->createUrl('login'); ?>">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Trạm / cửa</label>
                        <select name="station_id" class="form-select form-select-lg" required>
                            <option value="">-- Chọn trạm --</option>
                            <?php foreach ($stations as $st): ?>
                                <option value="<?php echo (int) $st['id']; ?>">
                                    <?php
                                    echo CHtml::encode($st['name']);
                                    if (!empty($st['location'])) {
                                        echo ' (' . CHtml::encode($st['location']) . ')';
                                    }
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($stations)): ?>
                            <div class="form-text text-danger">
                                Chưa có trạm nào được cấu hình. Vui lòng liên hệ BTC.
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Mã truy cập</label>
                        <input type="password" name="passcode" class="form-control form-control-lg"
                               inputmode="numeric" autocomplete="off" required placeholder="Nhập mã truy cập của trạm">
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Mở màn quét
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
