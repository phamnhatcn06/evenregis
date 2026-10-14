<?php

/**
 * Sport Stage Setup Command — Dựng đủ CẤU TRÚC giai đoạn cho các nội dung thi nhiều giai đoạn
 * theo docs/The_le.xlsx, trước khi chạy sportstagebackfill (backfill chỉ sửa cấu hình, không tạo stage).
 *
 *   - Bóng bàn đơn nam:       Play-off (mới, thứ tự 1)        -> vòng bảng + KO (stage có sẵn, thứ tự 2)
 *   - Bóng bàn đơn nữ:        Vòng loại 3 bảng (stage có sẵn) -> Vòng chung kết vòng tròn (mới, trống)
 *   - Cầu lông đôi nam - nữ:  Vòng loại 3 bảng (stage có sẵn) -> Vòng chung kết vòng tròn (mới, trống)
 *
 * Stage CÓ SẴN của nội dung (đang chứa VĐV) được giữ nguyên VĐV/trận, chỉ đổi tên/loại/thứ tự.
 * Stage MỚI được tạo trống (VĐV vào qua nút "Chuyển người thắng / đội đi tiếp").
 * Tìm nội dung theo TÊN, giai đoạn mới theo TÊN (hoặc stage_type=playoff) -> chạy lại không tạo trùng.
 *
 * Yêu cầu backend đã có migration 2026_10_15_100010 (giá trị 'playoff' cho format).
 *
 * Usage:
 *   php yiiCmd.php sportstagesetup --eventId=3              # DRY-RUN: in kế hoạch
 *   php yiiCmd.php sportstagesetup --eventId=3 --commit=1   # Thực thi
 */
class SportStageSetupCommand extends CConsoleCommand
{
    public $defaultAction = 'index';

    const NTD = 'Nhà thi đấu Ninh Bình';

    /**
     * Cấu trúc mục tiêu theo tên nội dung.
     * - main: stage đang có của nội dung (giữ VĐV) -> đổi name/stage_type/stage_order
     * - new:  stage cần tạo thêm (trống)
     */
    protected function structureConfig()
    {
        $groupFinal = array(
            'name' => 'Vòng chung kết (Tranh 1-2-3)', 'stage_type' => 'final', 'stage_order' => 2,
            'format' => SportStages::FORMAT_ROUND_ROBIN, 'location' => self::NTD,
        );
        $groupQual = array(
            'name' => 'Vòng loại (3 bảng)', 'stage_type' => 'qualification', 'stage_order' => 1,
        );

        return array(
            'Bóng bàn đơn nam' => array(
                'main' => array('stage_type' => 'final', 'stage_order' => 2),
                'new' => array(
                    'name' => 'Play-off (chọn 8 đội)', 'stage_type' => 'playoff', 'stage_order' => 1,
                    'format' => SportStages::FORMAT_PLAYOFF, 'location' => self::NTD,
                    'rules' => '5 hiệp thắng 3 (11đ/séc, cách biệt 2đ). 2 trận play-off chọn 2 suất cuối vào vòng bảng (tổng 8 đội).',
                ),
            ),
            'Bóng bàn đơn nữ' => array('main' => $groupQual, 'new' => $groupFinal),
            'Cầu lông đôi nam - nữ' => array('main' => $groupQual, 'new' => $groupFinal),
        );
    }

    public function actionIndex($eventId = null, $commit = 0)
    {
        $commit = (int) $commit;
        if (!$eventId) {
            echo "ERROR: Thiếu --eventId=<id>.\n";
            return 1;
        }
        echo $commit ? ">>> CHE DO GHI (COMMIT)\n" : ">>> CHE DO DRY-RUN (them --commit=1 de thuc thi)\n";
        echo "Event ID: {$eventId}\n\n";

        $stagesBySport = $this->loadStagesBySport($eventId);

        foreach ($this->structureConfig() as $sportName => $cfg) {
            $stages = isset($stagesBySport[$this->normalize($sportName)]) ? $stagesBySport[$this->normalize($sportName)] : array();
            if (empty($stages)) {
                echo "[BO QUA] {$sportName}: khong co giai doan nao trong event.\n";
                continue;
            }
            echo "== {$sportName} ==\n";

            // Phân loại: stage "mới" đã tồn tại chưa, còn lại là ứng viên "chính"
            $newStage = null;
            $mainCandidates = array();
            foreach ($stages as $s) {
                if ($this->isNewStage($s, $cfg['new'])) {
                    $newStage = $s;
                } else {
                    $mainCandidates[] = $s;
                }
            }

            $main = $this->pickMain($mainCandidates, $cfg['main']);
            if ($main === null) {
                echo "  [LOI] Khong xac dinh duoc giai doan chinh (" . count($mainCandidates) . " ung vien). Xu ly thu cong.\n";
                continue;
            }
            $this->ensureMain($main, $cfg['main'], $commit);

            if ($newStage) {
                echo "  [CO SAN] {$newStage->name} (id={$newStage->id})\n";
                $this->ensureMain($newStage, array(
                    'name' => $cfg['new']['name'],
                    'stage_type' => $cfg['new']['stage_type'],
                    'stage_order' => $cfg['new']['stage_order'],
                ), $commit);
            } else {
                $this->createNew($eventId, $main->sport_id, $cfg['new'], $commit);
            }
        }

        echo "\nHoan tat. Buoc tiep theo: php yiiCmd.php sportstagebackfill --eventId={$eventId}\n";
        return 0;
    }

    protected function isNewStage($stage, $newCfg)
    {
        if ($this->normalize($stage->name) === $this->normalize($newCfg['name'])) {
            return true;
        }
        return $newCfg['stage_type'] === 'playoff' && $stage->stage_type === 'playoff';
    }

    /**
     * Chọn stage chính: duy nhất 1 ứng viên, hoặc ứng viên đã mang đúng tên mục tiêu.
     */
    protected function pickMain(array $candidates, $mainCfg)
    {
        if (count($candidates) === 1) {
            return $candidates[0];
        }
        if (isset($mainCfg['name'])) {
            foreach ($candidates as $c) {
                if ($this->normalize($c->name) === $this->normalize($mainCfg['name'])) {
                    return $c;
                }
            }
        }
        return null;
    }

    protected function ensureMain($stage, $mainCfg, $commit)
    {
        $changes = array();
        foreach (array('name', 'stage_type', 'stage_order') as $field) {
            if (isset($mainCfg[$field]) && (string) $stage->$field !== (string) $mainCfg[$field]) {
                $changes[$field] = array($stage->$field, $mainCfg[$field]);
            }
        }
        if (empty($changes)) {
            echo "  [DUNG]   {$stage->name} (id={$stage->id}) da dung cau truc\n";
            return;
        }
        $desc = implode(', ', array_map(function ($f, $v) {
            return "{$f}: \"{$v[0]}\" -> \"{$v[1]}\"";
        }, array_keys($changes), $changes));
        if ($stage->draw_status && $stage->draw_status !== SportStages::DRAW_NOT_DRAWN) {
            echo "  [CANH BAO] stage id={$stage->id} da boc tham ({$stage->draw_status}) — doi cau truc co the can boc lai.\n";
        }
        if (!$commit) {
            echo "  [SE CAP NHAT] {$stage->name} (id={$stage->id}): {$desc}\n";
            return;
        }

        $fields = array();
        foreach ($changes as $field => $v) {
            $fields[$field] = $v[1];
        }
        $result = SportStages::updateStructureViaApi($stage->id, $fields);
        echo !empty($result['success'])
            ? "  [CAP NHAT OK] id={$stage->id}: {$desc}\n"
            : "  [LOI] id={$stage->id}: " . $this->errMsg($result) . "\n";
    }

    protected function createNew($eventId, $sportId, $newCfg, $commit)
    {
        if (!$commit) {
            echo "  [SE TAO] \"{$newCfg['name']}\" (stage_type={$newCfg['stage_type']}, thu tu {$newCfg['stage_order']}, format={$newCfg['format']})\n";
            return;
        }
        $model = new SportStages;
        $model->setAttributes(array_merge($newCfg, array(
            'event_id' => $eventId,
            'sport_id' => $sportId,
            'status' => 1,
        )), false);
        $result = $model->storeViaApi();
        if (empty($result['success'])) {
            echo "  [LOI] Tao \"{$newCfg['name']}\": " . $this->errMsg($result) . "\n";
            return;
        }
        $id = $this->extractId($result);
        echo "  [TAO OK] \"{$newCfg['name']}\" id={$id}\n";

        // format ngoài ENUM bị MySQL lưu rỗng -> cảnh báo thiếu migration
        $created = ($id !== '?') ? SportStages::fetchFromApi($id) : null;
        if ($created && $created->format !== $newCfg['format']) {
            echo "  CANH BAO: format luu thanh \"{$created->format}\" thay vi \"{$newCfg['format']}\" — kiem tra migration "
                . "2026_10_15_100010_add_playoff_format_to_sport_stages_table.\n";
        }
    }

    protected function loadStagesBySport($eventId)
    {
        $map = array();
        foreach (SportStages::getApiDataProvider(array('event_id' => $eventId), 10000)->getData() as $row) {
            $map[$this->normalize($row->sport_name)][] = $row;
        }
        return $map;
    }

    protected function normalize($s)
    {
        return trim(preg_replace('/\s+/u', ' ', mb_strtolower((string) $s, 'UTF-8')));
    }

    protected function extractId($result)
    {
        $d = isset($result['data']) ? $result['data'] : null;
        if (is_array($d)) {
            if (isset($d['data']['id'])) {
                return $d['data']['id'];
            }
            if (isset($d['id'])) {
                return $d['id'];
            }
        }
        return '?';
    }

    protected function errMsg($result)
    {
        if (!empty($result['error'])) {
            return is_array($result['error']) ? json_encode($result['error'], JSON_UNESCAPED_UNICODE) : $result['error'];
        }
        return 'Unknown error (code=' . (isset($result['code']) ? $result['code'] : '?') . ')';
    }
}
