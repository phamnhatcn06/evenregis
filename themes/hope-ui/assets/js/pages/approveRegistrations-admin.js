(function () {
    'use strict';

    var config = document.getElementById('approve-actions-config');
    if (!config) {
        return;
    }

    var approveUrl = config.getAttribute('data-approve-url');
    var returnUrl = config.getAttribute('data-return-url');

    function reloadWithTab() {
        location.reload();
    }

    function approveRegistration(registrationId, propertyName) {
        Swal.fire({
            title: 'Duyệt đăng ký',
            text: 'Bạn có chắc chắn muốn duyệt phiếu đăng ký của ' + (propertyName || 'đơn vị này') + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Duyệt',
            cancelButtonText: 'Hủy',
            allowOutsideClick: false,
            showLoaderOnConfirm: true,
            preConfirm: function () {
                return new Promise(function (resolve, reject) {
                    $.post(approveUrl, { registration_id: registrationId }, function (response) {
                        resolve(response);
                    }, 'json').fail(function () {
                        reject('Có lỗi xảy ra khi gọi API.');
                    });
                }).catch(function (error) {
                    Swal.showValidationMessage(error);
                });
            }
        }).then(function (result) {
            if (result.isConfirmed && result.value) {
                if (result.value.success) {
                    Swal.fire({
                        title: 'Thành công!',
                        text: result.value.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(reloadWithTab);
                } else {
                    Toast.error(result.value.error || 'Có lỗi xảy ra.');
                }
            }
        });
    }

    function returnRegistration(registrationId, propertyName) {
        Swal.fire({
            title: 'Trả lại đăng ký',
            input: 'textarea',
            inputLabel: 'Lý do trả lại' + (propertyName ? ' (' + propertyName + ')' : ''),
            inputPlaceholder: 'Nhập lý do trả lại để đơn vị chỉnh sửa...',
            inputAttributes: {
                'aria-label': 'Lý do trả lại'
            },
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Trả lại',
            cancelButtonText: 'Hủy',
            allowOutsideClick: false,
            showLoaderOnConfirm: true,
            preConfirm: function (reason) {
                if (!reason || !reason.trim()) {
                    Swal.showValidationMessage('Vui lòng nhập lý do trả lại!');
                    return false;
                }
                return new Promise(function (resolve, reject) {
                    $.post(returnUrl, { registration_id: registrationId, reason: reason }, function (response) {
                        resolve(response);
                    }, 'json').fail(function () {
                        reject('Có lỗi xảy ra khi gọi API.');
                    });
                }).catch(function (error) {
                    Swal.showValidationMessage(error);
                });
            }
        }).then(function (result) {
            if (result.isConfirmed && result.value) {
                if (result.value.success) {
                    Swal.fire({
                        title: 'Đã trả lại!',
                        text: result.value.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then(reloadWithTab);
                } else {
                    Toast.error(result.value.error || 'Có lỗi xảy ra.');
                }
            }
        });
    }

    $(document).on('click', '.btn-approve-reg', function () {
        approveRegistration($(this).data('id'), $(this).data('property'));
    });

    $(document).on('click', '.btn-return-reg', function () {
        returnRegistration($(this).data('id'), $(this).data('property'));
    });
})();
