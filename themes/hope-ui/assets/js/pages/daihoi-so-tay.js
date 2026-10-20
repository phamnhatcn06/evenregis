/**
 * Sổ tay Đại hội - Scrollspy cho mục lục bên trái + hiệu ứng hover liên kết.
 * Highlight mục đang xem khi cuộn trang và cuộn mượt khi bấm mục lục.
 */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var toc = document.getElementById('handbookToc');
    if (!toc) {
      return;
    }

    var links = Array.prototype.slice.call(toc.querySelectorAll('a[href^="#"]'));
    var sections = links
      .map(function (link) {
        var id = link.getAttribute('href').slice(1);
        var el = document.getElementById(id);
        return el ? { link: link, el: el } : null;
      })
      .filter(Boolean);

    if (!sections.length) {
      return;
    }

    function setActive(link) {
      links.forEach(function (l) {
        l.classList.remove('active');
      });
      if (link) {
        link.classList.add('active');
      }
    }

    // Cuộn mượt khi bấm mục lục (bù trừ header dính).
    links.forEach(function (link) {
      link.addEventListener('click', function (e) {
        var id = link.getAttribute('href').slice(1);
        var target = document.getElementById(id);
        if (!target) {
          return;
        }
        e.preventDefault();
        var top = target.getBoundingClientRect().top + window.pageYOffset - 90;
        window.scrollTo({ top: top, behavior: 'smooth' });
        setActive(link);
      });
    });

    // Scrollspy: xác định section gần đỉnh màn hình nhất.
    function onScroll() {
      var current = sections[0];
      sections.forEach(function (s) {
        if (s.el.getBoundingClientRect().top - 110 <= 0) {
          current = s;
        }
      });
      setActive(current.link);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  });
})();
