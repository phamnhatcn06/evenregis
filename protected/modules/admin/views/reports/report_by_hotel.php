<?php
/**
 * Report By Hotel View
 * @var ReportByHotelController $this
 * @var bool $isHO
 * @var array $user
 * @var array $eventsList
 * @var string $selectedEventId
 * @var string $selectedEventName
 * @var array $reportData
 * @var array $propertyMap
 */

$this->breadcrumbs = array(
    'Báo cáo' => array('/admin/reports/admin'),
    'Theo khách sạn',
);

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl . '/assets/css/pages/reports-index.css');
?>

<div class="card">
    <div class="card-body">
        <!-- Filter & Title Section -->
        <div class="row align-items-center mb-4">
            <div class="col-md-7">
                <h3 class="mb-1 text-primary fw-bold">
                    <i class="fa fa-building me-2"></i>Báo cáo theo Khách sạn
                </h3>
                <p class="text-muted mb-0">
                    Thống kê VĐV thể thao, thí sinh thi nghiệp vụ, thi Miss và văn nghệ theo từng đơn vị
                    <br><strong class="text-secondary"><?php echo CHtml::encode($selectedEventName); ?></strong>
                </p>
            </div>
            <div class="col-md-5 mt-3 mt-md-0">
                <form method="get" id="eventFilterForm" class="d-flex justify-content-md-end gap-2">
                    <div class="flex-grow-1" style="max-width: 320px;">
                        <select name="event_id" id="eventIdFilter" class="form-select border-2 border-primary shadow-sm" onchange="document.getElementById('eventFilterForm').submit();">
                            <?php foreach ($eventsList as $eId => $event): ?>
                                <?php
                                $eName = isset($event->name) ? $event->name : (isset($event['name']) ? $event['name'] : '');
                                $selected = ($eId == $selectedEventId) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $eId; ?>" <?php echo $selected; ?>>
                                    <?php echo CHtml::encode($eName); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <a href="<?php echo $this->createUrl('export', array('event_id' => $selectedEventId, 'type' => 'all')); ?>" class="btn btn-success shadow-sm" title="Xuất tất cả">
                        <i class="fa fa-file-excel-o me-1"></i> Xuất Excel
                    </a>
                </form>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <?php
        $totalSport = 0;
        $totalCompetition = 0;
        $totalBeauty = 0;
        $totalTalent = 0;
        foreach ($reportData as $propId => $data) {
            $totalSport += count($data['sport_athletes']);
            $totalCompetition += count($data['competition_contestants']);
            $totalBeauty += count($data['beauty_contestants']);
            $totalTalent += count($data['talent_entries']);
        }
        ?>
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm bg-gradient-blue h-100 text-white">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-white-50 small fw-bold">VĐV Thể thao</span>
                            <h3 class="mb-0 mt-1 fw-bold text-white"><?php echo number_format($totalSport); ?></h3>
                        </div>
                        <div class="bg-white-20 rounded-3 p-2">
                            <i class="fa fa-futbol-o fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm bg-gradient-teal h-100 text-white">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-white-50 small fw-bold">Thi nghiệp vụ</span>
                            <h3 class="mb-0 mt-1 fw-bold text-white"><?php echo number_format($totalCompetition); ?></h3>
                        </div>
                        <div class="bg-white-20 rounded-3 p-2">
                            <i class="fa fa-graduation-cap fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm bg-gradient-purple h-100 text-white">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-white-50 small fw-bold">Thi Miss</span>
                            <h3 class="mb-0 mt-1 fw-bold text-white"><?php echo number_format($totalBeauty); ?></h3>
                        </div>
                        <div class="bg-white-20 rounded-3 p-2">
                            <i class="fa fa-female fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm bg-gradient-orange h-100 text-white">
                    <div class="card-body d-flex align-items-center justify-content-between p-3">
                        <div>
                            <span class="text-white-50 small fw-bold">Tiết mục văn nghệ</span>
                            <h3 class="mb-0 mt-1 fw-bold text-white"><?php echo number_format($totalTalent); ?></h3>
                        </div>
                        <div class="bg-white-20 rounded-3 p-2">
                            <i class="fa fa-music fa-2x text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Filter -->
        <div class="mb-3">
            <input type="text" id="searchProperty" class="form-control" placeholder="Tìm kiếm theo tên hoặc mã đơn vị..." style="max-width: 400px;">
        </div>

        <?php if (empty($reportData)): ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle me-2"></i>
                Không có dữ liệu đăng ký cho sự kiện này.
            </div>
        <?php else: ?>
            <!-- Accordion for each property -->
            <div class="accordion" id="accordionProperties">
                <?php $index = 0; foreach ($reportData as $propId => $data): $index++; ?>
                    <?php
                    $sportCount = count($data['sport_athletes']);
                    $compCount = count($data['competition_contestants']);
                    $beautyCount = count($data['beauty_contestants']);
                    $talentCount = count($data['talent_entries']);
                    ?>
                    <div class="accordion-item property-item mb-2 border rounded shadow-sm" data-code="<?php echo CHtml::encode(strtolower($data['code'])); ?>" data-name="<?php echo CHtml::encode(strtolower($data['name'])); ?>">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed py-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $propId; ?>">
                                <div class="d-flex align-items-center gap-3 w-100">
                                    <span class="badge bg-primary"><?php echo CHtml::encode($data['code']); ?></span>
                                    <strong><?php echo CHtml::encode($data['name']); ?></strong>
                                    <div class="ms-auto d-flex gap-2 me-3">
                                        <?php if ($sportCount > 0): ?>
                                            <span class="badge bg-info" title="VĐV Thể thao"><i class="fa fa-futbol-o"></i> <?php echo $sportCount; ?></span>
                                        <?php endif; ?>
                                        <?php if ($compCount > 0): ?>
                                            <span class="badge bg-success" title="Thi nghiệp vụ"><i class="fa fa-graduation-cap"></i> <?php echo $compCount; ?></span>
                                        <?php endif; ?>
                                        <?php if ($beautyCount > 0): ?>
                                            <span class="badge bg-danger" title="Thi Miss"><i class="fa fa-female"></i> <?php echo $beautyCount; ?></span>
                                        <?php endif; ?>
                                        <?php if ($talentCount > 0): ?>
                                            <span class="badge bg-warning text-dark" title="Văn nghệ"><i class="fa fa-music"></i> <?php echo $talentCount; ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </button>
                        </h2>
                        <div id="collapse<?php echo $propId; ?>" class="accordion-collapse collapse" data-bs-parent="#accordionProperties">
                            <div class="accordion-body">
                                <!-- Tabs for each category -->
                                <ul class="nav nav-tabs nav-fill mb-3" role="tablist">
                                    <li class="nav-item">
                                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sport<?php echo $propId; ?>">
                                            <i class="fa fa-futbol-o me-1"></i> VĐV Thể thao (<?php echo $sportCount; ?>)
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#comp<?php echo $propId; ?>">
                                            <i class="fa fa-graduation-cap me-1"></i> Thi nghiệp vụ (<?php echo $compCount; ?>)
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#beauty<?php echo $propId; ?>">
                                            <i class="fa fa-female me-1"></i> Thi Miss (<?php echo $beautyCount; ?>)
                                        </button>
                                    </li>
                                    <li class="nav-item">
                                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#talent<?php echo $propId; ?>">
                                            <i class="fa fa-music me-1"></i> Văn nghệ (<?php echo $talentCount; ?>)
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content">
                                    <!-- Sport Athletes Tab -->
                                    <div class="tab-pane fade show active" id="sport<?php echo $propId; ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 text-primary"><i class="fa fa-futbol-o me-1"></i> Danh sách VĐV tham gia thể thao</h6>
                                            <?php if ($sportCount > 0): ?>
                                                <a href="<?php echo $this->createUrl('exportByHotel', array('event_id' => $selectedEventId, 'property_id' => $propId, 'type' => 'sport')); ?>" class="btn btn-sm btn-outline-success">
                                                    <i class="fa fa-download"></i> Xuất Excel
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (empty($data['sport_athletes'])): ?>
                                            <p class="text-muted fst-italic">Không có VĐV nào</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover table-bordered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="50">STT</th>
                                                            <th>Họ tên</th>
                                                            <th>Phòng ban - Chức danh</th>
                                                            <th>Bộ môn</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $stt = 1; foreach ($data['sport_athletes'] as $athlete): ?>
                                                            <tr>
                                                                <td class="text-center"><?php echo $stt++; ?></td>
                                                                <td><?php echo CHtml::encode($athlete['full_name']); ?></td>
                                                                <td><?php echo CHtml::encode($athlete['position']); ?></td>
                                                                <td><span class="badge bg-info"><?php echo CHtml::encode($athlete['sport_name']); ?></span></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Competition Tab -->
                                    <div class="tab-pane fade" id="comp<?php echo $propId; ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 text-success"><i class="fa fa-graduation-cap me-1"></i> Danh sách thí sinh thi nghiệp vụ</h6>
                                            <?php if ($compCount > 0): ?>
                                                <a href="<?php echo $this->createUrl('exportByHotel', array('event_id' => $selectedEventId, 'property_id' => $propId, 'type' => 'competition')); ?>" class="btn btn-sm btn-outline-success">
                                                    <i class="fa fa-download"></i> Xuất Excel
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (empty($data['competition_contestants'])): ?>
                                            <p class="text-muted fst-italic">Không có thí sinh nào</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover table-bordered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="50">STT</th>
                                                            <th>Họ tên</th>
                                                            <th>Phòng ban - Chức danh</th>
                                                            <th>Cuộc thi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $stt = 1; foreach ($data['competition_contestants'] as $contestant): ?>
                                                            <tr>
                                                                <td class="text-center"><?php echo $stt++; ?></td>
                                                                <td><?php echo CHtml::encode($contestant['full_name']); ?></td>
                                                                <td><?php echo CHtml::encode($contestant['position']); ?></td>
                                                                <td><span class="badge bg-success"><?php echo CHtml::encode($contestant['competition_name']); ?></span></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Beauty Contest Tab -->
                                    <div class="tab-pane fade" id="beauty<?php echo $propId; ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 text-danger"><i class="fa fa-female me-1"></i> Danh sách thí sinh thi Miss</h6>
                                            <?php if ($beautyCount > 0): ?>
                                                <a href="<?php echo $this->createUrl('exportByHotel', array('event_id' => $selectedEventId, 'property_id' => $propId, 'type' => 'beauty')); ?>" class="btn btn-sm btn-outline-success">
                                                    <i class="fa fa-download"></i> Xuất Excel
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (empty($data['beauty_contestants'])): ?>
                                            <p class="text-muted fst-italic">Không có thí sinh nào</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover table-bordered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="50">STT</th>
                                                            <th>Họ tên</th>
                                                            <th>Phòng ban - Chức danh</th>
                                                            <th>Cuộc thi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $stt = 1; foreach ($data['beauty_contestants'] as $contestant): ?>
                                                            <tr>
                                                                <td class="text-center"><?php echo $stt++; ?></td>
                                                                <td><?php echo CHtml::encode($contestant['full_name']); ?></td>
                                                                <td><?php echo CHtml::encode($contestant['position']); ?></td>
                                                                <td><span class="badge bg-danger"><?php echo CHtml::encode($contestant['contest_name']); ?></span></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Talent Entries Tab -->
                                    <div class="tab-pane fade" id="talent<?php echo $propId; ?>">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 text-warning"><i class="fa fa-music me-1"></i> Danh sách tiết mục văn nghệ</h6>
                                            <?php if ($talentCount > 0): ?>
                                                <a href="<?php echo $this->createUrl('exportByHotel', array('event_id' => $selectedEventId, 'property_id' => $propId, 'type' => 'talent')); ?>" class="btn btn-sm btn-outline-success">
                                                    <i class="fa fa-download"></i> Xuất Excel
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (empty($data['talent_entries'])): ?>
                                            <p class="text-muted fst-italic">Không có tiết mục nào</p>
                                        <?php else: ?>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover table-bordered mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th width="50">STT</th>
                                                            <th>Tên tiết mục</th>
                                                            <th>Thể loại</th>
                                                            <th>Hội diễn</th>
                                                            <th width="80">Số người</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php $stt = 1; foreach ($data['talent_entries'] as $entry): ?>
                                                            <tr>
                                                                <td class="text-center"><?php echo $stt++; ?></td>
                                                                <td><?php echo CHtml::encode($entry['title']); ?></td>
                                                                <td><span class="badge bg-secondary"><?php echo CHtml::encode($entry['category_name']); ?></span></td>
                                                                <td><?php echo CHtml::encode($entry['show_name']); ?></td>
                                                                <td class="text-center"><?php echo $entry['participant_count']; ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchProperty');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var keyword = this.value.toLowerCase().trim();
            var items = document.querySelectorAll('.property-item');
            items.forEach(function(item) {
                var code = item.getAttribute('data-code') || '';
                var name = item.getAttribute('data-name') || '';
                if (code.indexOf(keyword) !== -1 || name.indexOf(keyword) !== -1) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
});
</script>
