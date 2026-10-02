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
);
$this->Tabletitle = 'Danh sách đăng ký bộ môn chạy';
$canUpdate = PermissionHelper::can('runregistrations', 'update');
?>
<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?php echo $this->createUrl('admin'); ?>" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Sự kiện</label>
                <?php echo CHtml::dropDownList('event_id', $eventId, $eventList, array(
                    'class' => 'form-select',
                    'empty' => '-- Chọn sự kiện --',
                    'onchange' => 'this.form.submit()',
                )); ?>
            </div>
            <div class="col-md-7 text-end">
                <?php if ($eventId): ?>
                    <a href="<?php echo $this->createUrl('export', array('event_id' => $eventId)); ?>" class="btn btn-success">
                        <i class="fa fa-file-excel-o me-1"></i>Xuất Excel
                    </a>
                    <?php if ($canUpdate): ?>
                        <form method="post" action="<?php echo $this->createUrl('genLucky'); ?>" style="display:inline-block;">
                            <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                            <button type="submit" class="btn btn-warning">
                                <i class="fa fa-random me-1"></i>Cấp số lucky
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

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
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted mb-0">Tổng: <strong><?php echo count($registrations); ?></strong> người đã đăng ký.</p>
        <?php endif; ?>
    </div>
</div>
