<?php
$canEdit = $model->isEditable();

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
        'label' => 'Xuất Excel',
        'labelIcon' => 'Xuất Excel',
        'url' => Yii::app()->createUrl('/admin/reports/exportUnit', array('id' => $model->id)),
        'color' => 'info',
        'icon' => 'fa-file-excel-o',
        'id' => 'btn_export_excel',
    ),
);
if ($canEdit) {
    $this->menu[] = array(
        'label' => Yii::t('app', 'Delete'),
        'labelIcon' => Yii::t('app', 'Delete'),
        'url' => $this->createUrl('delete', array('id' => $model->id)),
        'color' => 'danger',
        'icon' => 'fa-trash',
        'id' => 'btn_delete'
    );
}

$pendingRequestCount = isset($incomingRequestsData) ? count($incomingRequestsData) : 0;
$this->breadcrumbs = array(
    Registrations::label(2) => array('admin'),
    Yii::t('app', 'View'),
);
$tabtile = 'Chi tiết phiếu đăng ký của ' . $model->property_name;
// /$tabtile .=   ($pendingRequestCount > 0 ? '<span class="badge bg-danger rounded-pill ms-2">' . $pendingRequestCount . ' yêu cầu chờ xử lý</span>' : '');
$this->Tabletitle =  $tabtile;
?>

<?php
// Helper function to resolve content code from request
function resolveContentCode($req)
{
    // Try direct content_code first
    $code = isset($req->content_code) ? $req->content_code : '';

    // Try to derive from content_name if not set
    if (empty($code) && isset($req->content_name)) {
        $name = mb_strtolower($req->content_name, 'UTF-8');
        if (strpos($name, 'thể thao') !== false || strpos($name, 'sport') !== false) {
            $code = 'sports';
        } elseif (strpos($name, 'văn nghệ') !== false || strpos($name, 'talent') !== false) {
            $code = 'talent';
        } elseif (strpos($name, 'nghiệp vụ') !== false || strpos($name, 'competition') !== false) {
            $code = 'competition';
        } elseif (strpos($name, 'miss') !== false || strpos($name, 'sắc đẹp') !== false) {
            $code = 'miss';
        }
    }

    // Normalize
    if ($code === 'sport') $code = 'sports';
    if ($code === 'competitions') $code = 'competition';
    if ($code === 'talents') $code = 'talent';
    if ($code === 'beauty_contests') $code = 'miss';

    return $code ?: 'sports'; // Default to sports
}

// Group alliance requests and history by content code
$allianceByContent = array(
    'sports' => array('pending' => array(), 'history' => array()),
    'talent' => array('pending' => array(), 'history' => array()),
    'competition' => array('pending' => array(), 'history' => array()),
    'miss' => array('pending' => array(), 'history' => array()),
    'other' => array('pending' => array(), 'history' => array()),
);

// Group incoming pending requests
if (!empty($incomingRequestsData)) {
    foreach ($incomingRequestsData as $item) {
        $contentCode = resolveContentCode($item['request']);
        if (!isset($allianceByContent[$contentCode])) $contentCode = 'other';
        $allianceByContent[$contentCode]['pending'][] = $item;
    }
}

// Group alliance history
if (!empty($allianceHistory)) {
    foreach ($allianceHistory as $item) {
        $contentCode = resolveContentCode($item['request']);
        if (!isset($allianceByContent[$contentCode])) $contentCode = 'other';
        $allianceByContent[$contentCode]['history'][] = $item;
    }
}
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

if (isset($allianceRequest) && $allianceRequest && $allianceRequest->status == AllianceRequests::STATUS_APPROVED && !empty($model->relation_property_name)) {
    $attributes[] = array('label' => 'Đơn vị liên quân', 'value' => $model->relation_property_name);
}


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

<style>
    @keyframes pulse-border {
        0% {
            border-left-color: #ffc107;
        }

        50% {
            border-left-color: #ff9800;
        }

        100% {
            border-left-color: #ffc107;
        }
    }

    .alliance-request-alert {
        animation: pulse-border 2s ease-in-out infinite;
    }

    @media (max-width: 768px) {
        .alliance-request-alert {
            flex-direction: column !important;
            align-items: flex-start !important;
        }

        .alliance-request-alert .d-flex.align-items-center.ms-3 {
            margin-left: 0 !important;
            margin-top: 1rem;
            width: 100%;
        }

        .alliance-request-alert .d-flex.align-items-center.ms-3 button {
            flex: 1;
        }
    }

    /* Căn chỉnh cột đồng nhất giữa các bảng nội dung */
    .content-table {
        table-layout: fixed;
        width: 100%;
    }

    .content-table .col-stt {
        width: 45px;
    }

    .content-table .col-name {
        width: 200px;
    }

    .content-table .col-count {
        width: 80px;
    }

    .content-table .col-list {
        width: auto;
    }

    .content-table .col-action {
        width: 90px;
    }
</style>

<?php if ((int)$model->status === Registrations::STATUS_REJECTED): ?>
    <div class="alert alert-warning d-flex align-items-start mb-3" role="alert">
        <i class="fa fa-exclamation-triangle fa-lg me-3 mt-1 text-warning flex-shrink-0"></i>
        <div class="flex-grow-1">
            <strong>Phiếu đăng ký đã được trả về — cần chỉnh sửa và gửi lại.</strong>
            <?php if (!empty($model->rejection_reason)): ?>
                <div class="mt-1">Lý do: <?php echo CHtml::encode($model->rejection_reason); ?></div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<div class="row mb-3">
    <!-- Thông tin chung -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin chung <?= ($pendingRequestCount > 0 ? '<span class="badge bg-danger rounded-pill ms-2">' . $pendingRequestCount . ' yêu cầu chờ xử lý</span>' : '') ?></h5>
                <div class="btn-group">
                    <?php if ((int)$model->status === Registrations::STATUS_DRAFT || (int)$model->status === Registrations::STATUS_REJECTED): ?>
                        <form id="form-submit-registration" method="post" action="<?php echo $this->createUrl('submit', array('id' => $model->id)); ?>" style="display:inline;">
                            <button type="button" class="btn btn-sm btn-info" onclick="confirmSubmitRegistration()">
                                <i class="fa fa-paper-plane me-1"></i>Gửi bản đăng ký
                            </button>
                        </form>
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-file-text me-2"></i>Tệp đính kèm</h5>
                <?php if ($canEdit): ?>
                    <button type="button" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#uploadDocumentModal">
                        <i class="fa fa-upload me-1"></i>Tải lên
                    </button>
                <?php endif; ?>
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
                                        <?php if ($canEdit): ?>
                                            <button type="button" class="btn btn-xs btn-outline-danger" onclick="confirmDeleteDocument(<?php echo $index; ?>)" title="Xóa">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
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

<?php if (!empty($approvalLogs)): ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-history me-2"></i>Lịch sử duyệt bản đăng ký</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px;">STT</th>
                                    <th>Bước duyệt</th>
                                    <th style="width:120px;">Hành động</th>
                                    <th>Người thực hiện</th>
                                    <th>Thời gian</th>
                                    <th>Ghi chú / Lý do</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approvalLogs as $idx => $log): ?>
                                    <tr>
                                        <td class="text-center"><?php echo $idx + 1; ?></td>
                                        <td><?php echo CHtml::encode($log->step_name ?: 'Bước ' . $log->step_index); ?></td>
                                        <td><?php echo BaseRegistrationApprovalLogs::getActionLabel($log->action); ?></td>
                                        <td><?php echo CHtml::encode($log->approver_name ?: '-'); ?></td>
                                        <td><?php echo $log->acted_at ? date('d/m/Y H:i', $log->acted_at) : '-'; ?></td>
                                        <td><?php echo CHtml::encode($log->comment ?: '-'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

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

// Load event contents để lấy event_content_id cho từng loại nội dung
// Gọi API trực tiếp để lấy raw data
$ecResult = ApiClient::get(ApiEndpoints::EVENT_CONTENT_LIST, array('event_id' => $model->event_id));
$eventContents = array();
if ($ecResult['success'] && isset($ecResult['data']['data'])) {
    $eventContents = $ecResult['data']['data'];
} elseif ($ecResult['success'] && isset($ecResult['data']) && is_array($ecResult['data'])) {
    $eventContents = $ecResult['data'];
}

$contentIdMap = array('sports' => null, 'talent' => null, 'competition' => null, 'miss' => null);
foreach ($eventContents as $ec) {
    $code = isset($ec['content_code']) ? $ec['content_code'] : (isset($ec['code']) ? $ec['code'] : '');
    $ecId = isset($ec['id']) ? $ec['id'] : null;
    // Normalize content codes
    if ($code === 'sport') $code = 'sports';
    if ($code === 'competitions') $code = 'competition';
    if ($code === 'talents') $code = 'talent';
    if ($code === 'beauty_contests') $code = 'miss';
    // Gán vào map
    if (array_key_exists($code, $contentIdMap) && $ecId) {
        $contentIdMap[$code] = $ecId;
    }
}
?>

<div class="card mb-3" id="attendees-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-users me-2"></i>Danh sách người tham dự (<?php echo count($attendees); ?>)</h5>
        <?php if ($canEdit): ?>
            <div>
                <?php if ($isHotel): ?>
                    <button type="button" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#addAttendeeFromStaffModal">
                        <i class="fa fa-user-plus me-1"></i>Chọn từ danh sách nhân viên
                    </button>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#addAttendeeManualModal">
                    <i class="fa fa-user-plus me-1"></i>Thêm người tham dự
                </button>
                <button type="button" class="btn btn-sm btn-success text-white ms-1" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                    <i class="fa fa-file-excel-o me-1"></i>Import Excel
                </button>

            </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <!-- Filter -->
        <div class="row mb-3 g-2">
            <div class="col-md-3">
                <input type="text" class="form-control form-control-sm" id="filter_name" placeholder="Tìm theo tên...">
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filter_role">
                    <option value="">-- Vai trò --</option>
                    <?php foreach ($roles as $rId => $rName): ?>
                        <option value="<?php echo CHtml::encode($rName); ?>"><?php echo CHtml::encode($rName); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filter_status">
                    <option value="">-- Trạng thái --</option>
                    <option value="Chờ duyệt">Chờ duyệt</option>
                    <option value="Đã duyệt">Đã duyệt</option>
                    <option value="Từ chối">Từ chối</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select form-select-sm" id="filter_transport">
                    <option value="">-- Phương tiện --</option>
                    <?php foreach ($transports as $tId => $tName): ?>
                        <option value="<?php echo CHtml::encode($tName); ?>"><?php echo CHtml::encode($tName); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn_reset_filter">
                    <i class="fa fa-refresh me-1"></i>Xóa lọc
                </button>
            </div>
        </div>
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
                        <?php if ($canEdit): ?>
                            <th style="width:70px;"></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($attendees)): ?>
                        <?php foreach ($attendees as $idx => $att):
                            $attId = isset($att['id']) ? $att['id'] : '';
                            $fullName = isset($att['full_name']) ? $att['full_name'] : '';
                            $position = isset($att['position']) ? $att['position'] : '';
                            $roleName = Attendees::resolveRoleNames(isset($att['role_id']) ? $att['role_id'] : '');
                            $photoPath = isset($att['portrait_path']) ? $att['portrait_path'] : (isset($att['photo_path']) ? $att['photo_path'] : '');
                            $approvalStatus = isset($att['approval_status']) ? (int)$att['approval_status'] : Attendees::APPROVAL_PENDING;
                            $startDate = isset($att['join_hotel_date']) ? $att['join_hotel_date'] : (isset($att['start_date']) ? $att['start_date'] : '');
                            $checkInDate = isset($att['check_in_date']) ? $att['check_in_date'] : '';
                            $checkOutDate = isset($att['check_out_date']) ? $att['check_out_date'] : '';
                            $transportName = isset($att['transport_name']) ? $att['transport_name'] : '';
                        ?>
                            <tr>
                                <td class="text-center" style="width: 50px;"><?php echo $idx + 1; ?></td>
                                <td class="text-center">
                                    <?php if ($photoPath): ?>
                                        <img src="<?php echo CHtml::encode($photoPath); ?>" class="rounded mx-auto d-block" style="width:160px;height:160px;object-fit:cover;cursor:pointer;" onclick="viewDocument('<?php echo CHtml::encode($photoPath); ?>', 'image')" title="Click để xem">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width:160px;height:160px;">
                                            <i class="fa fa-user text-muted fa-3x"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo CHtml::encode($fullName); ?></td>
                                <td><?php echo CHtml::encode($position); ?></td>
                                <td>
                                    <?php if (!empty($roleName)): ?>
                                        <?php foreach (array_map('trim', explode(',', $roleName)) as $role): ?>
                                            <span class="badge <?php echo Attendees::getRoleBadgeClass($role); ?> me-1 mb-1"><?php echo CHtml::encode($role); ?></span><br />
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $startDate ? date('d/m/Y', strtotime($startDate)) : '-'; ?></td>
                                <td><?php echo $checkInDate ? date('d/m/Y', strtotime($checkInDate)) : '-'; ?></td>
                                <td><?php echo $checkOutDate ? date('d/m/Y', strtotime($checkOutDate)) : '-'; ?></td>
                                <td><?php echo CHtml::encode($transportName ?: '-'); ?></td>
                                <td>
                                    <?php echo Attendees::getApprovalStatusLabel($approvalStatus); ?>
                                    <?php if ($approvalStatus != Attendees::APPROVAL_PENDING): ?>
                                        <button type="button" class="btn btn-xs btn-link p-0 ms-1"
                                            onclick="showApprovalLog(<?php echo CHtml::encode(CJSON::encode(array(
                                                                            'name' => $fullName,
                                                                            'status' => $approvalStatus,
                                                                            'approved_by' => isset($att['approved_by']) ? $att['approved_by'] : '',
                                                                            'approved_at' => isset($att['approved_at']) ? $att['approved_at'] : '',
                                                                            'rejection_reason' => isset($att['rejection_reason']) ? $att['rejection_reason'] : '',
                                                                        ))); ?>)" title="Xem log duyệt">
                                            <i class="fa fa-history text-info"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                                <?php if ($canEdit): ?>
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
$detailsByContent = array(
    'sports' => array(),
    'competition' => array(),
    'miss' => array(),
    'talent' => array(),
);
foreach ($registrationDetails as $detail) {
    $code = isset($detail['content_code']) ? $detail['content_code'] : 'other';
    // Normalize: API có thể trả về "competitions" hoặc "competition", "sports" hoặc "sport"
    if ($code === 'competitions') $code = 'competition';
    if ($code === 'sport') $code = 'sports';
    if (isset($detailsByContent[$code])) {
        $detailsByContent[$code][] = $detail;
    }
}

// Check allowed contents - nếu không có cấu hình thì cho phép tất cả
$allowedContents = isset($allowedContentCodes) ? $allowedContentCodes : array();
$showAllContents = empty($allowedContents);
$canShowSports = $showAllContents || in_array('sports', $allowedContents);
$canShowCompetition = $showAllContents || in_array('competition', $allowedContents);
$canShowTalent = $showAllContents || in_array('talent', $allowedContents);
$canShowMiss = $showAllContents || in_array('miss', $allowedContents);
?>
<div class="row">
    <div class="col-md-12">
        <!-- 1. ĐĂNG KÝ THI ĐẤU THỂ THAO -->
        <?php if ($canShowSports): ?>
            <?php
            $sportsPendingCount = count($allianceByContent['sports']['pending']);
            $sportsHistoryCount = count($allianceByContent['sports']['history']);
            $sportsHasAlliance = ($sportsPendingCount > 0 || $sportsHistoryCount > 0);
            ?>
            <div class="card mb-3" id="sports-registration-card" data-event-content-id="<?php echo $contentIdMap['sports']; ?>">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fa fa-futbol-o me-2 text-primary"></i>Đăng ký thi đấu thể thao
                        <?php if ($sportsPendingCount > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-2"><?php echo $sportsPendingCount; ?> yêu cầu</span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if ($sportsHasAlliance): ?>
                            <div class="col-md-3 mb-3 mb-md-0">

                                <?php $this->renderPartial('_alliance_sidebar', array(
                                    'pendingRequests' => $allianceByContent['sports']['pending'],
                                    'historyItems' => $allianceByContent['sports']['history'],
                                    'contentCode' => 'sports',
                                    'allianceRequest' => $allianceRequest,
                                    'model' => $model,
                                )); ?>

                            </div>
                        <?php endif; ?>
                        <div class="<?php echo $sportsHasAlliance ? 'col-md-9' : 'col-12'; ?>">

                            <?php if ($canEdit): ?>
                                <!-- Form chọn liên quân và môn thể thao -->
                                <div class="row mb-3 g-3 align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label mb-1">Đơn vị liên quân</label>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary bg-white" data-bs-toggle="modal" data-bs-target="#alliancePropertyModal">
                                                <i class="fa fa-handshake-o me-1"></i>Thêm đơn vị liên quân
                                            </button>
                                            <div id="alliance_selected_texts" class="mt-2 small text-primary fw-bold"></div>
                                        </div>
                                        <select class="d-none" id="sport_alliance_property" multiple></select>
                                        <small class="text-muted mt-1 d-block">Áp dụng cho môn đội > 3 người. Để trống nếu không liên quân.</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label mb-1">Môn thể thao <span class="text-danger">*</span></label>
                                        <select class="form-select" id="sport_select_main">
                                            <option value="">-- Chọn môn thể thao --</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-sm btn-primary text-white" id="btn_open_sport_modal" disabled>
                                            <i class="fa fa-users me-1"></i>Chọn VĐV & Đăng ký
                                        </button>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Preview: Danh sách đang chọn (chưa lưu) -->
                            <div id="sport_preview_container" class="mb-3" style="display:none;">
                                <div class="alert alert-info py-2 mb-2">
                                    <i class="fa fa-info-circle me-1"></i>Danh sách đang chọn (chưa lưu vào hệ thống)
                                </div>
                                <div id="sport_preview_list"></div>
                                <div class="text-end mt-2">
                                    <button type="button" class="btn btn-sm btn-success" id="btn_save_all_sports">
                                        <i class="fa fa-save me-1"></i>Lưu tất cả đăng ký
                                    </button>
                                </div>
                            </div>

                            <?php
                            if (!empty($sportTeams)) {
                                // Sắp xếp $sportTeams theo tên bộ môn thi đấu
                                usort($sportTeams, function ($a, $b) {
                                    $nameA = isset($a->sport_name) ? $a->sport_name : (isset($a['sport_name']) ? $a['sport_name'] : '');
                                    $nameB = isset($b->sport_name) ? $b->sport_name : (isset($b['sport_name']) ? $b['sport_name'] : '');
                                    return strcmp(mb_strtolower($nameA, 'UTF-8'), mb_strtolower($nameB, 'UTF-8'));
                                });

                                // Tính toán số lượng dòng của mỗi bộ môn để gộp dòng (rowspan)
                                $sportCounts = array();
                                foreach ($sportTeams as $team) {
                                    $sportName = isset($team->sport_name) ? $team->sport_name : (isset($team['sport_name']) ? $team['sport_name'] : '');
                                    if (!isset($sportCounts[$sportName])) {
                                        $sportCounts[$sportName] = 0;
                                    }
                                    $sportCounts[$sportName]++;
                                }
                            }

                            $ownAttendeeIds = array();
                            if (!empty($attendees)) {
                                foreach ($attendees as $att) {
                                    if (isset($att['id'])) {
                                        $ownAttendeeIds[] = (int)$att['id'];
                                    }
                                }
                            }
                            ?>
                            <?php
                            // Nhóm theo từng đội (team), mỗi đội là 1 bảng riêng
                            $teamsData = array();
                            foreach ($sportTeams as $team) {
                                $teamId = isset($team->id) ? $team->id : (isset($team['id']) ? $team['id'] : null);
                                $sportName = isset($team->sport_name) ? $team->sport_name : (isset($team['sport_name']) ? $team['sport_name'] : '');
                                $teamName = isset($team->team_name) ? $team->team_name : (isset($team->name) ? $team->name : (isset($team['name']) ? $team['name'] : ''));
                                $teamPropertyId = isset($team->property_id) ? $team->property_id : (isset($team['property_id']) ? $team['property_id'] : null);
                                $members = ($teamId && isset($sportTeamMembers[$teamId])) ? $sportTeamMembers[$teamId] : array();

                                $allianceProperties = array();
                                $membersList = array();
                                $hasOwnMembers = false;
                                foreach ($members as $member) {
                                    $memberPropertyName = isset($member['property_name']) ? $member['property_name'] : '';
                                    $memberId = isset($member['id']) ? $member['id'] : null;
                                    $membersList[] = array(
                                        'id' => $memberId,
                                        'attendee_name' => isset($member['attendee_name']) ? $member['attendee_name'] : (isset($member['name']) ? $member['name'] : ''),
                                        'gender' => isset($member['gender']) ? $member['gender'] : '',
                                        'property_name' => $memberPropertyName,
                                    );
                                    if ($memberPropertyName === $model->property_name) {
                                        $hasOwnMembers = true;
                                    }
                                    if (!empty($memberPropertyName) && $memberPropertyName !== $model->property_name && !in_array($memberPropertyName, $allianceProperties)) {
                                        $allianceProperties[] = $memberPropertyName;
                                    }
                                }

                                $isTeamOwner = ($teamPropertyId == $model->property_id);
                                $teamsData[] = array(
                                    'team_id' => $teamId,
                                    'sport_name' => $sportName,
                                    'team_name' => $teamName,
                                    'members' => $membersList,
                                    'alliance_properties' => $allianceProperties,
                                    'is_alliance' => !empty($allianceProperties),
                                    'is_team_owner' => $isTeamOwner,
                                    'has_own_members' => $hasOwnMembers,
                                );
                            }
                            // Sắp xếp theo tên môn
                            usort($teamsData, function ($a, $b) {
                                return strcmp($a['sport_name'], $b['sport_name']);
                            });
                            ?>
                            <?php if (empty($teamsData)): ?>
                                <p class="text-muted mb-0" id="no_sport_msg">Chưa đăng ký môn thể thao nào.</p>
                            <?php else: ?>
                                <?php foreach ($teamsData as $teamData): ?>
                                    <div class="d-flex justify-content-between align-items-center mb-2 mt-3">
                                        <div>
                                            <h6 class="mb-0 d-inline">
                                                <i class="fa fa-trophy text-warning me-1"></i><?php echo CHtml::encode($teamData['sport_name']); ?>
                                                <span class="text-muted">-</span>
                                                <span class="badge bg-primary"><?php echo CHtml::encode($teamData['team_name']); ?></span>
                                                (<?php echo count($teamData['members']); ?> VĐV)
                                            </h6>
                                            <?php if ($teamData['is_alliance']): ?>
                                                <span class="badge bg-info ms-2"><i class="fa fa-handshake-o me-1"></i>Liên quân: <?php echo CHtml::encode(implode(', ', $teamData['alliance_properties'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($canEdit && ($teamData['is_team_owner'] || $teamData['has_own_members'])): ?>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="RegistrationView.editSportTeam(<?php echo $teamData['team_id']; ?>)" title="Sửa danh sách VĐV">
                                                    <i class="fa fa-pencil me-1"></i>Sửa
                                                </button>
                                                <?php if (!$teamData['is_alliance'] && $teamData['is_team_owner']): ?>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteTeam(<?php echo $teamData['team_id']; ?>)" title="Xóa đội">
                                                        <i class="fa fa-trash me-1"></i>Xóa
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!$teamData['is_alliance'] && $teamData['is_team_owner']): ?>
                                                <form method="post" action="<?php echo $this->createUrl('deleteSportTeam', array('id' => $teamData['team_id'], 'registration_id' => $model->id)); ?>" id="delete-team-form-<?php echo $teamData['team_id']; ?>" style="display:none;"></form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-sm mb-3">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:50px;" class="text-center">STT</th>
                                                    <th style="width:180px;">Họ tên</th>
                                                    <th style="width:80px;" class="text-center">Giới tính</th>
                                                    <th>Đơn vị</th>
                                                    <?php if ($canEdit): ?>
                                                        <th style="width:70px;" class="text-center">Thao tác</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($teamData['members'] as $idx => $member):
                                                    $memberId = isset($member['id']) ? $member['id'] : null;
                                                    $memberPropertyName = isset($member['property_name']) ? $member['property_name'] : '';
                                                    $memberGender = isset($member['gender']) ? strtolower($member['gender']) : '';
                                                    $isOwnMember = ($memberPropertyName === $model->property_name);
                                                ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $idx + 1; ?></td>
                                                        <td><?php echo CHtml::encode(isset($member['attendee_name']) ? $member['attendee_name'] : ''); ?></td>
                                                        <td class="text-center">
                                                            <?php
                                                            if ($memberGender === 'male' || $memberGender === 'nam') {
                                                                echo '<span class="badge bg-primary">Nam</span>';
                                                            } elseif ($memberGender === 'female' || $memberGender === 'nữ' || $memberGender === 'nu') {
                                                                echo '<span class="badge bg-danger">Nữ</span>';
                                                            } else {
                                                                echo '-';
                                                            }
                                                            ?>
                                                        </td>
                                                        <td><?php echo CHtml::encode($memberPropertyName ?: '-'); ?></td>
                                                        <?php if ($canEdit): ?>
                                                            <td class="text-center">
                                                                <?php if ($isOwnMember && $memberId): ?>
                                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteTeamMember(<?php echo $memberId; ?>, <?php echo $teamData['team_id']; ?>)" title="Xóa khỏi đội">
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div><!-- end main col -->
                    </div><!-- end row -->
                </div>
            </div>
        <?php endif; ?>

    </div>
    <div class="col-md-12">

        <!-- 2. ĐĂNG KÝ THI NGHIỆP VỤ -->
        <?php if ($canShowCompetition): ?>
            <?php
            $competitionPendingCount = count($allianceByContent['competition']['pending']);
            $competitionHistoryCount = count($allianceByContent['competition']['history']);
            $competitionHasAlliance = ($competitionPendingCount > 0 || $competitionHistoryCount > 0);
            ?>
            <div class="card mb-3" id="competition-registration-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fa fa-trophy me-2 text-primary"></i>Đăng ký thi nghiệp vụ
                        <?php if ($competitionPendingCount > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-2"><?php echo $competitionPendingCount; ?> yêu cầu</span>
                        <?php endif; ?>
                    </h5>
                    <?php if ($canEdit): ?>
                        <button type="button" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#addCompetitionModal" onclick="resetCompetitionModal()">
                            <i class="fa fa-plus me-1"></i>Đăng ký
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if ($competitionHasAlliance): ?>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <?php $this->renderPartial('_alliance_sidebar', array(
                                    'pendingRequests' => $allianceByContent['competition']['pending'],
                                    'historyItems' => $allianceByContent['competition']['history'],
                                    'contentCode' => 'competition',
                                    'model' => $model,
                                )); ?>
                            </div>
                        <?php endif; ?>
                        <div class="<?php echo $competitionHasAlliance ? 'col-md-9' : 'col-12'; ?>">

                            <?php if (empty($competitionRegistrations)): ?>
                                <p class="text-muted mb-0">Chưa đăng ký thi nghiệp vụ nào.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-sm mb-0 content-table" id="competition-list-table">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="col-stt text-center">STT</th>
                                                <th class="col-name">Cuộc thi</th>
                                                <th class="col-count text-center">Số người</th>
                                                <th class="col-list">Danh sách thí sinh</th>
                                                <?php if ($canEdit): ?>
                                                    <th class="col-action text-center">Thao tác</th>
                                                <?php endif; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $compIdx = 0;
                                            foreach ($competitionRegistrations as $compId => $compData): $compIdx++; ?>
                                                <tr data-competition-id="<?php echo $compId; ?>">
                                                    <td class="text-center"><?php echo $compIdx; ?></td>
                                                    <td><?php echo CHtml::encode($compData['competition_name']); ?></td>
                                                    <td class="text-center"><?php echo count($compData['attendees']); ?></td>
                                                    <td>
                                                        <?php foreach ($compData['attendees'] as $idx => $att):
                                                            $name = $att['attendee_name'];
                                                            $position = $att['position_name'];
                                                            $division = $att['division_name'];
                                                            $nameInfo = CHtml::encode($name);
                                                            $details = array();
                                                            if ($position) $details[] = CHtml::encode($position);
                                                            if ($division) $details[] = 'Bộ phận: ' . CHtml::encode($division);
                                                            if (!empty($details)) {
                                                                $nameInfo .= ' <small class="text-muted">(' . implode(' - ', $details) . ')</small>';
                                                            }
                                                        ?>
                                                            <div><?php echo ($idx + 1) . '. ' . $nameInfo; ?></div>
                                                        <?php endforeach; ?>
                                                    </td>
                                                    <?php if ($canEdit): ?>
                                                        <td class="text-center">
                                                            <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="RegistrationView.editCompetitionRegistration(<?php echo $compId; ?>, '<?php echo addslashes($compData['competition_name']); ?>')" title="Sửa">
                                                                <i class="fa fa-pencil"></i>
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="RegistrationView.deleteCompetitionRegistration(<?php echo $compId; ?>)" title="Xóa">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    <?php endif; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div><!-- end table-responsive -->
                            <?php endif; ?>
                        </div><!-- end main col -->
                    </div><!-- end row -->
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-3">

    <div class="col-md-12">

        <!-- 4. ĐĂNG KÝ VĂN NGHỆ -->
        <?php if ($canShowTalent): ?>
            <?php
            $talentPendingCount = count($allianceByContent['talent']['pending']);
            $talentHistoryCount = count($allianceByContent['talent']['history']);
            $talentHasAlliance = ($talentPendingCount > 0 || $talentHistoryCount > 0);
            ?>
            <div class="card mb-3" id="talent-registration-card" data-event-content-id="<?php echo $contentIdMap['talent']; ?>">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fa fa-music me-2 text-primary"></i>Đăng ký văn nghệ
                        <?php if ($talentPendingCount > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-2"><?php echo $talentPendingCount; ?> yêu cầu</span>
                        <?php endif; ?>
                    </h5>
                    <?php if ($canEdit && empty($talentEntries)): ?>
                        <button type="button" class="btn btn-sm btn-primary text-white" id="btn_open_talent_modal">
                            <i class="fa fa-plus me-1"></i>Đăng ký
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if ($talentHasAlliance): ?>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <?php $this->renderPartial('_alliance_sidebar', array(
                                    'pendingRequests' => $allianceByContent['talent']['pending'],
                                    'historyItems' => $allianceByContent['talent']['history'],
                                    'contentCode' => 'talent',
                                    'model' => $model,
                                )); ?>
                            </div>
                        <?php endif; ?>
                        <div class="<?php echo $talentHasAlliance ? 'col-md-9' : 'col-12'; ?>">
                            <?php if ($canEdit): ?>
                                <!-- Đơn vị liên quân -->
                                <div class="row mb-3 g-3 align-items-end">
                                    <div class="col-md-6">
                                        <label class="form-label mb-1">Đơn vị liên quân</label>
                                        <div>
                                            <button type="button" class="btn btn-sm btn-outline-primary bg-white" data-bs-toggle="modal" data-bs-target="#talentAlliancePropertyModal">
                                                <i class="fa fa-handshake-o me-1"></i>Thêm đơn vị liên quân
                                            </button>
                                            <div id="talent_alliance_selected_texts" class="mt-2 small text-primary fw-bold"></div>
                                        </div>
                                        <select class="d-none" id="talent_alliance_property" name="alliance_property_ids[]" multiple></select>
                                        <small class="text-muted mt-1 d-block">Chọn đơn vị cùng biểu diễn (nếu có)</small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div id="talent-entries-container">
                                <?php if (empty($talentEntries)): ?>
                                    <p class="text-muted mb-0 no-talent-message">Chưa đăng ký tiết mục văn nghệ nào.</p>
                                    <div class="table-responsive" style="display: none;">
                                        <table class="table table-bordered table-striped table-sm mb-0 content-table" id="talent-entries-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="col-stt text-center">STT</th>
                                                    <th class="col-name">Tiết mục</th>
                                                    <th class="col-count text-center">Thể loại</th>
                                                    <th class="col-list">Nguồn gốc/Xuất xứ</th>
                                                    <?php if ($canEdit): ?>
                                                        <th class="col-action text-center">Thao tác</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted mb-0 no-talent-message" style="display: none;">Chưa đăng ký tiết mục văn nghệ nào.</p>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-sm mb-0 content-table" id="talent-entries-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="col-stt text-center">STT</th>
                                                    <th class="col-name">Tiết mục</th>
                                                    <th class="col-count text-center">Thể loại</th>
                                                    <th class="col-list">Nguồn gốc/Xuất xứ</th>
                                                    <?php if ($canEdit): ?>
                                                        <th class="col-action text-center">Thao tác</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php $talentIdx = 0;
                                                foreach ($talentEntries as $entry): $talentIdx++;
                                                    $entryId = isset($entry->id) ? $entry->id : (isset($entry['id']) ? $entry['id'] : null);
                                                    $entryTitle = isset($entry->title) ? $entry->title : (isset($entry['title']) ? $entry['title'] : '-');
                                                    $categoryName = isset($entry->category_name) ? $entry->category_name : (isset($entry['category_name']) ? $entry['category_name'] : '-');
                                                    $entryOrigin = isset($entry->origin) ? $entry->origin : (isset($entry['origin']) ? $entry['origin'] : '');
                                                ?>
                                                    <tr id="talent-row-<?php echo $entryId; ?>">
                                                        <td class="text-center"><?php echo $talentIdx; ?></td>
                                                        <td class="talent-title"><?php echo CHtml::encode($entryTitle); ?></td>
                                                        <td class="text-center"><span class="badge bg-info talent-category"><?php echo CHtml::encode($categoryName); ?></span></td>
                                                        <td class="talent-origin"><?php echo CHtml::encode($entryOrigin); ?></td>
                                                        <?php if ($canEdit): ?>
                                                            <td class="text-center text-nowrap">
                                                                <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editTalentEntry(<?php echo $entryId; ?>)" title="Sửa">
                                                                    <i class="fa fa-pencil"></i>
                                                                </button>
                                                                <form method="post" action="<?php echo $this->createUrl('deleteTalentEntry', array('id' => $entryId, 'registration_id' => $model->id)); ?>" id="delete-talent-form-<?php echo $entryId; ?>" style="display:none;"></form>
                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteTalent(<?php echo $entryId; ?>)" title="Xóa">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div><!-- end main col -->
                    </div><!-- end row -->
                </div>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-md-12">
        <!-- 3. ĐĂNG KÝ THI SẮC ĐẸP (MISS) -->
        <?php if ($canShowMiss): ?>
            <?php
            $missPendingCount = count($allianceByContent['miss']['pending']);
            $missHistoryCount = count($allianceByContent['miss']['history']);
            $missHasAlliance = ($missPendingCount > 0 || $missHistoryCount > 0);
            ?>
            <div class="card mb-3" id="miss-registration-card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fa fa-star me-2 text-primary"></i>Đăng ký thi Miss Mường Thanh
                        <?php if ($missPendingCount > 0): ?>
                            <span class="badge bg-danger rounded-pill ms-2"><?php echo $missPendingCount; ?> yêu cầu</span>
                        <?php endif; ?>
                    </h5>
                    <?php if ($canEdit): ?>
                        <button type="button" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#addMissModal">
                            <i class="fa fa-plus me-1"></i>Đăng ký
                        </button>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if ($missHasAlliance): ?>
                            <div class="col-md-3 mb-3 mb-md-0">
                                <?php $this->renderPartial('_alliance_sidebar', array(
                                    'pendingRequests' => $allianceByContent['miss']['pending'],
                                    'historyItems' => $allianceByContent['miss']['history'],
                                    'contentCode' => 'miss',
                                    'model' => $model,
                                )); ?>
                            </div>
                        <?php endif; ?>
                        <div class="<?php echo $missHasAlliance ? 'col-md-9' : 'col-12'; ?>">

                            <?php if (empty($beautyContestants)): ?>
                                <p class="text-muted mb-0">Chưa có đăng ký</p>
                            <?php else: ?>
                                <?php foreach ($beautyContestants as $contestData): ?>
                                    <h6 class="mb-2"><i class="fa fa-trophy text-warning me-1"></i><?php echo CHtml::encode($contestData['contest_name']); ?> (<?php echo count($contestData['contestants']); ?> thí sinh)</h6>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-sm mb-3 content-table">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="col-stt text-center">STT</th>
                                                    <th class="col-name">Họ tên</th>
                                                    <th class="col-list">Email cá nhân</th>
                                                    <?php if ($canEdit): ?>
                                                        <th class="col-action text-center">Thao tác</th>
                                                    <?php endif; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($contestData['contestants'] as $idx => $c): ?>
                                                    <tr>
                                                        <td class="text-center"><?php echo $idx + 1; ?></td>
                                                        <td>
                                                            <?php
                                                            $nameInfo = CHtml::encode($c['attendee_name']);
                                                            $details = array();
                                                            if (!empty($c['position_name'])) $details[] = CHtml::encode($c['position_name']);
                                                            if (!empty($c['division_name'])) $details[] = 'Bộ phận: ' . CHtml::encode($c['division_name']);
                                                            if (!empty($details)) {
                                                                $nameInfo .= ' <small class="text-muted">(' . implode(' - ', $details) . ')</small>';
                                                            }
                                                            echo $nameInfo;
                                                            ?>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <input type="email" class="form-control form-control-sm contestant-personal-email"
                                                                    id="contestant-email-<?php echo $c['id']; ?>"
                                                                    data-contestant-id="<?php echo $c['id']; ?>"
                                                                    value="<?php echo CHtml::encode(isset($c['personal_email']) ? $c['personal_email'] : ''); ?>"
                                                                    placeholder="Nhập email cá nhân..."
                                                                    style="max-width: 250px;"
                                                                    <?php echo !$canEdit ? 'disabled' : ''; ?>>
                                                                <?php if ($canEdit): ?>
                                                                    <button type="button" class="btn btn-sm btn-outline-success btn-save-contestant-email"
                                                                        data-contestant-id="<?php echo $c['id']; ?>" title="Lưu email">
                                                                        <i class="fa fa-save"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                        <?php if ($canEdit): ?>
                                                            <td class="text-center">
                                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="RegistrationView.deleteMissContestant(<?php echo $c['id']; ?>)" title="Xóa">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            </td>
                                                        <?php endif; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div><!-- end table-responsive -->
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div><!-- end main col -->
                    </div><!-- end row -->
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>


<?php if ($canShowSports): ?>
    <?php $this->renderPartial('_modal_add_sport', array('model' => $model)); ?>
<?php endif; ?>
<?php if ($canShowCompetition): ?>
    <?php $this->renderPartial('_modal_add_competition', array('model' => $model)); ?>
    <?php $this->renderPartial('_modal_edit_competition', array('model' => $model)); ?>
<?php endif; ?>
<?php if ($canShowMiss): ?>
    <?php $this->renderPartial('_modal_add_miss', array('model' => $model)); ?>
    <?php $this->renderPartial('_modal_edit_miss', array('model' => $model)); ?>
<?php endif; ?>
<?php if ($canShowTalent): ?>
    <?php $this->renderPartial('_modal_add_talent', array('model' => $model)); ?>
    <?php $this->renderPartial('_modal_edit_talent'); ?>
<?php endif; ?>
<?php $this->renderPartial('_modal_document'); ?>
<?php $this->renderPartial('_modal_video'); ?>
<?php $this->renderPartial('_modal_reject', array('model' => $model)); ?>
<?php $this->renderPartial('_modal_add_attendee_staff', array('model' => $model, 'roles' => $roles, 'transports' => $transports)); ?>
<?php $this->renderPartial('_modal_edit_attendee', array('model' => $model, 'roles' => $roles, 'transports' => $transports)); ?>
<?php $this->renderPartial('_modal_add_attendee_manual', array('model' => $model, 'roles' => $roles, 'transports' => $transports)); ?>
<?php $this->renderPartial('_modal_import_attendees', array('model' => $model)); ?>
<?php $this->renderPartial('_modal_all_documents'); ?>

<!-- Modal Upload Document -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="uploadDocumentForm" method="post" action="<?php echo $this->createUrl('uploadDocument', array('id' => $model->id)); ?>" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-upload me-2"></i>Tải lên tệp đính kèm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn tệp đính kèm <span class="text-danger">*</span></label>
                        <input type="file" name="documents[]" id="upload_documents" multiple required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btn_upload_document">
                        <i class="fa fa-upload me-1"></i>Tải lên
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form xóa document -->
<form id="deleteDocumentForm" method="post" action="<?php echo $this->createUrl('deleteDocument', array('id' => $model->id)); ?>" style="display:none;">
    <input type="hidden" name="document_index" id="delete_document_index">
</form>

<!-- Modal Log duyệt nhân viên -->
<div class="modal fade" id="approvalLogModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-history me-2"></i>Log duyệt nhân viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th style="width:35%;background:#f8f9fa;">Họ tên</th>
                            <td id="log_attendee_name"></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Trạng thái duyệt</th>
                            <td id="log_approval_status"></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Người duyệt</th>
                            <td id="log_approved_by"></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Ngày duyệt</th>
                            <td id="log_approved_at"></td>
                        </tr>
                        <tr id="log_rejection_row" style="display:none;">
                            <th style="background:#f8f9fa;">Lý do từ chối</th>
                            <td id="log_rejection_reason" class="text-danger"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Chọn Đơn vị liên quân -->
<div class="modal fade" id="alliancePropertyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-handshake-o me-2"></i>Chọn đơn vị liên quân</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="alliance_modal_list" style="max-height: 300px; overflow-y: auto;">
                    <div class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" id="btn_confirm_alliance">Xác nhận</button>
            </div>
        </div>
    </div>
</div>

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
$existingSportTeams = array();
// Include sport IDs from existing sport teams
foreach ($sportTeams as $team) {
    $teamPropertyId = isset($team->property_id) ? $team->property_id : (isset($team['property_id']) ? $team['property_id'] : null);
    if ($teamPropertyId != $model->property_id) {
        continue;
    }
    $teamSportId = isset($team->sport_id) ? $team->sport_id : (isset($team['sport_id']) ? $team['sport_id'] : null);
    if ($teamSportId && !in_array((int)$teamSportId, $sportIds)) {
        $sportIds[] = (int)$teamSportId;
    }
    $teamId = isset($team->id) ? $team->id : (isset($team['id']) ? $team['id'] : null);
    $teamName = isset($team->team_name) ? $team->team_name : (isset($team->name) ? $team->name : (isset($team['name']) ? $team['name'] : ''));
    if ($teamSportId) {
        $existingSportTeams[] = array(
            'id' => $teamId,
            'sportId' => (int)$teamSportId,
            'teamName' => $teamName,
        );
    }
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
    'canEdit' => $canEdit,
    'existingSportTeams' => $existingSportTeams,
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
        if (typeof flatpickr === "undefined") {
            console.warn("flatpickr not loaded yet, retrying...");
            setTimeout(window.initDatePickers, 100);
            return;
        }
        document.querySelectorAll(".datepicker").forEach(function(el) {
            if (el._flatpickr || el.classList.contains("flatpickr-input")) return;
            flatpickr(el, {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                altInputClass: "form-control bg-white",
                allowInput: true,
                locale: Vietnamese
            });
        });
    };
', CClientScript::POS_END);

// Register DataTable
Yii::app()->clientScript->registerCssFile($baseUrl . '/assets/vendor/DataTables/datatables.min.css');
Yii::app()->clientScript->registerScriptFile($baseUrl . '/assets/vendor/DataTables/datatables.min.js', CClientScript::POS_END);

// Register init script
Yii::app()->clientScript->registerScript('registrations-view-init', '
    window.BASE_URL = "' . Yii::app()->createUrl('/') . '";
    document.addEventListener("DOMContentLoaded", function() {
        RegistrationView.init(' . CJSON::encode($jsConfig) . ');
        window.initDatePickers();
        initAttendeesDataTable();
    });
    function viewDocument(url, type) { RegistrationView.viewDocument(url, type); }
    function confirmDeleteDetail(id) { RegistrationView.confirmDeleteDetail(id); }
    function resetSportModal() { RegistrationView.resetSportModal(); }
    function resetCompetitionModal() { RegistrationView.resetCompetitionModal(); }
    function editAttendee(id) { RegistrationView.editAttendee(id); }
    function confirmDeleteAttendee(id) { RegistrationView.confirmDeleteAttendee(id); }
    function removeAllianceProperty(id) { RegistrationView.removeAllianceProperty(id); }
    function confirmDeleteTeam(id) { RegistrationView.confirmDeleteTeam(id); }
    function showApprovalLog(data) {
        document.getElementById("log_attendee_name").textContent = data.name || "-";
        var statusLabels = {0: "Chờ duyệt", 1: "Đã duyệt", 2: "Từ chối"};
        var statusClasses = {0: "bg-warning text-dark", 1: "bg-success", 2: "bg-danger"};
        var status = parseInt(data.status) || 0;
        document.getElementById("log_approval_status").innerHTML = "<span class=\"badge " + statusClasses[status] + "\">" + statusLabels[status] + "</span>";
        document.getElementById("log_approved_by").textContent = data.approved_by || "-";
        var approvedAt = "-";
        if (data.approved_at) {
            var d = new Date(data.approved_at * 1000);
            approvedAt = ("0" + d.getDate()).slice(-2) + "/" + ("0" + (d.getMonth()+1)).slice(-2) + "/" + d.getFullYear() + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
        }
        document.getElementById("log_approved_at").textContent = approvedAt;
        var rejectionRow = document.getElementById("log_rejection_row");
        if (status === 2 && data.rejection_reason) {
            rejectionRow.style.display = "";
            document.getElementById("log_rejection_reason").textContent = data.rejection_reason;
        } else {
            rejectionRow.style.display = "none";
        }
        var modal = new bootstrap.Modal(document.getElementById("approvalLogModal"));
        modal.show();
    }
    function confirmApproveAlliance(id) {
        Swal.fire({
            title: "Xác nhận liên quân",
            text: "Bạn có chắc chắn muốn chấp nhận yêu cầu liên quân này?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#198754",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Chấp nhận",
            cancelButtonText: "Hủy"
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById("approve-alliance-form-" + id).submit();
            }
        });
    }
    function confirmRejectAlliance(id) {
        Swal.fire({
            title: "Từ chối liên quân",
            text: "Vui lòng nhập lý do từ chối yêu cầu liên quân:",
            input: "text",
            inputPlaceholder: "Nhập lý do từ chối...",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Từ chối",
            cancelButtonText: "Hủy",
            inputValidator: (value) => {
                if (!value) {
                    return "Bạn cần nhập lý do từ chối!";
                }
            }
        }).then(function(result) {
            if (result.isConfirmed && result.value) {
                document.getElementById("rejection_reason_" + id).value = result.value;
                document.getElementById("reject-alliance-form-" + id).submit();
            }
        });
    }
    function confirmDeleteTalent(id) {
        Swal.fire({
            title: "Xác nhận xóa",
            text: "Bạn có chắc muốn xóa tiết mục này?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Xóa",
            cancelButtonText: "Hủy"
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById("delete-talent-form-" + id).submit();
            }
        });
    }
    function viewTalentVideo(url, title) {
        var container = document.getElementById("videoContainer");
        var downloadLink = document.getElementById("videoDownloadLink");
        document.getElementById("videoModalTitle").textContent = title || "Video tiết mục";

        // Check if YouTube link
        var youtubeMatch = url.match(/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
        if (youtubeMatch) {
            container.innerHTML = "<iframe src=\"https://www.youtube.com/embed/" + youtubeMatch[1] + "?autoplay=1\" allowfullscreen allow=\"autoplay\" class=\"w-100 h-100\" style=\"border:none;\"></iframe>";
            downloadLink.href = "https://www.youtube.com/watch?v=" + youtubeMatch[1];
            downloadLink.style.display = "inline-block";
        } else if (url.match(/\.(mp4|webm|ogg|mov)$/i)) {
            container.innerHTML = "<video controls autoplay class=\"w-100 h-100\"><source src=\"" + url + "\" type=\"video/mp4\">Trình duyệt không hỗ trợ video.</video>";
            downloadLink.href = url;
            downloadLink.style.display = "inline-block";
        } else {
            container.innerHTML = "<div class=\"d-flex align-items-center justify-content-center h-100 bg-light\"><div class=\"text-center\"><i class=\"fa fa-external-link fa-3x text-muted mb-3\"></i><p>Link video bên ngoài</p><a href=\"" + url + "\" target=\"_blank\" class=\"btn btn-primary\"><i class=\"fa fa-play-circle me-1\"></i>Mở video</a></div></div>";
            downloadLink.href = url;
            downloadLink.style.display = "inline-block";
        }
        var modal = new bootstrap.Modal(document.getElementById("videoModal"));
        modal.show();
    }
    function stopVideo() {
        var container = document.getElementById("videoContainer");
        container.innerHTML = "";
    }
    var currentEditingTalentId = null;
    function editTalentEntry(id) {
        currentEditingTalentId = id;
        fetch(window.BASE_URL + "/admin/registrations/getTalentEntry?id=" + id, {
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
            .then(function(res) {
                if (!res.ok) {
                    throw new Error("HTTP " + res.status);
                }
                return res.json();
            })
            .then(function(data) {
                if (data.success && data.data) {
                    var entry = data.data;
                    var setVal = function(id, val) {
                        var el = document.getElementById(id);
                        if (el) el.value = val || "";
                    };
                    setVal("edit_talent_title", entry.title);
                    setVal("edit_talent_category", entry.category_id);
                    setVal("edit_talent_duration", entry.duration_seconds);
                    setVal("edit_talent_director", entry.director);
                    setVal("edit_talent_director_phone", entry.director_phone);
                    setVal("edit_talent_description", entry.description);
                    setVal("edit_talent_content", entry.content);
                    setVal("edit_talent_origin", entry.origin);
                    setVal("edit_talent_participant_count", entry.participant_count);
                    setVal("edit_talent_music_path", entry.music_path);
                    setVal("edit_talent_video_path", entry.video_path);
                    setVal("edit_talent_note", entry.note);

                    var selectedMemberIds = [];
                    if (data.members && Array.isArray(data.members)) {
                        selectedMemberIds = data.members.map(function(m) { return m.attendee_id; });
                    }
                    RegistrationView.loadAttendeesForEditTalent(selectedMemberIds);

                    var modal = new bootstrap.Modal(document.getElementById("editTalentModal"));
                    modal.show();
                } else {
                    Toast.error(data.message || "Không thể tải thông tin tiết mục");
                }
            })
            .catch(function(err) {
                console.error("Get talent error:", err);
                Toast.error("Lỗi kết nối đến server");
            });
    }
    function previewEditVideo() {
        var url = document.getElementById("edit_talent_video_path").value;
        if (url) {
            viewTalentVideo(url, "Xem trước video");
        }
    }
    document.getElementById("editTalentForm").addEventListener("submit", function(e) {
        e.preventDefault();
        var form = this;
        var btn = document.getElementById("btn_submit_edit_talent");
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = "<i class=\"fa fa-spinner fa-spin me-1\"></i>Đang lưu...";

        var formData = new FormData(form);
        fetch(window.BASE_URL + "/admin/registrations/updateTalentEntry?id=" + currentEditingTalentId, {
            method: "POST",
            body: formData,
            headers: { "X-Requested-With": "XMLHttpRequest" }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                Toast.success("Cập nhật tiết mục thành công");
                var modalEl = document.getElementById("editTalentModal");
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                // Cập nhật dòng tương ứng trong bảng trực tiếp
                var row = document.getElementById("talent-row-" + currentEditingTalentId);
                if (row) {
                    var titleVal = document.getElementById("edit_talent_title").value;
                    var catSelect = document.getElementById("edit_talent_category");
                    var categoryName = catSelect.options[catSelect.selectedIndex].text;
                    var originVal = document.getElementById("edit_talent_origin").value;

                    row.querySelector(".talent-title").textContent = titleVal;
                    row.querySelector(".talent-category").textContent = categoryName;
                    row.querySelector(".talent-origin").textContent = originVal;
                }
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            } else {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                Toast.error(data.message || "Cập nhật thất bại");
            }
        })
        .catch(function(err) {
            console.error("Edit talent error:", err);
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            Toast.error("Lỗi kết nối đến server");
        });
    });
    function confirmResubmitRegistration() {
        var form = document.getElementById("form-resubmit-registration");
        if (!form) return;

        Swal.fire({
            title: "Gửi lại phiếu đăng ký?",
            text: "Phiếu sẽ được gửi lại cho HO xem xét phê duyệt.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#e6a817",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Gửi lại",
            cancelButtonText: "Hủy"
        }).then(function(result) {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }

    function confirmSubmitRegistration() {
        var form = document.getElementById("form-submit-registration");
        if (!form) return;

        // Hiển thị loading khi check
        Swal.fire({
            title: "Đang kiểm tra thông tin...",
            text: "Vui lòng chờ trong giây lát.",
            allowOutsideClick: false,
            didOpen: function() {
                Swal.showLoading();
            }
        });

        // Hàm helper tránh XSS
        var escapeHtml = function(text) {
            if (!text) return "";
            var map = {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                "\"": "&quot;",
                "\'": "&#039;"
            };
            return text.replace(/[&<>"\']/g, function(m) { return map[m]; });
        };

        fetch((window.BASE_URL || "") + "/admin/registrations/checkSubmitValid?id=' . $model->id . '")
            .then(function(response) { return response.json(); })
            .then(function(data) {
                Swal.close();

                if (!data.success) {
                    var errorHtml = "<div class=\"text-start\" style=\"max-height: 400px; overflow-y: auto;\">";
                    errorHtml += "<p class=\"text-danger fw-bold mb-2\">Phiếu đăng ký chưa đủ điều kiện gửi đăng ký:</p>";
                    errorHtml += "<ul class=\"ps-3 mb-0\">";
                    data.errors.forEach(function(err) {
                        errorHtml += "<li class=\"mb-1 text-muted\" style=\"list-style-type: disc;\"><small>" + escapeHtml(err) + "</small></li>";
                    });
                    errorHtml += "</ul></div>";

                    Swal.fire({
                        title: "Chưa đủ điều kiện",
                        html: errorHtml,
                        icon: "warning",
                        confirmButtonText: "Đóng",
                        confirmButtonColor: "#6c757d",
                        customClass: {
                            htmlContainer: "px-3"
                        }
                    });
                    return;
                }

                // Nếu hợp lệ, tiến hành xác nhận nộp
                Swal.fire({
                    title: "Xác nhận gửi bản đăng ký",
                    text: "Bạn có chắc muốn gửi phiếu đăng ký này? Những người tham dự bị từ chối trước đó sẽ được chuyển về trạng thái chờ duyệt.",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonColor: "#17a2b8",
                    cancelButtonColor: "#6c757d",
                    confirmButtonText: "Gửi bản đăng ký",
                    cancelButtonText: "Hủy"
                }).then(function(result) {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "Đang xử lý...",
                            text: "Vui lòng chờ trong giây lát.",
                            allowOutsideClick: false,
                            didOpen: function() {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            })
            .catch(function() {
                Swal.close();
                Swal.fire({
                    title: "Lỗi",
                    text: "Lỗi kết nối server khi kiểm tra thông tin.",
                    icon: "error",
                    confirmButtonText: "Đóng",
                    confirmButtonColor: "#6c757d"
                });
            });
    }

    function confirmDeleteDocument(index) {
        Swal.fire({
            title: "Xác nhận xóa",
            text: "Bạn có chắc muốn xóa tệp đính kèm này?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Xóa",
            cancelButtonText: "Hủy"
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Đang xử lý...",
                    text: "Vui lòng chờ trong giây lát.",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                document.getElementById("delete_document_index").value = index;
                document.getElementById("deleteDocumentForm").submit();
            }
        });
    }

    function initAttendeesDataTable() {
        if (typeof $.fn.DataTable === "undefined") return;
        var table = $("#attendees-table").DataTable({
            paging: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Tất cả"]],
            ordering: true,
            searching: true,
            dom: "lrtip",
            language: {
                lengthMenu: "Hiển thị _MENU_ dòng",
                info: "Đang xem _START_ - _END_ / _TOTAL_ người",
                infoEmpty: "Không có dữ liệu",
                infoFiltered: "(lọc từ _MAX_ người)",
                paginate: { first: "Đầu", last: "Cuối", next: "Sau", previous: "Trước" },
                emptyTable: "Chưa có người tham dự nào."
            },
            columnDefs: [
                { orderable: false, targets: [1, -1] }
            ]
        });

        $("#filter_name").on("keyup", function() {
            table.column(2).search(this.value).draw();
        });
        $("#filter_role").on("change", function() {
            table.column(4).search(this.value).draw();
        });
        $("#filter_status").on("change", function() {
            table.column(9).search(this.value).draw();
        });
        $("#filter_transport").on("change", function() {
            table.column(8).search(this.value).draw();
        });
        $("#btn_reset_filter").on("click", function() {
            $("#filter_name").val("");
            $("#filter_role").val("");
            $("#filter_status").val("");
            $("#filter_transport").val("");
            table.search("").columns().search("").draw();
        });
    }

    $(document).on("click", ".btn-save-contestant-email", function() {
        var btn = $(this);
        var contestantId = btn.data("contestant-id");
        var input = $("#contestant-email-" + contestantId);
        var email = input.val();
        var originalHtml = btn.html();

        btn.prop("disabled", true).html("<i class=\"fa fa-spinner fa-spin\"></i>");
        input.removeClass("border-success border-danger");

        $.ajax({
            url: window.BASE_URL + "/admin/registrations/updateContestantEmail",
            type: "POST",
            data: {
                contestant_id: contestantId,
                personal_email: email
            },
            success: function(res) {
                btn.prop("disabled", false).html(originalHtml);
                if (res.success) {
                    input.addClass("border-success");
                    Toast.success("Cập nhật email cá nhân thành công");
                    setTimeout(function() {
                        input.removeClass("border-success");
                    }, 2000);
                } else {
                    input.addClass("border-danger");
                    Toast.error(res.error || "Không thể cập nhật email");
                }
            },
            error: function() {
                btn.prop("disabled", false).html(originalHtml);
                input.addClass("border-danger");
                Toast.error("Lỗi kết nối máy chủ");
            }
        });
    });
', CClientScript::POS_END);
?>