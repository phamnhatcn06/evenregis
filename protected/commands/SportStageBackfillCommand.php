<?php

/**
 * Sport Stage Backfill Command — Chuẩn hoá cấu hình thể thức + điền thể lệ (rules)
 * cho các giai đoạn (sport_stages) đã tồn tại, khớp theo file docs/The_le.xlsx.
 *
 * KHÁC SportSeedCommand: command này KHÔNG tạo mới sport/stage (tránh trùng),
 * chỉ UPDATE các stage sẵn có trong event — khớp theo `sport_name` + tên giai đoạn.
 * Bản trùng/thừa chỉ được LIỆT KÊ để người dùng tự duyệt (không tự xoá).
 *
 * Luôn gọi qua Model -> ApiClient (không ghi DB trực tiếp).
 *
 * Usage:
 *   php protected/yiic sportstagebackfill --eventId=3            # DRY-RUN: in kế hoạch
 *   php protected/yiic sportstagebackfill --eventId=3 --commit=1 # Thực thi cập nhật
 */
class SportStageBackfillCommand extends CConsoleCommand
{
    public $defaultAction = 'index';

    const RACKET_15 = '3 hiệp thắng 2 (chạm 15đ, ăn điểm trực tiếp). Bốc thăm chia cặp 8 đội, loại trực tiếp tranh 1-2-3-4.';

    public function actionIndex($eventId = null, $commit = 0)
    {
        $commit = (int) $commit;
        if (!$eventId) {
            echo "ERROR: Thiếu --eventId=<id>.\n";
            return 1;
        }

        echo $commit ? ">>> CHE DO GHI (COMMIT) — se cap nhat qua API\n"
                     : ">>> CHE DO DRY-RUN — chi in ke hoach (them --commit=1 de thuc thi)\n";
        echo "Event ID: {$eventId}\n\n";

        $stages = $this->loadStages($eventId);
        if (empty($stages)) {
            echo "Khong co stage nao trong event.\n";
            return 0;
        }

        $specs = $this->specsBySportName();
        $seenFinalByName = array(); // phát hiện trùng

        echo "=== BACKFILL STAGES ===\n";
        foreach ($stages as $st) {
            $sportName = isset($st['sport_name']) ? $st['sport_name'] : '';
            $stageName = isset($st['name']) ? $st['name'] : '';
            $id = $st['id'];

            // Nội dung thừa (không có trong The_le)
            if (!isset($specs[$sportName])) {
                echo "  [THUA]  id={$id} \"{$sportName} / {$stageName}\" — khong co trong The_le, bo qua.\n";
                continue;
            }

            $spec = $this->pickSpec($specs[$sportName], $st);
            if ($spec === null) {
                echo "  [? ]    id={$id} \"{$sportName} / {$stageName}\" — khong map duoc giai doan, bo qua.\n";
                continue;
            }

            // Phát hiện trùng (cùng sport + cùng tên giai đoạn mục tiêu)
            $dupKey = $sportName . '|' . $spec['match'];
            if (isset($seenFinalByName[$dupKey])) {
                echo "  [TRUNG] id={$id} \"{$sportName} / {$stageName}\" — da co ban {$seenFinalByName[$dupKey]} cho giai doan nay. Van cap nhat, NEN xoa thu cong ban trung.\n";
            } else {
                $seenFinalByName[$dupKey] = $id;
            }

            $this->applySpec($id, $stageName, $st, $spec, $commit);
        }

        echo "\nHoan tat.\n";
        return 0;
    }

    /**
     * Chọn spec phù hợp cho stage: với nội dung nhiều giai đoạn, phân biệt theo tên.
     */
    protected function pickSpec($spec, $st)
    {
        // Giai đoạn play-off có cấu hình riêng, không áp spec của vòng chính
        if (isset($st['stage_type']) && $st['stage_type'] === 'playoff') {
            return null;
        }
        // Nội dung 1 giai đoạn
        if (isset($spec['format'])) {
            return array_merge($spec, array('match' => 'single'));
        }
        // Nội dung nhiều giai đoạn: 'qualification' + 'final'
        $name = mb_strtolower(isset($st['name']) ? $st['name'] : '', 'UTF-8');
        $type = isset($st['stage_type']) ? $st['stage_type'] : '';
        $isQual = (strpos($name, 'loại') !== false || strpos($name, 'bảng') !== false || $type === 'qualification');
        if ($isQual && isset($spec['qualification'])) {
            return array_merge($spec['qualification'], array('match' => 'qualification'));
        }
        if (isset($spec['final'])) {
            return array_merge($spec['final'], array('match' => 'final'));
        }
        return null;
    }

    protected function applySpec($id, $stageName, $st, $spec, $commit)
    {
        $plan = sprintf(
            "fmt=%s grp=%s adv=%s brk=%s diem=%s tb=%s ghep=%s",
            $spec['format'],
            isset($spec['num_groups']) ? $spec['num_groups'] : '-',
            isset($spec['advance_per_group']) ? $spec['advance_per_group'] : '-',
            isset($spec['bracket_size']) ? $spec['bracket_size'] : '-',
            isset($spec['win_points']) ? ($spec['win_points'] . '-' . $spec['draw_points'] . '-' . $spec['loss_points']) : '-',
            isset($spec['tiebreak']) ? $spec['tiebreak'] : '-',
            isset($spec['ko_pairing']) ? $spec['ko_pairing'] : '-'
        );

        if (!$commit) {
            echo "  [SE CAP NHAT] id={$id} \"{$st['sport_name']} / {$stageName}\" => {$plan}\n";
            return;
        }

        $model = new SportStages;
        $model->id = $id;
        $model->name = $stageName; // giữ nguyên tên
        $model->format = $spec['format'];
        $model->num_groups = isset($spec['num_groups']) ? $spec['num_groups'] : '';
        $model->teams_per_group = isset($spec['teams_per_group']) ? $spec['teams_per_group'] : '';
        $model->advance_per_group = isset($spec['advance_per_group']) ? $spec['advance_per_group'] : '';
        $model->bracket_size = isset($spec['bracket_size']) ? $spec['bracket_size'] : '';
        $model->rules = isset($spec['rules']) ? $spec['rules'] : '';
        foreach (array('win_points', 'draw_points', 'loss_points', 'tiebreak', 'ko_pairing') as $name) {
            $model->$name = isset($spec[$name]) ? $spec[$name] : '';
        }

        $result = $model->updateConfigViaApi();
        if (!empty($result['success'])) {
            echo "  [OK]    id={$id} \"{$st['sport_name']} / {$stageName}\" => {$plan}\n";
        } else {
            echo "  [LOI]   id={$id}: " . $this->errMsg($result) . "\n";
        }
    }

    /**
     * Cấu hình mục tiêu theo tên nội dung (sport_name trong DB).
     * - Nội dung 1 giai đoạn: mảng phẳng có 'format'.
     * - Nội dung nhiều giai đoạn: có khoá 'qualification' và 'final'.
     */
    protected function specsBySportName()
    {
        $KO8 = function ($rules) {
            return array('format' => 'single_elimination', 'bracket_size' => 8, 'rules' => $rules);
        };
        $racket = self::RACKET_15;

        // Cách tính điểm vòng bảng theo điều lệ từng môn (docs/dieule)
        $football = array('win_points' => 3, 'draw_points' => 1, 'loss_points' => 0, 'tiebreak' => 'goal_difference');
        $winTwoLossOne = array('win_points' => 2, 'draw_points' => 1, 'loss_points' => 1, 'tiebreak' => 'head_to_head');
        $winsOnly = array('win_points' => 1, 'draw_points' => 0, 'loss_points' => 0, 'tiebreak' => 'goal_difference');

        return array(
            // Bóng đá
            'Bóng đá nam' => $KO8('25 phút/hiệp. Bốc thăm chia cặp, loại trực tiếp tranh 1-2-3-4.'),
            'Bóng đá nữ' => array(
                'format' => 'round_robin_knockout', 'num_groups' => 2, 'teams_per_group' => 3,
                'advance_per_group' => 2, 'bracket_size' => 4, 'ko_pairing' => 'placement',
                'rules' => '20 phút/hiệp. Bốc thăm chia 2 bảng, đấu vòng tròn (thắng 3đ, hoà 1đ, thua 0đ; bằng điểm xét hiệu số → bàn thắng → đối đầu). Nhất bảng vào tranh 1-2, Nhì bảng vào tranh 3-4.',
            ) + $football,

            // Kéo co
            'Kéo co phối hợp Nam - Nữ' => array(
                'format' => 'round_robin_knockout', 'num_groups' => 2, 'teams_per_group' => 4,
                'advance_per_group' => 2, 'bracket_size' => 4, 'ko_pairing' => 'cross',
                'win_points' => 2, 'draw_points' => 0, 'loss_points' => 0, 'tiebreak' => 'head_to_head',
                'rules' => '3 hiệp thắng 2. Bốc thăm 2 bảng đấu vòng tròn (thắng 2đ, thua 0đ, bỏ cuộc 0đ; bằng điểm xét đối đầu → hiệu số hiệp) chọn Nhất-Nhì; bán kết đấu chéo; chung kết loại trực tiếp tranh 1-2-3-4.',
            ),

            // Bóng bàn đơn nam — play-off (stage riêng) rồi vòng bảng + KO
            'Bóng bàn đơn nam' => array(
                'format' => 'round_robin_knockout', 'num_groups' => 2, 'teams_per_group' => 4,
                'advance_per_group' => 2, 'bracket_size' => 4, 'ko_pairing' => 'cross',
                'rules' => 'Vòng bảng: 3 hiệp thắng 2 (11đ/séc, cách biệt 2đ); thắng 2đ, thua 1đ, bằng điểm xét đối đầu → hiệu số séc. VCK: 5 hiệp thắng 3. 2 bảng vòng tròn, Nhất-Nhì mỗi bảng vào bán kết, chung kết tranh 1-2-3-4.',
            ) + $winTwoLossOne,

            // Bóng bàn đơn nữ — vòng loại 3 bảng + CK vòng tròn 3
            'Bóng bàn đơn nữ' => array(
                'qualification' => array(
                    'format' => 'round_robin', 'num_groups' => 3, 'teams_per_group' => 4, 'advance_per_group' => 1,
                    'rules' => 'Vòng loại: 3 hiệp thắng 2 (11đ/séc, cách biệt 2đ); thắng 2đ, thua 1đ, bằng điểm xét đối đầu → hiệu số séc. Bốc thăm 3 bảng (4+4+3) đấu vòng tròn, chọn Nhất mỗi bảng.',
                ) + $winTwoLossOne,
                'final' => array(
                    'format' => 'round_robin', 'num_groups' => 1, 'teams_per_group' => 3, 'advance_per_group' => 3,
                    'rules' => 'VCK: 5 hiệp thắng 3 (11đ/séc, cách biệt 2đ). Nhất 3 bảng vào đấu vòng tròn tranh 1-2-3 (thắng 2đ, thua 1đ).',
                ) + $winTwoLossOne,
            ),

            // Cầu lông đơn/đôi — loại trực tiếp
            'Cầu lông đơn nam' => $KO8($racket),
            'Cầu lông đơn nữ' => $KO8($racket),
            'Cầu lông đôi nam' => $KO8($racket),
            'Cầu lông đôi nữ' => $KO8($racket),

            // Cầu lông đôi nam - nữ — 3 bảng + CK vòng tròn 3
            'Cầu lông đôi nam - nữ' => array(
                'qualification' => array(
                    'format' => 'round_robin', 'num_groups' => 3, 'teams_per_group' => 3, 'advance_per_group' => 1,
                    'rules' => '1 hiệp chạm 21đ. Bốc thăm 3 bảng đấu vòng tròn, xếp theo số trận thắng (bằng nhau xét hiệu số ván → hiệu số điểm), chọn Nhất mỗi bảng.',
                ) + $winsOnly,
                'final' => array(
                    'format' => 'round_robin', 'num_groups' => 1, 'teams_per_group' => 3, 'advance_per_group' => 3,
                    'rules' => '1 hiệp chạm 21đ. Nhất 3 bảng vào đấu vòng tròn tranh 1-2-3, xếp theo số trận thắng.',
                ) + $winsOnly,
            ),

            // Pickleball — loại trực tiếp
            'Pickleball đôi nam' => $KO8($racket),
            'Pickleball đôi nữ' => $KO8($racket),
            'Pickleball đôi nam - nữ' => $KO8($racket),

            // Bơi (6 nội dung) — phân lượt/làn, tính thời gian
            'Bơi tự do Nam - cự ly 40m' => $this->swimSpec(),
            'Bơi tự do Nam - cự ly 80m' => $this->swimSpec(),
            'Bơi tự do Nữ - cự ly 40m' => $this->swimSpec(),
            'Bơi ếch Nam - cự ly 40m' => $this->swimSpec(),
            'Bơi ếch Nam - cự ly 80m' => $this->swimSpec(),
            'Bơi ếch Nữ - cự ly 40m' => $this->swimSpec(),
        );
    }

    protected function swimSpec()
    {
        // Bơi chỉ thi 1 giai đoạn Vòng chung kết (thí sinh BTC gắn sẵn).
        return array(
            'format' => 'heat_lane',
            'rules' => 'Tính thời gian. Thi chung kết, phân lượt/làn (6 làn/lượt); xếp hạng 1-2-3 theo thành tích.',
        );
    }

    protected function loadStages($eventId)
    {
        $data = array();
        try {
            $rows = SportStages::getApiDataProvider(array('event_id' => $eventId), 10000)->getData();
            foreach ($rows as $row) {
                $data[] = is_object($row) ? $row->attributes + array(
                    'sport_name' => isset($row->sport_name) ? $row->sport_name : '',
                    'format' => isset($row->format) ? $row->format : '',
                    'stage_type' => isset($row->stage_type) ? $row->stage_type : '',
                ) : $row;
            }
        } catch (Exception $e) {
            echo "CANH BAO: Khong tai duoc stages ({$e->getMessage()}).\n";
        }
        echo "Stages hien co: " . count($data) . " ban ghi.\n\n";
        return $data;
    }

    protected function errMsg($result)
    {
        if (isset($result['error']) && $result['error']) {
            return is_array($result['error']) ? json_encode($result['error'], JSON_UNESCAPED_UNICODE) : $result['error'];
        }
        return 'Unknown error (code=' . (isset($result['code']) ? $result['code'] : '?') . ')';
    }
}
