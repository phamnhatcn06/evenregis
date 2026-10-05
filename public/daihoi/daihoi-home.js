/**
 * Daihoi public homepage - JS.
 * - Hero slider carousel (tự chạy + prev/next + dots)
 * - Tự động làm mới khối "Kết quả nổi bật" (realtime) mỗi 30 giây
 * Vanilla JS, không phụ thuộc thư viện ngoài. Tách khỏi view theo rule dự án.
 */
(function () {
  'use strict';

  /* ===================== Hero Slider ===================== */
  (function initSlider() {
    var slides = document.querySelectorAll('.hero-slide');
    var dots = document.querySelectorAll('.slider-dot');
    var counter = document.getElementById('slider-counter');
    var prevBtn = document.getElementById('slider-prev');
    var nextBtn = document.getElementById('slider-next');

    if (!slides.length) return;

    var currentSlide = 0;
    var totalSlides = slides.length;
    var autoSlideTimer;

    function showSlide(index) {
      if (index < 0) index = totalSlides - 1;
      if (index >= totalSlides) index = 0;

      slides.forEach(function (slide, idx) {
        if (idx === index) {
          slide.classList.remove('d-none');
          setTimeout(function () {
            slide.classList.remove('opacity-0');
            slide.classList.add('opacity-100', 'active');
          }, 20);
        } else {
          slide.classList.remove('opacity-100', 'active');
          slide.classList.add('opacity-0');
          setTimeout(function () {
            if (!slide.classList.contains('active')) {
              slide.classList.add('d-none');
            }
          }, 300);
        }
      });

      dots.forEach(function (dot, idx) {
        if (idx === index) {
          dot.style.width = '24px';
          dot.style.backgroundColor = '#2dd4bf';
        } else {
          dot.style.width = '8px';
          dot.style.backgroundColor = 'rgba(255,255,255,0.4)';
        }
      });

      if (counter) {
        counter.textContent = '0' + (index + 1) + ' / 0' + totalSlides;
      }

      currentSlide = index;
    }

    function startAutoSlide() {
      stopAutoSlide();
      autoSlideTimer = setInterval(function () {
        showSlide(currentSlide + 1);
      }, 5500);
    }

    function stopAutoSlide() {
      if (autoSlideTimer) clearInterval(autoSlideTimer);
    }

    if (nextBtn) {
      nextBtn.addEventListener('click', function () {
        showSlide(currentSlide + 1);
        startAutoSlide();
      });
    }
    if (prevBtn) {
      prevBtn.addEventListener('click', function () {
        showSlide(currentSlide - 1);
        startAutoSlide();
      });
    }
    dots.forEach(function (dot) {
      dot.addEventListener('click', function () {
        var target = parseInt(this.getAttribute('data-slide'), 10);
        showSlide(target);
        startAutoSlide();
      });
    });

    startAutoSlide();
  })();

  /* ===================== Realtime: Kết quả nổi bật ===================== */
  (function initLiveResults() {
    var root = document.getElementById('daihoi-root');
    var grid = document.getElementById('live-match-grid');
    if (!root || !grid) return;

    var baseUrl = root.getAttribute('data-base-url') || '';

    function esc(s) {
      var div = document.createElement('div');
      div.textContent = s == null ? '' : String(s);
      return div.innerHTML;
    }

    var STATUS = {
      live: { cls: 'bg-danger', text: 'Đang diễn ra' },
      done: { cls: 'bg-secondary', text: 'Đã kết thúc' },
      upcoming: { cls: 'bg-primary', text: 'Sắp diễn ra' },
      ongoing: { cls: 'bg-danger', text: 'Đang diễn ra' },
      completed: { cls: 'bg-secondary', text: 'Đã kết thúc' },
      scheduled: { cls: 'bg-primary', text: 'Sắp diễn ra' },
      cancelled: { cls: 'bg-light text-dark', text: 'Đã huỷ' },
      postponed: { cls: 'bg-warning text-dark', text: 'Hoãn' }
    };

    function initials(name) {
      var parts = String(name || '').trim().split(/\s+/);
      var s = '';
      for (var i = 0; i < parts.length && s.length < 3; i++) {
        if (parts[i]) s += parts[i].charAt(0);
      }
      return (s || '--').toUpperCase().slice(0, 3);
    }

    function scores(m) {
      if (m.home_score != null && m.away_score != null) {
        return [m.home_score, m.away_score];
      }
      var raw = m.score != null ? String(m.score) : '';
      var parts = raw.split(/[-:]/);
      if (parts.length === 2) return [parts[0].trim(), parts[1].trim()];
      return ['', ''];
    }

    function buildCard(m) {
      var st = STATUS[m.status] || STATUS.done;
      var sc = scores(m);
      var home = m.home_name || m.home || m.team_a || '';
      var away = m.away_name || m.away || m.team_b || '';
      var title = m.sport_name || m.category || 'Thi đấu';
      var sub = m.round_name || m.stage || '';
      var meta = (m.time || m.kickoff_time || '') + (m.venue ? ' • ' + m.venue : '');

      return '' +
        '<div class="col-12 col-md-4">' +
        '  <div class="card h-100 border-0 rounded-4 shadow-sm bg-white overflow-hidden card-hover" style="border-top: 4px solid #10b981 !important;">' +
        '    <div class="p-3 pb-2 border-bottom bg-slate-50 d-flex align-items-center justify-content-between">' +
        '      <div class="d-flex align-items-center gap-2">' +
        '        <div class="rounded-circle bg-teal-50 text-teal-600 border border-teal-200 d-flex align-items-center justify-content-center" style="width:28px;height:28px;">' +
        '          <span class="material-symbols-outlined fs-6">emoji_events</span>' +
        '        </div>' +
        '        <div><span class="fw-bold text-dark small d-block lh-1">' + esc(title) + '</span>' +
        '          <span class="text-muted" style="font-size:10.5px;">' + esc(sub) + '</span></div>' +
        '      </div>' +
        '      <span class="badge ' + st.cls + ' text-white rounded-pill px-2 py-1 fw-semibold" style="font-size:10px;">' + esc(st.text) + '</span>' +
        '    </div>' +
        '    <div class="p-3">' +
        '      <div class="d-flex align-items-center justify-content-between p-2 rounded-3 mb-2" style="background-color:#f8fafc;border-left:3px solid #2563eb;">' +
        '        <div class="d-flex align-items-center gap-2"><div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:11px;background:linear-gradient(135deg,#1d4ed8,#3b82f6);">' + esc(initials(home)) + '</div>' +
        '          <span class="fw-bold text-dark small">' + esc(home) + '</span></div>' +
        '        <span class="badge rounded-3 px-2 py-1 text-primary fw-black fs-4 font-monospace" style="background-color:#dbeafe;">' + esc(sc[0]) + '</span>' +
        '      </div>' +
        '      <div class="d-flex align-items-center justify-content-between p-2 rounded-3">' +
        '        <div class="d-flex align-items-center gap-2"><div class="rounded-circle bg-light text-secondary border d-flex align-items-center justify-content-center fw-bold" style="width:32px;height:32px;font-size:11px;">' + esc(initials(away)) + '</div>' +
        '          <span class="fw-semibold text-secondary small">' + esc(away) + '</span></div>' +
        '        <span class="badge rounded-3 px-2 py-1 text-secondary fw-bold fs-4 font-monospace bg-light border">' + esc(sc[1]) + '</span>' +
        '      </div>' +
        '    </div>' +
        '    <div class="d-flex align-items-center justify-content-between px-3 py-2 border-top bg-light text-muted" style="font-size:11.5px;">' +
        '      <span class="d-flex align-items-center gap-1"><span class="material-symbols-outlined text-secondary fs-6">schedule</span> ' + esc(meta) + '</span>' +
        '    </div>' +
        '  </div>' +
        '</div>';
    }

    function fetchJson(action) {
      return fetch(baseUrl + '/frontend/daihoi/' + action, {
        headers: { Accept: 'application/json' }
      })
        .then(function (r) { return r.json(); })
        .then(function (res) {
          var d = res && res.data;
          if (d && d.data) d = d.data;
          return Array.isArray(d) ? d : [];
        })
        .catch(function () { return null; });
    }

    function refresh() {
      fetchJson('jsonLive').then(function (items) {
        if (!items) return;
        if (!items.length) {
          fetchJson('jsonRecent').then(function (recent) {
            if (recent && recent.length) grid.innerHTML = recent.map(buildCard).join('');
          });
          return;
        }
        grid.innerHTML = items.map(buildCard).join('');
      });
    }

    setInterval(refresh, 30000);
  })();
})();
