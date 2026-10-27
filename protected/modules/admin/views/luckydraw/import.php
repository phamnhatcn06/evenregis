<?php
/**
 * Màn Import danh sách người trúng giải (bốc thủ công) + xem danh sách đã công bố.
 *
 * @var LuckyDrawController $this
 * @var array $eventList  Dropdown sự kiện (id => name)
 * @var int   $eventId    Sự kiện đang chọn
 * @var array $winners    Danh sách người trúng của sự kiện đang chọn
 * @var bool  $canDelete
 */

$this->breadcrumbs = array('Người trúng giải');
$this->Tabletitle  = 'Người trúng giải (Lucky Draw)';

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/plugins/toast.js',
    CClientScript::POS_END
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/luckydraw-import.js?v=1.0',
    CClientScript::POS_END
);

$e = function ($s) {
    return CHtml::encode($s !== null ? (string) $s : '');
};
$publicUrl = Yii::app()->request->baseUrl . '/luckydraw';
?>

<div id="lucky-import"
     data-parse-url="<?php echo $this->createUrl('parseExcel'); ?>"
     data-save-url="<?php echo $this->createUrl('saveImport'); ?>"
     data-delete-url="<?php echo $this->createUrl('deleteWinner', array('id' => '__ID__')); ?>"
     data-clear-url="<?php echo $this->createUrl('clearWinners'); ?>">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h4 class="mb-0"><i class="fa fa-trophy me-2"></i>Người trúng giải (bốc thủ công)</h4>
        <div class="d-flex gap-2">
            <a href="<?php echo $this->createUrl('downloadTemplate'); ?>" class="btn btn-outline-secondary">
                <i class="fa fa-download me-1"></i>Tải file mẫu
            </a>
            <a href="<?php echo $e($publicUrl); ?>" target="_blank" class="btn btn-outline-primary">
                <i class="fa fa-external-link me-1"></i>Xem trang công bố
            </a>
        </div>
    </div>

    <!-- Chọn sự kiện + Upload -->
    <div class="card mb-3">
        <div class="card-header"><h5 class="mb-0">Import danh sách</h5></div>
        <div class="card-body">
            <form id="form-import" onsubmit="return false;">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Sự kiện <span class="text-danger">*</span></label>
                        <select id="import-event" class="form-select" data-current="<?php echo (int) $eventId; ?>">
                            <?php foreach ($eventList as $id => $name): ?>
                                <option value="<?php echo (int) $id; ?>" <?php echo ((int) $id === (int) $eventId) ? 'selected' : ''; ?>>
                                    <?php echo $e($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Dữ liệu import <b>cộng dồn</b> vào sự kiện này.</small>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">File Excel (.xlsx) <span class="text-danger">*</span></label>
                        <input type="file" id="import-file" class="form-control" accept=".xlsx,.xls">
                    </div>
                    <div class="col-md-2">
                        <button type="button" id="btn-parse" class="btn btn-primary w-100">
                            <i class="fa fa-eye me-1"></i>Xem trước
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Kết quả đọc file: preview + lỗi -->
    <div class="card mb-3 d-none" id="preview-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Xem trước dữ liệu</h5>
            <span class="badge bg-info" id="preview-count">0 dòng hợp lệ</span>
        </div>
        <div class="card-body">
            <div id="preview-errors" class="alert alert-warning d-none"></div>
            <div class="table-responsive" style="max-height:360px;overflow:auto;">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:48px;">#</th>
                            <th>Họ và tên</th>
                            <th>Mã lucky draw</th>
                            <th>Đơn vị</th>
                            <th>Hạng giải</th>
                            <th>Tên giải thưởng</th>
                        </tr>
                    </thead>
                    <tbody id="preview-body"></tbody>
                </table>
            </div>
            <div class="mt-3 d-flex justify-content-end">
                <button type="button" id="btn-save" class="btn btn-success">
                    <i class="fa fa-save me-1"></i>Lưu danh sách
                </button>
            </div>
        </div>
    </div>

    <!-- Danh sách đã công bố -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Danh sách đã công bố (<span id="winner-count"><?php echo count($winners); ?></span>)</h5>
            <?php if ($canDelete): ?>
                <button type="button" id="btn-clear" class="btn btn-sm btn-outline-danger"<?php echo empty($winners) ? ' disabled' : ''; ?>>
                    <i class="fa fa-trash me-1"></i>Xoá toàn bộ
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:48px;">#</th>
                            <th>Họ và tên</th>
                            <th>Mã</th>
                            <th>Đơn vị</th>
                            <th>Hạng giải</th>
                            <th>Giải thưởng</th>
                            <?php if ($canDelete): ?><th style="width:72px;">Thao tác</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody id="winner-body">
                        <?php if (empty($winners)): ?>
                            <tr id="winner-empty"><td colspan="<?php echo $canDelete ? 7 : 6; ?>" class="text-center text-muted py-4">Chưa có người trúng giải cho sự kiện này.</td></tr>
                        <?php else: $i = 1; foreach ($winners as $w): ?>
                            <tr data-id="<?php echo (int) $w['id']; ?>">
                                <td><?php echo $i++; ?></td>
                                <td><?php echo $e($w['full_name']); ?></td>
                                <td><?php echo $e($w['lucky_code']); ?></td>
                                <td><?php echo $e($w['unit_label']); ?></td>
                                <td><?php echo $e($w['prize_tier']); ?></td>
                                <td><?php echo $e($w['prize_name']); ?></td>
                                <?php if ($canDelete): ?>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-del-winner" data-id="<?php echo (int) $w['id']; ?>">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
