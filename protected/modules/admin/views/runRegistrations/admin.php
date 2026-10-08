<?php
$this->breadcrumbs = array('Đăng ký chạy');
$this->menu = array(
    array(
        'label' => 'Nội dung chạy',
        'url' => $this->createUrl('/admin/runEvents/admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_events',
    ),
    array(
        'label' => 'Yêu cầu hủy',
        'url' => $this->createUrl('cancelRequests', $eventId ? array('event_id' => $eventId) : array()),
        'color' => 'warning',
        'icon' => 'fa-times-circle',
        'id' => 'btn_cancel_requests',
    ),
    array(
        'label' => 'Dashboard',
        'url' => $this->createUrl('/admin/activityDashboard/index', $eventId ? array('event_id' => $eventId) : array()),
        'color' => 'secondary',
        'icon' => 'fa-dashboard',
        'id' => 'btn_dashboard',
    ),
);
$this->Tabletitle = 'Danh sách đăng ký bộ môn chạy';
$canUpdate = PermissionHelper::can('runregistrations', 'update');
?>
<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?php echo $this->createUrl('admin'); ?>" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Sự kiện</label>
                <?php echo CHtml::dropDownList('event_id', $eventId, $eventList, array(
                    'class' => 'form-select',
                    'empty' => '-- Chọn sự kiện --',
                    'onchange' => 'this.form.submit()',
                )); ?>
            </div>
            <?php if ($eventId): ?>
            <div class="col-md-3">
                <label class="form-label">Tìm kiếm</label>
                <?php echo CHtml::textField('q', $filters['q'], array(
                    'class' => 'form-control',
                    'placeholder' => 'Tên / BIB / SĐT / Mã VĐV',
                )); ?>
            </div>
            <div class="col-md-2">
                <label class="form-label">Cự ly</label>
                <?php echo CHtml::dropDownList('cu_ly', $filters['cu_ly'], $cuLyOptions, array(
                    'class' => 'form-select',
                    'empty' => '-- Tất cả --',
                )); ?>
            </div>
            <div class="col-md-2">
                <label class="form-label">Giới tính</label>
                <?php echo CHtml::dropDownList('gender', $filters['gender'], array('1' => 'Nam', '0' => 'Nữ'), array(
                    'class' => 'form-select',
                    'empty' => '-- Tất cả --',
                )); ?>
            </div>
            <div class="col-md-2">
                <label class="form-label">Đơn vị</label>
                <?php echo CHtml::dropDownList('unit', $filters['unit'], $unitOptions, array(
                    'class' => 'form-select',
                    'empty' => '-- Tất cả --',
                )); ?>
            </div>
            <div class="col-12 d-flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary"><i class="fa fa-filter me-1"></i>Lọc</button>
                <a href="<?php echo $this->createUrl('admin', array('event_id' => $eventId)); ?>" class="btn btn-outline-secondary">
                    <i class="fa fa-times me-1"></i>Xóa lọc
                </a>
                <a href="<?php echo $this->createUrl('export', array('event_id' => $eventId)); ?>" class="btn btn-success ms-auto">
                    <i class="fa fa-file-excel-o me-1"></i>Xuất Excel
                </a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if ($eventId && $cancelRequestCount > 0): ?>
    <div class="alert alert-warning d-flex justify-content-between align-items-center" role="alert">
        <span>
            <i class="fa fa-exclamation-triangle me-1"></i>
            Có <strong><?php echo $cancelRequestCount; ?></strong> yêu cầu hủy cần xử lý.
        </span>
        <a href="<?php echo $this->createUrl('cancelRequests', array('event_id' => $eventId)); ?>" class="btn btn-sm btn-warning">
            <i class="fa fa-times-circle me-1"></i>Xử lý ngay
        </a>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (!$eventId): ?>
            <div class="alert alert-info mb-0">Vui lòng chọn sự kiện để xem danh sách đăng ký.</div>
        <?php elseif (empty($registrations)): ?>
            <div class="alert alert-warning mb-0">Chưa có ai đăng ký cho sự kiện này.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width:60px;">STT</th>
                            <th>Số BIB</th>
                            <th>Họ và tên</th>
                            <th>Đơn vị</th>
                            <th>Số điện thoại</th>
                            <th>Cự ly</th>
                            <th>Nội dung</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $i => $r): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><span class="badge bg-primary"><?php echo CHtml::encode(isset($r['bib_number']) ? $r['bib_number'] : ''); ?></span></td>
                                <td><?php echo CHtml::encode(isset($r['full_name']) ? $r['full_name'] : ''); ?></td>
                                <td><?php echo CHtml::encode(isset($r['unit_label']) ? $r['unit_label'] : ''); ?></td>
                                <td><?php echo CHtml::encode(isset($r['phone_number']) ? $r['phone_number'] : ''); ?></td>
                                <td><?php echo CHtml::encode(isset($r['run_event_name']) ? $r['run_event_name'] : ''); ?></td>
                                <td><?php echo CHtml::encode(!empty($r['age_group_label']) ? $r['age_group_label'] : '—'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted mb-0">Tổng: <strong><?php echo count($registrations); ?></strong> người đã đăng ký.</p>
        <?php endif; ?>
    </div>
</div>
