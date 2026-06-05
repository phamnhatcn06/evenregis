<?php

/**
 * Hiển thị tất cả đội thể thao theo bộ môn
 * @var string $sportName Tên môn thể thao
 * @var string $eventName Tên sự kiện
 * @var array $teamsByProperty Đội nhóm theo đơn vị [{property_name, teams: [...]}]
 */
?>
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="fa fa-futbol-o me-2"></i>
            <?php echo CHtml::encode($sportName); ?> - <?php echo CHtml::encode($eventName); ?>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($teamsByProperty)): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle me-2"></i>Chưa có đội đăng ký môn này.
            </div>
        <?php else: ?>
            <div class="row mb-3">
                <div class="col-md-4 d-flex align-items-center">
                    <label for="filter-property-sport" class="form-label mb-0 me-2 text-nowrap fw-semibold">
                        Lọc theo đơn vị:
                    </label>
                    <select id="filter-property-sport" class="form-select form-select-sm">
                        <option value="">-- Tất cả đơn vị --</option>
                        <?php foreach ($teamsByProperty as $propData): ?>
                            <option value="<?php echo CHtml::encode($propData['property_name']); ?>">
                                <?php echo CHtml::encode($propData['property_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Đơn vị đăng ký</th>
                            <th>Tên đội</th>
                            <th style="width:100px">Liên quân?</th>
                            <th style="width:120px">Trạng thái</th>
                            <th style="width:80px">SL</th>
                            <th style="width:100px">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $index = 1; ?>
                        <?php foreach ($teamsByProperty as $propData): ?>
                            <?php foreach ($propData['teams'] as $team): ?>
                                <tr class="team-row" data-property="<?php echo CHtml::encode($propData['property_name']); ?>">
                                    <td class="row-index"><?php echo $index++; ?></td>
                                    <td><?php echo CHtml::encode($propData['property_name']); ?></td>
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
                                    <td><?php echo SportTeams::getStatusLabel($team['status']); ?></td>
                                    <td><?php echo isset($team['member_count']) ? $team['member_count'] : '-'; ?></td>
                                    <td>
                                        <a target="_blank" href="<?php echo Yii::app()->createUrl('/admin/sportTeams/view', array('id' => $team['id'])); ?>" class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                <strong>Tổng:</strong> <?php
                                        $total = 0;
                                        foreach ($teamsByProperty as $propData) {
                                            $total += count($propData['teams']);
                                        }
                                        $originalText = $total . ' đội từ ' . count($teamsByProperty) . ' đơn vị';
                                        ?>
                <span id="total-teams-text" data-original="<?php echo CHtml::encode($originalText); ?>">
                    <?php echo CHtml::encode($originalText); ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    var filterSelect = document.getElementById('filter-property-sport');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            var selectedVal = this.value;
            var rows = document.querySelectorAll('.team-row');
            var idx = 1;
            
            rows.forEach(function(row) {
                if (!selectedVal || row.getAttribute('data-property') === selectedVal) {
                    row.style.display = '';
                    var idxCol = row.querySelector('.row-index');
                    if (idxCol) {
                        idxCol.textContent = idx++;
                    }
                } else {
                    row.style.display = 'none';
                }
            });
            
            var totalCount = idx - 1;
            var totalText = document.getElementById('total-teams-text');
            if (totalText) {
                if (selectedVal) {
                    totalText.textContent = totalCount + ' đội thuộc đơn vị "' + selectedVal + '"';
                } else {
                    var originalText = totalText.getAttribute('data-original');
                    totalText.textContent = originalText;
                }
            }
        });
    }
})();
</script>