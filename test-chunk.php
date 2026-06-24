<?php
$action = isset($_GET['action']) ? $_GET['action'] : '';
$uploadDir = __DIR__ . '/uploads/test/';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($action === 'chunk') {
    $chunkIndex = isset($_POST['chunkIndex']) ? (int)$_POST['chunkIndex'] : 0;
    $totalChunks = isset($_POST['totalChunks']) ? (int)$_POST['totalChunks'] : 0;
    $filename = isset($_POST['filename']) ? preg_replace('/[^a-zA-Z0-9._-]/', '', $_POST['filename']) : 'video.mp4';
    $fileId = isset($_POST['fileId']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_POST['fileId']) : '';

    if (empty($fileId)) {
        echo json_encode(['success' => false, 'error' => 'Missing fileId']);
        exit;
    }

    $tempDir = $uploadDir . 'chunks_' . $fileId . '/';
    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0755, true);
    }

    if (isset($_FILES['chunk']) && $_FILES['chunk']['error'] === UPLOAD_ERR_OK) {
        $chunkPath = $tempDir . $chunkIndex . '.part';
        move_uploaded_file($_FILES['chunk']['tmp_name'], $chunkPath);

        echo json_encode([
            'success' => true,
            'chunkIndex' => $chunkIndex,
            'received' => true
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Upload failed']);
    }
    exit;
}

if ($action === 'merge') {
    $totalChunks = isset($_POST['totalChunks']) ? (int)$_POST['totalChunks'] : 0;
    $filename = isset($_POST['filename']) ? preg_replace('/[^a-zA-Z0-9._-]/', '', $_POST['filename']) : 'video.mp4';
    $fileId = isset($_POST['fileId']) ? preg_replace('/[^a-zA-Z0-9]/', '', $_POST['fileId']) : '';

    $tempDir = $uploadDir . 'chunks_' . $fileId . '/';
    $finalPath = $uploadDir . time() . '_' . $filename;

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

    $size = round(filesize($finalPath) / 1024 / 1024, 2);
    echo json_encode([
        'success' => true,
        'message' => "Upload thành công! Size: {$size} MB",
        'path' => $finalPath
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Chunked Upload</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-top: 10px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-top: 10px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        button:disabled { background: #ccc; }
        #progress-bar { width: 100%; height: 30px; background: #e9ecef; border-radius: 5px; margin-top: 15px; }
        #progress-fill { height: 100%; background: #28a745; border-radius: 5px; width: 0%; text-align: center; color: white; line-height: 30px; font-weight: bold; }
        #status { margin-top: 10px; font-size: 14px; }
    </style>
</head>
<body>
    <h1>Test Chunked Upload (5MB/chunk)</h1>

    <div class="info">
        <p>File sẽ được chia thành chunks 5MB và upload từng phần.</p>
        <p>Nếu lỗi giữa chừng sẽ tự động retry.</p>
    </div>

    <input type="file" id="fileInput" accept="video/*">
    <br><br>
    <button id="uploadBtn" onclick="startUpload()">Upload</button>

    <div id="progress-bar" style="display:none;">
        <div id="progress-fill">0%</div>
    </div>
    <div id="status"></div>
    <div id="result"></div>

    <script>
    var CHUNK_SIZE = 5 * 1024 * 1024; // 5MB

    async function startUpload() {
        var fileInput = document.getElementById('fileInput');
        var file = fileInput.files[0];
        if (!file) {
            alert('Chọn file trước');
            return;
        }

        var btn = document.getElementById('uploadBtn');
        var progressBar = document.getElementById('progress-bar');
        var progressFill = document.getElementById('progress-fill');
        var status = document.getElementById('status');
        var result = document.getElementById('result');

        btn.disabled = true;
        progressBar.style.display = 'block';
        result.innerHTML = '';

        var totalChunks = Math.ceil(file.size / CHUNK_SIZE);
        var fileId = Date.now().toString(36) + Math.random().toString(36).substr(2);

        status.innerHTML = 'Tổng: ' + totalChunks + ' chunks (' + (file.size / 1024 / 1024).toFixed(1) + ' MB)';

        for (var i = 0; i < totalChunks; i++) {
            var start = i * CHUNK_SIZE;
            var end = Math.min(start + CHUNK_SIZE, file.size);
            var chunk = file.slice(start, end);

            var success = false;
            var retries = 0;

            while (!success && retries < 3) {
                try {
                    var formData = new FormData();
                    formData.append('chunk', chunk);
                    formData.append('chunkIndex', i);
                    formData.append('totalChunks', totalChunks);
                    formData.append('filename', file.name);
                    formData.append('fileId', fileId);

                    var response = await fetch('test-chunk.php?action=chunk', {
                        method: 'POST',
                        body: formData
                    });

                    if (response.ok) {
                        var data = await response.json();
                        if (data.success) {
                            success = true;
                        }
                    }
                } catch (e) {
                    console.error('Chunk ' + i + ' error:', e);
                }

                if (!success) {
                    retries++;
                    status.innerHTML = 'Chunk ' + (i + 1) + ' lỗi, retry ' + retries + '/3...';
                    await sleep(1000);
                }
            }

            if (!success) {
                result.innerHTML = '<div class="error">Upload thất bại ở chunk ' + (i + 1) + '</div>';
                btn.disabled = false;
                return;
            }

            var percent = Math.round(((i + 1) / totalChunks) * 100);
            progressFill.style.width = percent + '%';
            progressFill.textContent = percent + '%';
            status.innerHTML = 'Chunk ' + (i + 1) + '/' + totalChunks + ' done';
        }

        // Merge chunks
        status.innerHTML = 'Đang ghép file...';

        var mergeData = new FormData();
        mergeData.append('totalChunks', totalChunks);
        mergeData.append('filename', file.name);
        mergeData.append('fileId', fileId);

        var mergeResponse = await fetch('test-chunk.php?action=merge', {
            method: 'POST',
            body: mergeData
        });

        var mergeResult = await mergeResponse.json();

        if (mergeResult.success) {
            result.innerHTML = '<div class="success">' + mergeResult.message + '</div>';
            status.innerHTML = 'Hoàn tất!';
        } else {
            result.innerHTML = '<div class="error">Lỗi ghép file: ' + mergeResult.error + '</div>';
        }

        btn.disabled = false;
    }

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }
    </script>
</body>
</html>
