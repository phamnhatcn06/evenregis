<?php

/**
 * Alliance sidebar partial - shows pending requests, active alliances, and history in a sidebar column
 *
 * @var array $pendingRequests
 * @var array $historyItems
 * @var string $contentCode
 * @var Registrations $model
 */

$hasPending = !empty($pendingRequests);
$hasHistory = !empty($historyItems);

// Tách active alliances (approved) ra khỏi history
$activeAlliances = array();
$filteredHistory = array();
if (!empty($historyItems)) {
    foreach ($historyItems as $item) {
        $req = $item['request'];
        $status = isset($req->status) ? (int)$req->status : 0;
        if ($status == AllianceRequests::STATUS_APPROVED) {
            $activeAlliances[] = $item;
        } else {
            $filteredHistory[] = $item;
        }
    }
}
$hasActive = !empty($activeAlliances);
$hasFilteredHistory = !empty($filteredHistory);

if (!$hasPending && !$hasActive && !$hasFilteredHistory) return;
?>

<div class="alliance-sidebar">
    <div class="alliance-sidebar-header">
        <i class="fa fa-handshake-o"></i>
        <span>Liên quân</span>
    </div>
    <?php if (isset($allianceRequest) && $allianceRequest && $allianceRequest->status == AllianceRequests::STATUS_APPROVED && !empty($model->relation_property_name)): ?>
        <div class="alert alert-success d-flex align-items-center py-2 px-3 mb-3 border-start border-4 border-success">
            <i class="fa fa-handshake-o me-2 fa-lg text-success"></i>
            <div>
                Đơn vị đang liên quân với: <strong><?php echo CHtml::encode($model->relation_property_name); ?></strong>.
                Hệ thống đang hiển thị và chia sẻ danh sách các đội thi đấu thể thao của cả hai đơn vị.
            </div>
        </div>
    <?php endif; ?>
    <?php if ($hasPending): ?>
        <?php foreach ($pendingRequests as $item):
            $req = $item['request'];
            $reqId = $req->id;
            $requesterName = $item['requester_name'];
            $requestedAt = isset($req->requested_at) ? $req->requested_at : '';
        ?>
            <div class="alliance-pending-card">
                <div class="alliance-pending-icon">
                    <i class="fa fa-bell"></i>
                </div>
                <div class="alliance-pending-content">
                    <div class="alliance-pending-title">Yêu cầu liên quân</div>
                    <div class="alliance-pending-from">từ <strong><?php echo CHtml::encode($requesterName); ?></strong></div>
                    <?php if ($requestedAt): ?>
                        <div class="alliance-pending-time"><i class="fa fa-clock-o"></i><?php echo MyHelper::formatDateTime($requestedAt); ?></div>
                    <?php endif; ?>
                </div>
                <div class="alliance-pending-actions">
                    <button type="button" class="btn-alliance-accept" onclick="confirmApproveAlliance(<?php echo $reqId; ?>)">
                        <i class="fa fa-check"></i> Nhận
                    </button>
                    <button type="button" class="btn-alliance-reject" onclick="confirmRejectAlliance(<?php echo $reqId; ?>)">
                        <i class="fa fa-times"></i> Từ chối
                    </button>
                </div>
                <form id="approve-alliance-form-<?php echo $reqId; ?>" method="post" action="<?php echo $this->createUrl('approveAlliance', array('request_id' => $reqId, 'registration_id' => $model->id)); ?>" style="display:none;"></form>
                <form id="reject-alliance-form-<?php echo $reqId; ?>" method="post" action="<?php echo $this->createUrl('rejectAlliance', array('request_id' => $reqId, 'registration_id' => $model->id)); ?>" style="display:none;">
                    <input type="hidden" name="rejection_reason" id="rejection_reason_<?php echo $reqId; ?>">
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($hasActive): ?>
        <div class="alliance-active-section mb-3">
            <div class="alliance-section-title">
                <i class="fa fa-link text-success"></i>
                <span>Đang liên quân (<?php echo count($activeAlliances); ?>)</span>
            </div>
            <?php foreach ($activeAlliances as $item):
                $req = $item['request'];
                $reqId = isset($req->id) ? $req->id : 0;
                $isSent = $item['type'] === 'sent';
                $partnerName = $item['partner_name'];
                $requestedAt = isset($req->requested_at) ? $req->requested_at : '';
                // Chỉ cho xoá nếu là đơn vị nhận yêu cầu (received)
                $canDelete = !$isSent;
            ?>
                <div class="alliance-active-card">
                    <div class="alliance-active-icon">
                        <i class="fa fa-handshake-o"></i>
                    </div>
                    <div class="alliance-active-content">
                        <div class="alliance-active-partner">
                            <strong><?php echo CHtml::encode($partnerName); ?></strong>
                        </div>
                        <div class="alliance-active-type">
                            <?php echo $isSent ? '<span class="badge bg-info-light text-info">Đã gửi</span>' : '<span class="badge bg-success-light text-success">Đã nhận</span>'; ?>
                        </div>
                        <?php if ($requestedAt): ?>
                            <div class="alliance-active-time"><i class="fa fa-clock-o"></i><?php echo MyHelper::formatDateTime($requestedAt); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if ($canDelete): ?>
                        <div class="alliance-active-actions">
                            <button type="button" class="btn-alliance-delete" onclick="confirmDeleteAlliance(<?php echo $reqId; ?>)" title="Huỷ liên quân">
                                <i class="fa fa-times"></i>
                            </button>
                            <form id="delete-alliance-form-<?php echo $reqId; ?>" method="post" action="<?php echo $this->createUrl('deleteAlliance', array('request_id' => $reqId, 'registration_id' => $model->id)); ?>" style="display:none;"></form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($hasFilteredHistory): ?>
        <div class="alliance-history-section">
            <a class="alliance-history-toggle" data-bs-toggle="collapse" href="#allianceHistory-<?php echo $contentCode; ?>" role="button" aria-expanded="false">
                <i class="fa fa-history"></i>
                <span>Lịch sử (<?php echo count($filteredHistory); ?>)</span>
                <i class="fa fa-chevron-down toggle-icon"></i>
            </a>
            <div class="collapse" id="allianceHistory-<?php echo $contentCode; ?>">
                <div class="alliance-history-list">
                    <?php foreach ($filteredHistory as $idx => $item):
                        $req = $item['request'];
                        $isSent = $item['type'] === 'sent';
                        $partnerName = $item['partner_name'];
                        $status = isset($req->status) ? $req->status : 0;
                        $requestedAt = isset($req->requested_at) ? $req->requested_at : '';
                        $rejectionReason = isset($req->rejection_reason) ? $req->rejection_reason : '';

                        $statusClass = 'status-rejected';
                        $statusIcon = 'fa-times-circle';
                    ?>
                        <div class="alliance-history-item <?php echo $idx > 0 ? 'has-border' : ''; ?>">
                            <div class="alliance-history-status <?php echo $statusClass; ?>">
                                <i class="fa <?php echo $statusIcon; ?>"></i>
                            </div>
                            <div class="alliance-history-info">
                                <div class="alliance-history-partner">
                                    <?php echo $isSent ? 'Đến' : 'Từ'; ?> <strong><?php echo CHtml::encode($partnerName); ?></strong>
                                </div>
                                <div class="alliance-history-time"><?php echo $requestedAt ? MyHelper::formatDateTime($requestedAt) : ''; ?></div>
                                <?php if ($rejectionReason && $status == AllianceRequests::STATUS_REJECTED): ?>
                                    <div class="alliance-history-reason"><i class="fa fa-exclamation-triangle"></i><?php echo CHtml::encode($rejectionReason); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .alliance-sidebar {
        border-radius: 12px;
        padding: 16px;
        height: 100%;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .alliance-sidebar-header {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 600;
        color: #495057;
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.08);
    }

    .alliance-sidebar-header i {
        color: #6c757d;
    }

    .alliance-pending-card {
        background: #fff;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 10px;
        border-left: 3px solid #ffc107;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .alliance-pending-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 10px;
    }

    .alliance-pending-icon i {
        color: #856404;
        font-size: 14px;
    }

    .alliance-pending-title {
        font-size: 13px;
        font-weight: 600;
        color: #212529;
    }

    .alliance-pending-from {
        font-size: 12px;
        color: #6c757d;
        margin-top: 2px;
    }

    .alliance-pending-from strong {
        color: #495057;
    }

    .alliance-pending-time {
        font-size: 11px;
        color: #adb5bd;
        margin-top: 4px;
    }

    .alliance-pending-time i {
        margin-right: 4px;
    }

    .alliance-pending-actions {
        display: flex;
        gap: 6px;
        margin-top: 10px;
    }

    .btn-alliance-accept,
    .btn-alliance-reject {
        flex: 1;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s ease;
        border: none;
    }

    .btn-alliance-accept {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: #fff;
    }

    .btn-alliance-accept:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.35);
    }

    .btn-alliance-reject {
        background: #fff;
        color: #dc3545;
        border: 1px solid #dc3545;
    }

    .btn-alliance-reject:hover {
        background: #dc3545;
        color: #fff;
    }

    .alliance-history-section {
        margin-top: 12px;
    }

    .alliance-history-toggle {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        color: #6c757d;
        text-decoration: none;
        padding: 8px 10px;
        background: rgba(0, 0, 0, 0.03);
        border-radius: 6px;
        transition: all 0.2s ease;
    }

    .alliance-history-toggle:hover {
        background: rgba(0, 0, 0, 0.06);
        color: #495057;
    }

    .alliance-history-toggle .toggle-icon {
        margin-left: auto;
        font-size: 10px;
        transition: transform 0.2s ease;
    }

    .alliance-history-toggle[aria-expanded="true"] .toggle-icon {
        transform: rotate(180deg);
    }

    .alliance-history-list {
        margin-top: 10px;
        background: #fff;
        border-radius: 8px;
        padding: 8px;
    }

    .alliance-history-item {
        display: flex;
        gap: 10px;
        padding: 8px 4px;
    }

    .alliance-history-item.has-border {
        border-top: 1px solid #f1f3f4;
    }

    .alliance-history-status {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .alliance-history-status i {
        font-size: 12px;
    }

    .alliance-history-status.status-approved {
        background: #d4edda;
        color: #28a745;
    }

    .alliance-history-status.status-rejected {
        background: #f8d7da;
        color: #dc3545;
    }

    .alliance-history-status.status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .alliance-history-info {
        flex: 1;
        min-width: 0;
    }

    .alliance-history-partner {
        font-size: 12px;
        color: #495057;
    }

    .alliance-history-partner strong {
        color: #212529;
    }

    .alliance-history-time {
        font-size: 11px;
        color: #adb5bd;
        margin-top: 2px;
    }

    .alliance-history-reason {
        font-size: 11px;
        color: #dc3545;
        margin-top: 4px;
        padding: 4px 6px;
        background: #fff5f5;
        border-radius: 4px;
    }

    .alliance-history-reason i {
        margin-right: 4px;
    }

    /* Active alliances */
    .alliance-section-title {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 13px;
        font-weight: 600;
        color: #28a745;
        margin-bottom: 10px;
    }

    .alliance-active-card {
        background: #fff;
        border-radius: 10px;
        padding: 12px;
        margin-bottom: 8px;
        border-left: 3px solid #28a745;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        display: flex;
        align-items: flex-start;
        gap: 10px;
    }

    .alliance-active-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .alliance-active-icon i {
        color: #28a745;
        font-size: 14px;
    }

    .alliance-active-content {
        flex: 1;
        min-width: 0;
    }

    .alliance-active-partner {
        font-size: 13px;
        color: #212529;
    }

    .alliance-active-type {
        margin-top: 4px;
    }

    .alliance-active-type .badge {
        font-size: 10px;
        padding: 2px 6px;
    }

    .bg-info-light {
        background: #d1ecf1;
    }

    .bg-success-light {
        background: #d4edda;
    }

    .alliance-active-time {
        font-size: 11px;
        color: #adb5bd;
        margin-top: 4px;
    }

    .alliance-active-time i {
        margin-right: 4px;
    }

    .alliance-active-actions {
        flex-shrink: 0;
    }

    .btn-alliance-delete {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        border: 1px solid #dc3545;
        background: #fff;
        color: #dc3545;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .btn-alliance-delete:hover {
        background: #dc3545;
        color: #fff;
    }

    .btn-alliance-delete i {
        font-size: 12px;
    }
</style>