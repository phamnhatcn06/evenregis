<?php

/**
 * Swim Seed Import Command — Nạp thành tích vòng cụm làm hạt giống (seed) cho
 * vòng loại bơi VCK, đọc từ sheet "Bốc thăm" của docs/The_le.xlsx.
 *
 * Cấu trúc sheet: dòng tiêu đề nội dung có cột B = tên nội dung ("Bơi tự do Nam - cự ly 40m"),
 * cột C = số VĐV; các dòng VĐV bên dưới có cột B = thành tích ("30.65" / "1:19.03"),
 * cột C = họ tên, cột E = đơn vị. Nội dung không có thành tích (không thi vòng cụm) bị bỏ qua.
 *
 * Gửi qua Model -> API; backend khớp theo tên VĐV (dự phòng mã đơn vị) và ghi
 * sport_stage_teams.seed = thành tích (ms). Hạt giống nhỏ = nhanh = xếp trước khi bốc lượt/làn.
 *
 * Usage:
 *   php yiiCmd.php swimseedimport --eventId=3              # DRY-RUN: xem kết quả khớp
 *   php yiiCmd.php swimseedimport --eventId=3 --commit=1   # Ghi seed
 *   php yiiCmd.php swimseedimport --eventId=3 --file=docs/Other.xlsx --sheet="Bốc thăm"
 */
class SwimSeedImportCommand extends CConsoleCommand
{
    public $defaultAction = 'index';

    public function actionIndex($eventId = null, $commit = 0, $file = 'docs/The_le.xlsx', $sheet = 'Bốc thăm')
    {
        $commit = (int) $commit;
        if (!$eventId) {
            echo "ERROR: Thiếu --eventId=<id>.\n";
            return 1;
        }
        $path = $this->resolvePath($file);
        if (!is_file($path)) {
            echo "ERROR: Không tìm thấy file {$path}\n";
            return 1;
        }

        echo $commit ? ">>> CHE DO GHI (COMMIT)\n" : ">>> CHE DO DRY-RUN (them --commit=1 de ghi)\n";
        echo "Event ID: {$eventId} | File: {$path} | Sheet: {$sheet}\n\n";

        $blocks = $this->readSwimBlocks($path, $sheet);
        if ($blocks === null) {
            return 1;
        }
        $stageBySport = $this->loadQualStages($eventId);

        foreach ($blocks as $sportName => $entries) {
            if (empty($entries)) {
                echo "[BO QUA] {$sportName}: khong co thanh tich vong cum\n";
                continue;
            }
            $key = $this->normalize($sportName);
            if (!isset($stageBySport[$key])) {
                echo "[BO QUA] {$sportName}: khong tim thay giai doan vong loai (heat_lane)\n";
                continue;
            }
            $stage = $stageBySport[$key];
            $result = SportStages::importSwimSeeds($stage['id'], $entries, !$commit);
            if (empty($result['success'])) {
                echo "[LOI]   {$sportName}: " . $this->errMsg($result) . "\n";
                continue;
            }
            $data = isset($result['data']['data']) ? $result['data']['data'] : $result['data'];
            $matched = isset($data['matched']) ? $data['matched'] : array();
            $unmatched = isset($data['unmatched']) ? $data['unmatched'] : array();
            echo ($commit ? '[GHI]   ' : '[KHOP]  ') . "{$sportName} (stage {$stage['id']}): "
                . count($matched) . "/" . count($entries) . " khop\n";
            foreach ($unmatched as $u) {
                echo "          - KHONG KHOP: {$u['name']} (" . (isset($u['unit']) ? $u['unit'] : '') . ") — {$u['reason']}\n";
            }
        }

        echo "\nHoan tat.\n";
        return 0;
    }

    /**
     * Đọc sheet, trả về [tên nội dung bơi => [['name','unit','time_ms'], ...]].
     */
    protected function readSwimBlocks($path, $sheetName)
    {
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel.Classes');
        spl_autoload_unregister(array('YiiBase', 'autoload'));
        require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        try {
            $reader = PHPExcel_IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $book = $reader->load($path);
        } catch (Exception $e) {
            spl_autoload_register(array('YiiBase', 'autoload'));
            echo "ERROR: Khong doc duoc file Excel: {$e->getMessage()}\n";
            return null;
        }
        spl_autoload_register(array('YiiBase', 'autoload'));

        $ws = $book->getSheetByName($sheetName);
        if (!$ws) {
            echo "ERROR: Khong co sheet \"{$sheetName}\".\n";
            return null;
        }

        $blocks = array();
        $current = null;
        $maxRow = $ws->getHighestRow();
        for ($r = 1; $r <= $maxRow; $r++) {
            $colB = trim((string) $ws->getCell('B' . $r)->getValue());
            $colC = $ws->getCell('C' . $r)->getValue();
            $colE = trim((string) $ws->getCell('E' . $r)->getValue());

            // Dòng tiêu đề nội dung: cột B là chữ, cột C là số VĐV
            if ($colB !== '' && is_numeric($colC) && !is_numeric(str_replace(':', '', $colB))) {
                $current = (mb_stripos($colB, 'Bơi', 0, 'UTF-8') === 0) ? $colB : null;
                if ($current !== null) {
                    $blocks[$current] = array();
                }
                continue;
            }
            if ($current === null || !is_string($colC) || trim($colC) === '') {
                continue;
            }
            $ms = $this->parseTimeToMs($colB);
            if ($ms === null) {
                continue;
            }
            $blocks[$current][] = array('name' => trim($colC), 'unit' => $colE, 'time_ms' => $ms);
        }
        return $blocks;
    }

    /**
     * Map tên nội dung (chuẩn hoá) => giai đoạn bơi đầu tiên thi đấu của nội dung
     * (hiện bơi chỉ thi Vòng chung kết; nếu sau này có vòng loại thì ưu tiên vòng loại).
     */
    protected function loadQualStages($eventId)
    {
        $map = array();
        $rows = SportStages::getApiDataProvider(array('event_id' => $eventId), 10000)->getData();
        foreach ($rows as $row) {
            if (!SportStages::isSwimFormat($row->format)) {
                continue;
            }
            $key = $this->normalize($row->sport_name);
            $isQual = ($row->stage_type === 'qualification');
            if (!isset($map[$key]) || ($isQual && !$map[$key]['is_qual'])) {
                $map[$key] = array('id' => $row->id, 'name' => $row->name, 'is_qual' => $isQual);
            }
        }
        return $map;
    }

    /** "30.65" -> 30650, "1:19.03" -> 79030; chuỗi không hợp lệ -> null */
    protected function parseTimeToMs($text)
    {
        $s = trim((string) $text);
        if ($s === '' || !preg_match('/^(?:(\d+):)?(\d+(?:[.,]\d+)?)$/', $s, $m)) {
            return null;
        }
        $min = $m[1] !== '' ? (int) $m[1] : 0;
        $sec = (float) str_replace(',', '.', $m[2]);
        return (int) round(($min * 60 + $sec) * 1000);
    }

    protected function normalize($s)
    {
        return trim(preg_replace('/\s+/u', ' ', mb_strtolower((string) $s, 'UTF-8')));
    }

    protected function resolvePath($file)
    {
        if (is_file($file)) {
            return $file;
        }
        return dirname(Yii::app()->basePath) . DIRECTORY_SEPARATOR . $file;
    }

    protected function errMsg($result)
    {
        if (!empty($result['error'])) {
            return is_array($result['error']) ? json_encode($result['error'], JSON_UNESCAPED_UNICODE) : $result['error'];
        }
        return 'Unknown error (code=' . (isset($result['code']) ? $result['code'] : '?') . ')';
    }
}
