/**
 * Màn quản lý thí sinh Miss — xử lý thay ảnh bình chọn qua modal.
 * Mở modal khi bấm ô "Ảnh bình chọn", upload ảnh bằng AJAX (có loading state),
 * cập nhật lại thumbnail trên lưới mà không reload trang.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('modal_vote_photo');
        var form = document.getElementById('form_vote_photo');
        if (!modalEl || !form) {
            return;
        }

        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        var idInput = document.getElementById('vote_photo_id');
        var nameLabel = document.getElementById('vote_photo_name');
        var preview = document.getElementById('vote_photo_preview');
        var emptyBox = document.getElementById('vote_photo_empty');
        var fileInput = document.getElementById('vote_photo_file');
        var submitBtn = document.getElementById('btn_submit_vote_photo');
        var uploadUrl = form.getAttribute('data-url');
        var defaultBtnHtml = submitBtn.innerHTML;

        // Dòng (nút) đang được chỉnh sửa — để cập nhật thumbnail sau khi lưu.
        var activeCell = null;

        function showPreview(url) {
            if (url) {
                preview.src = url;
                preview.style.display = '';
                emptyBox.style.display = 'none';
            } else {
                preview.src = '';
                preview.style.display = 'none';
                emptyBox.style.display = '';
            }
        }

        // Mở modal khi bấm vào ô ảnh bình chọn.
        document.addEventListener('click', function (e) {
            var cell = e.target.closest('.vote-photo-cell');
            if (!cell) {
                return;
            }
            activeCell = cell;
            submitBtn.disabled = false;
            submitBtn.innerHTML = defaultBtnHtml;
            idInput.value = cell.getAttribute('data-id') || '';
            var name = cell.getAttribute('data-name') || '';
            nameLabel.textContent = name ? '— ' + name : '';
            fileInput.value = '';
            showPreview(cell.getAttribute('data-url') || '');
            modal.show();
        });

        // Xem trước ảnh ngay khi người dùng chọn file.
        fileInput.addEventListener('change', function () {
            if (fileInput.files && fileInput.files[0]) {
                showPreview(URL.createObjectURL(fileInput.files[0]));
            } else if (activeCell) {
                showPreview(activeCell.getAttribute('data-url') || '');
            }
        });

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!fileInput.files || fileInput.files.length === 0) {
                Toast.warning('Vui lòng chọn ảnh.');
                return;
            }

            var originalHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang tải...';

            var formData = new FormData(form);

            fetch(uploadUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (res) { return res.text(); })
                .then(function (text) {
                    var data;
                    try {
                        // Một số cấu hình in warning/notice trước JSON → cắt lấy phần JSON.
                        var start = text.indexOf('{');
                        data = JSON.parse(start >= 0 ? text.slice(start) : text);
                    } catch (err) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHtml;
                        console.error('Vote photo upload — phản hồi thô:', text);
                        Toast.error('Máy chủ trả về dữ liệu không hợp lệ.');
                        return;
                    }

                    if (data.success) {
                        modal.hide();
                        Toast.success(data.message || 'Thành công');
                        if (activeCell && data.vote_photo_url) {
                            updateCellThumb(activeCell, data.vote_photo_url);
                        }
                    } else {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalHtml;
                        Toast.error(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(function () {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                    Toast.error('Lỗi kết nối server');
                });
        });

        function updateCellThumb(cell, url) {
            cell.setAttribute('data-url', url);
            cell.innerHTML = '<img src="' + url + '" alt="Ảnh bình chọn" ' +
                'style="width:44px;height:56px;object-fit:cover;border-radius:4px;">';
        }
    });
})();
