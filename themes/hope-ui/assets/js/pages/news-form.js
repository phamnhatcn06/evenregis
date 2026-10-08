/**
 * news-form.js — Khởi tạo trình soạn thảo TinyMCE, ô chọn ngày và
 * tải ảnh đại diện cho form thêm/sửa tin tức (kiểu WordPress).
 */
(function () {
    'use strict';

    function initEditor() {
        var textarea = document.getElementById('News_content');
        if (!textarea || typeof tinymce === 'undefined') {
            return;
        }

        var uploadUrl = textarea.getAttribute('data-upload-url');
        var langUrl = textarea.getAttribute('data-lang-url');

        tinymce.init({
            target: textarea,
            language: 'vi',
            language_url: langUrl,
            height: 520,
            menubar: 'edit insert view format table tools',
            plugins: 'advlist autolink lists link image media table code fullscreen ' +
                'preview searchreplace visualblocks wordcount insertdatetime charmap quickbars',
            toolbar: 'undo redo | blocks | bold italic underline forecolor backcolor | ' +
                'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | ' +
                'link image media table | removeformat | code preview fullscreen',
            toolbar_mode: 'sliding',
            image_caption: true,
            image_advtab: true,
            quickbars_selection_toolbar: 'bold italic | quicklink h2 h3 blockquote',
            branding: false,
            promotion: false,
            relative_urls: false,
            remove_script_host: true,
            convert_urls: false,
            images_upload_handler: function (blobInfo, progress) {
                return new Promise(function (resolve, reject) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', uploadUrl);
                    xhr.upload.onprogress = function (e) {
                        if (e.lengthComputable) {
                            progress(e.loaded / e.total * 100);
                        }
                    };
                    xhr.onload = function () {
                        var json;
                        try {
                            json = JSON.parse(xhr.responseText);
                        } catch (err) {
                            reject({ message: 'Phản hồi không hợp lệ từ máy chủ.', remove: true });
                            return;
                        }
                        if (xhr.status !== 200 || !json.success || !json.location) {
                            reject({ message: (json && json.message) || 'Tải ảnh thất bại.', remove: true });
                            return;
                        }
                        resolve(json.location);
                    };
                    xhr.onerror = function () {
                        reject({ message: 'Lỗi kết nối khi tải ảnh.', remove: true });
                    };
                    var formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    xhr.send(formData);
                });
            }
        });
    }

    function initDatePicker() {
        var input = document.querySelector('.news-datetime');
        if (input && typeof flatpickr !== 'undefined') {
            flatpickr(input, {
                enableTime: true,
                enableSeconds: true,
                time_24hr: true,
                dateFormat: 'Y-m-d H:i:S',
                allowInput: true
            });
        }
    }

    function initThumbnail() {
        var fileInput = document.getElementById('thumbnail-file');
        var chooseBtn = document.getElementById('btn-choose-thumbnail');
        var removeBtn = document.getElementById('btn-remove-thumbnail');
        var hidden = document.getElementById('News_thumbnail');
        var previewWrap = document.getElementById('thumbnail-preview-wrap');
        var preview = document.getElementById('thumbnail-preview');
        var placeholder = document.getElementById('thumbnail-placeholder');

        if (!fileInput || !chooseBtn || !hidden) {
            return;
        }

        chooseBtn.addEventListener('click', function () {
            fileInput.click();
        });

        fileInput.addEventListener('change', function () {
            if (!fileInput.files || !fileInput.files[0]) {
                return;
            }
            var btnLabel = chooseBtn.querySelector('span');
            var originalText = btnLabel ? btnLabel.textContent : '';
            chooseBtn.disabled = true;
            if (btnLabel) { btnLabel.textContent = 'Đang tải...'; }

            var formData = new FormData();
            formData.append('file', fileInput.files[0]);

            fetch(fileInput.getAttribute('data-upload-url'), { method: 'POST', body: formData })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success && data.url) {
                        hidden.value = data.url;
                        preview.src = data.url;
                        previewWrap.classList.remove('d-none');
                        if (placeholder) { placeholder.classList.add('d-none'); }
                        if (removeBtn) { removeBtn.classList.remove('d-none'); }
                        if (typeof Toast !== 'undefined') { Toast.success('Tải ảnh thành công'); }
                    } else {
                        if (typeof Toast !== 'undefined') { Toast.error(data.message || 'Tải ảnh thất bại'); }
                    }
                })
                .catch(function () {
                    if (typeof Toast !== 'undefined') { Toast.error('Lỗi kết nối khi tải ảnh'); }
                })
                .finally(function () {
                    chooseBtn.disabled = false;
                    if (btnLabel) { btnLabel.textContent = originalText; }
                    fileInput.value = '';
                });
        });

        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                hidden.value = '';
                preview.src = '';
                previewWrap.classList.add('d-none');
                if (placeholder) { placeholder.classList.remove('d-none'); }
                removeBtn.classList.add('d-none');
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        initEditor();
        initDatePicker();
        initThumbnail();
    });
})();
