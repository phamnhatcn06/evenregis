<div class="modal fade" id="modalCompare" tabindex="-1" aria-labelledby="modalCompareLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCompareLabel">So sánh thí sinh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="compare_container">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalApprove" tabindex="-1" aria-labelledby="modalApproveLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalApproveLabel"><i class="fa fa-check me-2"></i>Duyệt thí sinh</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="approve_contestant_id">
                <input type="hidden" id="approve_contest_id">
                <p class="mb-3">Duyệt thí sinh: <strong id="approve_contestant_name"></strong></p>
                <div class="mb-3">
                    <label class="form-label fw-bold">Gán vào vòng thi:</label>
                    <div id="rounds_loading" class="text-center py-3" style="display:none;">
                        <i class="fa fa-spinner fa-spin fa-2x"></i>
                    </div>
                    <div id="rounds_list" class="list-group">
                    </div>
                    <small class="text-muted mt-2 d-block">Chọn vòng để gán thí sinh vào, hoặc bỏ qua để chỉ duyệt</small>
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

<template id="compare-card-template">
    <div class="compare-column">
        <div class="card h-100">
            <div id="carousel-{id}" class="carousel slide compare-carousel" data-bs-ride="false">
                <div class="carousel-indicators">
                </div>
                <div class="carousel-inner">
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carousel-{id}" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carousel-{id}" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            </div>
            <div class="card-body">
                <h5 class="card-title text-center mb-3"></h5>
                <table class="table table-sm table-bordered compare-table">
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</template>
