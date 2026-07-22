<?php
$this->breadcrumbs = array(
    'Cuộc thi văn nghệ' => array('admin'),
    $model->name,
);

$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
    array(
        'label' => 'DS Tiết mục',
        'url' => $this->createUrl('/admin/talentEntries/admin', array('TalentEntries[show_id]' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-music',
    ),
    array(
        'label' => 'Vòng thi',
        'url' => $this->createUrl('/admin/talentRounds/admin', array('TalentRounds[talent_show_id]' => $model->id)),
        'color' => 'success',
        'icon' => 'fa-layer-group',
    ),
    array(
        'label' => 'Cập nhật',
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'warning',
        'icon' => 'fa-edit',
        'id' => 'btn_update',
    ),
);

MyHelper::renderDeleteButton($this, 'talentShows', $model->id);

$this->Tabletitle = 'Chi tiết: ' . $model->name;
?>

<?php
$attributes = array(
    array('label' => $model->getAttributeLabel('id'), 'value' => $model->id),
    array('label' => $model->getAttributeLabel('event_id'), 'value' => isset($model->event_name) ? $model->event_name : $model->event_id),
    array('label' => $model->getAttributeLabel('name'), 'value' => $model->name),
    array('label' => $model->getAttributeLabel('description'), 'value' => $model->description ?: '-'),
    array('label' => $model->getAttributeLabel('registration_open_at'), 'value' => $model->registration_open_at ? date('d/m/Y', strtotime($model->registration_open_at)) : '-'),
    array('label' => $model->getAttributeLabel('registration_close_at'), 'value' => $model->registration_close_at ? date('d/m/Y', strtotime($model->registration_close_at)) : '-'),
    array('label' => $model->getAttributeLabel('show_date'), 'value' => $model->show_date ? date('d/m/Y', strtotime($model->show_date)) : '-'),
    array('label' => $model->getAttributeLabel('location'), 'value' => $model->location ?: '-'),
    array('label' => $model->getAttributeLabel('max_entries_per_org'), 'value' => $model->max_entries_per_org ?: '-'),
    array('label' => $model->getAttributeLabel('is_active'), 'value' => TalentShows::getActiveLabel($model->is_active), 'raw' => true),
    array('label' => $model->getAttributeLabel('created_at'), 'value' => $model->created_at ? date('d/m/Y H:i', strtotime($model->created_at)) : '-'),
    array('label' => $model->getAttributeLabel('updated_at'), 'value' => $model->updated_at ? date('d/m/Y H:i', strtotime($model->updated_at)) : '-'),
);

$totalAttrs = count($attributes);
$colClass = 'col-md-6';
$columns = 2;
$perColumn = ceil($totalAttrs / $columns);
?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-music me-2"></i>Thông tin cuộc thi văn nghệ</h5>
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

<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-music me-2"></i>Danh sách tiết mục (<?php echo $entries->getTotalItemCount(); ?>)</h5>
        <a href="<?php echo $this->createUrl('/admin/talentEntries/admin', array('TalentEntries[show_id]' => $model->id)); ?>" class="btn btn-sm btn-outline-primary">
            <i class="fa fa-list me-1"></i>Xem tất cả
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tên tiết mục</th>
                        <th>Thể loại</th>
                        <th>Đơn vị</th>
                        <th style="width:80px">Số người</th>
                        <th style="width:120px">Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $items = $entries->getData();
                    if (empty($items)):
                    ?>
                    <tr><td colspan="5" class="text-center text-muted py-4">Chưa có tiết mục đăng ký</td></tr>
                    <?php else: foreach ($items as $entry): ?>
                    <tr>
                        <td>
                            <a href="<?php echo $this->createUrl('/admin/talentEntries/view', array('id' => $entry->id)); ?>">
                                <?php echo CHtml::encode($entry->title); ?>
                            </a>
                        </td>
                        <td><?php echo CHtml::encode(isset($entry->category_name) ? $entry->category_name : ''); ?></td>
                        <td><?php echo CHtml::encode(isset($entry->property_name) ? $entry->property_name : ''); ?></td>
                        <td><?php echo CHtml::encode($entry->participant_count ?: '-'); ?></td>
                        <td><?php echo TalentEntries::getStatusLabel($entry->status); ?></td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
