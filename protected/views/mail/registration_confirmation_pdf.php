<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Phiếu xác nhận đăng ký tham dự</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 14mm 12mm 14mm 12mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', serif;
            font-size: 13pt;
            color: #000000;
            line-height: 1.35;
        }

        .page {
            position: relative;
        }

        .page-break {
            page-break-before: always;
        }

        /* Nhãn "MẪU SỐ x" ở góc trên bên phải */
        .form-tag {
            position: absolute;
            top: 0;
            right: 0;
            border: 1.5px solid #000;
            padding: 3px 10px;
            font-weight: bold;
            font-size: 11pt;
        }

        .doc-title {
            text-align: center;
            font-size: 15pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .doc-subtitle {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .doc-unit {
            text-align: center;
            font-size: 13pt;
            font-style: italic;
            margin-bottom: 10px;
        }

        .info-line {
            margin: 4px 0;
        }

        .dotted {
            border-bottom: 1px dotted #000;
            display: inline-block;
            min-width: 55%;
        }

        .intro-line {
            font-weight: bold;
            margin: 10px 0 8px 0;
        }

        table.grid {
            width: 100%;
            border-collapse: collapse;
        }

        table.grid th,
        table.grid td {
            border: 1px solid #000;
            padding: 5px 7px;
            vertical-align: top;
        }

        table.grid th {
            text-align: center;
            font-weight: bold;
            background-color: #eeeeee;
        }

        .text-center {
            text-align: center;
        }

        .col-stt {
            width: 8%;
            text-align: center;
        }

        .col-count {
            width: 24%;
        }

        .example {
            font-style: italic;
            color: #444;
        }

        /* MẪU SỐ 2 — danh sách VĐV */
        .sport-block {
            margin-bottom: 6px;
        }

        .sport-name {
            font-weight: bold;
            text-decoration: underline;
        }

        .athlete {
            padding-left: 6px;
        }

        /* Trang xác nhận nhân viên */
        table.confirm {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        table.confirm td {
            border: 1px solid #000;
            vertical-align: top;
            padding: 0;
        }

        .photo-box {
            width: 3cm;
            height: 4cm;
            border: 1px solid #000;
            text-align: center;
            font-size: 9pt;
            font-style: italic;
            color: #555;
            margin: 6px;
            padding-top: 8px;
        }

        .emp-info {
            padding: 8px 10px;
            font-size: 12pt;
        }

        .emp-info div {
            margin: 3px 0;
        }

        .commitment-text {
            margin-top: 18px;
            font-size: 13pt;
            text-align: justify;
            line-height: 1.5;
        }

        .signature {
            margin-top: 22px;
            text-align: right;
            padding-right: 40px;
        }

        .signature .role {
            font-weight: bold;
        }

        .signature .hint {
            font-style: italic;
            font-size: 10pt;
            color: #555;
        }

        .gender-nam {
            font-weight: bold;
        }

        .gender-nu {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <?php
    // ===== Chuẩn bị dữ liệu dùng chung =====
    $eventName = isset($model->event_name) && $model->event_name !== '' ? $model->event_name : 'ĐẠI HỘI MƯỜNG THANH';
    $propertyName = isset($model->property_name) && $model->property_name !== '' ? $model->property_name : '';
    $representative = isset($model->submitted_by) && $model->submitted_by !== '' ? $model->submitted_by : '';
    $contactEmail = $representative;

    $sportTeams = isset($sportTeams) && is_array($sportTeams) ? $sportTeams : array();
    $competitionRegistrations = isset($competitionRegistrations) && is_array($competitionRegistrations) ? $competitionRegistrations : array();
    $talentEntries = isset($talentEntries) && is_array($talentEntries) ? $talentEntries : array();
    $beautyContestants = isset($beautyContestants) && is_array($beautyContestants) ? $beautyContestants : array();

    // Hàm chuẩn hoá tên môn để so khớp danh mục cố định
    if (!function_exists('erNormSport')) {
        function erNormSport($s)
        {
            $s = mb_strtolower(trim((string)$s), 'UTF-8');
            $s = preg_replace('/\s+/u', ' ', $s);
            return $s;
        }
    }
    if (!function_exists('erGenderLabel')) {
        function erGenderLabel($g)
        {
            $gStr = ($g !== null) ? strtolower((string)$g) : '';
            if ($g === 1 || $g === '1' || $gStr === 'male' || $gStr === 'nam') {
                return '<span class="gender-nam">Nam</span>';
            }
            if ($g === 0 || $g === '0' || $gStr === 'female' || $gStr === 'nữ' || $gStr === 'nu') {
                return '<span class="gender-nu">Nữ</span>';
            }
            return '……';
        }
    }

    // Danh mục thi đấu cố định theo MẪU SỐ 1
    $sportCategories = array(
        'Bóng đá nam (05 người/đội, 03 dự bị)',
        'Bóng đá nữ (05 người/đội, 03 dự bị)',
        'Bóng chuyền nam (06 người/đội, 04 dự bị)',
        'Bóng chuyền nữ (06 người/đội, 04 dự bị)',
        'Cầu lông đơn nam',
        'Cầu lông đơn nữ',
        'Cầu lông đôi nam',
        'Cầu lông đôi nữ',
        'Cầu lông đôi nam nữ',
        'Bóng bàn đơn nam',
        'Bóng bàn đơn nữ',
        'Bóng bàn đôi nam',
        'Bóng bàn đôi nữ',
        'Bóng bàn đôi nam nữ',
        'Tennis đơn nam',
        'Tennis đơn nữ',
        'Tennis đôi nam',
        'Tennis đôi nữ',
        'Tennis đôi nam nữ',
        'Cờ tướng Nam',
        'Cờ tướng Nữ',
        'Kéo co nam nữ phối hợp (08 người/đội, 02 dự bị)',
    );

    // Đếm số đội đã đăng ký theo từng môn (khớp theo phần tên đứng trước dấu ngoặc)
    $teamCountBySport = array();
    foreach ($sportTeams as $team) {
        $key = erNormSport($team['sport_name']);
        if (!isset($teamCountBySport[$key])) {
            $teamCountBySport[$key] = 0;
        }
        $teamCountBySport[$key] += 1;
    }

    // Gom danh sách nhân viên tham dự (dùng cho trang xác nhận) từ mọi nội dung
    $confirmAttendees = array();
    $addConfirm = function ($name, $gender, $division, $discipline) use (&$confirmAttendees) {
        $name = trim((string)$name);
        if ($name === '') {
            return;
        }
        $k = mb_strtolower($name, 'UTF-8');
        if (!isset($confirmAttendees[$k])) {
            $confirmAttendees[$k] = array(
                'name' => $name,
                'gender' => $gender,
                'division' => $division,
                'disciplines' => array(),
            );
        }
        if ($gender !== null && $confirmAttendees[$k]['gender'] === null) {
            $confirmAttendees[$k]['gender'] = $gender;
        }
        if (empty($confirmAttendees[$k]['division']) && !empty($division)) {
            $confirmAttendees[$k]['division'] = $division;
        }
        if (!empty($discipline) && !in_array($discipline, $confirmAttendees[$k]['disciplines'])) {
            $confirmAttendees[$k]['disciplines'][] = $discipline;
        }
    };
    foreach ($sportTeams as $team) {
        foreach ($team['members'] as $m) {
            $addConfirm($m['attendee_name'], isset($m['gender']) ? $m['gender'] : null, isset($m['division_name']) ? $m['division_name'] : '', $team['sport_name']);
        }
    }
    foreach ($competitionRegistrations as $compData) {
        foreach ($compData['attendees'] as $c) {
            $addConfirm($c['attendee_name'], isset($c['gender']) ? $c['gender'] : null, isset($c['division_name']) ? $c['division_name'] : '', $compData['competition_name']);
        }
    }
    foreach ($talentEntries as $entry) {
        $label = 'Văn nghệ' . (!empty($entry['category_name']) ? ' (' . $entry['category_name'] . ')' : '');
        foreach ($entry['members'] as $m) {
            $addConfirm($m['attendee_name'], isset($m['gender']) ? $m['gender'] : null, isset($m['division_name']) ? $m['division_name'] : '', $label);
        }
    }
    foreach ($beautyContestants as $c) {
        $addConfirm($c['attendee_name'], null, isset($c['division_name']) ? $c['division_name'] : '', 'Miss Mường Thanh');
    }
    $confirmAttendees = array_values($confirmAttendees);
    ?>

    <!-- ============================ TRANG 1 — MẪU SỐ 1 ============================ -->
    <div class="page">
        <div class="form-tag">MẪU SỐ 1</div>

        <div class="doc-title">Đăng ký tham dự các hoạt động thể thao</div>
        <div class="doc-subtitle"><?php echo CHtml::encode(mb_strtoupper($eventName, 'UTF-8')); ?></div>

        <div style="margin-top:12px;">
            <div class="info-line">Tên đơn vị:
                <span class="dotted"><?php echo CHtml::encode($propertyName); ?></span>
            </div>
            <div class="info-line">Đại diện đơn vị:
                <span class="dotted"><?php echo CHtml::encode($representative); ?></span>
            </div>
            <div class="info-line">Số điện thoại liên hệ:
                <span class="dotted">&nbsp;</span>
            </div>
            <div class="info-line">Email:
                <span class="dotted"><?php echo CHtml::encode($contactEmail); ?></span>
            </div>
        </div>

        <div class="intro-line">Đăng ký tham dự các hoạt động thể thao:</div>

        <table class="grid">
            <thead>
                <tr>
                    <th class="col-stt">STT</th>
                    <th>Hạng mục thi đấu</th>
                    <th class="col-count">Số đội tham dự</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sportCategories as $i => $cat): ?>
                    <?php
                    // Khớp số đội đã đăng ký theo phần tên trước dấu "("
                    $baseName = trim(preg_replace('/\(.*$/u', '', $cat));
                    $count = null;
                    foreach ($teamCountBySport as $k => $cnt) {
                        if ($k === erNormSport($baseName) || strpos($k, erNormSport($baseName)) === 0) {
                            $count = $cnt;
                            break;
                        }
                    }
                    ?>
                    <tr>
                        <td class="col-stt"><?php echo $i + 1; ?></td>
                        <td><?php echo CHtml::encode($cat); ?></td>
                        <td><?php echo $count !== null ? ('<strong>' . (int)$count . ' đội</strong>') : '&nbsp;'; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ============================ TRANG 2 — MẪU SỐ 2 ============================ -->
    <div class="page page-break">
        <div class="form-tag">MẪU SỐ 2</div>

        <div class="doc-subtitle"><?php echo CHtml::encode(mb_strtoupper($eventName, 'UTF-8')); ?></div>
        <div class="doc-unit"><?php echo CHtml::encode($propertyName !== '' ? $propertyName : 'Khách sạn Mường Thanh ………'); ?> (Tên đơn vị)</div>
        <div class="doc-title">Danh sách các vận động viên tham gia thi đấu vòng loại</div>

        <?php if (empty($sportTeams)): ?>
            <p style="font-style:italic;">Chưa có thông tin đăng ký môn thể thao nào.</p>
        <?php else: ?>
            <table class="grid" style="margin-top:8px;">
                <tbody>
                    <?php
                    $totalTeams = count($sportTeams);
                    $half = (int)ceil($totalTeams / 2);
                    $leftCol = array_slice($sportTeams, 0, $half);
                    $rightCol = array_slice($sportTeams, $half);
                    $rows = max(count($leftCol), count($rightCol));
                    for ($r = 0; $r < $rows; $r++):
                    ?>
                        <tr>
                            <?php foreach (array($leftCol, $rightCol) as $col): ?>
                                <td style="width:50%;">
                                    <?php if (isset($col[$r])): $team = $col[$r]; ?>
                                        <div class="sport-block">
                                            <div class="sport-name"><?php echo CHtml::encode($team['sport_name']); ?></div>
                                            <?php foreach ($team['members'] as $m): ?>
                                                <div class="athlete"><?php echo CHtml::encode($m['attendee_name']); ?>
                                                    <?php if (!empty($m['gender']) || $m['gender'] === 0 || $m['gender'] === '0'): ?>
                                                        (<?php echo erGenderLabel($m['gender']); ?>)
                                                    <?php endif; ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        &nbsp;
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- ==================== TRANG 3 — XÁC NHẬN NHÂN VIÊN THAM GIA ==================== -->
    <div class="page page-break">
        <div class="doc-subtitle"><?php echo CHtml::encode(mb_strtoupper($eventName, 'UTF-8')); ?></div>
        <div class="doc-unit"><?php echo CHtml::encode($propertyName !== '' ? $propertyName : 'Khách sạn Mường Thanh …'); ?> (Tên đơn vị)</div>
        <div class="doc-title">Xác nhận nhân viên tham gia đại hội</div>

        <?php if (empty($confirmAttendees)): ?>
            <p style="font-style:italic;">Chưa có nhân viên tham dự.</p>
        <?php else: ?>
            <table class="confirm">
                <tbody>
                    <?php
                    $chunks = array_chunk($confirmAttendees, 2);
                    foreach ($chunks as $pair):
                    ?>
                        <tr>
                            <?php for ($c = 0; $c < 2; $c++): ?>
                                <?php if (isset($pair[$c])): $emp = $pair[$c]; ?>
                                    <td style="width:16%;">
                                        <div class="photo-box">Ảnh 3 x 4<br><br>Đóng dấu xác nhận<br>lên ảnh</div>
                                    </td>
                                    <td style="width:34%;">
                                        <div class="emp-info">
                                            <div><strong>Tên nhân viên:</strong> <?php echo CHtml::encode($emp['name']); ?></div>
                                            <div>Nam/nữ: <?php echo erGenderLabel($emp['gender']); ?></div>
                                            <div>Bộ phận/đơn vị: <?php echo CHtml::encode(!empty($emp['division']) ? $emp['division'] : '……'); ?></div>
                                            <div>Tham gia thi đấu môn: <?php echo CHtml::encode(!empty($emp['disciplines']) ? implode(', ', $emp['disciplines']) : '……'); ?></div>
                                            <div>Thời gian bắt đầu làm việc cho TĐ: …/…/…. (… tháng)</div>
                                        </div>
                                    </td>
                                <?php else: ?>
                                    <td style="width:16%;">&nbsp;</td>
                                    <td style="width:34%;">&nbsp;</td>
                                <?php endif; ?>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div class="commitment-text">
            Khách sạn / Công ty xin cam kết đây là nhân viên đã được ký hợp đồng lao động chính thức tính đến thời điểm
            <strong><?php echo CHtml::encode($eventName); ?></strong> quy định. Chúng tôi xin hoàn toàn chịu trách nhiệm
            trước Ban lãnh đạo Tập đoàn về độ chính xác, trung thực của những thông tin trên.
        </div>

        <div class="signature">
            <div class="role">GIÁM ĐỐC KHÁCH SẠN</div>
            <div class="hint">(Ký tên, đóng dấu)</div>
        </div>
    </div>

</body>

</html>
