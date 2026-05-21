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
                                <td class="text-center" style="width: 50px;"><?php echo $idx + 1; ?></td>
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
<div class="card mb-3" id="sports-registration-card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fa fa-futbol-o me-2 text-primary"></i>Đăng ký thi đấu thể thao</h5>
    </div>
    <div class="card-body">
        <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
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
                        <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
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
                    ?>
                        <tr>
                            <td><?php echo CHtml::encode($sportName); ?></td>
                            <td><span class="badge bg-primary"><?php echo CHtml::encode($teamName); ?></span></td>
                            <td class="text-center"><?php echo count($members); ?></td>
                            <td>
                                <?php foreach ($members as $idx => $member):
                                    $memberName = isset($member->attendee_name) ? $member->attendee_name :
                                        (isset($member['attendee_name']) ? $member['attendee_name'] :
                                        (isset($member->name) ? $member->name :
                                        (isset($member['name']) ? $member['name'] : '')));
                                ?>
                                    <span class="badge bg-light text-dark border me-1 mb-1"><?php echo ($idx + 1) . '. ' . CHtml::encode($memberName); ?></span>
                                <?php endforeach; ?>
                            </td>
                            <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                                <td class="text-center text-nowrap">
                                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="RegistrationView.editSportTeam(<?php echo $teamId; ?>)" title="Sửa">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <form method="post" action="<?php echo $this->createUrl('deleteSportTeam', array('id' => $teamId, 'registration_id' => $model->id)); ?>" id="delete-team-form-<?php echo $teamId; ?>" style="display:none;"></form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteTeam(<?php echo $teamId; ?>)" title="Xóa">
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

<!-- 2. ĐĂNG KÝ THI NGHIỆP VỤ -->
<div class="card mb-3" id="competition-registration-card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fa fa-trophy me-2 text-primary"></i>Đăng ký thi nghiệp vụ</h5>
    </div>
    <div class="card-body">
        <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
        <div class="mb-3">
            <button type="button" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#addCompetitionModal" onclick="resetCompetitionModal()">
                <i class="fa fa-plus me-1"></i>Đăng ký thi nghiệp vụ
            </button>
        </div>
        <?php endif; ?>

        <?php if (empty($competitionRegistrations)): ?>
            <p class="text-muted mb-0">Chưa đăng ký thi nghiệp vụ nào.</p>
        <?php else: ?>
            <table class="table table-bordered table-striped table-sm mb-0" id="competition-list-table">
                <thead class="table-light">
                    <tr>
                        <th>Cuộc thi</th>
                        <th style="width:100px;">Số người</th>
                        <th>Danh sách thí sinh</th>
                        <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                            <th style="width:60px;"></th>
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
                                    $info = $name;
                                    if ($position || $division) {
                                        $info .= ' (' . trim($position . ' - ' . $division, ' -') . ')';
                                    }
                                ?>
                                    <span class="badge bg-light text-dark border me-1 mb-1">
                                        <?php echo ($idx + 1) . '. ' . CHtml::encode($info); ?>
                                    </span>
                                <?php endforeach; ?>
                            </td>
                            <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteCompetitionRegistration(<?php echo $compId; ?>)">
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

<!-- 3. ĐĂNG KÝ THI SẮC ĐẸP (MISS) -->
<div class="card mb-3" id="miss-registration-card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fa fa-star me-2 text-primary"></i>Đăng ký thi sắc đẹp</h5>
    </div>
    <div class="card-body">
        <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
        <div class="mb-3">
            <button type="button" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#addMissModal">
                <i class="fa fa-plus me-1"></i>Đăng ký thi sắc đẹp
            </button>
        </div>
        <?php endif; ?>

        <?php if (empty($detailsByContent['miss'])): ?>
            <p class="text-muted mb-0">Chưa đăng ký thi sắc đẹp nào.</p>
        <?php else: ?>
            <table class="table table-bordered table-striped table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nội dung thi</th>
                        <th style="width:100px;">Số người</th>
                        <th>Danh sách thí sinh</th>
                        <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                            <th style="width:60px;"></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detailsByContent['miss'] as $detail):
                        $detailId = isset($detail['id']) ? $detail['id'] : null;
                        $missAtts = ($detailId && isset($detailAttendees[$detailId])) ? $detailAttendees[$detailId] : array();
                    ?>
                        <tr>
                            <td><?php echo CHtml::encode(isset($detail['content_name']) ? $detail['content_name'] : '-'); ?></td>
                            <td class="text-center"><?php echo count($missAtts); ?></td>
                            <td>
                                <?php foreach ($missAtts as $idx => $att):
                                    $name = isset($att['attendee_name']) ? $att['attendee_name'] : (isset($att['staff_name']) ? $att['staff_name'] : '');
                                ?>
                                    <span class="badge bg-light text-dark border me-1 mb-1"><?php echo ($idx + 1) . '. ' . CHtml::encode($name); ?></span>
                                <?php endforeach; ?>
                            </td>
                            <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                                <td class="text-center">
                                    <form method="post" action="<?php echo $this->createUrl('deleteDetail', array('id' => $detailId, 'registration_id' => $model->id)); ?>" id="delete-detail-form-<?php echo $detailId; ?>" style="display:none;"></form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteDetail(<?php echo $detailId; ?>)">
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

<!-- 4. ĐĂNG KÝ VĂN NGHỆ -->
<div class="card mb-3" id="talent-registration-card">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fa fa-music me-2 text-primary"></i>Đăng ký văn nghệ</h5>
    </div>
    <div class="card-body">
        <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
        <div class="mb-3">
            <button type="button" class="btn btn-sm btn-primary text-white" data-bs-toggle="modal" data-bs-target="#addTalentModal">
                <i class="fa fa-plus me-1"></i>Đăng ký văn nghệ
            </button>
        </div>
        <?php endif; ?>

        <?php if (empty($detailsByContent['talent'])): ?>
            <p class="text-muted mb-0">Chưa đăng ký tiết mục văn nghệ nào.</p>
        <?php else: ?>
            <table class="table table-bordered table-striped table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tiết mục</th>
                        <th style="width:120px;">Thể loại</th>
                        <th style="width:100px;">Số người</th>
                        <th>Danh sách</th>
                        <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                            <th style="width:60px;"></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($detailsByContent['talent'] as $detail):
                        $detailId = isset($detail['id']) ? $detail['id'] : null;
                        $talentAtts = ($detailId && isset($detailAttendees[$detailId])) ? $detailAttendees[$detailId] : array();
                    ?>
                        <tr>
                            <td><?php echo CHtml::encode(isset($detail['content_name']) ? $detail['content_name'] : '-'); ?></td>
                            <td><?php echo CHtml::encode(isset($detail['category_name']) ? $detail['category_name'] : '-'); ?></td>
                            <td class="text-center"><?php echo count($talentAtts); ?></td>
                            <td>
                                <?php foreach ($talentAtts as $idx => $att):
                                    $name = isset($att['attendee_name']) ? $att['attendee_name'] : (isset($att['staff_name']) ? $att['staff_name'] : '');
                                ?>
                                    <span class="badge bg-light text-dark border me-1 mb-1"><?php echo ($idx + 1) . '. ' . CHtml::encode($name); ?></span>
                                <?php endforeach; ?>
                            </td>
                            <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                                <td class="text-center">
                                    <form method="post" action="<?php echo $this->createUrl('deleteDetail', array('id' => $detailId, 'registration_id' => $model->id)); ?>" id="delete-detail-form-<?php echo $detailId; ?>" style="display:none;"></form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteDetail(<?php echo $detailId; ?>)">
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
<?php $this->renderPartial('_modal_document'); ?>
<?php $this->renderPartial('_modal_reject', array('model' => $model)); ?>
<?php $this->renderPartial('_modal_add_attendee_staff', array('model' => $model, 'roles' => $roles, 'transports' => $transports)); ?>
<?php $this->renderPartial('_modal_edit_attendee', array('model' => $model, 'roles' => $roles, 'transports' => $transports)); ?>
<?php $this->renderPartial('_modal_add_attendee_manual', array('model' => $model, 'roles' => $roles, 'transports' => $transports)); ?>
<?php $this->renderPartial('_modal_all_documents'); ?>

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
// Include sport IDs from existing sport teams
foreach ($sportTeams as $team) {
    $teamSportId = isset($team->sport_id) ? $team->sport_id : (isset($team['sport_id']) ? $team['sport_id'] : null);
    if ($teamSportId && !in_array((int)$teamSportId, $sportIds)) {
        $sportIds[] = (int)$teamSportId;
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