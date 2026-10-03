<?php
/**
 * Màn Tổng hợp danh sách Vòng Chung Kết (VCK) + mã lucky — danh sách chỉ đọc.
 *
 * @var FinalAttendeeRostersController $this
 * @var int $eventId
 * @var int $periodId
 * @var array $eventList
 * @var array $periodList
 * @var ApiDataProvider|null $dataProvider
 * @var array|null $stats
 * @var array $filterOptions
 * @var int|null $lastSyncedAt
 * @var int $pageSize
 * @var array $pageSizes
 * @var array $filters
 */

$this->breadcrumbs = array('Tổng hợp danh sách Vòng Chung Kết');
$this->Tabletitle  = 'Tổng hợp danh sách Vòng Chung Kết';

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/toast.js',
    CClientScript::POS_END
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/finalattendeerosters-admin.js',
    CClientScript::POS_END
);

$canCreate = PermissionHelper::can('finalattendeerosters', 'create');
$canUpdate = PermissionHelper::can('finalattendeerosters', 'update');

/** Dữ liệu cũ sau 24h thì nhắc HO đồng bộ lại */
$isStale = $lastSyncedAt && (time() - $lastSyncedAt) > 86400;

$flashMessages = Yii::app()->user->getFlashes();
?>

<div id="final-attendee-roster-config"
     data-event-id="<?php echo (int) $eventId; ?>"
     data-period-id="<?php echo (int) $periodId; ?>"
     data-filters-url="<?php echo $this->createUrl('admin'); ?>"
     data-filter-options-url="<?php echo $this->createUrl('filterOptions'); ?>"
     data-update-field-url="<?php echo $this->createUrl('updateField'); ?>"
     data-reset-field-url="<?php echo $this->createUrl('resetField'); ?>"
     data-can-update="<?php echo $canUpdate ? 1 : 0; ?>"
     data-field-labels="<?php echo CHtml::encode(CJSON::encode(FinalAttendeeRosters::editableFields())); ?>"
     data-flash="<?php echo CHtml::encode(CJSON::encode($flashMessages)); ?>"></div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Sự kiện <span class="text-danger">*</span></label>
                <form method="get" action="<?php echo $this->createUrl('admin'); ?>" id="form-event">
                    <?php echo CHtml::dropDownList('event_id', $eventId, $eventList, array(
                        'class'    => 'form-select',
                        'empty'    => '-- Chọn sự kiện --',
                        'onchange' => 'this.form.submit()',
                    )); ?>
                </form>
            </div>

            <div class="col-md-4">
                <label class="form-label">Đợt Vòng Chung Kết <span class="text-danger">*</span></label>
                <form method="get" action="<?php echo $this->createUrl('admin'); ?>" id="form-period">
                    <input type="hidden" name="event_id" value="<?php echo (int) $eventId; ?>">
                    <?php echo CHtml::dropDownList('period_id', $periodId, $periodList, array(
                        'class'    => 'form-select',
                        'empty'    => $eventId ? '-- Chọn đợt Vòng Chung Kết --' : '-- Chọn sự kiện trước --',
                        'disabled' => $eventId ? null : 'disabled',
                        'onchange' => 'this.form.submit()',
                    )); ?>
                </form>
            </div>

            <div class="col-md-4 text-end">
                <?php if ($eventId && $periodId): ?>
                    <?php if ($canCreate): ?>
                        <button type="button" class="btn btn-primary"
                                data-bs-toggle="modal" data-bs-target="#modal_sync">
                            <i class="fa fa-refresh me-1"></i>Đồng bộ từ danh sách VCK
                        </button>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($lastSyncedAt): ?>
            <div class="mt-2 small text-muted">
                Đồng bộ lần cuối: <?php echo date('d/m/Y H:i', $lastSyncedAt); ?>
                <?php if ($isStale): ?>
                    <span class="badge bg-warning text-dark ms-1">
                        <i class="fa fa-exclamation-triangle me-1"></i>Dữ liệu có thể đã cũ
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if (!$eventId || !$periodId): ?>
    <div class="alert alert-info">
        <i class="fa fa-info-circle me-1"></i>
        Vui lòng chọn <strong>sự kiện</strong> và <strong>đợt Vòng Chung Kết</strong> để xem danh sách tổng hợp.
    </div>
<?php else: ?>

    <?php if ($stats !== null): ?>
    <div class="row g-2 mb-3">
        <?php
        $cards = array(
            array('label' => 'Tổng số người', 'value' => $stats['total'], 'icon' => 'fa-users', 'class' => 'text-primary'),
            array('label' => 'Đã có mã lucky', 'value' => $stats['with_lucky'], 'icon' => 'fa-ticket', 'class' => 'text-success'),
            array('label' => 'Chưa có mã lucky', 'value' => $stats['without_lucky'], 'icon' => 'fa-hourglass-half', 'class' => $stats['without_lucky'] > 0 ? 'text-danger' : 'text-muted'),
            array('label' => 'Đã đặt PIN', 'value' => $stats['pin_set'], 'icon' => 'fa-lock', 'class' => 'text-info'),
            array('label' => 'Đã sửa tay', 'value' => $stats['with_override'], 'icon' => 'fa-pencil', 'class' => 'text-warning'),
            array('label' => 'Xung đột cần soát', 'value' => $stats['conflicts'], 'icon' => 'fa-exclamation-triangle', 'class' => $stats['conflicts'] > 0 ? 'text-danger' : 'text-muted'),
        );
        foreach ($cards as $card):
        ?>
        <div class="col-md-2 col-6">
            <div class="card h-100">
                <div class="card-body py-3 text-center">
                    <div class="<?php echo $card['class']; ?>">
                        <i class="fa <?php echo $card['icon']; ?> fa-lg"></i>
                    </div>
                    <div class="h4 mb-0 mt-1"><?php echo number_format($card['value']); ?></div>
                    <div class="small text-muted"><?php echo $card['label']; ?></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php if ($stats['withdrawn'] > 0 && (string) $filters['with_trashed'] !== '1'): ?>
        <div class="alert alert-secondary py-2 small">
            <i class="fa fa-user-times me-1"></i>
            Có <strong><?php echo number_format($stats['withdrawn']); ?></strong> người đã huỷ tư cách đang bị ẩn.
            <a href="<?php echo $this->createUrl('admin', array_merge(
                array('event_id' => $eventId, 'period_id' => $periodId, 'with_trashed' => 1),
                array_filter($filters, function ($v) { return $v !== null && $v !== ''; })
            )); ?>">Hiện họ</a>
        </div>
    <?php endif; ?>
    <?php endif; ?>

    <?php $this->renderPartial('_filters', array(
        'eventId'       => $eventId,
        'periodId'      => $periodId,
        'filterOptions' => $filterOptions,
        'filters'       => $filters,
        'pageSize'      => $pageSize,
        'pageSizes'     => $pageSizes,
    )); ?>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px;">STT</th>
                            <th style="width:140px;">Mã lucky</th>
                            <th>Họ và tên</th>
                            <th style="width:110px;">Mã NV</th>
                            <th>Đơn vị</th>
                            <th>Bộ phận</th>
                            <th>Phòng ban</th>
                            <th>Chức danh</th>
                            <th style="width:80px;">Size áo</th>
                            <th style="width:110px;">Loại</th>
                            <th style="width:90px;">PIN</th>
                            <th style="width:150px;">Trạng thái</th>
                            <?php if ($canUpdate): ?>
                                <th style="width:90px;">Thao tác</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rows  = $dataProvider->getData();
                        $index = $dataProvider->pagination ? $dataProvider->pagination->offset : 0;

                        foreach ($rows as $row):
                            $index++;
                            $isWithdrawn = !empty($row->is_withdrawn);
                            $overridden  = is_array($row->overridden_fields) ? $row->overridden_fields : array();
                        ?>
                        <tr class="<?php echo $isWithdrawn ? 'table-secondary opacity-75' : ''; ?>">
                            <td><?php echo $index; ?></td>

                            <td>
                                <?php if ($row->lucky_number): ?>
                                    <div class="fw-bold"><?php echo CHtml::encode($row->lucky_number); ?></div>
                                    <div class="small text-muted">
                                        <?php echo CHtml::encode($row->login_identifier); ?>
                                        <button type="button" class="btn btn-link btn-sm p-0 ms-1 js-copy"
                                                data-copy="<?php echo CHtml::encode($row->login_identifier); ?>"
                                                title="Sao chép định danh">
                                            <i class="fa fa-copy"></i>
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Chưa cấp</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php echo $this->renderPartial('_cell', array(
                                    'row'        => $row,
                                    'field'      => 'full_name',
                                    'value'      => $row->full_name,
                                    'overridden' => $overridden,
                                    'canUpdate'  => $canUpdate,
                                ), true); ?>
                                <?php if ($row->attendee_id): ?>
                                    <div class="small">
                                        <a href="<?php echo $this->createUrl('/admin/attendees/view', array('id' => $row->attendee_id)); ?>"
                                           target="_blank">Xem bản ghi gốc</a>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($overridden)): ?>
                                    <span class="badge bg-warning text-dark mt-1">
                                        <i class="fa fa-pencil me-1"></i>Đã sửa tay (<?php echo count($overridden); ?> trường)
                                    </span>
                                <?php endif; ?>
                            </td>

                            <td><?php echo $this->renderPartial('_cell', array(
                                'row' => $row, 'field' => 'staff_code', 'value' => $row->staff_code, 'overridden' => $overridden,
                                    'canUpdate'  => $canUpdate,
                            ), true); ?></td>

                            <td><?php echo $this->renderPartial('_cell', array(
                                'row' => $row, 'field' => 'property_name', 'value' => $row->property_name, 'overridden' => $overridden,
                                    'canUpdate'  => $canUpdate,
                            ), true); ?></td>

                            <td>
                                <?php if (trim((string) $row->division_name) === ''): ?>
                                    <span class="badge bg-warning text-dark">Chưa xác định</span>
                                <?php else: ?>
                                    <?php echo $this->renderPartial('_cell', array(
                                        'row' => $row, 'field' => 'division_name', 'value' => $row->division_name, 'overridden' => $overridden,
                                    'canUpdate'  => $canUpdate,
                                    ), true); ?>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (trim((string) $row->department_name) === ''): ?>
                                    <span class="badge bg-warning text-dark">Chưa xác định</span>
                                <?php else: ?>
                                    <?php echo $this->renderPartial('_cell', array(
                                        'row' => $row, 'field' => 'department_name', 'value' => $row->department_name, 'overridden' => $overridden,
                                    'canUpdate'  => $canUpdate,
                                    ), true); ?>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php echo $this->renderPartial('_cell', array(
                                    'row'        => $row,
                                    'field'      => 'position',
                                    'value'      => $row->position_display,
                                    'overridden' => $overridden,
                                    'canUpdate'  => $canUpdate,
                                ), true); ?>
                            </td>

                            <td><?php echo CHtml::encode($row->shirt_size ?: '-'); ?></td>

                            <td><?php echo FinalAttendeeRosters::getTypeBadge($row->attendee_type); ?></td>

                            <td>
                                <?php echo $row->pin_is_set
                                    ? '<span class="badge bg-success">Đã đặt</span>'
                                    : '<span class="badge bg-secondary">Chưa đặt</span>'; ?>
                            </td>

                            <td>
                                <?php echo FinalAttendeeRosters::getStatusLabel($row->status, $isWithdrawn); ?>
                                <?php if ($row->conflict_flag): ?>
                                    <div class="mt-1">
                                        <span class="badge bg-danger" title="<?php echo CHtml::encode(FinalAttendeeRosters::getConflictLabel($row->conflict_flag)); ?>">
                                            <i class="fa fa-exclamation-triangle me-1"></i><?php echo CHtml::encode(FinalAttendeeRosters::getConflictLabel($row->conflict_flag)); ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <?php if ($canUpdate): ?>
                            <td>
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary js-edit-row"
                                        data-row="<?php echo CHtml::encode(CJSON::encode(array(
                                            'id'                => $row->id,
                                            'full_name'         => $row->full_name,
                                            'staff_code'        => $row->staff_code,
                                            'id_card'           => $row->id_card,
                                            'phone_number'      => $row->phone_number,
                                            'email'             => $row->email,
                                            'property_name'     => $row->property_name,
                                            'unit_label'        => $row->unit_label,
                                            'division_name'     => $row->division_name,
                                            'department_name'   => $row->department_name,
                                            'position'          => $row->position,
                                            'shirt_size'        => $row->shirt_size,
                                            'attendee_type'     => $row->attendee_type,
                                            'note'              => $row->note,
                                            'overridden_fields' => $overridden,
                                            'source_snapshot'   => is_array($row->source_snapshot) ? $row->source_snapshot : array(),
                                            'lucky_number'      => $row->lucky_number,
                                            'updated_by'        => $row->updated_by,
                                            'last_synced_at'    => $row->last_synced_at,
                                        ))); ?>"
                                        title="Sửa thông tin người này">
                                    <i class="fa fa-pencil"></i>
                                </button>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>

                        <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="<?php echo $canUpdate ? 13 : 12; ?>" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                Không có dữ liệu. Hãy bấm <strong>Đồng bộ từ danh sách VCK</strong> để nạp danh sách.
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($dataProvider->pagination): ?>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="small text-muted">
                        Tổng <strong><?php echo number_format($dataProvider->getTotalItemCount()); ?></strong> người
                    </div>
                    <?php $this->widget('CLinkPager', array(
                        'pages'                => $dataProvider->pagination,
                        'htmlOptions'          => array('class' => 'pagination mb-0'),
                        'header'               => '',
                        'selectedPageCssClass' => 'active',
                    )); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($canUpdate): ?>
        <?php $this->renderPartial('_modal_edit_row'); ?>
    <?php endif; ?>

    <?php if ($canCreate): ?>
        <?php $this->renderPartial('_modal_sync', array(
            'eventId'       => $eventId,
            'periodId'      => $periodId,
            'filterOptions' => $filterOptions,
        )); ?>
    <?php endif; ?>
<?php endif; ?>
