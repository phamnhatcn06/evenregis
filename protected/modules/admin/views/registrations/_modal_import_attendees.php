<!-- Modal Import Attendees -->
<div class="modal fade" id="importExcelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('importExcelAttendees'); ?>" id="import-excel-form" enctype="multipart/form-data">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="event_id" value="<?php echo $model->event_id; ?>">
                <input type="hidden" name="property_id" value="<?php echo $model->property_id; ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-file-excel-o me-2"></i>Import danh sách từ Excel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2">
                        <i class="fa fa-info-circle me-1"></i>Tính năng này dành cho các đơn vị ngoài. Người tham dự được import sẽ có trạng thái <strong>Chờ duyệt</strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tải file mẫu import</label>
                        <div>
                            <a href="<?php echo $this->createUrl('downloadImportTemplate'); ?>" class="btn btn-sm btn-outline-success" target="_blank">
                                <i class="fa fa-download me-1"></i>mau_import_nguoi_tham_du.xlsx
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Chọn file Excel <span class="text-danger">*</span></label>
                        <div id="import_excel_preview" class="premium-preview-box mb-2"></div>
                        <div class="premium-upload-zone">
                            <input type="file" name="excel_file" accept=".xls,.xlsx" required>
                            <div class="upload-info">
                                <i class="fa fa-file-excel-o upload-icon text-success"></i>
                                <div class="upload-title">Kéo thả file Excel vào đây hoặc click để chọn</div>
                                <div class="upload-hint">Chỉ hỗ trợ định dạng .xls và .xlsx (Tối đa 10MB)</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-success" id="btn_submit_import_excel">
                        <i class="fa fa-upload me-1"></i>Import dữ liệu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelector('input[name="excel_file"]').addEventListener('change', function(e) {
        var file = e.target.files[0];
        var preview = document.getElementById('import_excel_preview');
        if (file && preview) {
            preview.innerHTML = '<span class="badge bg-success"><i class="fa fa-file-excel-o me-1"></i>' + file.name + '</span>';
        } else if (preview) {
            preview.innerHTML = '';
        }
    });
</script>

<script>
    document.getElementById('import-excel-form').addEventListener('submit', function() {
        const btn = document.getElementById('btn_submit_import_excel');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';
    });
</script>