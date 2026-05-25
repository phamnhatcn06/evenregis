<?php
$this->menu = array(
    array(
        'label' => 'Danh sách',
        'labelIcon' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
);

$this->breadcrumbs = array(
    'Phê duyệt đăng ký' => array('admin'),
    'Chi tiết',
);

$this->Tabletitle = 'Phê duyệt đăng ký của ' . $model->property_name;
?>

<?php
$attributes = array(
    array('label' => 'Sự kiện', 'value' => isset($model->event_name) ? $model->event_name : ''),
    array('label' => 'Đơn vị', 'value' => isset($model->property_name) ? $model->property_name : ''),
    array('label' => 'Đợt đăng ký', 'value' => isset($model->period_name) ? $model->period_name : ''),
    array('label' => 'Trạng thái', 'value' => Registrations::getStatusLabel($model->status), 'raw' => true),
    array('label' => 'Ngày nộp', 'value' => $model->submitted_at ? MyHelper::formatDateTime($model->submitted_at) : '-'),
    array('label' => 'Ghi chú', 'value' => $model->note ?: '-'),
);
?>

<div class="row mb-3">
    <div class="col-md-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin đăng ký</h5>
                <?php if ((int)$model->status === Registrations::STATUS_SUBMITTED): ?>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-success btn-sm px-3" onclick="approveRegistration()">
                        <i class="fa fa-check-circle me-1"></i>Duyệt đăng ký
                    </button>
                    <button type="button" class="btn btn-outline-warning btn-sm px-3" onclick="returnRegistration()">
                        <i class="fa fa-undo me-1"></i>Trả lại
                    </button>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped mb-0">
                    <tbody>
                        <?php foreach ($attributes as $attr): ?>
                            <tr>
                                <th style="width:30%;background:#f8f9fa;"><?php echo CHtml::encode($attr['label']); ?></th>
                                <td><?php echo isset($attr['raw']) && $attr['raw'] ? $attr['value'] : CHtml::encode($attr['value']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 bg-light">
            <div class="card-body text-center">
                <h3 class="mb-3"><?php echo count($attendees); ?></h3>
                <p class="text-muted mb-0">Người tham dự</p>
                <?php
                $pending = 0;
                $approved = 0;
                $rejected = 0;
                foreach ($attendees as $att) {
                    $status = isset($att['approval_status']) ? (int)$att['approval_status'] : 0;
                    if ($status == Attendees::APPROVAL_APPROVED) $approved++;
                    elseif ($status == Attendees::APPROVAL_REJECTED) $rejected++;
                    else $pending++;
                }
                ?>
                <hr>
                <div class="row text-center">
                    <div class="col-4">
                        <span class="badge bg-warning"><?php echo $pending; ?></span>
                        <small class="d-block">Chờ duyệt</small>
                    </div>
                    <div class="col-4">
                        <span class="badge bg-success"><?php echo $approved; ?></span>
                        <small class="d-block">Đã duyệt</small>
                    </div>
                    <div class="col-4">
                        <span class="badge bg-danger"><?php echo $rejected; ?></span>
                        <small class="d-block">Từ chối</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-users me-2"></i>Danh sách người tham dự</h5>
        <?php if ((int)$model->status === Registrations::STATUS_SUBMITTED && $pending > 0): ?>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-success btn-sm px-3" onclick="approveAllAttendees()">
                <i class="fa fa-check me-1"></i>Duyệt tất cả
            </button>
            <button type="button" class="btn btn-outline-danger btn-sm px-3" onclick="rejectAllAttendees()">
                <i class="fa fa-times me-1"></i>Từ chối tất cả
            </button>
        </div>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm mb-0" id="attendees-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">STT</th>
                        <th style="width:80px;">Ảnh</th>
                        <th>Họ tên</th>
                        <th>Phòng ban - Chức danh</th>
                        <th>Vai trò</th>
                        <th>Ngày vào làm</th>
                        <th>Ngày đến</th>
                        <th>Ngày đi</th>
                        <th style="width:100px;">Trạng thái</th>
                        <th style="width:60px;">Tài liệu</th>
                        <?php if ((int)$model->status === Registrations::STATUS_SUBMITTED): ?>
                            <th style="width:150px;">Thao tác</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendees)): ?>
                        <tr>
                            <td colspan="<?php echo (int)$model->status === Registrations::STATUS_SUBMITTED ? 11 : 10; ?>" class="text-center text-muted">Chưa có người tham dự nào.</td>
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
                        ?>
                            <tr id="attendee-row-<?php echo $attId; ?>">
                                <td class="text-center"><?php echo $idx + 1; ?></td>
                                <td class="text-center">
                                    <?php if ($photoPath): ?>
                                        <img src="<?php echo CHtml::encode($photoPath); ?>" class="rounded" style="width:60px;height:60px;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:60px;height:60px;">
                                            <i class="fa fa-user text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo CHtml::encode($fullName); ?></td>
                                <td><?php echo CHtml::encode($position); ?></td>
                                <td><?php echo CHtml::encode($roleName); ?></td>
                                <td><?php echo $startDate ? date('d/m/Y', strtotime($startDate)) : '-'; ?></td>
                                <td><?php echo $checkInDate ? date('d/m/Y', strtotime($checkInDate)) : '-'; ?></td>
                                <td><?php echo $checkOutDate ? date('d/m/Y', strtotime($checkOutDate)) : '-'; ?></td>
                                <td class="status-cell">
                                    <?php echo Attendees::getApprovalStatusLabel($approvalStatus); ?>
                                    <?php if ($approvalStatus == Attendees::APPROVAL_REJECTED && !empty($att['rejection_reason'])): ?>
                                        <br><small class="text-danger"><i class="fa fa-info-circle"></i> <?php echo CHtml::encode($att['rejection_reason']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $hasDoc = !empty($att['portrait_path']) || !empty($att['cccd_front_path']) || !empty($att['cccd_back_path']) || !empty($att['contract_path']);
                                    if ($hasDoc):
                                        $docs = array(
                                            'portrait' => isset($att['portrait_path']) ? $att['portrait_path'] : '',
                                            'cccd_front' => isset($att['cccd_front_path']) ? $att['cccd_front_path'] : '',
                                            'cccd_back' => isset($att['cccd_back_path']) ? $att['cccd_back_path'] : '',
                                            'contract' => isset($att['contract_path']) ? $att['contract_path'] : '',
                                        );
                                    ?>
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="viewAllDocuments(this)" data-docs="<?php echo CHtml::encode(CJSON::encode($docs)); ?>" title="Xem tài liệu">
                                            <i class="fa fa-folder-open-o"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ((int)$model->status === Registrations::STATUS_SUBMITTED): ?>
                                    <td class="text-center action-cell">
                                        <?php if ($approvalStatus == Attendees::APPROVAL_PENDING): ?>
                                            <button type="button" class="btn btn-sm btn-success me-1" onclick="approveAttendee(<?php echo $attId; ?>)" title="Duyệt">
                                                <i class="fa fa-check"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="rejectAttendee(<?php echo $attId; ?>)" title="Từ chối">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
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

<!-- 1. ĐĂNG KÝ THI ĐẤU THỂ THAO -->
<div class="card mb-3">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fa fa-futbol-o me-2 text-primary"></i>Đăng ký thi đấu thể thao</h5>
    </div>
    <div class="card-body">
        <?php if (empty($sportTeams)): ?>
            <p class="text-muted mb-0">Chưa đăng ký môn thể thao nào.</p>
        <?php else: ?>
            <table class="table table-bordered table-striped table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Môn thi đấu</th>
                        <th>Tên đội</th>
                        <th style="width:100px;">Số VĐV</th>
                        <th>Danh sách VĐV</th>
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
                                    $memberName = isset($member['attendee_name']) ? $member['attendee_name'] : (isset($member['name']) ? $member['name'] : '');
                                ?>
                                    <span class="badge bg-light text-dark border me-1 mb-1"><?php echo ($idx + 1) . '. ' . CHtml::encode($memberName); ?></span>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- 2. ĐĂNG KÝ THI NGHIỆP VỤ -->
<div class="card mb-3">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fa fa-trophy me-2 text-primary"></i>Đăng ký thi nghiệp vụ</h5>
    </div>
    <div class="card-body">
        <?php if (empty($competitionRegistrations)): ?>
            <p class="text-muted mb-0">Chưa đăng ký thi nghiệp vụ nào.</p>
        <?php else: ?>
            <table class="table table-bordered table-striped table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cuộc thi</th>
                        <th style="width:100px;">Số người</th>
                        <th>Danh sách thí sinh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($competitionRegistrations as $compId => $compData): ?>
                        <tr>
                            <td><?php echo CHtml::encode($compData['competition_name']); ?></td>
                            <td class="text-center"><?php echo count($compData['attendees']); ?></td>
                            <td>
                                <?php foreach ($compData['attendees'] as $idx => $att):
                                    $name = $att['attendee_name'];
                                    $position = $att['position_name'];
                                    $division = $att['division_name'];
                                    $info = $name;
                                    if ($position || $division) {
                                        $info .= ' <small class="text-muted">(' . trim($position . ' - ' . $division, ' -') . ')</small>';
                                    }
                                ?>
                                    <div><?php echo ($idx + 1) . '. ' . $info; ?></div>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- 3. ĐĂNG KÝ THI SẮC ĐẸP (MISS) -->
<div class="card mb-3">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fa fa-star me-2 text-primary"></i>Đăng ký thi sắc đẹp</h5>
    </div>
    <div class="card-body">
        <?php if (empty($beautyContestants)): ?>
            <p class="text-muted mb-0">Chưa đăng ký thi sắc đẹp nào.</p>
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
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contestData['contestants'] as $c): ?>
                        <tr>
                            <td><span class="badge bg-primary"><?php echo CHtml::encode($c['candidate_number']); ?></span></td>
                            <td><?php echo CHtml::encode($c['attendee_name']); ?></td>
                            <td class="text-center"><?php echo isset($c['height_cm']) && $c['height_cm'] ? $c['height_cm'] : '-'; ?></td>
                            <td class="text-center"><?php echo isset($c['weight_kg']) && $c['weight_kg'] ? $c['weight_kg'] : '-'; ?></td>
                            <td class="text-center"><?php echo isset($c['measurements']) && $c['measurements'] ? CHtml::encode($c['measurements']) : '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- 4. ĐĂNG KÝ VĂN NGHỆ -->
<div class="card mb-3">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fa fa-music me-2 text-primary"></i>Đăng ký văn nghệ</h5>
    </div>
    <div class="card-body">
        <?php if (empty($talentEntries)): ?>
            <p class="text-muted mb-0">Chưa đăng ký tiết mục văn nghệ nào.</p>
        <?php else: ?>
            <table class="table table-bordered table-striped table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tiết mục</th>
                        <th style="width:120px;">Thể loại</th>
                        <th style="width:100px;">Số người</th>
                        <th>Danh sách</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($talentEntries as $entry):
                        $entryId = isset($entry->id) ? $entry->id : (isset($entry['id']) ? $entry['id'] : null);
                        $entryTitle = isset($entry->title) ? $entry->title : (isset($entry['title']) ? $entry['title'] : '-');
                        $categoryName = isset($entry->category_name) ? $entry->category_name : (isset($entry['category_name']) ? $entry['category_name'] : '-');
                        $members = ($entryId && isset($talentEntryMembers[$entryId])) ? $talentEntryMembers[$entryId] : array();
                    ?>
                        <tr>
                            <td><?php echo CHtml::encode($entryTitle); ?></td>
                            <td><?php echo CHtml::encode($categoryName); ?></td>
                            <td class="text-center"><?php echo count($members); ?></td>
                            <td>
                                <?php foreach ($members as $idx => $member):
                                    $name = isset($member['attendee_name']) ? $member['attendee_name'] : '';
                                ?>
                                    <span class="badge bg-light text-dark border me-1 mb-1"><?php echo ($idx + 1) . '. ' . CHtml::encode($name); ?></span>
                                <?php endforeach; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal View All Documents -->
<div class="modal fade" id="allDocumentsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tài liệu đính kèm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="all_documents_viewer">
                <!-- Content will be injected dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Modal View Single Document -->
<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xem tài liệu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="document_viewer">
                <!-- Content will be injected dynamically -->
            </div>
        </div>
    </div>
</div>

<?php
$baseUrl = Yii::app()->theme->baseUrl;
Yii::app()->clientScript->registerCssFile($baseUrl . '/assets/vendor/DataTables/datatables.min.css');
Yii::app()->clientScript->registerScriptFile($baseUrl . '/assets/vendor/DataTables/datatables.min.js', CClientScript::POS_END);

$registrationId = $model->id;
$approveAttendeeUrl = $this->createUrl('approveAttendee');
$rejectAttendeeUrl = $this->createUrl('rejectAttendee');
$approveAllUrl = $this->createUrl('approveAll');
$rejectAllUrl = $this->createUrl('rejectAll');
$returnUrl = $this->createUrl('return');
$adminUrl = $this->createUrl('admin');

Yii::app()->clientScript->registerScript('approve-registrations-view', "
var registrationId = {$registrationId};

function viewAllDocuments(btn) {
    var docs = JSON.parse(btn.getAttribute('data-docs'));
    var labels = {
        'portrait': 'Ảnh chân dung',
        'cccd_front': 'CCCD mặt trước',
        'cccd_back': 'CCCD mặt sau',
        'contract': 'Hợp đồng'
    };
    var html = '<div class=\"row g-3\">';
    for (var key in docs) {
        if (docs[key]) {
            var ext = docs[key].split('.').pop().toLowerCase();
            var isPdf = (ext === 'pdf');
            html += '<div class=\"col-md-6 col-lg-3\">';
            html += '<div class=\"card h-100\">';
            html += '<div class=\"card-header py-2 bg-light\"><small class=\"fw-bold\">' + labels[key] + '</small></div>';
            if (isPdf) {
                html += '<div class=\"card-body text-center d-flex align-items-center justify-content-center\" style=\"min-height:150px;\">';
                html += '<i class=\"fa fa-file-pdf-o fa-3x text-danger\"></i>';
                html += '</div>';
            } else {
                html += '<img src=\"' + docs[key] + '\" class=\"card-img-top\" style=\"height:150px;object-fit:cover;cursor:pointer;\" onclick=\"viewDocument(\\'' + docs[key] + '\\', \\'image\\')\">';
            }
            html += '<div class=\"card-footer text-center py-2\">';
            if (!isPdf) {
                html += '<button type=\"button\" class=\"btn btn-xs btn-outline-primary me-1\" onclick=\"viewDocument(\\'' + docs[key] + '\\', \\'image\\')\"><i class=\"fa fa-eye\"></i></button>';
            }
            html += '<a href=\"' + docs[key] + '\" class=\"btn btn-xs btn-outline-secondary\" download><i class=\"fa fa-download\"></i></a>';
            if (isPdf) {
                html += ' <a href=\"' + docs[key] + '\" target=\"_blank\" class=\"btn btn-xs btn-outline-info\"><i class=\"fa fa-external-link\"></i></a>';
            }
            html += '</div></div></div>';
        }
    }
    html += '</div>';
    document.getElementById('all_documents_viewer').innerHTML = html;
    var modal = new bootstrap.Modal(document.getElementById('allDocumentsModal'));
    modal.show();
}

function viewDocument(url, type) {
    var viewer = document.getElementById('document_viewer');
    if (type === 'image') {
        viewer.innerHTML = '<img src=\"' + url + '\" style=\"max-width:100%;max-height:80vh;\">';
    } else if (type === 'pdf') {
        viewer.innerHTML = '<iframe src=\"' + url + '\" style=\"width:100%;height:80vh;border:none;\"></iframe>';
    }
    var modal = new bootstrap.Modal(document.getElementById('documentModal'));
    modal.show();
}

function approveAttendee(attendeeId) {
    Swal.fire({
        title: 'Xác nhận duyệt',
        text: 'Bạn có chắc chắn muốn phê duyệt người tham dự này?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Duyệt',
        cancelButtonText: 'Hủy'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post('{$approveAttendeeUrl}', { attendee_id: attendeeId }, function(response) {
                if (response.success) {
                    Toast.success(response.message);
                    $('#attendee-row-' + attendeeId + ' .status-cell').html('<span class=\"badge bg-success\">Đã duyệt</span>');
                    $('#attendee-row-' + attendeeId + ' .action-cell').html('<span class=\"text-muted\">-</span>');
                } else {
                    Toast.error(response.error || 'Có lỗi xảy ra.');
                }
            }, 'json').fail(function() {
                Toast.error('Có lỗi xảy ra khi gọi API.');
            });
        }
    });
}

function rejectAttendee(attendeeId) {
    Swal.fire({
        title: 'Từ chối người tham dự',
        input: 'textarea',
        inputLabel: 'Lý do từ chối',
        inputPlaceholder: 'Nhập lý do từ chối...',
        inputAttributes: {
            'aria-label': 'Lý do từ chối'
        },
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Từ chối',
        cancelButtonText: 'Hủy',
        inputValidator: function(value) {
            if (!value || !value.trim()) {
                return 'Vui lòng nhập lý do từ chối!';
            }
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post('{$rejectAttendeeUrl}', { attendee_id: attendeeId, reason: result.value }, function(response) {
                if (response.success) {
                    Toast.success(response.message);
                    var statusHtml = '<span class=\"badge bg-danger\">Từ chối</span><br><small class=\"text-danger\"><i class=\"fa fa-info-circle\"></i> ' + $('<div>').text(result.value).html() + '</small>';
                    $('#attendee-row-' + attendeeId + ' .status-cell').html(statusHtml);
                    $('#attendee-row-' + attendeeId + ' .action-cell').html('<span class=\"text-muted\">-</span>');
                } else {
                    Toast.error(response.error || 'Có lỗi xảy ra.');
                }
            }, 'json').fail(function() {
                Toast.error('Có lỗi xảy ra khi gọi API.');
            });
        }
    });
}

function approveRegistration() {
    Swal.fire({
        title: 'Duyệt đăng ký',
        text: 'Bạn có chắc chắn muốn duyệt phiếu đăng ký này?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Duyệt',
        cancelButtonText: 'Hủy',
        allowOutsideClick: false,
        showLoaderOnConfirm: true,
        preConfirm: function() {
            return new Promise(function(resolve, reject) {
                $.post('{$approveAllUrl}', { registration_id: registrationId }, function(response) {
                    resolve(response);
                }, 'json').fail(function() {
                    reject('Có lỗi xảy ra khi gọi API.');
                });
            }).catch(function(error) {
                Swal.showValidationMessage(error);
            });
        }
    }).then(function(result) {
        if (result.isConfirmed && result.value) {
            if (result.value.success) {
                Swal.fire({
                    title: 'Thành công!',
                    text: result.value.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(function() {
                    window.location.href = '{$adminUrl}';
                });
            } else {
                Toast.error(result.value.error || 'Có lỗi xảy ra.');
            }
        }
    });
}

function returnRegistration() {
    Swal.fire({
        title: 'Trả lại đăng ký',
        input: 'textarea',
        inputLabel: 'Lý do trả lại',
        inputPlaceholder: 'Nhập lý do trả lại để đơn vị chỉnh sửa...',
        inputAttributes: {
            'aria-label': 'Lý do trả lại'
        },
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Trả lại',
        cancelButtonText: 'Hủy',
        inputValidator: function(value) {
            if (!value || !value.trim()) {
                return 'Vui lòng nhập lý do trả lại!';
            }
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post('{$returnUrl}', { registration_id: registrationId, reason: result.value }, function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Đã trả lại!',
                        text: response.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(function() {
                        window.location.href = '{$adminUrl}';
                    });
                } else {
                    Toast.error(response.error || 'Có lỗi xảy ra.');
                }
            }, 'json').fail(function() {
                Toast.error('Có lỗi xảy ra khi gọi API.');
            });
        }
    });
}

function approveAllAttendees() {
    Swal.fire({
        title: 'Duyệt tất cả người tham dự',
        text: 'Bạn có chắc chắn muốn duyệt tất cả người đang chờ duyệt?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Duyệt tất cả',
        cancelButtonText: 'Hủy'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post('{$approveAttendeeUrl}', { registration_id: registrationId, all: 1 }, function(response) {
                if (response.success) {
                    Toast.success(response.message);
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    Toast.error(response.error || 'Có lỗi xảy ra.');
                }
            }, 'json').fail(function() {
                Toast.error('Có lỗi xảy ra khi gọi API.');
            });
        }
    });
}

function rejectAllAttendees() {
    Swal.fire({
        title: 'Từ chối tất cả người tham dự',
        input: 'textarea',
        inputLabel: 'Lý do từ chối',
        inputPlaceholder: 'Nhập lý do từ chối...',
        inputAttributes: {
            'aria-label': 'Lý do từ chối'
        },
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Từ chối tất cả',
        cancelButtonText: 'Hủy',
        inputValidator: function(value) {
            if (!value || !value.trim()) {
                return 'Vui lòng nhập lý do từ chối!';
            }
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post('{$rejectAttendeeUrl}', { registration_id: registrationId, all: 1, reason: result.value }, function(response) {
                if (response.success) {
                    Toast.success(response.message);
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    Toast.error(response.error || 'Có lỗi xảy ra.');
                }
            }, 'json').fail(function() {
                Toast.error('Có lỗi xảy ra khi gọi API.');
            });
        }
    });
}

$(document).ready(function() {
    if ($.fn.DataTable) {
        $('#attendees-table').DataTable({
            paging: true,
            pageLength: 25,
            ordering: true,
            searching: true,
            language: {
                lengthMenu: 'Hiển thị _MENU_ dòng',
                info: 'Đang xem _START_ - _END_ / _TOTAL_ người',
                infoEmpty: 'Không có dữ liệu',
                infoFiltered: '(lọc từ _MAX_ người)',
                search: 'Tìm kiếm:',
                paginate: { first: 'Đầu', last: 'Cuối', next: 'Sau', previous: 'Trước' },
                emptyTable: 'Chưa có người tham dự nào.'
            },
            columnDefs: [
                { orderable: false, targets: [1, -1] }
            ]
        });
    }
});
", CClientScript::POS_END);
?>
