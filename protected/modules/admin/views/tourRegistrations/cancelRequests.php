<?php
$this->breadcrumbs = array('Đăng ký tham quan' => $this->createUrl('admin'), 'Yêu cầu hủy');
$this->menu = array(
    array(
        'label' => 'Danh sách đăng ký',
        'url' => $this->createUrl('admin', $eventId ? array('event_id' => $eventId) : array()),
        'color' => 'primary',
        'icon' => 'fa-list',
        'id' => 'btn_registrations',
    ),
);
$this->Tabletitle = 'Yêu cầu hủy đăng ký đi tham quan';
$canUpdate = PermissionHelper::can('tourregistrations', 'update');

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/tourRegistrations-cancelRequests.js',
    CClientScript::POS_END
);
?>
<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?php echo $this->createUrl('cancelRequests'); ?>" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Sự kiện</label>
                <?php echo CHtml::dropDownList('event_id', $eventId, $eventList, array(
                    'class' => 'form-select',
                    'empty' => '-- Chọn sự kiện --',
                    'onchange' => 'this.form.submit()',
                )); ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (!$eventId): ?>
            <div class="alert alert-info mb-0">Vui lòng chọn sự kiện để xem các yêu cầu hủy.</div>
        <?php elseif (empty($requests)): ?>
            <div class="alert alert-success mb-0">Không có yêu cầu hủy nào đang chờ duyệt.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                        <tr>
                            <th style="width:50px;">STT</th>
                            <th>Đợt</th>
                            <th>Họ và tên</th>
                            <th>Đơn vị</th>
                            <th>Lý do hủy</th>
                            <th style="width:200px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $i => $r): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo CHtml::encode(isset($r['tour_session_name']) ? $r['tour_session_name'] : ''); ?></td>
                                <td><?php echo CHtml::encode(isset($r['full_name']) ? $r['full_name'] : ''); ?></td>
                                <td><?php echo CHtml::encode(isset($r['unit_label']) ? $r['unit_label'] : ''); ?></td>
                                <td><?php echo CHtml::encode(isset($r['cancel_reason']) ? $r['cancel_reason'] : ''); ?></td>
                                <td>
                                    <?php if ($canUpdate): ?>
                                        <?php echo CHtml::form($this->createUrl('approveCancel', array('id' => $r['id'], 'event_id' => $eventId)), 'post', array('class' => 'd-inline', 'id' => 'form-approve-' . $r['id'])); ?>
                                            <button type="button" class="btn btn-sm btn-success btn-approve-cancel" data-form="form-approve-<?php echo (int) $r['id']; ?>">
                                                <i class="fa fa-check"></i> Duyệt hủy
                                            </button>
                                        </form>
                                        <?php echo CHtml::form($this->createUrl('rejectCancel', array('id' => $r['id'], 'event_id' => $eventId)), 'post', array('class' => 'd-inline', 'id' => 'form-reject-' . $r['id'])); ?>
                                            <button type="button" class="btn btn-sm btn-outline-secondary btn-reject-cancel" data-form="form-reject-<?php echo (int) $r['id']; ?>">
                                                <i class="fa fa-times"></i> Từ chối
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">Không có quyền</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p class="text-muted mb-0">Tổng: <strong><?php echo count($requests); ?></strong> yêu cầu chờ duyệt.</p>
        <?php endif; ?>
    </div>
</div>
