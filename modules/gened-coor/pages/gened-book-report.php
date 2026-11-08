<?php $pageTitle = 'GenED Book Report'; ?>
<div class="module-header gened-book-report-header">
    <div>
        <h1> GenEd Book Report</h1>
        <p></p>
        <span class="gened-book-report-demo-note"></span>
    </div>
    <button type="button" class="btn btn-primary" id="genedBookReportAdd">
        <i class="fas fa-plus" aria-hidden="true"></i> Book Assignment
    </button>
</div>

<section class="module-content academics-management-page gened-book-report-page" id="genedBookReportPage">
    <p class="gened-book-report-catalog-notice" id="genedBookReportCatalogNotice" role="status">Loading GenEd departments, sections, subjects, and faculty...</p>
    <section class="gened-book-report-filters" aria-label="Book report filters">
        <label>
            Semester
            <select id="genedBookReportSemester" class="form-select">
                <option value="">All Semesters</option>
            </select>
        </label>
        <label>
            Status
            <select id="genedBookReportStatus" class="form-select">
                <option value="">All</option>
                <option>Active</option>
                <option>Archived</option>
            </select>
        </label>
        <label>
            Department
            <select id="genedBookReportDepartment" class="form-select">
                <option value="">All</option>
            </select>
        </label>
        <label class="gened-book-report-search">
            Search
            <input type="search" id="genedBookReportSearch" class="form-control" placeholder="Search section, subject, book, or teacher">
        </label>
    </section>

    <div class="gened-book-report-summary" aria-label="Dummy book report summary">
        <article class="gened-book-report-stat">
            <span>Copies Distributed</span>
            <strong id="genedBooksDistributed">—</strong>
        </article>
        <article class="gened-book-report-stat">
            <span>Paid / Collected</span>
            <strong id="genedBooksClaimed">—</strong>
        </article>
        <article class="gened-book-report-stat">
            <span>Not Collected (For Return)</span>
            <strong id="genedBooksUnclaimed">—</strong>
        </article>
        <article class="gened-book-report-stat">
            <span>Records</span>
            <strong id="genedBookRecordCount">—</strong>
        </article>
        <article class="gened-book-report-stat">
            <span>Different Books</span>
            <strong id="genedBookTitleCount">—</strong>
        </article>
    </div>

    <section class="gened-book-report-departments" aria-label="Students who paid or claimed books by department">
    </section>

    <div class="gened-book-report-table-card">
        <div class="gened-book-report-table-scroll">
            <table class="gened-book-report-table">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Section</th>
                        <th>Subject</th>
                        <th>Book Title</th>
                        <th>Teacher</th>
                        <th>Students</th>
                        <th>Distributed</th>
                        <th>Paid</th>
                        <th>Not Collected</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="genedBookReportRows">
                    <tr><td colspan="11" class="gened-book-report-empty">Loading sample records...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <p class="gened-book-report-footnote"></p>
</section>

<div class="gened-book-report-modal" id="genedBookReportModal" aria-hidden="true">
    <section class="gened-book-report-dialog" role="dialog" aria-modal="true" aria-labelledby="genedBookReportModalTitle">
        <div class="gened-book-report-modal-header">
            <h2 id="genedBookReportModalTitle">Assigned to Books</h2>
            <button type="button" class="gened-book-report-close" data-book-report-close aria-label="Close">&times;</button>
        </div>
        <form id="genedBookReportForm">
            <div class="gened-book-report-form-grid">
                <label>Semester
                    <select name="period" id="bookReportFormPeriod" required>
                        <option value="">Loading semesters...</option>
                    </select>
                </label>
                <label>Department
                    <select name="department" id="bookReportFormDepartment" required disabled>
                        <option value="">Select department</option>
                    </select>
                </label>
                <label>Section
                    <select name="section" id="bookReportFormSection" required disabled>
                        <option value="">Select section</option>
                    </select>
                </label>
                <label>Subject
                    <select name="subject" id="bookReportFormSubject" required disabled>
                        <option value="">Select GenEd subject</option>
                    </select>
                </label>
                <label class="gened-book-report-form-wide">Book Title
                    <input name="book" required>
                </label>
                <label>Teacher
                    <select name="teacher" id="bookReportFormFaculty" required disabled>
                        <option value="">Select assigned faculty</option>
                    </select>
                </label>
                <label>Enrolled Students
                    <input name="students" id="bookReportFormStudents" type="number" min="0" step="1" readonly required>
                </label>
                <label>Distributed
                    <input name="given" type="number" min="0" step="1" required>
                </label>
                <label>Colleted / Paid
                    <input name="claimed" type="number" min="0" step="1" required>
                </label>
            </div>
            <p class="gened-book-report-form-error" id="genedBookReportFormError" role="alert"></p>
            <div class="gened-book-report-modal-actions">
                <button type="button" class="btn btn-outline-secondary" data-book-report-close>Cancel</button>
                <button type="submit" class="btn btn-primary">I-save ang sample record</button>
            </div>
        </form>
    </section>
</div>

<div class="gened-book-report-modal" id="genedBookReportViewModal" aria-hidden="true">
    <section class="gened-book-report-dialog" role="dialog" aria-modal="true" aria-labelledby="genedBookReportViewTitle">
        <div class="gened-book-report-modal-header">
            <h2 id="genedBookReportViewTitle">Detalye ng Book Assignment</h2>
            <button type="button" class="gened-book-report-close" data-book-report-view-close aria-label="Close">&times;</button>
        </div>
        <div class="gened-book-report-view-details" id="genedBookReportViewDetails"></div>
    </section>
</div>
