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
                        <tr>
                            <td colspan="<?php echo $model->status == Registrations::STATUS_DRAFT ? 11 : 10; ?>" class="text-center text-muted">Chưa có người tham dự nào.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attendees as $idx => $att):
                            $attId = isset($att['id']) ? $att['id'] : '';
                            $fullName = isset($att['full_name']) ? $att['full_name'] : '';
                            $position = isset($att['position']) ? $att['position'] : '';
                            $roleName = isset($att['role_name']) ? $att['role_name'] : '';
                            $photoPath = isset($att['portrait_path']) ? $att['portrait_path'] : (isset($att['photo_path']) ? $att['photo_path'] : '');
                            $approvalStatus = isset($att['approval_status']) ? (int)$att['approval_status'] : Attendees::APPROVAL_PENDING;
                            $startDate = isset($att['join_hotel_date']) ? $att['join_hotel_date'] : (isset($att['start_date']) ? $att['start_date'] : '');
                            $checkInDate = isset($att['check_in_date']) ? $att['check_in_date'] : '';
                            $checkOutDate = isset($att['check_out_date']) ? $att['check_out_date'] : '';
                            $transportName = isset($att['transport_name']) ? $att['transport_name'] : '';
                        ?>
                            <tr>
                                <td class="text-center"><?php echo $idx + 1; ?></td>
                                <td class="text-center">
                                    <?php if ($photoPath): ?>
                                        <img src="<?php echo CHtml::encode($photoPath); ?>" class="rounded" style="width:160px;height:160px;object-fit:cover;cursor:pointer;" onclick="viewDocument('<?php echo CHtml::encode($photoPath); ?>', 'image')" title="Click để xem">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:160px;height:160px;">
                                            <i class="fa fa-user text-muted fa-3x"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo CHtml::encode($fullName); ?></td>
                                <td><?php echo CHtml::encode($position); ?></td>
                                <td><?php echo CHtml::encode($roleName); ?></td>
                                <td><?php echo $startDate ? date('d/m/Y', strtotime($startDate)) : '-'; ?></td>
                                <td><?php echo $checkInDate ? date('d/m/Y', strtotime($checkInDate)) : '-'; ?></td>
                                <td><?php echo $checkOutDate ? date('d/m/Y', strtotime($checkOutDate)) : '-'; ?></td>
                                <td><?php echo CHtml::encode($transportName ?: '-'); ?></td>
                                <td><?php echo Attendees::getApprovalStatusLabel($approvalStatus); ?></td>
                                <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                                    <td class="text-center">
                                        <?php if (!empty($att['contract_path']) || !empty($att['portrait_path']) || !empty($att['cccd_front_path']) || !empty($att['cccd_back_path'])):
                                            $docs = array(
                                                'portrait' => isset($att['portrait_path']) ? $att['portrait_path'] : (isset($att['photo_path']) ? $att['photo_path'] : ''),
                                                'cccd_front' => isset($att['cccd_front_path']) ? $att['cccd_front_path'] : '',
                                                'cccd_back' => isset($att['cccd_back_path']) ? $att['cccd_back_path'] : '',
                                                'contract' => isset($att['contract_path']) ? $att['contract_path'] : '',
                                            );
                                        ?>
                                            <button type="button" class="btn btn-sm btn-outline-info me-1" onclick="viewAllDocuments(this)" data-docs="<?php echo CHtml::encode(CJSON::encode($docs)); ?>" title="Xem tài liệu đính kèm">
                                                <i class="fa fa-folder-open-o"></i>
                                            </button>
                                        <?php endif; ?>
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
                <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addDetailModal" onclick="resetSportModal()">
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

<?php $this->renderPartial('_modal_add_sport', array('model' => $model)); ?>
<?php $this->renderPartial('_modal_add_competition', array('model' => $model)); ?>
<?php $this->renderPartial('_modal_document'); ?>
<?php $this->renderPartial('_modal_reject', array('model' => $model)); ?>
<?php $this->renderPartial('_modal_add_attendee_staff', array('model' => $model, 'roles' => $roles, 'transports' => $transports)); ?>
<?php $this->renderPartial('_modal_edit_attendee', array('model' => $model, 'roles' => $roles, 'transports' => $transports)); ?>
<?php $this->renderPartial('_modal_add_attendee_manual', array('model' => $model, 'roles' => $roles, 'transports' => $transports)); ?>
<?php $this->renderPartial('_modal_all_documents'); ?>

<?php
// Register flatpickr
$baseUrl = Yii::app()->theme->baseUrl;
Yii::app()->clientScript->registerCssFile($baseUrl . '/assets/vendor/flatpickr/dist/flatpickr.min.css');
Yii::app()->clientScript->registerScriptFile($baseUrl . '/assets/vendor/flatpickr/dist/flatpickr.min.js', CClientScript::POS_END);

// Register JS file
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/registrations-view.js?v=' . time(),
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

// Flatpickr Vietnamese locale
Yii::app()->clientScript->registerScript('flatpickr-locale', '
    var Vietnamese = {
        weekdays: {
            shorthand: ["CN", "T2", "T3", "T4", "T5", "T6", "T7"],
            longhand: ["Chủ nhật", "Thứ hai", "Thứ ba", "Thứ tư", "Thứ năm", "Thứ sáu", "Thứ bảy"]
        },
        months: {
            shorthand: ["Th1", "Th2", "Th3", "Th4", "Th5", "Th6", "Th7", "Th8", "Th9", "Th10", "Th11", "Th12"],
            longhand: ["Tháng 1", "Tháng 2", "Tháng 3", "Tháng 4", "Tháng 5", "Tháng 6", "Tháng 7", "Tháng 8", "Tháng 9", "Tháng 10", "Tháng 11", "Tháng 12"]
        },
        firstDayOfWeek: 1
    };
    window.initDatePickers = function() {
        console.log("initDatePickers called, found elements:", document.querySelectorAll(".datepicker").length);
        document.querySelectorAll(".datepicker").forEach(function(el) {
            console.log("Processing element:", el.id, "already has flatpickr:", !!el._flatpickr);
            if (el._flatpickr || el.classList.contains("flatpickr-input")) return;
            var fp = flatpickr(el, {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                altInputClass: "form-control bg-white",
                allowInput: true,
                locale: Vietnamese
            });
            console.log("Flatpickr initialized for:", el.id, "instance:", fp);
        });
    };
', CClientScript::POS_END);

// Register init script
Yii::app()->clientScript->registerScript('registrations-view-init', '
    window.BASE_URL = "' . Yii::app()->createUrl('/') . '";
    document.addEventListener("DOMContentLoaded", function() {
        RegistrationView.init(' . CJSON::encode($jsConfig) . ');
        window.initDatePickers();
    });
    function viewDocument(url, type) { RegistrationView.viewDocument(url, type); }
    function confirmDeleteDetail(id) { RegistrationView.confirmDeleteDetail(id); }
    function resetAddModal() { RegistrationView.resetAddModal(); }
    function resetCompetitionModal() { RegistrationView.resetCompetitionModal(); }
    function editAttendee(id) { RegistrationView.editAttendee(id); }
    function confirmDeleteAttendee(id) { RegistrationView.confirmDeleteAttendee(id); }
', CClientScript::POS_END);
?>