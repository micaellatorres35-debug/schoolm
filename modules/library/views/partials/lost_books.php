<div class="tab-content" id="tab-lost">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-exclamation-triangle" style="color:var(--danger)"></i> Lost Books
            </h3>
            <button class="btn btn-sm btn-outline" onclick="LostBooks.load()">
                <i class="fas fa-sync-alt"></i> Refresh
            </button>
        </div>
        <p class="text-muted mb-2">
            <i class="fas fa-info-circle"></i>
            Books reported as lost during return. All borrower information is recorded here.
        </p>

        <div class="d-flex gap-1 mb-2 flex-wrap" id="lostSummary"></div>

        <div id="lostBooksContent"><div class="spinner"></div></div>
    </div>
</div>

<script>
const LostBooks = (() => {

    function esc(s) {
        if (!s) return '—';
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    async function load() {
        const content = document.getElementById('lostBooksContent');
        const summary = document.getElementById('lostSummary');
        if (!content) return;

        content.innerHTML = '<div class="spinner"></div>';

        try {
            const res   = await app.api.get({ action: 'get_lost_books' });
            const books = res.lost_books || [];

            const uniqueBooks     = new Set(books.map(b => b.book_id)).size;
            const uniqueBorrowers = new Set(books.map(b => b.borrower_id)).size;
            summary.innerHTML = `
                <div class="alert-chip alert-chip-warning">
                    <i class="fas fa-book"></i>
                    <span><strong>${books.length}</strong> lost record(s)</span>
                </div>
                <div class="alert-chip alert-chip-info">
                    <i class="fas fa-layer-group"></i>
                    <span><strong>${uniqueBooks}</strong> book title(s)</span>
                </div>
                <div class="alert-chip" style="background:rgba(239,68,68,.08);color:#b91c1c;border:1px solid rgba(239,68,68,.2)">
                    <i class="fas fa-users"></i>
                    <span><strong>${uniqueBorrowers}</strong> member(s) involved</span>
                </div>`;

            if (!books.length) {
                content.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-check-circle" style="color:var(--success)"></i>
                        <p>No lost books recorded — all good!</p>
                    </div>`;
                return;
            }

            content.innerHTML = `
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Book</th>
                        <th>Borrower Info</th>
                        <th>Contact</th>
                        <th>Borrow Date</th>
                        <th>Date Lost</th>
                        <th>Notes</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                ${books.map(b => `
                <tr style="background:rgba(239,68,68,.025)">
                    <td>
                        ${b.cover_local
                            ? `<img src="uploads/covers/${esc(b.cover_local)}" style="width:36px;height:50px;object-fit:cover;border-radius:4px">`
                            : b.cover_url
                            ? `<img src="${esc(b.cover_url)}" style="width:36px;height:50px;object-fit:cover;border-radius:4px" onerror="this.style.display='none'">`
                            : `<div style="width:36px;height:50px;background:var(--gradient-danger);border-radius:4px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.9rem"><i class="fas fa-book"></i></div>`}
                    </td>
                    <td>
                        <div class="fw-600">${esc(b.book_title)}</div>
                        <div class="text-muted small">${esc(b.book_author)}</div>
                        <div class="text-muted small">${esc(b.genre)}${b.isbn ? ' &nbsp;·&nbsp; ISBN: ' + esc(b.isbn) : ''}</div>
                    </td>
                    <td>
                        <div class="fw-600">${esc(b.borrower_name)}</div>
                        <div class="text-muted small">
                            <i class="fas fa-id-card"></i> ${esc(b.student_number)}
                        </div>
                        <div class="text-muted small">
                            <span class="badge badge-info" style="font-size:.65rem">${esc(b.borrower_type)}</span>
                            ${b.borrower_grade ? `<span class="text-muted small">&nbsp;${esc(b.borrower_grade)}</span>` : ''}
                        </div>
                    </td>
                    <td>
                        ${b.borrower_email ? `<div class="small"><i class="fas fa-envelope text-muted"></i> ${esc(b.borrower_email)}</div>` : ''}
                        ${b.borrower_phone ? `<div class="small"><i class="fas fa-phone text-muted"></i> ${esc(b.borrower_phone)}</div>` : ''}
                        ${!b.borrower_email && !b.borrower_phone ? '<span class="text-muted small">—</span>' : ''}
                    </td>
                    <td class="small">${esc(b.borrow_date)}<br><span class="text-muted">Due: ${esc(b.due_date)}</span></td>
                    <td>
                        <span class="badge badge-danger" style="font-size:.75rem">
                            <i class="fas fa-calendar-times"></i> ${esc(b.lost_date || 'Unknown')}
                        </span>
                    </td>
                    <td class="small text-muted" style="max-width:160px;white-space:normal">
                        ${b.lost_notes ? esc(b.lost_notes) : '<em>No notes</em>'}
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline btn-danger"
                            onclick="LostBooks.remove(${b.id})"
                            title="Remove this lost record">
                            <i class="fas fa-trash"></i> Remove
                        </button>
                    </td>
                </tr>`).join('')}
                </tbody>
            </table>
            </div>`;

        } catch(e) {
            console.error('Lost books error:', e);
            content.innerHTML = '<p class="text-danger">Error loading lost books.</p>';
        }
    }

    async function remove(transId) {
        const ok = await app.ui.confirm(
            'Remove this lost book record? This will permanently delete it from the system.',
            'Remove Lost Record',
            'Remove',
            'Cancel',
            'danger'
        );
        if (!ok) return;

        const res = await app.api.post('remove_lost_book', { transaction_id: transId });
        if (res.error) { app.ui.showNotif(res.error, 'error'); return; }

        app.ui.showNotif(res.message, 'success');
        load();
        app.dashboard.load();
    }

    return { load, remove };
})();
</script>