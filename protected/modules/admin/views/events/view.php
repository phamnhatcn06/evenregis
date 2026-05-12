<?php
$this->menu = array(
    array(
        'label' => Yii::t('app', 'Manage') . ' ' . $model->label(2),
        'labelIcon' => Yii::t('app', 'Manage'),
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
    array(
        'label' => Yii::t('app', 'Create') . ' ' . $model->label(),
        'labelIcon' => Yii::t('app', 'Create'),
        'url' => $this->createUrl('create'),
        'color' => 'success',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => Yii::t('app', 'Update') . ' ' . $model->label(),
        'labelIcon' => Yii::t('app', 'Update'),
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'warning',
        'icon' => 'fa-pencil',
        'id' => 'btn_update',
    ),
    array(
        'label' => Yii::t('app', 'Delete') . ' ' . $model->label(),
        'labelIcon' => Yii::t('app', 'Delete'),
        'url' => $this->createUrl('delete', array('id' => $model->id)),
        'color' => 'danger',
        'icon' => 'fa-trash',
        'id' => 'btn_delete'
    ),
);


$this->breadcrumbs = array(
    Events::label(2),
    Yii::t('app', 'View') . ' ' . $model->label(),
);

$this->Tabletitle = Yii::t('app', 'View') . ' ' . $model->label() . ': ' . $model->name;
?>

<?php
$attributes = array(
    array('label' => $model->getAttributeLabel('code'), 'value' => $model->code),
    array('label' => $model->getAttributeLabel('name'), 'value' => $model->name),
    array('label' => $model->getAttributeLabel('from_date'), 'value' => MyHelper::formatDate($model->from_date)),
    array('label' => $model->getAttributeLabel('to_date'), 'value' => MyHelper::formatDate($model->to_date)),
    array('label' => $model->getAttributeLabel('description'), 'value' => $model->description),
    array(
        'label' => $model->getAttributeLabel('status'),
        'value' => $model->status == 1
            ? '<span class="badge bg-success">Hoạt động</span>'
            : '<span class="badge bg-secondary">Không hoạt động</span>',
        'raw' => true
    ),
);

$totalAttrs = count($attributes);
if ($totalAttrs <= 4) {
    $colClass = 'col-12';
    $columns = 1;
} elseif ($totalAttrs <= 8) {
    $colClass = 'col-md-6';
    $columns = 2;
} else {
    $colClass = 'col-md-4';
    $columns = 3;
}
$perColumn = ceil($totalAttrs / $columns);
?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin sự kiện</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php for ($col = 0; $col < $columns; $col++): ?>
                <div class="<?php echo $colClass; ?>">
                    <table class="table table-bordered table-striped mb-0">
                        <tbody>
                            <?php
                            $start = $col * $perColumn;
                            $end = min($start + $perColumn, $totalAttrs);
                            for ($i = $start; $i < $end; $i++):
                                $attr = $attributes[$i];
                            ?>
                                <tr>
                                    <th style="width:40%;background:#f8f9fa;"><?php echo CHtml::encode($attr['label']); ?></th>
                                    <td><?php echo isset($attr['raw']) && $attr['raw'] ? $attr['value'] : CHtml::encode($attr['value']); ?></td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>
<div class="row mt-3">
    <div class="col-md-6 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-building me-2"></i>Đơn vị tham gia</h5>
            </div>
            <div class="card-body">
                <?php
                $availableProperties = array();
                $selectedProperties = array();
                $eventUnitMap = array();

                foreach ($allProperties as $p) {
                    $pId = isset($p['id']) ? $p['id'] : (isset($p->id) ? $p->id : null);
                    $pName = isset($p['name']) ? $p['name'] : (isset($p->name) ? $p->name : '');
                    if ($pId) {
                        $availableProperties[$pId] = $pName;
                    }
                }

                if (!empty($eventUnits)) {
                    foreach ($eventUnits as $eu) {
                        $pId = $eu['property_id'];
                        $eventUnitMap[$pId] = $eu['id'];
                        if (isset($availableProperties[$pId])) {
                            $selectedProperties[$pId] = $availableProperties[$pId];
                            unset($availableProperties[$pId]);
                        }
                    }
                }
                ?>
                <form id="form-units" method="post" action="<?php echo Yii::app()->createUrl('admin/events/syncUnits', array('id' => $model->id)); ?>">
                    <div class="row">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Đơn vị chưa chọn</label>
                            <select id="available-units" class="form-select" multiple size="10">
                                <?php foreach ($availableProperties as $id => $name): ?>
                                    <option value="<?php echo $id; ?>"><?php echo CHtml::encode($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center gap-2">
                            <button type="button" class="btn btn-outline-primary" style="width:30px;height:30px;padding:0;" onclick="moveSelected('available-units', 'selected-units')" title="Thêm">
                                <i class="fa fa-angle-right"></i>
                            </button>
                            <button type="button" class="btn btn-outline-primary" style="width:30px;height:30px;padding:0;" onclick="moveAll('available-units', 'selected-units')" title="Thêm tất cả">
                                <i class="fa fa-angle-double-right"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" style="width:30px;height:30px;padding:0;" onclick="moveSelected('selected-units', 'available-units')" title="Xóa">
                                <i class="fa fa-angle-left"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" style="width:30px;height:30px;padding:0;" onclick="moveAll('selected-units', 'available-units')" title="Xóa tất cả">
                                <i class="fa fa-angle-double-left"></i>
                            </button>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Đơn vị đã chọn <span class="badge bg-primary" id="selected-count"><?php echo count($selectedProperties); ?></span></label>
                            <select id="selected-units" name="property_ids[]" class="form-select" multiple size="10">
                                <?php foreach ($selectedProperties as $id => $name): ?>
                                    <option value="<?php echo $id; ?>"><?php echo CHtml::encode($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="mt-3">
                        <button type="submit" class="btn btn-primary btn-sm" onclick="selectAllSelected()">
                            <i class="fa fa-save me-1"></i>Lưu thay đổi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6 d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-list-alt me-2"></i>Nội dung sự kiện</h5>
            </div>
            <div class="card-body">
                <?php
                $existingContentIds = array();
                if (!empty($eventContents)) {
                    foreach ($eventContents as $ec) {
                        $existingContentIds[] = $ec['content_id'];
                    }
                }
                $availableContents = array();
                foreach ($allContents as $c) {
                    $cId = isset($c['id']) ? $c['id'] : (isset($c->id) ? $c->id : null);
                    if ($cId && !in_array($cId, $existingContentIds)) {
                        $availableContents[$cId] = isset($c['name']) ? $c['name'] : (isset($c->name) ? $c->name : '');
                    }
                }
                ?>
                <?php if (!empty($availableContents)): ?>
                    <form method="post" action="<?php echo Yii::app()->createUrl('admin/events/addContent', array('id' => $model->id)); ?>" class="d-flex align-items-center gap-2 mb-3">
                        <?php echo CHtml::dropDownList('content_id', '', $availableContents, array('class' => 'form-select form-select-sm', 'style' => 'width:250px;', 'prompt' => '-- Chọn nội dung --')); ?>
                        <button type="submit" class="btn btn-sm btn-success"><i class="fa fa-plus me-1"></i>Thêm</button>
                    </form>
                <?php endif; ?>
                <?php
                $existingSportIds = array();
                if (!empty($eventSports)) {
                    foreach ($eventSports as $es) {
                        $existingSportIds[$es['sport_id']] = $es;
                    }
                }
                $availableSports = array();
                foreach ($allSports as $s) {
                    $sId = isset($s['id']) ? $s['id'] : (isset($s->id) ? $s->id : null);
                    if ($sId && !isset($existingSportIds[$sId])) {
                        $availableSports[$sId] = isset($s['name']) ? $s['name'] : (isset($s->name) ? $s->name : '');
                    }
                }
                ?>
                <?php if (!empty($eventContents)): ?>
                    <div class="row">
                        <?php foreach ($eventContents as $ec):
                            $contentCode = isset($ec['content_code']) ? $ec['content_code'] : '';
                        ?>
                            <div class="col-md-4 mb-3 event-content">
                                <div class="card h-100 border">
                                    <div class="card-header py-2 d-flex justify-content-between align-items-center bg-light">
                                        <span class="fw-semibold"><?php echo CHtml::encode(isset($ec['content_name']) ? $ec['content_name'] : ''); ?></span>
                                        <form method="post" action="<?php echo Yii::app()->createUrl('admin/events/removeContent', array('id' => $model->id, 'contentId' => $ec['id'])); ?>" style="display:inline;" id="form-remove-<?php echo $ec['id']; ?>">
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="confirmDelete('form-remove-<?php echo $ec['id']; ?>')">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <div class="card-body p-2 content-detail">
                                        <?php if ($contentCode === 'sports'): ?>
                                            <?php if (!empty($eventSports)): ?>
                                                <ul class="list-unstyled mb-2">
                                                    <?php foreach ($eventSports as $es): ?>
                                                        <li class="d-flex justify-content-between align-items-center mb-1">
                                                            <span><i class="fa fa-futbol-o me-1"></i><?php echo CHtml::encode($es['sport_name']); ?></span>
                                                            <form method="post" action="<?php echo Yii::app()->createUrl('admin/events/removeSport', array('id' => $model->id, 'sportId' => $es['id'])); ?>" style="display:inline;" id="form-remove-sport-<?php echo $es['id']; ?>">
                                                                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="confirmDelete('form-remove-sport-<?php echo $es['id']; ?>')">
                                                                    <i class="fa fa-times"></i>
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                            <?php if (!empty($availableSports)): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalAddSport">
                                                    <i class="fa fa-plus me-1"></i>Thêm môn
                                                </button>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <small class="text-muted">Chưa có chi tiết</small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Chưa có nội dung nào được thêm.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Modal Add Sport -->
<div class="modal fade" id="modalAddSport" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo Yii::app()->createUrl('admin/events/addSport', array('id' => $model->id)); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm môn thể thao</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <?php echo CHtml::dropDownList('sport_id', '', $availableSports, array(
                        'class' => 'form-select',
                        'prompt' => '-- Chọn môn thể thao --'
                    )); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/events-view.js',
    CClientScript::POS_END
);
?>