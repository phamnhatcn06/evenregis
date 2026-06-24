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

    // Video preview handling
    var videoInput = document.getElementById('video-input');
    var videoPreview = document.getElementById('video-preview');

    if (videoInput && videoPreview) {
        videoInput.addEventListener('change', function(e) {
            var file = e.target.files[0];
            if (file && file.type.startsWith('video/')) {
                var url = URL.createObjectURL(file);
                videoPreview.src = url;
                videoPreview.style.display = 'block';
            }
        });
    }

    // Form submission
    var form = document.getElementById('miss-submit-form');
    var btn = document.getElementById('btn_submit');

    if (form && btn) {
        form.addEventListener('submit', function(e) {
            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Đang gửi...';

            var fileInputs = form.querySelectorAll('input[type="file"]');
            var maxImageSize = 20 * 1024 * 1024;
            var maxVideoSize = 500 * 1024 * 1024;
            var hasError = false;

            fileInputs.forEach(function(input) {
                if (input.files.length > 0) {
                    var file = input.files[0];
                    var isVideo = input.name === 'video_path';
                    var maxSize = isVideo ? maxVideoSize : maxImageSize;

                    if (file.size > maxSize) {
                        e.preventDefault();
                        hasError = true;
                        var sizeLabel = isVideo ? '500MB' : '20MB';
                        alert('File ' + file.name + ' vượt quá kích thước cho phép (' + sizeLabel + ')');
                        btn.disabled = false;
                        btn.innerHTML = originalHtml;
                    }
                }
            });
        });
    }
});
