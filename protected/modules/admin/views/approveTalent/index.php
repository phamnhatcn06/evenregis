<?php
$this->breadcrumbs = array(
    'Xét duyệt Văn nghệ',
);

$this->menu = array();
$this->Tabletitle = 'Xét duyệt tiết mục Văn nghệ';

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl . '/assets/css/pages/approve-talent.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->theme->baseUrl . '/assets/js/pages/approve-talent.js', CClientScript::POS_END);
?>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Hội diễn</label>
                <select name="show_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <?php foreach ($shows as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($_GET['show_id']) && $_GET['show_id'] == $id) ? 'selected' : ''; ?>>
                            <?php echo CHtml::encode($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Thể loại</label>
                <select name="category_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <?php foreach ($categories as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($_GET['category_id']) && $_GET['category_id'] == $id) ? 'selected' : ''; ?>>
                            <?php echo CHtml::encode($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Trạng thái duyệt</label>
                <select name="status" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <?php foreach (TalentEntries::getStatusOptions() as $val => $label): ?>
                        <option value="<?php echo $val; ?>" <?php echo (isset($_GET['status']) && $_GET['status'] !== '' && $_GET['status'] == $val) ? 'selected' : ''; ?>>
                            <?php echo CHtml::encode($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Video</label>
                <select name="has_video" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <option value="1" <?php echo (isset($_GET['has_video']) && $_GET['has_video'] == '1') ? 'selected' : ''; ?>>Đã upload</option>
                    <option value="0" <?php echo (isset($_GET['has_video']) && $_GET['has_video'] === '0') ? 'selected' : ''; ?>>Chưa upload</option>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label">&nbsp;</label>
                <div>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-search me-1"></i>Lọc</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row" id="entries-grid">
    <?php if (empty($entries)): ?>
        <div class="col-12">
            <div class="alert alert-info">Không có tiết mục nào.</div>
        </div>
    <?php else: ?>
        <?php foreach ($entries as $e): ?>
            <?php
            $categoryName = !empty($e->category_name) ? $e->category_name : '';
            $propertyName = !empty($e->property_name) ? $e->property_name : '';
            $duration = $e->duration_seconds ? gmdate('i:s', $e->duration_seconds) : '';
            ?>
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card talent-card h-100" data-id="<?php echo $e->id; ?>">
                    <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
                        <span class="badge bg-primary"><?php echo CHtml::encode($categoryName); ?></span>
                        <?php echo TalentEntries::getStatusLabel($e->status); ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-2"><?php echo CHtml::encode($e->title); ?></h5>
                        <p class="card-text text-muted mb-1">
                            <i class="fa fa-building me-1"></i><?php echo CHtml::encode($propertyName); ?>
                        </p>
                        <?php if ($e->is_alliance_team): ?>
                            <p class="card-text small mb-1">
                                <span class="badge bg-warning text-dark"><i class="fa fa-users me-1"></i>Đội liên quân</span>
                            </p>
                        <?php endif; ?>
                        <?php if ($duration): ?>
                            <p class="card-text small mb-1">
                                <i class="fa fa-clock-o me-1"></i><?php echo $duration; ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($e->participant_count)): ?>
                            <p class="card-text small mb-1">
                                <i class="fa fa-user me-1"></i><?php echo $e->participant_count; ?> người
                            </p>
                        <?php endif; ?>
                        <p class="card-text small mb-1">
                            <?php if (!empty($e->video_path)): ?>
                                <span class="text-success"><i class="fa fa-video-camera me-1"></i>Đã upload video</span>
                            <?php else: ?>
                                <span class="text-warning"><i class="fa fa-exclamation-circle me-1"></i>Chưa upload video</span>
                            <?php endif; ?>
                        </p>
                        <?php if (!empty($e->description)): ?>
                            <p class="card-text small text-muted mt-2 entry-description"><?php echo CHtml::encode(mb_substr($e->description, 0, 100)); ?><?php echo mb_strlen($e->description) > 100 ? '...' : ''; ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-info flex-fill btn-view-detail" data-id="<?php echo $e->id; ?>">
                                <i class="fa fa-eye me-1"></i>Chi tiết
                            </button>
                            <?php if ($e->status != TalentEntries::STATUS_APPROVED): ?>
                                <button type="button" class="btn btn-sm btn-success btn-approve" data-id="<?php echo $e->id; ?>" title="Duyệt">
                                    <i class="fa fa-check"></i>
                                </button>
                            <?php endif; ?>
                            <?php if ($e->status != TalentEntries::STATUS_REJECTED): ?>
                                <button type="button" class="btn btn-sm btn-danger btn-reject" data-id="<?php echo $e->id; ?>" title="Từ chối">
                                    <i class="fa fa-times"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php $this->renderPartial('_modal_detail'); ?>

<script>
var approveTalentConfig = {
    getDetailUrl: '<?php echo $this->createUrl("getDetail"); ?>',
    approveUrl: '<?php echo $this->createUrl("approve"); ?>',
    rejectUrl: '<?php echo $this->createUrl("reject"); ?>'
};
</script>
