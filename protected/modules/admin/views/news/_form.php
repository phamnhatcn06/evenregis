<?php
/**
 * Form thêm/sửa tin tức — bố cục kiểu WordPress (2 cột).
 * Cột chính: tiêu đề + trình soạn thảo (TinyMCE) + tóm tắt.
 * Cột phải: hộp Xuất bản, Phân loại, Ảnh đại diện.
 */
$theme = Yii::app()->theme->baseUrl;
$cs = Yii::app()->clientScript;

// Flatpickr cho ô ngày xuất bản
$cs->registerCssFile($theme . '/assets/vendor/flatpickr/dist/flatpickr.min.css');
$cs->registerScriptFile($theme . '/assets/vendor/flatpickr/dist/flatpickr.min.js', CClientScript::POS_END);

// TinyMCE (self-hosted, không dùng CDN)
$cs->registerScriptFile($theme . '/assets/vendor/tinymce/tinymce.min.js', CClientScript::POS_END);
$cs->registerScriptFile($theme . '/assets/js/pages/news-form.js', CClientScript::POS_END);

$uploadUrl = $this->createUrl('uploadImage');
$langUrl = $theme . '/assets/vendor/tinymce/langs/vi.js';
?>
<div class="form-wrap news-editor">
<?php $form = $this->beginWidget('booster.widgets.TbActiveForm', array(
    'id' => 'news-form',
    'enableClientValidation' => false,
    'htmlOptions' => array('class' => 'news-form'),
)); ?>

<?php echo $form->errorSummary($model); ?>

<div class="row g-3">
    <!-- ===================== CỘT CHÍNH ===================== -->
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-body">
                <?php echo $form->labelEx($model, 'title', array('class' => 'form-label fw-bold')); ?>
                <?php echo $form->textField($model, 'title', array(
                    'maxlength' => 255,
                    'class' => 'form-control form-control-lg',
                    'placeholder' => 'Nhập tiêu đề tin tức...',
                )); ?>
                <?php echo $form->error($model, 'title'); ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Nội dung</h5></div>
            <div class="card-body">
                <?php echo $form->textArea($model, 'content', array(
                    'id' => 'News_content',
                    'rows' => 18,
                    'class' => 'form-control news-content-editor',
                    'data-upload-url' => $uploadUrl,
                    'data-lang-url' => $langUrl,
                )); ?>
                <?php echo $form->error($model, 'content'); ?>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Tóm tắt</h5></div>
            <div class="card-body">
                <?php echo $form->textArea($model, 'excerpt', array(
                    'rows' => 3,
                    'class' => 'form-control',
                    'placeholder' => 'Đoạn mô tả ngắn hiển thị ở danh sách tin...',
                )); ?>
                <div class="form-text">Tối đa khoảng 300 ký tự. Để trống sẽ tự lấy phần đầu nội dung.</div>
            </div>
        </div>
    </div>

    <!-- ===================== CỘT PHẢI ===================== -->
    <div class="col-lg-4">
        <!-- Hộp Xuất bản -->
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0"><i class="fa fa-paper-plane me-1"></i> Xuất bản</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <?php echo $form->label($model, 'is_published', array('class' => 'form-label')); ?>
                    <?php echo $form->dropDownList($model, 'is_published', array(
                        News::IS_DRAFT => 'Bản nháp',
                        News::IS_PUBLISHED => 'Đã xuất bản',
                    ), array('class' => 'form-select')); ?>
                </div>
                <div class="mb-3">
                    <?php echo $form->label($model, 'published_at', array('class' => 'form-label')); ?>
                    <?php echo $form->textField($model, 'published_at', array(
                        'class' => 'form-control news-datetime',
                        'placeholder' => 'YYYY-MM-DD HH:MM:SS',
                        'autocomplete' => 'off',
                    )); ?>
                    <div class="form-text">Để trống = xuất bản ngay khi lưu.</div>
                </div>
                <div class="form-check form-switch mb-3">
                    <?php echo $form->checkBox($model, 'is_featured', array('class' => 'form-check-input')); ?>
                    <?php echo $form->label($model, 'is_featured', array('class' => 'form-check-label')); ?>
                </div>
            </div>
            <div class="card-footer d-grid">
                <button id="btn-submit" type="submit" class="btn btn-primary">
                    <i class="fa fa-save me-1"></i> Lưu tin tức
                </button>
            </div>
        </div>

        <!-- Hộp Phân loại -->
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0"><i class="fa fa-folder-open me-1"></i> Phân loại</h5></div>
            <div class="card-body">
                <div class="mb-3">
                    <?php echo $form->labelEx($model, 'event_id', array('class' => 'form-label')); ?>
                    <?php echo $form->dropDownList($model, 'event_id', $eventList, array(
                        'class' => 'form-select',
                        'prompt' => '-- Chọn sự kiện --',
                    )); ?>
                    <?php echo $form->error($model, 'event_id'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $form->label($model, 'category_id', array('class' => 'form-label')); ?>
                    <?php echo $form->dropDownList($model, 'category_id', $newsCategories, array(
                        'class' => 'form-select',
                        'prompt' => '-- Chọn danh mục --',
                    )); ?>
                </div>
                <div class="mb-0">
                    <?php echo $form->label($model, 'category', array('class' => 'form-label')); ?>
                    <?php echo $form->dropDownList($model, 'category', $categoryOptions, array(
                        'class' => 'form-select',
                    )); ?>
                </div>
            </div>
        </div>

        <!-- Hộp Ảnh đại diện -->
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0"><i class="fa fa-image me-1"></i> Ảnh đại diện</h5></div>
            <div class="card-body text-center">
                <div id="thumbnail-preview-wrap" class="mb-2 <?php echo $model->thumbnail ? '' : 'd-none'; ?>">
                    <img id="thumbnail-preview" src="<?php echo $model->thumbnail ? CHtml::encode($model->thumbnail) : ''; ?>"
                         class="img-fluid rounded border" alt="Ảnh đại diện" style="max-height:200px;">
                </div>
                <div id="thumbnail-placeholder" class="text-muted py-4 border rounded mb-2 <?php echo $model->thumbnail ? 'd-none' : ''; ?>">
                    <i class="fa fa-image fa-2x d-block mb-2"></i>
                    Chưa có ảnh đại diện
                </div>

                <?php echo $form->hiddenField($model, 'thumbnail', array('id' => 'News_thumbnail')); ?>
                <input type="file" id="thumbnail-file" accept="image/*" class="d-none"
                       data-upload-url="<?php echo CHtml::encode($uploadUrl); ?>">

                <div class="d-grid gap-2">
                    <button type="button" id="btn-choose-thumbnail" class="btn btn-outline-primary btn-sm">
                        <i class="fa fa-upload me-1"></i> <span>Tải ảnh lên</span>
                    </button>
                    <button type="button" id="btn-remove-thumbnail" class="btn btn-outline-danger btn-sm <?php echo $model->thumbnail ? '' : 'd-none'; ?>">
                        <i class="fa fa-trash me-1"></i> Gỡ ảnh
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->endWidget(); ?>
</div><!-- /.news-editor -->
