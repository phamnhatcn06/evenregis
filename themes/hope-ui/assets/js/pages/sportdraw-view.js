/**
 * SportDraw View - Modern Interactive Tournament Bracket & Schedule Engine
 * Handles:
 * 1. Dynamic SVG connector lines, tree routing, path tracing, zoom/pan controls, fullscreen.
 * 2. Quick Result recording modal & inline updates.
 * 3. Match start time & venue scheduling modal.
 * 4. Drag & drop match reordering with backend persistence.
 * 5. Chronological sorting by match start time.
 * 6. Dynamic group standings recalculation in real-time.
 */

(function () {
    'use strict';

    var currentZoom = 1;
    var connectorMode = 'step'; // 'step' (orthogonal) or 'curve' (bezier)
    var selectedTeamId = '';

    // Cache elements
    var rootEl = document.getElementById('sport-draw-view-root');
    var viewport = document.getElementById('bracket-viewport');
    var treeWrapper = document.getElementById('bracket-tree-wrapper');
    var svgCanvas = document.getElementById('bracket-svg-canvas');
    var teamFilterSelect = document.getElementById('bracket-team-filter');
    var zoomLevelDisplay = document.getElementById('bracket-zoom-level');

    // URLs and metadata from root
    var stageId = rootEl ? rootEl.getAttribute('data-stage-id') : '';
    var eventId = rootEl ? rootEl.getAttribute('data-event-id') : '';
    var sportId = rootEl ? rootEl.getAttribute('data-sport-id') : '';
    var saveResultUrl = rootEl ? rootEl.getAttribute('data-save-result-url') : '';
    var updateMatchUrl = rootEl ? rootEl.getAttribute('data-update-match-url') : '';
    var deleteMatchUrl = rootEl ? rootEl.getAttribute('data-delete-match-url') : '';
    var reorderMatchesUrl = rootEl ? rootEl.getAttribute('data-reorder-matches-url') : '';
    var standingsUrl = rootEl ? rootEl.getAttribute('data-standings-url') : '';

    // Modal elements
    var modalEl = document.getElementById('modal-match-editor');
    var modalBs = null;
    if (modalEl && window.bootstrap && window.bootstrap.Modal) {
        modalBs = new window.bootstrap.Modal(modalEl);
    }

    /**
     * Helper to show toast message
     */
    function showToast(msg, type) {
        type = type || 'success';
        if (window.Toast && typeof window.Toast[type] === 'function') {
            window.Toast[type](msg);
        } else if (window.Toast && typeof window.Toast.show === 'function') {
            window.Toast.show(msg, type);
        } else {
            alert(msg);
        }
    }

    /* ==========================================================================
       1. SVG CONNECTORS & TREE ROUTING
       ========================================================================== */
    function renderConnectors() {
        if (!svgCanvas || !treeWrapper) return;

        var zoom = currentZoom || 1;
        var w = treeWrapper.scrollWidth;
        var h = treeWrapper.scrollHeight;
        if (w > 0 && h > 0) {
            svgCanvas.setAttribute('width', w);
            svgCanvas.setAttribute('height', h);
            svgCanvas.setAttribute('viewBox', '0 0 ' + w + ' ' + h);
        }

        while (svgCanvas.firstChild) {
            svgCanvas.removeChild(svgCanvas.firstChild);
        }

        var wrapperRect = treeWrapper.getBoundingClientRect();

        // Match cards with target
        var matchCards = treeWrapper.querySelectorAll('.bracket-match-card[data-target-id]');
        matchCards.forEach(function (card) {
            var targetId = card.getAttribute('data-target-id');
            if (!targetId) return;

            var targetCard = treeWrapper.querySelector('.bracket-match-card[data-match-id="' + targetId + '"]');
            if (!targetCard) return;

            var outAnchor = card.querySelector('.bracket-anchor-out');
            var inAnchor = targetCard.querySelector('.bracket-anchor-in');
            if (!outAnchor || !inAnchor) return;

            var outRect = outAnchor.getBoundingClientRect();
            var inRect = inAnchor.getBoundingClientRect();

            var x1 = (outRect.left - wrapperRect.left) / zoom;
            var y1 = (outRect.top - wrapperRect.top) / zoom;
            var x2 = (inRect.left - wrapperRect.left) / zoom;
            var y2 = (inRect.top - wrapperRect.top) / zoom;

            var isWinnerFeeder = card.getAttribute('data-has-winner') === '1';
            var matchId = card.getAttribute('data-match-id');

            createPath(x1, y1, x2, y2, {
                fromMatch: matchId,
                toMatch: targetId,
                isWinner: isWinnerFeeder,
                isGold: false
            });
        });

        // Final match connecting to Champion Showcase
        var finalCard = treeWrapper.querySelector('.bracket-match-card[data-is-final="1"]');
        var championCard = document.getElementById('bracket-champion-card');
        if (finalCard && championCard) {
            var fOut = finalCard.querySelector('.bracket-anchor-out');
            var cIn = championCard.querySelector('.bracket-anchor-in');
            if (fOut && cIn) {
                var fRect = fOut.getBoundingClientRect();
                var cRect = cIn.getBoundingClientRect();

                var fx1 = (fRect.left - wrapperRect.left) / zoom;
                var fy1 = (fRect.top - wrapperRect.top) / zoom;
                var cx2 = (cRect.left - wrapperRect.left) / zoom;
                var cy2 = (cRect.top - wrapperRect.top) / zoom;

                var isFinalDecided = finalCard.getAttribute('data-has-winner') === '1';
                var finalId = finalCard.getAttribute('data-match-id');

                createPath(fx1, fy1, cx2, cy2, {
                    fromMatch: finalId,
                    toMatch: 'champion',
                    isWinner: isFinalDecided,
                    isGold: isFinalDecided
                });
            }
        }
    }

    function createPath(x1, y1, x2, y2, meta) {
        var path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
        var d = '';

        if (connectorMode === 'curve') {
            var dx = (x2 - x1) * 0.5;
            d = 'M ' + x1 + ' ' + y1 +
                ' C ' + (x1 + dx) + ' ' + y1 +
                ', ' + (x2 - dx) + ' ' + y2 +
                ', ' + x2 + ' ' + y2;
        } else {
            var midX = x1 + (x2 - x1) * 0.5;
            var r = Math.min(12, Math.abs(x2 - x1) / 4, Math.abs(y2 - y1) / 2);
            if (Math.abs(y2 - y1) < 4 || r < 3) {
                d = 'M ' + x1 + ' ' + y1 + ' H ' + x2;
            } else if (y2 > y1) {
                d = 'M ' + x1 + ' ' + y1 +
                    ' H ' + (midX - r) +
                    ' Q ' + midX + ' ' + y1 + ' ' + midX + ' ' + (y1 + r) +
                    ' V ' + (y2 - r) +
                    ' Q ' + midX + ' ' + y2 + ' ' + (midX + r) + ' ' + y2 +
                    ' H ' + x2;
            } else {
                d = 'M ' + x1 + ' ' + y1 +
                    ' H ' + (midX - r) +
                    ' Q ' + midX + ' ' + y1 + ' ' + midX + ' ' + (y1 - r) +
                    ' V ' + (y2 + r) +
                    ' Q ' + midX + ' ' + y2 + ' ' + (midX + r) + ' ' + y2 +
                    ' H ' + x2;
            }
        }

        path.setAttribute('d', d);
        path.setAttribute('class', 'bracket-connector-path' +
            (meta.isWinner ? ' path-winner' : '') +
            (meta.isGold ? ' path-gold' : ''));
        path.setAttribute('data-from-match', meta.fromMatch);
        path.setAttribute('data-to-match', meta.toMatch);

        svgCanvas.appendChild(path);
    }

    function highlightMatchPath(matchId, active) {
        if (!svgCanvas || !treeWrapper || !matchId) return;

        var connectedPaths = svgCanvas.querySelectorAll(
            'path[data-from-match="' + matchId + '"], path[data-to-match="' + matchId + '"]'
        );
        connectedPaths.forEach(function (p) {
            p.classList.toggle('path-highlighted', active);
        });

        var currentCard = treeWrapper.querySelector('.bracket-match-card[data-match-id="' + matchId + '"]');
        if (currentCard) {
            currentCard.classList.toggle('card-highlighted', active);
        }
    }

    function applyTeamFilter(teamId) {
        if (!treeWrapper || !svgCanvas) return;
        selectedTeamId = teamId;
        var allCards = treeWrapper.querySelectorAll('.bracket-match-card');
        var allPaths = svgCanvas.querySelectorAll('.bracket-connector-path');

        if (!teamId) {
            allCards.forEach(function (c) {
                c.classList.remove('card-highlighted', 'card-faded');
            });
            allPaths.forEach(function (p) {
                p.classList.remove('path-highlighted');
            });
            return;
        }

        allCards.forEach(function (card) {
            var teamA = card.getAttribute('data-team-a-id');
            var teamB = card.getAttribute('data-team-b-id');
            var matchId = card.getAttribute('data-match-id');

            if (teamA === teamId || teamB === teamId) {
                card.classList.add('card-highlighted');
                card.classList.remove('card-faded');

                var winnerId = card.getAttribute('data-winner-id');
                if (winnerId === teamId) {
                    var outPaths = svgCanvas.querySelectorAll('path[data-from-match="' + matchId + '"]');
                    outPaths.forEach(function (p) { p.classList.add('path-highlighted'); });
                }
            } else {
                card.classList.remove('card-highlighted');
                card.classList.add('card-faded');
            }
        });
    }

    function setZoom(val) {
        if (!treeWrapper) return;
        currentZoom = Math.min(1.8, Math.max(0.45, Math.round(val * 100) / 100));
        treeWrapper.style.transform = 'scale(' + currentZoom + ')';
        if (zoomLevelDisplay) {
            zoomLevelDisplay.textContent = Math.round(currentZoom * 100) + '%';
        }
        window.requestAnimationFrame(renderConnectors);
    }

    // Viewport mouse dragging / panning
    if (viewport) {
        var isPanning = false;
        var startX, startY, scrollLeft, scrollTop;

        viewport.addEventListener('mousedown', function (e) {
            if (e.target.closest('a') || e.target.closest('button') || e.target.closest('select') || e.target.closest('input')) {
                return;
            }
            isPanning = true;
            viewport.classList.add('is-dragging');
            startX = e.pageX - viewport.offsetLeft;
            startY = e.pageY - viewport.offsetTop;
            scrollLeft = viewport.scrollLeft;
            scrollTop = viewport.scrollTop;
        });

        document.addEventListener('mousemove', function (e) {
            if (!isPanning) return;
            e.preventDefault();
            var x = e.pageX - viewport.offsetLeft;
            var y = e.pageY - viewport.offsetTop;
            viewport.scrollLeft = scrollLeft - (x - startX) * 1.2;
            viewport.scrollTop = scrollTop - (y - startY) * 1.2;
        });

        document.addEventListener('mouseup', function () {
            if (isPanning) {
                isPanning = false;
                viewport.classList.remove('is-dragging');
            }
        });
    }

    if (treeWrapper) {
        treeWrapper.addEventListener('mouseover', function (e) {
            var card = e.target.closest('.bracket-match-card');
            if (card) {
                highlightMatchPath(card.getAttribute('data-match-id'), true);
            }
        });

        treeWrapper.addEventListener('mouseout', function (e) {
            var card = e.target.closest('.bracket-match-card');
            if (card) {
                highlightMatchPath(card.getAttribute('data-match-id'), false);
                if (selectedTeamId) applyTeamFilter(selectedTeamId);
            }
        });
    }

    /* ==========================================================================
       2. MODAL GHI KẾT QUẢ & LỊCH THI ĐẤU
       ========================================================================== */
    var modalMatchIdInput = document.getElementById('modal-field-match-id');
    var modalScoreAInput = document.getElementById('modal-field-score-a');
    var modalScoreBInput = document.getElementById('modal-field-score-b');
    var modalWinnerSelect = document.getElementById('modal-field-winner');
    var modalIsDrawCheck = document.getElementById('modal-field-is-draw');
    var modalMatchTimeInput = document.getElementById('modal-field-match-time');
    var modalLocationInput = document.getElementById('modal-field-location');
    var modalMatchOrderInput = document.getElementById('modal-field-match-order');
    var modalTeamANameEl = document.getElementById('modal-team-a-name');
    var modalTeamBNameEl = document.getElementById('modal-team-b-name');
    var modalMatchTitleEl = document.getElementById('modal-match-title');
    var modalWinnerSection = document.getElementById('modal-winner-section');

    function openMatchEditor(matchData) {
        if (!modalEl) return;

        modalMatchIdInput.value = matchData.id || '';
        modalScoreAInput.value = (matchData.scoreA !== null && matchData.scoreA !== undefined) ? matchData.scoreA : '';
        modalScoreBInput.value = (matchData.scoreB !== null && matchData.scoreB !== undefined) ? matchData.scoreB : '';
        modalMatchOrderInput.value = matchData.order || '';
        modalLocationInput.value = matchData.location || '';

        // Match time format YYYY-MM-DDTHH:mm
        if (matchData.matchTime) {
            var formatted = matchData.matchTime.replace(' ', 'T').substring(0, 16);
            modalMatchTimeInput.value = formatted;
        } else {
            modalMatchTimeInput.value = '';
        }

        modalTeamANameEl.textContent = matchData.teamAName || 'Đội A';
        modalTeamBNameEl.textContent = matchData.teamBName || 'Đội B';
        modalMatchTitleEl.textContent = 'Trận #' + (matchData.code || matchData.id) + ' (' + (matchData.round || '') + ')';

        // Setup winner select
        if (modalWinnerSelect) {
            modalWinnerSelect.innerHTML = '<option value="">-- Tự động theo tỉ số --</option>';
            if (matchData.teamAId && matchData.teamAName) {
                var optA = document.createElement('option');
                optA.value = matchData.teamAId;
                optA.textContent = 'Thắng: ' + matchData.teamAName;
                if (matchData.winnerId == matchData.teamAId) optA.selected = true;
                modalWinnerSelect.appendChild(optA);
            }
            if (matchData.teamBId && matchData.teamBName) {
                var optB = document.createElement('option');
                optB.value = matchData.teamBId;
                optB.textContent = 'Thắng: ' + matchData.teamBName;
                if (matchData.winnerId == matchData.teamBId) optB.selected = true;
                modalWinnerSelect.appendChild(optB);
            }
        }

        if (modalIsDrawCheck) {
            modalIsDrawCheck.checked = (matchData.isDraw === '1' || matchData.isDraw === true);
        }

        var isKnockout = (matchData.matchType === 'knockout' || matchData.matchType === 'final' || matchData.matchType === 'playoff');
        if (modalWinnerSection) {
            modalWinnerSection.style.display = isKnockout ? 'block' : 'none';
        }

        if (modalBs) {
            modalBs.show();
        } else if (window.$ && $('#modal-match-editor').modal) {
            $('#modal-match-editor').modal('show');
        }
    }

    // Bind click to open editor from cards and list rows
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-quick-result, .match-time-btn, .btn-edit-match');
        if (!btn) return;

        e.preventDefault();
        var matchItem = btn.closest('[data-match-id]');
        if (!matchItem) return;

        var mId = matchItem.getAttribute('data-match-id') || '';
        var matchType = matchItem.getAttribute('data-match-type') || 'group';
        var tAId = matchItem.getAttribute('data-team-a-id') || '';
        var tBId = matchItem.getAttribute('data-team-b-id') || '';

        // Chặn nhập tỉ số nếu chưa có đội thật trong sơ đồ loại trực tiếp
        if (btn.classList.contains('btn-quick-result') && matchType !== 'group' && (!tAId || !tBId)) {
            showToast('Trận đấu này chưa xác định đủ 2 đội tham gia. Vui lòng ghi kết quả vòng bảng và cập nhật danh sách đội trước khi nhập tỉ số!', 'warning');
            return;
        }

        // Chặn chỉnh sửa trận ảo / placeholder
        if (mId.indexOf('ko_') === 0) {
            showToast('Sơ đồ nhánh đang ở chế độ xem trước khung thi đấu. Nhấn nút "Cập nhật danh sách đội từ BXH" để khởi tạo các trận đấu chính thức!', 'info');
            return;
        }

        var data = {
            id: mId,
            code: matchItem.getAttribute('data-match-code') || mId,
            round: matchItem.getAttribute('data-round-name') || '',
            matchType: matchType,
            teamAId: tAId,
            teamBId: tBId,
            teamAName: matchItem.getAttribute('data-team-a-name') || 'Đội A',
            teamBName: matchItem.getAttribute('data-team-b-name') || 'Đội B',
            scoreA: matchItem.getAttribute('data-score-a'),
            scoreB: matchItem.getAttribute('data-score-b'),
            winnerId: matchItem.getAttribute('data-winner-id') || '',
            isDraw: matchItem.getAttribute('data-is-draw') || '0',
            matchTime: matchItem.getAttribute('data-match-time') || '',
            location: matchItem.getAttribute('data-location') || '',
            order: matchItem.getAttribute('data-match-order') || ''
        };

        openMatchEditor(data);
    });

    // Save Result & Schedule form submit
    var formModalEditor = document.getElementById('form-modal-match-editor');
    if (formModalEditor) {
        formModalEditor.addEventListener('submit', function (e) {
            e.preventDefault();

            var matchId = modalMatchIdInput.value;
            var scoreA = modalScoreAInput.value.trim();
            var scoreB = modalScoreBInput.value.trim();
            var winnerId = modalWinnerSelect ? modalWinnerSelect.value : '';
            var isDraw = (modalIsDrawCheck && modalIsDrawCheck.checked) ? 1 : 0;
            var matchTime = modalMatchTimeInput.value;
            var location = modalLocationInput.value.trim();
            var matchOrder = modalMatchOrderInput.value.trim();

            if (!matchId) return;

            var btnSubmit = document.getElementById('btn-modal-save-all');
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu...';
            }

            var promises = [];

            // 1. Save Result if scores are entered
            if (scoreA !== '' && scoreB !== '') {
                var fdResult = new FormData();
                fdResult.append('match_id', matchId);
                fdResult.append('score_a', scoreA);
                fdResult.append('score_b', scoreB);
                if (winnerId) fdResult.append('winner_team_id', winnerId);
                fdResult.append('is_draw', isDraw);

                promises.push(
                    fetch(saveResultUrl, { method: 'POST', body: fdResult })
                        .then(function (r) { return r.json(); })
                );
            }

            // 2. Save Schedule (time, location, order)
            var fdSchedule = new FormData();
            fdSchedule.append('match_id', matchId);
            fdSchedule.append('event_id', eventId);
            fdSchedule.append('sport_id', sportId);
            if (matchTime) {
                fdSchedule.append('match_time', matchTime.replace('T', ' ') + ':00');
            } else {
                fdSchedule.append('match_time', '');
            }
            fdSchedule.append('location', location);
            if (matchOrder) fdSchedule.append('match_order', matchOrder);

            promises.push(
                fetch(updateMatchUrl, { method: 'POST', body: fdSchedule })
                    .then(function (r) { return r.json(); })
            );

            Promise.all(promises).then(function (results) {
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fa fa-save me-1"></i>Lưu thông tin';
                }

                var hasFail = results.some(function (res) { return !res || !res.success; });
                if (hasFail) {
                    var errMsg = results.find(function (res) { return res && !res.success; })?.message || 'Có lỗi khi lưu.';
                    showToast(errMsg, 'error');
                } else {
                    showToast('Đã cập nhật kết quả và lịch thi đấu thành công!', 'success');
                    if (modalBs) modalBs.hide();
                    else if (window.$) $('#modal-match-editor').modal('hide');

                    // Update DOM elements for this match
                    updateMatchDom(matchId, {
                        scoreA: scoreA,
                        scoreB: scoreB,
                        winnerId: winnerId,
                        matchTime: matchTime ? (matchTime.replace('T', ' ') + ':00') : '',
                        location: location,
                        order: matchOrder
                    });

                    // Recalculate group standings in real-time
                    recalculateStandings();

                    // Re-render SVG connectors
                    renderConnectors();
                }
            }).catch(function (err) {
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fa fa-save me-1"></i>Lưu thông tin';
                }
                showToast('Lỗi kết nối máy chủ: ' + err.message, 'error');
            });
        });
    }

    /**
     * Update match elements in DOM after save
     */
    function updateMatchDom(matchId, data) {
        var elements = document.querySelectorAll('[data-match-id="' + matchId + '"]');
        elements.forEach(function (el) {
            // Update attributes
            if (data.scoreA !== '') el.setAttribute('data-score-a', data.scoreA);
            if (data.scoreB !== '') el.setAttribute('data-score-b', data.scoreB);
            if (data.winnerId) el.setAttribute('data-winner-id', data.winnerId);
            if (data.matchTime !== undefined) el.setAttribute('data-match-time', data.matchTime);
            if (data.location !== undefined) el.setAttribute('data-location', data.location);
            if (data.order) el.setAttribute('data-match-order', data.order);

            // Update scores in bracket card
            var scoreBoxes = el.querySelectorAll('.team-score-box');
            if (scoreBoxes.length >= 2 && data.scoreA !== '' && data.scoreB !== '') {
                scoreBoxes[0].textContent = data.scoreA;
                scoreBoxes[1].textContent = data.scoreB;
                el.classList.add('status-completed');
                el.setAttribute('data-has-winner', data.winnerId ? '1' : '0');

                var statusBadge = el.querySelector('.match-status-badge');
                if (statusBadge) {
                    statusBadge.className = 'match-status-badge bg-success text-white';
                    statusBadge.innerHTML = '<i class="fa fa-check me-1"></i>Đã xong';
                }
            }

            // Update scores in list row
            var scoreBadge = el.querySelector('.match-score-pill');
            if (scoreBadge && data.scoreA !== '' && data.scoreB !== '') {
                scoreBadge.innerHTML = '<strong>' + data.scoreA + ' - ' + data.scoreB + '</strong>';
                scoreBadge.classList.remove('bg-light', 'text-muted');
                scoreBadge.classList.add('bg-primary', 'text-white');
            }

            // Update time button
            var timeBtn = el.querySelector('.match-time-btn');
            if (timeBtn) {
                if (data.matchTime) {
                    timeBtn.classList.add('has-time');
                    var dateObj = new Date(data.matchTime.replace(' ', 'T'));
                    var timeStr = !isNaN(dateObj.getTime())
                        ? (('0' + dateObj.getHours()).slice(-2) + ':' + ('0' + dateObj.getMinutes()).slice(-2) + ' ' +
                           ('0' + dateObj.getDate()).slice(-2) + '/' + ('0' + (dateObj.getMonth() + 1)).slice(-2) + '/' + dateObj.getFullYear())
                        : data.matchTime;
                    timeBtn.innerHTML = '<i class="fa fa-clock-o me-1"></i>' + timeStr;
                } else {
                    timeBtn.classList.remove('has-time');
                    timeBtn.innerHTML = '<i class="fa fa-calendar-plus-o me-1"></i>Đặt giờ & sân';
                }
            }

            // Update location badge
            var locBadge = el.querySelector('.location-badge');
            if (locBadge) {
                if (data.location) {
                    locBadge.innerHTML = '<i class="fa fa-map-marker text-danger me-1"></i>' + data.location;
                    locBadge.style.display = 'inline-block';
                } else {
                    locBadge.style.display = 'none';
                }
            }
        });
    }

    /* ==========================================================================
       3. TÍNH BẢNG XẾP HẠNG THỜI GIAN THỰC (DYNAMIC GROUP STANDINGS)
       ========================================================================== */
    function recalculateStandings() {
        var groupTables = document.querySelectorAll('.group-standings-table');
        if (!groupTables.length) return;

        groupTables.forEach(function (table) {
            var groupLabel = table.getAttribute('data-group-label');
            if (!groupLabel) return;

            var matchRows = document.querySelectorAll('.sortable-match-item[data-group-label="' + groupLabel + '"]');
            var teamRows = table.querySelectorAll('tbody tr[data-team-id]');
            var stats = {};

            teamRows.forEach(function (tr) {
                var tid = tr.getAttribute('data-team-id');
                stats[tid] = {
                    teamId: tid,
                    tr: tr,
                    played: 0,
                    won: 0,
                    drawn: 0,
                    lost: 0,
                    gf: 0,
                    ga: 0,
                    gd: 0,
                    points: 0
                };
            });

            matchRows.forEach(function (m) {
                var sA = m.getAttribute('data-score-a');
                var sB = m.getAttribute('data-score-b');
                var tA = m.getAttribute('data-team-a-id');
                var tB = m.getAttribute('data-team-b-id');

                if (sA !== null && sB !== null && sA !== '' && sB !== '' && stats[tA] && stats[tB]) {
                    var numA = parseInt(sA, 10);
                    var numB = parseInt(sB, 10);

                    stats[tA].played++;
                    stats[tB].played++;
                    stats[tA].gf += numA;
                    stats[tA].ga += numB;
                    stats[tB].gf += numB;
                    stats[tB].ga += numA;

                    if (numA > numB) {
                        stats[tA].won++;
                        stats[tA].points += 3;
                        stats[tB].lost++;
                    } else if (numB > numA) {
                        stats[tB].won++;
                        stats[tB].points += 3;
                        stats[tA].lost++;
                    } else {
                        stats[tA].drawn++;
                        stats[tA].points += 1;
                        stats[tB].drawn++;
                        stats[tB].points += 1;
                    }
                }
            });

            // Calculate GD and sort
            var sortedTeams = Object.values(stats);
            sortedTeams.forEach(function (s) { s.gd = s.gf - s.ga; });

            sortedTeams.sort(function (a, b) {
                if (b.points !== a.points) return b.points - a.points;
                if (b.gd !== a.gd) return b.gd - a.gd;
                if (b.gf !== a.gf) return b.gf - a.gf;
                return 0;
            });

            // Re-order tbody and update text
            var tbody = table.querySelector('tbody');
            var isMedalTable = table.getAttribute('data-is-medal-table') === '1';
            var advanceCount = parseInt(table.getAttribute('data-advance-count') || '2', 10);

            sortedTeams.forEach(function (s, index) {
                var rank = index + 1;
                var tr = s.tr;
                var rankBadge = tr.querySelector('.rank-badge');
                if (rankBadge) {
                    rankBadge.textContent = rank;
                    if (s.played > 0 && isMedalTable) {
                        rankBadge.className = 'rank-badge ' + (rank === 1 ? 'rank-gold' : (rank === 2 ? 'rank-silver' : (rank === 3 ? 'rank-bronze' : 'bg-light text-muted')));
                    } else {
                        rankBadge.className = 'rank-badge bg-light text-muted';
                    }
                }

                // Manage qualification highlight & badge
                var qualifyBadge = tr.querySelector('.standings-qualify-badge');
                if (qualifyBadge) {
                    qualifyBadge.style.display = (s.played > 0 && rank <= advanceCount && !isMedalTable) ? 'inline-block' : 'none';
                }
                if (s.played > 0 && rank <= advanceCount && !isMedalTable) {
                    tr.classList.add('standings-qualify-row');
                } else {
                    tr.classList.remove('standings-qualify-row');
                }

                var tdP = tr.querySelector('.col-played'); if (tdP) tdP.textContent = s.played;
                var tdW = tr.querySelector('.col-won'); if (tdW) tdW.textContent = s.won;
                var tdD = tr.querySelector('.col-drawn'); if (tdD) tdD.textContent = s.drawn;
                var tdL = tr.querySelector('.col-lost'); if (tdL) tdL.textContent = s.lost;
                var tdDiff = tr.querySelector('.col-diff'); if (tdDiff) tdDiff.textContent = s.gf + ':' + s.ga;
                var tdPts = tr.querySelector('.col-pts'); if (tdPts) tdPts.textContent = s.points;

                tbody.appendChild(tr);
            });
        });
    }

    // Nút "Tính lại BXH" từ backend
    var btnRecompute = document.getElementById('btn-recompute-standings');
    if (btnRecompute) {
        btnRecompute.addEventListener('click', function () {
            btnRecompute.disabled = true;
            btnRecompute.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang tính BXH...';

            fetch(standingsUrl, { method: 'POST' })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    btnRecompute.disabled = false;
                    btnRecompute.innerHTML = '<i class="fa fa-refresh me-1"></i>Tính lại BXH';
                    if (res && res.success) {
                        showToast('Đã tính lại bảng xếp hạng thành công!', 'success');
                        setTimeout(function () { window.location.reload(); }, 600);
                    } else {
                        showToast(res?.message || 'Không thể tính BXH.', 'error');
                    }
                })
                .catch(function (err) {
                    btnRecompute.disabled = false;
                    btnRecompute.innerHTML = '<i class="fa fa-refresh me-1"></i>Tính lại BXH';
                    showToast('Lỗi: ' + err.message, 'error');
                });
        });
    }

    // Cập nhật danh sách đội từ BXH các bảng vào Sơ đồ nhánh loại trực tiếp
    var generateKnockoutUrl = rootEl ? rootEl.getAttribute('data-generate-knockout-url') : '';
    function handleSyncBracket(btn) {
        if (!generateKnockoutUrl) return;

        var completedGroupMatches = parseInt(rootEl ? rootEl.getAttribute('data-completed-group-matches') || '0' : '0', 10);
        var totalGroupMatches = parseInt(rootEl ? rootEl.getAttribute('data-total-group-matches') || '0' : '0', 10);

        var confirmMsg = '';
        var force = 0;

        if (totalGroupMatches > 0 && completedGroupMatches === 0) {
            confirmMsg = 'Vòng bảng hiện chưa có trận đấu nào có kết quả (0/' + totalGroupMatches + ' trận).\nBạn có muốn cập nhật sơ đồ loại trực tiếp theo bảng xếp hạng hiện tại không?';
            force = 1;
        } else if (totalGroupMatches > 0 && completedGroupMatches < totalGroupMatches) {
            confirmMsg = 'Vòng bảng mới hoàn thành ' + completedGroupMatches + '/' + totalGroupMatches + ' trận.\nBạn có chắc muốn cập nhật sơ đồ nhánh theo bảng xếp hạng tạm thời hiện tại?';
            force = 1;
        } else {
            confirmMsg = 'Vòng bảng đã hoàn thành ' + completedGroupMatches + '/' + totalGroupMatches + ' trận.\nXác nhận cập nhật danh sách các đội nhất, nhì bảng vào sơ đồ loại trực tiếp?';
            force = 0;
        }

        if (!confirm(confirmMsg)) {
            return;
        }

        var origHtml = btn ? btn.innerHTML : '';
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang cập nhật...';
        }

        var fd = new FormData();
        fd.append('force', force);

        fetch(generateKnockoutUrl, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
                if (res && res.success) {
                    showToast(res.message || 'Đã cập nhật danh sách đội vào sơ đồ nhánh thành công!', 'success');
                    setTimeout(function () { window.location.reload(); }, 600);
                } else {
                    showToast(res ? (res.message || 'Lỗi cập nhật nhánh.') : 'Lỗi cập nhật.', 'error');
                }
            })
            .catch(function (err) {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                }
                showToast('Lỗi kết nối: ' + err.message, 'error');
            });
    }

    var btnSyncBracket = document.getElementById('btn-sync-bracket-teams');
    if (btnSyncBracket) {
        btnSyncBracket.addEventListener('click', function () {
            handleSyncBracket(btnSyncBracket);
        });
    }

    var btnPushToBracket = document.getElementById('btn-push-to-bracket');
    if (btnPushToBracket) {
        btnPushToBracket.addEventListener('click', function () {
            handleSyncBracket(btnPushToBracket);
        });
    }

    // Đặt chỗ trước đội thắng play-off vào các bảng (không xáo lại đội đã bốc)
    var reservePlayoffUrl = rootEl ? rootEl.getAttribute('data-reserve-playoff-url') : '';
    var btnReservePlayoff = document.getElementById('btn-reserve-playoff');
    if (btnReservePlayoff && reservePlayoffUrl) {
        btnReservePlayoff.addEventListener('click', function () {
            var sourceStageId = btnReservePlayoff.getAttribute('data-source-stage-id') || '';
            var confirmMsg = 'Thêm 1 slot "Thắng Play-off" vào mỗi bảng và sinh các trận vòng tròn liên quan?\n' +
                'Các đội đã bốc sẽ được giữ nguyên. Đội thắng tự điền vào slot khi play-off có kết quả.';

            var run = function () {
                var origHtml = btnReservePlayoff.innerHTML;
                btnReservePlayoff.disabled = true;
                btnReservePlayoff.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang đặt chỗ...';

                var fd = new FormData();
                if (sourceStageId) fd.append('source_stage_id', sourceStageId);

                fetch(reservePlayoffUrl, { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (res && res.success) {
                            showToast(res.message || 'Đã đặt chỗ play-off vào vòng bảng.', 'success');
                            setTimeout(function () { window.location.reload(); }, 600);
                        } else {
                            btnReservePlayoff.disabled = false;
                            btnReservePlayoff.innerHTML = origHtml;
                            showToast(res ? (res.message || 'Không thể đặt chỗ play-off.') : 'Lỗi.', 'error');
                        }
                    })
                    .catch(function (err) {
                        btnReservePlayoff.disabled = false;
                        btnReservePlayoff.innerHTML = origHtml;
                        showToast('Lỗi kết nối: ' + err.message, 'error');
                    });
            };

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    title: 'Đặt chỗ Play-off',
                    text: confirmMsg,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Đặt chỗ',
                    cancelButtonText: 'Hủy'
                }).then(function (result) { if (result.isConfirmed) run(); });
            } else if (confirm(confirmMsg)) {
                run();
            }
        });
    }

    // Lấy đội nhất bảng vòng loại vào vòng chung kết (tạo slot ảo + tự điền sau)
    var reserveGroupWinnersUrl = rootEl ? rootEl.getAttribute('data-reserve-group-winners-url') : '';
    var btnReserveGroupWinners = document.getElementById('btn-reserve-group-winners');
    if (btnReserveGroupWinners && reserveGroupWinnersUrl) {
        btnReserveGroupWinners.addEventListener('click', function () {
            var qualStageId = btnReserveGroupWinners.getAttribute('data-qual-source-stage-id') || '';
            var confirmMsg = 'Tạo slot giữ chỗ "Nhất bảng" cho mỗi bảng vòng loại và sinh lịch vòng tròn giữa chúng?\n' +
                'Đội thật sẽ tự điền vào khi bảng vòng loại tương ứng đá xong.';

            var run = function () {
                var origHtml = btnReserveGroupWinners.innerHTML;
                btnReserveGroupWinners.disabled = true;
                btnReserveGroupWinners.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang tạo slot...';

                var fd = new FormData();
                if (qualStageId) fd.append('qual_source_stage_id', qualStageId);

                fetch(reserveGroupWinnersUrl, { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        if (res && res.success) {
                            showToast(res.message || 'Đã tạo slot lấy đội nhất bảng vào vòng chung kết.', 'success');
                            setTimeout(function () { window.location.reload(); }, 600);
                        } else {
                            btnReserveGroupWinners.disabled = false;
                            btnReserveGroupWinners.innerHTML = origHtml;
                            showToast(res ? (res.message || 'Không thể tạo slot vòng chung kết.') : 'Lỗi.', 'error');
                        }
                    })
                    .catch(function (err) {
                        btnReserveGroupWinners.disabled = false;
                        btnReserveGroupWinners.innerHTML = origHtml;
                        showToast('Lỗi kết nối: ' + err.message, 'error');
                    });
            };

            if (window.Swal && typeof window.Swal.fire === 'function') {
                window.Swal.fire({
                    title: 'Lấy Nhất bảng vòng loại',
                    text: confirmMsg,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Tạo slot',
                    cancelButtonText: 'Hủy'
                }).then(function (result) { if (result.isConfirmed) run(); });
            } else if (confirm(confirmMsg)) {
                run();
            }
        });
    }

    /* ==========================================================================
       4. KÉO THẢ SẮP XẾP CÁC TRẬN ĐẤU (HTML5 DRAG & DROP)
       ========================================================================== */
    var dragItem = null;
    var matchLists = document.querySelectorAll('.sortable-matches-container');

    matchLists.forEach(function (container) {
        container.addEventListener('dragstart', function (e) {
            var item = e.target.closest('.sortable-match-item');
            if (!item) return;
            dragItem = item;
            item.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', item.getAttribute('data-match-id'));
        });

        container.addEventListener('dragover', function (e) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            var target = e.target.closest('.sortable-match-item');
            if (!target || target === dragItem) return;

            var rect = target.getBoundingClientRect();
            var mid = rect.top + rect.height / 2;
            if (e.clientY < mid) {
                container.insertBefore(dragItem, target);
            } else {
                container.insertBefore(dragItem, target.nextSibling);
            }
        });

        container.addEventListener('dragend', function () {
            if (dragItem) {
                dragItem.classList.remove('is-dragging');
                dragItem = null;
            }
            updateOrderBadges(container);
            showReorderSaveButton(true);
        });
    });

    function updateOrderBadges(container) {
        var items = container.querySelectorAll('.sortable-match-item');
        items.forEach(function (item, idx) {
            var order = idx + 1;
            item.setAttribute('data-match-order', order);
            var pill = item.querySelector('.order-num');
            if (pill) pill.textContent = order;
        });
    }

    var btnSaveOrder = document.getElementById('btn-save-match-order');
    function showReorderSaveButton(show) {
        if (!btnSaveOrder) return;
        btnSaveOrder.style.display = show ? 'inline-flex' : 'none';
        if (show) btnSaveOrder.classList.add('animate__animated', 'animate__pulse');
    }

    if (btnSaveOrder) {
        btnSaveOrder.addEventListener('click', function () {
            var allItems = document.querySelectorAll('.sortable-match-item');
            var orders = [];
            allItems.forEach(function (item, idx) {
                var mId = item.getAttribute('data-match-id');
                if (mId) {
                    orders.push({
                        match_id: parseInt(mId, 10),
                        match_order: idx + 1
                    });
                }
            });

            btnSaveOrder.disabled = true;
            btnSaveOrder.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu thứ tự...';

            var fd = new FormData();
            fd.append('event_id', eventId);
            fd.append('sport_id', sportId);
            fd.append('orders', JSON.stringify(orders));

            fetch(reorderMatchesUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    btnSaveOrder.disabled = false;
                    btnSaveOrder.innerHTML = '<i class="fa fa-check me-1"></i>Đã lưu thứ tự';
                    if (res && res.success) {
                        showToast('Đã lưu thứ tự sắp xếp các trận đấu thành công!', 'success');
                        setTimeout(function () { showReorderSaveButton(false); }, 1200);
                    } else {
                        showToast(res?.message || 'Lỗi lưu thứ tự.', 'error');
                    }
                })
                .catch(function (err) {
                    btnSaveOrder.disabled = false;
                    btnSaveOrder.innerHTML = '<i class="fa fa-save me-1"></i>Lưu thứ tự';
                    showToast('Lỗi kết nối: ' + err.message, 'error');
                });
        });
    }

    /* ==========================================================================
       5. SẮP XẾP CÁC TRẬN THEO THỨ TỰ THỜI GIAN
       ========================================================================== */
    var btnSortByTime = document.getElementById('btn-sort-by-time');
    if (btnSortByTime) {
        btnSortByTime.addEventListener('click', function () {
            var containers = document.querySelectorAll('.sortable-matches-container');
            var sortedCount = 0;

            containers.forEach(function (container) {
                var items = Array.from(container.querySelectorAll('.sortable-match-item'));
                if (items.length < 2) return;

                items.sort(function (a, b) {
                    var tA = a.getAttribute('data-match-time') || '';
                    var tB = b.getAttribute('data-match-time') || '';

                    // Matches with time come first in chronological order
                    if (tA && tB) {
                        return new Date(tA.replace(' ', 'T')) - new Date(tB.replace(' ', 'T'));
                    }
                    if (tA && !tB) return -1;
                    if (!tA && tB) return 1;

                    // Fallback to order
                    var ordA = parseInt(a.getAttribute('data-match-order') || '999', 10);
                    var ordB = parseInt(b.getAttribute('data-match-order') || '999', 10);
                    return ordA - ordB;
                });

                // Re-append in chronological order
                items.forEach(function (item) {
                    container.appendChild(item);
                });

                updateOrderBadges(container);
                sortedCount += items.length;
            });

            showToast('Đã sắp xếp các trận theo thứ tự thời gian tăng dần! Nhấn "Lưu thứ tự" để lưu lại.', 'info');
            showReorderSaveButton(true);
        });
    }

    /* ==========================================================================
       6. TOOLBAR LISTENERS (ZOOM, FULLSCREEN, PRINT)
       ========================================================================== */
    var btnZoomIn = document.getElementById('btn-bracket-zoomin');
    var btnZoomOut = document.getElementById('btn-bracket-zoomout');
    var btnZoomReset = document.getElementById('btn-bracket-zoomreset');
    var btnToggleLines = document.getElementById('btn-bracket-toggle-lines');
    var btnFullscreen = document.getElementById('btn-bracket-fullscreen');
    var btnCloseFullscreen = document.getElementById('btn-bracket-fullscreen-close');
    var btnPrint = document.getElementById('btn-bracket-print');

    if (btnZoomIn) btnZoomIn.addEventListener('click', function () { setZoom(currentZoom + 0.15); });
    if (btnZoomOut) btnZoomOut.addEventListener('click', function () { setZoom(currentZoom - 0.15); });
    if (btnZoomReset) btnZoomReset.addEventListener('click', function () { setZoom(1); });

    if (btnToggleLines) {
        btnToggleLines.addEventListener('click', function () {
            connectorMode = (connectorMode === 'step') ? 'curve' : 'step';
            var modeText = (connectorMode === 'step') ? 'Góc vuông' : 'Đường cong';
            btnToggleLines.querySelector('.mode-label').textContent = modeText;
            renderConnectors();
        });
    }

    if (teamFilterSelect) {
        teamFilterSelect.addEventListener('change', function () {
            applyTeamFilter(this.value);
        });
    }

    if (btnFullscreen && viewport) {
        btnFullscreen.addEventListener('click', function () {
            viewport.classList.toggle('is-fullscreen');
            document.body.style.overflow = viewport.classList.contains('is-fullscreen') ? 'hidden' : '';
            setTimeout(renderConnectors, 250);
        });
    }

    if (btnCloseFullscreen && viewport) {
        btnCloseFullscreen.addEventListener('click', function () {
            viewport.classList.remove('is-fullscreen');
            document.body.style.overflow = '';
            setTimeout(renderConnectors, 250);
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && viewport && viewport.classList.contains('is-fullscreen')) {
            viewport.classList.remove('is-fullscreen');
            document.body.style.overflow = '';
            setTimeout(renderConnectors, 250);
        }
    });

    if (btnPrint) {
        btnPrint.addEventListener('click', function () { window.print(); });
    }

    window.addEventListener('resize', function () {
        window.requestAnimationFrame(renderConnectors);
    });

    setTimeout(renderConnectors, 150);
    setTimeout(renderConnectors, 600);

    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(renderConnectors);
    }

})();
