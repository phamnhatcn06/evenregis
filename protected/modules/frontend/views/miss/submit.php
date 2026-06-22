<?php
$this->pageTitle = 'Gửi hồ sơ dự thi Miss - ' . Yii::app()->name;
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0"><i class="fa fa-star me-2"></i>Gửi hồ sơ dự thi Miss</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>Xin chào <?php echo CHtml::encode($model->attendee_name); ?>!</strong><br>
                    Cuộc thi: <strong><?php echo CHtml::encode($model->contest_name); ?></strong><br>
                    Đơn vị: <strong><?php echo CHtml::encode($model->property_name); ?></strong>
                </div>

                <form id="miss-submit-form" method="post" enctype="multipart/form-data">
                    <h5 class="border-bottom pb-2 mb-3">Thông tin cá nhân</h5>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Chiều cao (cm) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" class="form-control" name="BeautyContestants[height_cm]"
                                   value="<?php echo CHtml::encode($model->height_cm); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Cân nặng (kg) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" class="form-control" name="BeautyContestants[weight_kg]"
                                   value="<?php echo CHtml::encode($model->weight_kg); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Số đo 3 vòng <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="BeautyContestants[measurements]"
                                   value="<?php echo CHtml::encode($model->measurements); ?>"
                                   placeholder="VD: 90-60-90" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Năng khiếu / Tài năng</label>
                        <input type="text" class="form-control" name="BeautyContestants[talent]"
                               value="<?php echo CHtml::encode($model->talent); ?>"
                               placeholder="VD: Múa dân gian, Hát...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tiểu sử / Giới thiệu bản thân</label>
                        <textarea class="form-control" name="BeautyContestants[bio]" rows="4"
                                  placeholder="Giới thiệu ngắn gọn về bản thân..."><?php echo CHtml::encode($model->bio); ?></textarea>
                    </div>

                    <h5 class="border-bottom pb-2 mb-3 mt-4">Ảnh dự thi</h5>
                    <p class="text-muted small">Định dạng: JPG, PNG. Tối đa 20MB/ảnh.</p>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ảnh chân dung 1 (tỉ lệ 3:4) <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="photo_portrait" accept="image/*"
                                   <?php echo empty($model->photo_portrait) ? 'required' : ''; ?>>
                            <?php if (!empty($model->photo_portrait)): ?>
                                <small class="text-success"><i class="fa fa-check"></i> Đã có ảnh</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ảnh chân dung 2 (tỉ lệ 3:4)</label>
                            <input type="file" class="form-control" name="photo_portrait_2" accept="image/*">
                            <?php if (!empty($model->photo_portrait_2)): ?>
                                <small class="text-success"><i class="fa fa-check"></i> Đã có ảnh</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ảnh toàn thân 1 (tỉ lệ 9:16) <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="photo_full_body" accept="image/*"
                                   <?php echo empty($model->photo_full_body) ? 'required' : ''; ?>>
                            <?php if (!empty($model->photo_full_body)): ?>
                                <small class="text-success"><i class="fa fa-check"></i> Đã có ảnh</small>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ảnh toàn thân 2 (tỉ lệ 9:16)</label>
                            <input type="file" class="form-control" name="photo_full_body_2" accept="image/*">
                            <?php if (!empty($model->photo_full_body_2)): ?>
                                <small class="text-success"><i class="fa fa-check"></i> Đã có ảnh</small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <h5 class="border-bottom pb-2 mb-3 mt-4">Video dự thi</h5>
                    <p class="text-muted small">Định dạng: MP4, MOV. Tối đa 4 phút, 500MB.</p>

                    <div class="mb-3">
                        <label class="form-label">Video dự thi</label>
                        <input type="file" class="form-control" name="video_path" accept="video/*">
                        <?php if (!empty($model->video_path)): ?>
                            <small class="text-success"><i class="fa fa-check"></i> Đã có video</small>
                        <?php endif; ?>
                    </div>

                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle me-1"></i>
                        <strong>Lưu ý:</strong> Sau khi gửi, bạn không thể chỉnh sửa hồ sơ. Vui lòng kiểm tra kỹ trước khi gửi.
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg" id="btn_submit">
                            <i class="fa fa-paper-plane me-1"></i>Gửi hồ sơ dự thi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('miss-submit-form');
    var btn = document.getElementById('btn_submit');

    form.addEventListener('submit', function(e) {
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang gửi...';

        var fileInputs = form.querySelectorAll('input[type="file"]');
        var maxImageSize = 20 * 1024 * 1024;
        var maxVideoSize = 500 * 1024 * 1024;
        var hasError = false;

        fileInputs.forEach(function(input) {
            if (input.files.length > 0) {
                var file = input.files[0];
                var isVideo = input.name === 'video_path';
                var maxSize = isVideo ? maxVideoSize : maxImageSize;

                if (file.size > maxSize) {
                    e.preventDefault();
                    hasError = true;
                    var sizeLabel = isVideo ? '500MB' : '20MB';
                    alert('File ' + file.name + ' vượt quá kích thước cho phép (' + sizeLabel + ')');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            }
        });
    });
});
</script>
