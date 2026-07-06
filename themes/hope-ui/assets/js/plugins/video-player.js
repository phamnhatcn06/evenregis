/**
 * Video Player Helper using Plyr.js
 * Wrapper để khởi tạo Plyr player với config chuẩn
 */

var VideoPlayer = {
    instances: [],
    defaultConfig: {
        controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'settings', 'pip', 'fullscreen'],
        settings: ['quality', 'speed'],
        speed: { selected: 1, options: [0.5, 0.75, 1, 1.25, 1.5, 2] },
        keyboard: { focused: true, global: false },
        tooltips: { controls: true, seek: true },
        loadSprite: false,
        storage: { enabled: true, key: 'plyr' },
        i18n: {
            restart: 'Phát lại',
            play: 'Phát',
            pause: 'Tạm dừng',
            fastForward: 'Tua tới {seektime}s',
            rewind: 'Tua lùi {seektime}s',
            seek: 'Tìm kiếm',
            seekLabel: '{currentTime} / {duration}',
            played: 'Đã phát',
            buffered: 'Đã tải',
            currentTime: 'Thời gian hiện tại',
            duration: 'Thời lượng',
            volume: 'Âm lượng',
            mute: 'Tắt tiếng',
            unmute: 'Bật tiếng',
            enterFullscreen: 'Toàn màn hình',
            exitFullscreen: 'Thoát toàn màn hình',
            settings: 'Cài đặt',
            speed: 'Tốc độ',
            normal: 'Bình thường',
            quality: 'Chất lượng',
            pip: 'Ảnh trong ảnh'
        }
    },

    /**
     * Khởi tạo player cho một element
     * @param {string|Element} selector - CSS selector hoặc element
     * @param {object} options - Config bổ sung
     * @returns {Plyr|null}
     */
    init: function(selector, options) {
        if (typeof Plyr === 'undefined') {
            console.error('Plyr.js chưa được tải');
            return null;
        }

        var element = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (!element) return null;

        var config = Object.assign({}, this.defaultConfig, options || {});
        var player = new Plyr(element, config);
        this.instances.push(player);
        return player;
    },

    /**
     * Khởi tạo tất cả video trên trang
     * @param {string} selector - CSS selector cho các video
     * @param {object} options - Config bổ sung
     * @returns {Array<Plyr>}
     */
    initAll: function(selector, options) {
        var self = this;
        var elements = document.querySelectorAll(selector || 'video.plyr-video, .plyr-video video');
        var players = [];
        elements.forEach(function(el) {
            var player = self.init(el, options);
            if (player) players.push(player);
        });
        return players;
    },

    /**
     * Tạo video player trong container
     * @param {string|Element} container - Container element hoặc selector
     * @param {string} src - Video URL
     * @param {object} options - Config bổ sung
     * @returns {Plyr|null}
     */
    create: function(container, src, options) {
        var containerEl = typeof container === 'string' ? document.querySelector(container) : container;
        if (!containerEl) return null;

        var video = document.createElement('video');
        video.className = 'plyr-video';
        video.setAttribute('playsinline', '');
        video.setAttribute('controls', '');

        if (src) {
            var source = document.createElement('source');
            source.src = src;
            source.type = this.getVideoType(src);
            video.appendChild(source);
        }

        containerEl.innerHTML = '';
        containerEl.appendChild(video);

        return this.init(video, options);
    },

    /**
     * Xác định MIME type từ URL
     */
    getVideoType: function(url) {
        if (!url) return 'video/mp4';
        var ext = url.split('.').pop().toLowerCase().split('?')[0];
        var types = {
            'mp4': 'video/mp4',
            'webm': 'video/webm',
            'ogg': 'video/ogg',
            'mov': 'video/quicktime',
            'm3u8': 'application/x-mpegURL'
        };
        return types[ext] || 'video/mp4';
    },

    /**
     * Dừng tất cả player
     */
    pauseAll: function() {
        this.instances.forEach(function(player) {
            if (player && typeof player.pause === 'function') {
                player.pause();
            }
        });
    },

    /**
     * Hủy tất cả player
     */
    destroyAll: function() {
        this.instances.forEach(function(player) {
            if (player && typeof player.destroy === 'function') {
                player.destroy();
            }
        });
        this.instances = [];
    },

    /**
     * Hủy player cụ thể
     */
    destroy: function(player) {
        if (player && typeof player.destroy === 'function') {
            player.destroy();
            var idx = this.instances.indexOf(player);
            if (idx > -1) this.instances.splice(idx, 1);
        }
    }
};

window.VideoPlayer = VideoPlayer;
