<?php

class MissFileController extends AdminController
{
    /**
     * Serve file with access control
     * URL: /admin/missFile/view?path=nguyen-thi-a/photo_portrait-1234567890.jpg
     */
    public function actionView($path = '')
    {
        // Check permission
        if (!PermissionHelper::can('beautyContestants', 'read')) {
            throw new CHttpException(403, 'Bạn không có quyền xem file này.');
        }

        // Sanitize path - prevent directory traversal
        $path = str_replace(array('..', "\0"), '', $path);
        $path = ltrim($path, '/\\');

        if (empty($path)) {
            throw new CHttpException(404, 'File không tồn tại.');
        }

        $filePath = Yii::getPathOfAlias('webroot') . '/uploads/miss/' . $path;

        if (!file_exists($filePath) || !is_file($filePath)) {
            throw new CHttpException(404, 'File không tồn tại.');
        }

        // Get mime type
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($filePath);
        } else {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeTypes = array(
                'jpg' => 'image/jpeg',
                'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'webp' => 'image/webp',
                'mp4' => 'video/mp4',
                'mov' => 'video/quicktime',
                'avi' => 'video/x-msvideo',
                'webm' => 'video/webm'
            );
            $mimeType = isset($mimeTypes[$ext]) ? $mimeTypes[$ext] : 'application/octet-stream';
        }

        // Try to resize image if requested and it is an image
        $w = Yii::app()->request->getQuery('w');
        if ($w && is_numeric($w) && strpos($mimeType, 'image/') === 0) {
            $pathInfo = pathinfo($filePath);
            $cacheFile = $pathInfo['dirname'] . DIRECTORY_SEPARATOR . $pathInfo['filename'] . '_' . $w . 'w.' . $pathInfo['extension'];
            if (!file_exists($cacheFile)) {
                try {
                    $thumb = Yii::app()->phpThumb->create($filePath);
                    $thumb->resize($w, 0);
                    $thumb->save($cacheFile);
                } catch (Exception $e) {
                    // Fallback to original file on failure
                }
            }
            if (file_exists($cacheFile)) {
                $filePath = $cacheFile;
            }
        }

        // Only allow image and video files
        $allowedTypes = array(
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo',
            'video/webm'
        );

        if (!in_array($mimeType, $allowedTypes)) {
            throw new CHttpException(403, 'Loại file không được phép.');
        }

        // Send file
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: private, max-age=3600');

        readfile($filePath);
        Yii::app()->end();
    }
}
