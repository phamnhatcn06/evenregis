<!-- Modal Gửi Email Xác Nhận -->
<div class="modal fade" id="sendMailModal" tabindex="-1" aria-labelledby="sendMailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="sendMailModalLabel">
                    <i class="fa fa-paper-plane me-2"></i>Gửi email xác nhận đăng ký
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="form-send-mail" onsubmit="return false;">
                    <input type="hidden" id="send_mail_registration_id" name="registration_id" value="">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small mb-1">Đơn vị đăng ký:</label>
                        <div class="fw-bold fs-6 text-dark" id="send_mail_property_display">-</div>
                    </div>

                    <div class="mb-3">
                        <label for="send_mail_recipient_email" class="form-label fw-bold">
                            Email người nhận <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control" id="send_mail_recipient_email" name="recipient_email" placeholder="Ví dụ: donvi@muongthanh.vn" required>
                        </div>
                        <div class="form-text text-muted">Mặc định là email cấu hình của đơn vị (cột mail_confirm). Bạn có thể thay đổi địa chỉ email này nếu cần.</div>
                    </div>

                    <div class="alert alert-info d-flex align-items-start mb-0 p-2 text-dark" style="font-size: 13px;">
                        <i class="fa fa-info-circle me-2 mt-1 text-info fs-5"></i>
                        <div>
                            Nội dung mail sẽ tự động kèm theo danh sách chi tiết:
                            <ul class="mb-0 ps-3 mt-1">
                                <li><strong>Đợt 1</strong>: Bảng các đội thi theo môn thể thao kèm danh sách VĐV và danh sách thí sinh Miss.</li>
                                <li><strong>Đợt 2</strong>: Bảng các môn thi nghiệp vụ và danh sách thí sinh dự thi.</li>
                                <li><strong>Đợt 3</strong>: Danh sách các tiết mục và thông tin văn nghệ.</li>
                            </ul>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">
                    <i class="fa fa-times me-1"></i>Hủy
                </button>
                <button type="button" class="btn btn-primary px-4" id="btn-do-send-mail" onclick="submitSendMail()">
                    <i class="fa fa-paper-plane me-1" id="icon-send-mail"></i>Gửi email
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openSendMailModal(registrationId, propertyName, submittedByEmail) {
    document.getElementById('send_mail_registration_id').value = registrationId;
    document.getElementById('send_mail_property_display').textContent = propertyName || 'Đơn vị';

    var emailInput = document.getElementById('send_mail_recipient_email');
    // Chỉ lấy danh sách email cấu hình ở cột mail_confirm của đơn vị (property).
    // KHÔNG dùng email người nộp phiếu (submitted_by) làm người nhận.
    emailInput.value = '';
    emailInput.placeholder = 'Đang tải email đơn vị...';

    var url = '<?php echo Yii::app()->createUrl("/admin/approveRegistrations/getMailRecipient"); ?>?registration_id=' + encodeURIComponent(registrationId);
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data && data.success && data.email) {
                emailInput.value = data.email;
                emailInput.placeholder = 'Ví dụ: donvi@muongthanh.vn';
            } else {
                emailInput.value = '';
                emailInput.placeholder = 'Đơn vị chưa cấu hình email nhận (mail_confirm)';
            }
        })
        .catch(function() {
            emailInput.value = '';
            emailInput.placeholder = 'Ví dụ: donvi@muongthanh.vn';
        });

    var modalEl = document.getElementById('sendMailModal');
    var modal = bootstrap.Modal.getInstance(modalEl);
    if (!modal) {
        modal = new bootstrap.Modal(modalEl);
    }
    modal.show();
}

function submitSendMail() {
    var regId = document.getElementById('send_mail_registration_id').value;
    var email = document.getElementById('send_mail_recipient_email').value.trim();

    if (!email) {
        if (typeof Swal !== 'undefined') {
            Swal.fire('Lỗi', 'Vui lòng nhập địa chỉ email người nhận.', 'error');
        } else {
            alert('Vui lòng nhập địa chỉ email người nhận.');
        }
        return;
    }

    var btn = document.getElementById('btn-do-send-mail');
    var icon = document.getElementById('icon-send-mail');
    btn.disabled = true;
    icon.className = 'fa fa-spinner fa-spin me-1';

    var formData = new FormData();
    formData.append('registration_id', regId);
    formData.append('recipient_email', email);
    if (typeof yii !== 'undefined' && yii.getCsrfToken) {
        formData.append(yii.getCsrfTokenName(), yii.getCsrfToken());
    }

    fetch('<?php echo Yii::app()->createUrl("/admin/approveRegistrations/sendEmail"); ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(function(response) {
        return response.text().then(function(text) {
            var data;
            try {
                var jsonMatch = text.match(/\{[\s\S]*\}/);
                if (jsonMatch) {
                    data = JSON.parse(jsonMatch[0]);
                } else {
                    data = JSON.parse(text);
                }
            } catch (e) {
                console.error("Server response raw text:", text);
                data = { success: false, error: 'Phản hồi từ máy chủ không hợp lệ (' + response.status + '): ' + (text ? text.substring(0, 200) : 'Rỗng') };
            }
            return data;
        });
    })
    .then(function(data) {
        btn.disabled = false;
        icon.className = 'fa fa-paper-plane me-1';

        if (data.success) {
            var modalEl = document.getElementById('sendMailModal');
            var modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            // Cập nhật ngay cột trạng thái "Gửi mail" của phiếu vừa gửi (trên mọi tab)
            var statusCells = document.querySelectorAll('.send-mail-status[data-reg-id="' + regId + '"]');
            statusCells.forEach(function(cell) {
                cell.innerHTML = '<span class="badge bg-success"><i class="fa fa-check me-1"></i>Đã gửi</span>';
            });

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công',
                    text: data.message || 'Đã gửi email xác nhận thành công.'
                });
            } else if (typeof $.toast !== 'undefined') {
                $.toast({
                    heading: 'Thành công',
                    text: data.message || 'Đã gửi email xác nhận thành công.',
                    icon: 'success',
                    position: 'top-right'
                });
            } else {
                alert(data.message || 'Đã gửi email xác nhận thành công.');
            }
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire('Lỗi', data.error || 'Không thể gửi email.', 'error');
            } else {
                alert(data.error || 'Không thể gửi email.');
            }
        }
    })
    .catch(function(err) {
        btn.disabled = false;
        icon.className = 'fa fa-paper-plane me-1';
        console.error("Fetch error:", err);
        var msg = (err && err.message) ? err.message : 'Đã xảy ra lỗi khi kết nối tới máy chủ.';
        if (typeof Swal !== 'undefined') {
            Swal.fire('Lỗi kết nối', msg, 'error');
        } else {
            alert('Lỗi kết nối: ' + msg);
        }
    });
}

// Delegation cho các nút Gửi mail trong DataTables
document.addEventListener('click', function(e) {
    var btn = e.target.closest('.btn-send-mail');
    if (btn) {
        e.preventDefault();
        var id = btn.getAttribute('data-id');
        var property = btn.getAttribute('data-property') || '';
        var email = btn.getAttribute('data-email') || '';
        openSendMailModal(id, property, email);
    }
});
</script>
