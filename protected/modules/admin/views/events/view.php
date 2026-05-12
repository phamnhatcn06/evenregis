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

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-list-alt me-2"></i>Nội dung sự kiện</h5>
    </div>
    <div class="card-body pb-2">
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
    <div class="card-body">
        <?php if (!empty($eventContents)): ?>
        <div class="row">
            <?php foreach ($eventContents as $ec): ?>
            <div class="col-md-4 mb-3">
                <div class="card h-100 border">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center bg-light">
                        <span class="fw-semibold"><?php echo CHtml::encode(isset($ec['content']['name']) ? $ec['content']['name'] : ''); ?></span>
                        <form method="post" action="<?php echo Yii::app()->createUrl('admin/events/removeContent', array('id' => $model->id, 'contentId' => $ec['id'])); ?>" style="display:inline;" id="form-remove-<?php echo $ec['id']; ?>">
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="confirmDelete('form-remove-<?php echo $ec['id']; ?>')">
                                <i class="fa fa-times"></i>
                            </button>
                        </form>
                    </div>
                    <div class="card-body p-2">
                        <small class="text-muted">Chưa có chi tiết</small>
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