<?php $pageTitle = 'GenED Item Analysis'; ?>
<div class="module-header ge-curriculum-header gened-item-analysis-header">
    <div>
        <h1>GenED Item Analysis</h1>
        <p>Review exam performance and record GenEd student scores.</p>
    </div>
    <button type="button" class="btn btn-primary gened-item-analysis-export" id="genedItemAnalysisExport" disabled>
        <i class="fas fa-file-export" aria-hidden="true"></i>
        Export Exam Report
    </button>
</div>

<section class="module-content academics-management-page ge-curriculum-page gened-item-analysis-page" id="genedItemAnalysisPage" aria-label="GenEd item analysis">
    <div class="gened-item-analysis-notice" id="genedItemAnalysisNotice" role="status">
        Loading GenEd exam schedules...
    </div>

    <section class="gened-item-analysis-card" aria-labelledby="genedItemAnalysisFiltersTitle">
        <div class="gened-item-analysis-section-heading">
            <div>
                <h2 id="genedItemAnalysisFiltersTitle">Exam Filters</h2>
                <p>Filter actual scheduled exams by academic period and GenEd subject.</p>
            </div>
        </div>
        <div class="gened-item-analysis-filters">
            <label>
                Academic Year
                <select id="genedIaSchoolYear" class="form-select"><option value="">All Academic Years</option></select>
            </label>
            <label>
                Semester
                <select id="genedIaSemester" class="form-select"><option value="">All Semesters</option></select>
            </label>
            <label>
                Department
                <select id="genedIaDepartment" class="form-select"><option value="">All Departments</option></select>
            </label>
            <label>
                GenEd Subject
                <select id="genedIaSubject" class="form-select"><option value="">All GenEd Subjects</option></select>
            </label>
            <label>
                Section
                <select id="genedIaSection" class="form-select"><option value="">All Sections</option></select>
            </label>
            <label>
                Exam Type
                <select id="genedIaExamType" class="form-select"><option value="">All Exam Types</option></select>
            </label>
            <label class="gened-item-analysis-exam-filter">
                Scheduled Exam
                <select id="genedIaSchedule" class="form-select">
                    <option value="">Select an exam type and GenEd subject</option>
                </select>
            </label>
        </div>
    </section>

    <div class="gened-item-analysis-data-note">
        Scores entered here are stored for the selected scheduled exam. Only recorded scores are used in pass/fail results. Item difficulty still requires question-level answers.
    </div>

    <section class="gened-item-analysis-card" aria-labelledby="genedIaExamTitle">
        <div class="gened-item-analysis-section-heading">
            <div>
                <h2 id="genedIaExamTitle"> Exam Information</h2>
                <p>Selected scheduled examination.</p>
            </div>
        </div>
        <div class="gened-item-analysis-info-grid" id="genedIaExamInformation">
            <div class="gened-item-analysis-info-empty">Select a scheduled exam to view its information.</div>
        </div>
    </section>

    <section class="gened-item-analysis-card" aria-labelledby="genedIaPerformanceTitle">
        <div class="gened-item-analysis-section-heading">
            <div>
                <h2 id="genedIaPerformanceTitle"> Exam Results</h2>
                <p id="genedIaOverallDescription">Passed and failed results will be calculated from entered student scores.</p>
            </div>
        </div>
        <p class="gened-item-analysis-period" id="genedIaAcademicPeriod">Select a scheduled exam to display its academic period.</p>
        <div class="gened-item-analysis-summary-grid">
            <article><span>Total Students</span><strong id="genedIaTotalStudents">—</strong></article>
            <article><span>Passed Students</span><strong id="genedIaPassed">—</strong></article>
            <article><span>Failed Students</span><strong id="genedIaFailed">—</strong></article>
            <article><span>Passing Rate</span><strong id="genedIaPassingRate">—</strong></article>
            <article><span>Failure Rate</span><strong id="genedIaFailureRate">—</strong></article>
            <article><span>Average Score</span><strong id="genedIaAverageScore">—</strong></article>
        </div>
        <div class="gened-item-analysis-charts">
            <article class="gened-item-analysis-chart-card">
                <h3>Overall Examination Results</h3>
                <p id="genedIaOverallScope">Select an exam to see results across its departments.</p>
                <div class="gened-item-analysis-pie-wrap">
                    <div class="gened-item-analysis-pie is-empty" id="genedIaPassFailChart" role="img" aria-label="No exam scores have been recorded"></div>
                </div>
                <p class="gened-item-analysis-chart-total"><strong id="genedIaChartScored">0</strong> scored students</p>
                <div class="gened-item-analysis-pie-legend">
                    <span><i class="gened-item-analysis-swatch is-passed"></i>Passed <strong id="genedIaChartPassed">0 (0%)</strong></span>
                    <span><i class="gened-item-analysis-swatch is-failed"></i>Failed <strong id="genedIaChartFailed">0 (0%)</strong></span>
                </div>
                <p id="genedIaChartNote">Results will appear after student scores are entered.</p>
            </article>
            <article class="gened-item-analysis-chart-card">
                <h3>Item Difficulty</h3>
                <div class="gened-item-analysis-empty-chart" role="img" aria-label="Item difficulty is unavailable until student answers per question are recorded">
                    <span>No item data</span>
                </div>
                <p>Difficulty index needs actual student answers for each question.</p>
            </article>
        </div>
    </section>

    <section class="gened-item-analysis-card" aria-labelledby="genedIaSectionsTitle">
        <div class="gened-item-analysis-section-heading">
            <div>
                <h2 id="genedIaSectionsTitle">Results by Department and Section</h2>
                <p>Pass and fail counts for sections with saved scores.</p>
            </div>
        </div>
        <div class="gened-item-analysis-table-wrap">
            <table class="gened-item-analysis-table">
                <thead>
                    <tr>
                        <th scope="col">Department</th>
                        <th scope="col">Section</th>
                        <th scope="col">Total Students</th>
                        <th scope="col">Scored</th>
                        <th scope="col">Passed</th>
                        <th scope="col">Failed</th>
                    </tr>
                </thead>
                <tbody id="genedIaSectionRows">
                    <tr><td colspan="6" class="gened-item-analysis-empty-row">Select an exam to view section results.</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="gened-item-analysis-card" aria-labelledby="genedIaStudentsTitle">
        <div class="gened-item-analysis-section-heading">
            <div>
                <h2 id="genedIaStudentsTitle"> Student Exam Scores</h2>
                <p id="genedIaStudentCount">Select an exam to view and enter scores for its enrolled students.</p>
            </div>
        </div>
        <div class="gened-item-analysis-score-settings">
            <label>Total Items<input type="number" id="genedIaTotalItemsInput" min="1" step="1"></label>
            <label>Passing Score<input type="number" id="genedIaPassingScoreInput" min="0" step="0.01"></label>
            <button type="button" class="btn btn-primary" id="genedIaSaveScores" disabled>Save Exam Scores</button>
        </div>
        <p class="gened-item-analysis-score-help">Enter a score for each student. Blank scores are left unchanged; only saved scores are included in the chart and summaries.</p>
        <div class="gened-item-analysis-table-wrap">
            <table class="gened-item-analysis-table">
                <thead>
                    <tr>
                        <th scope="col">Student ID</th>
                        <th scope="col">Student Name</th>
                        <th scope="col">Score</th>
                        <th scope="col">Percentage</th>
                        <th scope="col">Result</th>
                    </tr>
                </thead>
                <tbody id="genedIaStudentRows">
                    <tr><td colspan="5" class="gened-item-analysis-empty-row">Select a scheduled exam to view enrolled students.</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="gened-item-analysis-card" aria-labelledby="genedIaItemsTitle">
        <div class="gened-item-analysis-section-heading">
            <div>
                <h2 id="genedIaItemsTitle"> Item Analysis</h2>
                <p>Question-level statistics appear only when actual student responses are available.</p>
            </div>
        </div>
        <div class="gened-item-analysis-table-wrap">
            <table class="gened-item-analysis-table">
                <thead>
                    <tr>
                        <th scope="col">Item No.</th>
                        <th scope="col">Correct</th>
                        <th scope="col">Incorrect</th>
                        <th scope="col">Difficulty Index</th>
                        <th scope="col">Discrimination Index</th>
                        <th scope="col">Remarks</th>
                    </tr>
                </thead>
                <tbody id="genedIaItemRows">
                    <tr><td colspan="6" class="gened-item-analysis-empty-row">No per-question answers are recorded in the system.</td></tr>
                </tbody>
            </table>
        </div>
    </section>
</section>