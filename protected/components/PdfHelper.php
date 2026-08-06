<?php

class PdfHelper
{
    /**
     * Tự động đăng ký autoloader cho Dompdf và các thư viện phụ thuộc trong application.vendors
     */
    public static function registerAutoloader()
    {
        static $registered = false;
        if ($registered) return;

        $vendorDir = Yii::getPathOfAlias('application.vendors');

        spl_autoload_register(function ($class) use ($vendorDir) {
            if ($class === 'Dompdf\Cpdf' || $class === 'Cpdf') {
                $cpdfFile = $vendorDir . '/dompdf/dompdf/lib/Cpdf.php';
                if (file_exists($cpdfFile)) {
                    require_once $cpdfFile;
                    return true;
                }
            }
            $prefixes = array(
                'Dompdf\\' => $vendorDir . '/dompdf/dompdf/src/',
                'FontLib\\' => $vendorDir . '/phenx/php-font-lib/src/FontLib/',
                'Svg\\' => $vendorDir . '/phenx/php-svg-lib/src/Svg/',
                'Sabberworm\\CSS\\' => $vendorDir . '/sabberworm/php-css-parser/src/',
                'Masterminds\\' => $vendorDir . '/masterminds/html5/src/',
            );
            foreach ($prefixes as $prefix => $baseDir) {
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) === 0) {
                    $relativeClass = substr($class, $len);
                    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                        return true;
                    }
                }
            }
            return false;
        }, true, true);

        $registered = true;
    }

    /**
     * Xuất file PDF xác nhận đăng ký và lưu vào thư mục runtime
     * @param int|string $registrationId ID phiếu đăng ký
     * @param array $data Dữ liệu render views
     * @return string Đường dẫn file PDF đã tạo
     */
    public static function generateRegistrationPdf($registrationId, $data)
    {
        self::registerAutoloader();

        if (!class_exists('Dompdf\Dompdf')) {
            throw new Exception('Thư viện Dompdf chưa được tải thành công.');
        }

        $viewPath = Yii::getPathOfAlias('application.views.mail.registration_confirmation_pdf') . '.php';
        if (!file_exists($viewPath)) {
            $viewPath = Yii::getPathOfAlias('application.views.mail.registration_confirmation') . '.php';
        }

        extract($data);
        ob_start();
        include($viewPath);
        $html = ob_get_clean();

        // Thư mục cache font phải ghi được để dompdf tự cài Times New Roman (TTF có glyph tiếng Việt)
        $fontDir = Yii::getPathOfAlias('application.runtime') . DIRECTORY_SEPARATOR . 'dompdf_fonts';
        if (!file_exists($fontDir)) {
            @mkdir($fontDir, 0777, true);
        }

        $dompdf = new Dompdf\Dompdf(array(
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'isFontSubsettingEnabled' => true,
            'fontDir' => $fontDir,
            'fontCache' => $fontDir,
            // Cho phép nạp font TTF nằm trong thư mục protected (mặc định chroot chỉ ở vendor dompdf)
            'chroot' => array(
                Yii::getPathOfAlias('application'),
                Yii::getPathOfAlias('application.vendors.dompdf.dompdf'),
                // Cho phép đọc ảnh chân dung người tham dự lưu trong webroot/uploads
                Yii::getPathOfAlias('webroot'),
            ),
            'defaultFont' => 'Times New Roman',
        ));

        // Đăng ký font Times New Roman (TTF có glyph tiếng Việt) đóng gói trong application.data.fonts
        $fontBase = str_replace('\\', '/', Yii::getPathOfAlias('application.data.fonts'));
        $timesFonts = array(
            array('weight' => 'normal', 'style' => 'normal', 'file' => 'times.ttf'),
            array('weight' => 'bold', 'style' => 'normal', 'file' => 'timesbd.ttf'),
            array('weight' => 'normal', 'style' => 'italic', 'file' => 'timesi.ttf'),
            array('weight' => 'bold', 'style' => 'italic', 'file' => 'timesbi.ttf'),
        );
        $fontMetrics = $dompdf->getFontMetrics();
        foreach ($timesFonts as $f) {
            $ttf = $fontBase . '/' . $f['file'];
            if (file_exists($ttf)) {
                $fontMetrics->registerFont(
                    array('family' => 'Times New Roman', 'weight' => $f['weight'], 'style' => $f['style']),
                    'file://' . $ttf
                );
            }
        }

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        $tempDir = Yii::getPathOfAlias('application.runtime');
        if (!file_exists($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }
        if (!is_writable($tempDir)) {
            throw new Exception('Thư mục runtime không ghi được, không thể tạo file PDF: ' . $tempDir);
        }

        // Phần nội dung theo đợt đăng ký cho tên file
        $isDot1 = !empty($data['isDot1']);
        $isDot2 = !empty($data['isDot2']);
        $periodContentCodes = isset($data['periodContentCodes']) && is_array($data['periodContentCodes']) ? $data['periodContentCodes'] : array();
        $isDot3 = in_array('talent', $periodContentCodes) && !$isDot1;
        if ($isDot1) {
            $contentPart = 'The_Thao_Miss';
        } elseif ($isDot2) {
            $contentPart = 'Nghiep_Vu';
        } elseif ($isDot3) {
            $contentPart = 'Van_Nghe';
        } else {
            $contentPart = 'Chung';
        }

        // Cấu trúc tên file: Phieu_Xac_Nhan_Dang_Ky_{nội dung đợt}_{mã đơn vị (cột prefix)}_{ID phiếu}
        $unitCode = !empty($data['model']->property_code) ? MyHelper::toSlug($data['model']->property_code) : 'DONVI';
        $pdfFileName = 'Phieu_Xac_Nhan_Dang_Ky_' . $contentPart . '_' . strtoupper($unitCode) . '_' . $registrationId . '.pdf';
        $filePath = $tempDir . DIRECTORY_SEPARATOR . $pdfFileName;

        if (file_put_contents($filePath, $pdfOutput) === false) {
            throw new Exception('Ghi file PDF thất bại: ' . $filePath);
        }

        return $filePath;
    }
}
