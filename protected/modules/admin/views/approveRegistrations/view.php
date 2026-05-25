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
                <?php if ($model->status == Registrations::STATUS_SUBMITTED): ?>
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-success" onclick="approveAllRegistration()">
                        <i class="fa fa-check-circle me-1"></i>Duyệt tất cả
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="rejectAllRegistration()">
                        <i class="fa fa-times-circle me-1"></i>Từ chối tất cả
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
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-users me-2"></i>Danh sách người tham dự</h5>
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
                        <?php if ($model->status == Registrations::STATUS_SUBMITTED): ?>
                            <th style="width:150px;">Thao tác</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendees)): ?>
                        <tr>
                            <td colspan="<?php echo $model->status == Registrations::STATUS_SUBMITTED ? 11 : 10; ?>" class="text-center text-muted">Chưa có người tham dự nào.</td>
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
                                <td class="status-cell"><?php echo Attendees::getApprovalStatusLabel($approvalStatus); ?></td>
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
                                <?php if ($model->status == Registrations::STATUS_SUBMITTED): ?>
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
                    $('#attendee-row-' + attendeeId + ' .status-cell').html('<span class=\"badge bg-danger\">Từ chối</span>');
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

function approveAllRegistration() {
    Swal.fire({
        title: 'Duyệt tất cả',
        text: 'Bạn có chắc chắn muốn phê duyệt toàn bộ phiếu đăng ký này?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Duyệt tất cả',
        cancelButtonText: 'Hủy'
    }).then(function(result) {
        if (result.isConfirmed) {
            $.post('{$approveAllUrl}', { registration_id: registrationId }, function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Thành công!',
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

function rejectAllRegistration() {
    Swal.fire({
        title: 'Từ chối toàn bộ đăng ký',
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
            $.post('{$rejectAllUrl}', { registration_id: registrationId, reason: result.value }, function(response) {
                if (response.success) {
                    Swal.fire({
                        title: 'Đã từ chối!',
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
