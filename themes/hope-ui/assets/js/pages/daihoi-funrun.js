/**
 * Trang thông tin Fun Run: click ảnh sơ đồ đường chạy để mở lightbox phóng to.
 */
document.addEventListener('DOMContentLoaded', function () {
    var lightbox = document.getElementById('funrunLightbox');
    if (!lightbox) {
        return;
    }
    var lightboxImg = lightbox.querySelector('.funrun-lightbox__img');
    var closeBtn = lightbox.querySelector('.funrun-lightbox__close');

    function openLightbox(src, alt) {
        lightboxImg.setAttribute('src', src);
        lightboxImg.setAttribute('alt', alt || 'Ảnh sơ đồ phóng to');
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.remove('is-open');
        lightboxImg.setAttribute('src', '');
        document.body.style.overflow = '';
    }

    var maps = document.querySelectorAll('.funrun-map');
    maps.forEach(function (img) {
        img.addEventListener('click', function () {
            openLightbox(img.getAttribute('data-full') || img.getAttribute('src'), img.getAttribute('alt'));
        });
    });

    closeBtn.addEventListener('click', closeLightbox);

    // Click nền tối để đóng, nhưng không đóng khi click vào chính ảnh.
    lightbox.addEventListener('click', function (e) {
        if (e.target !== lightboxImg) {
            closeLightbox();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && lightbox.classList.contains('is-open')) {
            closeLightbox();
        }
    });
});
