/**
 * Daihoi public website - countdown + auto refresh khối realtime.
 * Vanilla JS, không phụ thuộc thư viện ngoài.
 */
(function () {
  "use strict";

  var root = document.getElementById("daihoi-root");
  if (!root) return;

  var baseUrl = root.getAttribute("data-base-url") || "";
  var targetIso = root.getAttribute("data-countdown-target");

  // ---- Countdown ----
  function pad(n) {
    return String(n).padStart(2, "0");
  }

  function setText(id, value) {
    var el = document.getElementById(id);
    if (el) el.textContent = value;
  }

  function updateCountdown(target) {
    var distance = Math.max(0, target - Date.now());
    var days = Math.floor(distance / 86400000);
    var hours = Math.floor((distance / 3600000) % 24);
    var minutes = Math.floor((distance / 60000) % 60);
    var seconds = Math.floor((distance / 1000) % 60);
    setText("days", pad(days));
    setText("hours", pad(hours));
    setText("minutes", pad(minutes));
    setText("seconds", pad(seconds));
  }

  if (targetIso) {
    var target = new Date(targetIso).getTime();
    if (!isNaN(target)) {
      updateCountdown(target);
      setInterval(function () {
        updateCountdown(target);
      }, 1000);
    }
  }

  // ---- Helpers render ----
  function statusBadge(status) {
    var map = {
      live: '<span class="status live">LIVE</span>',
      done: '<span class="status done">Kết thúc</span>',
      upcoming: '<span class="status upcoming">Sắp đấu</span>'
    };
    return map[status] || "";
  }

  function esc(s) {
    var div = document.createElement("div");
    div.textContent = s == null ? "" : String(s);
    return div.innerHTML;
  }

  function renderMatch(m) {
    var score = m.score != null ? m.score : (m.time || "");
    return (
      '<div class="match-row">' +
      '<span class="team">' + esc(m.home_name || m.home || "") + "</span>" +
      '<span class="score">' + esc(score) + "</span>" +
      '<span class="team right">' + esc(m.away_name || m.away || "") + "</span>" +
      statusBadge(m.status) +
      "</div>"
    );
  }

  function fetchJson(action) {
    return fetch(baseUrl + "/frontend/daihoi/" + action, {
      headers: { Accept: "application/json" }
    })
      .then(function (r) {
        return r.json();
      })
      .then(function (res) {
        var d = res && res.data;
        if (d && d.data) d = d.data;
        return Array.isArray(d) ? d : [];
      })
      .catch(function () {
        return null;
      });
  }

  function refreshLive() {
    var container = document.getElementById("live-match-list");
    if (!container) return;
    fetchJson("jsonLive").then(function (items) {
      if (items && items.length) {
        container.innerHTML = items.map(renderMatch).join("");
      }
    });
  }

  function refreshRankings() {
    var container = document.getElementById("rank-list");
    if (!container) return;
    fetchJson("jsonRankings?limit=5").then(function (items) {
      if (items && items.length) {
        container.innerHTML = items
          .map(function (r, i) {
            return (
              '<div class="rank-row">' +
              '<span class="rank-index">' + (i + 1) + "</span>" +
              '<span class="rank-name">' + esc(r.name || r.org_name || "") + "</span>" +
              '<span class="rank-points">' + esc((r.points != null ? r.points : 0)) + " điểm</span>" +
              "</div>"
            );
          })
          .join("");
      }
    });
  }

  // Làm mới các khối realtime mỗi 30 giây
  refreshLive();
  refreshRankings();
  setInterval(refreshLive, 30000);
  setInterval(refreshRankings, 60000);
})();
