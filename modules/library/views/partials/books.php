<div class="tab-content" id="tab-books">
    <div class="card mb-2">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-book"></i> Book Catalog Management</h3></div>
        <div class="grid-2">
            <div>
                <h4 class="mb-1" id="bookFormTitle">Add New Book</h4>
                <input type="hidden" id="editBookId" value="">
                <div class="form-group"><label>Book Title <span style="color:red">*</span></label><input type="text" id="bookTitle" placeholder="Enter book title" autocomplete="off" oninput="app.books.clearFieldError('bookTitle')"></div>
                <div class="form-group"><label>Author <span style="color:red">*</span></label><input type="text" id="bookAuthor" placeholder="Author's full name" autocomplete="off" oninput="app.books.clearFieldError('bookAuthor')"></div>
                <div class="grid-2">
                    <div class="form-group">
                        <label>Catalog / Genre</label>
                        <select id="bookGenre">
                            <optgroup label="── General Works (000s) ──">
                                <option value="General Reference">General Reference</option>
                                <option value="Computer Science & IT">Computer Science &amp; IT</option>
                                <option value="Library & Information Science">Library &amp; Information Science</option>
                            </optgroup>
                            <optgroup label="── Philosophy & Psychology (100s) ──">
                                <option value="Philosophy">Philosophy</option>
                                <option value="Psychology">Psychology</option>
                                <option value="Ethics & Logic">Ethics &amp; Logic</option>
                            </optgroup>
                            <optgroup label="── Religion (200s) ──">
                                <option value="Religion & Theology">Religion &amp; Theology</option>
                                <option value="Bible & Sacred Texts">Bible &amp; Sacred Texts</option>
                            </optgroup>
                            <optgroup label="── Social Sciences (300s) ──">
                                <option value="Social Science">Social Science</option>
                                <option value="Political Science">Political Science</option>
                                <option value="Economics">Economics</option>
                                <option value="Law & Government">Law &amp; Government</option>
                                <option value="Education">Education</option>
                                <option value="Commerce & Business">Commerce &amp; Business</option>
                                <option value="Criminology">Criminology</option>
                            </optgroup>
                            <optgroup label="── Language (400s) ──">
                                <option value="Filipino Language">Filipino Language</option>
                                <option value="English Language">English Language</option>
                                <option value="Foreign Language">Foreign Language</option>
                            </optgroup>
                            <optgroup label="── Pure Science (500s) ──">
                                <option value="Mathematics">Mathematics</option>
                                <option value="Biology">Biology</option>
                                <option value="Chemistry">Chemistry</option>
                                <option value="Physics">Physics</option>
                                <option value="Earth Science">Earth Science</option>
                                <option value="Natural Science">Natural Science</option>
                            </optgroup>
                            <optgroup label="── Applied Science (600s) ──">
                                <option value="Technology & Engineering">Technology &amp; Engineering</option>
                                <option value="Medicine & Health">Medicine &amp; Health</option>
                                <option value="Agriculture">Agriculture</option>
                                <option value="Nursing">Nursing</option>
                            </optgroup>
                            <optgroup label="── Arts & Recreation (700s) ──">
                                <option value="Arts & Music">Arts &amp; Music</option>
                                <option value="Sports & Recreation">Sports &amp; Recreation</option>
                                <option value="Architecture & Design">Architecture &amp; Design</option>
                            </optgroup>
                            <optgroup label="── Literature (800s) ──">
                                <option value="Filipino Literature">Filipino Literature</option>
                                <option value="English Literature">English Literature</option>
                                <option value="Fiction">Fiction</option>
                                <option value="Poetry & Drama">Poetry &amp; Drama</option>
                            </optgroup>
                            <optgroup label="── History & Geography (900s) ──">
                                <option value="Philippine History">Philippine History</option>
                                <option value="World History">World History</option>
                                <option value="Geography & Travel">Geography &amp; Travel</option>
                                <option value="Biography & Autobiography">Biography &amp; Autobiography</option>
                            </optgroup>
                            <optgroup label="── Special Collections ──">
                                <option value="Filipiniana">Filipiniana</option>
                                <option value="Periodicals & Journals">Periodicals &amp; Journals</option>
                                <option value="Thesis & Research">Thesis &amp; Research</option>
                                <option value="Government Documents">Government Documents</option>
                                <option value="Children & Young Adult">Children &amp; Young Adult</option>
                            </optgroup>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Year</label>
                        <input type="number" id="bookYear" min="1800" max="<?= date('Y') ?>" value="<?= date('Y') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>ISBN <span class="text-muted small">(auto-fetches cover)</span></label>
                    <input type="text" id="bookISBN" placeholder="978-0-0000-0000-0" oninput="app.books.previewISBNCover()">
                </div>

                <!-- Stock field -->
                <div class="form-group">
                    <label>Total Stock / Copies <span style="color:red">*</span>
                        <span class="text-muted small" style="font-weight:400"> — how many copies does the library own?</span>
                    </label>
                    <input type="number" id="bookTotalStock" value="1" min="1" max="9999"
                        placeholder="e.g. 5" style="max-width:160px">
                </div>

                <div class="form-group"><label>Description</label><textarea id="bookDescription" placeholder="Brief description of the book..."></textarea></div>
                <div class="form-group">
                    <label>Book Cover Image</label>
                    <div class="image-upload-area" id="coverUploadArea"
                         onclick="document.getElementById('coverFileInput').click()"
                         ondragover="app.books.handleDragOver(event)"
                         ondrop="app.books.handleDrop(event)">
                        <img id="coverPreviewImg" src="" alt="" style="display:none;max-height:140px;border-radius:8px;margin-bottom:.75rem">
                        <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:var(--gray);display:block;margin-bottom:.5rem" id="uploadIcon"></i>
                        <p style="margin:0;color:var(--gray)">Click or drag to upload cover image</p>
                        <p class="text-muted small" style="margin-top:.25rem">JPG, PNG, WebP up to 5MB</p>
                    </div>
                    <input type="file" id="coverFileInput" accept="image/*" style="display:none" onchange="app.books.handleCoverUpload(event)">
                    <input type="hidden" id="bookCoverUrl" value="">
                </div>
                <div class="d-flex gap-1">
                    <button class="btn btn-success w-100" id="bookSubmitBtn" onclick="app.books.submit()"><i class="fas fa-plus-circle"></i> Add Book</button>
                    <button class="btn btn-outline" id="bookCancelBtn" style="display:none" onclick="app.books.cancelEdit()"><i class="fas fa-times"></i> Cancel</button>
                </div>
            </div>
            <div>
                <div class="d-flex gap-1 mb-1 align-center">
                    <div class="search-box" style="flex:1;margin:0">
                        <i class="fas fa-search"></i>
                        <input type="text" id="bookSearch" placeholder="Search books..." oninput="app.books.search(this.value)">
                    </div>
                    <select id="bookStatusFilter" onchange="app.books.filter()" style="width:auto;padding:.875rem">
                        <option value="">All Status</option>
                        <option value="available">Available</option>
                        <option value="borrowed">Borrowed</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table>
                        <thead><tr><th>Cover</th><th>Title / Author</th><th>Catalog</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead>
                        <tbody id="booksTableBody"><tr><td colspan="6" class="empty-state"><div class="spinner"></div></td></tr></tbody>
                    </table>
                </div>
                <div class="pagination">
                    <span class="pagination-info" id="booksPaginationInfo">Loading...</span>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-outline" onclick="app.books.changePage(-1)"><i class="fas fa-chevron-left"></i></button>
                        <button class="btn btn-sm btn-outline" onclick="app.books.changePage(1)"><i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
