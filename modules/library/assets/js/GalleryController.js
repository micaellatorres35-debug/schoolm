'use strict';
class GalleryController {
    #api; #ui;
    #pager      = new Paginator(12);
    #searchTerm = '';
    #debounceId = null;
    static #COLORS = ['#2563eb','#7c3aed','#059669','#d97706','#dc2626','#0891b2','#9333ea','#0f766e'];

    constructor(api, ui) { this.#api = api; this.#ui = ui; }

    search(value) {
        this.#searchTerm = (value ?? '').trim();
        this.#pager.reset();
        clearTimeout(this.#debounceId);
        this.#debounceId = setTimeout(() => this.load(), 300);
    }

    async load() {
        const search = this.#searchTerm;
        const genre  = document.getElementById('galleryGenreFilter')?.value  || '';
        const status = document.getElementById('galleryStatusFilter')?.value || '';
        const data   = await this.#api.get({ action: 'get_books', search, genre, status, page: this.#pager.page, limit: this.#pager.pageSize });

        this.#pager.total = data.total || 0;
        const grid = document.getElementById('galleryGrid');

        if (!data.books?.length) {
            grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><i class="fas fa-th-large"></i><p>No books found</p></div>';
        } else {
            grid.innerHTML = data.books.map(b => this.#renderCard(b)).join('');
        }
        document.getElementById('galleryPaginationInfo').textContent = this.#pager.infoText('books');
    }

    #renderCard(b) {
        const src   = this.#ui.coverSrc(b);
        const color = GalleryController.#COLORS[b.id % GalleryController.#COLORS.length];

        // Borrow icon only shows when:
        // - status is 'available'
        // - available_stock > 0
        // - NOT marked as lost
        const canBorrow = b.status === 'available'
            && parseInt(b.available_stock) > 0
            && b.status !== 'lost'
            && b.status !== 'damaged';

        return `
        <div class="book-card">
            <div class="book-cover-wrap">
                ${src ? `<img src="${this.#ui.escH(src)}" alt="${this.#ui.escH(b.title)}" onerror="this.style.display='none';this.nextSibling.style.display='flex'">` : ''}
                <div class="book-cover-placeholder" style="background:linear-gradient(135deg,${color},${color}cc);${src ? 'display:none' : ''}">
                    <i class="fas fa-book"></i>
                </div>
            </div>
            <div class="book-card-actions">
                ${canBorrow ? `
                    <button class="btn-borrow" onclick="event.stopPropagation();app.gallery.borrow(${b.id})" title="Borrow this book">
                        <i class="fas fa-handshake"></i>
                    </button>
                ` : ''}
                <button class="btn-view" onclick="event.stopPropagation();app.books.view(${b.id})" title="View"><i class="fas fa-eye"></i></button>
                <button class="btn-edit" onclick="event.stopPropagation();app.ui.switchTab('books');app.books.edit(${b.id})" title="Edit"><i class="fas fa-edit"></i></button>
                <button class="btn-del"  onclick="event.stopPropagation();app.books.delete(${b.id})" title="Delete"><i class="fas fa-trash"></i></button>
            </div>
            <div class="book-info">
                <div class="book-title-card">${this.#ui.escH(b.title)}</div>
                <div class="book-author-card">${this.#ui.escH(b.author)}</div>
                <span class="badge ${this.#ui.badgeClass(b.status)}" style="font-size:.7rem">${b.status}</span>
            </div>
        </div>`;
    }

    changePage(dir) { this.#pager.move(dir); this.load(); }

    async loadGenreOptions() {
        const data = await this.#api.get({ action: 'get_genres' });
        const sel  = document.getElementById('galleryGenreFilter');
        const cur  = sel.value;
        sel.innerHTML = '<option value="">All Category</option>'
            + (data.genres || []).map(g => `<option value="${this.#ui.escH(g)}" ${g === cur ? 'selected' : ''}>${this.#ui.escH(g)}</option>`).join('');
    }

    borrow(bookId) {
        app.ui.switchTab('borrow');

        let attempts = 0;
        const maxAttempts = 30;

        const tryFill = async () => {
            attempts++;

            const bookIdEl = document.getElementById('borrowBookId');
            const bookSrch = document.getElementById('borrowBookSearch');
            const bookChip = document.getElementById('borrowBookChip');

            if (!bookIdEl || !bookSrch) {
                if (attempts < maxAttempts) return setTimeout(tryFill, 100);
                this.#ui.showNotif('Borrow form not ready. Please try again.', 'error');
                return;
            }

            try {
                const book = await this.#api.get({ action: 'get_book', id: bookId });
                if (book.error) {
                    this.#ui.showNotif(book.error, 'error');
                    return;
                }

                if (book.status !== 'available' || parseInt(book.available_stock) <= 0) {
                    this.#ui.showNotif('This book is not available for borrowing.', 'error');
                    return;
                }

                bookIdEl.value = book.id;
                bookSrch.value = book.title + ' — ' + book.author;

                if (bookChip) {
                    bookChip.innerHTML = `
                        <div class="selected-chip">
                            <i class="fas fa-book"></i> ${this.#ui.escH(book.title)}
                            <button type="button" onclick="this.closest('.selected-chip').remove();document.getElementById('borrowBookId').value='';document.getElementById('borrowBookSearch').value=''">×</button>
                        </div>`;
                }

                try { if (window.BS?.pickBookExternal) BS.pickBookExternal(book); } catch(_) {}

                setTimeout(() => {
                    document.getElementById('borrowMemberSearch')?.focus();
                }, 100);

                this.#ui.showNotif(`Ready to borrow "${book.title}" — select a member.`, 'success');
            } catch (e) {
                console.error('Borrow pre-fill error:', e);
                this.#ui.showNotif('Could not pre-fill borrow form.', 'error');
            }
        };

        tryFill();
    }
}