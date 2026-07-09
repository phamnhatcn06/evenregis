<?php

/**
 * Transcode Video Command
 *
 * Chuyển đổi video gốc sang định dạng web tối ưu (giữ nguyên resolution, giảm bitrate)
 * File gốc: video.mp4
 * File web: video_web.mp4
 *
 * Usage:
 *   php protected/yiic transcodevideo              # Transcode tất cả video chưa có _web
 *   php protected/yiic transcodevideo --path=/path/to/video.mp4  # Transcode 1 file
 *   php protected/yiic transcodevideo --crf=23     # Chất lượng (18-28, thấp = đẹp hơn)
 */
class TranscodeVideoCommand extends CConsoleCommand
{
    public $defaultAction = 'index';

    // FFmpeg quality settings
    const DEFAULT_CRF = 28;        // Chất lượng (18=gần lossless, 23=tốt, 28=nhỏ hơn)
    const DEFAULT_PRESET = 'medium'; // Tốc độ encode (ultrafast, fast, medium, slow)
    const DEFAULT_AUDIO_BITRATE = '128k';

    // Thư mục chứa video uploads
    protected $uploadDirs = array(
        'uploads/videos',
        'uploads/talent',
        'uploads/miss',
    );

    public function actionIndex($path = null, $crf = self::DEFAULT_CRF)
    {
        if (!$this->checkFFmpeg()) {
            echo "ERROR: FFmpeg chưa được cài đặt trên server.\n";
            echo "Cài đặt: sudo apt install ffmpeg (Ubuntu) hoặc yum install ffmpeg (CentOS)\n";
            return 1;
        }

        if ($path) {
            return $this->transcodeFile($path, $crf);
        }

        return $this->transcodeAll($crf);
    }

    /**
     * Transcode tất cả video chưa có file _web
     */
    protected function transcodeAll($crf)
    {
        $basePath = Yii::getPathOfAlias('webroot');
        $totalProcessed = 0;
        $totalSkipped = 0;
        $totalFailed = 0;

        foreach ($this->uploadDirs as $dir) {
            $fullPath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
            if (!is_dir($fullPath)) {
                continue;
            }

            echo "Đang quét thư mục: {$dir}\n";
            $files = $this->scanVideoFiles($fullPath);

            foreach ($files as $file) {
                $webFile = $this->getWebFilePath($file);

                if (file_exists($webFile)) {
                    echo "  [SKIP] " . basename($file) . " - đã có file web\n";
                    $totalSkipped++;
                    continue;
                }

                echo "  [PROCESSING] " . basename($file) . "\n";
                $result = $this->transcode($file, $webFile, $crf);

                if ($result) {
                    $originalBytes = filesize($file);
                    $webBytes = filesize($webFile);

                    // Nếu file web lớn hơn hoặc bằng file gốc → xóa, không cần transcode
                    if ($webBytes >= $originalBytes) {
                        unlink($webFile);
                        echo "    [SKIP] File gốc đã tối ưu sẵn, không cần transcode\n";
                        $totalSkipped++;
                        continue;
                    }

                    $originalSize = $this->formatFileSize($originalBytes);
                    $webSize = $this->formatFileSize($webBytes);
                    $reduction = round((1 - $webBytes / $originalBytes) * 100);
                    echo "    OK: {$originalSize} → {$webSize} (-{$reduction}%)\n";
                    $totalProcessed++;
                } else {
                    echo "    FAILED\n";
                    $totalFailed++;
                }
            }
        }

        echo "\n=== KẾT QUẢ ===\n";
        echo "Đã xử lý: {$totalProcessed}\n";
        echo "Đã bỏ qua: {$totalSkipped}\n";
        echo "Thất bại: {$totalFailed}\n";

        return $totalFailed > 0 ? 1 : 0;
    }

    /**
     * Transcode 1 file cụ thể
     */
    protected function transcodeFile($path, $crf)
    {
        $basePath = Yii::getPathOfAlias('webroot');
        $fullPath = strpos($path, $basePath) === 0 ? $path : $basePath . DIRECTORY_SEPARATOR . ltrim($path, '/\\');

        if (!file_exists($fullPath)) {
            echo "ERROR: File không tồn tại: {$path}\n";
            return 1;
        }

        $webFile = $this->getWebFilePath($fullPath);

        if (file_exists($webFile)) {
            echo "File web đã tồn tại: " . basename($webFile) . "\n";
            echo "Xóa file cũ và transcode lại? (y/n): ";
            $confirm = trim(fgets(STDIN));
            if (strtolower($confirm) !== 'y') {
                return 0;
            }
            unlink($webFile);
        }

        echo "Đang transcode: " . basename($fullPath) . "\n";
        $result = $this->transcode($fullPath, $webFile, $crf);

        if ($result) {
            $originalSize = $this->formatFileSize(filesize($fullPath));
            $webSize = $this->formatFileSize(filesize($webFile));
            echo "Thành công: {$originalSize} → {$webSize}\n";
            return 0;
        }

        echo "Thất bại!\n";
        return 1;
    }

    /**
     * Thực hiện transcode bằng FFmpeg
     */
    protected function transcode($inputFile, $outputFile, $crf)
    {
        $preset = self::DEFAULT_PRESET;
        $audioBitrate = self::DEFAULT_AUDIO_BITRATE;

        // FFmpeg command giữ nguyên resolution, giảm bitrate
        $cmd = sprintf(
            'ffmpeg -i %s -c:v libx264 -crf %d -preset %s -c:a aac -b:a %s -movflags +faststart -y %s 2>&1',
            escapeshellarg($inputFile),
            (int)$crf,
            escapeshellarg($preset),
            escapeshellarg($audioBitrate),
            escapeshellarg($outputFile)
        );

        $output = array();
        $returnCode = 0;
        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0) {
            Yii::log("Transcode failed: " . implode("\n", $output), CLogger::LEVEL_ERROR);
            return false;
        }

        return file_exists($outputFile) && filesize($outputFile) > 0;
    }

    /**
     * Lấy đường dẫn file web từ file gốc
     * video.mp4 → video_web.mp4
     */
    protected function getWebFilePath($originalPath)
    {
        $pathInfo = pathinfo($originalPath);
        return $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_web.' . $pathInfo['extension'];
    }

    /**
     * Quét tất cả file video trong thư mục
     */
    protected function scanVideoFiles($dir)
    {
        $files = array();
        $extensions = array('mp4', 'mov', 'webm', 'avi', 'mkv');

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $ext = strtolower($file->getExtension());
                $filename = $file->getFilename();

                // Bỏ qua file đã là _web
                if (strpos($filename, '_web.') !== false) {
                    continue;
                }

                if (in_array($ext, $extensions)) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * Kiểm tra FFmpeg đã cài chưa
     */
    protected function checkFFmpeg()
    {
        exec('ffmpeg -version 2>&1', $output, $returnCode);
        return $returnCode === 0;
    }

    /**
     * Format file size
     */
    protected function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }

    public function getHelp()
    {
        return <<<EOD
USAGE
  yiic transcodevideo [--path=<file>] [--crf=<quality>]

DESCRIPTION
  Chuyển đổi video gốc sang định dạng web tối ưu (H.264, AAC).
  Giữ nguyên độ phân giải, chỉ giảm bitrate để streaming mượt hơn.

  File gốc: video.mp4 (1.5GB, chất lượng cao)
  File web: video_web.mp4 (300MB, tối ưu streaming)

OPTIONS
  --path    Đường dẫn đến 1 file cụ thể. Nếu không có, sẽ quét tất cả thư mục uploads.
  --crf     Chất lượng video (18-28). Mặc định: 23
            18 = Gần lossless (file lớn)
            23 = Chất lượng tốt (khuyến nghị)
            28 = Chất lượng vừa (file nhỏ)

EXAMPLES
  yiic transcodevideo                           # Transcode tất cả
  yiic transcodevideo --path=uploads/video.mp4  # Transcode 1 file
  yiic transcodevideo --crf=20                  # Chất lượng cao hơn

EOD;
    }
}
