document.addEventListener('DOMContentLoaded', function() {
    var CHUNK_SIZE = 5 * 1024 * 1024; // 5MB

    // Image preview handling
    var imageInputs = document.querySelectorAll('input[type="file"][accept="image/*"]');

    imageInputs.forEach(function(input) {
        input.addEventListener('change', function(e) {
            var file = e.target.files[0];
            var previewId = input.getAttribute('data-preview');
            var preview = document.getElementById(previewId);
            var wrapper = input.closest('.photo-upload-wrapper');

            if (file && file.type.startsWith('image/')) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    wrapper.classList.add('has-preview');
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Video elements
    var videoWrapper = document.querySelector('.video-upload-wrapper');
    var videoInput = document.getElementById('video-input');
    var videoPreview = document.getElementById('video-preview');
    var form = document.getElementById('miss-submit-form');
    var btn = document.getElementById('btn_submit');

    // Get config
    var uploadUrl = videoWrapper ? videoWrapper.getAttribute('data-upload-url') : null;
    var folderName = videoWrapper ? videoWrapper.getAttribute('data-folder-name') : null;

    var videoUploaded = false;
    var uploadedVideoPath = '';
    var isUploading = false;

    // Create progress UI
    if (videoWrapper) {
        var progressHtml =
            '<div class="video-progress" style="display:none; margin-top:15px;">' +
                '<div style="background:#e9ecef; border-radius:5px; height:30px; overflow:hidden;">' +
                    '<div class="progress-fill" style="background:#28a745; height:100%; width:0%; transition:width 0.3s; text-align:center; color:white; line-height:30px; font-weight:bold;">0%</div>' +
                '</div>' +
                '<div class="upload-status" style="text-align:center; margin-top:8px; font-size:14px;"></div>' +
            '</div>';
        videoWrapper.insertAdjacentHTML('beforeend', progressHtml);
    }

    var progressContainer = videoWrapper ? videoWrapper.querySelector('.video-progress') : null;
    var progressFill = progressContainer ? progressContainer.querySelector('.progress-fill') : null;
    var uploadStatus = progressContainer ? progressContainer.querySelector('.upload-status') : null;

    // Video selection
    if (videoInput) {
        videoInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (!file) return;

            // Validate size
            if (file.size > 500 * 1024 * 1024) {
                alert('Video tối đa 500MB');
                videoInput.value = '';
                return;
            }

            // Show preview
            var url = URL.createObjectURL(file);
            videoPreview.src = url;
            videoPreview.style.display = 'block';

            // Start chunked upload
            startChunkedUpload(file);
        });
    }

    async function startChunkedUpload(file) {
        if (!uploadUrl || isUploading) return;

        isUploading = true;
        videoUploaded = false;
        uploadedVideoPath = '';

        // Show progress
        progressContainer.style.display = 'block';
        progressFill.style.width = '0%';
        progressFill.textContent = '0%';

        // Disable submit
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Đang tải video...';
        }

        var totalChunks = Math.ceil(file.size / CHUNK_SIZE);
        var fileId = Date.now().toString(36) + Math.random().toString(36).substr(2);

        uploadStatus.innerHTML = '<i class="fa fa-upload"></i> 0/' + totalChunks + ' chunks (0%)';

        // Upload chunks
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
                    formData.append('folderName', folderName);

                    var response = await fetch(uploadUrl + '?act=chunk', {
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
                    uploadStatus.innerHTML = '<i class="fa fa-refresh fa-spin"></i> Chunk ' + (i + 1) + ' lỗi, retry ' + retries + '/3...';
                    await sleep(1000);
                }
            }

            if (!success) {
                uploadStatus.innerHTML = '<i class="fa fa-times-circle"></i> Upload thất bại. <a href="#" onclick="location.reload()">Thử lại</a>';
                progressFill.style.background = '#dc3545';
                isUploading = false;
                enableSubmit();
                return;
            }

            var percent = Math.round(((i + 1) / totalChunks) * 100);
            progressFill.style.width = percent + '%';
            progressFill.textContent = percent + '%';

            var uploadedMB = ((i + 1) * CHUNK_SIZE / 1024 / 1024).toFixed(1);
            var totalMB = (file.size / 1024 / 1024).toFixed(1);
            if (uploadedMB > totalMB) uploadedMB = totalMB;
            uploadStatus.innerHTML = '<i class="fa fa-upload"></i> ' + uploadedMB + 'MB / ' + totalMB + 'MB (' + percent + '%)';
        }

        // Merge chunks
        uploadStatus.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang ghép file...';

        try {
            var mergeData = new FormData();
            mergeData.append('totalChunks', totalChunks);
            mergeData.append('filename', file.name);
            mergeData.append('fileId', fileId);
            mergeData.append('folderName', folderName);

            var mergeResponse = await fetch(uploadUrl + '?act=merge', {
                method: 'POST',
                body: mergeData
            });

            var mergeResult = await mergeResponse.json();

            if (mergeResult.success) {
                videoUploaded = true;
                uploadedVideoPath = mergeResult.path;
                progressFill.style.width = '100%';
                progressFill.textContent = '100%';
                uploadStatus.innerHTML = '<i class="fa fa-check-circle"></i> Upload hoàn tất!';
                uploadStatus.style.color = '#28a745';
            } else {
                uploadStatus.innerHTML = '<i class="fa fa-times-circle"></i> Lỗi ghép file';
                progressFill.style.background = '#dc3545';
            }
        } catch (e) {
            uploadStatus.innerHTML = '<i class="fa fa-times-circle"></i> Lỗi ghép file';
            progressFill.style.background = '#dc3545';
        }

        isUploading = false;
        enableSubmit();
    }

    function enableSubmit() {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-paper-plane me-2"></i>Gửi hồ sơ dự thi';
        }
    }

    function sleep(ms) {
        return new Promise(function(resolve) { setTimeout(resolve, ms); });
    }

    // Form submission
    if (form && btn) {
        form.addEventListener('submit', function(e) {
            // Validate images
            var fileInputs = form.querySelectorAll('input[type="file"][accept="image/*"]');
            var maxImageSize = 20 * 1024 * 1024;

            for (var i = 0; i < fileInputs.length; i++) {
                var input = fileInputs[i];
                if (input.files.length > 0 && input.files[0].size > maxImageSize) {
                    e.preventDefault();
                    alert('Ảnh ' + input.files[0].name + ' vượt quá 20MB');
                    return;
                }
            }

            // Check if video is uploading
            if (isUploading) {
                e.preventDefault();
                alert('Vui lòng đợi video upload xong');
                return;
            }

            // Add uploaded video path
            if (videoUploaded && uploadedVideoPath) {
                var hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'BeautyContestants[video_path]';
                hiddenInput.value = uploadedVideoPath;
                form.appendChild(hiddenInput);

                // Remove file input name to prevent re-upload
                if (videoInput) {
                    videoInput.removeAttribute('name');
                }
            }

            // Show loading
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Đang gửi...';
        });
    }
});
