<?php
/** @var TalentEntries $e */
$categoryName = !empty($e->category_name) ? $e->category_name : '';
$propertyName = !empty($e->property_name) ? $e->property_name : '';
$duration = $e->duration_seconds ? gmdate('i:s', $e->duration_seconds) : '';
?>
<div class="col-xl-3 col-lg-4 col-md-6 mb-4">
    <div class="card talent-card h-100" data-id="<?php echo $e->id; ?>" data-show-id="<?php echo CHtml::encode($e->show_id); ?>">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <span class="badge bg-primary"><?php echo CHtml::encode($categoryName); ?></span>
            <?php echo TalentEntries::getStatusLabel($e->status); ?>
        </div>
        <div class="card-body">
            <h5 class="card-title mb-2"><?php echo CHtml::encode($e->title); ?></h5>
            <p class="card-text text-muted mb-1">
                <i class="fa fa-building me-1"></i><?php echo CHtml::encode($propertyName); ?>
            </p>
            <?php if ($e->is_alliance_team): ?>
                <p class="card-text small mb-1">
                    <span class="badge bg-warning text-dark"><i class="fa fa-users me-1"></i>Đội liên quân</span>
                </p>
            <?php endif; ?>
            <?php if ($duration): ?>
                <p class="card-text small mb-1">
                    <i class="fa fa-clock-o me-1"></i><?php echo $duration; ?>
                </p>
            <?php endif; ?>
            <?php if (!empty($e->participant_count)): ?>
                <p class="card-text small mb-1">
                    <i class="fa fa-user me-1"></i><?php echo $e->participant_count; ?> người
                </p>
            <?php endif; ?>
            <p class="card-text mb-1">
                <?php if (!empty($e->video_path)): ?>
                    <span class="badge bg-success"><i class="fa fa-check-circle me-1"></i>Đã upload video</span>
                <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="fa fa-exclamation-triangle me-1"></i>Chưa upload video</span>
                <?php endif; ?>
            </p>
            <?php if (!empty($e->description)): ?>
                <p class="card-text small text-muted mt-2 entry-description"><?php echo CHtml::encode(mb_substr($e->description, 0, 100)); ?><?php echo mb_strlen($e->description) > 100 ? '...' : ''; ?></p>
            <?php endif; ?>
        </div>
        <div class="card-footer bg-transparent">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-info flex-fill btn-view-detail" data-id="<?php echo $e->id; ?>">
                    <i class="fa fa-eye me-1"></i>Chi tiết
                </button>
                <?php if ($e->status != TalentEntries::STATUS_APPROVED): ?>
                    <button type="button" class="btn btn-sm btn-success btn-approve" data-id="<?php echo $e->id; ?>" data-show-id="<?php echo CHtml::encode($e->show_id); ?>" title="Duyệt">
                        <i class="fa fa-check"></i>
                    </button>
                <?php endif; ?>
                <?php if ($e->status != TalentEntries::STATUS_REJECTED): ?>
                    <button type="button" class="btn btn-sm btn-danger btn-reject" data-id="<?php echo $e->id; ?>" title="Từ chối">
                        <i class="fa fa-times"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
