<?php

/**
 * Video Helper
 *
 * Hỗ trợ lấy đường dẫn video phù hợp:
 * - Streaming: dùng file _web (nếu có)
 * - Download: dùng file gốc
 */
class VideoHelper
{
    /**
     * Lấy URL video để streaming (ưu tiên file _web)
     *
     * @param string $videoPath Đường dẫn video gốc
     * @return string URL video để play
     */
    public static function getStreamUrl($videoPath)
    {
        if (empty($videoPath)) {
            return '';
        }

        $webPath = self::getWebPath($videoPath);

        // Kiểm tra file _web tồn tại
        if ($webPath && self::fileExists($webPath)) {
            return $webPath;
        }

        return $videoPath;
    }

    /**
     * Lấy URL video gốc để download
     *
     * @param string $videoPath Đường dẫn video
     * @return string URL video gốc
     */
    public static function getDownloadUrl($videoPath)
    {
        if (empty($videoPath)) {
            return '';
        }

        // Nếu đang là file _web, trả về file gốc
        if (strpos($videoPath, '_web.') !== false) {
            return self::getOriginalPath($videoPath);
        }

        return $videoPath;
    }

    /**
     * Kiểm tra có file web không
     *
     * @param string $videoPath Đường dẫn video gốc
     * @return bool
     */
    public static function hasWebVersion($videoPath)
    {
        if (empty($videoPath)) {
            return false;
        }

        $webPath = self::getWebPath($videoPath);
        return $webPath && self::fileExists($webPath);
    }

    /**
     * Chuyển đổi path gốc → path web
     * video.mp4 → video_web.mp4
     */
    public static function getWebPath($originalPath)
    {
        if (empty($originalPath) || strpos($originalPath, '_web.') !== false) {
            return $originalPath;
        }

        $pathInfo = pathinfo($originalPath);
        $dir = isset($pathInfo['dirname']) ? $pathInfo['dirname'] : '';
        $filename = isset($pathInfo['filename']) ? $pathInfo['filename'] : '';
        $ext = isset($pathInfo['extension']) ? $pathInfo['extension'] : 'mp4';

        if ($dir && $dir !== '.') {
            return $dir . '/' . $filename . '_web.' . $ext;
        }

        return $filename . '_web.' . $ext;
    }

    /**
     * Chuyển đổi path web → path gốc
     * video_web.mp4 → video.mp4
     */
    public static function getOriginalPath($webPath)
    {
        if (empty($webPath)) {
            return '';
        }

        return str_replace('_web.', '.', $webPath);
    }

    /**
     * Kiểm tra file tồn tại (hỗ trợ cả URL và local path)
     */
    protected static function fileExists($path)
    {
        // URL
        if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
            $headers = @get_headers($path);
            return $headers && strpos($headers[0], '200') !== false;
        }

        // Local path
        $basePath = Yii::getPathOfAlias('webroot');

        // Nếu là path tương đối (bắt đầu bằng /)
        if (strpos($path, '/') === 0) {
            $fullPath = $basePath . $path;
        } else {
            $fullPath = $basePath . '/' . $path;
        }

        return file_exists($fullPath);
    }

    /**
     * Lấy thông tin video (size gốc vs web)
     */
    public static function getVideoInfo($videoPath)
    {
        $info = array(
            'original_path' => $videoPath,
            'original_size' => 0,
            'web_path' => null,
            'web_size' => 0,
            'has_web' => false,
        );

        if (empty($videoPath)) {
            return $info;
        }

        $basePath = Yii::getPathOfAlias('webroot');

        // Original file
        $originalFullPath = $basePath . '/' . ltrim($videoPath, '/');
        if (file_exists($originalFullPath)) {
            $info['original_size'] = filesize($originalFullPath);
        }

        // Web file
        $webPath = self::getWebPath($videoPath);
        $webFullPath = $basePath . '/' . ltrim($webPath, '/');
        if (file_exists($webFullPath)) {
            $info['web_path'] = $webPath;
            $info['web_size'] = filesize($webFullPath);
            $info['has_web'] = true;
        }

        return $info;
    }

    /**
     * Format file size
     */
    public static function formatSize($bytes)
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
}
