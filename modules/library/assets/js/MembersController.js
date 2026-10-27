'use strict';
class MembersController {
    #api; #ui;
    #pager           = new Paginator(10);
    #searchTerm      = '';
    #debounceId      = null;
    #studentDebounce = null;

    constructor(api, ui) { this.#api = api; this.#ui = ui; }

    async load() {
        const search = this.#searchTerm;
        const type   = document.getElementById('memberTypeFilter')?.value || '';
        const data   = await this.#api.get({ action: 'get_borrowers', search, type, page: this.#pager.page, limit: this.#pager.pageSize });

        this.#pager.total = data.total || 0;
        const tbody = document.getElementById('membersTableBody');

        if (!data.borrowers?.length) {
            tbody.innerHTML = `<tr><td colspan="4" class="empty-state">
                <i class="fas fa-users" style="display:block;font-size:2rem;color:var(--gray-light);margin-bottom:.5rem"></i>
                <p>${search ? `No members matching "<strong>${this.#ui.escH(search)}</strong>"` : 'No members found'}</p>
            </td></tr>`;
        } else {
            tbody.innerHTML = data.borrowers.map(m => {
                const typeColors = { Student: '#2563eb', Teacher: '#059669', Staff: '#d97706' };
                const tc = typeColors[m.type] || '#64748b';
                const overdueHTML = m.overdue_count > 0
                    ? `<span class="badge badge-danger" style="font-size:.7rem">${m.overdue_count} overdue</span>`
                    : '';
                return `<tr>
                    <td>
                        <strong>${this.#ui.escH(m.name)}</strong><br>
                        <small class="text-muted">${this.#ui.escH(m.borrower_id)}</small>
                    </td>
                    <td><span style="color:${tc};font-weight:600">${m.type}</span></td>
                    <td>
                        <span class="badge badge-info">${m.currently_borrowed || 0} active</span>
                        ${overdueHTML}
                    </td>
                    <td><div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline" onclick="app.members.view(${m.id})" title="View"><i class="fas fa-eye"></i></button>
                        <button class="btn btn-sm btn-outline" onclick="app.members.edit(${m.id})" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-sm btn-outline btn-danger" onclick="app.members.delete(${m.id})" title="Delete"><i class="fas fa-trash"></i></button>
                    </div></td>
                </tr>`;
            }).join('');
        }
        document.getElementById('membersPaginationInfo').textContent = this.#pager.infoText('members');
    }

    search(value) {
        this.#searchTerm = (value ?? '').trim();
        this.#pager.reset();
        clearTimeout(this.#debounceId);
        this.#debounceId = setTimeout(() => this.load(), 300);
    }

    filter()        { this.#pager.reset(); this.load(); }
    changePage(dir) { this.#pager.move(dir); this.load(); }

    searchStudents(value) {
        const query   = (value ?? '').trim();
        const results = document.getElementById('studentSearchResults');
        const empty   = document.getElementById('studentSearchEmpty');
        const spinner = document.getElementById('studentSearchSpinner');

        if (query.length < 2) {
            results.style.display = 'none';
            empty.style.display   = 'none';
            spinner.style.display = 'none';
            return;
        }

        clearTimeout(this.#studentDebounce);
        this.#studentDebounce = setTimeout(async () => {
            spinner.style.display = 'block';
            results.style.display = 'none';
            empty.style.display   = 'none';

            try {
                const data     = await this.#api.get({ action: 'search_students', search: query, limit: 15 });
                const students = data.students ?? [];

                spinner.style.display = 'none';

                if (!students.length) {
                    empty.style.display = 'block';
                    return;
                }

                document.getElementById('studentSearchBody').innerHTML = students.map(s => `
                    <tr>
                        <td><strong>${this.#ui.escH(String(s.student_number))}</strong></td>
                        <td>${this.#ui.escH(s.full_name)}</td>
                        <td><span class="genre-pill">${this.#ui.escH(s.course ?? '—')}</span></td>
                        <td>
                            <button class="btn btn-sm btn-success"
                                onclick="app.members.importStudent(${s.student_number}, '${this.#ui.escH(s.full_name).replace(/'/g,"\\'")}')">
                                <i class="fas fa-plus"></i> Import
                            </button>
                        </td>
                    </tr>
                `).join('');

                results.style.display = '';

            } catch(e) {
                spinner.style.display = 'none';
                empty.style.display   = 'block';
            }
        }, 350);
    }

    async importStudent(studentNumber, name) {
        const ok = await this.#ui.confirm(
            `Import "${name}" (Student #${studentNumber}) as a library member?`,
            'Import Student',
            'Import',
            'Cancel',
            'success'
        );
        if (!ok) return;

        const res = await this.#api.post('import_student', { student_number: studentNumber });
        if (res.error) { this.#ui.showNotif(res.error, 'error'); return; }

        this.#ui.showNotif(res.message, 'success');

        const searchEl = document.getElementById('studentSearch');
        if (searchEl) searchEl.value = '';
        document.getElementById('studentSearchResults').style.display = 'none';
        document.getElementById('studentSearchEmpty').style.display   = 'none';

        this.load();
        app.dashboard.load();
    }

    async delete(id) {
        const ok = await this.#ui.confirm(
            'Delete this member? This cannot be undone.',
            'Delete Member',
            'Delete',
            'Cancel',
            'danger'
        );
        if (!ok) return;

        const res = await this.#api.post('delete_borrower', { id });
        if (res.error) { this.#ui.showNotif(res.error, 'error'); return; }
        this.#ui.showNotif(res.message, 'success');
        this.load();
        app.dashboard.load();
    }

    async edit(id) {
        const m = await this.#api.get({ action: 'get_borrower', id });
        if (m.error) { this.#ui.showNotif(m.error, 'error'); return; }

        document.getElementById('modalTitle').textContent = 'Edit Member';
        document.getElementById('modalContent').innerHTML = `
            <input type="hidden" id="editMemberId" value="${m.id}">
            <div class="form-group">
                <label>Full Name <span style="color:red">*</span></label>
                <input type="text" id="editMemberName" value="${this.#ui.escH(m.name)}">
            </div>
            <div class="form-group">
                <label>ID Number</label>
                <input type="text" value="${this.#ui.escH(m.borrower_id)}" disabled>
            </div>
            <div class="form-group">
                <label>Type</label>
                <select id="editMemberType">
                    <option value="Student" ${m.type === 'Student' ? 'selected' : ''}>Student</option>
                    <option value="Teacher" ${m.type === 'Teacher' ? 'selected' : ''}>Teacher</option>
                    <option value="Staff"   ${m.type === 'Staff'   ? 'selected' : ''}>Staff</option>
                </select>
            </div>
            <div class="d-flex gap-1">
                <button class="btn btn-success w-100" onclick="app.members.saveEdit()">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button class="btn btn-outline" onclick="app.ui.closeModal('detailModal')">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>`;
        this.#ui.openModal('detailModal');
    }

    async saveEdit() {
        const id   = document.getElementById('editMemberId').value;
        const name = document.getElementById('editMemberName').value.trim();
        const type = document.getElementById('editMemberType').value;

        if (!name) { this.#ui.showNotif('Name is required.', 'error'); return; }

        const res = await this.#api.post('update_borrower', { id: parseInt(id), name, type });

        if (res.error) { this.#ui.showNotif(res.error, 'error'); return; }
        this.#ui.showNotif(res.message, 'success');
        this.#ui.closeModal('detailModal');
        this.load();
    }

    async view(id) {
        const m = await this.#api.get({ action: 'get_borrower', id });
        if (m.error) { this.#ui.showNotif(m.error, 'error'); return; }

        const typeColors = { Student: '#2563eb', Teacher: '#059669', Staff: '#d97706' };
        const tc = typeColors[m.type] || '#64748b';

        document.getElementById('modalTitle').textContent = m.name;
        document.getElementById('modalContent').innerHTML = `
            <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1.5rem">
                <div style="width:60px;height:60px;border-radius:50%;background:${tc};display:flex;align-items:center;justify-content:center;color:#fff;font-size:1.4rem;flex-shrink:0">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <h3 style="margin:0">${this.#ui.escH(m.name)}</h3>
                    <div class="text-muted">${this.#ui.escH(m.borrower_id)} &nbsp;·&nbsp; <span style="color:${tc};font-weight:600">${m.type}</span></div>
                </div>
            </div>
            <p><strong>Joined:</strong> ${m.join_date}</p>
            <h4 class="mb-1">Borrowing History</h4>
            ${m.history?.length ? m.history.map(h => `
            <div style="border-left:3px solid var(--primary);padding-left:1rem;margin-bottom:.75rem">
                <strong>${this.#ui.escH(h.book_title)}</strong><br>
                <small>Borrowed: ${h.borrow_date} &nbsp;·&nbsp; Due: ${h.due_date} &nbsp;·&nbsp; <span class="badge ${this.#ui.badgeClass(h.status)}">${h.status}</span></small>
            </div>`).join('') : '<p class="text-muted">No borrowing history.</p>'}
            <div class="d-flex gap-1 mt-2">
                <button class="btn btn-sm" onclick="app.ui.closeModal('detailModal');app.members.edit(${m.id})"><i class="fas fa-edit"></i> Edit</button>
            </div>`;
        this.#ui.openModal('detailModal');
    }
}