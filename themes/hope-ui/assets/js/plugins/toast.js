/**
 * Toast Notification Helper
 * Sử dụng Bootstrap 5 Toast thay vì Alert
 * Auto close sau 5 giây
 */
var Toast = (function () {
    var containerId = 'toast-container';
    var defaultDuration = 5000;

    function getContainer() {
        var container = document.getElementById(containerId);
        if (!container) {
            container = document.createElement('div');
            container.id = containerId;
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1080';
            document.body.appendChild(container);
        }
        return container;
    }

    function getIcon(type) {
        var icons = {
            success: '<svg class="bi flex-shrink-0 me-2" width="20" height="20" fill="currentColor"><use xlink:href="#check-circle-fill"/></svg>',
            error: '<svg class="bi flex-shrink-0 me-2" width="20" height="20" fill="currentColor"><use xlink:href="#exclamation-triangle-fill"/></svg>',
            warning: '<svg class="bi flex-shrink-0 me-2" width="20" height="20" fill="currentColor"><use xlink:href="#exclamation-triangle-fill"/></svg>',
            info: '<svg class="bi flex-shrink-0 me-2" width="20" height="20" fill="currentColor"><use xlink:href="#info-fill"/></svg>'
        };
        return icons[type] || icons.info;
    }

    function getColorClass(type) {
        var classes = {
            success: 'bg-success text-white',   // Xanh lá cây
            error: 'bg-danger text-white',      // Đỏ
            warning: 'bg-warning text-dark',    // Vàng
            info: 'bg-primary text-white'       // Xanh dương
        };
        return classes[type] || classes.info;
    }

    function show(message, type, duration) {
        type = type || 'info';
        duration = duration || defaultDuration;

        var container = getContainer();
        var toastId = 'toast-' + Date.now();
        var colorClass = getColorClass(type);

        var toastHtml =
            '<div id="' + toastId + '" class="toast align-items-center ' + colorClass + ' border-0" role="alert" aria-live="assertive" aria-atomic="true">' +
                '<div class="d-flex">' +
                    '<div class="toast-body">' +
                        '<i class="' + getIconClass(type) + ' me-2"></i>' +
                        message +
                    '</div>' +
                    '<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>' +
                '</div>' +
            '</div>';

        container.insertAdjacentHTML('beforeend', toastHtml);

        var toastElement = document.getElementById(toastId);
        var toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: duration
        });

        toastElement.addEventListener('hidden.bs.toast', function () {
            toastElement.remove();
        });

        toast.show();
        return toast;
    }

    function getIconClass(type) {
        var icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-times-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        return icons[type] || icons.info;
    }

    return {
        success: function (message, duration) {
            return show(message, 'success', duration);
        },
        error: function (message, duration) {
            return show(message, 'error', duration);
        },
        warning: function (message, duration) {
            return show(message, 'warning', duration);
        },
        info: function (message, duration) {
            return show(message, 'info', duration);
        },
        show: show
    };
})();
