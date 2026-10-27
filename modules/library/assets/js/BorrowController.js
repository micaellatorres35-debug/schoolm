'use strict';
class BorrowController {
    #api; #ui;
    #transactionTitles = {};

    constructor(api, ui) { this.#api = api; this.#ui = ui; }

    async loadActive() {
        const data  = await this.#api.get({ action: 'get_transactions', status: 'active', limit: 20 });
        const panel = document.getElementById('activeBorrowingsPanel');
        if (!panel) return;

        if (!data.transactions?.length) {
            panel.innerHTML = '<div class="empty-state"><i class="fas fa-book-reader"></i><p>No active borrowings</p></div>';
            return;
        }
        panel.innerHTML = data.transactions.map(t => `
            <div class="d-flex gap-1 align-center mb-1" style="padding:.75rem 0;border-bottom:1px solid var(--border-color)">
                ${this.#ui.bookCoverHTML(t, 28)}
                <div style="flex:1;min-width:0">
                    <div class="fw-600 small" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${this.#ui.escH(t.book_title)}</div>
                    <div class="text-muted small">${this.#ui.escH(t.borrower_name)} &nbsp;·&nbsp; Due: ${t.due_date}</div>
                </div>
            </div>`).join('');
    }

    async loadReturnList() {
        const panel = document.getElementById('returnableList');
        if (!panel) return;

        panel.innerHTML = '<div class="spinner"></div>';

        try {
            const data = await this.#api.get({ action: 'get_returnable' });
            const rows = data.transactions || [];

            if (!rows.length) {
                panel.innerHTML = '<div class="empty-state"><i class="fas fa-check-circle"></i><p>No books to return</p></div>';
                return;
            }

            this.#transactionTitles = {};
            rows.forEach(t => { this.#transactionTitles[t.id] = t.book_title; });

            panel.innerHTML = `
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Book</th>
                        <th>Member</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                ${rows.map(t => `
                <tr class="${t.status === 'overdue' ? 'row-overdue' : ''}">
                    <td>${this.#ui.bookCoverHTML(t, 28)}</td>
                    <td>
                        <strong>${this.#ui.escH(t.book_title)}</strong>
                        <br><small class="text-muted">${this.#ui.escH(t.book_author || '')}</small>
                    </td>
                    <td>
                        ${this.#ui.escH(t.borrower_name)}
                        <br><small class="text-muted">${this.#ui.escH(t.member_id)}</small>
                    </td>
                    <td>
                        ${t.due_date}
                        ${t.status === 'overdue' ? '<br><span class="badge badge-danger">OVERDUE</span>' : ''}
                    </td>
                    <td><span class="badge ${this.#ui.badgeClass(t.status)}">${t.status}</span></td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-success" onclick="app.borrow.openReturnModal(${t.id})">
                                <i class="fas fa-undo"></i> Return
                            </button>
                            <button class="btn btn-sm btn-outline btn-danger" onclick="app.borrow.reportLost(${t.id})">
                                <i class="fas fa-exclamation-triangle"></i>
                            </button>
                        </div>
                    </td>
                </tr>`).join('')}
                </tbody>
            </table>
            </div>`;

        } catch (error) {
            console.error('Error loading return list:', error);
            panel.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-circle" style="color:var(--danger)"></i><p class="text-danger">Error loading return list. Please refresh and try again.</p></div>';
        }
    }

    openReturnModal(transId) {
        const title = this.#transactionTitles[transId] || 'Unknown Book';
        document.getElementById('returnModalContent').innerHTML = `
            <p class="mb-2">Returning: <strong>${this.#ui.escH(title)}</strong></p>
            <div class="form-group"><label>Book Condition</label>
                <select id="returnCondition">
                    <option value="Excellent">Excellent</option>
                    <option value="Good" selected>Good</option>
                    <option value="Fair">Fair</option>
                    <option value="Poor">Poor</option>
                </select>
            </div>
            <button class="btn btn-success w-100" onclick="app.borrow.confirmReturn(${transId})">
                <i class="fas fa-check"></i> Confirm Return
            </button>`;
        this.#ui.openModal('returnModal');
    }

    async confirmReturn(transId) {
        const condition = document.getElementById('returnCondition').value;

        const res = await this.#api.post('return_book', {
            transaction_id: transId,
            condition,
        });

        if (res.error) { this.#ui.showNotif(res.error, 'error'); return; }
        this.#ui.showNotif(res.message, 'success');
        this.#ui.closeModal('returnModal');
        this.loadReturnList();
        this.loadActive();
        app.dashboard.load();
        app.transactions.load();
        app.books.load();
        app.gallery.load();
    }

    async reportLost(transId) {
        const notes = await this.#ui.prompt(
            'Reason / notes for lost book (optional):',
            'Report Lost Book',
            'e.g. Student misplaced it, damaged beyond repair...',
            'Continue',
            'Cancel'
        );
        if (notes === null) return;

        const ok = await this.#ui.confirm(
            'Mark this book as LOST? This cannot be undone.',
            'Confirm Report Lost',
            'Mark as Lost',
            'Cancel',
            'danger'
        );
        if (!ok) return;

        const res = await this.#api.post('report_lost', {
            transaction_id: transId,
            lost_notes: notes,
        });

        if (res.error) { this.#ui.showNotif(res.error, 'error'); return; }

        this.#ui.showNotif(res.message, 'success');
        this.loadReturnList();
        this.loadActive();
        app.dashboard.load();
        app.transactions.load();
        app.books.load();
        app.gallery.load();
    }
}