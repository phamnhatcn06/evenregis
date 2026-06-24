<?php
$this->pageTitle = 'Gửi hồ sơ dự thi Miss - ' . Yii::app()->name;
$baseUrl = Yii::app()->theme->baseUrl;
?>

<style>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

.miss-submit-page {
    min-height: 100vh;
    background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,250,245,0.95) 100%),
                url('<?php echo $baseUrl; ?>/assets/images/miss-bg.jpg') center center / cover no-repeat fixed;
    padding: 30px 15px;
    font-family: 'Montserrat', sans-serif;
}

.miss-submit-page::before {
    content: '';
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background:
        radial-gradient(ellipse at bottom left, rgba(255,107,107,0.15) 0%, transparent 50%),
        radial-gradient(ellipse at top right, rgba(255,159,67,0.15) 0%, transparent 50%),
        radial-gradient(ellipse at bottom right, rgba(0,206,201,0.1) 0%, transparent 50%);
    pointer-events: none;
    z-index: 0;
}

.miss-header {
    text-align: center;
    margin-bottom: 30px;
    position: relative;
    z-index: 1;
}

.miss-header .brand-logos {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 60px;
    margin-bottom: 20px;
}

.miss-header .brand-logos img {
    height: 60px;
    object-fit: contain;
}

.miss-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 2.5rem;
    font-weight: 700;
    background: linear-gradient(135deg, #d4145a 0%, #fbb03b 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    margin-bottom: 10px;
    letter-spacing: 2px;
}

.miss-subtitle {
    color: #666;
    font-size: 1.1rem;
    font-weight: 400;
}

.miss-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.08);
    border: 1px solid rgba(255,255,255,0.8);
    backdrop-filter: blur(10px);
    overflow: hidden;
    position: relative;
    z-index: 1;
    max-width: 900px;
    margin: 0 auto;
}

.miss-card-header {
    background: linear-gradient(135deg, #d4145a 0%, #fbb03b 100%);
    padding: 25px 30px;
    text-align: center;
}

.miss-card-header h4 {
    color: #fff;
    font-family: 'Montserrat', sans-serif;
    font-size: 1.5rem;
    font-weight: 600;
    margin: 0;
}

.miss-card-body {
    padding: 30px;
}

.contestant-info {
    background: linear-gradient(135deg, #f8f9fa 0%, #fff5f8 100%);
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
    border-left: 4px solid #d4145a;
}

.contestant-info strong {
    color: #d4145a;
}

.section-title {
    font-family: 'Montserrat', sans-serif;
    font-size: 1.3rem;
    color: #333;
    border-bottom: 2px solid #fbb03b;
    padding-bottom: 10px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-title i {
    color: #d4145a;
}

.form-label {
    font-weight: 500;
    color: #444;
    margin-bottom: 8px;
}

.form-control {
    border-radius: 10px;
    border: 1px solid #ddd;
    padding: 12px 15px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #d4145a;
    box-shadow: 0 0 0 3px rgba(212,20,90,0.1);
}

/* Image Upload with Preview */
.photo-upload-wrapper {
    position: relative;
    border: 2px dashed #ddd;
    border-radius: 12px;
    padding: 15px;
    text-align: center;
    transition: all 0.3s ease;
    background: #fafafa;
    min-height: 200px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.photo-upload-wrapper:hover {
    border-color: #d4145a;
    background: #fff5f8;
}

.photo-upload-wrapper.has-preview {
    padding: 10px;
}

.photo-upload-wrapper input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 10;
}

.photo-upload-wrapper .upload-icon {
    font-size: 2.5rem;
    color: #d4145a;
    margin-bottom: 10px;
}

.photo-upload-wrapper .upload-text {
    color: #666;
    font-size: 0.9rem;
}

.photo-upload-wrapper .upload-text strong {
    color: #d4145a;
}

.photo-preview {
    width: 100%;
    max-height: 300px;
    object-fit: contain;
    border-radius: 8px;
    display: none;
}

.photo-upload-wrapper.has-preview .photo-preview {
    display: block;
}

.photo-upload-wrapper.has-preview .upload-placeholder {
    display: none;
}

.photo-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
    display: block;
}

.photo-label .ratio {
    font-weight: 400;
    color: #888;
    font-size: 0.85rem;
}

.existing-photo {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
    color: #28a745;
    font-size: 0.85rem;
}

.existing-photo img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 6px;
    border: 2px solid #28a745;
}

/* Video Upload */
.video-upload-wrapper {
    border: 2px dashed #ddd;
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    background: #fafafa;
    position: relative;
    transition: all 0.3s ease;
}

.video-upload-wrapper:hover {
    border-color: #d4145a;
    background: #fff5f8;
}

.video-upload-wrapper input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.video-preview {
    max-width: 100%;
    max-height: 300px;
    border-radius: 8px;
    display: none;
    margin-top: 15px;
}

.alert-warning-custom {
    background: linear-gradient(135deg, #fff5e6 0%, #ffefef 100%);
    border: 1px solid #ffc107;
    border-radius: 12px;
    padding: 15px 20px;
    color: #856404;
}

.btn-submit-miss {
    background: linear-gradient(135deg, #d4145a 0%, #fbb03b 100%);
    border: none;
    border-radius: 50px;
    padding: 15px 50px;
    font-size: 1.1rem;
    font-weight: 600;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    box-shadow: 0 5px 20px rgba(212,20,90,0.3);
}

.btn-submit-miss:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(212,20,90,0.4);
    color: #fff;
}

.btn-submit-miss:disabled {
    opacity: 0.7;
    transform: none;
}

@media (max-width: 768px) {
    .miss-title {
        font-size: 1.8rem;
    }

    .brand-logos {
        gap: 30px !important;
    }

    .brand-logos img {
        height: 40px !important;
    }

    .miss-card-body {
        padding: 20px;
    }
}
</style>

<div class="miss-submit-page">
    <div class="miss-card">
        <div class="miss-card-header">
            <h4><i class="fa fa-star me-2"></i>Gửi hồ sơ dự thi</h4>
        </div>
        <div class="miss-card-body">
            <div class="contestant-info">
                <strong>Xin chào <?php echo CHtml::encode($model->attendee_name); ?>!</strong><br>
                Cuộc thi: <strong><?php echo CHtml::encode($model->contest_name); ?></strong><br>
                Đơn vị: <strong><?php echo CHtml::encode($model->property_name); ?></strong>
            </div>

            <form id="miss-submit-form" method="post" enctype="multipart/form-data">
                <div class="section-title">
                    <i class="fa fa-user"></i>Thông tin cá nhân
                </div>

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

                <div class="section-title mt-4">
                    <i class="fa fa-camera"></i>Ảnh dự thi
                </div>
                <p class="text-muted small mb-3">Định dạng: JPG, PNG. Tối đa 20MB/ảnh. Nhấn vào ô để chọn ảnh.</p>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="photo-label">
                            Ảnh chân dung 1 <span class="text-danger">*</span>
                            <span class="ratio">(tỉ lệ 3:4)</span>
                        </label>
                        <div class="photo-upload-wrapper" id="wrapper-portrait">
                            <input type="file" name="photo_portrait" accept="image/*"
                                   data-preview="preview-portrait"
                                   <?php echo empty($model->photo_portrait) ? 'required' : ''; ?>>
                            <div class="upload-placeholder">
                                <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="upload-text"><strong>Nhấn để chọn ảnh</strong><br>hoặc kéo thả vào đây</div>
                            </div>
                            <img class="photo-preview" id="preview-portrait" alt="Preview">
                        </div>
                        <?php if (!empty($model->photo_portrait)): ?>
                            <div class="existing-photo">
                                <i class="fa fa-check-circle"></i> Đã có ảnh
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="photo-label">
                            Ảnh chân dung 2
                            <span class="ratio">(tỉ lệ 3:4)</span>
                        </label>
                        <div class="photo-upload-wrapper" id="wrapper-portrait2">
                            <input type="file" name="photo_portrait_2" accept="image/*"
                                   data-preview="preview-portrait2">
                            <div class="upload-placeholder">
                                <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="upload-text"><strong>Nhấn để chọn ảnh</strong><br>hoặc kéo thả vào đây</div>
                            </div>
                            <img class="photo-preview" id="preview-portrait2" alt="Preview">
                        </div>
                        <?php if (!empty($model->photo_portrait_2)): ?>
                            <div class="existing-photo">
                                <i class="fa fa-check-circle"></i> Đã có ảnh
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label class="photo-label">
                            Ảnh toàn thân 1 <span class="text-danger">*</span>
                            <span class="ratio">(tỉ lệ 9:16)</span>
                        </label>
                        <div class="photo-upload-wrapper" id="wrapper-fullbody">
                            <input type="file" name="photo_full_body" accept="image/*"
                                   data-preview="preview-fullbody"
                                   <?php echo empty($model->photo_full_body) ? 'required' : ''; ?>>
                            <div class="upload-placeholder">
                                <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="upload-text"><strong>Nhấn để chọn ảnh</strong><br>hoặc kéo thả vào đây</div>
                            </div>
                            <img class="photo-preview" id="preview-fullbody" alt="Preview">
                        </div>
                        <?php if (!empty($model->photo_full_body)): ?>
                            <div class="existing-photo">
                                <i class="fa fa-check-circle"></i> Đã có ảnh
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label class="photo-label">
                            Ảnh toàn thân 2
                            <span class="ratio">(tỉ lệ 9:16)</span>
                        </label>
                        <div class="photo-upload-wrapper" id="wrapper-fullbody2">
                            <input type="file" name="photo_full_body_2" accept="image/*"
                                   data-preview="preview-fullbody2">
                            <div class="upload-placeholder">
                                <div class="upload-icon"><i class="fa fa-cloud-upload"></i></div>
                                <div class="upload-text"><strong>Nhấn để chọn ảnh</strong><br>hoặc kéo thả vào đây</div>
                            </div>
                            <img class="photo-preview" id="preview-fullbody2" alt="Preview">
                        </div>
                        <?php if (!empty($model->photo_full_body_2)): ?>
                            <div class="existing-photo">
                                <i class="fa fa-check-circle"></i> Đã có ảnh
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="section-title mt-4">
                    <i class="fa fa-video-camera"></i>Video dự thi
                </div>
                <p class="text-muted small mb-3">Định dạng: MP4, MOV. Tối đa 4 phút, 500MB.</p>

                <div class="video-upload-wrapper mb-4">
                    <input type="file" name="video_path" accept="video/*" id="video-input">
                    <div class="upload-icon"><i class="fa fa-film"></i></div>
                    <div class="upload-text"><strong>Nhấn để chọn video</strong><br>hoặc kéo thả vào đây</div>
                    <video class="video-preview" id="video-preview" controls></video>
                    <?php if (!empty($model->video_path)): ?>
                        <div class="existing-photo" style="justify-content: center; margin-top: 10px;">
                            <i class="fa fa-check-circle"></i> Đã có video
                        </div>
                    <?php endif; ?>
                </div>

                <div class="alert-warning-custom mb-4">
                    <i class="fa fa-exclamation-triangle me-1"></i>
                    <strong>Lưu ý:</strong> Sau khi gửi, bạn không thể chỉnh sửa hồ sơ. Vui lòng kiểm tra kỹ trước khi gửi.
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-submit-miss" id="btn_submit">
                        <i class="fa fa-paper-plane me-2"></i>Gửi hồ sơ dự thi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview handling
    var imageInputs = document.querySelectorAll('input[type="file"][accept="image/*"]');

    imageInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            var file = e.target.files[0];
            var previewId = input.getAttribute('data-preview');
            var preview = document.getElementById(previewId);
            var wrapper = input.closest('.photo-upload-wrapper');

            if (file && file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    wrapper.classList.add('has-preview');
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Video preview handling
    var videoInput = document.getElementById('video-input');
    var videoPreview = document.getElementById('video-preview');

    if (videoInput && videoPreview) {
        videoInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file && file.type.startsWith('video/')) {
                var url = URL.createObjectURL(file);
                videoPreview.src = url;
                videoPreview.style.display = 'block';
            }
        });
    }

    // Form submission
    var form = document.getElementById('miss-submit-form');
    var btn = document.getElementById('btn_submit');

    form.addEventListener('submit', function(e) {
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Đang gửi...';

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
