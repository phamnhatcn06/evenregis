<?php

/**
 * Sport Seed Command — Seed môn/nội dung thi đấu (sports) + giai đoạn (sport_stages)
 * cho Đại hội Mường Thanh 2026 theo file bốc thăm docs/Boc_tham.xlsx.
 *
 * Nguồn mapping: docs/Boc_tham.xlsx (sheet "Bang ma" = bảng mã nội dung, "Thể lệ" = thể thức).
 *
 * Luôn gọi qua Model -> ApiClient (không ghi DB trực tiếp), tôn trọng kiến trúc 2 repo.
 * Idempotent: kiểm tra theo `code` (sports) và (event_id + sport_id + name) (stages)
 * trước khi tạo, nên chạy lại nhiều lần an toàn.
 *
 * Usage:
 *   php protected/yiic sportseed --eventId=5              # DRY-RUN: chỉ in kế hoạch, không ghi
 *   php protected/yiic sportseed --eventId=5 --commit=1   # Thực thi tạo qua API
 *   php protected/yiic sportseed --eventId=5 --commit=1 --stagesOnly=1  # Chỉ seed stages
 */
class SportSeedCommand extends CConsoleCommand
{
    public $defaultAction = 'index';

    /** @var array map code => id của sports đã tồn tại/được tạo */
    protected $sportIdByCode = array();

    public function actionIndex($eventId = null, $commit = 0, $stagesOnly = 0)
    {
        $commit = (int) $commit;
        if (!$eventId) {
            echo "ERROR: Thiếu --eventId=<id> (ID sự kiện Đại hội).\n";
            return 1;
        }

        echo $commit ? ">>> CHE DO GHI (COMMIT) — se tao du lieu qua API\n"
                     : ">>> CHE DO DRY-RUN — chi in ke hoach, khong ghi (them --commit=1 de thuc thi)\n";
        echo "Event ID: {$eventId}\n\n";

        // 1. Nạp sports hiện có để idempotent
        $this->loadExistingSports();

        // 2. Seed sports (cha + con)
        if (!$stagesOnly) {
            $this->seedSports($commit);
        } else {
            echo "(Bo qua seed sports theo --stagesOnly=1)\n\n";
        }

        // 3. Seed stages
        $this->seedStages($eventId, $commit);

        echo "\nHoan tat.\n";
        return 0;
    }

    // ============================================================
    // SPORTS
    // ============================================================

    protected function loadExistingSports()
    {
        $this->sportIdByCode = array();
        try {
            $data = Sports::getApiDataProvider(array(), 10000)->getData();
            foreach ($data as $row) {
                $code = is_object($row) ? $row->code : (isset($row['code']) ? $row['code'] : null);
                $id = is_object($row) ? $row->id : (isset($row['id']) ? $row['id'] : null);
                if ($code !== null && $id !== null) {
                    $this->sportIdByCode[$code] = $id;
                }
            }
        } catch (Exception $e) {
            echo "CANH BAO: Khong tai duoc danh sach sports hien co ({$e->getMessage()}). Van tiep tuc.\n";
        }
        echo "Sports hien co: " . count($this->sportIdByCode) . " ban ghi.\n\n";
    }

    /**
     * Danh mục sports cha (category) + nội dung con (content).
     * code của con trùng Mã nội dung trong sheet "Bang ma".
     */
    protected function sportsConfig()
    {
        return array(
            // parentCode => array('name' => ..., 'children' => array(code => name))
            'BĐ' => array('name' => 'Bóng đá', 'children' => array(
                'BĐ.M' => 'Bóng đá Nam',
                'BĐ.F' => 'Bóng đá Nữ',
            )),
            'BB' => array('name' => 'Bóng bàn', 'children' => array(
                'BB.M' => 'Bóng bàn đơn Nam',
                'BB.F' => 'Bóng bàn đơn Nữ',
            )),
            'BOI' => array('name' => 'Bơi', 'children' => array(
                'BTD.M40' => 'Bơi tự do Nam 40m',
                'BTD.M80' => 'Bơi tự do Nam 80m',
                'BTD.F40' => 'Bơi tự do Nữ 40m',
                'BE.M40' => 'Bơi ếch Nam 40m',
                'BE.M80' => 'Bơi ếch Nam 80m',
                'BE.F40' => 'Bơi ếch Nữ 40m',
            )),
            'CL' => array('name' => 'Cầu lông', 'children' => array(
                'CL.M' => 'Cầu lông đơn Nam',
                'CL.F' => 'Cầu lông đơn Nữ',
                'CL.2M' => 'Cầu lông đôi Nam',
                'CL.2F' => 'Cầu lông đôi Nữ',
                'CL.MF' => 'Cầu lông đôi Nam - Nữ',
            )),
            'PK' => array('name' => 'Pickleball', 'children' => array(
                'PK.2M' => 'Pickleball đôi Nam',
                'PK.2F' => 'Pickleball đôi Nữ',
                'PK.MF' => 'Pickleball đôi Nam - Nữ',
            )),
            // Kéo co: 1 nội dung độc lập (không có cấp con)
            'KC' => array('name' => 'Kéo co (phối hợp Nam - Nữ)', 'leaf' => true, 'children' => array()),
        );
    }

    protected function seedSports($commit)
    {
        echo "=== SEED SPORTS ===\n";
        $sortOrder = 1;
        foreach ($this->sportsConfig() as $parentCode => $cfg) {
            $isLeaf = !empty($cfg['leaf']);
            // Cha (hoặc leaf đứng độc lập)
            $parentId = $this->ensureSport(
                $parentCode,
                $cfg['name'],
                null,
                $isLeaf ? 'content' : 'category',
                $sortOrder++,
                $commit
            );

            foreach ($cfg['children'] as $code => $name) {
                $this->ensureSport($code, $name, $parentId, 'content', $sortOrder++, $commit);
            }
        }
        echo "\n";
    }

    /**
     * Đảm bảo một sport tồn tại (theo code). Trả về id (hoặc placeholder khi dry-run).
     */
    protected function ensureSport($code, $name, $parentId, $type, $sortOrder, $commit)
    {
        if (isset($this->sportIdByCode[$code])) {
            echo "  [BO QUA] {$code} — da ton tai (id={$this->sportIdByCode[$code]})\n";
            return $this->sportIdByCode[$code];
        }

        $label = $parentId ? "con" : ($type === 'category' ? "cha" : "don");
        if (!$commit) {
            echo "  [SE TAO] {$code} ({$label}) \"{$name}\" parent_id=" . ($parentId ?: 'NULL') . " type={$type}\n";
            $this->sportIdByCode[$code] = "NEW:{$code}"; // placeholder để link con trong dry-run
            return $this->sportIdByCode[$code];
        }

        $model = new Sports;
        $model->code = $code;
        $model->name = $name;
        $model->parent_id = $this->isPlaceholder($parentId) ? null : $parentId;
        $model->type = $type;
        $model->is_active = 1;
        $model->sort_order = $sortOrder;

        $result = $model->storeViaApi();
        if (!empty($result['success'])) {
            $newId = $this->extractId($result);
            $this->sportIdByCode[$code] = $newId;
            echo "  [TAO OK] {$code} ({$label}) id={$newId}\n";
            return $newId;
        }

        echo "  [LOI]   {$code}: " . $this->errMsg($result) . "\n";
        return null;
    }

    // ============================================================
    // STAGES
    // ============================================================

    /**
     * Cấu hình giai đoạn cho từng nội dung.
     * format: round_robin | single_elimination | round_robin_knockout | heat_lane
     * stage_type (<=13): qualification | playoff | final
     */
    protected function stagesConfig()
    {
        $KS = 'KS Mường Thanh Ninh Bình';
        $NTD = 'Nhà thi đấu Ninh Bình';
        $BB_POOL = 'Bể bơi khách sạn';
        $TEDDY = 'Tổ hợp Teddy Bear (Xuân Thành)';

        // Loại trực tiếp thuần (1 stage final, single_elimination)
        $ko = function ($loc, $bracket = 8, $rules = '') {
            return array(array(
                'name' => 'Vòng chung kết (loại trực tiếp)',
                'stage_type' => 'final', 'stage_order' => 1,
                'format' => 'single_elimination', 'bracket_size' => $bracket,
                'location' => $loc, 'rules' => $rules,
            ));
        };

        // Thể lệ tính điểm/set dùng chung
        $RACKET_15 = '3 hiệp thắng 2 (chạm 15đ, ăn điểm trực tiếp). Loại trực tiếp tranh 1-2-3-4, có tranh hạng 3.';

        return array(
            // Bóng đá Nam — 8 đội loại trực tiếp
            'BĐ.M' => $ko($TEDDY, 8, '25 phút/hiệp. Bốc thăm chia cặp, loại trực tiếp tranh 1-2-3-4.'),

            // Bóng đá Nữ — 2 bảng vòng tròn -> tranh 1-2 / 3-4
            'BĐ.F' => array(array(
                'name' => 'Vòng bảng + Chung kết', 'stage_type' => 'final', 'stage_order' => 1,
                'format' => 'round_robin_knockout',
                'num_groups' => 2, 'teams_per_group' => 3, 'advance_per_group' => 2, 'bracket_size' => 4,
                'location' => $TEDDY,
                'rules' => '20 phút/hiệp. Bốc thăm chia 2 bảng, đấu vòng tròn. Nhất bảng vào tranh 1-2, Nhì bảng vào tranh 3-4.',
            )),

            // Kéo co — 8 đội, 2 bảng vòng tròn, BK chéo, CK tranh 1-2-3-4
            'KC' => array(array(
                'name' => 'Vòng bảng + Chung kết', 'stage_type' => 'final', 'stage_order' => 1,
                'format' => 'round_robin_knockout',
                'num_groups' => 2, 'teams_per_group' => 4, 'advance_per_group' => 2, 'bracket_size' => 4,
                'location' => $NTD,
                'rules' => '3 hiệp thắng 2. Bốc thăm 2 bảng đấu vòng tròn chọn Nhất-Nhì; bán kết đấu chéo; chung kết loại trực tiếp tranh 1-2-3-4.',
            )),

            // Bóng bàn đơn Nam — 2 play-off + 2 bảng vòng tròn -> BK, CK
            'BB.M' => array(
                array(
                    'name' => 'Play-off', 'stage_type' => 'playoff', 'stage_order' => 1,
                    'format' => 'single_elimination', 'bracket_size' => 4,
                    'location' => $NTD,
                    'rules' => '5 hiệp thắng 3 (11đ/séc, cách biệt 2đ). 2 trận play-off chọn đủ 8 đội vào vòng bảng.',
                ),
                array(
                    'name' => 'Vòng bảng + Chung kết', 'stage_type' => 'final', 'stage_order' => 2,
                    'format' => 'round_robin_knockout',
                    'num_groups' => 2, 'teams_per_group' => 4, 'advance_per_group' => 2, 'bracket_size' => 4,
                    'location' => $NTD,
                    'rules' => 'Vòng bảng: 3 hiệp thắng 2 (11đ/séc, cách biệt 2đ). VCK: 5 hiệp thắng 3. 2 bảng vòng tròn, Nhất-Nhì mỗi bảng vào bán kết, chung kết tranh 1-2-3-4.',
                ),
            ),

            // Bóng bàn đơn Nữ — 11 đội, 3 bảng vòng tròn, VCK nhất bảng tranh 1-2-3
            'BB.F' => array(
                array(
                    'name' => 'Vòng loại (3 bảng)', 'stage_type' => 'qualification', 'stage_order' => 1,
                    'format' => 'round_robin',
                    'num_groups' => 3, 'teams_per_group' => 4, 'advance_per_group' => 1,
                    'location' => $NTD,
                    'rules' => 'Vòng loại: 3 hiệp thắng 2 (11đ/séc, cách biệt 2đ). Bốc thăm 3 bảng (4+4+3) đấu vòng tròn, chọn Nhất mỗi bảng.',
                ),
                array(
                    'name' => 'Vòng chung kết (Tranh 1-2-3)', 'stage_type' => 'final', 'stage_order' => 2,
                    'format' => 'round_robin',
                    'num_groups' => 1, 'teams_per_group' => 3, 'advance_per_group' => 3,
                    'location' => $NTD,
                    'rules' => 'VCK: 5 hiệp thắng 3 (11đ/séc, cách biệt 2đ). Nhất 3 bảng vào đấu vòng tròn tranh 1-2-3.',
                ),
            ),

            // Cầu lông đơn/đôi (trừ MF) — loại trực tiếp
            'CL.M' => $ko($NTD, 8, $RACKET_15),
            'CL.F' => $ko($NTD, 8, $RACKET_15),
            'CL.2M' => $ko($NTD, 8, $RACKET_15),
            'CL.2F' => $ko($NTD, 8, $RACKET_15),

            // Cầu lông đôi Nam-Nữ — 9 đội, 3 bảng vòng tròn, CK vòng tròn chọn 1-2-3
            'CL.MF' => array(
                array(
                    'name' => 'Vòng loại (3 bảng)', 'stage_type' => 'qualification', 'stage_order' => 1,
                    'format' => 'round_robin',
                    'num_groups' => 3, 'teams_per_group' => 3, 'advance_per_group' => 1,
                    'location' => $NTD,
                ),
                array(
                    'name' => 'Vòng chung kết (Tranh 1-2-3)', 'stage_type' => 'final', 'stage_order' => 2,
                    'format' => 'round_robin',
                    'num_groups' => 1, 'teams_per_group' => 3, 'advance_per_group' => 3,
                    'location' => $NTD,
                ),
            ),

            // Pickleball — loại trực tiếp
            'PK.2M' => $ko($KS, 8),
            'PK.2F' => $ko($KS, 8),
            'PK.MF' => $ko($KS, 8),

            // Bơi (6 nội dung) — vòng loại theo lượt + chung kết top 6
            'BTD.M40' => $this->swimStages($BB_POOL),
            'BTD.M80' => $this->swimStages($BB_POOL),
            'BTD.F40' => $this->swimStages($BB_POOL),
            'BE.M40' => $this->swimStages($BB_POOL),
            'BE.M80' => $this->swimStages($BB_POOL),
            'BE.F40' => $this->swimStages($BB_POOL),
        );
    }

    protected function swimStages($loc)
    {
        return array(
            array(
                'name' => 'Vòng loại (phân lượt)', 'stage_type' => 'qualification', 'stage_order' => 1,
                'format' => 'heat_lane', 'location' => $loc,
            ),
            array(
                'name' => 'Chung kết (Top 6)', 'stage_type' => 'final', 'stage_order' => 2,
                'format' => 'heat_lane', 'location' => $loc,
            ),
        );
    }

    protected function seedStages($eventId, $commit)
    {
        echo "=== SEED STAGES ===\n";
        $existing = $this->loadExistingStages($eventId);

        foreach ($this->stagesConfig() as $sportCode => $stages) {
            $sportId = isset($this->sportIdByCode[$sportCode]) ? $this->sportIdByCode[$sportCode] : null;
            if ($sportId === null) {
                echo "  [BO QUA] {$sportCode}: chua co sport_id (seed sports truoc).\n";
                continue;
            }

            foreach ($stages as $s) {
                $key = $sportId . '|' . $s['name'];
                if (!$this->isPlaceholder($sportId) && isset($existing[$key])) {
                    echo "  [BO QUA] {$sportCode} / {$s['name']} — da ton tai (id={$existing[$key]})\n";
                    continue;
                }

                if (!$commit) {
                    echo "  [SE TAO] {$sportCode} / {$s['name']} [type={$s['stage_type']}, format={$s['format']}]\n";
                    continue;
                }

                $model = new SportStages;
                $model->setAttributes(array_merge($s, array(
                    'event_id' => $eventId,
                    'sport_id' => $sportId,
                    'status' => 'active',
                )), false);

                $result = $model->storeViaApi();
                if (!empty($result['success'])) {
                    echo "  [TAO OK] {$sportCode} / {$s['name']} id=" . $this->extractId($result) . "\n";
                } else {
                    echo "  [LOI]   {$sportCode} / {$s['name']}: " . $this->errMsg($result) . "\n";
                }
            }
        }
        echo "\n";
    }

    /**
     * Map "sport_id|name" => stage_id của các stage đã có trong event.
     */
    protected function loadExistingStages($eventId)
    {
        $map = array();
        try {
            $data = SportStages::getApiDataProvider(array('event_id' => $eventId), 10000)->getData();
            foreach ($data as $row) {
                $sportId = is_object($row) ? $row->sport_id : (isset($row['sport_id']) ? $row['sport_id'] : null);
                $name = is_object($row) ? $row->name : (isset($row['name']) ? $row['name'] : null);
                $id = is_object($row) ? $row->id : (isset($row['id']) ? $row['id'] : null);
                if ($sportId !== null && $name !== null) {
                    $map[$sportId . '|' . $name] = $id;
                }
            }
        } catch (Exception $e) {
            echo "CANH BAO: Khong tai duoc stages hien co ({$e->getMessage()}).\n";
        }
        echo "Stages hien co trong event: " . count($map) . " ban ghi.\n";
        return $map;
    }

    // ============================================================
    // Helpers
    // ============================================================

    protected function isPlaceholder($id)
    {
        return is_string($id) && strpos($id, 'NEW:') === 0;
    }

    protected function extractId($result)
    {
        $d = isset($result['data']) ? $result['data'] : null;
        if (is_array($d)) {
            if (isset($d['id'])) {
                return $d['id'];
            }
            if (isset($d['data']) && is_array($d['data']) && isset($d['data']['id'])) {
                return $d['data']['id'];
            }
        }
        return '?';
    }

    protected function errMsg($result)
    {
        if (isset($result['error']) && $result['error']) {
            return is_array($result['error']) ? json_encode($result['error'], JSON_UNESCAPED_UNICODE) : $result['error'];
        }
        return 'Unknown error (code=' . (isset($result['code']) ? $result['code'] : '?') . ')';
    }
}
