import './bootstrap';

import Alpine from 'alpinejs';
import GLightbox from 'glightbox';

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
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function openConfirmModal({
    title = 'Konfirmasi',
    message = 'Apakah Anda yakin?',
    confirmText = 'Ya',
    cancelText = 'Batal',
    danger = false,
    onConfirm = () => {},
} = {}) {
    const modalId = 'ui-confirm-modal';
    const existing = document.getElementById(modalId);
    if (existing) existing.remove();

    const modal = document.createElement('div');
    modal.id = modalId;
    modal.className = 'fixed inset-0 z-[1200] flex items-center justify-center p-4 ui-fade';

    modal.innerHTML = `
        <div class="absolute inset-0 bg-black/55 backdrop-blur-[2px]" data-cancel></div>
        <div class="relative w-full max-w-md rounded-3xl border border-slate-200 bg-white shadow-2xl ui-pop dark:border-slate-700 dark:bg-slate-900/95">
            <div class="flex items-start justify-between gap-3 border-b border-slate-200 px-6 py-5 dark:border-slate-800">
                <div class="min-w-0">
                    <div class="text-sm font-black text-slate-900 dark:text-slate-100">${escapeHtml(title)}</div>
                    <div class="mt-1 text-[12px] text-slate-500 dark:text-slate-300">${escapeHtml(message)}</div>
                </div>
                <button type="button" class="inline-flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-white/10 dark:hover:text-slate-200" aria-label="Tutup" data-cancel>
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="px-6 py-5 flex items-center justify-end gap-3">
                <button type="button" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-950/30 dark:text-slate-200 dark:hover:bg-white/10" data-cancel>
                    ${escapeHtml(cancelText)}
                </button>
                <button type="button" class="inline-flex items-center justify-center rounded-xl px-4 py-2.5 text-sm font-black text-white shadow-sm ${danger ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700'}" data-confirm>
                    ${escapeHtml(confirmText)}
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);

    const close = () => modal.remove();
    modal.querySelectorAll('[data-cancel]').forEach((el) => el.addEventListener('click', close));

    const onKey = (e) => {
        if (e.key === 'Escape') {
            window.removeEventListener('keydown', onKey);
            close();
        }
    };
    window.addEventListener('keydown', onKey);

    const confirmBtn = modal.querySelector('[data-confirm]');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', () => {
            try { onConfirm(); } finally { close(); }
        });
    }
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
    // Keep toast styling stable across builds (avoid relying on dynamically-generated Tailwind classes),
    // but also include minimal utility fallbacks so the toast remains usable even if CSS layers fail to load.
    const typeBorderBgClasses = {
        chat: 'border-blue-200 bg-white',
        leave: 'border-amber-200 bg-white',
        presence: 'border-emerald-200 bg-white',
        job: 'border-indigo-200 bg-white',
        job_assigned: 'border-indigo-200 bg-white',
        checklist: 'border-sky-200 bg-white',
        general: 'border-slate-200 bg-white',
    };

    const fallbackToastClasses = [
        'pointer-events-auto',
        'relative',
        'flex',
        'items-start',
        'gap-3',
        'overflow-hidden',
        'rounded-xl',
        'border',
        'px-3.5',
        'py-3',
        'shadow-2xl',
        'ring-1',
        'ring-slate-900/5',
        'text-slate-900',
    ].join(' ');
    toast.className = `${fallbackToastClasses} ${typeBorderBgClasses[type] || typeBorderBgClasses.general} app-toast ${variant.toastClass}`;
    toast.setAttribute('role', 'status');

    const iconWrap = document.createElement('div');
    const iconColorClasses = {
        chat: 'bg-blue-500/15 text-blue-700',
        leave: 'bg-amber-500/15 text-amber-700',
        presence: 'bg-emerald-500/15 text-emerald-700',
        job: 'bg-indigo-500/15 text-indigo-700',
        job_assigned: 'bg-indigo-500/15 text-indigo-700',
        checklist: 'bg-sky-500/15 text-sky-700',
        general: 'bg-slate-500/15 text-slate-700',
    };
    iconWrap.className = `app-toast-icon mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl text-sm shadow-inner ${iconColorClasses[type] || iconColorClasses.general}`;

    const icon = document.createElement('i');
    icon.className = `fas ${variant.icon}`;
    icon.setAttribute('aria-hidden', 'true');
    iconWrap.append(icon);

    const bodyWrap = document.createElement('div');
    bodyWrap.className = 'app-toast-content min-w-0 flex-1';

    const titleEl = document.createElement('p');
    titleEl.className = 'app-toast-title mb-0.5 pr-7 text-[13px] font-bold leading-5 text-slate-950';

    if (type !== 'chat') {
        const labelEl = document.createElement('span');
        labelEl.textContent = variant.label;
        titleEl.append(labelEl, document.createTextNode(' '));
    }

    titleEl.append(document.createTextNode(limitText(title, toastLimits.title, variant.label)));

    const bodyEl = document.createElement('p');
    bodyEl.className = 'app-toast-body line-clamp-2 break-words text-xs leading-5 text-slate-600';
    bodyEl.textContent = limitText(message, toastLimits.message, 'Notifikasi baru');

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'app-toast-close absolute right-2 top-2 inline-flex h-7 w-7 items-center justify-center rounded-lg text-[12px] text-slate-500 hover:bg-slate-900/5 hover:text-slate-800 focus:outline-none focus:ring-1 focus:ring-slate-400';
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

function getPreferredTheme() {
    try {
        const stored = localStorage.getItem('theme');
        if (stored === 'dark' || stored === 'light') return stored;
    } catch (_) {}

    const systemDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    return systemDark ? 'dark' : 'light';
}

function setTheme(theme) {
    const next = theme === 'dark' ? 'dark' : 'light';
    document.documentElement.classList.toggle('dark', next === 'dark');
    document.documentElement.style.colorScheme = next;
    try { localStorage.setItem('theme', next); } catch (_) {}
    updateThemeToggles(next);
}

function updateThemeToggles(theme) {
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        const icon = btn.querySelector('[data-theme-icon]');
        const label = btn.querySelector('[data-theme-label]');

        if (icon) {
            icon.classList.toggle('fa-moon', theme === 'light');
            icon.classList.toggle('fa-sun', theme === 'dark');
        }
        if (label) {
            label.textContent = theme === 'dark' ? 'Light' : 'Dark';
        }
    });
}

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
        this.durationText = this.formatTime(this.audio?.duration);
    },

    onTimeUpdate() {
        if (!this.audio?.duration) return;
        this.progress = (this.audio.currentTime / this.audio.duration) * 100;
        this.currentTimeText = this.formatTime(this.audio.currentTime);
    },

    onEnded() {
        this.isPlaying = false;
        this.progress = 0;
        if (this.audio) {
            this.audio.currentTime = 0;
        }
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
        if (!this.audio || !this.audio.duration) return;
        const progressBar = event.currentTarget;
        const clickPosition = (event.clientX - progressBar.getBoundingClientRect().left) / progressBar.offsetWidth;
        this.audio.currentTime = clickPosition * this.audio.duration;
    },

    formatTime(seconds) {
        if (isNaN(seconds) || seconds === Infinity) return '00:00';
        const minutes = Math.floor(seconds / 60);
        const remainingSeconds = Math.floor(seconds % 60);
        return `${String(minutes).padStart(2, '0')}:${String(remainingSeconds).padStart(2, '0')}`;
    },
}));

Alpine.start();

// Initialize GLightbox for image and video lightbox
const lightbox = GLightbox({
    selector: '[data-lightbox]',
    titleSize: 'h3',
    descPosition: 'bottom',
    loop: true,
});

window.AppLightbox = lightbox;

// Video playback uses GLightbox the same way images do (selector: [data-lightbox]).

// Reinitialize lightbox on Alpine component updates
document.addEventListener('alpine:init', () => {
    lightbox.reload();
});

document.addEventListener('DOMContentLoaded', () => {
    const initialTheme = getPreferredTheme();
    updateThemeToggles(initialTheme);

    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
            setTheme(current === 'dark' ? 'light' : 'dark');
        });
    });

    document.querySelector('main')?.classList.add('content-fade-in');

    // Chat emoji picker (used by admin chat view)
    initChatEmojiPicker();

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

    // Logout confirmation (center modal, not native browser confirm)
    document.querySelectorAll('form[data-logout-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            openConfirmModal({
                title: 'Keluar?',
                message: 'Anda yakin ingin log out dari akun ini?',
                confirmText: 'Log Out',
                cancelText: 'Batal',
                danger: true,
                onConfirm: () => form.submit(),
            });
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

        const userId = document
            .querySelector('meta[name="app-user-id"]')
            ?.getAttribute('content');

        // Prefer realtime via WebSocket (Echo + Reverb). Fallback ke polling kalau Echo tidak tersedia.
        if (window.Echo && userId) {
            const notifySingle = (notification) => {
                const normalized = normalizeServerNotification(notification);
                if (!normalized.id) return;

                showAppToast({
                    title: normalized.title,
                    message: normalized.message,
                    type: normalized.type,
                    container,
                });

                showBrowserNotification({
                    title: normalized.title,
                    message: normalized.message,
                    type: normalized.type,
                });
            };

            window.Echo.private(`App.Models.User.${userId}`).notification((notification) => {
                notifySingle(notification);
            });

            return;
        }

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

function initChatEmojiPicker(root = document) {
    const emojiBtn = root.getElementById?.('emoji-btn') || root.querySelector?.('#emoji-btn');
    const emojiMenu = root.getElementById?.('emoji-menu') || root.querySelector?.('#emoji-menu');
    const emojiGrid = root.getElementById?.('emoji-grid') || root.querySelector?.('#emoji-grid');
    const emojiRecent = root.getElementById?.('emoji-recent') || root.querySelector?.('#emoji-recent');
    const messageInput = root.getElementById?.('message-input') || root.querySelector?.('#message-input');
    const attachMenu = root.getElementById?.('attach-menu') || root.querySelector?.('#attach-menu');

    if (!emojiBtn || !emojiMenu || !emojiGrid || !emojiRecent || !messageInput) return;

    const EMOJI_RECENT_KEY = 'chat_emoji_recent_v1';
    const EMOJI_LIST = [
        '😀','😁','😂','🤣','😊','😍','😘','😎','🤩','🤔',
        '😅','😇','🙂','🙃','😉','😌','😋','😜','🤗','😴',
        '😢','😭','😤','😡','🤯','😱','😬','🤐','😷','🤒',
        '👍','👎','🙏','👏','🙌','🤝','💪','🔥','✨','⭐',
        '✅','❌','⚠️','🚀','🎉','💯','💡','📌','📝','📎',
        '📷','🎥','🎧','📞','📍','🧾','💳','🏦','🛒','📦',
    ];

    const loadRecentEmojis = () => {
        try {
            const raw = localStorage.getItem(EMOJI_RECENT_KEY);
            const parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed.slice(0, 20) : [];
        } catch {
            return [];
        }
    };

    const saveRecentEmoji = (emoji) => {
        const current = loadRecentEmojis().filter((e) => e !== emoji);
        current.unshift(emoji);
        try {
            localStorage.setItem(EMOJI_RECENT_KEY, JSON.stringify(current.slice(0, 20)));
        } catch {}
    };

    const insertEmojiAtCursor = (emoji) => {
        messageInput.focus();
        const start = messageInput.selectionStart ?? messageInput.value.length;
        const end = messageInput.selectionEnd ?? messageInput.value.length;
        const value = messageInput.value ?? '';
        messageInput.value = value.slice(0, start) + emoji + value.slice(end);
        const nextPos = start + emoji.length;
        messageInput.setSelectionRange(nextPos, nextPos);
        messageInput.dispatchEvent(new Event('input', { bubbles: true }));
    };

    const renderEmojiButtons = (containerEl, emojis) => {
        containerEl.innerHTML = '';
        emojis.forEach((emoji) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'h-8 w-8 rounded-xl hover:bg-slate-100 active:scale-95 transition inline-flex items-center justify-center text-[18px]';
            btn.textContent = emoji;
            btn.setAttribute('aria-label', `Emoji ${emoji}`);
            btn.addEventListener('click', () => {
                insertEmojiAtCursor(emoji);
                saveRecentEmoji(emoji);
                renderRecent();
                toggleEmojiMenu(false);
            });
            containerEl.appendChild(btn);
        });
    };

    const renderRecent = () => {
        const recent = loadRecentEmojis();
        const fallback = ['😀','😂','😊','😍','👍','🙏','🎉','🔥','✅','📌'];
        renderEmojiButtons(emojiRecent, recent.length ? recent : fallback);
    };

    const toggleEmojiMenu = (forceState = null) => {
        const shouldOpen = forceState === null ? emojiMenu.classList.contains('hidden') : forceState;
        emojiMenu.classList.toggle('hidden', !shouldOpen);
    };

    // Initial render
    renderEmojiButtons(emojiGrid, EMOJI_LIST);
    renderRecent();

    emojiBtn.addEventListener('click', () => {
        // close attach menu if open
        if (attachMenu && !attachMenu.classList.contains('hidden')) {
            attachMenu.classList.add('hidden');
        }
        toggleEmojiMenu();
    });

    document.addEventListener('click', (e) => {
        if (emojiMenu.classList.contains('hidden')) return;
        const clickedInside = e.target?.closest?.('#emoji-menu') || e.target?.closest?.('#emoji-btn');
        if (!clickedInside) toggleEmojiMenu(false);
    });
}
