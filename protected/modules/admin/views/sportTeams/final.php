<?php
/* @var $this SportTeamsController */
/* @var $events array id=>name */
/* @var $sportsItems array danh sách môn thi đã sắp theo cây cha-con */
/* @var $levelMap array id môn => cấp độ trong cây */
/* @var $hasChildren array id môn cha => true */

$this->breadcrumbs = array(
    'Thể thao' => array('admin'),
    'Chọn vào chung kết',
);
$this->Tabletitle = 'Chọn đội vào vòng chung kết';

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/finals-manager.js',
    CClientScript::POS_END
);
?>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Sự kiện <span class="text-danger">*</span></label>
                <select id="finals-event" class="form-select">
                    <option value="">-- Chọn sự kiện --</option>
                    <?php foreach ($events as $id => $name): ?>
                        <option value="<?php echo $id; ?>"><?php echo CHtml::encode($name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Môn thi <span class="text-danger">*</span></label>
                <select id="finals-sport" class="form-select">
                    <option value="">-- Chọn môn thi --</option>
                    <?php foreach ($sportsItems as $s):
                        $sId = is_object($s) ? $s->id : (isset($s['id']) ? $s['id'] : null);
                        $sName = is_object($s) ? $s->name : (isset($s['name']) ? $s['name'] : '');
                        $sActive = is_object($s) ? $s->is_active : (isset($s['is_active']) ? $s['is_active'] : 1);
                        if (!$sId || !$sActive) continue;
                        $level = isset($levelMap[$sId]) ? $levelMap[$sId] : 0;
                        $prefix = $level > 0 ? str_repeat('—', $level) . ' ' : '';
                        $isGroup = isset($hasChildren[$sId]);
                    ?>
                        <?php if ($isGroup): ?>
                            <option value="" disabled style="font-weight:bold;background:#f0f0f0;"><?php echo CHtml::encode($prefix . $sName); ?></option>
                        <?php else: ?>
                            <option value="<?php echo $sId; ?>"><?php echo CHtml::encode($prefix . $sName); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div id="finals-app"
     data-mode="sport"
     data-candidates-url="<?php echo $this->createUrl('/admin/sportTeams/finalCandidates'); ?>"
     data-list-url="<?php echo $this->createUrl('/admin/sportTeams/finalList'); ?>"
     data-add-url="<?php echo $this->createUrl('/admin/sportTeams/finalAdd'); ?>"
     data-remove-url="<?php echo $this->createUrl('/admin/sportTeams/finalRemove'); ?>">
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Đội đủ điều kiện</h5>
                    <button type="button" id="finals-add-btn" class="btn btn-primary btn-sm" disabled>
                        <i class="fa fa-arrow-right me-1"></i>Thêm vào chung kết
                    </button>
                </div>
                <div class="card-body">
                    <input type="text" id="finals-search" class="form-control mb-3" placeholder="Tìm kiếm...">
                    <div id="finals-candidates" style="max-height: 60vh; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Đã vào chung kết <span class="badge bg-success" id="finals-count">0</span></h5>
                </div>
                <div class="card-body">
                    <div id="finals-finalists" style="max-height: 66vh; overflow-y: auto;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
