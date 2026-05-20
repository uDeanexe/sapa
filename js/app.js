import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

const notificationVariants = {
    chat: {
        label: 'Chat',
        icon: 'fa-comments',
        toastClass: 'app-toast-chat',
    },
    leave: {
        label: 'Izin',
        icon: 'fa-file-signature',
        toastClass: 'app-toast-leave',
    },
    presence: {
        label: 'Presensi',
        icon: 'fa-calendar-check',
        toastClass: 'app-toast-presence',
    },
    job: {
        label: 'Tugas',
        icon: 'fa-briefcase',
        toastClass: 'app-toast-job',
    },
    job_assigned: {
        label: 'Tugas',
        icon: 'fa-briefcase',
        toastClass: 'app-toast-job',
    },
    checklist: {
        label: 'Checklist',
        icon: 'fa-list-check',
        toastClass: 'app-toast-checklist',
    },
    general: {
        label: 'Info',
        icon: 'fa-bell',
        toastClass: 'app-toast-general',
    },
};

const toastLimits = {
    title: 90,
    message: 180,
};

function escapeHtml(value) {
    return String(value ?? '')
        .replace(new RegExp('&', 'g'), '&amp;')
        .replace(new RegExp('<', 'g'), '&lt;')
        .replace(new RegExp('>', 'g'), '&gt;')
        .replace(new RegExp('"', 'g'), '&quot;')
        .replace(new RegExp("'", 'g'), '&#039;');
}

function normalizeText(value, fallback = '') {
    return String(value ?? fallback)
        .replace(/\s+/g, ' ')
        .trim();
}

function limitText(value, maxLength, fallback = '') {
    const text = normalizeText(value, fallback);

    if (text.length <= maxLength) {
        return text;
    }

    return `${text.slice(0, Math.max(0, maxLength - 3)).trim()}...`;
}

function getNotificationVariant(type = 'general') {
    return notificationVariants[type] || notificationVariants.general;
}

function normalizeServerNotification(notification) {
    const type = String(notification?.type || 'general');
    const variant = getNotificationVariant(type);
    const title = limitText(notification?.title, toastLimits.title, variant.label || 'Notifikasi');
    const message = limitText(notification?.message, toastLimits.message, 'Notifikasi baru');

    return {
        id: String(notification?.id || ''),
        title,
        message,
        type,
    };
}

function ensureBrowserNotificationPermission() {
    if (!('Notification' in window)) return;
    if (Notification.permission === 'default') {
        Notification.requestPermission().catch(() => {});
    }
}

function showBrowserNotification({ title, message, type = 'general' }) {
    if (!('Notification' in window) || document.hasFocus()) return;
    if (Notification.permission !== 'granted') return;

    const variant = getNotificationVariant(type);

    try {
        const notification = new Notification(title || variant.label, {
            body: message || 'Notifikasi baru',
            silent: true,
            tag: `backend-notify-${type}`,
        });
        setTimeout(() => notification.close(), 3500);
    } catch (error) {
        console.error('Browser notification error:', error);
    }
}

function showAppToast({ title, message, type = 'general', container = null }) {
    const wrap = typeof container === 'string'
        ? document.querySelector(container)
        : (container || document.getElementById('global-chat-toast-wrap'));

    if (!wrap) return;

    const variant = getNotificationVariant(type);
    const toast = document.createElement('div');
    toast.className = `app-toast ${variant.toastClass}`;
    toast.setAttribute('role', 'status');

    const iconWrap = document.createElement('div');
    iconWrap.className = 'app-toast-icon';

    const icon = document.createElement('i');
    icon.className = `fas ${variant.icon}`;
    icon.setAttribute('aria-hidden', 'true');
    iconWrap.append(icon);

    const bodyWrap = document.createElement('div');
    bodyWrap.className = 'min-w-0 flex-1';

    const titleEl = document.createElement('p');
    titleEl.className = 'app-toast-title';

    if (type !== 'chat') {
        const labelEl = document.createElement('span');
        labelEl.textContent = variant.label;
        titleEl.append(labelEl, document.createTextNode(' '));
    }

    titleEl.append(document.createTextNode(limitText(title, toastLimits.title, variant.label)));

    const bodyEl = document.createElement('p');
    bodyEl.className = 'app-toast-body';
    bodyEl.textContent = limitText(message, toastLimits.message, 'Notifikasi baru');

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'app-toast-close';
    closeButton.setAttribute('aria-label', 'Tutup notifikasi');

    const closeIcon = document.createElement('i');
    closeIcon.className = 'fas fa-xmark';
    closeIcon.setAttribute('aria-hidden', 'true');
    closeButton.append(closeIcon);

    bodyWrap.append(titleEl, bodyEl);
    toast.append(iconWrap, bodyWrap, closeButton);

    wrap.prepend(toast);

    const closeToast = () => {
        toast.classList.add('is-leaving');
        setTimeout(() => toast.remove(), 240);
    };

    closeButton.addEventListener('click', closeToast);
    setTimeout(closeToast, 3600);
}

function refreshProgressBars(root = document) {
    root.querySelectorAll('[data-progress-width]').forEach((bar) => {
        const value = Number.parseFloat(bar.dataset.progressWidth || '0');
        const boundedValue = Math.min(100, Math.max(0, Number.isFinite(value) ? value : 0));

        bar.style.width = `${boundedValue}%`;
    });
}

window.AppNotify = {
    escapeHtml,
    ensureBrowserNotificationPermission,
    showBrowserNotification,
    showToast: showAppToast,
    variant: getNotificationVariant,
};

Alpine.store('ui', {
    lockScroll() {
        document.documentElement.classList.add('overflow-hidden');
    },
    unlockScroll() {
        document.documentElement.classList.remove('overflow-hidden');
    },
});

Alpine.data('audioPlayer', () => ({
    isPlaying: false,
    progress: 0,
    currentTimeText: '00:00',
    durationText: '00:00',
    audio: null,
    
    init() {
        this.audio = this.$refs.audio;
        this.$watch('progress', value => {
            if (this.$refs.progressFill) {
                this.$refs.progressFill.style.width = `${value}%`;
            }
        });
    },
    onLoadedMetadata() { 
        if (this.audio) this.durationText = this.formatTime(this.audio.duration); 
    },
    onTimeUpdate() {
        if (!this.audio || !this.audio.duration) return;
        this.progress = (this.audio.currentTime / this.audio.duration) * 100;
        this.currentTimeText = this.formatTime(this.audio.currentTime);
    },
    onEnded() {
        this.isPlaying = false;
        this.progress = 0;
        if (this.audio) this.audio.currentTime = 0;
    },
    togglePlay() {
        if (!this.audio) return;
        if (this.audio.paused) {
            this.audio.play();
            this.isPlaying = true;
        } else {
            this.audio.pause();
            this.isPlaying = false;
        }
    },
    seek(event) {
        const progressBar = event.currentTarget;
        const clickPosition = (event.clientX - progressBar.getBoundingClientRect().left) / progressBar.offsetWidth;
        if (this.audio) this.audio.currentTime = clickPosition * this.audio.duration;
    },
    formatTime(seconds) {
        if (isNaN(seconds) || seconds === Infinity) return '00:00';
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
    }
}));

Alpine.start();

document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('main')?.classList.add('content-fade-in');

    document.addEventListener('click', (event) => {
        const dismissButton = event.target.closest('[data-dismiss]');
        if (dismissButton) {
            document.querySelector(dismissButton.dataset.dismiss)?.remove();
        }
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(form.dataset.confirm)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('form[data-submit-lock]').forEach((form) => {
        form.addEventListener('submit', () => {
            const submitter = form.querySelector('[type="submit"]');
            if (!submitter) return;

            submitter.disabled = true;
            submitter.classList.add('opacity-60', 'cursor-not-allowed');
        });
    });

    document.querySelectorAll('input[type="file"][data-file-name]').forEach((input) => {
        const target = document.querySelector(input.dataset.fileName);
        if (!target) return;

        input.addEventListener('change', () => {
            target.textContent = input.files?.[0]?.name || target.dataset.emptyText || 'Belum ada file';
        });
    });

    refreshProgressBars();

    document.querySelectorAll('[data-server-notification-poll]').forEach((container) => {
        if (container.dataset.enabled !== 'true') return;

        let pollInterval = null;
        let sinceId = 0;
        let hasBootstrapped = false;

        const stopPolling = () => {
            if (pollInterval) {
                clearInterval(pollInterval);
                pollInterval = null;
            }
        };

        const notifyFromServer = (notifications) => {
            notifications
                .map(normalizeServerNotification)
                .filter((notification) => notification.id)
                .forEach((notification) => {
                    showAppToast({
                        title: notification.title,
                        message: notification.message,
                        type: notification.type,
                        container,
                    });

                    showBrowserNotification({
                        title: notification.title,
                        message: notification.message,
                        type: notification.type,
                    });
                });
        };

        const pollServerNotifications = () => {
            if (document.hidden || !container.dataset.pollUrl) return;

            fetch(`${container.dataset.pollUrl}?since_id=${sinceId}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => {
                    if (response.status === 401 || response.status === 419) {
                        stopPolling();
                        return null;
                    }
                    if (!response.ok) throw new Error('Global notification poll failed');
                    return response.json();
                })
                .then((payload) => {
                    if (!payload) return;

                    if (Array.isArray(payload.notifications) && payload.notifications.length > 0) {
                        if (hasBootstrapped) {
                            notifyFromServer(payload.notifications);
                        }

                        const newest = payload.notifications[payload.notifications.length - 1];
                        if (newest?.id) {
                            sinceId = String(newest.id);
                        }
                    }

                    if (payload.latest_id) {
                        sinceId = String(payload.latest_id);
                    }
                    hasBootstrapped = true;
                })
                .catch((error) => {
                    console.error('Global notification poll error:', error);
                });
        };

        document.addEventListener('click', ensureBrowserNotificationPermission, { once: true });
        pollServerNotifications();
        pollInterval = setInterval(pollServerNotifications, 5000);

        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                pollServerNotifications();
            }
        });

        window.addEventListener('beforeunload', stopPolling);
    });
});
