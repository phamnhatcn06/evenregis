<div class="modal fade" id="modalApprove" tabindex="-1" aria-labelledby="modalApproveLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalApproveLabel"><i class="fa fa-check me-2"></i>Duyệt tiết mục</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="approve_entry_id">
                <input type="hidden" id="approve_show_id">
                <p class="mb-3">Duyệt tiết mục: <strong id="approve_entry_name"></strong></p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Gán vào vòng thi:</label>
                    <div id="rounds_loading" class="text-center py-3" style="display:none;">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                    </div>
                    <div id="rounds_list" class="list-group">
                    </div>
                    <small class="text-muted mt-2 d-block">Chọn vòng để gán tiết mục vào, hoặc bỏ qua để chỉ duyệt</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" id="btn_confirm_approve">
                    <i class="fa fa-check me-1"></i>Duyệt
                </button>
            </div>
        </div>
    </div>
</div>
