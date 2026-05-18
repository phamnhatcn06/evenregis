<?php
$this->menu = array(
    array(
        'label' => Yii::t('app', 'Manage'),
        'labelIcon' => Yii::t('app', 'Manage'),
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
    array(
        'label' => Yii::t('app', 'Create'),
        'labelIcon' => Yii::t('app', 'Create'),
        'url' => $this->createUrl('create'),
        'color' => 'success',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => Yii::t('app', 'Update'),
        'labelIcon' => Yii::t('app', 'Update'),
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'warning',
        'icon' => 'fa-pencil',
        'id' => 'btn_update',
    ),
    array(
        'label' => Yii::t('app', 'Delete'),
        'labelIcon' => Yii::t('app', 'Delete'),
        'url' => $this->createUrl('delete', array('id' => $model->id)),
        'color' => 'danger',
        'icon' => 'fa-trash',
        'id' => 'btn_delete'
    ),
);

$this->breadcrumbs = array(
    Registrations::label(2) => array('admin'),
    Yii::t('app', 'View'),
);

$this->Tabletitle = 'Chi tiết phiếu đăng ký của ' . $model->property_name;
?>

<?php
$attributes = array(
    array('label' => 'ID', 'value' => $model->id),
    array('label' => 'Sự kiện', 'value' => isset($model->event_name) ? $model->event_name : ''),
    array('label' => 'Đơn vị', 'value' => isset($model->property_name) ? $model->property_name : ''),
    array('label' => 'Đợt đăng ký', 'value' => isset($model->period_name) ? $model->period_name : ''),
    array('label' => 'Trạng thái', 'value' => Registrations::getStatusLabel($model->status), 'raw' => true),
    array('label' => 'Ngày nộp', 'value' => $model->submitted_at ? MyHelper::formatDateTime($model->submitted_at) : '-'),
    array('label' => 'Ngày duyệt', 'value' => $model->reviewed_at ? MyHelper::formatDateTime($model->reviewed_at) : '-'),
    array('label' => 'Lý do từ chối', 'value' => $model->rejection_reason ?: '-'),
    array('label' => 'Ghi chú', 'value' => $model->note ?: '-'),
    array('label' => 'Ngày tạo', 'value' => MyHelper::formatDateTime($model->created_at)),
);

// Parse documents
$documents = array();
if (!empty($model->document)) {
    $parsed = json_decode($model->document, true);
    if (is_array($parsed)) {
        $documents = $parsed;
    } elseif (is_string($model->document)) {
        $documents = array($model->document);
    }
}

?>

<div class="row mb-3">
    <!-- Thông tin chung -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin chung</h5>
                <div class="btn-group">
                    <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                        <form method="post" action="<?php echo $this->createUrl('submit', array('id' => $model->id)); ?>" style="display:inline;">
                            <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Bạn có chắc muốn nộp phiếu đăng ký này?')">
                                <i class="fa fa-paper-plane me-1"></i>Nộp
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if ($model->status == Registrations::STATUS_SUBMITTED): ?>
                        <form method="post" action="<?php echo $this->createUrl('approve', array('id' => $model->id)); ?>" style="display:inline;">
                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Bạn có chắc muốn phê duyệt phiếu đăng ký này?')">
                                <i class="fa fa-check me-1"></i>Duyệt
                            </button>
                        </form>
                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                            <i class="fa fa-times me-1"></i>Từ chối
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped mb-0">
                    <tbody>
                        <?php foreach ($attributes as $attr): ?>
                            <tr>
                                <th style="width:35%;background:#f8f9fa;"><?php echo CHtml::encode($attr['label']); ?></th>
                                <td><?php echo isset($attr['raw']) && $attr['raw'] ? $attr['value'] : CHtml::encode($attr['value']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tệp đính kèm -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-file-text me-2"></i>Tệp đính kèm</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($documents)): ?>
                    <div class="row g-2">
                        <?php foreach ($documents as $index => $docUrl):
                            $filename = basename($docUrl);
                            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'));
                            $isPdf = ($ext === 'pdf');
                        ?>
                            <div class="col-6 col-md-4">
                                <div class="card h-100">
                                    <?php if ($isImage): ?>
                                        <img src="<?php echo CHtml::encode($docUrl); ?>" class="card-img-top" style="height:120px;object-fit:cover;cursor:pointer;"
                                            onclick="viewDocument('<?php echo CHtml::encode($docUrl); ?>', 'image')" title="Click để xem">
                                    <?php else: ?>
                                        <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:120px;">
                                            <?php if ($isPdf): ?>
                                                <i class="fa fa-file-pdf-o fa-2x text-danger"></i>
                                            <?php elseif (in_array($ext, array('doc', 'docx'))): ?>
                                                <i class="fa fa-file-word-o fa-2x text-primary"></i>
                                            <?php else: ?>
                                                <i class="fa fa-file-o fa-2x text-muted"></i>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="card-body p-2 text-center">
                                        <small class="text-truncate d-block mb-1" title="<?php echo CHtml::encode($filename); ?>">
                                            <?php echo CHtml::encode($filename); ?>
                                        </small>
                                        <?php if ($isImage || $isPdf): ?>
                                            <button type="button" class="btn btn-xs btn-outline-primary"
                                                onclick="viewDocument('<?php echo CHtml::encode($docUrl); ?>', '<?php echo $isImage ? 'image' : 'pdf'; ?>')">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                        <a href="<?php echo CHtml::encode($docUrl); ?>" class="btn btn-xs btn-outline-secondary" download>
                                            <i class="fa fa-download"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">Không có tệp đính kèm.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Load attendees
$attendees = Attendees::getByRegistrationId($model->id);
$property = Properties::fetchFromApi($model->property_id);
$isHotel = $property && !empty($property->smile_code);

// Load roles for dropdown
$rolesData = Roles::getApiDataProvider(array(), 100)->getData();
$roles = array();
foreach ($rolesData as $r) {
    $rId = isset($r['id']) ? $r['id'] : (isset($r->id) ? $r->id : null);
    $rName = isset($r['name']) ? $r['name'] : (isset($r->name) ? $r->name : '');
    if ($rId) $roles[$rId] = $rName;
}

// Load transports for dropdown
$transportsData = Transports::getApiDataProvider(array(), 100)->getData();
$transports = array();
foreach ($transportsData as $t) {
    $tId = isset($t['id']) ? $t['id'] : (isset($t->id) ? $t->id : null);
    $tName = isset($t['name']) ? $t['name'] : (isset($t->name) ? $t->name : '');
    if ($tId) $transports[$tId] = $tName;
}
?>

<div class="card mb-3" id="attendees-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-users me-2"></i>Danh sách người tham dự (<?php echo count($attendees); ?>)</h5>
        <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
            <div>
                <?php if ($isHotel): ?>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addAttendeeFromStaffModal">
                        <i class="fa fa-user-plus me-1"></i>Chọn từ danh sách nhân viên
                    </button>
                <?php else: ?>
                    <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addAttendeeManualModal">
                        <i class="fa fa-user-plus me-1"></i>Thêm người tham dự
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm mb-0" id="attendees-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">STT</th>
                        <th style="width:60px;">Ảnh</th>
                        <th>Họ tên</th>
                        <th>Phòng ban - Chức danh</th>
                        <th>Vai trò</th>
                        <th>Ngày vào làm</th>
                        <th>Ngày đến</th>
                        <th>Ngày đi</th>
                        <th>Phương tiện</th>
                        <th style="width:90px;">Trạng thái</th>
                        <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                            <th style="width:70px;"></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendees)): ?>
                        <tr><td colspan="<?php echo $model->status == Registrations::STATUS_DRAFT ? 11 : 10; ?>" class="text-center text-muted">Chưa có người tham dự nào.</td></tr>
                    <?php else: ?>
                        <?php foreach ($attendees as $idx => $att):
                            $attId = isset($att['id']) ? $att['id'] : '';
                            $fullName = isset($att['full_name']) ? $att['full_name'] : '';
                            $position = isset($att['position']) ? $att['position'] : '';
                            $roleName = isset($att['role_name']) ? $att['role_name'] : '';
                            $photoPath = isset($att['portrait_path']) ? $att['portrait_path'] : (isset($att['photo_path']) ? $att['photo_path'] : '');
                            $approvalStatus = isset($att['approval_status']) ? (int)$att['approval_status'] : Attendees::APPROVAL_PENDING;
                            $startDate = isset($att['start_date']) ? $att['start_date'] : '';
                            $arrivalDate = isset($att['arrival_date']) ? $att['arrival_date'] : '';
                            $departureDate = isset($att['departure_date']) ? $att['departure_date'] : '';
                            $transportName = isset($att['transport_name']) ? $att['transport_name'] : '';
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $idx + 1; ?></td>
                                <td class="text-center">
                                    <?php if ($photoPath): ?>
                                        <img src="<?php echo CHtml::encode($photoPath); ?>" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                            <i class="fa fa-user text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo CHtml::encode($fullName); ?></td>
                                <td><?php echo CHtml::encode($position); ?></td>
                                <td><?php echo CHtml::encode($roleName); ?></td>
                                <td><?php echo $startDate ? date('d/m/Y', strtotime($startDate)) : '-'; ?></td>
                                <td><?php echo $arrivalDate ? date('d/m/Y', strtotime($arrivalDate)) : '-'; ?></td>
                                <td><?php echo $departureDate ? date('d/m/Y', strtotime($departureDate)) : '-'; ?></td>
                                <td><?php echo CHtml::encode($transportName ?: '-'); ?></td>
                                <td><?php echo Attendees::getApprovalStatusLabel($approvalStatus); ?></td>
                                <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editAttendee(<?php echo $attId; ?>)" title="Sửa">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteAttendee(<?php echo $attId; ?>)" title="Xóa">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                        <form method="post" action="<?php echo $this->createUrl('deleteAttendee', array('id' => $attId, 'registration_id' => $model->id)); ?>" id="delete-attendee-form-<?php echo $attId; ?>" style="display:none;"></form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Nhóm chi tiết đăng ký theo loại nội dung
$detailsByContent = array();
foreach ($registrationDetails as $detail) {
    $code = isset($detail['content_code']) ? $detail['content_code'] : 'other';
    if (!isset($detailsByContent[$code])) {
        $detailsByContent[$code] = array();
    }
    $detailsByContent[$code][] = $detail;
}

$contentConfig = array(
    'sports' => array('icon' => 'fa-futbol-o', 'label' => 'Thi đấu thể thao', 'itemLabel' => 'Môn', 'qtyLabel' => 'Số đội/người'),
    'competition' => array('icon' => 'fa-trophy', 'label' => 'Thi nghiệp vụ', 'itemLabel' => 'Cuộc thi', 'qtyLabel' => 'Số người'),
    'miss' => array('icon' => 'fa-star', 'label' => 'Hội thi sắc đẹp', 'itemLabel' => 'Nội dung', 'qtyLabel' => 'Số người'),
    'talent' => array('icon' => 'fa-music', 'label' => 'Hội diễn văn nghệ', 'itemLabel' => 'Nội dung', 'qtyLabel' => 'Số tiết mục'),
);
?>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-list me-2"></i>Chi tiết đăng ký</h5>
        <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
            <div class="">
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addDetailModal" onclick="resetAddModal()">
                    <i class="fa fa-futbol-o me-1"></i>Đăng ký thể thao
                </button>
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addCompetitionModal" onclick="resetCompetitionModal()">
                    <i class="fa fa-trophy me-1"></i>Đăng ký nghiệp vụ
                </button>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <?php if (empty($registrationDetails)): ?>
            <p class="text-muted mb-0">Chưa có chi tiết đăng ký nào.</p>
        <?php else: ?>
            <?php foreach ($detailsByContent as $code => $details):
                $config = isset($contentConfig[$code]) ? $contentConfig[$code] : array('icon' => 'fa-list', 'label' => 'Khác', 'itemLabel' => 'Nội dung', 'qtyLabel' => 'Số lượng');
            ?>
                <div class="mb-4">
                    <h6 class="border-bottom pb-2 mb-3">
                        <i class="fa <?php echo $config['icon']; ?> me-2"></i><?php echo $config['label']; ?>
                    </h6>
                    <table class="table table-bordered table-striped table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?php echo $config['itemLabel']; ?></th>
                                <th style="width:120px;"><?php echo $config['qtyLabel']; ?></th>
                                <th>Ghi chú</th>
                                <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                                    <th style="width:60px;"></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($details as $detail):
                                $itemName = '-';
                                if (!empty($detail['sport_name'])) $itemName = $detail['sport_name'];
                                elseif (!empty($detail['competition_name'])) $itemName = $detail['competition_name'];
                                elseif (!empty($detail['content_name'])) $itemName = $detail['content_name'];
                                $detailId = isset($detail['id']) ? $detail['id'] : null;
                                $attendees = ($code === 'competition' && $detailId && isset($detailAttendees[$detailId])) ? $detailAttendees[$detailId] : array();
                            ?>
                                <tr>
                                    <td>
                                        <?php echo CHtml::encode($itemName); ?>
                                        <?php if ($code === 'competition' && !empty($attendees)): ?>
                                            <div class="mt-2">
                                                <small class="text-muted d-block mb-1">Danh sách thí sinh:</small>
                                                <?php foreach ($attendees as $idx => $att):
                                                    $staffName = isset($att['staff_name']) ? $att['staff_name'] : (isset($att['staff_full_name']) ? $att['staff_full_name'] : '');
                                                    $staffCode = isset($att['staff_code']) ? $att['staff_code'] : '';
                                                ?>
                                                    <span class="badge bg-light text-dark border me-1 mb-1">
                                                        <?php echo ($idx + 1) . '. '; ?>
                                                        <?php echo CHtml::encode($staffCode ? $staffCode . ' - ' . $staffName : $staffName); ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center"><?php echo CHtml::encode(isset($detail['quantity']) ? $detail['quantity'] : 1); ?></td>
                                    <td><?php echo CHtml::encode(isset($detail['note']) ? $detail['note'] : ''); ?></td>
                                    <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                                        <td class="text-center">
                                            <form method="post" action="<?php echo $this->createUrl('deleteDetail', array('id' => $detail['id'], 'registration_id' => $model->id)); ?>" style="display:inline;" id="delete-detail-form-<?php echo $detail['id']; ?>">
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteDetail(<?php echo $detail['id']; ?>)">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Add Sports -->
<div class="modal fade" id="addDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addDetail'); ?>" id="add-detail-form">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="content_type" id="content_type" value="sports">
                <input type="hidden" name="content_id" id="content_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-futbol-o me-2"></i>Đăng ký thể thao</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Môn thể thao <span class="text-danger">*</span></label>
                        <select class="form-select" id="item_id" name="item_id" required>
                            <option value="">-- Chọn môn thể thao --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số đội/người <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="quantity" id="quantity" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea class="form-control" name="note" id="note" rows="2" placeholder="Ghi chú thêm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Đăng ký</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Competition Registration -->
<div class="modal fade" id="addCompetitionModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addCompetitionRegistration'); ?>" id="add-competition-form">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="content_id" id="comp_content_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Đăng ký thi nghiệp vụ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Cuộc thi <span class="text-danger">*</span></label>
                                <select class="form-select" id="comp_competition_id" name="competition_id" required>
                                    <option value="">-- Chọn cuộc thi --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Đơn vị <span class="text-danger">*</span></label>
                                <select class="form-select" id="comp_property_id" name="property_id" required>
                                    <option value="">-- Chọn cuộc thi trước --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số lượng tối đa</label>
                                <input type="text" class="form-control" id="comp_max_per_org" readonly value="-">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" name="note" rows="2" placeholder="Ghi chú thêm..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Chọn nhân viên tham dự <span class="text-danger">*</span></label>
                            <div class="row" id="dual_listbox_wrapper" style="display:none;">
                                <div class="col-md-5">
                                    <div class="card h-100">
                                        <div class="card-header py-2">
                                            <small class="fw-bold">Danh sách nhân viên</small>
                                            <input type="text" class="form-control form-control-sm mt-2" id="staff_search" placeholder="Tìm kiếm...">
                                        </div>
                                        <div class="card-body p-0" style="height:300px;overflow-y:auto;">
                                            <div class="list-group list-group-flush" id="available_staff_list">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="btn_add_staff" title="Thêm">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="btn_add_all_staff" title="Thêm tất cả">
                                        <i class="fa fa-angle-double-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger mb-2" id="btn_remove_staff" title="Xóa">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn_remove_all_staff" title="Xóa tất cả">
                                        <i class="fa fa-angle-double-left"></i>
                                    </button>
                                </div>
                                <div class="col-md-5">
                                    <div class="card h-100">
                                        <div class="card-header py-2">
                                            <small class="fw-bold">Đã chọn (<span id="selected_count">0</span>/<span id="max_count">0</span>)</small>
                                        </div>
                                        <div class="card-body p-0" style="height:340px;overflow-y:auto;">
                                            <div class="list-group list-group-flush" id="selected_staff_list">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="staff_placeholder" class="text-center text-muted py-5">
                                <i class="fa fa-users fa-3x mb-3"></i>
                                <p>Vui lòng chọn cuộc thi và đơn vị để hiển thị danh sách nhân viên</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success" id="btn_submit_competition">Đăng ký</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal View Document -->
<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xem tài liệu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="documentModalBody" style="min-height:500px;">
            </div>
            <div class="modal-footer">
                <a href="#" id="documentDownloadLink" class="btn btn-primary" download>
                    <i class="fa fa-download me-1"></i>Tải xuống
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('reject', array('id' => $model->id)); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Từ chối phiếu đăng ký</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Nhập lý do từ chối..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Attendee from Staff (for Hotels) -->
<div class="modal fade" id="addAttendeeFromStaffModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addAttendeesFromStaff'); ?>" id="add-attendees-staff-form">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="event_id" value="<?php echo $model->event_id; ?>">
                <input type="hidden" name="property_id" value="<?php echo $model->property_id; ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-users me-2"></i>Chọn nhân viên tham dự</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                            <select class="form-select" name="role_id" id="staff_role_id" required>
                                <option value="">-- Chọn vai trò --</option>
                                <?php foreach ($roles as $rId => $rName): ?>
                                    <option value="<?php echo $rId; ?>"><?php echo CHtml::encode($rName); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ngày đến <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="arrival_date" id="staff_arrival_date" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Ngày đi <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="departure_date" id="staff_departure_date" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Phương tiện <span class="text-danger">*</span></label>
                            <select class="form-select" name="transport_id" id="staff_transport_id" required>
                                <option value="">-- Chọn --</option>
                                <?php foreach ($transports as $tId => $tName): ?>
                                    <option value="<?php echo $tId; ?>"><?php echo CHtml::encode($tName); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="card h-100">
                                <div class="card-header py-2">
                                    <small class="fw-bold">Danh sách nhân viên</small>
                                    <input type="text" class="form-control form-control-sm mt-2" id="attendee_staff_search" placeholder="Tìm kiếm theo tên, mã NV...">
                                </div>
                                <div class="card-body p-0" style="height:300px;overflow-y:auto;">
                                    <div class="list-group list-group-flush" id="attendee_available_staff_list">
                                        <div class="text-center text-muted py-5">Đang tải danh sách nhân viên...</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                            <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="btn_add_attendee_staff" title="Thêm">
                                <i class="fa fa-chevron-right"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="btn_add_all_attendee_staff" title="Thêm tất cả">
                                <i class="fa fa-angle-double-right"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger mb-2" id="btn_remove_attendee_staff" title="Xóa">
                                <i class="fa fa-chevron-left"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" id="btn_remove_all_attendee_staff" title="Xóa tất cả">
                                <i class="fa fa-angle-double-left"></i>
                            </button>
                        </div>
                        <div class="col-md-5">
                            <div class="card h-100">
                                <div class="card-header py-2">
                                    <small class="fw-bold">Đã chọn (<span id="attendee_selected_count">0</span>)</small>
                                </div>
                                <div class="card-body p-0" style="height:340px;overflow-y:auto;">
                                    <div class="list-group list-group-flush" id="attendee_selected_staff_list">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success" id="btn_submit_attendees_staff">Thêm người tham dự</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Attendee -->
<div class="modal fade" id="editAttendeeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" id="edit-attendee-form" enctype="multipart/form-data">
                <input type="hidden" name="attendee_id" id="edit_attendee_id">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-pencil me-2"></i>Sửa thông tin người tham dự</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="full_name" id="edit_full_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Chức danh</label>
                                <input type="text" class="form-control bg-light" id="edit_position" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Phòng ban</label>
                                <input type="text" class="form-control bg-light" id="edit_department" readonly>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                                <select class="form-select" name="role_id" id="edit_role_id" required>
                                    <option value="">-- Chọn vai trò --</option>
                                    <?php foreach ($roles as $rId => $rName): ?>
                                        <option value="<?php echo $rId; ?>"><?php echo CHtml::encode($rName); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" name="note" id="edit_note" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ảnh chân dung (530x530px)</label>
                                <div id="edit_portrait_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="portrait_file" accept="image/*">
                                <small class="text-muted">Để trống nếu không thay đổi</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ảnh CCCD mặt trước</label>
                                <div id="edit_cccd_front_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="cccd_front_file" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ảnh CCCD mặt sau</label>
                                <div id="edit_cccd_back_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="cccd_back_file" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hợp đồng lao động</label>
                                <div id="edit_contract_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="contract_file" accept="image/*,.pdf">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btn_save_attendee">
                        <i class="fa fa-save me-1"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Add Attendee Manual (for non-Hotels) -->
<div class="modal fade" id="addAttendeeManualModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addAttendeeManual'); ?>" id="add-attendee-manual-form" enctype="multipart/form-data">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="event_id" value="<?php echo $model->event_id; ?>">
                <input type="hidden" name="property_id" value="<?php echo $model->property_id; ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-user-plus me-2"></i>Thêm người tham dự</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="full_name" required placeholder="Nhập họ và tên">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Chức danh</label>
                                <input type="text" class="form-control" name="position" placeholder="Nhập chức danh">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                                <select class="form-select" name="role_id" required>
                                    <option value="">-- Chọn vai trò --</option>
                                    <?php foreach ($roles as $rId => $rName): ?>
                                        <option value="<?php echo $rId; ?>"><?php echo CHtml::encode($rName); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày bắt đầu làm việc <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="start_date" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Phương tiện <span class="text-danger">*</span></label>
                                        <select class="form-select" name="transport_id" required>
                                            <option value="">-- Chọn --</option>
                                            <?php foreach ($transports as $tId => $tName): ?>
                                                <option value="<?php echo $tId; ?>"><?php echo CHtml::encode($tName); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày đến <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="arrival_date" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày đi <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="departure_date" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" name="note" rows="2" placeholder="Ghi chú thêm..."></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ảnh chân dung (530x530px) <span class="text-danger">*</span></label>
                                <div id="add_portrait_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="portrait_file" accept="image/*" required>
                                <small class="text-muted">Ảnh chân dung dùng để in thẻ</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ảnh CCCD mặt trước <span class="text-danger">*</span></label>
                                <div id="add_cccd_front_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="cccd_front_file" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ảnh CCCD mặt sau <span class="text-danger">*</span></label>
                                <div id="add_cccd_back_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="cccd_back_file" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Hợp đồng lao động</label>
                                <div id="add_contract_preview" class="mb-2"></div>
                                <input type="file" class="form-control" name="contract_file" accept="image/*,.pdf">
                                <small class="text-muted">File ảnh hoặc PDF</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Thêm người tham dự</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Register JS file
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/registrations-view.js',
    CClientScript::POS_END
);

// Prepare config data
$sportIds = array();
$competitionIds = array();
foreach ($registrationDetails as $d) {
    if (!empty($d['sport_id'])) $sportIds[] = (int)$d['sport_id'];
    if (!empty($d['competition_id'])) $competitionIds[] = (int)$d['competition_id'];
}

// Lấy danh sách staff_id đã là attendee
$existingStaffIds = array();
foreach ($attendees as $att) {
    if (!empty($att['staff_id'])) {
        $existingStaffIds[] = (int)$att['staff_id'];
    }
}

$jsConfig = array(
    'eventId' => $model->event_id ? $model->event_id : null,
    'registrationId' => $model->id,
    'propertyId' => $model->property_id,
    'propertyCode' => isset($model->property_code) ? $model->property_code : '',
    'isHotel' => $isHotel,
    'registeredSports' => $sportIds,
    'registeredCompetitions' => $competitionIds,
    'existingStaffIds' => $existingStaffIds,
);

// Register init script
Yii::app()->clientScript->registerScript('registrations-view-init', '
    window.BASE_URL = "' . Yii::app()->createUrl('/') . '";
    document.addEventListener("DOMContentLoaded", function() {
        RegistrationView.init(' . CJSON::encode($jsConfig) . ');
    });
    function viewDocument(url, type) { RegistrationView.viewDocument(url, type); }
    function confirmDeleteDetail(id) { RegistrationView.confirmDeleteDetail(id); }
    function resetAddModal() { RegistrationView.resetAddModal(); }
    function resetCompetitionModal() { RegistrationView.resetCompetitionModal(); }
    function editAttendee(id) { RegistrationView.editAttendee(id); }
    function confirmDeleteAttendee(id) { RegistrationView.confirmDeleteAttendee(id); }
', CClientScript::POS_END);
?>