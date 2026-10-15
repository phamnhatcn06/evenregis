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
                <?php if ($canUpdate && !empty($runEventList)): ?>
                <button type="button" class="btn btn-primary ms-auto" id="btn_open_manual_assign">
                    <i class="fa fa-user-plus me-1"></i>Gán thủ công
                </button>
                <a href="<?php echo $this->createUrl('export', array('event_id' => $eventId)); ?>" class="btn btn-success">
                    <i class="fa fa-file-excel-o me-1"></i>Xuất Excel
                </a>
                <?php else: ?>
                <a href="<?php echo $this->createUrl('export', array('event_id' => $eventId)); ?>" class="btn btn-success ms-auto">
                    <i class="fa fa-file-excel-o me-1"></i>Xuất Excel
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<?php if ($eventId && $canUpdate && !empty($runEventList)): ?>
    <div id="run-manual-assign-config"
         data-search-url="<?php echo $this->createUrl('searchPeople', array('event_id' => $eventId)); ?>"
         data-check-url="<?php echo $this->createUrl('checkRegistration'); ?>"
         data-assign-url="<?php echo $this->createUrl('manualAssign'); ?>"
         data-change-url="<?php echo $this->createUrl('changeContent'); ?>"></div>
    <?php $this->renderPartial('_modal_manual_assign', array('runEventList' => $runEventList)); ?>
    <?php
    $manualAssignJs = '/assets/js/pages/runregistrations-manual-assign.js';
    $manualAssignVer = @filemtime(Yii::app()->theme->basePath . $manualAssignJs);
    Yii::app()->clientScript->registerScriptFile(
        Yii::app()->theme->baseUrl . $manualAssignJs . ($manualAssignVer ? '?v=' . $manualAssignVer : ''),
        CClientScript::POS_END
    );
    ?>
<?php endif; ?>

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

<?php if ($eventId && !empty($countByContent)): ?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0"><i class="fa fa-bar-chart me-1"></i>Số người đăng ký theo nội dung</h6>
                <a href="<?php echo $this->createUrl('exportBib', array('event_id' => $eventId)); ?>" class="btn btn-sm btn-primary">
                    <i class="fa fa-id-card-o me-1"></i>Xuất tất cả thẻ BIB
                </a>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($countByContent as $contentName => $contentCount): ?>
                    <span class="badge bg-light text-dark border fs-6 p-2 d-inline-flex align-items-center gap-2">
                        <span><?php echo CHtml::encode($contentName); ?>:
                            <strong class="text-primary"><?php echo (int) $contentCount; ?></strong></span>
                        <a href="<?php echo $this->createUrl('exportBib', array('event_id' => $eventId, 'cu_ly' => $contentName)); ?>"
                           class="text-decoration-none" title="Xuất thẻ BIB nội dung này">
                            <i class="fa fa-id-card-o"></i>
                        </a>
                    </span>
                <?php endforeach; ?>
                <span class="badge bg-primary fs-6 p-2">
                    Tổng: <strong><?php echo (int) $totalCount; ?></strong>
                </span>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (!$eventId): ?>
            <div class="alert alert-info mb-0">Vui lòng chọn sự kiện để xem danh sách đăng ký.</div>
        <?php elseif (empty($registrations)): ?>
            <?php if ($totalCount > 0): ?>
                <div class="alert alert-warning mb-0">Không có kết quả phù hợp với bộ lọc.</div>
            <?php else: ?>
                <div class="alert alert-warning mb-0">Chưa có ai đăng ký cho sự kiện này.</div>
            <?php endif; ?>
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
                            <th>Năm sinh</th>
                            <th>Giới tính</th>
                            <th>Cự ly</th>
                            <th>Nội dung</th>
                            <?php if ($canUpdate && !empty($runEventList)): ?>
                            <th style="width:130px;">Thao tác</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $i => $r): ?>
                            <?php
                            $genderLabel = '—';
                            if (isset($r['gender']) && $r['gender'] !== null && $r['gender'] !== '') {
                                $genderLabel = ((int) $r['gender'] === 1) ? 'Nam' : 'Nữ';
                            }
                            ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td>
                                    <span class="badge bg-primary"><?php echo CHtml::encode(isset($r['bib_number']) ? $r['bib_number'] : ''); ?></span>
                                    <?php if (!empty($r['bib_number'])): ?>
                                    <a href="<?php echo $this->createUrl('exportBibOne', array('event_id' => $eventId, 'id' => (int) $r['id'])); ?>"
                                       class="btn btn-sm btn-outline-success ms-1 py-0 px-1" title="Xuất thẻ BIB người này">
                                        <i class="fa fa-download"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo CHtml::encode(isset($r['full_name']) ? $r['full_name'] : ''); ?></td>
                                <td>
                                    <?php if (!empty($r['is_guest'])): ?>
                                        <span class="badge bg-warning text-dark"><i class="fa fa-user-o me-1"></i>Khách ngoài</span>
                                        <?php if (!empty($r['company'])): ?>
                                            <div class="small text-muted mt-1"><?php echo CHtml::encode($r['company']); ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <?php echo CHtml::encode(!empty($r['unit_label']) ? $r['unit_label'] : '—'); ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo CHtml::encode(isset($r['phone_number']) ? $r['phone_number'] : ''); ?></td>
                                <td><?php echo CHtml::encode(!empty($r['birth_year']) ? $r['birth_year'] : '—'); ?></td>
                                <td><?php echo CHtml::encode($genderLabel); ?></td>
                                <td><?php echo CHtml::encode(isset($r['run_event_name']) ? $r['run_event_name'] : ''); ?></td>
                                <td><?php echo CHtml::encode(!empty($r['age_group_label']) ? $r['age_group_label'] : '—'); ?></td>
                                <?php if ($canUpdate && !empty($runEventList)): ?>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary js-change-content"
                                            data-registration-id="<?php echo (int) $r['id']; ?>"
                                            data-run-event-id="<?php echo isset($r['run_event_id']) ? (int) $r['run_event_id'] : ''; ?>"
                                            data-full-name="<?php echo CHtml::encode(isset($r['full_name']) ? $r['full_name'] : ''); ?>"
                                            data-unit="<?php echo CHtml::encode(!empty($r['unit_label']) ? $r['unit_label'] : ''); ?>"
                                            data-bib="<?php echo CHtml::encode(isset($r['bib_number']) ? $r['bib_number'] : ''); ?>"
                                            data-birth-year="<?php echo !empty($r['birth_year']) ? (int) $r['birth_year'] : ''; ?>"
                                            data-gender="<?php echo (isset($r['gender']) && $r['gender'] !== null && $r['gender'] !== '') ? (int) $r['gender'] : ''; ?>">
                                        <i class="fa fa-exchange me-1"></i>Đổi nội dung
                                    </button>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted mb-0">Hiển thị: <strong><?php echo count($registrations); ?></strong> / Tổng: <strong><?php echo $totalCount; ?></strong> người đã đăng ký.</p>
        <?php endif; ?>
    </div>
</div>
