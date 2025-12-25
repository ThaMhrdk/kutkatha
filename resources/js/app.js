import './bootstrap';

/**
 * Kutkatha Async Utilities
 * Model asynchronous implementation for real-time features
 */
window.Kutkatha = {
    /**
     * API base configuration
     */
    api: {
        baseUrl: '/api/v1',
        token: null,

        /**
         * Set authentication token
         */
        setToken(token) {
            this.token = token;
            localStorage.setItem('kutkatha_token', token);
        },

        /**
         * Get authentication token
         */
        getToken() {
            if (!this.token) {
                this.token = localStorage.getItem('kutkatha_token');
            }
            return this.token;
        },

        /**
         * Make async API request
         */
        async request(method, endpoint, data = null, options = {}) {
            const url = this.baseUrl + endpoint;
            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
            };

            if (this.getToken()) {
                headers['Authorization'] = 'Bearer ' + this.getToken();
            }

            const config = {
                method: method.toUpperCase(),
                headers,
                ...options,
            };

            if (data && ['POST', 'PUT', 'PATCH'].includes(config.method)) {
                if (data instanceof FormData) {
                    delete config.headers['Content-Type'];
                    config.body = data;
                } else {
                    config.body = JSON.stringify(data);
                }
            }

            try {
                const response = await fetch(url, config);
                const json = await response.json();

                if (!response.ok) {
                    throw new Error(json.message || 'Request failed');
                }

                return json;
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        },

        // Convenience methods
        get: (endpoint, options) => Kutkatha.api.request('GET', endpoint, null, options),
        post: (endpoint, data, options) => Kutkatha.api.request('POST', endpoint, data, options),
        put: (endpoint, data, options) => Kutkatha.api.request('PUT', endpoint, data, options),
        delete: (endpoint, options) => Kutkatha.api.request('DELETE', endpoint, null, options),
    },

    /**
     * Chat module - Async polling implementation
     */
    chat: {
        pollingInterval: null,
        lastMessageId: null,
        consultationId: null,
        messagesContainer: null,

        /**
         * Initialize chat with async polling
         */
        init(consultationId, containerId, options = {}) {
            this.consultationId = consultationId;
            this.messagesContainer = document.getElementById(containerId);
            this.pollingIntervalMs = options.pollingInterval || 3000; // 3 seconds default

            // Load initial messages
            this.loadMessages().then(() => {
                // Start polling for new messages
                this.startPolling();
            });

            // Setup send form if exists
            const sendForm = document.getElementById('chat-form');
            if (sendForm) {
                sendForm.addEventListener('submit', (e) => this.handleSend(e));
            }
        },

        /**
         * Load messages asynchronously
         */
        async loadMessages() {
            try {
                const endpoint = this.lastMessageId
                    ? `/user/consultations/${this.consultationId}/messages?after_id=${this.lastMessageId}`
                    : `/user/consultations/${this.consultationId}/messages`;

                const response = await Kutkatha.api.get(endpoint);

                if (response.data.messages && response.data.messages.length > 0) {
                    this.appendMessages(response.data.messages);
                    this.lastMessageId = response.data.last_id;
                }
            } catch (error) {
                console.error('Failed to load messages:', error);
            }
        },

        /**
         * Append messages to chat container
         */
        appendMessages(messages) {
            if (!this.messagesContainer) return;

            messages.forEach(msg => {
                const messageEl = this.createMessageElement(msg);
                this.messagesContainer.appendChild(messageEl);
            });

            // Scroll to bottom
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        },

        /**
         * Create message HTML element
         */
        createMessageElement(message) {
            const div = document.createElement('div');
            const isOwn = message.sender_id == window.currentUserId;

            div.className = `chat-message ${isOwn ? 'own' : 'other'} mb-3`;
            div.innerHTML = `
                <div class="message-bubble ${isOwn ? 'bg-primary text-white' : 'bg-light'}">
                    <div class="message-sender small fw-bold mb-1">${message.sender.name}</div>
                    <div class="message-content">${this.escapeHtml(message.message)}</div>
                    ${message.attachment ? `<div class="message-attachment mt-2">
                        <a href="/storage/${message.attachment}" target="_blank" class="${isOwn ? 'text-white' : 'text-primary'}">
                            <i class="fas fa-paperclip me-1"></i>Lampiran
                        </a>
                    </div>` : ''}
                    <div class="message-time small ${isOwn ? 'text-white-50' : 'text-muted'} mt-1">
                        ${new Date(message.created_at).toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'})}
                    </div>
                </div>
            `;

            return div;
        },

        /**
         * Handle send message
         */
        async handleSend(e) {
            e.preventDefault();

            const form = e.target;
            const input = form.querySelector('[name="message"]');
            const fileInput = form.querySelector('[name="attachment"]');
            const submitBtn = form.querySelector('[type="submit"]');

            if (!input.value.trim()) return;

            submitBtn.disabled = true;

            try {
                const formData = new FormData();
                formData.append('message', input.value);

                if (fileInput && fileInput.files[0]) {
                    formData.append('attachment', fileInput.files[0]);
                }

                await Kutkatha.api.post(
                    `/user/consultations/${this.consultationId}/messages`,
                    formData
                );

                // Clear form
                input.value = '';
                if (fileInput) fileInput.value = '';

                // Immediately fetch new messages
                await this.loadMessages();

            } catch (error) {
                alert('Gagal mengirim pesan: ' + error.message);
            } finally {
                submitBtn.disabled = false;
            }
        },

        /**
         * Start async polling for new messages
         */
        startPolling() {
            if (this.pollingInterval) return;

            this.pollingInterval = setInterval(() => {
                this.loadMessages();
            }, this.pollingIntervalMs);
        },

        /**
         * Stop polling
         */
        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        },

        /**
         * Escape HTML to prevent XSS
         */
        escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        },
    },

    /**
     * Notifications module - Async polling
     */
    notifications: {
        pollingInterval: null,
        badge: null,
        dropdown: null,

        init(badgeId, dropdownId, options = {}) {
            this.badge = document.getElementById(badgeId);
            this.dropdown = document.getElementById(dropdownId);
            this.pollingIntervalMs = options.pollingInterval || 30000; // 30 seconds

            this.loadNotifications();
            this.startPolling();
        },

        async loadNotifications() {
            try {
                const response = await fetch('/notifications/unread', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                });

                const data = await response.json();

                if (this.badge) {
                    this.badge.textContent = data.count;
                    this.badge.style.display = data.count > 0 ? 'inline-block' : 'none';
                }

                if (this.dropdown && data.notifications) {
                    this.renderNotifications(data.notifications);
                }
            } catch (error) {
                console.error('Failed to load notifications:', error);
            }
        },

        renderNotifications(notifications) {
            if (!this.dropdown) return;

            if (notifications.length === 0) {
                this.dropdown.innerHTML = '<div class="dropdown-item text-muted">Tidak ada notifikasi baru</div>';
                return;
            }

            this.dropdown.innerHTML = notifications.map(n => `
                <a class="dropdown-item ${n.read_at ? '' : 'bg-light'}" href="${n.data.action_url || '#'}">
                    <div class="fw-bold">${n.data.title}</div>
                    <div class="small text-muted">${n.data.message}</div>
                </a>
            `).join('');
        },

        startPolling() {
            if (this.pollingInterval) return;

            this.pollingInterval = setInterval(() => {
                this.loadNotifications();
            }, this.pollingIntervalMs);
        },

        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        },
    },

    /**
     * Async form submission utility
     */
    form: {
        /**
         * Submit form asynchronously with loading state
         */
        async submit(formId, options = {}) {
            const form = document.getElementById(formId);
            if (!form) return;

            const submitBtn = form.querySelector('[type="submit"]');
            const originalText = submitBtn?.innerHTML;

            try {
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Memproses...';
                }

                const formData = new FormData(form);

                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Terjadi kesalahan');
                }

                if (options.onSuccess) {
                    options.onSuccess(data);
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    Kutkatha.toast.success(data.message || 'Berhasil!');
                }

                return data;

            } catch (error) {
                if (options.onError) {
                    options.onError(error);
                } else {
                    Kutkatha.toast.error(error.message);
                }
                throw error;
            } finally {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                }
            }
        },
    },

    /**
     * Toast notification utility
     */
    toast: {
        show(message, type = 'info', duration = 5000) {
            const container = document.getElementById('toast-container') || this.createContainer();

            const toast = document.createElement('div');
            toast.className = `toast show align-items-center text-white bg-${type} border-0`;
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            `;

            container.appendChild(toast);

            // Auto remove
            setTimeout(() => toast.remove(), duration);

            // Close button
            toast.querySelector('.btn-close').addEventListener('click', () => toast.remove());
        },

        success(message) { this.show(message, 'success'); },
        error(message) { this.show(message, 'danger'); },
        warning(message) { this.show(message, 'warning'); },
        info(message) { this.show(message, 'info'); },

        createContainer() {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        },
    },

    /**
     * Async data loading with skeleton
     */
    async loadData(url, containerId, options = {}) {
        const container = document.getElementById(containerId);
        if (!container) return;

        // Show loading skeleton
        if (options.showSkeleton !== false) {
            container.innerHTML = this.getSkeleton(options.skeletonType || 'card');
        }

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                },
            });

            const data = await response.json();

            if (options.render) {
                container.innerHTML = options.render(data);
            }

            return data;

        } catch (error) {
            container.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    Gagal memuat data. <a href="#" onclick="location.reload()">Coba lagi</a>
                </div>
            `;
            throw error;
        }
    },

    getSkeleton(type) {
        switch (type) {
            case 'card':
                return `
                    <div class="card skeleton-card">
                        <div class="card-body">
                            <div class="skeleton-line w-75 mb-2"></div>
                            <div class="skeleton-line w-50 mb-2"></div>
                            <div class="skeleton-line w-100"></div>
                        </div>
                    </div>
                `;
            case 'list':
                return Array(5).fill(`
                    <div class="skeleton-list-item d-flex align-items-center mb-2">
                        <div class="skeleton-circle me-3"></div>
                        <div class="flex-grow-1">
                            <div class="skeleton-line w-75 mb-1"></div>
                            <div class="skeleton-line w-50"></div>
                        </div>
                    </div>
                `).join('');
            default:
                return '<div class="skeleton-line"></div>';
        }
    },
};

// Add skeleton styles
const skeletonStyles = document.createElement('style');
skeletonStyles.textContent = `
    @keyframes skeleton-loading {
        0% { background-position: -200px 0; }
        100% { background-position: calc(200px + 100%) 0; }
    }

    .skeleton-line, .skeleton-circle {
        background: linear-gradient(90deg, #f0f0f0 0px, #e0e0e0 40px, #f0f0f0 80px);
        background-size: 200px 100%;
        animation: skeleton-loading 1.5s ease-in-out infinite;
        border-radius: 4px;
    }

    .skeleton-line {
        height: 16px;
        display: block;
    }

    .skeleton-circle {
        width: 48px;
        height: 48px;
        border-radius: 50%;
    }

    .chat-message.own {
        text-align: right;
    }

    .chat-message.own .message-bubble {
        display: inline-block;
        text-align: left;
        border-radius: 18px 18px 4px 18px;
        padding: 12px 16px;
        max-width: 70%;
    }

    .chat-message.other .message-bubble {
        display: inline-block;
        border-radius: 18px 18px 18px 4px;
        padding: 12px 16px;
        max-width: 70%;
    }
`;
document.head.appendChild(skeletonStyles);
