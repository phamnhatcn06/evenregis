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

$totalAttrs = count($attributes);
if ($totalAttrs <= 4) {
    $colClass = 'col-12';
    $columns = 1;
} elseif ($totalAttrs <= 8) {
    $colClass = 'col-md-6';
    $columns = 2;
} else {
    $colClass = 'col-md-4';
    $columns = 3;
}
$perColumn = ceil($totalAttrs / $columns);
?>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin phiếu đăng ký</h5>
        <div class="btn-group">
            <?php if ($model->status == Registrations::STATUS_DRAFT): ?>
                <form method="post" action="<?php echo $this->createUrl('submit', array('id' => $model->id)); ?>" style="display:inline;">
                    <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Bạn có chắc muốn nộp phiếu đăng ký này?')">
                        <i class="fa fa-paper-plane me-1"></i>Nộp đăng ký
                    </button>
                </form>
            <?php endif; ?>
            <?php if ($model->status == Registrations::STATUS_SUBMITTED): ?>
                <form method="post" action="<?php echo $this->createUrl('approve', array('id' => $model->id)); ?>" style="display:inline;">
                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Bạn có chắc muốn phê duyệt phiếu đăng ký này?')">
                        <i class="fa fa-check me-1"></i>Phê duyệt
                    </button>
                </form>
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fa fa-times me-1"></i>Từ chối
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <?php for ($col = 0; $col < $columns; $col++): ?>
                <div class="<?php echo $colClass; ?>">
                    <table class="table table-bordered table-striped mb-0">
                        <tbody>
                            <?php
                            $start = $col * $perColumn;
                            $end = min($start + $perColumn, $totalAttrs);
                            for ($i = $start; $i < $end; $i++):
                                $attr = $attributes[$i];
                            ?>
                                <tr>
                                    <th style="width:40%;background:#f8f9fa;"><?php echo CHtml::encode($attr['label']); ?></th>
                                    <td><?php echo isset($attr['raw']) && $attr['raw'] ? $attr['value'] : CHtml::encode($attr['value']); ?></td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-file-text me-2"></i>Tài liệu đính kèm</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($documents)): ?>
            <div class="row g-3">
                <?php foreach ($documents as $index => $docUrl):
                    $filename = basename($docUrl);
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $isImage = in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'));
                    $isPdf = ($ext === 'pdf');
                ?>
                    <div class="col-6 col-md-2">
                        <div class="card h-100">
                            <?php if ($isImage): ?>
                                <img src="<?php echo CHtml::encode($docUrl); ?>" class="card-img-top" style="height:220px;object-fit:cover;cursor:pointer;"
                                    onclick="viewDocument('<?php echo CHtml::encode($docUrl); ?>', 'image')" title="Click để xem">
                            <?php else: ?>
                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:220px;">
                                    <?php if ($isPdf): ?>
                                        <i class="fa fa-file-pdf-o fa-3x text-danger"></i>
                                    <?php elseif (in_array($ext, array('doc', 'docx'))): ?>
                                        <i class="fa fa-file-word-o fa-3x text-primary"></i>
                                    <?php else: ?>
                                        <i class="fa fa-file-o fa-3x text-muted"></i>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="card-body p-2 text-center">
                                <small class="text-truncate d-block mb-2" title="<?php echo CHtml::encode($filename); ?>">
                                    <?php echo CHtml::encode($filename); ?>
                                </small>
                                <?php if ($isImage || $isPdf): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="viewDocument('<?php echo CHtml::encode($docUrl); ?>', '<?php echo $isImage ? 'image' : 'pdf'; ?>')">
                                        <i class="fa fa-eye me-1"></i>Xem
                                    </button>
                                <?php endif; ?>
                                <a href="<?php echo CHtml::encode($docUrl); ?>" class="btn btn-sm btn-outline-secondary" download>
                                    <i class="fa fa-download me-1"></i>Tải
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">Không có tài liệu đính kèm.</p>
        <?php endif; ?>
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
            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addDetailModal" onclick="resetAddModal()">
                <i class="fa fa-plus me-1"></i>Thêm nội dung
            </button>
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
                            ?>
                                <tr>
                                    <td><?php echo CHtml::encode($itemName); ?></td>
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

<!-- Modal Add Detail -->
<div class="modal fade" id="addDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addDetail'); ?>" id="add-detail-form">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="content_type" id="content_type" value="">
                <input type="hidden" name="content_id" id="content_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm nội dung đăng ký</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Loại nội dung <span class="text-danger">*</span></label>
                        <select class="form-select" id="content_select" required>
                            <option value="">-- Chọn loại nội dung --</option>
                        </select>
                    </div>
                    <div class="mb-3" id="item_wrapper" style="display:none;">
                        <label class="form-label" id="item_label">Bộ môn <span class="text-danger">*</span></label>
                        <select class="form-select" id="item_id" name="item_id">
                            <option value="">-- Chọn bộ môn --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" id="quantity_label">Số lượng (người/đội) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="quantity" id="quantity" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea class="form-control" name="note" id="note" rows="2" placeholder="Ghi chú thêm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Thêm</button>
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

<script>
    function viewDocument(url, type) {
        var modalBody = document.getElementById('documentModalBody');
        var downloadLink = document.getElementById('documentDownloadLink');

        downloadLink.href = url;

        if (type === 'image') {
            modalBody.innerHTML = '<div class="text-center p-3"><img src="' + url + '" class="img-fluid" style="max-height:80vh;"></div>';
        } else if (type === 'pdf') {
            modalBody.innerHTML = '<iframe src="' + url + '" style="width:100%;height:80vh;border:none;"></iframe>';
        }

        var modal = new bootstrap.Modal(document.getElementById('documentModal'));
        modal.show();
    }

    function confirmDeleteDetail(detailId) {
        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc chắn muốn xóa nội dung này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById('delete-detail-form-' + detailId).submit();
            }
        });
    }

    var eventId = <?php echo $model->event_id ? $model->event_id : 'null'; ?>;
    var contentsData = [];

    // Danh sách đã đăng ký để loại trừ
    var registeredSports = <?php
                            $sportIds = array();
                            $competitionIds = array();
                            foreach ($registrationDetails as $d) {
                                if (!empty($d['sport_id'])) $sportIds[] = (int)$d['sport_id'];
                                if (!empty($d['competition_id'])) $competitionIds[] = (int)$d['competition_id'];
                            }
                            echo json_encode($sportIds);
                            ?>;
    var registeredCompetitions = <?php echo json_encode($competitionIds); ?>;

    // Load contents data khi page load
    document.addEventListener('DOMContentLoaded', function() {
        var contentSelect = document.getElementById('content_select');

        if (eventId && contentSelect) {
            fetch('<?php echo Yii::app()->createUrl("/admin/registrations/getEventContents"); ?>?event_id=' + eventId)
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success && data.data) {
                        contentsData = data.data;
                    }
                });

            contentSelect.addEventListener('change', function() {
                var selectedOpt = this.options[this.selectedIndex];
                var contentCode = selectedOpt.getAttribute('data-code') || '';
                var contentId = this.value;

                document.getElementById('content_type').value = contentCode;
                document.getElementById('content_id').value = contentId;

                loadContentItems(contentCode);
            });
        }
    });

    function renderSportsTree(data, excludeIds) {
        var html = '<option value="">-- Chọn môn thể thao --</option>';
        var groups = {};
        var prefixes = ['Bóng bàn', 'Bóng đá', 'Cầu lông', 'Pickerball', 'Bơi ếch', 'Bơi tự do', 'Kéo co', 'Tennis', 'Cờ vua', 'Cờ tướng'];

        data.forEach(function(item) {
            if (excludeIds.indexOf(parseInt(item.id)) !== -1) return;
            var groupName = 'Khác';
            for (var i = 0; i < prefixes.length; i++) {
                if (item.name.indexOf(prefixes[i]) === 0) {
                    groupName = prefixes[i];
                    break;
                }
            }
            if (!groups[groupName]) groups[groupName] = [];
            groups[groupName].push(item);
        });

        var sortedGroups = Object.keys(groups).sort();
        sortedGroups.forEach(function(groupName) {
            var items = groups[groupName];
            if (items.length > 1) {
                html += '<option value="" disabled style="font-weight:bold;background:#e9ecef;">▸ ' + groupName + '</option>';
                items.forEach(function(item) {
                    html += '<option value="' + item.id + '">&nbsp;&nbsp;&nbsp;' + item.name + '</option>';
                });
            } else {
                html += '<option value="' + items[0].id + '">' + items[0].name + '</option>';
            }
        });
        return html;
    }

    function resetAddModal() {
        var contentSelect = document.getElementById('content_select');
        var itemSelect = document.getElementById('item_id');
        var itemWrapper = document.getElementById('item_wrapper');

        // Reset form
        document.getElementById('add-detail-form').reset();
        document.getElementById('content_type').value = '';
        document.getElementById('content_id').value = '';
        document.getElementById('quantity').value = '1';

        // Populate content dropdown
        contentSelect.innerHTML = '<option value="">-- Chọn loại nội dung --</option>';
        contentsData.forEach(function(c) {
            var opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            opt.setAttribute('data-code', c.code || '');
            contentSelect.appendChild(opt);
        });

        itemWrapper.style.display = 'none';
        itemSelect.innerHTML = '<option value="">-- Chọn bộ môn --</option>';
        itemSelect.removeAttribute('required');
    }

    function loadContentItems(contentCode) {
        var itemLabel = document.getElementById('item_label');
        var quantityLabel = document.getElementById('quantity_label');
        var itemSelect = document.getElementById('item_id');
        var itemWrapper = document.getElementById('item_wrapper');

        itemSelect.innerHTML = '<option value="">-- Đang tải... --</option>';

        if (contentCode === 'sports') {
            itemLabel.innerHTML = 'Môn thể thao <span class="text-danger">*</span>';
            quantityLabel.innerHTML = 'Số đội/người <span class="text-danger">*</span>';
            itemWrapper.style.display = 'block';
            itemSelect.setAttribute('required', 'required');

            fetch('<?php echo Yii::app()->createUrl("/admin/registrations/getContentItems"); ?>?event_id=' + eventId + '&content_type=sports')
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    if (data.success && data.data && data.data.length > 0) {
                        itemSelect.innerHTML = renderSportsTree(data.data, registeredSports);
                    } else {
                        itemSelect.innerHTML = '<option value="">-- Đã đăng ký hết --</option>';
                    }
                });
        } else if (contentCode === 'competition') {
            itemLabel.innerHTML = 'Cuộc thi <span class="text-danger">*</span>';
            quantityLabel.innerHTML = 'Số người <span class="text-danger">*</span>';
            itemWrapper.style.display = 'block';
            itemSelect.setAttribute('required', 'required');

            fetch('<?php echo Yii::app()->createUrl("/admin/registrations/getContentItems"); ?>?event_id=' + eventId + '&content_type=competition')
                .then(function(response) {
                    return response.json();
                })
                .then(function(data) {
                    var html = '<option value="">-- Chọn cuộc thi --</option>';
                    if (data.success && data.data && data.data.length > 0) {
                        data.data.forEach(function(item) {
                            if (registeredCompetitions.indexOf(parseInt(item.id)) === -1) {
                                html += '<option value="' + item.id + '">' + item.name + '</option>';
                            }
                        });
                    }
                    itemSelect.innerHTML = html;
                });
        } else if (contentCode === 'miss') {
            quantityLabel.innerHTML = 'Số người dự thi <span class="text-danger">*</span>';
            itemWrapper.style.display = 'none';
            itemSelect.removeAttribute('required');
        } else if (contentCode === 'talent') {
            quantityLabel.innerHTML = 'Số tiết mục <span class="text-danger">*</span>';
            itemWrapper.style.display = 'none';
            itemSelect.removeAttribute('required');
        } else {
            itemWrapper.style.display = 'none';
            itemSelect.removeAttribute('required');
        }
    }
</script>