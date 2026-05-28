<!-- Modal Edit Miss Contestant -->
<div class="modal fade" id="editMissModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" id="edit-miss-form">
                <input type="hidden" name="id" id="edit_miss_id">
                <div class="modal-header">
                    <h5 class="modal-title">Cập nhật thông tin thí sinh</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Thí sinh</label>
                        <input type="text" class="form-control" id="edit_miss_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số báo danh</label>
                        <input type="text" class="form-control" id="edit_miss_candidate_number" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Chiều cao (cm)</label>
                                <input type="number" step="0.1" class="form-control" name="height_cm" id="edit_miss_height">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Cân nặng (kg)</label>
                                <input type="number" step="0.1" class="form-control" name="weight_kg" id="edit_miss_weight">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số đo 3 vòng</label>
                        <input type="text" class="form-control" name="measurements" id="edit_miss_measurements" placeholder="VD: 90-60-90">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tài năng</label>
                        <input type="text" class="form-control" name="talent" id="edit_miss_talent" placeholder="VD: Múa, hát...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tiểu sử</label>
                        <textarea class="form-control" name="bio" id="edit_miss_bio" rows="3" placeholder="Giới thiệu ngắn..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btn_submit_edit_miss">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
