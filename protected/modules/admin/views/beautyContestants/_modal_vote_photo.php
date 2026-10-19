<?php
/**
 * Modal thay ảnh bình chọn cho thí sinh Miss.
 * Form action được JS gán động theo thí sinh được chọn.
 */
?>
<div class="modal fade" id="modal_vote_photo" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form_vote_photo" enctype="multipart/form-data"
                  data-url="<?php echo $this->createUrl('uploadVotePhoto'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa fa-image me-1"></i>Ảnh bình chọn
                        <span id="vote_photo_name" class="text-muted fw-normal"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="vote_photo_id" value="">

                    <div class="text-center mb-3">
                        <img id="vote_photo_preview" src=""
                             alt="Ảnh bình chọn"
                             class="img-fluid rounded border"
                             style="max-height: 320px; object-fit: contain; display: none;">
                        <div id="vote_photo_empty" class="text-muted py-5 border rounded bg-light">
                            <i class="fa fa-image fa-2x mb-2 d-block"></i>
                            Chưa có ảnh bình chọn
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Chọn ảnh mới</label>
                        <input type="file" name="vote_photo" id="vote_photo_file"
                               class="form-control" accept="image/jpeg,image/png,image/webp">
                        <small class="text-muted">Định dạng JPG, PNG hoặc WEBP. Tối đa 5MB.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_vote_photo">
                        <i class="fa fa-save me-1"></i>Lưu ảnh
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
