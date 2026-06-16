<?php
/**
 * Hiển thị tất cả thí sinh thi nghiệp vụ theo cuộc thi, phân chia theo cụm (khu vực)
 * @var string $competitionName Tên cuộc thi
 * @var string $eventName Tên sự kiện
 * @var int $competitionId ID cuộc thi hiện tại
 * @var int $eventId ID sự kiện hiện tại
 * @var array $contestantsByRegion Thí sinh nhóm theo khu vực
 * @var array $regionList Danh sách khu vực để filter
 * @var array $competitionsList Danh sách cuộc thi
 */
?>
<style>
.table-fixed-cols { table-layout: fixed; width: 100%; }
.table-fixed-cols td, .table-fixed-cols th { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
</style>
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0 text-white">
            <?php echo CHtml::encode($competitionName); ?> - <?php echo CHtml::encode($eventName); ?>
        </h5>
    </div>
    <div class="card-body">
        <div class="row mb-3 g-2">
            <div class="col-auto d-flex align-items-center">
                <label for="filter-change-competition" class="form-label mb-0 me-2 text-nowrap fw-semibold">Nghiệp vụ:</label>
                <select id="filter-change-competition" class="form-select form-select-sm" style="min-width:200px;">
                    <?php foreach ($competitionsList as $item): ?>
                        <option value="<?php echo CHtml::encode($item['id']); ?>" <?php echo $item['id'] == $competitionId ? 'selected="selected"' : ''; ?>>
                            <?php echo CHtml::encode($item['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!empty($contestantsByRegion)): ?>
            <div class="col-auto d-flex align-items-center">
                <label for="filter-region" class="form-label mb-0 me-2 text-nowrap fw-semibold">Cụm:</label>
                <select id="filter-region" class="form-select form-select-sm" style="min-width:140px;">
                    <option value="">-- Tất cả cụm --</option>
                    <?php foreach ($regionList as $regionId => $regionName): ?>
                        <option value="<?php echo CHtml::encode($regionId); ?>"><?php echo CHtml::encode($regionName); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto d-flex align-items-center">
                <label for="filter-property" class="form-label mb-0 me-2 text-nowrap fw-semibold">Đơn vị:</label>
                <select id="filter-property" class="form-select form-select-sm" style="min-width:180px;">
                    <option value="">-- Chọn cụm trước --</option>
                </select>
            </div>
            <?php endif; ?>
        </div>

        <?php if (empty($contestantsByRegion)): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle me-2"></i>Chưa có thí sinh đăng ký thi nghiệp vụ này.
            </div>
        <?php else: ?>

            <?php
            $globalIndex = 1;
            foreach ($contestantsByRegion as $regionData):
                // Đếm số đội (unique registration_id) và số người
                $regionTeamIds = array();
                $regionMemberCount = 0;
                foreach ($regionData['properties'] as $propData) {
                    foreach ($propData['contestants'] as $contestant) {
                        $regionMemberCount++;
                        $regId = isset($contestant['registration_id']) ? $contestant['registration_id'] : null;
                        if ($regId) {
                            $regionTeamIds[$regId] = true;
                        }
                    }
                }
                $regionTeamCount = count($regionTeamIds);
            ?>
                <div class="region-block mb-4" data-region-id="<?php echo CHtml::encode($regionData['region_id']); ?>">
                    <h5 class="bg-light p-2 rounded border-start border-4 border-primary mb-3">
                        <i class="fa fa-map-marker me-2"></i>
                        <?php echo CHtml::encode($regionData['region_name']); ?>
                        <span class="badge bg-primary ms-2"><?php echo $regionTeamCount; ?> đội</span>
                        <span class="badge bg-success ms-1"><?php echo $regionMemberCount; ?> người</span>
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:4%">STT</th>
                                    <th style="width:10%">Tên đội</th>
                                    <th style="width:20%">Họ tên</th>
                                    <th style="width:18%">Đơn vị</th>
                                    <th style="width:15%">Chức danh</th>
                                    <th style="width:28%">Nghiệp vụ đăng ký</th>
                                    <th style="width:5%"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($regionData['properties'] as $propData):
                                    // Sắp xếp contestants theo registration_id để cùng đội nằm liền nhau
                                    $sortedContestants = $propData['contestants'];
                                    usort($sortedContestants, function($a, $b) {
                                        $regA = isset($a['registration_id']) ? $a['registration_id'] : 0;
                                        $regB = isset($b['registration_id']) ? $b['registration_id'] : 0;
                                        return $regA - $regB;
                                    });

                                    // Track registration_id đã render để dùng rowspan
                                    $renderedRegIds = array();
                                ?>
                                    <?php foreach ($sortedContestants as $idx => $contestant):
                                        $regId = isset($contestant['registration_id']) ? $contestant['registration_id'] : null;
                                        $memberCount = isset($contestant['member_count']) ? $contestant['member_count'] : 1;
                                        $isFirstOfTeam = $regId && !isset($renderedRegIds[$regId]);
                                        if ($regId) {
                                            $renderedRegIds[$regId] = true;
                                        }
                                    ?>
                                        <tr class="contestant-row"
                                            data-property="<?php echo CHtml::encode($propData['property_name']); ?>"
                                            data-region-id="<?php echo CHtml::encode($regionData['region_id']); ?>"
                                            data-status="<?php echo $contestant['status']; ?>"
                                            data-team-name="<?php echo CHtml::encode($contestant['team_name']); ?>"
                                            data-department="<?php echo CHtml::encode(isset($contestant['attendee_department']) ? $contestant['attendee_department'] : ''); ?>"
                                            data-position="<?php echo CHtml::encode(isset($contestant['attendee_position']) ? $contestant['attendee_position'] : ''); ?>">
                                            <td class="row-index"><?php echo $globalIndex++; ?></td>
                                            <?php if ($isFirstOfTeam && $memberCount > 1): ?>
                                                <td rowspan="<?php echo $memberCount; ?>" class="align-middle text-center" style="background:#f8f9fa;">
                                                    <?php if (!empty($contestant['team_name'])): ?>
                                                        <span class="badge bg-primary"><?php echo CHtml::encode($contestant['team_name']); ?></span>
                                                        <div class="small text-muted mt-1"><?php echo $memberCount; ?> người</div>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php elseif (!$regId || $memberCount <= 1): ?>
                                                <td class="align-middle text-center">
                                                    <?php if (!empty($contestant['team_name'])): ?>
                                                        <span class="badge bg-secondary"><?php echo CHtml::encode($contestant['team_name']); ?></span>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>
                                            <td><?php echo CHtml::encode($contestant['attendee_name']); ?></td>
                                            <td><?php echo CHtml::encode($propData['property_name']); ?></td>
                                            <td><?php echo CHtml::encode($contestant['attendee_position']); ?></td>
                                            <td>
                                                <?php
                                                $competitions = isset($contestant['registered_competitions']) ? $contestant['registered_competitions'] : array();
                                                if (!empty($competitions)) {
                                                    foreach ($competitions as $compName) {
                                                        echo '<span class="badge bg-info me-1 mb-1">' . CHtml::encode($compName) . '</span>';
                                                    }
                                                } else {
                                                    echo '-';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info btn-view-contestant" data-id="<?php echo $contestant['id']; ?>" title="Xem chi tiết" style="width:30px;height:30px;padding:0;display:inline-flex;align-items:center;justify-content:center;">
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
                $allTeamIds = array();
                $totalMembers = 0;
                $totalProperties = 0;
                foreach ($contestantsByRegion as $regionData) {
                    foreach ($regionData['properties'] as $propData) {
                        $totalProperties++;
                        foreach ($propData['contestants'] as $c) {
                            $totalMembers++;
                            $regId = isset($c['registration_id']) ? $c['registration_id'] : null;
                            if ($regId) {
                                $allTeamIds[$regId] = true;
                            }
                        }
                    }
                }
                $totalTeams = count($allTeamIds);
                $originalText = $totalTeams . ' đội (' . $totalMembers . ' thí sinh) từ ' . $totalProperties . ' đơn vị thuộc ' . count($contestantsByRegion) . ' cụm';
                ?>
                <span id="total-contestants-text" data-original="<?php echo CHtml::encode($originalText); ?>">
                    <?php echo CHtml::encode($originalText); ?>
                </span>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal xem chi tiết thí sinh -->
<div class="modal fade" id="modalViewContestant" tabindex="-1" aria-labelledby="modalViewContestantLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="modalViewContestantLabel">Chi tiết thí sinh</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body" id="modalViewContestantBody">
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
    var ajaxViewUrl = '<?php echo Yii::app()->createUrl("/admin/competitionRegistrations/ajaxView"); ?>';

    document.querySelectorAll('.btn-view-contestant').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var modalBody = document.getElementById('modalViewContestantBody');
            var modal = new bootstrap.Modal(document.getElementById('modalViewContestant'));

            modalBody.innerHTML = '<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">Đang tải...</p></div>';
            modal.show();

            fetch(ajaxViewUrl + '?id=' + id, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    if (data.success) {
                        var d = data.data;
                        var genderLabel = d.attendee_gender === 'male' ? 'Nam' : (d.attendee_gender === 'female' ? 'Nữ' : '-');
                        var html = '<table class="table table-bordered">';
                        html += '<tr><th style="width:35%;background:#f8f9fa;">Số báo danh</th><td><strong>' + (d.candidate_number || '-') + '</strong></td></tr>';
                        html += '<tr><th style="background:#f8f9fa;">Họ tên</th><td>' + (d.attendee_name || '-') + '</td></tr>';
                        html += '<tr><th style="background:#f8f9fa;">Đơn vị</th><td>' + (d.property_name || '-') + '</td></tr>';
                        html += '<tr><th style="background:#f8f9fa;">Chức danh</th><td>' + (d.attendee_position || '-') + '</td></tr>';
                        html += '<tr><th style="background:#f8f9fa;">Giới tính</th><td>' + genderLabel + '</td></tr>';
                        html += '<tr><th style="background:#f8f9fa;">Cuộc thi</th><td>' + (d.competition_name || '-') + '</td></tr>';
                        html += '<tr><th style="background:#f8f9fa;">Trạng thái</th><td>' + d.status_label + '</td></tr>';
                        html += '<tr><th style="background:#f8f9fa;">Ngày đăng ký</th><td>' + (d.registered_at || '-') + '</td></tr>';
                        if (d.note) {
                            html += '<tr><th style="background:#f8f9fa;">Ghi chú</th><td>' + d.note + '</td></tr>';
                        }
                        html += '</table>';
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

    var filterRegion = document.getElementById('filter-region');
    var filterProperty = document.getElementById('filter-property');

    // Dữ liệu đơn vị theo cụm
    var propertiesByRegion = <?php
        $propertiesByRegion = array();
        foreach ($contestantsByRegion as $regionData) {
            $regionId = $regionData['region_id'];
            $propertiesByRegion[$regionId] = array();
            foreach ($regionData['properties'] as $propData) {
                $propertiesByRegion[$regionId][] = $propData['property_name'];
            }
        }
        echo json_encode($propertiesByRegion);
    ?>;

    // Cập nhật dropdown đơn vị khi chọn cụm
    function updatePropertyDropdown(regionId) {
        if (!filterProperty) return;
        filterProperty.innerHTML = '';

        if (!regionId) {
            filterProperty.innerHTML = '<option value="">-- Chọn cụm trước --</option>';
            return;
        }

        var properties = propertiesByRegion[regionId] || [];
        if (properties.length === 0) {
            filterProperty.innerHTML = '<option value="">-- Không có đơn vị --</option>';
            return;
        }

        filterProperty.innerHTML = '<option value="">-- Tất cả đơn vị --</option>';
        properties.forEach(function(propName) {
            var option = document.createElement('option');
            option.value = propName;
            option.textContent = propName;
            filterProperty.appendChild(option);
        });
    }

    var filterDepartment = document.getElementById('filter-department');
    var filterPosition = document.getElementById('filter-position');

    function applyFilters() {
        var selectedRegion = filterRegion ? filterRegion.value : '';
        var selectedProperty = filterProperty ? filterProperty.value : '';
        var selectedDepartment = filterDepartment ? filterDepartment.value : '';
        var selectedPosition = filterPosition ? filterPosition.value : '';
        var rows = document.querySelectorAll('.contestant-row');
        var regionBlocks = document.querySelectorAll('.region-block');
        var idx = 1;

        rows.forEach(function(row) {
            var rowRegion = row.getAttribute('data-region-id');
            var rowProperty = row.getAttribute('data-property');
            var rowDepartment = row.getAttribute('data-department') || '';
            var rowPosition = row.getAttribute('data-position') || '';
            var matchRegion = !selectedRegion || rowRegion === selectedRegion;
            var matchProperty = !selectedProperty || rowProperty === selectedProperty;
            var matchDepartment = !selectedDepartment || rowDepartment === selectedDepartment;
            var matchPosition = !selectedPosition || rowPosition === selectedPosition;

            if (matchRegion && matchProperty && matchDepartment && matchPosition) {
                row.style.display = '';
                var idxCol = row.querySelector('.row-index');
                if (idxCol) { idxCol.textContent = idx++; }
            } else {
                row.style.display = 'none';
            }
        });

        regionBlocks.forEach(function(block) {
            var blockRegionId = block.getAttribute('data-region-id');
            var visibleRows = block.querySelectorAll('.contestant-row:not([style*="display: none"])');

            if (selectedRegion && blockRegionId !== selectedRegion) {
                block.style.display = 'none';
            } else if (visibleRows.length === 0) {
                block.style.display = 'none';
            } else {
                block.style.display = '';
            }
        });

        var totalCount = idx - 1;
        var totalText = document.getElementById('total-contestants-text');
        if (totalText) {
            if (selectedRegion || selectedProperty || selectedDepartment || selectedPosition) {
                var filterDesc = [];
                if (selectedRegion) {
                    var regionOption = filterRegion.options[filterRegion.selectedIndex];
                    filterDesc.push('cụm "' + regionOption.text + '"');
                }
                if (selectedProperty) {
                    filterDesc.push('đơn vị "' + selectedProperty + '"');
                }
                if (selectedDepartment) {
                    filterDesc.push('phòng ban "' + selectedDepartment + '"');
                }
                if (selectedPosition) {
                    filterDesc.push('chức danh "' + selectedPosition + '"');
                }
                totalText.textContent = totalCount + ' người thuộc ' + filterDesc.join(', ');
            } else {
                totalText.textContent = totalText.getAttribute('data-original');
            }
        }
    }

    if (filterRegion) {
        filterRegion.addEventListener('change', function() {
            updatePropertyDropdown(this.value);
            applyFilters();
        });
    }

    if (filterProperty) {
        filterProperty.addEventListener('change', applyFilters);
    }

    if (filterDepartment) {
        filterDepartment.addEventListener('change', applyFilters);
    }

    if (filterPosition) {
        filterPosition.addEventListener('change', applyFilters);
    }

    var filterChangeCompetition = document.getElementById('filter-change-competition');
    if (filterChangeCompetition) {
        filterChangeCompetition.addEventListener('change', function() {
            var selectedCompetitionId = this.value;
            if (selectedCompetitionId) {
                var currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('competition_id', selectedCompetitionId);
                window.location.href = currentUrl.toString();
            }
        });
    }
})();
</script>
