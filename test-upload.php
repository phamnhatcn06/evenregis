<?php
ini_set('memory_limit', '1G');
ini_set('upload_max_filesize', '600M');
ini_set('post_max_size', '650M');
ini_set('max_execution_time', 900);
ini_set('max_input_time', 900);

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/test/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = time() . '_' . basename($_FILES['video']['name']);
        $targetPath = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['video']['tmp_name'], $targetPath)) {
            $size = round(filesize($targetPath) / 1024 / 1024, 2);
            $message = "Upload thành công! File: $filename ($size MB)";
        } else {
            $error = "Lỗi di chuyển file";
        }
    } else {
        $errorCodes = array(
            UPLOAD_ERR_INI_SIZE => 'File vượt quá upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File vượt quá MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File chỉ upload một phần',
            UPLOAD_ERR_NO_FILE => 'Không có file nào được upload',
            UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục temp',
            UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file',
            UPLOAD_ERR_EXTENSION => 'Extension chặn upload',
        );
        $code = isset($_FILES['video']) ? $_FILES['video']['error'] : -1;
        $error = isset($errorCodes[$code]) ? $errorCodes[$code] : "Lỗi không xác định (code: $code)";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Upload 500MB</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; }
        .info { background: #e7f3ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .info p { margin: 5px 0; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; }
        input[type="file"] { margin: 10px 0; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; }
        #progress { margin-top: 10px; display: none; }
        #progress-bar { width: 100%; height: 25px; background: #e9ecef; border-radius: 5px; }
        #progress-fill { height: 100%; background: #28a745; border-radius: 5px; width: 0%; transition: width 0.3s; text-align: center; color: white; line-height: 25px; }
    </style>
</head>
<body>
    <h1>Test Upload File Lớn</h1>

    <div class="info">
        <p><strong>PHP Settings hiện tại:</strong></p>
        <p>memory_limit: <?php echo ini_get('memory_limit'); ?></p>
        <p>upload_max_filesize: <?php echo ini_get('upload_max_filesize'); ?></p>
        <p>post_max_size: <?php echo ini_get('post_max_size'); ?></p>
        <p>max_execution_time: <?php echo ini_get('max_execution_time'); ?>s</p>
        <p>max_input_time: <?php echo ini_get('max_input_time'); ?>s</p>
    </div>

    <?php if ($message): ?>
        <div class="success"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" id="uploadForm">
        <p><strong>Chọn video (max 500MB):</strong></p>
        <input type="file" name="video" accept="video/*" id="fileInput" required>
        <br><br>
        <button type="submit" id="submitBtn">Upload</button>

        <div id="progress">
            <div id="progress-bar">
                <div id="progress-fill">0%</div>
            </div>
            <p id="status"></p>
        </div>
    </form>

    <script>
    document.getElementById('uploadForm').addEventListener('submit', function(e) {
        e.preventDefault();

        var fileInput = document.getElementById('fileInput');
        var file = fileInput.files[0];
        if (!file) return;

        var formData = new FormData();
        formData.append('video', file);

        var xhr = new XMLHttpRequest();
        var progress = document.getElementById('progress');
        var progressFill = document.getElementById('progress-fill');
        var status = document.getElementById('status');
        var submitBtn = document.getElementById('submitBtn');

        progress.style.display = 'block';
        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang upload...';

        xhr.upload.addEventListener('progress', function(e) {
            if (e.lengthComputable) {
                var percent = Math.round((e.loaded / e.total) * 100);
                progressFill.style.width = percent + '%';
                progressFill.textContent = percent + '%';
                status.textContent = 'Đang tải: ' + (e.loaded / 1024 / 1024).toFixed(1) + ' / ' + (e.total / 1024 / 1024).toFixed(1) + ' MB';
            }
        });

        xhr.addEventListener('load', function() {
            if (xhr.status === 200) {
                status.textContent = 'Upload thành công! Đang reload...';
                location.reload();
            } else {
                status.textContent = 'Lỗi: ' + xhr.status + ' ' + xhr.statusText;
                submitBtn.disabled = false;
                submitBtn.textContent = 'Upload';
            }
        });

        xhr.addEventListener('error', function() {
            status.textContent = 'Lỗi kết nối server';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Upload';
        });

        xhr.addEventListener('timeout', function() {
            status.textContent = 'Timeout - Server không phản hồi';
            submitBtn.disabled = false;
            submitBtn.textContent = 'Upload';
        });

        xhr.timeout = 900000; // 15 phút
        xhr.open('POST', 'test-upload.php');
        xhr.send(formData);
    });
    </script>
</body>
</html>
