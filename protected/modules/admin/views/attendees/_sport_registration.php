<?php
/**
 * Partial view: Sport Registration - BR-REG-05
 * Đăng ký môn thể thao với giới hạn số môn root
 *
 * @var Attendees $attendee
 * @var Events $event
 * @var array $sports Danh sách môn thể thao
 * @var array $registeredSportIds Các môn đã đăng ký
 */

$maxSports = isset($event->max_sports_per_attendee) ? (int)$event->max_sports_per_attendee : 3;
$sportCount = RegistrationValidator::countRootSportsRegistered($attendee->id);
$remaining = max(0, $maxSports - $sportCount);

Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/sport-registration.js',
    CClientScript::POS_END
);
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa fa-futbol-o"></i> Đăng ký thể thao
        </h5>
        <div>
            <span class="badge bg-primary" id="current-sport-count">
                <?php echo $sportCount; ?>/<?php echo $maxSports; ?> môn
            </span>
        </div>
    </div>
    <div class="card-body"
         id="sport-registration-container"
         data-max-sports="<?php echo $maxSports; ?>"
         data-attendee-id="<?php echo $attendee->id; ?>"
         data-event-id="<?php echo $event->id; ?>"
         data-api-url="<?php echo Yii::app()->params['externalApiUrl']; ?>"
         data-api-key="<?php echo Yii::app()->params['externalApiKey']; ?>">

        <!-- Cảnh báo giới hạn -->
        <div id="sport-limit-warning"
             class="alert <?php echo $remaining <= 0 ? 'alert-danger' : ($remaining === 1 ? 'alert-warning' : 'alert-info'); ?>"
             style="<?php echo $remaining < $maxSports ? '' : 'display:none;'; ?>">
            <?php if ($remaining <= 0): ?>
                <i class="fa fa-exclamation-circle"></i>
                Đã đạt giới hạn <?php echo $maxSports; ?> môn thể thao
            <?php elseif ($remaining === 1): ?>
                <i class="fa fa-exclamation-triangle"></i>
                Còn có thể đăng ký 1 môn nữa
            <?php else: ?>
                <i class="fa fa-info-circle"></i>
                Còn có thể đăng ký <span id="remaining-sports"><?php echo $remaining; ?></span> môn
            <?php endif; ?>
        </div>

        <!-- Danh sách môn thể thao -->
        <div class="row">
            <?php
            $groupedSports = array();
            foreach ($sports as $sport) {
                $parentId = $sport->parent_id ? $sport->parent_id : $sport->id;
                if (!isset($groupedSports[$parentId])) {
                    $groupedSports[$parentId] = array('parent' => null, 'children' => array());
                }
                if (!$sport->parent_id) {
                    $groupedSports[$parentId]['parent'] = $sport;
                } else {
                    $groupedSports[$parentId]['children'][] = $sport;
                }
            }
            ?>

            <?php foreach ($groupedSports as $rootId => $group): ?>
                <?php
                $rootSport = $group['parent'];
                $children = $group['children'];
                $hasChildren = !empty($children);
                $rootName = $rootSport ? $rootSport->name : 'Không xác định';

                $isRootRegistered = false;
                if ($hasChildren) {
                    foreach ($children as $child) {
                        if (in_array($child->id, $registeredSportIds)) {
                            $isRootRegistered = true;
                            break;
                        }
                    }
                } else {
                    $isRootRegistered = in_array($rootId, $registeredSportIds);
                }

                $canRegisterNew = $isRootRegistered || $remaining > 0;
                ?>

                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card h-100 sport-group <?php echo $canRegisterNew ? '' : 'border-secondary'; ?>">
                        <div class="card-header bg-light py-2">
                            <strong><?php echo CHtml::encode($rootName); ?></strong>
                            <?php if ($isRootRegistered): ?>
                                <span class="badge bg-success float-end">Đã đăng ký</span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body p-2">
                            <?php if ($hasChildren): ?>
                                <?php foreach ($children as $child): ?>
                                    <?php
                                    $isChecked = in_array($child->id, $registeredSportIds);
                                    $isDisabled = !$canRegisterNew && !$isChecked;
                                    ?>
                                    <div class="form-check sport-item <?php echo $isDisabled ? 'disabled' : ''; ?>">
                                        <input class="form-check-input sport-checkbox"
                                               type="checkbox"
                                               name="SportRegistration[]"
                                               value="<?php echo $child->id; ?>"
                                               id="sport_<?php echo $child->id; ?>"
                                               data-sport-id="<?php echo $child->id; ?>"
                                               data-root-sport-id="<?php echo $rootId; ?>"
                                               <?php echo $isChecked ? 'checked' : ''; ?>
                                               <?php echo $isDisabled ? 'disabled' : ''; ?>>
                                        <label class="form-check-label <?php echo $isDisabled ? 'text-muted' : ''; ?>"
                                               for="sport_<?php echo $child->id; ?>">
                                            <?php echo CHtml::encode($child->name); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php
                                $isChecked = in_array($rootId, $registeredSportIds);
                                $isDisabled = !$canRegisterNew && !$isChecked;
                                ?>
                                <div class="form-check sport-item <?php echo $isDisabled ? 'disabled' : ''; ?>">
                                    <input class="form-check-input sport-checkbox"
                                           type="checkbox"
                                           name="SportRegistration[]"
                                           value="<?php echo $rootId; ?>"
                                           id="sport_<?php echo $rootId; ?>"
                                           data-sport-id="<?php echo $rootId; ?>"
                                           data-root-sport-id="<?php echo $rootId; ?>"
                                           <?php echo $isChecked ? 'checked' : ''; ?>
                                           <?php echo $isDisabled ? 'disabled' : ''; ?>>
                                    <label class="form-check-label <?php echo $isDisabled ? 'text-muted' : ''; ?>"
                                           for="sport_<?php echo $rootId; ?>">
                                        Đăng ký tham gia
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <hr>

        <p class="text-muted small mb-0">
            <i class="fa fa-info-circle"></i>
            <strong>Lưu ý:</strong> Mỗi người tham dự tối đa <?php echo $maxSports; ?> môn thể thao.
            Các môn con (VD: Bóng đá nam, Bóng đá nữ) được tính chung vào 1 môn gốc (Bóng đá).
        </p>
    </div>
</div>
