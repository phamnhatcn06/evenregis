<?php
$canEdit = in_array($model->status, array(Registrations::STATUS_DRAFT, Registrations::STATUS_REJECTED));

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
    Yii::t('app', 'View') . ($pendingRequestCount > 0 ? ' <span class="badge bg-danger rounded-pill">' . $pendingRequestCount . '</span>' : ''),
);

$this->Tabletitle = 'Chi tiết phiếu đăng ký của ' . $model->property_name . ($pendingRequestCount > 0 ? ' <span class="badge bg-danger rounded-pill ms-2">' . $pendingRequestCount . ' yêu cầu chờ xử lý</span>' : '');
?>

<?php
// Helper function to resolve content code from request
function resolveContentCode($req) {
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
    0% { border-left-color: #ffc107; }
    50% { border-left-color: #ff9800; }
    100% { border-left-color: #ffc107; }
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
</style>

<div class="row mb-3">
    <!-- Thông tin chung -->
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin chung</h5>
                <div class="btn-group">
                    <?php if ($canEdit): ?>
                        <form id="form-submit-registration" method="post" action="<?php echo $this->createUrl('submit', array('id' => $model->id)); ?>" style="display:inline;">
                            <button type="button" class="btn btn-sm btn-info" onclick="confirmSubmitRegistration()">
                                <i class="fa fa-paper-plane me-1"></i>Nộp
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
                <?php else: ?>
                    <button type="button" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#addAttendeeManualModal">
                        <i class="fa fa-user-plus me-1"></i>Thêm người tham dự
                    </button>
                <?php endif; ?>
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
                                            <span class="badge <?php echo Attendees::getRoleBadgeClass($role); ?> me-1 mb-1"><?php echo CHtml::encode($role); ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $startDate ? date('d/m/Y', strtotime($startDate)) : '-'; ?></td>
                                <td><?php echo $checkInDate ? date('d/m/Y', strtotime($checkInDate)) : '-'; ?></td>
                                <td><?php echo $checkOutDate ? date('d/m/Y', strtotime($checkOutDate)) : '-'; ?></td>
                                <td><?php echo CHtml::encode($transportName ?: '-'); ?></td>
                                <td><?php echo Attendees::getApprovalStatusLabel($approvalStatus); ?></td>
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
?>

<!-- 1. ĐĂNG KÝ THI ĐẤU THỂ THAO -->
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
                    'model' => $model,
                )); ?>
            </div>
            <?php endif; ?>
            <div class="<?php echo $sportsHasAlliance ? 'col-md-9' : 'col-12'; ?>">
        <?php if (isset($allianceRequest) && $allianceRequest && $allianceRequest->status == AllianceRequests::STATUS_APPROVED && !empty($model->relation_property_name)): ?>
            <div class="alert alert-success d-flex align-items-center py-2 px-3 mb-3 border-start border-4 border-success">
                <i class="fa fa-handshake-o me-2 fa-lg text-success"></i>
                <div>
                    Đơn vị đang liên quân với: <strong><?php echo CHtml::encode($model->relation_property_name); ?></strong>. 
                    Hệ thống đang hiển thị và chia sẻ danh sách các đội thi đấu thể thao của cả hai đơn vị.
                </div>
            </div>
        <?php endif; ?>
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

        <!-- Danh sách đã đăng ký -->
        <?php if (empty($sportTeams)): ?>
            <p class="text-muted mb-0" id="no_sport_msg">Chưa đăng ký môn thể thao nào.</p>
        <?php else: ?>
            <table class="table table-bordered table-striped table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Môn thi đấu</th>
                        <th>Tên đội</th>
                        <th style="width:100px;">Số VĐV</th>
                        <th>Danh sách VĐV</th>
                        <?php if ($canEdit): ?>
                            <th style="width:80px;"></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sportTeams as $team):
                        $teamId = isset($team->id) ? $team->id : (isset($team['id']) ? $team['id'] : null);
                        $sportName = isset($team->sport_name) ? $team->sport_name : (isset($team['sport_name']) ? $team['sport_name'] : '');
                        $teamName = isset($team->team_name) ? $team->team_name : (isset($team->name) ? $team->name : (isset($team['name']) ? $team['name'] : ''));
                        $members = ($teamId && isset($sportTeamMembers[$teamId])) ? $sportTeamMembers[$teamId] : array();
                        $teamPropertyId = isset($team->property_id) ? $team->property_id : (isset($team['property_id']) ? $team['property_id'] : null);
                    ?>
                        <tr>
                            <td><?php echo CHtml::encode($sportName); ?></td>
                            <td><span class="badge bg-primary"><?php echo CHtml::encode($teamName); ?></span></td>
                            <td class="text-center"><?php echo count($members); ?></td>
                            <td>
                                <?php foreach ($members as $idx => $member):
                                    $memberName = isset($member['attendee_name']) ? $member['attendee_name'] : (isset($member['name']) ? $member['name'] : '');
                                    $memberPosition = isset($member['position_name']) ? $member['position_name'] : '';
                                    $memberDivision = isset($member['division_name']) ? $member['division_name'] : '';
                                    $memberProperty = isset($member['property_name']) ? $member['property_name'] : '';
                                    $nameInfo = CHtml::encode($memberName);
                                    $details = array();
                                    if ($memberPosition) $details[] = CHtml::encode($memberPosition);
                                    if ($memberDivision) $details[] = 'Bộ phận: ' . CHtml::encode($memberDivision);
                                    if ($memberProperty) $details[] = 'Đơn vị: ' . CHtml::encode($memberProperty);
                                    if (!empty($details)) {
                                        $nameInfo .= ' <small class="text-muted">(' . implode(' - ', $details) . ')</small>';
                                    }
                                ?>
                                    <div><?php echo ($idx + 1) . '. ' . $nameInfo; ?></div>
                                <?php endforeach; ?>
                            </td>
                            <?php if ($canEdit): ?>
                                <td class="text-center text-nowrap">
                                    <?php if ($teamPropertyId == $model->property_id): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="RegistrationView.editSportTeam(<?php echo $teamId; ?>)" title="Sửa">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <form method="post" action="<?php echo $this->createUrl('deleteSportTeam', array('id' => $teamId, 'registration_id' => $model->id)); ?>" id="delete-team-form-<?php echo $teamId; ?>" style="display:none;"></form>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteTeam(<?php echo $teamId; ?>)" title="Xóa">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Liên quân</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
            </div><!-- end main col -->
        </div><!-- end row -->
    </div>
</div>

<!-- 2. ĐĂNG KÝ THI NGHIỆP VỤ -->
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
            <table class="table table-bordered table-striped table-sm mb-0" id="competition-list-table">
                <thead class="table-light">
                    <tr>
                        <th>Cuộc thi</th>
                        <th style="width:100px;">Số người</th>
                        <th>Danh sách thí sinh</th>
                        <?php if ($canEdit): ?>
                            <th style="width:100px;">Thao tác</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($competitionRegistrations as $compId => $compData): ?>
                        <tr data-competition-id="<?php echo $compId; ?>">
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
        <?php endif; ?>
            </div><!-- end main col -->
        </div><!-- end row -->
    </div>
</div>

<!-- 3. ĐĂNG KÝ THI SẮC ĐẸP (MISS) -->
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
                <table class="table table-bordered table-striped table-sm mb-3">
                    <thead class="table-light">
                        <tr>
                            <th style="width:80px;">SBD</th>
                            <th>Họ tên</th>
                            <th style="width:80px;">Cao (cm)</th>
                            <th style="width:80px;">Nặng (kg)</th>
                            <th style="width:100px;">Số đo</th>
                            <?php if ($canEdit): ?>
                                <th style="width:60px;"></th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contestData['contestants'] as $c): ?>
                            <tr>
                                <td><span class="badge bg-primary"><?php echo CHtml::encode($c['candidate_number']); ?></span></td>
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
                                <td class="text-center"><?php echo isset($c['height_cm']) && $c['height_cm'] ? $c['height_cm'] : '-'; ?></td>
                                <td class="text-center"><?php echo isset($c['weight_kg']) && $c['weight_kg'] ? $c['weight_kg'] : '-'; ?></td>
                                <td class="text-center"><?php echo isset($c['measurements']) && $c['measurements'] ? CHtml::encode($c['measurements']) : '-'; ?></td>
                                <?php if ($canEdit): ?>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="RegistrationView.editMissContestant(<?php echo $c['id']; ?>)" title="Sửa">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="RegistrationView.deleteMissContestant(<?php echo $c['id']; ?>)" title="Xóa">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endforeach; ?>
        <?php endif; ?>
            </div><!-- end main col -->
        </div><!-- end row -->
    </div>
</div>

<!-- 4. ĐĂNG KÝ VĂN NGHỆ -->
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
        <?php if ($canEdit && empty($talentEntries)): ?>
            <!-- Form chọn liên quân và thể loại -->
            <div class="row mb-3 g-3 align-items-end">
                <div class="col-md-5">
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
                <div class="col-md-4">
                    <label class="form-label mb-1">Thể loại <span class="text-danger">*</span></label>
                    <select class="form-select" id="talent_category_select_main">
                        <option value="">-- Chọn thể loại --</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-sm btn-primary text-white" id="btn_open_talent_modal" disabled>
                        <i class="fa fa-users me-1"></i>Chọn người & Đăng ký
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($talentEntries)): ?>
            <p class="text-muted mb-0">Chưa đăng ký tiết mục văn nghệ nào.</p>
        <?php else: ?>
            <table class="table table-bordered table-striped table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tiết mục</th>
                        <th style="width:120px;">Thể loại</th>
                        <th style="width:80px;">Thời lượng</th>
                        <th>Mô tả</th>
                        <th style="width:80px;">Số người</th>
                        <th>Danh sách</th>
                        <th style="width:80px;">Video</th>
                        <?php if ($canEdit): ?>
                            <th style="width:60px;"></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($talentEntries as $entry):
                        $entryId = isset($entry->id) ? $entry->id : (isset($entry['id']) ? $entry['id'] : null);
                        $entryTitle = isset($entry->title) ? $entry->title : (isset($entry['title']) ? $entry['title'] : '-');
                        $categoryName = isset($entry->category_name) ? $entry->category_name : (isset($entry['category_name']) ? $entry['category_name'] : '-');
                        $description = isset($entry->description) ? $entry->description : (isset($entry['description']) ? $entry['description'] : '');
                        $durationSeconds = isset($entry->duration_seconds) ? $entry->duration_seconds : (isset($entry['duration_seconds']) ? $entry['duration_seconds'] : 0);
                        $videoPath = isset($entry->video_path) ? $entry->video_path : (isset($entry['video_path']) ? $entry['video_path'] : '');
                        $musicPath = isset($entry->music_path) ? $entry->music_path : (isset($entry['music_path']) ? $entry['music_path'] : '');
                        $members = ($entryId && isset($talentEntryMembers[$entryId])) ? $talentEntryMembers[$entryId] : array();

                        // Format duration
                        $durationText = '-';
                        if ($durationSeconds > 0) {
                            $mins = floor($durationSeconds / 60);
                            $secs = $durationSeconds % 60;
                            $durationText = $mins . ':' . str_pad($secs, 2, '0', STR_PAD_LEFT);
                        }
                    ?>
                        <tr>
                            <td><?php echo CHtml::encode($entryTitle); ?></td>
                            <td><span class="badge bg-info"><?php echo CHtml::encode($categoryName); ?></span></td>
                            <td class="text-center"><?php echo $durationText; ?></td>
                            <td>
                                <?php if ($description): ?>
                                    <span class="text-muted" title="<?php echo CHtml::encode($description); ?>">
                                        <?php echo CHtml::encode(mb_substr($description, 0, 50) . (mb_strlen($description) > 50 ? '...' : '')); ?>
                                    </span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?php echo count($members); ?></td>
                            <td>
                                <?php foreach ($members as $idx => $member):
                                    $name = isset($member['attendee_name']) ? $member['attendee_name'] : '';
                                    $pos = isset($member['position_name']) ? $member['position_name'] : '';
                                    $div = isset($member['division_name']) ? $member['division_name'] : '';
                                    $nameInfo = CHtml::encode($name);
                                    $details = array();
                                    if ($pos) $details[] = CHtml::encode($pos);
                                    if ($div) $details[] = 'Bộ phận: ' . CHtml::encode($div);
                                    if (!empty($details)) {
                                        $nameInfo .= ' <small class="text-muted">(' . implode(' - ', $details) . ')</small>';
                                    }
                                ?>
                                    <div class="mb-1"><?php echo ($idx + 1) . '. ' . $nameInfo; ?></div>
                                <?php endforeach; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($videoPath): ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="viewTalentVideo('<?php echo CHtml::encode(addslashes($videoPath)); ?>', '<?php echo CHtml::encode(addslashes($entryTitle)); ?>')" title="Xem video">
                                        <i class="fa fa-play-circle"></i>
                                    </button>
                                <?php elseif ($musicPath): ?>
                                    <a href="<?php echo CHtml::encode($musicPath); ?>" target="_blank" class="btn btn-sm btn-outline-secondary" title="Nghe nhạc">
                                        <i class="fa fa-music"></i>
                                    </a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
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
        <?php endif; ?>
    </div>
</div>

<?php $this->renderPartial('_modal_add_sport', array('model' => $model)); ?>
<?php $this->renderPartial('_modal_add_competition', array('model' => $model)); ?>
<?php $this->renderPartial('_modal_edit_competition', array('model' => $model)); ?>
<?php $this->renderPartial('_modal_add_miss', array('model' => $model)); ?>
<?php $this->renderPartial('_modal_edit_miss', array('model' => $model)); ?>
<?php $this->renderPartial('_modal_add_talent', array('model' => $model)); ?>
<?php $this->renderPartial('_modal_edit_talent'); ?>
<?php $this->renderPartial('_modal_document'); ?>
<?php $this->renderPartial('_modal_video'); ?>
<?php $this->renderPartial('_modal_reject', array('model' => $model)); ?>
<?php $this->renderPartial('_modal_add_attendee_staff', array('model' => $model, 'roles' => $roles, 'transports' => $transports)); ?>
<?php $this->renderPartial('_modal_edit_attendee', array('model' => $model, 'roles' => $roles, 'transports' => $transports)); ?>
<?php $this->renderPartial('_modal_add_attendee_manual', array('model' => $model, 'roles' => $roles, 'transports' => $transports)); ?>
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
                        <label class="form-label">Chọn tệp <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" name="documents[]" id="upload_documents" multiple required>
                        <small class="text-muted">Hỗ trợ: JPG, PNG, PDF, DOC, DOCX, XLS, XLSX (tối đa 10MB/tệp)</small>
                    </div>
                    <div id="upload_preview" class="row g-2"></div>
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
    function editTalentEntry(id) {
        fetch("/admin/registrations/getTalentEntry?id=" + id)
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    var entry = data.data;
                    document.getElementById("edit_talent_id").value = entry.id;
                    document.getElementById("edit_talent_title").value = entry.title || "";
                    document.getElementById("edit_talent_category").value = entry.category_id || "";
                    document.getElementById("edit_talent_duration").value = entry.duration_seconds || "";
                    document.getElementById("edit_talent_director").value = entry.director || "";
                    document.getElementById("edit_talent_director_phone").value = entry.director_phone || "";
                    document.getElementById("edit_talent_description").value = entry.description || "";
                    document.getElementById("edit_talent_content").value = entry.content || "";
                    document.getElementById("edit_talent_origin").value = entry.origin || "";
                    document.getElementById("edit_talent_participant_count").value = entry.participant_count || "";
                    document.getElementById("edit_talent_music_path").value = entry.music_path || "";
                    document.getElementById("edit_talent_video_path").value = entry.video_path || "";
                    document.getElementById("edit_talent_note").value = entry.note || "";

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
            .catch(function() {
                Toast.error("Lỗi kết nối server");
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
        fetch("/admin/registrations/updateTalentEntry", {
            method: "POST",
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                Toast.success("Cập nhật tiết mục thành công");
                bootstrap.Modal.getInstance(document.getElementById("editTalentModal")).hide();
                setTimeout(function() { location.reload(); }, 1000);
            } else {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                Toast.error(data.message || "Cập nhật thất bại");
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            Toast.error("Lỗi kết nối server");
        });
    });
    function confirmSubmitRegistration() {
        Swal.fire({
            title: "Xác nhận nộp phiếu",
            text: "Bạn có chắc muốn nộp phiếu đăng ký này? Những người tham dự bị từ chối trước đó sẽ được chuyển về trạng thái chờ duyệt.",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#17a2b8",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Nộp",
            cancelButtonText: "Hủy"
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById("form-submit-registration").submit();
            }
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
                document.getElementById("delete_document_index").value = index;
                document.getElementById("deleteDocumentForm").submit();
            }
        });
    }

    document.getElementById("upload_documents").addEventListener("change", function(e) {
        var preview = document.getElementById("upload_preview");
        preview.innerHTML = "";
        var files = e.target.files;
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var col = document.createElement("div");
            col.className = "col-4";
            var isImage = file.type.startsWith("image/");
            if (isImage) {
                var reader = new FileReader();
                reader.onload = (function(col) {
                    return function(e) {
                        col.innerHTML = "<div class=\"border rounded p-1 text-center\"><img src=\"" + e.target.result + "\" class=\"img-fluid\" style=\"max-height:60px;\"></div>";
                    };
                })(col);
                reader.readAsDataURL(file);
            } else {
                col.innerHTML = "<div class=\"border rounded p-2 text-center\"><i class=\"fa fa-file-o\"></i><br><small class=\"text-truncate d-block\">" + file.name + "</small></div>";
            }
            preview.appendChild(col);
        }
    });

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
', CClientScript::POS_END);
?>