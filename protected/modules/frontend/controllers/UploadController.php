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

        $chunkNumber = isset($_POST['resumableChunkNumber']) ? (int)$_POST['resumableChunkNumber'] : 0;
        $totalChunks = isset($_POST['resumableTotalChunks']) ? (int)$_POST['resumableTotalChunks'] : 0;
        $identifier = isset($_POST['resumableIdentifier']) ? preg_replace('/[^0-9a-zA-Z_-]/', '', $_POST['resumableIdentifier']) : '';
        $filename = isset($_POST['resumableFilename']) ? $_POST['resumableFilename'] : '';
        $contestantId = isset($_POST['contestant_id']) ? (int)$_POST['contestant_id'] : 0;
        $folderName = isset($_POST['folder_name']) ? preg_replace('/[^0-9a-zA-Z_-]/', '', $_POST['folder_name']) : '';

        if (empty($identifier) || empty($filename) || $chunkNumber < 1) {
            echo json_encode(array('success' => false, 'error' => 'Invalid parameters'));
            Yii::app()->end();
        }

        $tempDir = Yii::getPathOfAlias('webroot') . '/uploads/temp/' . $identifier;
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $chunkFile = $tempDir . '/' . $chunkNumber . '.part';

        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            move_uploaded_file($_FILES['file']['tmp_name'], $chunkFile);
        } else {
            echo json_encode(array('success' => false, 'error' => 'No file uploaded'));
            Yii::app()->end();
        }

        $uploadedChunks = glob($tempDir . '/*.part');

        if (count($uploadedChunks) >= $totalChunks) {
            $finalDir = Yii::getPathOfAlias('webroot') . '/uploads/miss/' . $folderName;
            if (!is_dir($finalDir)) {
                mkdir($finalDir, 0755, true);
            }

            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $finalFilename = 'video_path-' . time() . '.' . $ext;
            $finalPath = $finalDir . '/' . $finalFilename;

            $fp = fopen($finalPath, 'wb');
            for ($i = 1; $i <= $totalChunks; $i++) {
                $chunkPath = $tempDir . '/' . $i . '.part';
                if (file_exists($chunkPath)) {
                    fwrite($fp, file_get_contents($chunkPath));
                    unlink($chunkPath);
                }
            }
            fclose($fp);

            @rmdir($tempDir);

            $relativePath = 'uploads/miss/' . $folderName . '/' . $finalFilename;

            echo json_encode(array(
                'success' => true,
                'completed' => true,
                'path' => $relativePath,
                'message' => 'Upload hoàn tất'
            ));
        } else {
            echo json_encode(array(
                'success' => true,
                'completed' => false,
                'uploaded' => count($uploadedChunks),
                'total' => $totalChunks
            ));
        }

        Yii::app()->end();
    }
}
