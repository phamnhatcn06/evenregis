<?php

/**
 * Hiển thị tất cả đội thể thao theo đơn vị
 * @var string $propertyName Tên đơn vị
 * @var string $eventName Tên sự kiện
 * @var array $teamsBySport Đội nhóm theo môn [{sport_name, teams: [...]}]
 */
?>
<div class="card">
    <div class="card-header bg-warning text-white">
        <h5 class="mb-0">
            <i class="fa fa-building me-2"></i>
            <?php echo CHtml::encode($propertyName); ?> - <?php echo CHtml::encode($eventName); ?>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($teamsBySport)): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle me-2"></i>Đơn vị chưa đăng ký đội thi đấu nào.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($teamsBySport as $sportData): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0">
                                    <i class="fa fa-futbol-o me-2"></i>
                                    <?php echo CHtml::encode($sportData['sport_name']); ?>
                                    <span class="badge bg-secondary ms-2"><?php echo count($sportData['teams']); ?> đội</span>
                                </h6>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Tên đội</th>
                                            <th style="width:80px">Liên quân</th>
                                            <th style="width:80px">SL</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($sportData['teams'] as $team): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?php echo Yii::app()->createUrl('/admin/sportTeams/view', array('id' => $team['id'])); ?>">
                                                        <?php echo CHtml::encode($team['team_name'] ?: $team['name']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php if ($team['is_alliance']): ?>
                                                        <span class="badge bg-info">Có</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary">Không</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo $team['member_count']; ?></td>
                                            </tr>
                                            <?php if (!empty($team['members'])): ?>
                                                <tr>
                                                    <td colspan="3" class="p-0">
                                                        <table class="table table-sm table-bordered mb-0 ms-4" style="width:calc(100% - 2rem)">
                                                            <thead class="table-light">
                                                                <tr>
                                                                    <th style="width:50px">STT</th>
                                                                    <th>Họ tên</th>
                                                                    <th>Chức danh</th>
                                                                    <th>Phòng ban</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php foreach ($team['members'] as $idx => $member): ?>
                                                                    <tr>
                                                                        <td><?php echo $idx + 1; ?></td>
                                                                        <td><?php echo CHtml::encode($member['name']); ?></td>
                                                                        <td><?php echo CHtml::encode(isset($member['attendee_position']) ? $member['attendee_position'] : ''); ?></td>
                                                                        <td><?php echo CHtml::encode($member['department']); ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>