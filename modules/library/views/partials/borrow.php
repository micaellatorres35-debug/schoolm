<style>
.autocomplete-wrap { position: relative; }
.autocomplete-list {
    position: absolute;
    top: 100%; left: 0; right: 0;
    background: var(--card-bg);
    border: 2px solid var(--primary);
    border-top: none;
    border-radius: 0 0 var(--border-radius-sm) var(--border-radius-sm);
    max-height: 220px;
    overflow-y: auto;
    z-index: 9999;
    display: none;
    box-shadow: 0 8px 24px rgba(37,99,235,.12);
}
.autocomplete-list.open { display: block; }
.autocomplete-item {
    padding: .75rem 1rem;
    cursor: pointer;
    border-bottom: 1px solid var(--border-color);
    transition: background .15s;
    font-size: .9rem;
}
.autocomplete-item:last-child { border-bottom: none; }
.autocomplete-item:hover,
.autocomplete-item.ac-active { background: rgba(37,99,235,.08); }
.autocomplete-item .item-main { font-weight: 600; color: var(--dark); }
.autocomplete-item .item-sub  { font-size: .78rem; color: var(--gray); margin-top: .1rem; }
.autocomplete-item .item-badge {
    font-size: .68rem; font-weight: 600; padding: .15rem .5rem;
    border-radius: 50px; background: rgba(16,185,129,.15);
    color: var(--success); border: 1px solid rgba(16,185,129,.2);
    float: right; margin-top: .1rem;
}
.selected-chip {
    display: inline-flex; align-items: center; gap: .5rem;
    background: rgba(37,99,235,.1); color: var(--primary);
    border: 1px solid rgba(37,99,235,.25);
    border-radius: 50px; padding: .35rem .85rem;
    font-size: .85rem; font-weight: 600; margin-top: .4rem;
}
.selected-chip button {
    background: none; border: none; cursor: pointer;
    color: var(--primary); font-size: 1rem; padding: 0; line-height: 1;
}
.autocomplete-empty {
    padding: .75rem 1rem; color: var(--gray);
    font-size: .85rem; font-style: italic;
}
</style>

<div class="tab-content" id="tab-borrow">
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-handshake"></i> Borrow a Book</h3></div>
        <div class="grid-2">
            <div>
                <input type="hidden" id="borrowBookId">
                <input type="hidden" id="borrowMemberId">

                <div class="form-group">
                    <label>Select Book <span style="color:red">*</span></label>
                    <div class="autocomplete-wrap" id="bookWrap">
                        <input type="text" id="borrowBookSearch"
                            placeholder="Type book title or author..."
                            autocomplete="off"
                            oninput="BS.searchBook(this.value)"
                            onkeydown="BS.keyNav(event,'book')"
                            onfocus="BS.searchBook(this.value)">
                        <div class="autocomplete-list" id="bookDropdown"></div>
                    </div>
                    <div id="borrowBookChip"></div>
                </div>

                <div class="form-group">
                    <label>Select Member <span style="color:red">*</span></label>
                    <div class="autocomplete-wrap" id="memberWrap">
                        <input type="text" id="borrowMemberSearch"
                            placeholder="Type member name or student number..."
                            autocomplete="off"
                            oninput="BS.searchMember(this.value)"
                            onkeydown="BS.keyNav(event,'member')"
                            onfocus="BS.searchMember(this.value)">
                        <div class="autocomplete-list" id="memberDropdown"></div>
                    </div>
                    <div id="borrowMemberChip"></div>
                </div>

                <div class="form-group">
                    <label>Borrow Duration</label>
                    <select id="borrowDays">
                        <option value="14" selected>14 days (loading...)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Notes</label>
                    <textarea id="borrowNotes" placeholder="Optional notes..." style="min-height:80px"></textarea>
                </div>

                <button class="btn btn-success w-100" id="issueBookBtn" onclick="BS.issue()">
                    <i class="fas fa-handshake"></i> Issue Book
                </button>
            </div>
            <div>
                <h4 class="mb-1">Active Borrowings</h4>
                <div id="activeBorrowingsPanel"><div class="spinner"></div></div>
            </div>
        </div>
    </div>
</div>

<div class="tab-content" id="tab-return">
    <div class="card">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-undo"></i> Return a Book</h3></div>
        <div id="returnableList"><div class="spinner"></div></div>
    </div>
</div>

<script>
const BS = (() => {
    let bookResults = [], memberResults = [];
    let bookIdx = -1, memberIdx = -1;
    let bookTimer, memberTimer;

    const esc = s => s == null ? '' : String(s)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');

    async function loadBorrowDays() {
        const select = document.getElementById('borrowDays');
        if (!select) return;

        try {
            const res = await fetch('api.php?action=get_settings&_t=' + Date.now(), {
                credentials: 'same-origin',
                cache: 'no-store'
            });
            const settings = await res.json();

            console.log('[BS] max_borrow_days =', settings.max_borrow_days);

            const maxDays = parseInt(settings.max_borrow_days) || 14;

            let html = '';
            for (let i = 1; i <= maxDays; i++) {
                const label = i === maxDays
                    ? i + ' day' + (i !== 1 ? 's' : '') + ' (maximum)'
                    : i + ' day' + (i !== 1 ? 's' : '');
                html += '<option value="' + i + '"' + (i === maxDays ? ' selected' : '') + '>' + label + '</option>';
            }
            select.innerHTML = html;
            console.log('[BS] Dropdown built with', maxDays, 'days');
        } catch (e) {
            console.error('[BS] Error loading settings:', e);
        }
    }

    document.addEventListener('mousedown', e => {
        if (!e.target.closest('#bookWrap'))   close('book');
        if (!e.target.closest('#memberWrap')) close('member');
    });

    function close(type) {
        const el = document.getElementById(type === 'book' ? 'bookDropdown' : 'memberDropdown');
        if (el) el.classList.remove('open');
    }

    async function searchBook(val) {
        clearTimeout(bookTimer);
        if (!val.trim()) { close('book'); return; }
        bookTimer = setTimeout(async () => {
            const res = await fetch('api.php?action=get_books&status=available&search=' + encodeURIComponent(val.trim()) + '&limit=10', { credentials: 'same-origin' });
            const data = await res.json();
            bookResults = data.books || [];
            bookIdx = -1;
            renderBook();
        }, 250);
    }

    function renderBook() {
        const drop = document.getElementById('bookDropdown');
        if (!bookResults.length) {
            drop.innerHTML = '<div class="autocomplete-empty">No available books found</div>';
        } else {
            drop.innerHTML = bookResults.map((b,i) =>
                '<div class="autocomplete-item" data-i="' + i + '" onmousedown="BS.pickBook(' + i + ')">' +
                '<span class="item-badge">Available</span>' +
                '<div class="item-main">' + esc(b.title) + '</div>' +
                '<div class="item-sub">' + esc(b.author) + ' &nbsp;&middot;&nbsp; ' + esc(b.genre||'General') + '</div>' +
                '</div>'
            ).join('');
        }
        drop.classList.add('open');
    }

    function pickBook(i) {
        const b = bookResults[i]; if (!b) return;
        setBook(b);
    }

    function pickBookExternal(book) {
        if (!book) return;
        setBook(book);
    }

    function setBook(b) {
        const idEl = document.getElementById('borrowBookId');
        const srch = document.getElementById('borrowBookSearch');
        const chip = document.getElementById('borrowBookChip');
        if (idEl) idEl.value = b.id;
        if (srch) srch.value = b.title + ' \u2014 ' + b.author;
        close('book');
        if (chip) {
            chip.innerHTML = '<div class="selected-chip"><i class="fas fa-book"></i> ' + esc(b.title) +
                ' <button type="button" onmousedown="BS.clearBook()">&times;</button></div>';
        }
    }

    function clearBook() {
        const idEl = document.getElementById('borrowBookId');
        const srch = document.getElementById('borrowBookSearch');
        const chip = document.getElementById('borrowBookChip');
        if (idEl) idEl.value = '';
        if (srch) srch.value = '';
        if (chip) chip.innerHTML = '';
        close('book');
        bookResults = [];
    }

    async function searchMember(val) {
        clearTimeout(memberTimer);
        if (!val.trim()) { close('member'); return; }
        memberTimer = setTimeout(async () => {
            const res = await fetch('api.php?action=get_borrowers&search=' + encodeURIComponent(val.trim()) + '&limit=10', { credentials: 'same-origin' });
            const data = await res.json();
            memberResults = data.borrowers || [];
            memberIdx = -1;
            renderMember();
        }, 250);
    }

    function renderMember() {
        const drop = document.getElementById('memberDropdown');
        if (!memberResults.length) {
            drop.innerHTML = '<div class="autocomplete-empty">No members found</div>';
        } else {
            drop.innerHTML = memberResults.map((m,i) =>
                '<div class="autocomplete-item" data-i="' + i + '" onmousedown="BS.pickMember(' + i + ')">' +
                '<span class="item-badge" style="background:rgba(37,99,235,.12);color:var(--primary);border-color:rgba(37,99,235,.2)">' + esc(m.type) + '</span>' +
                '<div class="item-main">' + esc(m.name) + '</div>' +
                '<div class="item-sub">' + esc(m.borrower_id) + (m.grade ? ' &nbsp;&middot;&nbsp; ' + esc(m.grade) : '') + '</div>' +
                '</div>'
            ).join('');
        }
        drop.classList.add('open');
    }

    function pickMember(i) {
        const m = memberResults[i]; if (!m) return;
        setMember(m);
    }

    function setMember(m) {
        const idEl = document.getElementById('borrowMemberId');
        const srch = document.getElementById('borrowMemberSearch');
        const chip = document.getElementById('borrowMemberChip');
        if (idEl) idEl.value = m.id;
        if (srch) srch.value = m.name + ' (' + m.borrower_id + ')';
        close('member');
        if (chip) {
            chip.innerHTML = '<div class="selected-chip"><i class="fas fa-user"></i> ' + esc(m.name) +
                ' <button type="button" onmousedown="BS.clearMember()">&times;</button></div>';
        }
    }

    function clearMember() {
        const idEl = document.getElementById('borrowMemberId');
        const srch = document.getElementById('borrowMemberSearch');
        const chip = document.getElementById('borrowMemberChip');
        if (idEl) idEl.value = '';
        if (srch) srch.value = '';
        if (chip) chip.innerHTML = '';
        close('member');
        memberResults = [];
    }

    function keyNav(e, type) {
        const drop  = document.getElementById(type==='book'?'bookDropdown':'memberDropdown');
        const items = drop.querySelectorAll('.autocomplete-item');
        let   idx   = type==='book' ? bookIdx : memberIdx;
        if (!drop.classList.contains('open') || !items.length) return;
        if (e.key==='ArrowDown') { e.preventDefault(); idx = Math.min(idx+1, items.length-1); }
        else if (e.key==='ArrowUp') { e.preventDefault(); idx = Math.max(idx-1, 0); }
        else if (e.key==='Enter') { e.preventDefault(); if(idx>=0) type==='book'?pickBook(idx):pickMember(idx); return; }
        else if (e.key==='Escape') { close(type); return; }
        else return;
        items.forEach(i=>i.classList.remove('ac-active'));
        if (idx>=0) items[idx].classList.add('ac-active');
        if (type==='book') bookIdx=idx; else memberIdx=idx;
    }

    async function issue() {
        const book_id     = document.getElementById('borrowBookId').value;
        const borrower_id = document.getElementById('borrowMemberId').value;
        const borrow_days = document.getElementById('borrowDays').value;
        const notes       = document.getElementById('borrowNotes').value.trim();

        if (!book_id)     { app.ui.showNotif('Please select a book.','error');   return; }
        if (!borrower_id) { app.ui.showNotif('Please select a member.','error'); return; }

        const btn = document.getElementById('issueBookBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        try {
            const res = await app.api.post('borrow_book',
                { book_id:+book_id, borrower_id:+borrower_id, borrow_days:+borrow_days, notes });
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-handshake"></i> Issue Book';
            if (res.error) { app.ui.showNotif(res.error,'error'); return; }
            app.ui.showNotif(res.message,'success');
            clearBook(); clearMember();
            document.getElementById('borrowNotes').value = '';
            loadBorrowDays();
            app.borrow.loadActive();
            app.dashboard.load();
            app.books.load();
            app.gallery.load();
            app.transactions.load();
        } catch(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-handshake"></i> Issue Book';
            app.ui.showNotif('Error processing borrow.','error');
        }
    }

    return {
        searchBook, searchMember,
        pickBook, pickMember, pickBookExternal,
        setBook, setMember,
        clearBook, clearMember,
        keyNav, issue, loadBorrowDays
    };
})();

(function() {
    let attempts = 0;
    const interval = setInterval(function() {
        attempts++;
        if (typeof BS !== 'undefined' && typeof BS.loadBorrowDays === 'function') {
            BS.loadBorrowDays();
            clearInterval(interval);
        }
        if (attempts >= 40) clearInterval(interval);
    }, 200);
})();
</script>