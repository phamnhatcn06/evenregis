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
                <label class="form-label">Đơn vị</label>
                <select name="property_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <?php foreach ($properties as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($_GET['property_id']) && $_GET['property_id'] == $id) ? 'selected' : ''; ?>>
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

<?php if (empty($entries)): ?>
    <div class="alert alert-info">Không có tiết mục nào.</div>
<?php else: ?>
    <ul class="nav nav-tabs mb-3" id="round-tabs" role="tablist">
        <?php foreach ($rounds as $i => $round): ?>
            <?php $tabId = 'round-' . $round['id']; ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $i === 0 ? 'active' : ''; ?>" id="<?php echo $tabId; ?>-tab"
                    data-bs-toggle="tab" data-bs-target="#<?php echo $tabId; ?>" type="button" role="tab">
                    <?php echo CHtml::encode($round['name']); ?>
                    <span class="badge bg-secondary ms-1"><?php echo $round['count']; ?></span>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content" id="round-tabs-content">
        <?php foreach ($rounds as $i => $round): ?>
            <?php $tabId = 'round-' . $round['id']; ?>
            <div class="tab-pane fade <?php echo $i === 0 ? 'show active' : ''; ?>" id="<?php echo $tabId; ?>" role="tabpanel">
                <div class="row">
                    <?php foreach ($grouped[$round['id']] as $e): ?>
                        <?php $this->renderPartial('_card', array('e' => $e)); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php $this->renderPartial('_modal_detail'); ?>
<?php $this->renderPartial('_modal_approve'); ?>

<script>
var approveTalentConfig = {
    getDetailUrl: '<?php echo $this->createUrl("getDetail"); ?>',
    getRoundsUrl: '<?php echo $this->createUrl("getRounds"); ?>',
    approveUrl: '<?php echo $this->createUrl("approve"); ?>',
    rejectUrl: '<?php echo $this->createUrl("reject"); ?>'
};
</script>
