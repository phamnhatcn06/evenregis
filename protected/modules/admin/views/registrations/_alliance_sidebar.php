<?php
/**
 * Alliance sidebar partial - shows pending requests and history in a sidebar column
 *
 * @var array $pendingRequests
 * @var array $historyItems
 * @var string $contentCode
 * @var Registrations $model
 */

$hasPending = !empty($pendingRequests);
$hasHistory = !empty($historyItems);

if (!$hasPending && !$hasHistory) return;
?>

<div class="bg-light rounded p-3 h-100">
    <h6 class="mb-3 text-muted"><i class="fa fa-handshake-o me-2"></i>Liên quân</h6>

    <?php if ($hasPending): ?>
        <?php foreach ($pendingRequests as $item):
            $req = $item['request'];
            $reqId = $req->id;
            $requesterName = $item['requester_name'];
            $requestedAt = isset($req->requested_at) ? $req->requested_at : '';
        ?>
            <div class="border border-warning rounded p-2 mb-2 bg-white alliance-request-alert" style="border-left: 4px solid #ffc107 !important;">
                <div class="d-flex align-items-start mb-2">
                    <i class="fa fa-bell text-warning me-2 mt-1"></i>
                    <div class="small">
                        <strong>Yêu cầu liên quân</strong>
                        <div class="text-muted">từ <strong><?php echo CHtml::encode($requesterName); ?></strong></div>
                        <?php if ($requestedAt): ?>
                            <div class="text-muted" style="font-size:11px;"><i class="fa fa-clock-o me-1"></i><?php echo MyHelper::formatDateTime($requestedAt); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-sm btn-success flex-fill py-1" onclick="confirmApproveAlliance(<?php echo $reqId; ?>)">
                        <i class="fa fa-check"></i> Nhận
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger flex-fill py-1" onclick="confirmRejectAlliance(<?php echo $reqId; ?>)">
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

    <?php if ($hasHistory): ?>
        <div class="small">
            <a class="text-muted" data-bs-toggle="collapse" href="#allianceHistory-<?php echo $contentCode; ?>" role="button" aria-expanded="false">
                <i class="fa fa-history me-1"></i>Lịch sử (<?php echo count($historyItems); ?>)
                <i class="fa fa-chevron-down ms-1" style="font-size:10px;"></i>
            </a>
            <div class="collapse mt-2" id="allianceHistory-<?php echo $contentCode; ?>">
                <?php foreach ($historyItems as $idx => $item):
                    $req = $item['request'];
                    $isSent = $item['type'] === 'sent';
                    $partnerName = $item['partner_name'];
                    $status = isset($req->status) ? $req->status : 0;
                    $requestedAt = isset($req->requested_at) ? $req->requested_at : '';
                    $rejectionReason = isset($req->rejection_reason) ? $req->rejection_reason : '';

                    if ($status == AllianceRequests::STATUS_APPROVED) {
                        $statusClass = 'text-success';
                        $statusIcon = 'fa-check-circle';
                    } elseif ($status == AllianceRequests::STATUS_REJECTED) {
                        $statusClass = 'text-danger';
                        $statusIcon = 'fa-times-circle';
                    } else {
                        $statusClass = 'text-warning';
                        $statusIcon = 'fa-clock-o';
                    }
                ?>
                <div class="<?php echo $idx > 0 ? 'mt-2 pt-2 border-top' : ''; ?>" style="font-size:12px;">
                    <div class="d-flex justify-content-between">
                        <span>
                            <i class="fa <?php echo $statusIcon; ?> <?php echo $statusClass; ?> me-1"></i>
                            <?php echo $isSent ? 'Đến' : 'Từ'; ?> <strong><?php echo CHtml::encode($partnerName); ?></strong>
                        </span>
                    </div>
                    <div class="text-muted" style="font-size:11px;"><?php echo $requestedAt ? MyHelper::formatDateTime($requestedAt) : ''; ?></div>
                    <?php if ($rejectionReason && $status == AllianceRequests::STATUS_REJECTED): ?>
                        <div class="text-danger" style="font-size:11px;"><i class="fa fa-exclamation-triangle me-1"></i><?php echo CHtml::encode($rejectionReason); ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>
