<?php
/**
 * Alliance section partial - shows pending requests and history for a specific content
 *
 * @var array $pendingRequests - pending alliance requests for this content
 * @var array $historyItems - alliance history for this content
 * @var Registrations $model - registration model
 * @var CController $this - controller
 */

$hasPending = !empty($pendingRequests);
$hasHistory = !empty($historyItems);

if (!$hasPending && !$hasHistory) return;
?>

<?php if ($hasPending): ?>
    <?php foreach ($pendingRequests as $item):
        $req = $item['request'];
        $reqId = $req->id;
        $requesterName = $item['requester_name'];
        $requestedAt = isset($req->requested_at) ? $req->requested_at : '';
    ?>
        <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3 p-3 rounded shadow-sm border-start border-4 border-warning alliance-request-alert">
            <div class="d-flex align-items-center">
                <i class="fa fa-handshake-o fa-2x text-warning me-3"></i>
                <div>
                    <h6 class="alert-heading mb-1 text-dark fw-bold">Yêu cầu liên quân</h6>
                    <span>Đơn vị <strong><?php echo CHtml::encode($requesterName); ?></strong> gửi yêu cầu liên quân với đơn vị của bạn.</span>
                    <?php if ($requestedAt): ?>
                        <div class="text-muted small mt-1"><i class="fa fa-clock-o me-1"></i>Gửi lúc: <?php echo MyHelper::formatDateTime($requestedAt); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex align-items-center ms-3">
                <button type="button" class="btn btn-sm btn-success text-white px-3 fw-bold me-2" onclick="confirmApproveAlliance(<?php echo $reqId; ?>)">
                    <i class="fa fa-check me-1"></i>Chấp nhận
                </button>
                <form id="approve-alliance-form-<?php echo $reqId; ?>" method="post" action="<?php echo $this->createUrl('approveAlliance', array('request_id' => $reqId, 'registration_id' => $model->id)); ?>" style="display:none;"></form>
                <button type="button" class="btn btn-sm btn-outline-danger btn-outline-danger-hover px-3 fw-bold bg-white" onclick="confirmRejectAlliance(<?php echo $reqId; ?>)">
                    <i class="fa fa-times me-1"></i>Từ chối
                </button>
                <form id="reject-alliance-form-<?php echo $reqId; ?>" method="post" action="<?php echo $this->createUrl('rejectAlliance', array('request_id' => $reqId, 'registration_id' => $model->id)); ?>" style="display:none;">
                    <input type="hidden" name="rejection_reason" id="rejection_reason_<?php echo $reqId; ?>">
                </form>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if ($hasHistory): ?>
    <div class="mb-3">
        <a class="text-muted small" data-bs-toggle="collapse" href="#allianceHistory-<?php echo $contentCode; ?>" role="button" aria-expanded="false">
            <i class="fa fa-history me-1"></i>Lịch sử liên quân (<?php echo count($historyItems); ?>)
            <i class="fa fa-chevron-down ms-1"></i>
        </a>
        <div class="collapse mt-2" id="allianceHistory-<?php echo $contentCode; ?>">
            <div class="border rounded p-2 bg-light">
                <?php foreach ($historyItems as $idx => $item):
                    $req = $item['request'];
                    $isSent = $item['type'] === 'sent';
                    $partnerName = $item['partner_name'];
                    $status = isset($req->status) ? $req->status : 0;
                    $requestedAt = isset($req->requested_at) ? $req->requested_at : '';
                    $reviewedAt = isset($req->reviewed_at) ? $req->reviewed_at : '';
                    $rejectionReason = isset($req->rejection_reason) ? $req->rejection_reason : '';

                    if ($status == AllianceRequests::STATUS_APPROVED) {
                        $statusClass = 'bg-success';
                        $statusText = 'Đã chấp nhận';
                    } elseif ($status == AllianceRequests::STATUS_REJECTED) {
                        $statusClass = 'bg-danger';
                        $statusText = 'Đã từ chối';
                    } elseif ($status == AllianceRequests::STATUS_CANCELLED) {
                        $statusClass = 'bg-secondary';
                        $statusText = 'Đã hủy';
                    } else {
                        $statusClass = 'bg-warning';
                        $statusText = 'Chờ xác nhận';
                    }
                ?>
                <div class="d-flex align-items-center small <?php echo $idx > 0 ? 'mt-2 pt-2 border-top' : ''; ?>">
                    <span class="badge <?php echo $isSent ? 'bg-info' : 'bg-primary'; ?> me-2"><?php echo $isSent ? 'Gửi' : 'Nhận'; ?></span>
                    <span class="flex-grow-1">
                        <?php echo $isSent ? 'Đến' : 'Từ'; ?> <strong><?php echo CHtml::encode($partnerName); ?></strong>
                        <span class="text-muted ms-2"><?php echo $requestedAt ? MyHelper::formatDateTime($requestedAt) : ''; ?></span>
                    </span>
                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span>
                </div>
                <?php if ($rejectionReason && $status == AllianceRequests::STATUS_REJECTED): ?>
                    <div class="small text-danger mt-1 ms-5"><i class="fa fa-exclamation-triangle me-1"></i><?php echo CHtml::encode($rejectionReason); ?></div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
