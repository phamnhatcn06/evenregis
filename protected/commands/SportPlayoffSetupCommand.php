<?php

/**
 * Sport Playoff Setup Command — Tạo giai đoạn Play-off cho các nội dung có vòng play-off
 * trước vòng bảng (hiện tại: Bóng bàn đơn nam — 2 trận play-off chọn đủ 8 VĐV, theo docs/The_le.xlsx).
 *
 * Với mỗi nội dung cấu hình bên dưới:
 *   1. Nếu chưa có stage_type = playoff -> tạo stage Play-off (stage_order = 1, format = playoff).
 *   2. Lùi các giai đoạn còn lại của nội dung lên sau play-off (stage_order = 2, 3...).
 * Tìm nội dung theo TÊN (sport_name), không theo id -> dùng được trên mọi DB (local/prod).
 * Chạy lại nhiều lần an toàn: đã có play-off thì bỏ qua, thứ tự đúng rồi thì không cập nhật.
 *
 * Yêu cầu: backend đã chạy migration 2026_10_15_100010_add_playoff_format_to_sport_stages_table
 * (nếu chưa, MySQL lưu format rỗng — command sẽ cảnh báo sau khi tạo).
 *
 * Usage:
 *   php yiiCmd.php sportplayoffsetup --eventId=3              # DRY-RUN: in kế hoạch
 *   php yiiCmd.php sportplayoffsetup --eventId=3 --commit=1   # Thực thi
 */
class SportPlayoffSetupCommand extends CConsoleCommand
{
    public $defaultAction = 'index';

    /**
     * Nội dung cần play-off: tên nội dung => cấu hình stage play-off.
     */
    protected function playoffConfig()
    {
        return array(
            'Bóng bàn đơn nam' => array(
                'name' => 'Play-off (chọn 8 đội)',
                'location' => 'Nhà thi đấu Ninh Bình',
                'rules' => '5 hiệp thắng 3 (11đ/séc, cách biệt 2đ). 2 trận play-off chọn 2 suất cuối vào vòng bảng (tổng 8 đội).',
            ),
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

        foreach ($this->playoffConfig() as $sportName => $cfg) {
            $key = $this->normalize($sportName);
            if (empty($stagesBySport[$key])) {
                echo "[BO QUA] {$sportName}: khong tim thay giai doan nao cua noi dung nay trong event.\n";
                continue;
            }
            $stages = $stagesBySport[$key];
            $sportId = $stages[0]->sport_id;

            $playoff = null;
            $others = array();
            foreach ($stages as $s) {
                if ($s->stage_type === 'playoff') {
                    $playoff = $s;
                } else {
                    $others[] = $s;
                }
            }

            // 1. Tạo stage play-off nếu chưa có
            if ($playoff) {
                echo "[CO SAN] {$sportName}: play-off id={$playoff->id} \"{$playoff->name}\" (format=" . ($playoff->format ?: 'RONG') . ")\n";
            } elseif (!$commit) {
                echo "[SE TAO] {$sportName}: \"{$cfg['name']}\" (stage_type=playoff, stage_order=1, format=playoff)\n";
            } else {
                $this->createPlayoff($eventId, $sportId, $sportName, $cfg);
            }

            // 2. Đưa các giai đoạn còn lại ra sau play-off, giữ thứ tự tương đối hiện có
            usort($others, function ($a, $b) {
                return ((int) $a->stage_order - (int) $b->stage_order) ?: ((int) $a->id - (int) $b->id);
            });
            $order = 2;
            foreach ($others as $s) {
                if ((int) $s->stage_order === $order) {
                    echo "[DUNG]   {$sportName} / {$s->name} (id={$s->id}) da o thu tu {$order}\n";
                } elseif (!$commit) {
                    echo "[SE CAP NHAT] {$sportName} / {$s->name} (id={$s->id}): stage_order {$s->stage_order} -> {$order}\n";
                } else {
                    $this->updateOrder($s, $order, $sportName);
                }
                $order++;
            }
        }

        echo "\nHoan tat.\n";
        return 0;
    }

    protected function createPlayoff($eventId, $sportId, $sportName, $cfg)
    {
        $model = new SportStages;
        $model->setAttributes(array(
            'event_id' => $eventId,
            'sport_id' => $sportId,
            'name' => $cfg['name'],
            'stage_type' => 'playoff',
            'stage_order' => 1,
            'format' => SportStages::FORMAT_PLAYOFF,
            'location' => $cfg['location'],
            'rules' => $cfg['rules'],
            'status' => 1,
        ), false);

        $result = $model->storeViaApi();
        if (empty($result['success'])) {
            echo "[LOI]    {$sportName}: " . $this->errMsg($result) . "\n";
            return;
        }
        $id = $this->extractId($result);
        echo "[TAO OK] {$sportName}: play-off id={$id}\n";

        // Kiểm tra format đã lưu đúng (thiếu migration enum -> MySQL lưu rỗng)
        $created = ($id !== '?') ? SportStages::fetchFromApi($id) : null;
        if ($created && $created->format !== SportStages::FORMAT_PLAYOFF) {
            echo "  CANH BAO: format luu thanh \"" . $created->format . "\" — backend chua chay migration "
                . "2026_10_15_100010_add_playoff_format_to_sport_stages_table. Chay migration roi sua format stage {$id}.\n";
        }
    }

    protected function updateOrder($stage, $order, $sportName)
    {
        // Dựng lại từ API để giữ nguyên toàn bộ cấu hình hiện có, chỉ đổi thứ tự
        $model = SportStages::fetchFromApi($stage->id);
        if ($model === null) {
            echo "[LOI]    {$sportName} / {$stage->name}: khong tai duoc stage id={$stage->id}\n";
            return;
        }
        $model->stage_order = $order;
        $result = $model->updateConfigViaApi();
        if (!empty($result['success'])) {
            echo "[CAP NHAT OK] {$sportName} / {$stage->name} (id={$stage->id}): stage_order -> {$order}\n";
        } else {
            echo "[LOI]    {$sportName} / {$stage->name}: " . $this->errMsg($result) . "\n";
        }
    }

    /**
     * Map tên nội dung (chuẩn hoá) => danh sách giai đoạn trong event.
     */
    protected function loadStagesBySport($eventId)
    {
        $map = array();
        $rows = SportStages::getApiDataProvider(array('event_id' => $eventId), 10000)->getData();
        foreach ($rows as $row) {
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
