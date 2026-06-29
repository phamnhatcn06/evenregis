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
