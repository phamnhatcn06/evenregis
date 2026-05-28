<!-- Modal Xem Video Tiết Mục Văn Nghệ -->
<div class="modal fade" id="videoModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-play-circle me-2"></i><span id="videoModalTitle">Video tiết mục</span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" onclick="stopVideo()"></button>
            </div>
            <div class="modal-body p-0">
                <div id="videoContainer" class="ratio ratio-16x9">
                    <!-- Video content will be injected here -->
                </div>
            </div>
            <div class="modal-footer">
                <a href="#" id="videoDownloadLink" class="btn btn-outline-primary" target="_blank" style="display:none;">
                    <i class="fa fa-external-link me-1"></i>Mở link gốc
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" onclick="stopVideo()">Đóng</button>
            </div>
        </div>
    </div>
</div>
