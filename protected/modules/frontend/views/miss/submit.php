<?php
$this->pageTitle = 'Gửi hồ sơ dự thi Miss - ' . Yii::app()->name;
$baseUrl = Yii::app()->theme->baseUrl;

Yii::app()->clientScript->registerCssFile($baseUrl . '/assets/css/pages/miss-frontend.css');
Yii::app()->clientScript->registerScriptFile($baseUrl . '/assets/js/pages/miss-submit.js', CClientScript::POS_END);
?>

<div class="miss-submit-page" style="background-image: url('<?php echo $baseUrl; ?>/assets/images/background-miss.jpg');">
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
                <input type="hidden" name="<?php echo Yii::app()->request->csrfTokenName; ?>" value="<?php echo Yii::app()->request->csrfToken; ?>" />
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
