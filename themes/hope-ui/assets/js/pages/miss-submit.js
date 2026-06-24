document.addEventListener('DOMContentLoaded', function() {
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

    // Chunked video upload with Resumable.js
    var videoWrapper = document.querySelector('.video-upload-wrapper');
    var videoInput = document.getElementById('video-input');
    var videoPreview = document.getElementById('video-preview');
    var form = document.getElementById('miss-submit-form');
    var btn = document.getElementById('btn_submit');

    // Get config from data attributes
    var uploadUrl = videoWrapper ? videoWrapper.getAttribute('data-upload-url') : null;
    var contestantId = videoWrapper ? videoWrapper.getAttribute('data-contestant-id') : null;
    var folderName = videoWrapper ? videoWrapper.getAttribute('data-folder-name') : null;
    var csrfToken = form ? form.querySelector('input[name="' + document.querySelector('meta[name="csrf-param"]')?.content + '"]')?.value : null;

    var videoUploaded = false;
    var uploadedVideoPath = '';
    var resumable = null;

    // Create progress bar
    var progressContainer = document.createElement('div');
    progressContainer.className = 'video-progress-container';
    progressContainer.style.display = 'none';
    progressContainer.innerHTML =
        '<div class="progress" style="height: 25px; margin-top: 10px;">' +
            '<div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%">' +
                '<span class="progress-text">0%</span>' +
            '</div>' +
        '</div>' +
        '<div class="upload-status text-center mt-2" style="font-size: 14px;"></div>';

    if (videoWrapper) {
        videoWrapper.appendChild(progressContainer);
    }

    var progressBar = progressContainer.querySelector('.progress-bar');
    var progressText = progressContainer.querySelector('.progress-text');
    var uploadStatus = progressContainer.querySelector('.upload-status');

    // Initialize Resumable.js
    if (typeof Resumable !== 'undefined' && uploadUrl) {
        resumable = new Resumable({
            target: uploadUrl,
            chunkSize: 5 * 1024 * 1024, // 5MB chunks
            simultaneousUploads: 1,
            testChunks: false,
            throttleProgressCallbacks: 1,
            query: {
                contestant_id: contestantId,
                folder_name: folderName
            },
            headers: {},
            maxFileSize: 500 * 1024 * 1024, // 500MB
            fileType: ['mp4', 'mov', 'avi', 'wmv', 'mkv']
        });

        // Assign browse button
        if (videoInput) {
            resumable.assignBrowse(videoInput);
            resumable.assignDrop(videoWrapper);
        }

        resumable.on('fileAdded', function(file) {
            // Show preview
            var url = URL.createObjectURL(file.file);
            videoPreview.src = url;
            videoPreview.style.display = 'block';

            // Show progress
            progressContainer.style.display = 'block';
            uploadStatus.innerHTML = '<i class="fa fa-cloud-upload"></i> Đang chuẩn bị upload...';
            uploadStatus.className = 'upload-status text-center mt-2 text-info';

            // Disable submit while uploading
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Đang tải video...';
            }

            // Start upload
            resumable.upload();
        });

        resumable.on('fileProgress', function(file) {
            var progress = Math.floor(file.progress() * 100);
            progressBar.style.width = progress + '%';
            progressText.textContent = progress + '%';

            var uploaded = formatSize(file.size * file.progress());
            var total = formatSize(file.size);
            uploadStatus.innerHTML = '<i class="fa fa-upload"></i> Đang tải: ' + uploaded + ' / ' + total;
        });

        resumable.on('fileSuccess', function(file, response) {
            try {
                var data = JSON.parse(response);
                if (data.success && data.completed) {
                    videoUploaded = true;
                    uploadedVideoPath = data.path;

                    progressBar.style.width = '100%';
                    progressBar.className = 'progress-bar bg-success';
                    progressText.textContent = '100%';
                    uploadStatus.innerHTML = '<i class="fa fa-check-circle"></i> Upload hoàn tất!';
                    uploadStatus.className = 'upload-status text-center mt-2 text-success';

                    // Re-enable submit
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa fa-paper-plane me-2"></i>Gửi hồ sơ dự thi';
                    }
                }
            } catch (e) {
                console.error('Parse error:', e);
            }
        });

        resumable.on('fileError', function(file, message) {
            progressBar.className = 'progress-bar bg-danger';
            uploadStatus.innerHTML = '<i class="fa fa-exclamation-circle"></i> Lỗi upload. <a href="#" onclick="location.reload()">Thử lại</a>';
            uploadStatus.className = 'upload-status text-center mt-2 text-danger';

            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-paper-plane me-2"></i>Gửi hồ sơ dự thi';
            }
        });

        resumable.on('fileRetry', function(file) {
            uploadStatus.innerHTML = '<i class="fa fa-refresh fa-spin"></i> Đang thử lại...';
            uploadStatus.className = 'upload-status text-center mt-2 text-warning';
        });
    }

    // Form submission
    if (form && btn) {
        form.addEventListener('submit', function(e) {
            // Validate images
            var fileInputs = form.querySelectorAll('input[type="file"][accept="image/*"]');
            var maxImageSize = 20 * 1024 * 1024;
            var hasError = false;

            fileInputs.forEach(function(input) {
                if (input.files.length > 0) {
                    var file = input.files[0];
                    if (file.size > maxImageSize) {
                        e.preventDefault();
                        hasError = true;
                        alert('Ảnh ' + file.name + ' vượt quá 20MB');
                        return;
                    }
                }
            });

            if (hasError) return;

            // Check if video is being uploaded
            if (resumable && resumable.isUploading()) {
                e.preventDefault();
                alert('Vui lòng đợi video upload xong');
                return;
            }

            // Add uploaded video path to form
            if (videoUploaded && uploadedVideoPath) {
                var hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'BeautyContestants[video_path]';
                hiddenInput.value = uploadedVideoPath;
                form.appendChild(hiddenInput);

                // Remove file input to prevent re-upload
                if (videoInput) {
                    videoInput.removeAttribute('name');
                }
            }

            // Show loading
            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Đang gửi...';
        });
    }

    // Fallback for browsers without Resumable support or small files
    if (videoInput && (!resumable || !resumable.support)) {
        videoInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file && file.type.startsWith('video/')) {
                var url = URL.createObjectURL(file);
                videoPreview.src = url;
                videoPreview.style.display = 'block';

                if (file.size > 500 * 1024 * 1024) {
                    alert('Video tối đa 500MB');
                    videoInput.value = '';
                    videoPreview.style.display = 'none';
                }
            }
        });
    }

    function formatSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        if (bytes < 1024 * 1024 * 1024) return (bytes / 1024 / 1024).toFixed(1) + ' MB';
        return (bytes / 1024 / 1024 / 1024).toFixed(1) + ' GB';
    }
});
