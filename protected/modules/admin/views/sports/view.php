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
    Sports::label(2),
    Yii::t('app', 'View') . ' ' . $model->label(),
);

$this->Tabletitle = Yii::t('app', 'View') . ' ' . $model->label() . ': ' . $model->name;
?>

<?php
$attributes = array(
    array('label' => $model->getAttributeLabel('code'), 'value' => $model->code),
    array('label' => $model->getAttributeLabel('name'), 'value' => $model->name),
    array('label' => $model->getAttributeLabel('type'), 'value' => $model->type),
    array('label' => $model->getAttributeLabel('description'), 'value' => $model->description),
    array('label' => $model->getAttributeLabel('document'), 'value' => $model->document),
    array(
        'label' => $model->getAttributeLabel('is_active'),
        'value' => $model->is_active
            ? '<span class="badge bg-success">Hoạt động</span>'
            : '<span class="badge bg-secondary">Không hoạt động</span>',
        'raw' => true
    ),
    array('label' => $model->getAttributeLabel('sort_order'), 'value' => $model->sort_order),
    array('label' => $model->getAttributeLabel('created_at'), 'value' => MyHelper::formatDateTime($model->created_at)),
    array('label' => $model->getAttributeLabel('updated_at'), 'value' => MyHelper::formatDateTime($model->updated_at)),
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
        <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin môn thể thao</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <?php for ($col = 0; $col < $columns; $col++): ?>
            <div class="<?php echo $colClass; ?>">
                <table class="table table-bordered table-striped">
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
