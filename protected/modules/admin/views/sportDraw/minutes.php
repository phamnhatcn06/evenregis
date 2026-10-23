<?php
/**
 * Trang in Biên bản trận đấu — Môn: Bóng bàn.
 *
 * Trang in độc lập (render qua renderPartial, không dùng layout admin).
 * Mỗi trận đấu in thành 1 trang A4 gồm 2 bản biên bản giống nhau (bản lưu +
 * bản cắt rời) đúng theo mẫu Word "Bien_ban_bong_ban.docx".
 *
 * @var array  $stage      Giai đoạn (sport_stages row)
 * @var string $sportName  Tên nội dung thi đấu
 * @var string $eventName  Tên sự kiện
 * @var array  $matches    Danh sách trận (đã kèm team_a / team_b)
 * @var bool   $isDoubles  Nội dung đôi?
 * @var bool   $isFemale   Nội dung nữ?
 */

$year = date('Y');

/** Ô checkbox: tick nếu $checked. */
$box = function ($checked) {
    return $checked ? '☒' : '☐';
};

/** Nhãn vòng đấu + xác định box round nào được tick. */
$roundInfo = function ($m) {
    $type = isset($m['match_type']) ? $m['match_type'] : '';
    $round = isset($m['round']) ? mb_strtolower($m['round'], 'UTF-8') : '';
    // key: qualify | r16 | quarter | semi | third | final
    if ($type === 'group' || $type === 'playoff') {
        return 'qualify';
    }
    if (strpos($round, 'chung kết') !== false) return 'final';
    if (strpos($round, 'hạng 3') !== false || strpos($round, 'ba-tư') !== false || strpos($round, 'tranh hạng') !== false) return 'third';
    if (strpos($round, 'bán kết') !== false) return 'semi';
    if (strpos($round, 'tứ kết') !== false) return 'quarter';
    if (strpos($round, '1/16') !== false) return 'r16';
    return 'qualify';
};

/** Tên hiển thị đơn vị/đội cho một bên. */
$teamOrgName = function ($team) {
    if (!$team) return '';
    if (!empty($team['property_name'])) return $team['property_name'];
    return isset($team['name']) ? $team['name'] : '';
};

/** Danh sách cầu thủ (gộp) cho một bên. */
$teamPlayers = function ($team) {
    if (!$team || empty($team['players'])) return '';
    return implode(' - ', $team['players']);
};
?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Biên bản trận đấu — <?php echo CHtml::encode($sportName); ?></title>
<style>
    * { box-sizing: border-box; }
    body {
        font-family: "Times New Roman", Times, serif;
        font-size: 13px;
        color: #000;
        margin: 0;
        background: #e9e9e9;
    }
    .toolbar {
        position: sticky; top: 0;
        background: #fff; border-bottom: 1px solid #ccc;
        padding: 10px 16px; text-align: center;
    }
    .toolbar button {
        font-size: 14px; padding: 8px 18px; cursor: pointer;
        border: 0; border-radius: 6px; background: #0d6efd; color: #fff;
    }
    .toolbar .muted { color: #666; font-size: 12px; margin-left: 10px; }
    .page {
        width: 210mm; min-height: 297mm;
        margin: 12px auto; padding: 10mm;
        background: #fff; box-shadow: 0 0 6px rgba(0,0,0,.2);
    }
    .minutes { }
    .cut {
        border: 0; border-top: 1px dashed #000;
        margin: 8mm 0; position: relative; text-align: center;
    }
    .cut span { position: relative; top: -10px; background: #fff; padding: 0 8px; font-size: 11px; color: #555; }
    h1 { text-align: center; font-size: 16px; margin: 0; text-transform: uppercase; }
    h2 { text-align: center; font-size: 14px; margin: 2px 0 8px; font-weight: bold; }
    .meta-line { margin: 3px 0; }
    .boxrow { margin: 5px 0; }
    .boxrow .bx { margin-right: 14px; white-space: nowrap; }
    table { width: 100%; border-collapse: collapse; margin: 6px 0; }
    table.info td { border: 1px solid #000; padding: 4px 6px; vertical-align: top; }
    table.result th, table.result td { border: 1px solid #000; padding: 5px 6px; text-align: center; }
    table.result th { background: #f0f0f0; }
    .section-title { font-weight: bold; margin: 8px 0 2px; }
    .sign td { border: 0; text-align: center; padding-top: 6px; vertical-align: top; width: 25%; }
    .sign .role { font-weight: bold; }
    .sign .space { height: 48px; }
    .fill { display: inline-block; min-width: 40px; border-bottom: 1px dotted #000; }
    @media print {
        body { background: #fff; }
        .toolbar { display: none; }
        .page { margin: 0; box-shadow: none; width: auto; min-height: auto; padding: 8mm; page-break-after: always; }
        .page:last-child { page-break-after: auto; }
    }
</style>
</head>
<body>

<div class="toolbar">
    <button type="button" onclick="window.print()">🖨 In biên bản</button>
    <span class="muted">
        <?php echo CHtml::encode($sportName); ?> — <?php echo CHtml::encode($stage['name']); ?>
        · <?php echo count($matches); ?> trận
    </span>
</div>

<?php if (empty($matches)): ?>
    <div class="page">
        <p style="text-align:center;margin-top:40px;">
            Chưa có trận đấu nào đã xác định đủ hai đội/VĐV cho giai đoạn này.
        </p>
    </div>
<?php else: ?>

<?php foreach ($matches as $m): ?>
    <?php
    $rKey = $roundInfo($m);
    $orgA = $teamOrgName($m['team_a']);
    $orgB = $teamOrgName($m['team_b']);
    $playersA = $teamPlayers($m['team_a']);
    $playersB = $teamPlayers($m['team_b']);
    $leadA = ($m['team_a'] && $m['team_a']['team_lead']) ? $m['team_a']['team_lead'] : '';
    $leadB = ($m['team_b'] && $m['team_b']['team_lead']) ? $m['team_b']['team_lead'] : '';
    $captA = ($m['team_a'] && $m['team_a']['captain']) ? $m['team_a']['captain'] : '';
    $captB = ($m['team_b'] && $m['team_b']['captain']) ? $m['team_b']['captain'] : '';
    $code = isset($m['code']) ? $m['code'] : '';
    $location = isset($m['location']) ? $m['location'] : '';

    // Render 1 bản biên bản (dùng lại cho cả bản lưu & bản cắt rời).
    ob_start();
    ?>
    <div class="minutes">
        <h1>Biên bản trận đấu</h1>
        <h2>Môn: <?php echo CHtml::encode($sportName); ?></h2>

        <div class="meta-line">
            Thời gian bắt đầu: Từ …… giờ …… phút, ngày …… tháng …… <?php echo $year; ?>
        </div>
        <div class="meta-line">
            Mã số trận: <strong><?php echo CHtml::encode($code); ?></strong>
            &nbsp;&nbsp;Bàn số: <?php echo $box(false); ?>1 &nbsp; <?php echo $box(false); ?>2
            <?php if ($location !== ''): ?>
                &nbsp;&nbsp;Địa điểm: <?php echo CHtml::encode($location); ?>
            <?php endif; ?>
        </div>

        <div class="boxrow">
            <span class="bx"><?php echo $box(!$isDoubles); ?> Đơn</span>
            <span class="bx"><?php echo $box($isDoubles); ?> Đôi</span>
            &nbsp;|&nbsp;
            <span class="bx"><?php echo $box(!$isFemale); ?> Nam</span>
            <span class="bx"><?php echo $box($isFemale); ?> Nữ</span>
        </div>
        <div class="boxrow">
            <span class="bx"><?php echo $box($rKey === 'qualify'); ?> Vòng loại</span>
            <span class="bx"><?php echo $box($rKey === 'r16'); ?> Vòng 1/16</span>
            <span class="bx"><?php echo $box($rKey === 'quarter'); ?> Tứ kết</span>
            <span class="bx"><?php echo $box($rKey === 'semi'); ?> Bán kết</span>
            <span class="bx"><?php echo $box($rKey === 'third'); ?> Ba-Tư</span>
            <span class="bx"><?php echo $box($rKey === 'final'); ?> Chung kết</span>
        </div>
        <div class="meta-line">Trận: <?php echo CHtml::encode(isset($m['round']) ? $m['round'] : ''); ?></div>

        <div class="section-title">Thông tin Đội/ VĐV thi đấu:</div>
        <table class="info">
            <tr>
                <td style="width:50%;"><strong>Đội/ VĐV số 1</strong></td>
                <td style="width:50%;"><strong>Đội/ VĐV số 2</strong></td>
            </tr>
            <tr>
                <td>Tên đội/ Cty: <strong><?php echo CHtml::encode($orgA); ?></strong></td>
                <td>Tên đội/ Cty: <strong><?php echo CHtml::encode($orgB); ?></strong></td>
            </tr>
            <tr>
                <td>Trưởng đoàn: <?php echo CHtml::encode($leadA); ?> &nbsp; ĐT: <span class="fill">&nbsp;</span></td>
                <td>Trưởng đoàn: <?php echo CHtml::encode($leadB); ?> &nbsp; ĐT: <span class="fill">&nbsp;</span></td>
            </tr>
            <tr>
                <td>Đội trưởng: <?php echo CHtml::encode($captA); ?> &nbsp; ĐT: <span class="fill">&nbsp;</span></td>
                <td>Đội trưởng: <?php echo CHtml::encode($captB); ?> &nbsp; ĐT: <span class="fill">&nbsp;</span></td>
            </tr>
            <tr>
                <td>Cầu thủ: <strong><?php echo CHtml::encode($playersA); ?></strong> &nbsp; ĐT: <span class="fill">&nbsp;</span></td>
                <td>Cầu thủ: <strong><?php echo CHtml::encode($playersB); ?></strong> &nbsp; ĐT: <span class="fill">&nbsp;</span></td>
            </tr>
        </table>

        <div class="section-title">Kết quả: Bóng bàn/ Điểm set (khoanh tròn tên đội thắng):</div>
        <table class="result">
            <thead>
                <tr>
                    <th style="width:70px;">Set thứ</th>
                    <th><?php echo CHtml::encode($orgA !== '' ? $orgA : 'Đội/ VĐV số 1'); ?></th>
                    <th><?php echo CHtml::encode($orgB !== '' ? $orgB : 'Đội/ VĐV số 2'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <tr><td><?php echo $i; ?></td><td>&nbsp;</td><td>&nbsp;</td></tr>
                <?php endfor; ?>
            </tbody>
        </table>

        <div class="meta-line">
            Thời gian kết thúc: lúc …… giờ …… phút, ngày …… tháng …… <?php echo $year; ?>
        </div>

        <table class="sign">
            <tr>
                <td class="role">Đội/ VĐV số 1</td>
                <td class="role">Trọng tài</td>
                <td class="role">Giám sát</td>
                <td class="role">Đội/ VĐV số 2</td>
            </tr>
            <tr><td class="space"></td><td class="space"></td><td class="space"></td><td class="space"></td></tr>
        </table>
    </div>
    <?php
    $block = ob_get_clean();
    ?>
    <div class="page">
        <?php echo $block; ?>
        <div class="cut"><span>✂ cắt tại đây</span></div>
        <?php echo $block; ?>
    </div>
<?php endforeach; ?>

<?php endif; ?>

</body>
</html>
