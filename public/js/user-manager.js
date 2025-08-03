class UserManager {
    constructor() {
        this.users = [];
        this.pagination = {};
        this.filters = {
            search: '',
            role: '',
            sort: 'created_at',
            direction: 'desc',
            per_page: 10
        };
        this.selectedUsers = new Set();
        this.currentUser = null;
        this.isEditing = false;
        
        // 予約管理関連
        this.reservations = [];
        this.reservationPagination = {};
        this.reservationFilters = {
            date_from: '',
            date_to: '',
            status: '',
            sort: 'created_at',
            direction: 'desc',
            per_page: 10
        };
        this.currentReservation = null;
        this.isEditingReservation = false;
        this.currentReservationUser = null;
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadUsers();
    }

    bindEvents() {
        // 検索とフィルター
        document.getElementById('search-input').addEventListener('input', this.debounce((e) => {
            this.filters.search = e.target.value;
            this.loadUsers();
        }, 500));

        document.getElementById('role-filter').addEventListener('change', (e) => {
            this.filters.role = e.target.value;
            this.loadUsers();
        });

        document.getElementById('sort-field').addEventListener('change', (e) => {
            this.filters.sort = e.target.value;
            this.loadUsers();
        });

        document.getElementById('sort-direction').addEventListener('change', (e) => {
            this.filters.direction = e.target.value;
            this.loadUsers();
        });

        document.getElementById('per-page').addEventListener('change', (e) => {
            this.filters.per_page = e.target.value;
            this.loadUsers();
        });

        // ボタンイベント
        document.getElementById('create-user-btn').addEventListener('click', () => this.openCreateModal());
        document.getElementById('clear-filters-btn').addEventListener('click', () => this.clearFilters());
        document.getElementById('bulk-delete-btn').addEventListener('click', () => this.bulkDelete());

        // モーダル関連
        document.getElementById('close-modal').addEventListener('click', () => this.closeModal());
        document.getElementById('cancel-btn').addEventListener('click', () => this.closeModal());
        document.getElementById('user-form').addEventListener('submit', (e) => this.handleFormSubmit(e));

        // 削除確認モーダル
        document.getElementById('cancel-delete').addEventListener('click', () => this.closeDeleteModal());
        document.getElementById('confirm-delete').addEventListener('click', () => this.confirmDelete());

        // 全選択チェックボックス
        document.getElementById('select-all').addEventListener('change', (e) => this.toggleSelectAll(e.target.checked));

        // モーダル外クリックで閉じる
        document.getElementById('user-modal').addEventListener('click', (e) => {
            if (e.target.id === 'user-modal') this.closeModal();
        });

        document.getElementById('delete-modal').addEventListener('click', (e) => {
            if (e.target.id === 'delete-modal') this.closeDeleteModal();
        });

        // 予約管理関連のイベント
        this.bindReservationEvents();
    }

    bindReservationEvents() {
        // 予約管理モーダル関連
        document.getElementById('close-reservation-modal')?.addEventListener('click', () => this.closeReservationModal());
        document.getElementById('close-reservation-form-modal')?.addEventListener('click', () => this.closeReservationFormModal());
        document.getElementById('cancel-reservation-btn')?.addEventListener('click', () => this.closeReservationFormModal());

        // 予約フィルター
        document.getElementById('reservation-date-from')?.addEventListener('change', () => this.loadReservations());
        document.getElementById('reservation-date-to')?.addEventListener('change', () => this.loadReservations());
        document.getElementById('reservation-status-filter')?.addEventListener('change', () => this.loadReservations());
        document.getElementById('reservation-sort')?.addEventListener('change', () => this.loadReservations());
        
        document.getElementById('clear-reservation-filters')?.addEventListener('click', () => this.clearReservationFilters());
        document.getElementById('create-reservation-btn')?.addEventListener('click', () => this.openCreateReservationModal());

        // 予約フォーム
        document.getElementById('reservation-form')?.addEventListener('submit', (e) => this.handleReservationFormSubmit(e));

        // モーダル外クリック
        document.getElementById('reservation-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'reservation-modal') this.closeReservationModal();
        });

        document.getElementById('reservation-form-modal')?.addEventListener('click', (e) => {
            if (e.target.id === 'reservation-form-modal') this.closeReservationFormModal();
        });
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    async loadUsers(page = 1) {
        this.showLoading(true);
        
        try {
            const params = new URLSearchParams({
                ...this.filters,
                page: page
            });

            const response = await fetch(`/admin/user-manager/users?${params}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.users = data.data;
                this.pagination = data.pagination;
                this.renderUsers();
                this.renderPagination();
                this.selectedUsers.clear();
                this.updateBulkDeleteButton();
                this.updateSelectAllCheckbox();
            } else {
                this.showAlert('error', 'ユーザーの読み込みに失敗しました。');
            }
        } catch (error) {
            console.error('Error loading users:', error);
            this.showAlert('error', 'ユーザーの読み込み中にエラーが発生しました。');
        } finally {
            this.showLoading(false);
        }
    }

    renderUsers() {
        const tbody = document.getElementById('users-table-body');
        
        if (this.users.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        ユーザーが見つかりませんでした。
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.users.map(user => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                    <input type="checkbox" class="user-checkbox rounded border-gray-300" 
                           value="${user.id}" ${this.selectedUsers.has(user.id) ? 'checked' : ''}>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center text-gray-700 font-medium">
                                ${user.name.charAt(0).toUpperCase()}
                            </div>
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">${this.escapeHtml(user.name)}</div>
                            <div class="text-sm text-gray-500">${this.escapeHtml(user.email)}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    ${user.is_admin ? 
                        '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">管理者</span>' : 
                        '<span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">一般ユーザー</span>'
                    }
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        ${user.reservations_count || 0}件
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    ${this.formatDate(user.created_at)}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                        <button onclick="userManager.openReservationModal(${user.id})" class="text-purple-600 hover:text-purple-900">
                            予約管理
                        </button>
                        <button onclick="userManager.openEditModal(${user.id})" class="text-blue-600 hover:text-blue-900">
                            編集
                        </button>
                        <button onclick="userManager.toggleAdmin(${user.id})" class="text-yellow-600 hover:text-yellow-900">
                            権限変更
                        </button>
                        <button onclick="userManager.openDeleteModal(${user.id})" class="text-red-600 hover:text-red-900">
                            削除
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        // チェックボックスイベントを再バインド
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const userId = parseInt(e.target.value);
                if (e.target.checked) {
                    this.selectedUsers.add(userId);
                } else {
                    this.selectedUsers.delete(userId);
                }
                this.updateBulkDeleteButton();
                this.updateSelectAllCheckbox();
            });
        });
    }

    renderPagination() {
        const container = document.getElementById('pagination-container');
        
        if (this.pagination.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        let paginationHtml = `
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    ${this.pagination.from || 0} - ${this.pagination.to || 0} / ${this.pagination.total} 件
                </div>
                <div class="flex space-x-1">
        `;

        // 前へボタン
        if (this.pagination.current_page > 1) {
            paginationHtml += `
                <button onclick="userManager.loadUsers(${this.pagination.current_page - 1})" 
                        class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded">
                    前へ
                </button>
            `;
        }

        // ページ番号ボタン
        const startPage = Math.max(1, this.pagination.current_page - 2);
        const endPage = Math.min(this.pagination.last_page, this.pagination.current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === this.pagination.current_page;
            paginationHtml += `
                <button onclick="userManager.loadUsers(${i})" 
                        class="px-3 py-2 text-sm ${isActive ? 
                            'bg-blue-500 text-white' : 
                            'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                        } rounded">
                    ${i}
                </button>
            `;
        }

        // 次へボタン
        if (this.pagination.current_page < this.pagination.last_page) {
            paginationHtml += `
                <button onclick="userManager.loadUsers(${this.pagination.current_page + 1})" 
                        class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded">
                    次へ
                </button>
            `;
        }

        paginationHtml += `
                </div>
            </div>
        `;

        container.innerHTML = paginationHtml;
    }

    clearFilters() {
        document.getElementById('search-input').value = '';
        document.getElementById('role-filter').value = '';
        document.getElementById('sort-field').value = 'created_at';
        document.getElementById('sort-direction').value = 'desc';
        document.getElementById('per-page').value = '10';
        
        this.filters = {
            search: '',
            role: '',
            sort: 'created_at',
            direction: 'desc',
            per_page: 10
        };
        
        this.loadUsers();
    }

    openCreateModal() {
        this.isEditing = false;
        this.currentUser = null;
        
        document.getElementById('modal-title').textContent = 'ユーザー作成';
        document.getElementById('password-label').innerHTML = 'パスワード <span class="text-red-500">*</span>';
        document.getElementById('password-help').style.display = 'none';
        document.getElementById('user-password').required = true;
        
        this.resetForm();
        this.showModal();
    }

    async openEditModal(userId) {
        this.isEditing = true;
        this.currentUser = userId;
        
        try {
            const response = await fetch(`/admin/user-manager/users/${userId}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const user = data.data;
                
                document.getElementById('modal-title').textContent = 'ユーザー編集';
                document.getElementById('password-label').innerHTML = '新しいパスワード';
                document.getElementById('password-help').style.display = 'block';
                document.getElementById('user-password').required = false;
                
                document.getElementById('user-name').value = user.name;
                document.getElementById('user-email').value = user.email;
                document.getElementById('user-password').value = '';
                document.getElementById('user-password-confirmation').value = '';
                document.getElementById('user-is-admin').checked = user.is_admin;
                
                this.clearFormErrors();
                this.showModal();
            } else {
                this.showAlert('error', 'ユーザー情報の取得に失敗しました。');
            }
        } catch (error) {
            console.error('Error loading user:', error);
            this.showAlert('error', 'ユーザー情報の取得中にエラーが発生しました。');
        }
    }

    showModal() {
        document.getElementById('user-modal').style.display = 'block';
    }

    closeModal() {
        document.getElementById('user-modal').style.display = 'none';
        this.resetForm();
    }

    resetForm() {
        document.getElementById('user-form').reset();
        this.clearFormErrors();
    }

    clearFormErrors() {
        document.querySelectorAll('[id^="error-"]').forEach(el => {
            el.style.display = 'none';
            el.textContent = '';
        });
        
        document.querySelectorAll('.border-red-300').forEach(el => {
            el.classList.remove('border-red-300');
            el.classList.add('border-gray-300');
        });
    }

    showFormErrors(errors) {
        this.clearFormErrors();
        
        Object.keys(errors).forEach(field => {
            const errorEl = document.getElementById(`error-${field}`);
            const inputEl = document.getElementById(`user-${field}`) || document.getElementById(`user-${field.replace('_', '-')}`);
            
            if (errorEl && inputEl) {
                errorEl.textContent = errors[field][0];
                errorEl.style.display = 'block';
                inputEl.classList.remove('border-gray-300');
                inputEl.classList.add('border-red-300');
            }
        });
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        // チェックボックスの値を適切に設定
        data.is_admin = document.getElementById('user-is-admin').checked;
        
        try {
            const url = this.isEditing ? 
                `/admin/user-manager/users/${this.currentUser}` : 
                '/admin/user-manager/users';
            
            const method = this.isEditing ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('success', result.message);
                this.closeModal();
                this.loadUsers();
            } else {
                if (result.errors) {
                    this.showFormErrors(result.errors);
                } else {
                    this.showAlert('error', result.message || 'エラーが発生しました。');
                }
            }
        } catch (error) {
            console.error('Error saving user:', error);
            this.showAlert('error', '保存中にエラーが発生しました。');
        }
    }

    openDeleteModal(userId) {
        const user = this.users.find(u => u.id === userId);
        if (!user) return;
        
        this.currentUser = userId;
        document.getElementById('delete-message').textContent = 
            `「${user.name}」を削除してもよろしいですか？この操作は元に戻すことができません。`;
        
        document.getElementById('delete-modal').style.display = 'block';
    }

    closeDeleteModal() {
        document.getElementById('delete-modal').style.display = 'none';
        this.currentUser = null;
    }

    async confirmDelete() {
        if (!this.currentUser) return;
        
        try {
            const response = await fetch(`/admin/user-manager/users/${this.currentUser}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('success', data.message);
                this.closeDeleteModal();
                this.loadUsers();
            } else {
                this.showAlert('error', data.message || '削除に失敗しました。');
            }
        } catch (error) {
            console.error('Error deleting user:', error);
            this.showAlert('error', '削除中にエラーが発生しました。');
        }
    }

    async bulkDelete() {
        if (this.selectedUsers.size === 0) return;
        
        if (!confirm(`選択した${this.selectedUsers.size}人のユーザーを削除してもよろしいですか？`)) {
            return;
        }
        
        try {
            const response = await fetch('/admin/user-manager/users/bulk-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    user_ids: Array.from(this.selectedUsers)
                })
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('success', data.message);
                this.selectedUsers.clear();
                this.updateBulkDeleteButton();
                this.loadUsers();
            } else {
                this.showAlert('error', data.message || '一括削除に失敗しました。');
            }
        } catch (error) {
            console.error('Error bulk deleting users:', error);
            this.showAlert('error', '一括削除中にエラーが発生しました。');
        }
    }

    async toggleAdmin(userId) {
        const user = this.users.find(u => u.id === userId);
        if (!user) return;
        
        const newStatus = user.is_admin ? '一般ユーザー' : '管理者';
        
        if (!confirm(`「${user.name}」の権限を${newStatus}に変更してもよろしいですか？`)) {
            return;
        }
        
        try {
            const response = await fetch(`/admin/user-manager/users/${userId}/toggle-admin`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('success', data.message);
                this.loadUsers();
            } else {
                this.showAlert('error', data.message || '権限変更に失敗しました。');
            }
        } catch (error) {
            console.error('Error toggling admin:', error);
            this.showAlert('error', '権限変更中にエラーが発生しました。');
        }
    }

    toggleSelectAll(checked) {
        this.selectedUsers.clear();
        
        if (checked) {
            this.users.forEach(user => {
                this.selectedUsers.add(user.id);
            });
        }
        
        document.querySelectorAll('.user-checkbox').forEach(checkbox => {
            checkbox.checked = checked;
        });
        
        this.updateBulkDeleteButton();
    }

    updateSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('select-all');
        const totalUsers = this.users.length;
        const selectedCount = this.selectedUsers.size;
        
        if (selectedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (selectedCount === totalUsers) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }

    updateBulkDeleteButton() {
        const button = document.getElementById('bulk-delete-btn');
        button.disabled = this.selectedUsers.size === 0;
        
        if (this.selectedUsers.size > 0) {
            button.textContent = `選択した${this.selectedUsers.size}人を削除`;
        } else {
            button.textContent = '選択したユーザーを削除';
        }
    }

    showAlert(type, message) {
        const container = document.getElementById('alert-container');
        const alert = document.getElementById('alert');
        const icon = document.getElementById('alert-icon');
        const messageEl = document.getElementById('alert-message');
        
        // リセット
        alert.className = 'rounded-lg p-4';
        icon.innerHTML = '';
        
        if (type === 'success') {
            alert.classList.add('bg-green-50', 'border', 'border-green-200');
            messageEl.classList.add('text-green-700');
            icon.innerHTML = `
                <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
            `;
        } else {
            alert.classList.add('bg-red-50', 'border', 'border-red-200');
            messageEl.classList.add('text-red-700');
            icon.innerHTML = `
                <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            `;
        }
        
        messageEl.textContent = message;
        container.style.display = 'block';
        
        // 5秒後に自動で隠す
        setTimeout(() => {
            container.style.display = 'none';
        }, 5000);
    }

    showLoading(show) {
        document.getElementById('loading').style.display = show ? 'block' : 'none';
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('ja-JP', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    // 予約管理機能
    async openReservationModal(userId) {
        this.currentReservationUser = userId;
        const user = this.users.find(u => u.id === userId);
        
        if (!user) return;
        
        document.getElementById('reservation-modal-title').textContent = `${user.name} の予約管理`;
        this.showReservationModal();
        
        // 初期フィルター設定
        this.reservationFilters = {
            date_from: '',
            date_to: '',
            status: '',
            sort: 'created_at',
            direction: 'desc',
            per_page: 10
        };
        
        this.clearReservationFilters();
        await this.loadReservations();
    }

    showReservationModal() {
        document.getElementById('reservation-modal').style.display = 'block';
    }

    closeReservationModal() {
        document.getElementById('reservation-modal').style.display = 'none';
        this.currentReservationUser = null;
        this.reservations = [];
    }

    async loadReservations(page = 1) {
        if (!this.currentReservationUser) return;
        
        try {
            const filters = {
                date_from: document.getElementById('reservation-date-from')?.value || '',
                date_to: document.getElementById('reservation-date-to')?.value || '',
                status: document.getElementById('reservation-status-filter')?.value || '',
                sort: document.getElementById('reservation-sort')?.value || 'created_at',
                direction: 'desc',
                per_page: 10,
                page: page
            };

            const params = new URLSearchParams(filters);

            const response = await fetch(`/admin/user-manager/users/${this.currentReservationUser}/reservations?${params}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.reservations = data.data;
                this.reservationPagination = data.pagination;
                this.renderReservations();
                this.renderReservationPagination();
            } else {
                this.showAlert('error', '予約の読み込みに失敗しました。');
            }
        } catch (error) {
            console.error('Error loading reservations:', error);
            this.showAlert('error', '予約の読み込み中にエラーが発生しました。');
        }
    }

    renderReservations() {
        const tbody = document.getElementById('reservations-table-body');
        
        if (this.reservations.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        予約が見つかりませんでした。
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = this.reservations.map(reservation => {
            const statusClass = {
                'confirmed': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800', 
                'completed': 'bg-blue-100 text-blue-800'
            }[reservation.status] || 'bg-gray-100 text-gray-800';

            const statusText = {
                'confirmed': '確定',
                'cancelled': 'キャンセル',
                'completed': '完了'
            }[reservation.status] || reservation.status;

            return `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${this.formatDate(reservation.time_slot?.date)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        ${reservation.time_slot?.start_time?.substring(0, 5)} - ${reservation.time_slot?.end_time?.substring(0, 5)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full ${statusClass}">
                            ${statusText}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        ${reservation.notes ? this.escapeHtml(reservation.notes) : '-'}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        ${this.formatDate(reservation.created_at)}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            <button onclick="userManager.openEditReservationModal(${reservation.id})" class="text-blue-600 hover:text-blue-900">
                                編集
                            </button>
                            <button onclick="userManager.deleteReservation(${reservation.id})" class="text-red-600 hover:text-red-900">
                                削除
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }

    renderReservationPagination() {
        const container = document.getElementById('reservation-pagination-container');
        
        if (this.reservationPagination.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        let paginationHtml = `
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    ${this.reservationPagination.from || 0} - ${this.reservationPagination.to || 0} / ${this.reservationPagination.total} 件
                </div>
                <div class="flex space-x-1">
        `;

        // 前へボタン
        if (this.reservationPagination.current_page > 1) {
            paginationHtml += `
                <button onclick="userManager.loadReservations(${this.reservationPagination.current_page - 1})" 
                        class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded">
                    前へ
                </button>
            `;
        }

        // ページ番号ボタン
        const startPage = Math.max(1, this.reservationPagination.current_page - 2);
        const endPage = Math.min(this.reservationPagination.last_page, this.reservationPagination.current_page + 2);

        for (let i = startPage; i <= endPage; i++) {
            const isActive = i === this.reservationPagination.current_page;
            paginationHtml += `
                <button onclick="userManager.loadReservations(${i})" 
                        class="px-3 py-2 text-sm ${isActive ? 
                            'bg-blue-500 text-white' : 
                            'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
                        } rounded">
                    ${i}
                </button>
            `;
        }

        // 次へボタン
        if (this.reservationPagination.current_page < this.reservationPagination.last_page) {
            paginationHtml += `
                <button onclick="userManager.loadReservations(${this.reservationPagination.current_page + 1})" 
                        class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded">
                    次へ
                </button>
            `;
        }

        paginationHtml += `
                </div>
            </div>
        `;

        container.innerHTML = paginationHtml;
    }

    clearReservationFilters() {
        document.getElementById('reservation-date-from').value = '';
        document.getElementById('reservation-date-to').value = '';
        document.getElementById('reservation-status-filter').value = '';
        document.getElementById('reservation-sort').value = 'created_at';
        this.loadReservations();
    }

    async openCreateReservationModal() {
        this.isEditingReservation = false;
        this.currentReservation = null;
        
        document.getElementById('reservation-form-title').textContent = '新規予約作成';
        document.getElementById('submit-reservation-btn').textContent = '作成';
        
        this.resetReservationForm();
        
        // 利用可能なタイムスロットを読み込み
        await this.loadAvailableTimeSlots();
        
        this.showReservationFormModal();
    }

    async openEditReservationModal(reservationId) {
        this.isEditingReservation = true;
        this.currentReservation = reservationId;
        
        try {
            const response = await fetch(`/admin/user-manager/reservations/${reservationId}`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const reservation = data.data;
                
                document.getElementById('reservation-form-title').textContent = '予約編集';
                document.getElementById('submit-reservation-btn').textContent = '更新';
                
                // 利用可能なタイムスロットを読み込み
                await this.loadAvailableTimeSlots();
                
                // フォームに値をセット
                document.getElementById('reservation-timeslot').value = reservation.time_slot_id;
                document.getElementById('reservation-status').value = reservation.status;
                document.getElementById('reservation-notes').value = reservation.notes || '';
                
                this.clearReservationFormErrors();
                this.showReservationFormModal();
            } else {
                this.showAlert('error', '予約情報の取得に失敗しました。');
            }
        } catch (error) {
            console.error('Error loading reservation:', error);
            this.showAlert('error', '予約情報の取得中にエラーが発生しました。');
        }
    }

    showReservationFormModal() {
        document.getElementById('reservation-form-modal').style.display = 'block';
    }

    closeReservationFormModal() {
        document.getElementById('reservation-form-modal').style.display = 'none';
        this.resetReservationForm();
    }

    resetReservationForm() {
        document.getElementById('reservation-form').reset();
        document.getElementById('reservation-status').value = 'confirmed';
        this.clearReservationFormErrors();
    }

    async loadAvailableTimeSlots() {
        try {
            const response = await fetch('/admin/user-manager/available-timeslots', {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const select = document.getElementById('reservation-timeslot');
                select.innerHTML = '<option value="">時間枠を選択してください</option>';
                
                data.data.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot.id;
                    option.textContent = `${slot.date} ${slot.start_time.substring(0, 5)}-${slot.end_time.substring(0, 5)}`;
                    select.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading time slots:', error);
        }
    }

    async handleReservationFormSubmit(e) {
        e.preventDefault();
        
        this.clearReservationFormErrors();
        
        const formData = {
            time_slot_id: document.getElementById('reservation-timeslot').value,
            status: document.getElementById('reservation-status').value,
            notes: document.getElementById('reservation-notes').value
        };

        try {
            const url = this.isEditingReservation ? 
                `/admin/user-manager/reservations/${this.currentReservation}` : 
                `/admin/user-manager/users/${this.currentReservationUser}/reservations`;
            
            const method = this.isEditingReservation ? 'PUT' : 'POST';
            
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await response.json();

            if (result.success) {
                this.showAlert('success', result.message);
                this.closeReservationFormModal();
                this.loadReservations();
                this.loadUsers(); // ユーザー一覧の予約数を更新
            } else {
                if (result.errors) {
                    this.showReservationFormErrors(result.errors);
                } else {
                    this.showAlert('error', result.message || 'エラーが発生しました。');
                }
            }
        } catch (error) {
            console.error('Error saving reservation:', error);
            this.showAlert('error', '保存中にエラーが発生しました。');
        }
    }

    async deleteReservation(reservationId) {
        if (!confirm('この予約を削除してもよろしいですか？')) {
            return;
        }
        
        try {
            const response = await fetch(`/admin/user-manager/reservations/${reservationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('success', data.message);
                this.loadReservations();
                this.loadUsers(); // ユーザー一覧の予約数を更新
            } else {
                this.showAlert('error', data.message || '削除に失敗しました。');
            }
        } catch (error) {
            console.error('Error deleting reservation:', error);
            this.showAlert('error', '削除中にエラーが発生しました。');
        }
    }

    showReservationFormErrors(errors) {
        this.clearReservationFormErrors();
        
        Object.keys(errors).forEach(field => {
            this.showReservationFieldError(field, errors[field][0]);
        });
    }

    showReservationFieldError(field, message) {
        const errorEl = document.getElementById(`error-${field}`);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = 'block';
        }
    }

    clearReservationFormErrors() {
        document.querySelectorAll('[id^="error-"]').forEach(el => {
            if (el.id.includes('reservation') || ['error-timeslot', 'error-status', 'error-notes'].includes(el.id)) {
                el.style.display = 'none';
                el.textContent = '';
            }
        });
    }
}

// グローバルインスタンス
const userManager = new UserManager();
