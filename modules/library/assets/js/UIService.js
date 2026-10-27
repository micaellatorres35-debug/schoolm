'use strict';

class Paginator {
    page     = 1;
    total    = 0;
    pageSize = 10;

    constructor(pageSize = 10) { this.pageSize = pageSize; }

    reset() { this.page = 1; }

    move(dir) {
        const maxPage = Math.max(1, Math.ceil(this.total / this.pageSize));
        this.page = Math.min(maxPage, Math.max(1, this.page + dir));
    }

    infoText(noun = 'items') {
        if (!this.total) return `No ${noun} found`;
        const from = (this.page - 1) * this.pageSize + 1;
        const to   = Math.min(this.page * this.pageSize, this.total);
        return `Showing ${from}–${to} of ${this.total} ${noun}`;
    }
}

class UIService {
    #notifTimer = null;

    showNotif(msg, type = 'info') {
        const n      = document.getElementById('notification');
        const icons  = { success: 'fa-check-circle', error: 'fa-exclamation-circle', info: 'fa-info-circle', warning: 'fa-exclamation-triangle' };
        document.getElementById('notifIcon').className = 'fas ' + (icons[type] ?? icons.info);
        document.getElementById('notifText').textContent = msg;
        n.className = `notification show ${type}`;
        clearTimeout(this.#notifTimer);
        this.#notifTimer = setTimeout(() => n.classList.remove('show'), 5000);
    }

    hideNotification() { document.getElementById('notification').classList.remove('show'); }

    openModal(id)  { document.getElementById(id).classList.add('active');    document.body.style.overflow = 'hidden'; }
    closeModal(id) { document.getElementById(id).classList.remove('active'); document.body.style.overflow = ''; }

    toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
        document.getElementById('sidebarOverlay').classList.toggle('active');
    }
    closeSidebar() {
        document.getElementById('sidebar').classList.remove('active');
        document.getElementById('sidebarOverlay').classList.remove('active');
    }

    /**
     * Rebuild the "Borrow Duration" dropdown from the current settings.
     * Can be called from anywhere — safe even if the borrow tab isn't open.
     */
    async refreshBorrowDays() {
        const select = document.getElementById('borrowDays');
        if (!select) {
            console.log('[UIService] borrowDays select not in DOM — skipping');
            return;
        }

        try {
            const res = await fetch('api.php?action=get_settings&_t=' + Date.now(), {
                credentials: 'same-origin',
                cache: 'no-store'
            });
            const settings = await res.json();
            const maxDays = parseInt(settings.max_borrow_days) || 14;

            console.log('[UIService] Rebuilding borrowDays dropdown with', maxDays, 'days');

            let html = '';
            for (let i = 1; i <= maxDays; i++) {
                const label = i === maxDays
                    ? i + ' day' + (i !== 1 ? 's' : '') + ' (maximum)'
                    : i + ' day' + (i !== 1 ? 's' : '');
                html += '<option value="' + i + '"' + (i === maxDays ? ' selected' : '') + '>' + label + '</option>';
            }
            select.innerHTML = html;
        } catch (e) {
            console.error('[UIService] refreshBorrowDays failed:', e);
        }
    }

    confirm(message, title = 'Confirm', okText = 'Yes', cancelText = 'Cancel', variant = 'danger') {
        return new Promise(resolve => {
            let modal = document.getElementById('confirmModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'confirmModal';
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content" style="max-width:420px;text-align:center;padding:2rem">
                        <div id="confirmModalIcon" style="width:64px;height:64px;border-radius:50%;background:rgba(239,68,68,.1);color:var(--danger);display:flex;align-items:center;justify-content:center;font-size:1.75rem;margin:0 auto 1.25rem">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h3 id="confirmModalTitle" style="margin-bottom:.5rem;font-size:1.15rem">Confirm</h3>
                        <p id="confirmModalMessage" style="color:var(--gray);font-size:.9rem;margin-bottom:1.75rem;line-height:1.5">Are you sure?</p>
                        <div style="display:flex;gap:.75rem">
                            <button class="btn btn-outline w-100" id="confirmModalCancel" style="text-transform:none;padding:.75rem">Cancel</button>
                            <button class="btn btn-danger w-100" id="confirmModalOk" style="text-transform:none;padding:.75rem">Yes</button>
                        </div>
                    </div>`;
                document.body.appendChild(modal);
            }

            const iconEl  = document.getElementById('confirmModalIcon');
            const okBtn   = document.getElementById('confirmModalOk');
            const cancelBtn = document.getElementById('confirmModalCancel');

            document.getElementById('confirmModalTitle').textContent   = title;
            document.getElementById('confirmModalMessage').textContent = message;

            const variants = {
                danger:  { bg: 'rgba(239,68,68,.1)',  color: 'var(--danger)',  icon: 'fa-exclamation-triangle', btn: 'btn-danger' },
                warning: { bg: 'rgba(245,158,11,.1)', color: 'var(--warning)', icon: 'fa-exclamation-circle', btn: 'btn-warning' },
                success: { bg: 'rgba(16,185,129,.1)', color: 'var(--success)', icon: 'fa-check-circle',       btn: 'btn-success' },
                info:    { bg: 'rgba(59,130,246,.1)', color: 'var(--info)',    icon: 'fa-info-circle',        btn: 'btn-info' },
            };
            const v = variants[variant] ?? variants.danger;

            iconEl.style.background = v.bg;
            iconEl.style.color      = v.color;
            iconEl.innerHTML        = `<i class="fas ${v.icon}"></i>`;

            okBtn.className     = `btn ${v.btn} w-100`;
            okBtn.textContent   = okText;
            cancelBtn.textContent = cancelText;

            const close = (result) => {
                modal.classList.remove('active');
                document.body.style.overflow = '';
                okBtn.onclick     = null;
                cancelBtn.onclick = null;
                modal.onclick     = null;
                document.removeEventListener('keydown', onKey);
                resolve(result);
            };

            const onKey = (e) => {
                if (e.key === 'Escape') close(false);
                if (e.key === 'Enter')  close(true);
            };

            okBtn.onclick     = () => close(true);
            cancelBtn.onclick = () => close(false);
            modal.onclick     = (e) => { if (e.target === modal) close(false); };
            document.addEventListener('keydown', onKey);

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => okBtn.focus(), 60);
        });
    }

    prompt(message, title = 'Input', placeholder = '', okText = 'OK', cancelText = 'Cancel') {
        return new Promise(resolve => {
            let modal = document.getElementById('promptModal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'promptModal';
                modal.className = 'modal';
                modal.innerHTML = `
                    <div class="modal-content" style="max-width:440px;padding:1.75rem">
                        <div class="modal-header" style="margin-bottom:1.25rem;padding-bottom:.75rem">
                            <h3 id="promptModalTitle" style="margin:0;font-size:1.1rem">Input</h3>
                            <button class="modal-close" id="promptModalClose"><i class="fas fa-times"></i></button>
                        </div>
                        <p id="promptModalMessage" style="color:var(--gray);font-size:.875rem;margin-bottom:1rem;line-height:1.5">Please enter a value:</p>
                        <div class="form-group" style="margin-bottom:1.25rem">
                            <input type="text" id="promptModalInput" style="width:100%">
                        </div>
                        <div style="display:flex;gap:.75rem">
                            <button class="btn btn-outline w-100" id="promptModalCancel" style="text-transform:none;padding:.7rem">Cancel</button>
                            <button class="btn btn-success w-100" id="promptModalOk" style="text-transform:none;padding:.7rem">OK</button>
                        </div>
                    </div>`;
                document.body.appendChild(modal);
            }

            const inputEl   = document.getElementById('promptModalInput');
            const okBtn     = document.getElementById('promptModalOk');
            const cancelBtn = document.getElementById('promptModalCancel');
            const closeBtn  = document.getElementById('promptModalClose');

            document.getElementById('promptModalTitle').textContent   = title;
            document.getElementById('promptModalMessage').textContent = message;

            inputEl.placeholder = placeholder;
            inputEl.value       = '';
            okBtn.textContent     = okText;
            cancelBtn.textContent = cancelText;

            const close = (result) => {
                modal.classList.remove('active');
                document.body.style.overflow = '';
                okBtn.onclick     = null;
                cancelBtn.onclick = null;
                closeBtn.onclick  = null;
                modal.onclick     = null;
                inputEl.onkeydown = null;
                document.removeEventListener('keydown', onKey);
                resolve(result);
            };

            const onKey = (e) => {
                if (e.key === 'Escape') close(null);
            };

            inputEl.onkeydown = (e) => {
                if (e.key === 'Enter') { e.preventDefault(); close(inputEl.value); }
            };

            okBtn.onclick     = () => close(inputEl.value);
            cancelBtn.onclick = () => close(null);
            closeBtn.onclick  = () => close(null);
            modal.onclick     = (e) => { if (e.target === modal) close(null); };
            document.addEventListener('keydown', onKey);

            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            setTimeout(() => inputEl.focus(), 80);
        });
    }

    switchTab(id) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.sidebar-nav .tab').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-' + id)?.classList.add('active');
        document.querySelectorAll('.sidebar-nav .tab').forEach(el => {
            if (el.getAttribute('onclick')?.includes(`'${id}'`)) el.classList.add('active');
        });
        this.closeSidebar();

        const map = {
            dashboard:    () => app.dashboard.load(),
            books:        () => app.books.load(),
            gallery:      () => { app.gallery.load(); app.gallery.loadGenreOptions(); },
            borrowers:    () => app.members.load(),
            borrow:       () => {
                app.borrow.loadActive();
                // Use UIService's own method — reliable even if BS isn't loaded
                this.refreshBorrowDays();
                if (window.BS?.loadBorrowDays) BS.loadBorrowDays();
            },
            return:       () => app.borrow.loadReturnList(),
            transactions: () => app.transactions.load(),
            reports:      () => {
                app.reports.load();
                if (typeof LibraryReports !== 'undefined') {
                    LibraryReports.loadSubmissions();
                    LibraryReports.loadDecisions();
                }
            },
            settings:     () => app.settings.load(),
            ai:           () => { if (typeof AI !== 'undefined') AI.init(); },
            lost:         () => { if (typeof LostBooks !== 'undefined') LostBooks.load(); }
        };
        map[id]?.();
    }

    escH(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    coverSrc(book) {
        if (book.cover_local) return 'uploads/covers/' + book.cover_local;
        if (book.cover_url)   return book.cover_url;
        return null;
    }

    bookCoverHTML(book, size = 40) {
        const src    = this.coverSrc(book);
        const colors = ['#2563eb','#7c3aed','#059669','#d97706','#dc2626','#0891b2'];
        const color  = colors[(book.id || 0) % colors.length];
        const fallback = `<span style="display:inline-flex;width:${size}px;height:${size*1.4}px;background:${color};border-radius:4px;align-items:center;justify-content:center;color:#fff;font-size:${size*0.4}px"><i class="fas fa-book"></i></span>`;
        if (!src) return fallback;
        const uid = `bc-${book.id||0}-${size}`;
        return `<img id="${uid}" src="${src}" alt="" style="width:${size}px;height:${size*1.4}px;object-fit:cover;border-radius:4px;vertical-align:middle" onerror="(function(el){el.outerHTML='<span style=\'display:inline-flex;width:${size}px;height:${size*1.4}px;background:${color};border-radius:4px;align-items:center;justify-content:center;color:#fff;font-size:${size*0.4}px\'><i class=\'fas fa-book\'></i></span>'})(document.getElementById('${uid}'))">`;
    }

    badgeClass(status) {
        return { available: 'badge-success', borrowed: 'badge-warning', overdue: 'badge-danger', returned: 'badge-info', active: 'badge-warning' }[status] ?? 'badge-info';
    }
}