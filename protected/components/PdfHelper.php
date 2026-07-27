<?php

class PdfHelper
{
    /**
     * Tự động đăng ký autoloader cho Dompdf và các thư viện phụ thuộc
     */
    public static function registerAutoloader()
    {
        static $registered = false;
        if ($registered) return;

        $vendorDir = 'E:/even_API/MTRegistrationPortal/vendor';

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
        });

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

        $viewPath = Yii::getPathOfAlias('application.views.mail.registration_confirmation_pdf') . '.php';
        if (!file_exists($viewPath)) {
            $viewPath = Yii::getPathOfAlias('application.views.mail.registration_confirmation') . '.php';
        }

        extract($data);
        ob_start();
        include($viewPath);
        $html = ob_get_clean();

        $dompdf = new Dompdf\Dompdf(array(
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'DejaVu Sans',
        ));

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfOutput = $dompdf->output();

        $tempDir = Yii::getPathOfAlias('application.runtime');
        if (!file_exists($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $propCode = !empty($data['model']->property_code) ? MyHelper::toSlug($data['model']->property_code) : 'DONVI';
        $pdfFileName = 'Phieu_Dang_Ky_' . strtoupper($propCode) . '_' . $registrationId . '.pdf';
        $filePath = $tempDir . DIRECTORY_SEPARATOR . $pdfFileName;

        file_put_contents($filePath, $pdfOutput);

        return $filePath;
    }
}
