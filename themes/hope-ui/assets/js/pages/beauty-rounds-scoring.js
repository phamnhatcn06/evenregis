document.addEventListener('DOMContentLoaded', function() {
    var saveUrl = document.getElementById('save_score_url').value;

    document.querySelectorAll('.btn-save-score').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var card = this.closest('.contestant-card');
            var resultId = card.getAttribute('data-result-id');
            var scoreInput = card.querySelector('.score-input');
            var noteInput = card.querySelector('.note-input');
            var scoreBadge = card.querySelector('.score-badge');
            var originalHtml = this.innerHTML;

            var score = scoreInput.value;
            var note = noteInput.value;

            if (score === '' || isNaN(score)) {
                Toast.warning('Vui lòng nhập điểm hợp lệ.');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

            var formData = new FormData();
            formData.append('result_id', resultId);
            formData.append('score', score);
            formData.append('note', note);

            fetch(saveUrl, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;

                if (data.success) {
                    Toast.success(data.message);
                    scoreBadge.textContent = parseFloat(score).toFixed(1);
                    card.classList.add('border-success');
                    setTimeout(function() {
                        card.classList.remove('border-success');
                    }, 2000);
                } else {
                    Toast.error(data.message);
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                Toast.error('Lỗi kết nối server');
            });
        });
    });

    document.querySelectorAll('.score-input').forEach(function(input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var btn = this.closest('.contestant-card').querySelector('.btn-save-score');
                btn.click();
            }
        });
    });
});
