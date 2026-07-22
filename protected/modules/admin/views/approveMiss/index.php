<?php
$this->breadcrumbs = array(
    'Xét duyệt Miss',
);

$this->menu = array();
$this->Tabletitle = 'Xét duyệt thí sinh Miss';

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl . '/assets/css/pages/approve-miss.css');
Yii::app()->clientScript->registerScriptFile(Yii::app()->theme->baseUrl . '/assets/js/pages/approve-miss.js', CClientScript::POS_END);
?>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Cuộc thi</label>
                <select name="contest_id" class="form-select form-select-sm">
                    <option value="">-- Tất cả --</option>
                    <?php foreach ($contests as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($_GET['contest_id']) && $_GET['contest_id'] == $id) ? 'selected' : ''; ?>>
                            <?php echo CHtml::encode($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Đơn vị</label>
                <select name="property_id" class="form-select form-select-sm">
                    <option value="">-- Tất cả --</option>
                    <?php foreach ($properties as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo (isset($_GET['property_id']) && $_GET['property_id'] == $id) ? 'selected' : ''; ?>>
                            <?php echo CHtml::encode($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Tất cả --</option>
                    <?php foreach (BeautyContestants::getStatusOptions() as $val => $label): ?>
                        <option value="<?php echo $val; ?>" <?php echo (isset($_GET['status']) && $_GET['status'] == $val) ? 'selected' : ''; ?>>
                            <?php echo CHtml::encode($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-lg-2 col-md-4">
                <label class="form-label">Tên thí sinh</label>
                <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Nhập tên..." value="<?php echo isset($_GET['keyword']) ? CHtml::encode($_GET['keyword']) : ''; ?>">
            </div>
            <div class="col-lg-2 col-md-4">
                <button type="submit" class="btn btn-primary btn-sm me-1"><i class="fa fa-search"></i> Lọc</button>
                <a href="<?php echo $this->createUrl('admin'); ?>" class="btn btn-outline-secondary btn-sm"><i class="fa fa-refresh"></i></a>
            </div>
        </form>
    </div>
</div>
<div class="mb-3 text-end">
    <button type="button" id="btn_compare" class="btn btn-info btn-sm" disabled>
        <i class="fa fa-columns me-1"></i>So sánh (<span id="compare_count">0</span>)
    </button>
    <button type="button" id="btn_clear_compare" class="btn btn-outline-secondary btn-sm">
        <i class="fa fa-times me-1"></i>Xóa chọn
    </button>
</div>

<?php
$hasUnassigned = !empty($unassigned);
$firstActive = 0; // index tab active mặc định
?>
<ul class="nav nav-tabs mb-3" id="roundTabs" role="tablist">
    <?php foreach ($roundTabs as $i => $tab): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo $i === $firstActive ? 'active' : ''; ?>" id="tab-round-<?php echo $tab['id']; ?>" data-bs-toggle="tab" data-bs-target="#round-<?php echo $tab['id']; ?>" type="button" role="tab">
                <?php echo CHtml::encode($tab['name']); ?>
                <span class="badge bg-secondary ms-1"><?php echo count($tab['contestants']); ?></span>
            </button>
        </li>
    <?php endforeach; ?>
    <?php if ($hasUnassigned): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo empty($roundTabs) ? 'active' : ''; ?>" id="tab-round-unassigned" data-bs-toggle="tab" data-bs-target="#round-unassigned" type="button" role="tab">
                <i class="fa fa-inbox me-1"></i>Chưa phân vòng
                <span class="badge bg-warning text-dark ms-1"><?php echo count($unassigned); ?></span>
            </button>
        </li>
    <?php endif; ?>
</ul>

<?php
// Giữ nguyên bộ lọc hiện tại khi xuất PDF để trùng khớp danh sách đang xem
$exportFilters = array();
foreach (array('contest_id', 'property_id', 'status', 'keyword') as $f) {
    if (isset($_GET[$f]) && $_GET[$f] !== '') {
        $exportFilters[$f] = $_GET[$f];
    }
}
?>
<div class="tab-content" id="roundTabsContent">
    <?php if (empty($roundTabs) && !$hasUnassigned): ?>
        <div class="alert alert-info">Chưa có vòng thi nào. Vui lòng tạo vòng thi trước.</div>
    <?php endif; ?>
    <?php foreach ($roundTabs as $i => $tab): ?>
        <div class="tab-pane fade <?php echo $i === $firstActive ? 'show active' : ''; ?>" id="round-<?php echo $tab['id']; ?>" role="tabpanel">
            <div class="mb-3 text-end">
                <a href="<?php echo $this->createUrl('exportPdf', array_merge(array('round_id' => $tab['id']), $exportFilters)); ?>" target="_blank" class="btn btn-danger btn-sm">
                    <i class="fa fa-file-pdf-o me-1"></i>Xuất PDF
                </a>
            </div>
            <?php $this->renderPartial('_grid', array(
                'contestants' => $tab['contestants'],
                'isFinalRound' => (isset($tab['round_type']) && $tab['round_type'] === 'final'),
            )); ?>
        </div>
    <?php endforeach; ?>
    <?php if ($hasUnassigned): ?>
        <div class="tab-pane fade <?php echo empty($roundTabs) ? 'show active' : ''; ?>" id="round-unassigned" role="tabpanel">
            <div class="mb-3 text-end">
                <a href="<?php echo $this->createUrl('exportPdf', array_merge(array('round_id' => 'unassigned'), $exportFilters)); ?>" target="_blank" class="btn btn-danger btn-sm">
                    <i class="fa fa-file-pdf-o me-1"></i>Xuất PDF
                </a>
            </div>
            <?php $this->renderPartial('_grid', array('contestants' => $unassigned, 'isFinalRound' => false)); ?>
        </div>
    <?php endif; ?>
</div>

<?php $this->renderPartial('_modal_detail'); ?>
<?php $this->renderPartial('_modal_compare'); ?>

<script>
    var approveMissConfig = {
        getDetailUrl: '<?php echo $this->createUrl("getDetail"); ?>',
        getRoundsUrl: '<?php echo $this->createUrl("getRounds"); ?>',
        approveUrl: '<?php echo $this->createUrl("approve"); ?>',
        rejectUrl: '<?php echo $this->createUrl("reject"); ?>'
    };
</script>