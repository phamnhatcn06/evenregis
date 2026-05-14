document.addEventListener('DOMContentLoaded', function() {
    // Load registration periods when event is selected
    var eventSelect = document.getElementById('event-select');
    var periodSelect = document.getElementById('period-select');

    if (eventSelect && periodSelect) {
        var apiUrl = periodSelect.getAttribute('data-api-url');
        var apiKey = periodSelect.getAttribute('data-api-key');

        eventSelect.addEventListener('change', function() {
            var eventId = this.value;
            periodSelect.innerHTML = '<option value="">-- Đang tải... --</option>';

            if (!eventId) {
                periodSelect.innerHTML = '<option value="">-- Chọn sự kiện trước --</option>';
                return;
            }

            fetch(apiUrl + '?event_id=' + eventId, {
                headers: {
                    'Authorization': 'Bearer ' + apiKey,
                    'Accept': 'application/json'
                }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                periodSelect.innerHTML = '<option value="">-- Chọn đợt đăng ký --</option>';
                var items = data.data || data;
                if (Array.isArray(items) && items.length > 0) {
                    items.forEach(function(p) {
                        var option = document.createElement('option');
                        option.value = p.id;
                        option.textContent = p.name;
                        periodSelect.appendChild(option);
                    });
                }
            })
            .catch(function() {
                periodSelect.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
            });
        });
    }

    // File upload handling
    var uploadArea = document.getElementById('uploadArea');
    var fileInput = document.getElementById('documentFiles');
    var previewContainer = document.getElementById('filePreview');
    var documentJson = document.getElementById('documentJson');

    var selectedFiles = [];
    var existingFiles = [];

    // Parse existing documents
    if (documentJson.value) {
        try {
            existingFiles = JSON.parse(documentJson.value);
            if (!Array.isArray(existingFiles)) {
                existingFiles = existingFiles ? [existingFiles] : [];
            }
            renderExistingFiles();
        } catch (e) {
            if (documentJson.value) {
                existingFiles = [documentJson.value];
                renderExistingFiles();
            }
        }
    }

    uploadArea.addEventListener('click', function() {
        fileInput.click();
    });

    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadArea.classList.add('bg-light');
    });

    uploadArea.addEventListener('dragleave', function() {
        uploadArea.classList.remove('bg-light');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadArea.classList.remove('bg-light');
        handleFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener('change', function() {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        var allowedTypes = ['application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg', 'image/png'];
        var maxSize = 5 * 1024 * 1024;

        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var ext = file.name.split('.').pop().toLowerCase();
            var validExt = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

            if (validExt.indexOf(ext) === -1) {
                Toast.error('File "' + file.name + '" không được hỗ trợ');
                continue;
            }
            if (file.size > maxSize) {
                Toast.error('File "' + file.name + '" vượt quá 5MB');
                continue;
            }
            selectedFiles.push(file);
        }
        renderPreview();
    }

    function renderExistingFiles() {
        existingFiles.forEach(function(url, index) {
            var filename = url.split('/').pop();
            var ext = filename.split('.').pop().toLowerCase();
            var isImage = ['jpg', 'jpeg', 'png'].indexOf(ext) !== -1;

            var col = document.createElement('div');
            col.className = 'col-6 col-md-3';
            col.id = 'existing-' + index;

            var card = document.createElement('div');
            card.className = 'card h-100 position-relative';

            var preview = '';
            if (isImage) {
                preview = '<img src="' + url + '" class="card-img-top" style="height:80px;object-fit:cover;">';
            } else {
                preview = '<div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:80px;">' +
                    '<i class="fa fa-file-' + getFileIcon(ext) + '-o fa-2x text-muted"></i></div>';
            }

            card.innerHTML = preview +
                '<div class="card-body p-2">' +
                    '<small class="text-truncate d-block" title="' + filename + '">' + filename + '</small>' +
                    '<span class="badge bg-success">Đã lưu</span>' +
                '</div>' +
                '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-existing" data-index="' + index + '">' +
                    '<i class="fa fa-times"></i>' +
                '</button>';

            col.appendChild(card);
            previewContainer.appendChild(col);
        });

        bindRemoveExisting();
    }

    function renderPreview() {
        document.querySelectorAll('.new-file-preview').forEach(function(el) {
            el.remove();
        });

        selectedFiles.forEach(function(file, index) {
            var ext = file.name.split('.').pop().toLowerCase();
            var isImage = ['jpg', 'jpeg', 'png'].indexOf(ext) !== -1;

            var col = document.createElement('div');
            col.className = 'col-6 col-md-3 new-file-preview';
            col.id = 'new-' + index;

            var card = document.createElement('div');
            card.className = 'card h-100 position-relative';

            if (isImage) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    card.querySelector('.preview-img').src = e.target.result;
                };
                reader.readAsDataURL(file);
                card.innerHTML = '<img src="" class="card-img-top preview-img" style="height:80px;object-fit:cover;">' +
                    '<div class="card-body p-2">' +
                        '<small class="text-truncate d-block" title="' + file.name + '">' + file.name + '</small>' +
                        '<span class="badge bg-info">Mới</span>' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-new" data-index="' + index + '">' +
                        '<i class="fa fa-times"></i>' +
                    '</button>';
            } else {
                card.innerHTML = '<div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:80px;">' +
                        '<i class="fa fa-file-' + getFileIcon(ext) + '-o fa-2x text-muted"></i>' +
                    '</div>' +
                    '<div class="card-body p-2">' +
                        '<small class="text-truncate d-block" title="' + file.name + '">' + file.name + '</small>' +
                        '<span class="badge bg-info">Mới</span>' +
                    '</div>' +
                    '<button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1 remove-new" data-index="' + index + '">' +
                        '<i class="fa fa-times"></i>' +
                    '</button>';
            }

            col.appendChild(card);
            previewContainer.appendChild(col);
        });

        bindRemoveNew();
        updateFileInput();
    }

    function getFileIcon(ext) {
        if (ext === 'pdf') return 'pdf';
        if (ext === 'doc' || ext === 'docx') return 'word';
        return 'text';
    }

    function bindRemoveExisting() {
        document.querySelectorAll('.remove-existing').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var index = parseInt(this.getAttribute('data-index'));
                existingFiles.splice(index, 1);
                document.getElementById('existing-' + index).remove();
                reindexExisting();
                updateDocumentJson();
            });
        });
    }

    function bindRemoveNew() {
        document.querySelectorAll('.remove-new').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var index = parseInt(this.getAttribute('data-index'));
                selectedFiles.splice(index, 1);
                renderPreview();
            });
        });
    }

    function reindexExisting() {
        var items = previewContainer.querySelectorAll('[id^="existing-"]');
        existingFiles = [];
        items.forEach(function(item, i) {
            item.id = 'existing-' + i;
            item.querySelector('.remove-existing').setAttribute('data-index', i);
        });
    }

    function updateFileInput() {
        var dt = new DataTransfer();
        selectedFiles.forEach(function(file) {
            dt.items.add(file);
        });
        fileInput.files = dt.files;
    }

    function updateDocumentJson() {
        documentJson.value = existingFiles.length > 0 ? JSON.stringify(existingFiles) : '';
    }

    var form = document.getElementById('registrations-form');
    var relationSelect = document.getElementById('relation-property-select');

    form.addEventListener('submit', function(e) {
        updateDocumentJson();

        // Kiểm tra nếu đang ở chế độ update (có data-original-value)
        if (relationSelect && relationSelect.hasAttribute('data-original-value')) {
            var originalValue = relationSelect.getAttribute('data-original-value') || '';
            var currentValue = relationSelect.value || '';

            // Nếu relation_property_id thay đổi, hiển thị cảnh báo
            if (originalValue !== currentValue) {
                e.preventDefault();

                var message = 'Bạn có chắc chắn muốn cập nhật phiếu đăng ký này?';
                if (currentValue && originalValue !== currentValue) {
                    message = '<p>Bạn có chắc chắn muốn cập nhật phiếu đăng ký này?</p>' +
                        '<div class="alert alert-warning text-start mt-3 mb-0">' +
                        '<i class="fa fa-exclamation-triangle me-2"></i>' +
                        '<strong>Lưu ý:</strong> Đơn vị liên quân đã thay đổi. ' +
                        'Yêu cầu liên quân sẽ cần được duyệt lại từ đơn vị được chọn.' +
                        '</div>';
                } else if (!currentValue && originalValue) {
                    message = '<p>Bạn có chắc chắn muốn cập nhật phiếu đăng ký này?</p>' +
                        '<div class="alert alert-info text-start mt-3 mb-0">' +
                        '<i class="fa fa-info-circle me-2"></i>' +
                        'Yêu cầu liên quân hiện tại sẽ bị hủy.' +
                        '</div>';
                }

                Swal.fire({
                    title: 'Xác nhận cập nhật',
                    html: message,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Cập nhật',
                    cancelButtonText: 'Hủy',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    preConfirm: function() {
                        Swal.showLoading();
                        return new Promise(function() {
                            form.submit();
                        });
                    }
                });
            }
        }
    });
});
