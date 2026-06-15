<?php

/**
 * Report Attendee Stats - Index View
 * Báo cáo thống kê người đăng ký vòng loại theo cụm và đơn vị
 *
 * @var ReportAttendeeStatsController $this
 * @var bool $isHO
 * @var array $eventsList
 * @var string $selectedEventId
 * @var string $selectedEventName
 * @var array $reportData
 */

Yii::app()->clientScript->registerCssFile(Yii::app()->theme->baseUrl . '/assets/css/pages/reports-index.css');
?>
<div class="card">
    <div class="card-body">
        <!-- Filter & Title Section -->
        <div class="row align-items-center mb-4">
            <div class="col-md-7">
                <h3 class="mb-1 text-primary fw-bold">Báo cáo thống kê người đăng ký vòng loại</h3>
                <p class="text-muted mb-0">Phân tích số người đăng ký theo <strong>NGƯỜI</strong> (không theo môn) của sự kiện: <strong class="text-secondary"><?php echo CHtml::encode($selectedEventName); ?></strong></p>
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
                    <button type="submit" class="btn btn-primary shadow-sm">
                        <i class="fa fa-refresh me-1"></i> Tải lại
                    </button>
                </form>
            </div>
        </div>

        <?php $summary = $reportData['summary']; ?>

        <!-- KPI Cards Section -->
        <div class="row g-3 mb-4">
            <!-- Card 1: Unique Attendees -->
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                    <div class="card-body d-flex align-items-center justify-content-between p-4 text-white">
                        <div>
                            <span class="small fw-bold text-uppercase" style="opacity: 0.8;">Tổng số người đăng ký</span>
                            <h2 class="mb-0 mt-1 fw-bold"><?php echo number_format($summary['total_unique_attendees']); ?></h2>
                            <small style="opacity: 0.7;">Unique theo người</small>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(255,255,255,0.2);">
                            <i class="fa fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total by Categories -->
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <div class="card-body p-4 text-white">
                        <span class="small fw-bold text-uppercase" style="opacity: 0.8;">Đăng ký theo hạng mục</span>
                        <div class="d-flex justify-content-between mt-2">
                            <div class="text-center">
                                <div class="fw-bold fs-4"><?php echo number_format($summary['total_sports_attendees']); ?></div>
                                <small style="opacity: 0.7;"><i class="fa fa-futbol-o me-1"></i>Thể thao</small>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold fs-4"><?php echo number_format($summary['total_competition_attendees']); ?></div>
                                <small style="opacity: 0.7;"><i class="fa fa-trophy me-1"></i>Nghiệp vụ</small>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold fs-4"><?php echo number_format($summary['total_miss_attendees']); ?></div>
                                <small style="opacity: 0.7;"><i class="fa fa-star me-1"></i>Miss</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: 3 Sports -->
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <div class="card-body d-flex align-items-center justify-content-between p-4 text-white">
                        <div>
                            <span class="small fw-bold text-uppercase" style="opacity: 0.8;">Đăng ký 3 môn thể thao</span>
                            <h2 class="mb-0 mt-1 fw-bold"><?php echo number_format($summary['attendees_3_sports']); ?></h2>
                            <small style="opacity: 0.7;">Người tham gia 3 môn</small>
                        </div>
                        <div class="rounded-3 p-3" style="background: rgba(255,255,255,0.2);">
                            <i class="fa fa-futbol-o fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 4: Multi Categories -->
            <div class="col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                    <div class="card-body p-4 text-white">
                        <span class="small fw-bold text-uppercase" style="opacity: 0.8;">Đăng ký nhiều hạng mục</span>
                        <div class="d-flex justify-content-between mt-2">
                            <div class="text-center">
                                <div class="fw-bold fs-4"><?php echo number_format($summary['attendees_3_categories']); ?></div>
                                <small style="opacity: 0.7;">Cả 3 hạng mục</small>
                            </div>
                            <div class="text-center">
                                <div class="fw-bold fs-4"><?php echo number_format($summary['attendees_2_categories']); ?></div>
                                <small style="opacity: 0.7;">2 hạng mục</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="card border shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-table me-2 text-primary"></i>Chi tiết theo Cụm và Đơn vị</h5>
                <a href="<?php echo $this->createUrl('/admin/reports/admin'); ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-arrow-left me-1"></i> Quay lại báo cáo chung
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0" id="attendeeStatsTable">
                        <thead class="table-primary">
                            <tr>
                                <th rowspan="2" class="text-center align-middle" style="width: 50px;">STT</th>
                                <th rowspan="2" class="align-middle" style="min-width: 150px;">Cụm</th>
                                <th rowspan="2" class="align-middle" style="min-width: 200px;">Đơn vị</th>
                                <th rowspan="2" class="text-center align-middle" style="width: 100px;">Tổng người<br><small class="text-muted">(Unique)</small></th>
                                <th colspan="3" class="text-center bg-info text-white">Theo hạng mục</th>
                                <th colspan="3" class="text-center bg-warning">Đăng ký nhiều</th>
                            </tr>
                            <tr>
                                <th class="text-center bg-info-subtle" style="width: 90px;"><i class="fa fa-futbol-o me-1"></i>Thể thao</th>
                                <th class="text-center bg-info-subtle" style="width: 90px;"><i class="fa fa-trophy me-1"></i>Nghiệp vụ</th>
                                <th class="text-center bg-info-subtle" style="width: 70px;"><i class="fa fa-star me-1"></i>Miss</th>
                                <th class="text-center bg-warning-subtle" style="width: 80px;">≥3 môn TT</th>
                                <th class="text-center bg-warning-subtle" style="width: 80px;">3 hạng mục</th>
                                <th class="text-center bg-warning-subtle" style="width: 80px;">2 hạng mục</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stt = 1;
                            $regionals = $reportData['regionals'];

                            if (empty($regionals)):
                            ?>
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fa fa-info-circle me-1"></i> Chưa có dữ liệu đăng ký
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($regionals as $regional): ?>
                                    <?php
                                    $properties = $regional['properties'];
                                    $propCount = count($properties);
                                    $isFirstProp = true;
                                    ?>
                                    <?php foreach ($properties as $prop): ?>
                                        <tr>
                                            <td class="text-center"><?php echo $stt++; ?></td>
                                            <?php if ($isFirstProp): ?>
                                                <td rowspan="<?php echo $propCount; ?>" class="align-middle fw-bold bg-light">
                                                    <?php echo CHtml::encode($regional['regional_name']); ?>
                                                </td>
                                            <?php $isFirstProp = false;
                                            endif; ?>
                                            <td>
                                                <span class="badge bg-secondary me-1"><?php echo CHtml::encode($prop['property_code']); ?></span>
                                                <?php echo CHtml::encode($prop['property_name']); ?>
                                            </td>
                                            <td class="text-center fw-bold"><?php echo number_format($prop['unique_attendees']); ?></td>
                                            <td class="text-center"><?php echo $prop['sports_attendees'] ?: '-'; ?></td>
                                            <td class="text-center"><?php echo $prop['competition_attendees'] ?: '-'; ?></td>
                                            <td class="text-center"><?php echo $prop['miss_attendees'] ?: '-'; ?></td>
                                            <td class="text-center"><?php echo $prop['attendees_3_sports'] ?: '-'; ?></td>
                                            <td class="text-center"><?php echo $prop['attendees_3_categories'] ?: '-'; ?></td>
                                            <td class="text-center"><?php echo $prop['attendees_2_categories'] ?: '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>

                                    <!-- Regional Subtotal -->
                                    <tr class="table-warning fw-bold">
                                        <td colspan="3" class="text-end">Tổng <?php echo CHtml::encode($regional['regional_name']); ?>:</td>
                                        <td class="text-center"><?php echo number_format($regional['totals']['unique_attendees']); ?></td>
                                        <td class="text-center"><?php echo $regional['totals']['sports_attendees'] ?: '-'; ?></td>
                                        <td class="text-center"><?php echo $regional['totals']['competition_attendees'] ?: '-'; ?></td>
                                        <td class="text-center"><?php echo $regional['totals']['miss_attendees'] ?: '-'; ?></td>
                                        <td class="text-center"><?php echo $regional['totals']['attendees_3_sports'] ?: '-'; ?></td>
                                        <td class="text-center"><?php echo $regional['totals']['attendees_3_categories'] ?: '-'; ?></td>
                                        <td class="text-center"><?php echo $regional['totals']['attendees_2_categories'] ?: '-'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                        <?php if (!empty($regionals)): ?>
                            <tfoot class="table-success">
                                <tr class="fw-bold">
                                    <td colspan="3" class="text-end">TỔNG CỘNG:</td>
                                    <td class="text-center"><?php echo number_format($summary['total_unique_attendees']); ?></td>
                                    <td class="text-center"><?php echo number_format($summary['total_sports_attendees']); ?></td>
                                    <td class="text-center"><?php echo number_format($summary['total_competition_attendees']); ?></td>
                                    <td class="text-center"><?php echo number_format($summary['total_miss_attendees']); ?></td>
                                    <td class="text-center"><?php echo number_format($summary['attendees_3_sports']); ?></td>
                                    <td class="text-center"><?php echo number_format($summary['attendees_3_categories']); ?></td>
                                    <td class="text-center"><?php echo number_format($summary['attendees_2_categories']); ?></td>
                                </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sport Athletes Stats Table -->
        <?php if (!empty($reportData['sportStats'])): ?>
            <div class="card border shadow-sm mt-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-futbol-o me-2 text-success"></i>Số lượng VĐV theo môn thể thao</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="table-success">
                                <tr>
                                    <th class="text-center" style="width: 60px;">STT</th>
                                    <th style="min-width: 250px;">Môn thể thao</th>
                                    <th class="text-center" style="width: 120px;">Số lượng VĐV</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sportStt = 1;
                                $totalAthletes = 0;
                                foreach ($reportData['sportStats'] as $sport):
                                    if ($sport['total_athletes'] == 0) continue;
                                    $children = isset($sport['children']) ? $sport['children'] : array();
                                    $activeChildren = array_filter($children, function ($c) {
                                        return $c['total_athletes'] > 0;
                                    });
                                    // Chỉ cộng children nếu có, không cộng cả parent (tránh đếm trùng)
                                    if (!empty($activeChildren)) {
                                        foreach ($activeChildren as $c) {
                                            $totalAthletes += $c['total_athletes'];
                                        }
                                    } else {
                                        $totalAthletes += $sport['total_athletes'];
                                    }
                                ?>
                                    <!-- Parent row -->
                                    <tr class="table-light">
                                        <td class="text-center fw-bold"><?php echo $sportStt++; ?></td>
                                        <td>
                                            <i class="fa fa-trophy text-warning me-1"></i>
                                            <strong><?php echo CHtml::encode($sport['sport_name']); ?></strong>
                                        </td>
                                        <td class="text-center fw-bold fs-5"><?php echo number_format($sport['total_athletes']); ?></td>
                                    </tr>
                                    <!-- Children rows -->
                                    <?php foreach ($activeChildren as $child): ?>
                                        <tr>
                                            <td></td>
                                            <td class="ps-4">
                                                <i class="fa fa-angle-right text-muted me-2"></i>
                                                <?php echo CHtml::encode($child['sport_name']); ?>
                                            </td>
                                            <td class="text-center"><?php echo number_format($child['total_athletes']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-warning">
                                <tr class="fw-bold">
                                    <td colspan="2" class="text-end">TỔNG CỘNG (lượt đăng ký):</td>
                                    <td class="text-center"><?php echo number_format($totalAthletes); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Row 2 bảng: Môn thể thao -->
        <div class="row mt-4">
            <!-- Top 50 đơn vị đăng ký ít MÔN thể thao nhất -->
            <?php if (!empty($reportData['top50LeastSports'])): ?>
            <div class="col-lg-6">
                <div class="card border shadow-sm h-100">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fa fa-arrow-down me-2 text-danger"></i>Top 50 ĐV ít MÔN thể thao</h6>
                        <a href="<?php echo $this->createUrl('/admin/reportAttendeeStats/exportSportsByProperty', array('event_id' => $selectedEventId, 'type' => 'least', 'category' => 'sport')); ?>" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel-o"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-bordered table-hover table-sm mb-0">
                                <thead class="table-danger sticky-top">
                                    <tr>
                                        <th class="text-center" style="width: 40px;">STT</th>
                                        <th style="width: 80px;">Mã ĐV</th>
                                        <th>Tên đơn vị</th>
                                        <th class="text-center" style="width: 70px;">Số môn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $stt = 1; foreach ($reportData['top50LeastSports'] as $item): ?>
                                        <tr title="<?php echo CHtml::encode($item['sport_names']); ?>">
                                            <td class="text-center"><?php echo $stt++; ?></td>
                                            <td><span class="badge bg-secondary"><?php echo CHtml::encode($item['property_code']); ?></span></td>
                                            <td><?php echo CHtml::encode($item['property_name']); ?></td>
                                            <td class="text-center fw-bold text-danger"><?php echo $item['sport_count']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Top 50 đơn vị đăng ký nhiều MÔN thể thao nhất -->
            <?php if (!empty($reportData['top50MostSports'])): ?>
            <div class="col-lg-6">
                <div class="card border shadow-sm h-100">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fa fa-arrow-up me-2 text-success"></i>Top 50 ĐV nhiều MÔN thể thao</h6>
                        <a href="<?php echo $this->createUrl('/admin/reportAttendeeStats/exportSportsByProperty', array('event_id' => $selectedEventId, 'type' => 'most', 'category' => 'sport')); ?>" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel-o"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-bordered table-hover table-sm mb-0">
                                <thead class="table-success sticky-top">
                                    <tr>
                                        <th class="text-center" style="width: 40px;">STT</th>
                                        <th style="width: 80px;">Mã ĐV</th>
                                        <th>Tên đơn vị</th>
                                        <th class="text-center" style="width: 70px;">Số môn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $stt = 1; foreach ($reportData['top50MostSports'] as $item): ?>
                                        <tr title="<?php echo CHtml::encode($item['sport_names']); ?>">
                                            <td class="text-center"><?php echo $stt++; ?></td>
                                            <td><span class="badge bg-secondary"><?php echo CHtml::encode($item['property_code']); ?></span></td>
                                            <td><?php echo CHtml::encode($item['property_name']); ?></td>
                                            <td class="text-center fw-bold text-success"><?php echo $item['sport_count']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Row 2 bảng: Nội dung thể thao -->
        <div class="row mt-4">
            <!-- Top 50 đơn vị đăng ký ít NỘI DUNG thể thao nhất -->
            <?php if (!empty($reportData['top50LeastContent'])): ?>
            <div class="col-lg-6">
                <div class="card border shadow-sm h-100">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fa fa-arrow-down me-2 text-warning"></i>Top 50 ĐV ít NỘI DUNG thể thao</h6>
                        <a href="<?php echo $this->createUrl('/admin/reportAttendeeStats/exportSportsByProperty', array('event_id' => $selectedEventId, 'type' => 'least', 'category' => 'content')); ?>" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel-o"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-bordered table-hover table-sm mb-0">
                                <thead class="table-warning sticky-top">
                                    <tr>
                                        <th class="text-center" style="width: 40px;">STT</th>
                                        <th style="width: 80px;">Mã ĐV</th>
                                        <th>Tên đơn vị</th>
                                        <th class="text-center" style="width: 70px;">Số ND</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $stt = 1; foreach ($reportData['top50LeastContent'] as $item): ?>
                                        <tr title="<?php echo CHtml::encode($item['content_names']); ?>">
                                            <td class="text-center"><?php echo $stt++; ?></td>
                                            <td><span class="badge bg-secondary"><?php echo CHtml::encode($item['property_code']); ?></span></td>
                                            <td><?php echo CHtml::encode($item['property_name']); ?></td>
                                            <td class="text-center fw-bold text-warning"><?php echo $item['content_count']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Top 50 đơn vị đăng ký nhiều NỘI DUNG thể thao nhất -->
            <?php if (!empty($reportData['top50MostContent'])): ?>
            <div class="col-lg-6">
                <div class="card border shadow-sm h-100">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h6 class="mb-0"><i class="fa fa-arrow-up me-2 text-primary"></i>Top 50 ĐV nhiều NỘI DUNG thể thao</h6>
                        <a href="<?php echo $this->createUrl('/admin/reportAttendeeStats/exportSportsByProperty', array('event_id' => $selectedEventId, 'type' => 'most', 'category' => 'content')); ?>" class="btn btn-success btn-sm">
                            <i class="fa fa-file-excel-o"></i>
                        </a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-bordered table-hover table-sm mb-0">
                                <thead class="table-primary sticky-top">
                                    <tr>
                                        <th class="text-center" style="width: 40px;">STT</th>
                                        <th style="width: 80px;">Mã ĐV</th>
                                        <th>Tên đơn vị</th>
                                        <th class="text-center" style="width: 70px;">Số ND</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $stt = 1; foreach ($reportData['top50MostContent'] as $item): ?>
                                        <tr title="<?php echo CHtml::encode($item['content_names']); ?>">
                                            <td class="text-center"><?php echo $stt++; ?></td>
                                            <td><span class="badge bg-secondary"><?php echo CHtml::encode($item['property_code']); ?></span></td>
                                            <td><?php echo CHtml::encode($item['property_name']); ?></td>
                                            <td class="text-center fw-bold text-primary"><?php echo $item['content_count']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Bảng đơn vị theo số người đăng ký thể thao -->
        <?php if (!empty($reportData['propertiesBySportsAttendees'])): ?>
            <div class="card border shadow-sm mt-4">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-sort-amount-desc me-2 text-info"></i>Đơn vị theo số người đăng ký thể thao (nhiều → ít)</h5>
                    <a href="<?php echo $this->createUrl('/admin/reportAttendeeStats/exportSportsByProperty', array('event_id' => $selectedEventId, 'type' => 'attendees', 'category' => 'attendees')); ?>" class="btn btn-success btn-sm">
                        <i class="fa fa-file-excel-o me-1"></i> Xuất Excel
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 500px;">
                        <table class="table table-bordered table-hover table-sm mb-0">
                            <thead class="table-info sticky-top">
                                <tr>
                                    <th class="text-center" style="width: 50px;">STT</th>
                                    <th style="width: 100px;">Mã ĐV</th>
                                    <th style="min-width: 250px;">Tên đơn vị</th>
                                    <th class="text-center" style="width: 120px;">Số người ĐK TT</th>
                                    <th class="text-center" style="width: 120px;">Tổng người ĐK</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $stt = 1; foreach ($reportData['propertiesBySportsAttendees'] as $item): ?>
                                    <tr>
                                        <td class="text-center"><?php echo $stt++; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo CHtml::encode($item['property_code']); ?></span></td>
                                        <td><?php echo CHtml::encode($item['property_name']); ?></td>
                                        <td class="text-center fw-bold text-info"><?php echo number_format($item['sports_attendees']); ?></td>
                                        <td class="text-center"><?php echo number_format($item['unique_attendees']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Notes -->
        <div class="alert alert-info mt-4">
            <h6 class="alert-heading"><i class="fa fa-info-circle me-1"></i> Ghi chú:</h6>
            <ul class="mb-0 small">
                <li><strong>Tổng số người đăng ký (Unique)</strong>: Số người tham gia ít nhất 1 hạng mục (Thể thao, Nghiệp vụ, hoặc Miss)</li>
                <li><strong>Thể thao</strong>: Số người có đăng ký ít nhất 1 môn thể thao</li>
                <li><strong>Nghiệp vụ</strong>: Số người có đăng ký thi nghiệp vụ</li>
                <li><strong>Miss</strong>: Số người có đăng ký thi sắc đẹp</li>
                <li><strong>≥3 môn TT</strong>: Số người đăng ký từ 3 bộ môn thể thao trở lên (ví dụ: Bóng đá, Cầu lông, Bóng bàn)</li>
                <li><strong>3 hạng mục</strong>: Số người tham gia cả 3 hạng mục (Thể thao + Nghiệp vụ + Miss)</li>
                <li><strong>2 hạng mục</strong>: Số người tham gia đúng 2 hạng mục</li>
                <li><strong>Top 50 ít/nhiều môn TT</strong>: Đếm số môn thể thao (parent sport) mà đơn vị có VĐV đăng ký</li>
            </ul>
        </div>
    </div>
</div>