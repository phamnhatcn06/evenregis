/**
 * Trang Lịch trình Đại hội — làm nổi bật ngày đang xem trên thanh điều hướng
 * và cuộn mượt khi bấm vào từng ngày.
 */
(function () {
  'use strict';

  function init() {
    var navItems = Array.prototype.slice.call(document.querySelectorAll('.ag-daynav-item'));
    var days = Array.prototype.slice.call(document.querySelectorAll('.ag-day'));
    if (!navItems.length || !days.length) {
      return;
    }

    // Cuộn mượt đến khối ngày tương ứng.
    navItems.forEach(function (item) {
      item.addEventListener('click', function (e) {
        var id = item.getAttribute('href');
        if (id && id.charAt(0) === '#') {
          var target = document.querySelector(id);
          if (target) {
            e.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
          }
        }
      });
    });

    // Đánh dấu ngày đang hiển thị trong viewport.
    function setActive(id) {
      navItems.forEach(function (item) {
        var on = item.getAttribute('href') === '#' + id;
        item.classList.toggle('is-active', on);
      });
    }

    // Đổ bóng / nền cho thanh ngày khi nó bắt đầu "dính" trên cùng.
    var wrap = document.querySelector('.ag-daynav-wrap');
    var sentinel = document.querySelector('.ag-daynav-sentinel');
    if (wrap && sentinel && 'IntersectionObserver' in window) {
      var stuckObserver = new IntersectionObserver(function (entries) {
        wrap.classList.toggle('is-stuck', !entries[0].isIntersecting);
      }, { threshold: 0 });
      stuckObserver.observe(sentinel);
    }

    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            setActive(entry.target.id);
          }
        });
      }, { rootMargin: '-45% 0px -50% 0px', threshold: 0 });
      days.forEach(function (d) { observer.observe(d); });
    }

    setActive(days[0].id);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
