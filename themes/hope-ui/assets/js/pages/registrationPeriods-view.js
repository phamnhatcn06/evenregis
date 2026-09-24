document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btn-build-final');
    if (!btn) {
        return;
    }

    var resultBox = document.getElementById('build-final-result');

    function renderSummary(data) {
        if (!data) {
            resultBox.innerHTML = '';
            return;
        }
        var rows = [
            ['Số đơn vị', data.organizations],
            ['Người tạo mới', data.attendees_created],
            ['Người đã có', data.attendees_existing],
            ['Nội dung liên kết', data.contents_linked],
            ['Finalist nguồn', data.source_finalists],
            ['Bỏ qua (thiếu đơn vị)', data.skipped_no_property]
        ];
        var html = '<div class="table-responsive"><table class="table table-bordered table-sm mb-0"><tbody>';
        rows.forEach(function (r) {
            var val = (r[1] === undefined || r[1] === null) ? 0 : r[1];
            html += '<tr><th style="width:60%;background:#f8f9fa;">' + r[0] + '</th><td>' + val + '</td></tr>';
        });
        html += '</tbody></table></div>';
        resultBox.innerHTML = html;
    }

    btn.addEventListener('click', function () {
        var url = btn.getAttribute('data-url');
        var originalHtml = btn.innerHTML;

        Swal.fire({
            title: 'Tổng hợp finalist vào VCK',
            text: 'Hệ thống sẽ gom toàn bộ VĐV/thí sinh đã lọt chung kết vào đợt đăng ký này. Bạn có chắc chắn?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f0a500',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Tổng hợp',
            cancelButtonText: 'Hủy'
        }).then(function (result) {
            if (!result.isConfirmed) {
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang tổng hợp...';

            fetch(url, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    if (data.success) {
                        Toast.success(data.message || 'Tổng hợp thành công');
                        renderSummary(data.data);
                    } else {
                        Toast.error(data.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    Toast.error('Lỗi kết nối server');
                });
        });
    });
});
