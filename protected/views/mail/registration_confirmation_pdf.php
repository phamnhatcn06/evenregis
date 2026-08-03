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

        /* Ảnh chân dung 3x4: dùng background-size:cover để dompdf giữ đúng tỉ lệ 3x4
           (dompdf không hỗ trợ object-fit nên không dùng thẻ <img>). */
        .photo-box.has-photo {
            padding: 0;
            background-repeat: no-repeat;
            background-position: center center;
            background-size: cover;
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
    // Môn đơn (cá nhân): mỗi đăng ký là 1 VĐV, không phải 1 đội
    if (!function_exists('erIsSingleSport')) {
        function erIsSingleSport($name)
        {
            $n = erNormSport($name);
            return (strpos($n, 'đơn') !== false) || (strpos($n, 'cờ tướng') !== false);
        }
    }
    // Môn đồng đội: không phải môn đơn cũng không phải môn đôi (vd bóng đá, bóng chuyền, kéo co)
    if (!function_exists('erIsTeamSport')) {
        function erIsTeamSport($name)
        {
            $n = erNormSport($name);
            return (strpos($n, 'đơn') === false) && (strpos($n, 'đôi') === false);
        }
    }
    // Ghép "Mã nhân viên - Tên nhân viên" (đã escape HTML)
    if (!function_exists('erNameWithCode')) {
        function erNameWithCode($code, $name)
        {
            $code = trim((string)$code);
            $name = CHtml::encode((string)$name);
            return $code !== '' ? (CHtml::encode($code) . ' - ' . $name) : $name;
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

    // Danh mục thi đấu theo các môn thể thao active của sự kiện (truyền từ controller/EmailHelper)
    $sportCategories = isset($sportCategories) && is_array($sportCategories) ? $sportCategories : array();

    // Sắp xếp các môn thể thao theo tên (alphabet, chuẩn hoá tiếng Việt)
    usort($sportCategories, function ($a, $b) {
        return strcmp(erNormSport($a), erNormSport($b));
    });
    usort($sportTeams, function ($a, $b) {
        $cmp = strcmp(erNormSport($a['sport_name']), erNormSport($b['sport_name']));
        if ($cmp !== 0) {
            return $cmp;
        }
        return strcmp(erNormSport(isset($a['team_name']) ? $a['team_name'] : ''), erNormSport(isset($b['team_name']) ? $b['team_name'] : ''));
    });

    // Đếm số đội & số VĐV đã đăng ký theo từng môn (khớp theo phần tên đứng trước dấu ngoặc)
    $teamCountBySport = array();
    $athleteCountBySport = array();
    foreach ($sportTeams as $team) {
        $key = erNormSport($team['sport_name']);
        if (!isset($teamCountBySport[$key])) {
            $teamCountBySport[$key] = 0;
            $athleteCountBySport[$key] = 0;
        }
        $teamCountBySport[$key] += 1;
        $athleteCountBySport[$key] += isset($team['members']) && is_array($team['members']) ? count($team['members']) : 0;
    }

    // Gom danh sách nhân viên tham dự (dùng cho trang xác nhận) từ mọi nội dung
    $confirmAttendees = array();
    $addConfirm = function ($name, $staffCode, $gender, $division, $discipline, $photo = '', $startWorkingDate = '') use (&$confirmAttendees) {
        $name = trim((string)$name);
        if ($name === '') {
            return;
        }
        $k = mb_strtolower($name, 'UTF-8');
        if (!isset($confirmAttendees[$k])) {
            $confirmAttendees[$k] = array(
                'name' => $name,
                'staff_code' => $staffCode,
                'gender' => $gender,
                'photo' => $photo,
                'division' => $division,
                'start_working_date' => $startWorkingDate,
                'disciplines' => array(),
            );
        }
        if (empty($confirmAttendees[$k]['start_working_date']) && !empty($startWorkingDate)) {
            $confirmAttendees[$k]['start_working_date'] = $startWorkingDate;
        }
        if (empty($confirmAttendees[$k]['staff_code']) && !empty($staffCode)) {
            $confirmAttendees[$k]['staff_code'] = $staffCode;
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
        if (empty($confirmAttendees[$k]['photo']) && !empty($photo)) {
            $confirmAttendees[$k]['photo'] = $photo;
        }
    };
    foreach ($sportTeams as $team) {
        foreach ($team['members'] as $m) {
            $addConfirm($m['attendee_name'], isset($m['staff_code']) ? $m['staff_code'] : '', isset($m['gender']) ? $m['gender'] : null, isset($m['division_name']) ? $m['division_name'] : '', $team['sport_name'], isset($m['photo_path']) ? $m['photo_path'] : '', isset($m['start_working_date']) ? $m['start_working_date'] : '');
        }
    }
    foreach ($competitionRegistrations as $compData) {
        foreach ($compData['attendees'] as $c) {
            $addConfirm($c['attendee_name'], isset($c['staff_code']) ? $c['staff_code'] : '', isset($c['gender']) ? $c['gender'] : null, isset($c['division_name']) ? $c['division_name'] : '', $compData['competition_name'], isset($c['photo_path']) ? $c['photo_path'] : '', isset($c['start_working_date']) ? $c['start_working_date'] : '');
        }
    }
    foreach ($talentEntries as $entry) {
        $label = 'Văn nghệ' . (!empty($entry['category_name']) ? ' (' . $entry['category_name'] . ')' : '');
        foreach ($entry['members'] as $m) {
            $addConfirm($m['attendee_name'], isset($m['staff_code']) ? $m['staff_code'] : '', isset($m['gender']) ? $m['gender'] : null, isset($m['division_name']) ? $m['division_name'] : '', $label, isset($m['photo_path']) ? $m['photo_path'] : '', isset($m['start_working_date']) ? $m['start_working_date'] : '');
        }
    }
    foreach ($beautyContestants as $c) {
        $addConfirm($c['attendee_name'], isset($c['staff_code']) ? $c['staff_code'] : '', null, isset($c['division_name']) ? $c['division_name'] : '', 'Miss Mường Thanh', isset($c['photo_path']) ? $c['photo_path'] : '', isset($c['start_working_date']) ? $c['start_working_date'] : '');
    }
    $confirmAttendees = array_values($confirmAttendees);

    // Gom nhóm thí sinh Miss theo cuộc thi (dùng cho MẪU 1 & MẪU 2)
    $beautyByContest = array();
    foreach ($beautyContestants as $c) {
        $cName = !empty($c['contest_name']) ? $c['contest_name'] : 'Miss Mường Thanh';
        if (!isset($beautyByContest[$cName])) {
            $beautyByContest[$cName] = array();
        }
        $beautyByContest[$cName][] = $c;
    }
    ksort($beautyByContest);

    // Danh sách thi nghiệp vụ (Đợt 2) — chuẩn hoá & sắp xếp theo tên nghiệp vụ
    $competitionList = array_values($competitionRegistrations);
    usort($competitionList, function ($a, $b) {
        return strcmp(erNormSport(isset($a['competition_name']) ? $a['competition_name'] : ''), erNormSport(isset($b['competition_name']) ? $b['competition_name'] : ''));
    });

    // Tiết mục văn nghệ (Đợt 3) — gom theo thể loại để tổng hợp MẪU SỐ 1
    $talentByCategory = array();
    foreach ($talentEntries as $entry) {
        $cat = !empty($entry['category_name']) ? $entry['category_name'] : 'Văn nghệ';
        if (!isset($talentByCategory[$cat])) {
            $talentByCategory[$cat] = array();
        }
        $talentByCategory[$cat][] = $entry;
    }
    ksort($talentByCategory);

    // Tiêu đề MẪU SỐ 1 thay đổi theo nội dung đăng ký của đợt (thể thao/miss/nghiệp vụ/văn nghệ)
    $mau1Parts = array();
    if (!empty($sportCategories) || !empty($sportTeams)) $mau1Parts[] = 'thể thao';
    if (!empty($beautyByContest)) $mau1Parts[] = 'miss';
    if (!empty($competitionList)) $mau1Parts[] = 'thi nghiệp vụ';
    if (!empty($talentByCategory)) $mau1Parts[] = 'văn nghệ';
    $mau1Title = 'Đăng ký tham dự các hoạt động' . (empty($mau1Parts) ? '' : ' ' . implode(', ', $mau1Parts));
    ?>

    <!-- ============================ TRANG 1 — MẪU SỐ 1 ============================ -->
    <div class="page">
        <div class="form-tag">MẪU SỐ 1</div>

        <div class="doc-title"><?php echo CHtml::encode($mau1Title); ?></div>
        <div class="doc-subtitle"><?php echo CHtml::encode(mb_strtoupper($eventName, 'UTF-8')); ?></div>
        <div class="doc-subtitle">Đơn vị: <?php echo CHtml::encode($propertyName); ?></div>
        <div class="intro-line"><?php echo CHtml::encode($mau1Title); ?>:</div>

        <table class="grid">
            <thead>
                <tr>
                    <th class="col-stt">STT</th>
                    <th>Hạng mục đăng ký</th>
                    <th class="col-count">Số lượng đăng ký</th>
                </tr>
            </thead>
            <tbody>
                <?php $stt = 0; ?>
                <?php if (empty($sportCategories) && empty($beautyByContest) && empty($competitionList) && empty($talentByCategory)): ?>
                    <tr>
                        <td colspan="3" class="text-center" style="font-style:italic;">Chưa có nội dung đăng ký.</td>
                    </tr>
                <?php endif; ?>

                <?php // Thể thao (Đợt 1) ?>
                <?php foreach ($sportCategories as $cat): ?>
                    <?php
                    // Khớp số đội & số VĐV đã đăng ký theo phần tên trước dấu "("
                    $baseName = trim(preg_replace('/\(.*$/u', '', (string)$cat));
                    $count = null;
                    $athletes = 0;
                    foreach ($teamCountBySport as $k => $cnt) {
                        if ($k === erNormSport($baseName) || strpos($k, erNormSport($baseName)) === 0) {
                            $count = $cnt;
                            $athletes = isset($athleteCountBySport[$k]) ? $athleteCountBySport[$k] : 0;
                            break;
                        }
                    }
                    ?>
                    <tr>
                        <td class="col-stt"><?php echo ++$stt; ?></td>
                        <td><?php echo CHtml::encode($cat); ?></td>
                        <td><?php
                            if ($count === null || ((int)$count === 0 && (int)$athletes === 0)) {
                                echo '<em>Không đăng ký</em>';
                            } else {
                                echo '<strong>' . (int)$count . ' đội - ' . (int)$athletes . ' VĐV</strong>';
                            }
                            ?></td>
                    </tr>
                <?php endforeach; ?>

                <?php // Thi nghiệp vụ (Đợt 2) — hiển thị tên các nghiệp vụ ?>
                <?php foreach ($competitionList as $comp): ?>
                    <tr>
                        <td class="col-stt"><?php echo ++$stt; ?></td>
                        <td><?php echo CHtml::encode($comp['competition_name']); ?></td>
                        <td><strong><?php echo count($comp['attendees']); ?> thí sinh</strong></td>
                    </tr>
                <?php endforeach; ?>

                <?php // Văn nghệ (Đợt 3) — gom theo thể loại ?>
                <?php foreach ($talentByCategory as $catName => $entries): ?>
                    <tr>
                        <td class="col-stt"><?php echo ++$stt; ?></td>
                        <td><?php echo CHtml::encode($catName); ?></td>
                        <td><strong><?php echo count($entries); ?> tiết mục</strong></td>
                    </tr>
                <?php endforeach; ?>

                <?php // Miss (Đợt 1) ?>
                <?php foreach ($beautyByContest as $contestName => $contestants): ?>
                    <tr>
                        <td class="col-stt"><?php echo ++$stt; ?></td>
                        <td><?php echo CHtml::encode($contestName); ?></td>
                        <td><strong><?php echo count($contestants); ?> thí sinh</strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ============================ TRANG 2 — MẪU SỐ 2 ============================ -->
    <div class="page page-break">
        <div class="form-tag">MẪU SỐ 2</div>

        <div class="doc-subtitle"><?php echo CHtml::encode(mb_strtoupper($eventName, 'UTF-8')); ?></div>
        <div class="doc-unit"><?php echo CHtml::encode($propertyName !== '' ? $propertyName : 'Khách sạn Mường Thanh ………'); ?></div>
        <div class="doc-title">Danh sách các vận động viên tham gia thi đấu vòng loại</div>

        <?php if (empty($sportTeams)): ?>
            <p style="font-style:italic;">Chưa có thông tin đăng ký môn thể thao nào.</p>
        <?php else: ?>
            <?php
            // Tách môn đồng đội và môn đơn/đôi để bố cục bảng đều nhau
            $teamGroup = array();
            $individualGroup = array();
            foreach ($sportTeams as $team) {
                $memberCount = isset($team['members']) && is_array($team['members']) ? count($team['members']) : 0;
                // Chỉ coi là môn đồng đội khi tên là đồng đội VÀ có nhiều hơn 1 VĐV.
                // Môn cá nhân (bơi, chạy, cờ...) mỗi nội dung chỉ 1 VĐV -> xếp 2 ô/dòng cho cân đối.
                if (erIsTeamSport($team['sport_name']) && $memberCount > 1) {
                    $teamGroup[] = $team;
                } else {
                    $individualGroup[] = $team;
                }
            }

            // Render một dòng VĐV: "Mã NV - Tên (Giới tính)"
            $renderAthlete = function ($m) {
                ob_start();
            ?>
                <div class="athlete"><?php echo erNameWithCode(isset($m['staff_code']) ? $m['staff_code'] : '', $m['attendee_name']); ?><?php if (!empty($m['gender']) || $m['gender'] === 0 || $m['gender'] === '0'): ?> (<?php echo erGenderLabel($m['gender']); ?>)<?php endif; ?></div>
            <?php
                return ob_get_clean();
            };

            // Hàm render một khối môn thành ô <td>. $twoCols = true: chia VĐV thành 2 cột.
            $renderSportCell = function ($team, $twoCols = false) use ($renderAthlete) {
                ob_start();
            ?>
                <div class="sport-block">
                    <div class="sport-name"><?php echo CHtml::encode($team['sport_name']); ?></div>
                    <?php if ($twoCols): ?>
                        <?php
                        $members = $team['members'];
                        $half = (int)ceil(count($members) / 2);
                        $colL = array_slice($members, 0, $half);
                        $colR = array_slice($members, $half);
                        $rows = max(count($colL), count($colR));
                        ?>
                        <table style="width:100%; border-collapse:collapse;">
                            <?php for ($r = 0; $r < $rows; $r++): ?>
                                <tr>
                                    <td style="width:50%; border:none; padding:0; vertical-align:top;"><?php echo isset($colL[$r]) ? $renderAthlete($colL[$r]) : '&nbsp;'; ?></td>
                                    <td style="width:50%; border:none; padding:0; vertical-align:top;"><?php echo isset($colR[$r]) ? $renderAthlete($colR[$r]) : '&nbsp;'; ?></td>
                                </tr>
                            <?php endfor; ?>
                        </table>
                    <?php else: ?>
                        <?php foreach ($team['members'] as $m): ?>
                            <?php echo $renderAthlete($m); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php
                return ob_get_clean();
            };
            ?>

            <?php // Môn đồng đội: mỗi nội dung 1 hàng - 1 ô (full-width) 
            ?>
            <?php if (!empty($teamGroup)): ?>
                <table class="grid" style="margin-top:8px;">
                    <tbody>
                        <?php foreach ($teamGroup as $team): ?>
                            <tr>
                                <td><?php echo $renderSportCell($team, true); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php // Môn đơn/đôi: 2 ô ngang hàng cho cân đối 
            ?>
            <?php if (!empty($individualGroup)): ?>
                <table class="grid" style="margin-top:8px;">
                    <tbody>
                        <?php foreach (array_chunk($individualGroup, 2) as $pair): ?>
                            <tr>
                                <td style="width:50%;"><?php echo $renderSportCell($pair[0]); ?></td>
                                <td style="width:50%;"><?php echo isset($pair[1]) ? $renderSportCell($pair[1]) : '&nbsp;'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (!empty($beautyByContest)): ?>
            <div class="doc-title" style="margin-top:16px;">Danh sách thí sinh dự thi Miss</div>
            <?php foreach ($beautyByContest as $contestName => $contestants): ?>
                <div class="sport-block">
                    <div class="sport-name"><?php echo CHtml::encode($contestName); ?></div>
                    <?php foreach ($contestants as $idx => $c): ?>
                        <div class="athlete">
                            <?php echo ($idx + 1) . '. ' . erNameWithCode(isset($c['staff_code']) ? $c['staff_code'] : '', $c['attendee_name']); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- ==================== TRANG 3 — XÁC NHẬN NHÂN VIÊN THAM GIA ==================== -->
    <div class="page page-break">
        <div class="doc-subtitle"><?php echo CHtml::encode(mb_strtoupper($eventName, 'UTF-8')); ?></div>
        <div class="doc-unit"><?php echo CHtml::encode($propertyName !== '' ? $propertyName : 'Khách sạn Mường Thanh …'); ?></div>
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
                                        <?php if (!empty($emp['photo'])): ?>
                                            <div class="photo-box has-photo" style="background-image:url('<?php echo str_replace(array("'", '\\'), array('%27', '/'), $emp['photo']); ?>');"></div>
                                        <?php else: ?>
                                            <div class="photo-box">Ảnh 3 x 4<br><br>Đóng dấu xác nhận<br>lên ảnh</div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="width:34%;">
                                        <div class="emp-info">
                                            <div><strong>Tên nhân viên:</strong> <?= $emp['name'] ?></div>
                                            <div>Mã nhân viên:</strong> <?= isset($emp['staff_code']) ? $emp['staff_code'] : '' ?></div>
                                            <div>Nam/nữ: <?php echo erGenderLabel($emp['gender']); ?></div>
                                            <div>Bộ phận/đơn vị: <?php echo CHtml::encode(!empty($emp['division']) ? $emp['division'] : '……'); ?></div>
                                            <div>Nội dung tham gia: <?php echo CHtml::encode(!empty($emp['disciplines']) ? implode(', ', $emp['disciplines']) : '……'); ?></div>
                                            <div>Thời gian bắt đầu làm việc cho TĐ: <?php echo CHtml::encode(MyHelper::formatWorkingDuration(isset($emp['start_working_date']) ? $emp['start_working_date'] : '')); ?></div>
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