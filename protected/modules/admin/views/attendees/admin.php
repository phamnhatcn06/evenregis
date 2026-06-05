<?php
/**
 * Admin Attendees List
 * @var CActiveDataProvider $dataProvider
 */

$this->pageTitle = 'Danh sách người tham dự';
$this->breadcrumbs = array(
    'Quản lý' => array('/admin/default/index'),
    'Người tham dự',
);

$eventList = array();
foreach ($events as $e) {
    $id = isset($e->id) ? $e->id : (isset($e['id']) ? $e['id'] : null);
    $name = isset($e->name) ? $e->name : (isset($e['name']) ? $e['name'] : '');
    if ($id) $eventList[$id] = $name;
}

$propertyList = array();
foreach ($properties as $p) {
    $id = isset($p->id) ? $p->id : (isset($p['id']) ? $p['id'] : null);
    $code = isset($p->code) ? $p->code : (isset($p['code']) ? $p['code'] : '');
    $name = isset($p->name) ? $p->name : (isset($p['name']) ? $p['name'] : '');
    if ($id) $propertyList[$id] = "{$code} - {$name}";
}
asort($propertyList);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Filter -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="get" class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Sự kiện</label>
                            <select name="event_id" class="form-select">
                                <option value="">-- Tất cả --</option>
                                <?php foreach ($eventList as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo (isset($_GET['event_id']) && $_GET['event_id'] == $id) ? 'selected' : ''; ?>>
                                        <?php echo CHtml::encode($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Đơn vị</label>
                            <select name="property_id" class="form-select">
                                <option value="">-- Tất cả --</option>
                                <?php foreach ($propertyList as $id => $name): ?>
                                    <option value="<?php echo $id; ?>" <?php echo (isset($_GET['property_id']) && $_GET['property_id'] == $id) ? 'selected' : ''; ?>>
                                        <?php echo CHtml::encode($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Họ tên</label>
                            <input type="text" name="full_name" class="form-control" placeholder="Tìm theo tên..."
                                   value="<?php echo isset($_GET['full_name']) ? CHtml::encode($_GET['full_name']) : ''; ?>">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search"></i> Lọc
                            </button>
                            <a href="<?php echo $this->createUrl('admin'); ?>" class="btn btn-secondary">
                                <i class="fa fa-refresh"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Data Table -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fa fa-users"></i> Danh sách người tham dự
                    </h4>
                    <?php if (PermissionHelper::can('attendee', 'create')): ?>
                        <a href="<?php echo $this->createUrl('create'); ?>" class="btn btn-primary btn-sm">
                            <i class="fa fa-plus"></i> Thêm mới
                        </a>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Ảnh</th>
                                    <th>Họ tên</th>
                                    <th>Chức vụ</th>
                                    <th>Đơn vị</th>
                                    <th>Giấy tờ</th>
                                    <th>Trạng thái</th>
                                    <th style="width:120px;">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $data = $dataProvider->getData();
                                $index = $dataProvider->pagination ? $dataProvider->pagination->offset : 0;
                                foreach ($data as $item):
                                    $index++;
                                    $id = isset($item->id) ? $item->id : (isset($item['id']) ? $item['id'] : '');
                                    $fullName = isset($item->full_name) ? $item->full_name : (isset($item['full_name']) ? $item['full_name'] : '');
                                    $position = isset($item->position) ? $item->position : (isset($item['position']) ? $item['position'] : '');
                                    $unitLabel = isset($item->unit_label) && trim($item->unit_label) !== '' ? $item->unit_label : (isset($item['unit_label']) && trim($item['unit_label']) !== '' ? $item['unit_label'] : '');
                                    if ($unitLabel === '') {
                                        $unitLabel = isset($item->property_name) ? $item->property_name : (isset($item['property_name']) ? $item['property_name'] : '');
                                    }
                                    $photoPath = isset($item->photo_path) ? $item->photo_path : (isset($item['photo_path']) ? $item['photo_path'] : '');
                                    $portraitPath = isset($item->portrait_path) ? $item->portrait_path : (isset($item['portrait_path']) ? $item['portrait_path'] : '');

                                    $hasAllDocs = !empty($item->cccd_front_path) && !empty($item->cccd_back_path)
                                        && !empty($item->portrait_path) && !empty($item->contract_path);
                                ?>
                                <tr>
                                    <td><?php echo $index; ?></td>
                                    <td>
                                        <?php if ($portraitPath): ?>
                                            <img src="<?php echo $portraitPath; ?>" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                        <?php elseif ($photoPath): ?>
                                            <img src="<?php echo $photoPath; ?>" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                        <?php else: ?>
                                            <span class="bg-secondary rounded d-inline-block text-center text-white" style="width:40px;height:40px;line-height:40px;">
                                                <i class="fa fa-user"></i>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo $this->createUrl('view', array('id' => $id)); ?>">
                                            <?php echo CHtml::encode($fullName); ?>
                                        </a>
                                    </td>
                                    <td><?php echo CHtml::encode($position); ?></td>
                                    <td><?php echo CHtml::encode($unitLabel); ?></td>
                                    <td>
                                        <?php if ($hasAllDocs): ?>
                                            <span class="badge bg-success"><i class="fa fa-check"></i> Đầy đủ</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><i class="fa fa-exclamation-triangle"></i> Thiếu</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $isActive = isset($item->is_active) ? $item->is_active : (isset($item['is_active']) ? $item['is_active'] : 1);
                                        echo $isActive
                                            ? '<span class="badge bg-success">Hoạt động</span>'
                                            : '<span class="badge bg-secondary">Không hoạt động</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo IconHelper::actionButtons($item, array('view', 'update', 'delete'), '/admin/attendees'); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>

                                <?php if (empty($data)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                        Không có dữ liệu
                                    </td>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($dataProvider->pagination): ?>
                        <div class="d-flex justify-content-center mt-3">
                            <?php $this->widget('CLinkPager', array(
                                'pages' => $dataProvider->pagination,
                                'htmlOptions' => array('class' => 'pagination'),
                                'header' => '',
                                'selectedPageCssClass' => 'active',
                            )); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
