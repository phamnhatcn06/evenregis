/* Thư viện ảnh đại hội: lazy-load (infinite scroll) + lightbox + đánh dấu "ảnh của tôi". */
(function () {
  'use strict';

  var grid = document.getElementById('gallery-grid');
  var lightbox = document.getElementById('gallery-lightbox');
  if (!grid || !lightbox) {
    return;
  }

  var albumId = parseInt(grid.getAttribute('data-album-id'), 10);
  var photosUrl = grid.getAttribute('data-photos-url');
  var claimUrl = grid.getAttribute('data-claim-url');
  var identified = grid.getAttribute('data-identified') === '1';
  var lastPage = parseInt(grid.getAttribute('data-last-page'), 10) || 1;
  var currentPage = 1;
  var loading = false;

  var glImg = lightbox.querySelector('.gl-img');
  var glDownload = lightbox.querySelector('.gl-download');
  var glClaim = lightbox.querySelector('.gl-claim');
  var glClaimLabel = lightbox.querySelector('.gl-claim-label');
  var glClaimers = lightbox.querySelector('.gl-claimers');
  var currentIndex = -1;

  if (identified) {
    lightbox.classList.add('can-claim');
  }

  function items() {
    return Array.prototype.slice.call(grid.querySelectorAll('.gallery-item'));
  }

  function escapeAttr(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;').replace(/"/g, '&quot;')
      .replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  // ---- Toast nhỏ ----
  var toastEl;
  function toast(message, isError) {
    if (!toastEl) {
      toastEl = document.createElement('div');
      toastEl.className = 'gl-toast';
      document.body.appendChild(toastEl);
    }
    toastEl.textContent = message;
    toastEl.className = 'gl-toast show' + (isError ? ' err' : '');
    clearTimeout(toastEl._t);
    toastEl._t = setTimeout(function () { toastEl.className = 'gl-toast'; }, 3000);
  }

  // Dựng 1 ô ảnh (khớp markup partial _photo_item.php).
  function buildItem(p) {
    var mine = p.mine ? '1' : '0';
    var count = p.claim_count || 0;
    var names = (p.claimer_names || []).join(', ');
    var fig = document.createElement('figure');
    fig.className = 'gallery-item' + (p.mine ? ' is-mine' : '');
    fig.setAttribute('data-id', p.id);
    fig.setAttribute('data-web', p.web_url || '');
    fig.setAttribute('data-original', p.original_url || '');
    fig.setAttribute('data-name', p.file_name || '');
    fig.setAttribute('data-mine', mine);
    fig.setAttribute('data-count', count);
    fig.setAttribute('data-names', names);
    fig.innerHTML =
      '<img src="' + escapeAttr(p.thumb_url) + '" alt="' + escapeAttr(p.file_name) + '" loading="lazy">' +
      '<button type="button" class="gi-claim" title="Đánh dấu ảnh của tôi"><span class="material-symbols-outlined">' +
      (p.mine ? 'how_to_reg' : 'person_add') + '</span></button>' +
      '<span class="gi-count"' + (count > 0 ? '' : ' style="display:none;"') +
      '><span class="material-symbols-outlined">group</span><span class="gi-count-n">' + count + '</span></span>';
    return fig;
  }

  // Cập nhật 1 ô ảnh theo kết quả claim/unclaim.
  function applyClaimState(fig, data) {
    fig.setAttribute('data-mine', data.mine ? '1' : '0');
    fig.setAttribute('data-count', data.claim_count);
    fig.setAttribute('data-names', (data.claimer_names || []).join(', '));
    fig.classList.toggle('is-mine', !!data.mine);
    var icon = fig.querySelector('.gi-claim .material-symbols-outlined');
    if (icon) { icon.textContent = data.mine ? 'how_to_reg' : 'person_add'; }
    var countEl = fig.querySelector('.gi-count');
    var countN = fig.querySelector('.gi-count-n');
    if (countEl && countN) {
      countN.textContent = data.claim_count;
      countEl.style.display = data.claim_count > 0 ? '' : 'none';
    }
  }

  // Gọi API đánh dấu / bỏ đánh dấu cho 1 ô ảnh.
  function toggleClaim(fig, done) {
    if (!identified) {
      toast('Quét QR trên thẻ của bạn để đánh dấu ảnh.', true);
      return;
    }
    var photoId = fig.getAttribute('data-id');
    var isMine = fig.getAttribute('data-mine') === '1';
    var body = new URLSearchParams();
    body.set('photo_id', photoId);
    body.set('action', isMine ? 'unclaim' : 'claim');

    fetch(claimUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
      body: body.toString()
    })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data && data.success) {
          applyClaimState(fig, data);
          toast(data.mine ? 'Đã đánh dấu ảnh của bạn.' : 'Đã bỏ đánh dấu.');
          if (done) { done(data); }
        } else {
          toast((data && data.message) || 'Không thực hiện được.', true);
        }
      })
      .catch(function () { toast('Lỗi kết nối. Vui lòng thử lại.', true); });
  }

  // ---- Lightbox ----
  function renderLightboxClaim(fig) {
    if (!identified || !glClaim) {
      return;
    }
    var mine = fig.getAttribute('data-mine') === '1';
    glClaim.classList.toggle('is-mine', mine);
    glClaim.querySelector('.material-symbols-outlined').textContent = mine ? 'how_to_reg' : 'person_add';
    if (glClaimLabel) { glClaimLabel.textContent = mine ? 'Bỏ đánh dấu' : 'Đây là ảnh của tôi'; }
    var names = fig.getAttribute('data-names') || '';
    if (glClaimers) {
      if (names) {
        glClaimers.textContent = 'Có mặt: ' + names;
        glClaimers.style.display = '';
      } else {
        glClaimers.style.display = 'none';
      }
    }
  }

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
    renderLightboxClaim(fig);
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
    var claimBtn = e.target.closest('.gi-claim');
    if (claimBtn) {
      e.stopPropagation();
      toggleClaim(claimBtn.closest('.gallery-item'));
      return;
    }
    var fig = e.target.closest('.gallery-item');
    if (fig) {
      openAt(items().indexOf(fig));
    }
  });

  lightbox.querySelector('.gl-close').addEventListener('click', close);
  lightbox.querySelector('.gl-prev').addEventListener('click', function () { step(-1); });
  lightbox.querySelector('.gl-next').addEventListener('click', function () { step(1); });
  if (glClaim) {
    glClaim.addEventListener('click', function () {
      var fig = items()[currentIndex];
      if (fig) {
        toggleClaim(fig, function () { renderLightboxClaim(fig); });
      }
    });
  }
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
