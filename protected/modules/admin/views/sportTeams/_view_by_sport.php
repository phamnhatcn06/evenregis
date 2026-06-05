<?php

/**
 * Hiển thị tất cả đội thể thao theo bộ môn, phân chia theo cụm (khu vực)
 * @var string $sportName Tên môn thể thao
 * @var string $eventName Tên sự kiện
 * @var array $teamsByRegion Đội nhóm theo khu vực [{region_name, properties: [{property_name, teams: [...]}]}]
 * @var array $regionList Danh sách khu vực để filter
 */
?>
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <?php echo CHtml::encode($sportName); ?> - <?php echo CHtml::encode($eventName); ?>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($teamsByRegion)): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle me-2"></i>Chưa có đội đăng ký môn này.
            </div>
        <?php else: ?>
            <div class="row mb-3">
                <div class="col-md-4 d-flex align-items-center">
                    <label for="filter-region-sport" class="form-label mb-0 me-2 text-nowrap fw-semibold">
                        Lọc theo cụm:
                    </label>
                    <select id="filter-region-sport" class="form-select form-select-sm">
                        <option value="">-- Tất cả cụm --</option>
                        <?php foreach ($regionList as $regionId => $regionName): ?>
                            <option value="<?php echo CHtml::encode($regionId); ?>">
                                <?php echo CHtml::encode($regionName); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-center">
                    <label for="filter-property-sport" class="form-label mb-0 me-2 text-nowrap fw-semibold">
                        Lọc theo đơn vị:
                    </label>
                    <select id="filter-property-sport" class="form-select form-select-sm">
                        <option value="">-- Tất cả đơn vị --</option>
                        <?php foreach ($teamsByRegion as $regionData): ?>
                            <?php foreach ($regionData['properties'] as $propData): ?>
                                <option value="<?php echo CHtml::encode($propData['property_name']); ?>">
                                    <?php echo CHtml::encode($propData['property_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <?php
            $globalIndex = 1;
            foreach ($teamsByRegion as $regionData):
            ?>
                <div class="region-block mb-4" data-region-id="<?php echo CHtml::encode($regionData['region_id']); ?>">
                    <h5 class="bg-light p-2 rounded border-start border-4 border-primary mb-3">
                        <i class="fa fa-map-marker me-2"></i>
                        <?php echo CHtml::encode($regionData['region_name']); ?>
                        <span class="badge bg-primary ms-2 region-team-count">
                            <?php
                            $regionTeamCount = 0;
                            foreach ($regionData['properties'] as $propData) {
                                $regionTeamCount += count($propData['teams']);
                            }
                            echo $regionTeamCount . ' đội';
                            ?>
                        </span>
                    </h5>

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
                                <?php foreach ($regionData['properties'] as $propData): ?>
                                    <?php foreach ($propData['teams'] as $team): ?>
                                        <tr class="team-row"
                                            data-property="<?php echo CHtml::encode($propData['property_name']); ?>"
                                            data-region-id="<?php echo CHtml::encode($regionData['region_id']); ?>">
                                            <td class="row-index"><?php echo $globalIndex++; ?></td>
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
                                                <button type="button" class="btn btn-sm btn-info btn-view-team" data-team-id="<?php echo $team['id']; ?>" title="Xem chi tiết" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
                                                    <?php echo IconHelper::render('view', 'icon-20', 20); ?>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="mt-3">
                <strong>Tổng:</strong>
                <?php
                $total = 0;
                $totalProperties = 0;
                foreach ($teamsByRegion as $regionData) {
                    foreach ($regionData['properties'] as $propData) {
                        $total += count($propData['teams']);
                        $totalProperties++;
                    }
                }
                $originalText = $total . ' đội từ ' . $totalProperties . ' đơn vị thuộc ' . count($teamsByRegion) . ' cụm';
                ?>
                <span id="total-teams-text" data-original="<?php echo CHtml::encode($originalText); ?>">
                    <?php echo CHtml::encode($originalText); ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal xem chi tiết đội -->
<div class="modal fade" id="modalViewTeam" tabindex="-1" aria-labelledby="modalViewTeamLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalViewTeamLabel">Chi tiết đội thi đấu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body" id="modalViewTeamBody">
                <div class="text-center py-4">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                    <p class="mt-2">Đang tải...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function() {
        var ajaxViewUrl = '<?php echo Yii::app()->createUrl("/admin/sportTeams/ajaxView"); ?>';

        // View team modal
        document.querySelectorAll('.btn-view-team').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var teamId = this.getAttribute('data-team-id');
                var modalBody = document.getElementById('modalViewTeamBody');
                var modal = new bootstrap.Modal(document.getElementById('modalViewTeam'));

                modalBody.innerHTML = '<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">Đang tải...</p></div>';
                modal.show();

                fetch(ajaxViewUrl + '?id=' + teamId, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function(res) {
                        return res.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            var d = data.data;
                            var html = '<div class="row">';
                            html += '<div class="col-md-5">';
                            html += '<h6 class="border-bottom pb-2 mb-3"><i class="fa fa-info-circle me-2"></i>Thông tin đội</h6>';
                            html += '<table class="table table-sm table-bordered">';
                            html += '<tr><th style="width:40%;background:#f8f9fa;">Tên đội</th><td>' + (d.team_name || '-') + '</td></tr>';
                            html += '<tr><th style="background:#f8f9fa;">Môn thể thao</th><td>' + (d.sport_name || '-') + '</td></tr>';
                            html += '<tr><th style="background:#f8f9fa;">Đơn vị</th><td>' + (d.property_name || '-') + '</td></tr>';
                            html += '<tr><th style="background:#f8f9fa;">Liên quân</th><td>' + (d.is_alliance ? '<span class="badge bg-info">Có</span>' : '<span class="badge bg-secondary">Không</span>') + '</td></tr>';
                            html += '<tr><th style="background:#f8f9fa;">Trạng thái</th><td>' + (d.status_label || '-') + '</td></tr>';
                            html += '</table></div>';

                            html += '<div class="col-md-7">';
                            html += '<h6 class="border-bottom pb-2 mb-3"><i class="fa fa-users me-2"></i>Danh sách thành viên (' + d.members.length + ')</h6>';
                            if (d.members.length > 0) {
                                html += '<div class="table-responsive" style="max-height:300px;overflow-y:auto;">';
                                html += '<table class="table table-sm table-bordered table-hover">';
                                html += '<thead class="table-light sticky-top"><tr><th style="width:40px;">#</th><th>Họ tên</th><th>Chức danh</th><th>Đơn vị</th></tr></thead><tbody>';
                                d.members.forEach(function(m, i) {
                                    html += '<tr><td>' + (i + 1) + '</td><td>' + (m.name || '-') + '</td><td>' + (m.position || '-') + '</td><td>' + (m.property_name || '-') + '</td></tr>';
                                });
                                html += '</tbody></table></div>';
                            } else {
                                html += '<p class="text-muted text-center">Chưa có thành viên</p>';
                            }
                            html += '</div></div>';
                            modalBody.innerHTML = html;
                        } else {
                            modalBody.innerHTML = '<div class="alert alert-danger">' + (data.message || 'Có lỗi xảy ra') + '</div>';
                        }
                    })
                    .catch(function() {
                        modalBody.innerHTML = '<div class="alert alert-danger">Lỗi kết nối server</div>';
                    });
            });
        });

        var filterRegion = document.getElementById('filter-region-sport');
        var filterProperty = document.getElementById('filter-property-sport');

        function applyFilters() {
            var selectedRegion = filterRegion ? filterRegion.value : '';
            var selectedProperty = filterProperty ? filterProperty.value : '';
            var rows = document.querySelectorAll('.team-row');
            var regionBlocks = document.querySelectorAll('.region-block');
            var idx = 1;

            // Filter rows
            rows.forEach(function(row) {
                var rowRegion = row.getAttribute('data-region-id');
                var rowProperty = row.getAttribute('data-property');
                var matchRegion = !selectedRegion || rowRegion === selectedRegion;
                var matchProperty = !selectedProperty || rowProperty === selectedProperty;

                if (matchRegion && matchProperty) {
                    row.style.display = '';
                    var idxCol = row.querySelector('.row-index');
                    if (idxCol) {
                        idxCol.textContent = idx++;
                    }
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide region blocks
            regionBlocks.forEach(function(block) {
                var blockRegionId = block.getAttribute('data-region-id');
                var visibleRows = block.querySelectorAll('.team-row:not([style*="display: none"])');

                if (selectedRegion && blockRegionId !== selectedRegion) {
                    block.style.display = 'none';
                } else if (visibleRows.length === 0) {
                    block.style.display = 'none';
                } else {
                    block.style.display = '';
                    // Update region team count
                    var countBadge = block.querySelector('.region-team-count');
                    if (countBadge) {
                        countBadge.textContent = visibleRows.length + ' đội';
                    }
                }
            });

            // Update total text
            var totalCount = idx - 1;
            var totalText = document.getElementById('total-teams-text');
            if (totalText) {
                if (selectedRegion || selectedProperty) {
                    var filterDesc = [];
                    if (selectedRegion) {
                        var regionOption = filterRegion.options[filterRegion.selectedIndex];
                        filterDesc.push('cụm "' + regionOption.text + '"');
                    }
                    if (selectedProperty) {
                        filterDesc.push('đơn vị "' + selectedProperty + '"');
                    }
                    totalText.textContent = totalCount + ' đội thuộc ' + filterDesc.join(', ');
                } else {
                    totalText.textContent = totalText.getAttribute('data-original');
                }
            }
        }

        if (filterRegion) {
            filterRegion.addEventListener('change', function() {
                // Reset property filter when region changes
                if (filterProperty) {
                    filterProperty.value = '';
                }
                applyFilters();
            });
        }

        if (filterProperty) {
            filterProperty.addEventListener('change', applyFilters);
        }
    })();
</script>