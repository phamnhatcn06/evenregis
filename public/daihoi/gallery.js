/* Thư viện ảnh đại hội: lazy-load (infinite scroll) + lightbox xem ảnh lớn. */
(function () {
  'use strict';

  var grid = document.getElementById('gallery-grid');
  var lightbox = document.getElementById('gallery-lightbox');
  if (!grid || !lightbox) {
    return;
  }

  var albumId = parseInt(grid.getAttribute('data-album-id'), 10);
  var photosUrl = grid.getAttribute('data-photos-url');
  var lastPage = parseInt(grid.getAttribute('data-last-page'), 10) || 1;
  var currentPage = 1;
  var loading = false;

  var glImg = lightbox.querySelector('.gl-img');
  var glDownload = lightbox.querySelector('.gl-download');
  var currentIndex = -1;

  function items() {
    return Array.prototype.slice.call(grid.querySelectorAll('.gallery-item'));
  }

  function escapeAttr(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
      .replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  // Dựng 1 ô ảnh (khớp markup partial _photo_item.php).
  function buildItem(p) {
    var fig = document.createElement('figure');
    fig.className = 'gallery-item';
    fig.setAttribute('data-web', p.web_url || '');
    fig.setAttribute('data-original', p.original_url || '');
    fig.setAttribute('data-name', p.file_name || '');
    fig.innerHTML = '<img src="' + escapeAttr(p.thumb_url) + '" alt="' +
      escapeAttr(p.file_name) + '" loading="lazy">';
    return fig;
  }

  // ---- Lightbox ----
  function openAt(index) {
    var list = items();
    if (index < 0 || index >= list.length) {
      return;
    }
    currentIndex = index;
    var fig = list[index];
    glImg.src = fig.getAttribute('data-web') || fig.querySelector('img').src;
    var original = fig.getAttribute('data-original') || glImg.src;
    glDownload.setAttribute('href', original);
    glDownload.setAttribute('download', fig.getAttribute('data-name') || '');
    lightbox.classList.add('open');
    lightbox.setAttribute('aria-hidden', 'false');
  }

  function close() {
    lightbox.classList.remove('open');
    lightbox.setAttribute('aria-hidden', 'true');
    glImg.src = '';
  }

  function step(delta) {
    var list = items();
    if (!list.length) {
      return;
    }
    openAt((currentIndex + delta + list.length) % list.length);
  }

  grid.addEventListener('click', function (e) {
    var fig = e.target.closest('.gallery-item');
    if (fig) {
      openAt(items().indexOf(fig));
    }
  });

  lightbox.querySelector('.gl-close').addEventListener('click', close);
  lightbox.querySelector('.gl-prev').addEventListener('click', function () { step(-1); });
  lightbox.querySelector('.gl-next').addEventListener('click', function () { step(1); });
  lightbox.addEventListener('click', function (e) {
    if (e.target === lightbox) { close(); }
  });
  document.addEventListener('keydown', function (e) {
    if (!lightbox.classList.contains('open')) {
      return;
    }
    if (e.key === 'Escape') { close(); }
    else if (e.key === 'ArrowLeft') { step(-1); }
    else if (e.key === 'ArrowRight') { step(1); }
  });

  // ---- Infinite scroll ----
  var sentinel = document.getElementById('gallery-sentinel');

  function loadMore() {
    if (loading || currentPage >= lastPage) {
      return;
    }
    loading = true;
    var next = currentPage + 1;
    var url = photosUrl + '?album_id=' + albumId + '&page=' + next;

    fetch(url, { headers: { 'Accept': 'application/json' } })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data && data.success && data.items && data.items.length) {
          var frag = document.createDocumentFragment();
          data.items.forEach(function (p) { frag.appendChild(buildItem(p)); });
          grid.appendChild(frag);
          currentPage = data.page || next;
          lastPage = data.last_page || lastPage;
        } else {
          currentPage = lastPage;
        }
        loading = false;
        if (currentPage >= lastPage && sentinel) {
          sentinel.style.display = 'none';
        }
      })
      .catch(function () {
        loading = false;
        if (sentinel) {
          sentinel.innerHTML = '<span class="text-secondary small">Không tải được thêm ảnh.</span>';
        }
      });
  }

  if (sentinel && 'IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      if (entries[0].isIntersecting) { loadMore(); }
    }, { rootMargin: '400px' });
    observer.observe(sentinel);
  } else if (sentinel) {
    window.addEventListener('scroll', function () {
      if (sentinel.getBoundingClientRect().top < window.innerHeight + 400) { loadMore(); }
    });
  }
})();
