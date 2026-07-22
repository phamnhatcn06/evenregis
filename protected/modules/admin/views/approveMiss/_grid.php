<?php
/**
 * Lưới thẻ thí sinh cho một tab (vòng thi)
 * @var array $contestants Danh sách thí sinh của vòng thi
 */
?>
<div class="row">
    <?php if (empty($contestants)): ?>
        <div class="col-12">
            <div class="alert alert-info mb-0">Không có thí sinh nào trong vòng thi này.</div>
        </div>
    <?php else: ?>
        <?php foreach ($contestants as $c): ?>
            <?php
            $photoUrl = '';
            if (!empty($c->photo_portrait)) {
                $photoUrl = $c->photo_portrait;
            } elseif (!empty($c->photo_full_body)) {
                $photoUrl = $c->photo_full_body;
            }

            // Convert to thumbnail URL (w=500) using MissFileController
            $thumbUrl = '';
            if (!empty($photoUrl)) {
                $pos = strpos($photoUrl, '/uploads/miss/');
                if ($pos !== false) {
                    $cleanPath = substr($photoUrl, $pos + strlen('/uploads/miss/'));
                    $thumbUrl = Yii::app()->createUrl('/admin/missFile/view') . '?path=' . urlencode($cleanPath) . '&w=500';
                } else {
                    $thumbUrl = $photoUrl;
                }
            }

            $unitName = '';
            if (!empty($c->property_name)) {
                $unitName = $c->property_name;
            } elseif (!empty($c->registration_id)) {
                $unitName = BeautyContestants::getPropertyNameByRegistrationId($c->registration_id);
            }

            $attendeeName = '';
            if (isset($c->members) && !empty($c->members)) {
                $attendeeName = $c->members[0]['attendee_name'];
            } elseif (!empty($c->attendee_name)) {
                $attendeeName = $c->attendee_name;
            }
            ?>
            <div class="col-xl-2 col-lg-4 col-md-6 mb-4">
                <div class="card contestant-card h-100" data-id="<?php echo $c->id; ?>" data-contest-id="<?php echo $c->contest_id; ?>">
                    <div class="card-img-wrapper">
                        <?php if ($thumbUrl): ?>
                            <img src="<?php echo CHtml::encode($thumbUrl); ?>" class="card-img-top contestant-photo" alt="<?php echo CHtml::encode($attendeeName); ?>" loading="lazy">
                        <?php else: ?>
                            <div class="card-img-top contestant-photo-placeholder">
                                <i class="fa fa-user fa-4x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="compare-checkbox">
                            <input type="checkbox" class="form-check-input compare-check" data-id="<?php echo $c->id; ?>" title="Chọn so sánh">
                        </div>
                        <div class="status-badge">
                            <?php echo BeautyContestants::getStatusLabel($c->status); ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title mb-2"><?php echo CHtml::encode($attendeeName); ?></h5>
                        <p class="card-text text-muted mb-1">
                            <i class="fa fa-building me-1"></i><?php echo CHtml::encode($unitName); ?>
                        </p>
                        <?php if (!empty($c->height_cm) || !empty($c->weight_kg)): ?>
                            <p class="card-text small mb-1">
                                <?php if (!empty($c->height_cm)): ?>
                                    <span class="me-2"><i class="fa fa-arrows-v me-1"></i><?php echo $c->height_cm; ?> cm</span>
                                <?php endif; ?>
                                <?php if (!empty($c->weight_kg)): ?>
                                    <span><i class="fa fa-balance-scale me-1"></i><?php echo $c->weight_kg; ?> kg</span>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($c->measurements)): ?>
                            <p class="card-text small mb-1">
                                <i class="fa fa-circle-o me-1"></i><?php echo CHtml::encode($c->measurements); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-info flex-fill btn-view-detail" data-id="<?php echo $c->id; ?>">
                                <i class="fa fa-eye me-1"></i>Chi tiết
                            </button>
                            <?php if ($c->status != BeautyContestants::STATUS_CONFIRMED): ?>
                                <button type="button" class="btn btn-sm btn-success btn-approve" data-id="<?php echo $c->id; ?>" title="Duyệt">
                                    <i class="fa fa-check"></i>
                                </button>
                            <?php endif; ?>
                            <?php if ($c->status != BeautyContestants::STATUS_DISQUALIFIED): ?>
                                <button type="button" class="btn btn-sm btn-danger btn-reject" data-id="<?php echo $c->id; ?>" title="Từ chối">
                                    <i class="fa fa-times"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
