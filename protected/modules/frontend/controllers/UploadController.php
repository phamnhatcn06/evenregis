<?php

class UploadController extends CController
{
    public function filters()
    {
        return array();
    }

    public function actionChunk()
    {
        header('Content-Type: application/json');

        $action = isset($_GET['act']) ? $_GET['act'] : 'chunk';
        $uploadDir = Yii::getPathOfAlias('webroot') . '/uploads/miss/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if ($action === 'chunk') {
            $chunkIndex = isset($_POST['chunkIndex']) ? (int)$_POST['chunkIndex'] : 0;
            $totalChunks = isset($_POST['totalChunks']) ? (int)$_POST['totalChunks'] : 0;
            $filename = isset($_POST['filename']) ? preg_replace('/[^a-zA-Z0-9._-]/', '', $_POST['filename']) : 'video.mp4';
            $fileId = isset($_POST['fileId']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_POST['fileId']) : '';
            $folderName = isset($_POST['folderName']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['folderName']) : '';

            if (empty($fileId)) {
                echo CJSON::encode(array('success' => false, 'error' => 'Missing fileId'));
                Yii::app()->end();
            }

            $tempDir = Yii::getPathOfAlias('webroot') . '/uploads/temp/chunks_' . $fileId . '/';
            if (!is_dir($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            if (isset($_FILES['chunk']) && $_FILES['chunk']['error'] === UPLOAD_ERR_OK) {
                $chunkPath = $tempDir . $chunkIndex . '.part';
                move_uploaded_file($_FILES['chunk']['tmp_name'], $chunkPath);

                echo CJSON::encode(array(
                    'success' => true,
                    'chunkIndex' => $chunkIndex,
                    'received' => true
                ));
            } else {
                echo CJSON::encode(array('success' => false, 'error' => 'Upload failed'));
            }
            Yii::app()->end();
        }

        if ($action === 'merge') {
            $totalChunks = isset($_POST['totalChunks']) ? (int)$_POST['totalChunks'] : 0;
            $filename = isset($_POST['filename']) ? preg_replace('/[^a-zA-Z0-9._-]/', '', $_POST['filename']) : 'video.mp4';
            $fileId = isset($_POST['fileId']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_POST['fileId']) : '';
            $folderName = isset($_POST['folderName']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_POST['folderName']) : '';

            if (empty($folderName)) {
                $folderName = 'contestant-' . time();
            }

            $tempDir = Yii::getPathOfAlias('webroot') . '/uploads/temp/chunks_' . $fileId . '/';
            $finalDir = $uploadDir . $folderName . '/';

            if (!is_dir($finalDir)) {
                mkdir($finalDir, 0755, true);
            }

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $finalFilename = 'video_path-' . time() . '.' . $ext;
            $finalPath = $finalDir . $finalFilename;

            $fp = fopen($finalPath, 'wb');
            for ($i = 0; $i < $totalChunks; $i++) {
                $chunkPath = $tempDir . $i . '.part';
                if (file_exists($chunkPath)) {
                    fwrite($fp, file_get_contents($chunkPath));
                    unlink($chunkPath);
                }
            }
            fclose($fp);
            @rmdir($tempDir);

            $relativePath = '/uploads/miss/' . $folderName . '/' . $finalFilename;
            $size = round(filesize($finalPath) / 1024 / 1024, 2);

            echo CJSON::encode(array(
                'success' => true,
                'message' => "Upload thành công! Size: {$size} MB",
                'path' => $relativePath
            ));
            Yii::app()->end();
        }

        echo CJSON::encode(array('success' => false, 'error' => 'Invalid action'));
        Yii::app()->end();
    }
}
