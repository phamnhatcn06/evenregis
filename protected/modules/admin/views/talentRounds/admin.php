<?php
$this->breadcrumbs = array(
    'Cuộc thi văn nghệ' => array('/admin/talentShows/admin'),
    'Vòng thi',
);

$this->menu = array(
    array(
        'label' => 'Thêm mới',
        'url' => $this->createUrl('create'),
        'color' => 'primary',
        'icon' => 'fa-plus',
    ),
);
$this->Tabletitle = 'Quản lý vòng thi Văn nghệ';
?>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?php echo $this->createUrl('admin'); ?>" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Cuộc thi</label>
                <select name="TalentRounds[talent_show_id]" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <?php foreach ($talentShows as $id => $name): ?>
                    <option value="<?php echo $id; ?>" <?php echo ($model->talent_show_id == $id) ? 'selected' : ''; ?>>
                        <?php echo CHtml::encode($name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Loại vòng</label>
                <select name="TalentRounds[round_type]" class="form-select">
                    <option value="">-- Tất cả --</option>
                    <?php foreach (TalentRounds::getRoundTypeOptions() as $type => $label): ?>
                    <option value="<?php echo $type; ?>" <?php echo ($model->round_type == $type) ? 'selected' : ''; ?>>
                        <?php echo CHtml::encode($label); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tên vòng</label>
                <input type="text" name="TalentRounds[name]" class="form-control" value="<?php echo CHtml::encode($model->name); ?>" placeholder="Tìm theo tên...">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fa fa-search me-1"></i>Tìm</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px">ID</th>
                        <th>Tên vòng</th>
                        <th>Cuộc thi</th>
                        <th style="width:120px">Loại vòng</th>
                        <th style="width:80px">Thứ tự</th>
                        <th style="width:100px">Điểm tối đa</th>
                        <th style="width:80px">Trọng số</th>
                        <th style="width:140px">Thời gian</th>
                        <th style="width:100px">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $items = $dataProvider->getData();
                    if (empty($items)):
                    ?>
                    <tr><td colspan="9" class="text-center text-muted py-4">Không có dữ liệu</td></tr>
                    <?php else: foreach ($items as $data): ?>
                    <tr>
                        <td><?php echo CHtml::encode($data->id); ?></td>
                        <td>
                            <a href="<?php echo $this->createUrl('view', array('id' => $data->id)); ?>">
                                <?php echo CHtml::encode($data->name); ?>
                            </a>
                        </td>
                        <td><?php echo CHtml::encode(isset($data->talent_show_name) ? $data->talent_show_name : ''); ?></td>
                        <td><span class="badge bg-info"><?php echo TalentRounds::getRoundTypeLabel($data->round_type); ?></span></td>
                        <td class="text-center"><?php echo CHtml::encode($data->round_order); ?></td>
                        <td class="text-end"><?php echo number_format($data->max_score, 2); ?></td>
                        <td class="text-end"><?php echo number_format($data->weight, 2); ?></td>
                        <td>
                            <?php if ($data->start_time): ?>
                            <small><?php echo date('d/m H:i', strtotime($data->start_time)); ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo IconHelper::actionButtons($data, array('view', 'update', 'delete'), '/admin/talentRounds'); ?>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php if ($dataProvider->getTotalItemCount() > $dataProvider->getPagination()->getPageSize()): ?>
    <div class="card-footer">
        <?php $this->widget('CLinkPager', array('pages' => $dataProvider->getPagination())); ?>
    </div>
    <?php endif; ?>
</div>
