# Modal Submit Loading State

## Quy tắc bắt buộc

Khi submit form trong modal, **PHẢI** thực hiện:

1. **Disable nút submit** ngay khi click
2. **Hiển thị loading spinner** trên nút submit
3. **Chờ response** từ server
4. **Đóng modal** sau khi nhận response thành công
5. **Khôi phục nút** nếu có lỗi

## Pattern chuẩn

### HTML - Nút submit trong modal
```html
<button type="submit" class="btn btn-primary" id="btn_submit_xxx">
    <i class="fa fa-save me-1"></i>Lưu
</button>
```

### JavaScript - Xử lý submit
```javascript
form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    var btn = document.getElementById('btn_submit_xxx');
    var originalHtml = btn.innerHTML;
    
    // 1. Disable và hiển thị loading
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';
    
    // 2. Gửi request
    fetch(url, { method: 'POST', body: formData })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                // 3. Đóng modal
                var modal = bootstrap.Modal.getInstance(document.getElementById('modalId'));
                if (modal) modal.hide();
                
                // 4. Thông báo và refresh/redirect
                Toast.success(data.message || 'Thành công');
                location.reload(); // hoặc cập nhật UI
            } else {
                // Khôi phục nút nếu lỗi
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                Toast.error(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(function(err) {
            // Khôi phục nút nếu lỗi network
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            Toast.error('Lỗi kết nối server');
        });
});
```

## Helper Function (khuyến nghị)

Thêm vào file JS chung để tái sử dụng:

```javascript
/**
 * Xử lý submit form trong modal với loading state
 * @param {string} formId - ID của form
 * @param {string} btnId - ID của nút submit
 * @param {string} modalId - ID của modal
 * @param {Function} onSuccess - Callback khi thành công (optional)
 */
function submitModalForm(formId, btnId, modalId, onSuccess) {
    var form = document.getElementById(formId);
    var btn = document.getElementById(btnId);
    var originalHtml = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';
    
    var formData = new FormData(form);
    
    fetch(form.action, { method: 'POST', body: formData })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
                if (modal) modal.hide();
                Toast.success(data.message || 'Thành công');
                if (typeof onSuccess === 'function') {
                    onSuccess(data);
                } else {
                    location.reload();
                }
            } else {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                Toast.error(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
            Toast.error('Lỗi kết nối server');
        });
}
```

## Lưu ý

- **KHÔNG** để form submit theo cách truyền thống (page reload)
- **KHÔNG** cho phép click nhiều lần khi đang xử lý
- **LUÔN** khôi phục trạng thái nút khi có lỗi
- **LUÔN** hiển thị thông báo (Toast) cho user biết kết quả
