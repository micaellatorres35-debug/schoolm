(() => {
    const rootId = 'genedItemAnalysisPage';
    const apiUrl = '/schoolm/modules/gened-coor/api/item_analysis.php';
    let schedules = [];
    let filterCatalogs = {};
    let selectedSchedule = null;
    let students = [];
    let examDetails = null;

    const filterIds = {
        schoolYear: 'genedIaSchoolYear',
        semester: 'genedIaSemester',
        department: 'genedIaDepartment',
        subject: 'genedIaSubject',
        section: 'genedIaSection',
        examType: 'genedIaExamType'
    };

    const text = value => String(value ?? '').trim();
    const escapeCsv = value => `"${String(value ?? '').replace(/"/g, '""')}"`;

    function currentPage() {
        return document.getElementById(rootId);
    }

    function setNotice(message, isError = false) {
        const notice = document.getElementById('genedItemAnalysisNotice');
        if (!notice) return;
        notice.textContent = message;
        notice.classList.toggle('is-error', isError);
        notice.hidden = !message;
    }

    function fillFilter(selectId, values, placeholder) {
        const select = document.getElementById(selectId);
        if (!select) return;
        const previousValue = select.value;
        select.replaceChildren(new Option(placeholder, ''));
        values.forEach(item => {
            const option = new Option(item.label, item.value);
            select.appendChild(option);
        });
        if (values.some(item => String(item.value) === previousValue)) {
            select.value = previousValue;
        }
    }

    function filteredSchedules() {
        return schedules.filter(schedule =>
            Object.entries(filterIds).every(([key, id]) => {
                const value = document.getElementById(id)?.value || '';
                if (!value) return true;
                const scheduleValue = key === 'schoolYear' ? schedule.school_year_id
                    : key === 'semester' ? schedule.semester
                    : key === 'department' ? schedule.department_id
                    : key === 'subject' ? schedule.subject_id
                    : key === 'section' ? schedule.section_id
                    : schedule.exam_type;
                return String(scheduleValue) === String(value);
            })
        );
    }

    function updateFilters() {
        const options = [
            ['schoolYear', 'school_years', 'All Academic Years', item => ({value: item.id, label: item.name})],
            ['semester', 'semesters', 'All Semesters', item => ({value: item, label: item})],
            ['department', 'departments', 'All Departments', item => ({value: item.id, label: `${item.code} - ${item.name}`})],
            ['subject', 'subjects', 'All GenEd Subjects', item => ({value: item.id, label: `${item.code} - ${item.name}`})],
            ['section', 'sections', 'All Sections', item => ({
                value: item.id,
                label: [
                    `${item.department_code} - ${item.section_code}`,
                    item.grade_level,
                    item.school_year,
                    item.semester
                ].filter(Boolean).join(' · ')
            })],
            ['examType', 'exam_types', 'All Exam Types', item => ({value: item.value, label: item.label})]
        ];
        options.forEach(([key, catalogKey, placeholder, mapOption]) => {
            const values = (filterCatalogs[catalogKey] || []).map(mapOption);
            fillFilter(filterIds[key], values, placeholder);
        });
    }

    function renderScheduleOptions() {
        const select = document.getElementById('genedIaSchedule');
        if (!select) return;
        const previousValue = select.value;
        const filtered = filteredSchedules();
        const examTypes = filterCatalogs.exam_types || [];
        const subjects = filterCatalogs.subjects || [];
        select.replaceChildren(new Option(
            'Select an exam type and GenEd subject',
            ''
        ));

        examTypes.forEach(examType => {
            subjects.forEach(subject => {
                const subjectSchedules = filtered.filter(schedule =>
                    String(schedule.exam_type) === String(examType.value)
                    && String(schedule.subject_id) === String(subject.id)
                );

                if (!subjectSchedules.length) {
                    select.appendChild(new Option(
                        `${examType.label} · ${subject.code} - ${subject.name} (No scheduled exam)`,
                        `catalog:${examType.value}:${subject.id}`
                    ));
                    return;
                }

                subjectSchedules.forEach(schedule => {
                    const label = [
                        examType.label,
                        `${subject.code} - ${subject.name}`,
                        schedule.section_code,
                        schedule.school_year,
                        schedule.semester
                    ].filter(Boolean).join(' · ');
                    select.appendChild(new Option(label, schedule.schedule_id));
                });
            });
        });

        if (Array.from(select.options).some(option => option.value === previousValue)) {
            select.value = previousValue;
        } else {
            select.value = '';
            clearSelection();
        }
    }

    function appendInfo(container, label, value) {
        const item = document.createElement('div');
        item.className = 'gened-item-analysis-info-item';
        const title = document.createElement('span');
        title.textContent = label;
        const content = document.createElement('strong');
        content.textContent = text(value) || 'Not recorded';
        item.append(title, content);
        container.appendChild(item);
    }

    function renderExamInformation(schedule, totalStudents, settings) {
        const container = document.getElementById('genedIaExamInformation');
        if (!container) return;
        container.replaceChildren();
        if (!schedule) {
            const empty = document.createElement('div');
            empty.className = 'gened-item-analysis-info-empty';
            empty.textContent = 'Select a scheduled exam to view its information.';
            container.appendChild(empty);
            return;
        }

        [
            ['Exam ID', `Exam #${schedule.exam_id}`],
            ['Academic Year', schedule.school_year],
            ['Semester', schedule.semester],
            ['Exam Title', schedule.exam_name],
            ['Subject Code', schedule.subject_code],
            ['GenEd Subject', schedule.subject_name],
            ['Department', schedule.department],
            ['Section', schedule.section_code],
            ['Faculty', schedule.faculty_names],
            ['Exam Status', schedule.exam_status],
            ['Exam Type', schedule.exam_type],
            ['Exam Date', schedule.exam_date],
            ['Total Items', settings?.total_items],
            ['Passing Score', settings ? `${settings.passing_score}/${settings.total_items}` : null],
            ['Total Students', totalStudents]
        ].forEach(([label, value]) => appendInfo(container, label, value));
    }

    function renderSummary(summary, sectionResults) {
        const totalStudents = Number(summary?.total_students || 0);
        const scoredStudents = Number(summary?.scored_students || 0);
        const passed = Number(summary?.passed || 0);
        const failed = Number(summary?.failed || 0);
        const passingRate = summary?.passing_rate;
        const totalItems = Number(examDetails?.settings?.total_items || 0);
        const passingScore = examDetails?.settings?.passing_score;

        document.getElementById('genedIaTotalStudents').textContent = selectedSchedule ? totalStudents : '—';
        document.getElementById('genedIaPassed').textContent = selectedSchedule ? passed : '—';
        document.getElementById('genedIaFailed').textContent = selectedSchedule ? failed : '—';
        document.getElementById('genedIaPassingRate').textContent =
            selectedSchedule && passingRate !== null ? `${Number(passingRate).toFixed(2)}%` : '—';
        document.getElementById('genedIaFailureRate').textContent =
            selectedSchedule && summary?.failure_rate !== null
                ? `${Number(summary.failure_rate).toFixed(2)}%`
                : '—';
        document.getElementById('genedIaAverageScore').textContent =
            selectedSchedule && summary?.average_score !== null
                ? `${Number(summary.average_score).toFixed(2)}/${totalItems || '—'}`
                : '—';
        document.getElementById('genedIaAcademicPeriod').textContent = selectedSchedule
            ? `${selectedSchedule.school_year} · ${selectedSchedule.semester}`
            : 'Select a scheduled exam to display its academic period.';
        document.getElementById('genedIaChartScored').textContent = scoredStudents;
        document.getElementById('genedIaChartPassed').textContent =
            `${passed} (${scoredStudents ? (passed / scoredStudents * 100).toFixed(2) : '0.00'}%)`;
        document.getElementById('genedIaChartFailed').textContent =
            `${failed} (${scoredStudents ? (failed / scoredStudents * 100).toFixed(2) : '0.00'}%)`;
        const selectedDepartmentId = document.getElementById(filterIds.department)?.value || '';
        const selectedSectionId = document.getElementById(filterIds.section)?.value || '';
        const selectedDepartment = (filterCatalogs.departments || [])
            .find(item => String(item.id) === selectedDepartmentId);
        const selectedSection = (filterCatalogs.sections || [])
            .find(item => String(item.id) === selectedSectionId);
        const scopeLabel = selectedSection
            ? `${selectedSection.department_code} · ${selectedSection.section_code}`
            : selectedDepartment
                ? selectedDepartment.code
                : 'All Departments';
        document.getElementById('genedIaOverallDescription').textContent = selectedSchedule
            ? `${selectedSchedule.subject_name} · ${scopeLabel}`
            : 'Passed and failed results will be calculated from entered student scores.';
        document.getElementById('genedIaOverallScope').textContent = selectedSchedule
            ? `${totalStudents} students across ${Number(summary?.sections || 0)} section(s). ${scoredStudents} scored. Passing score: ${
                passingScore !== null && passingScore !== undefined && totalItems
                    ? `${passingScore}/${totalItems}`
                    : 'not set'
            }.`
            : 'Select an exam to see results across its departments.';
        document.getElementById('genedIaChartNote').textContent = scoredStudents
            ? `Based on ${scoredStudents} saved student score(s).`
            : 'No student scores have been saved for this exam yet.';

        const pie = document.getElementById('genedIaPassFailChart');
        if (pie) {
            pie.classList.toggle('is-empty', scoredStudents === 0);
            pie.style.background = scoredStudents
                ? `conic-gradient(#16a34a 0deg ${passed / scoredStudents * 360}deg, #dc2626 ${passed / scoredStudents * 360}deg 360deg)`
                : '#e2e8f0';
            pie.setAttribute(
                'aria-label',
                scoredStudents
                    ? `${passed} passed and ${failed} failed out of ${scoredStudents} scored students`
                    : 'No exam scores have been recorded'
            );
        }

        const rows = document.getElementById('genedIaSectionRows');
        rows.replaceChildren();
        if (!selectedSchedule || !sectionResults?.length) {
            const row = rows.insertRow();
            const cell = row.insertCell();
            cell.colSpan = 6;
            cell.className = 'gened-item-analysis-empty-row';
            cell.textContent = 'No section results are available until scores are saved.';
            return;
        }
        sectionResults.forEach(result => {
            const row = rows.insertRow();
            [
                result.department,
                result.section_code,
                result.total_students,
                result.scored_students,
                result.passed,
                result.failed
            ].forEach(value => {
                row.insertCell().textContent = text(value);
            });
        });
        const totalRow = rows.insertRow();
        totalRow.className = 'gened-item-analysis-total-row';
        [
            'TOTAL',
            `${summary.sections} Sections`,
            totalStudents,
            scoredStudents,
            passed,
            failed
        ].forEach(value => totalRow.insertCell().textContent = String(value));
    }

    function renderStudents() {
        const body = document.getElementById('genedIaStudentRows');
        const countLabel = document.getElementById('genedIaStudentCount');
        const totalElement = document.getElementById('genedIaTotalStudents');
        if (!body || !countLabel || !totalElement) return;
        const totalItemsInput = document.getElementById('genedIaTotalItemsInput');
        const passingScoreInput = document.getElementById('genedIaPassingScoreInput');
        const saveButton = document.getElementById('genedIaSaveScores');
        body.replaceChildren();
        totalElement.textContent = selectedSchedule
            ? String(examDetails?.summary?.total_students ?? students.length)
            : '—';
        countLabel.textContent = selectedSchedule
            ? `${students.length} currently enrolled student${students.length === 1 ? '' : 's'} in ${selectedSchedule.department} · ${selectedSchedule.section_code}. Enter scores, then save.`
            : 'Select an exam to view and enter scores for its enrolled students.';
        if (selectedSchedule) {
            totalItemsInput.value = examDetails?.settings?.total_items ?? '';
            passingScoreInput.value = examDetails?.settings?.passing_score ?? '';
            totalItemsInput.disabled = false;
            passingScoreInput.disabled = false;
            saveButton.disabled = false;
        } else {
            totalItemsInput.disabled = true;
            passingScoreInput.disabled = true;
            saveButton.disabled = true;
        }

        if (!selectedSchedule || students.length === 0) {
            const row = body.insertRow();
            const cell = row.insertCell();
            cell.colSpan = 5;
            cell.className = 'gened-item-analysis-empty-row';
            cell.textContent = selectedSchedule
                ? 'No currently enrolled students were found for this section.'
                : 'Select a scheduled exam to view enrolled students.';
            return;
        }

        students.forEach(student => {
            const row = body.insertRow();
            row.insertCell().textContent = text(student.student_number);
            row.insertCell().textContent = text(student.student_name) || 'Name not available';
            const scoreCell = row.insertCell();
            const scoreInput = document.createElement('input');
            scoreInput.type = 'number';
            scoreInput.className = 'gened-item-analysis-score-input';
            scoreInput.min = '0';
            scoreInput.step = '0.01';
            scoreInput.max = text(totalItemsInput.value);
            scoreInput.value = student.score === null ? '' : text(student.score);
            scoreInput.dataset.studentId = student.student_id;
            scoreInput.setAttribute('aria-label', `Score for ${student.student_name || student.student_number}`);
            scoreCell.appendChild(scoreInput);
            const percentageCell = row.insertCell();
            const resultCell = row.insertCell();
            updateStudentResult(scoreInput, percentageCell, resultCell);
        });
    }

    function updateStudentResult(input, percentageCell, resultCell) {
        const score = Number(input.value);
        const totalItemsInput = document.getElementById('genedIaTotalItemsInput');
        const passingScoreInput = document.getElementById('genedIaPassingScoreInput');
        const totalItems = Number(totalItemsInput.value);
        const passingScore = Number(passingScoreInput.value);
        if (input.value === '' || !totalItems || !Number.isFinite(score)
            || passingScoreInput.value === '' || !Number.isFinite(passingScore) || passingScore > totalItems) {
            percentageCell.textContent = '—';
            resultCell.textContent = input.value === '' ? 'Not entered' : 'Set exam scores';
            resultCell.className = '';
            return;
        }
        const passed = score >= passingScore;
        percentageCell.textContent = `${(score / totalItems * 100).toFixed(2)}%`;
        resultCell.textContent = passed ? 'Passed' : 'Failed';
        resultCell.className = passed ? 'gened-item-analysis-passed' : 'gened-item-analysis-failed';
    }

    function clearSelection() {
        selectedSchedule = null;
        students = [];
        examDetails = null;
        renderExamInformation(null, 0, null);
        renderStudents();
        renderSummary({}, []);
        const exportButton = document.getElementById('genedItemAnalysisExport');
        if (exportButton) exportButton.disabled = true;
    }

    async function loadSelectedSchedule(scheduleId) {
        if (!scheduleId) {
            clearSelection();
            return;
        }
        setNotice('Loading exam details and scores...');
        try {
            const params = new URLSearchParams({
                action: 'details',
                schedule_id: scheduleId,
                department_id: document.getElementById(filterIds.department)?.value || '',
                section_id: document.getElementById(filterIds.section)?.value || ''
            });
            const response = await fetch(`${apiUrl}?${params}`, {
                credentials: 'same-origin'
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || `Unable to load exam details (${response.status}).`);
            }
            selectedSchedule = data.schedule;
            students = Array.isArray(data.students) ? data.students : [];
            examDetails = data;
            renderExamInformation(selectedSchedule, data.summary.total_students, data.settings);
            renderStudents();
            renderSummary(data.summary, data.section_results);
            document.getElementById('genedItemAnalysisExport').disabled = false;
            setNotice('Scores are saved per student and pass/fail results are summarized across the selected exam and subject.');
            return true;
        } catch (error) {
            clearSelection();
            setNotice(error.message || 'Unable to load exam details.', true);
            return false;
        }
    }

    async function saveScores() {
        if (!selectedSchedule) return;
        const totalItemsInput = document.getElementById('genedIaTotalItemsInput');
        const passingScoreInput = document.getElementById('genedIaPassingScoreInput');
        const totalItems = Number(totalItemsInput.value);
        const passingScore = Number(passingScoreInput.value);
        const saveButton = document.getElementById('genedIaSaveScores');
        if (!Number.isInteger(totalItems) || totalItems < 1 || !Number.isFinite(passingScore)
            || passingScoreInput.value === '' || passingScore < 0 || passingScore > totalItems) {
            setNotice('Enter a valid total item count and passing score within the exam score range.', true);
            return;
        }

        const scores = Array.from(document.querySelectorAll('#genedIaStudentRows .gened-item-analysis-score-input'))
            .map(input => ({student_id: Number(input.dataset.studentId), score: input.value}))
            .filter(item => item.score !== '');
        if (scores.some(item => !Number.isFinite(Number(item.score)) || Number(item.score) < 0 || Number(item.score) > totalItems)) {
            setNotice(`Each score must be between 0 and ${totalItems}.`, true);
            return;
        }

        saveButton.disabled = true;
        setNotice('Saving exam settings and student scores...');
        try {
            const response = await fetch(`${apiUrl}?action=save_scores`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    schedule_id: Number(selectedSchedule.schedule_id),
                    total_items: totalItems,
                    passing_score: passingScore,
                    scores
                })
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || `Unable to save scores (${response.status}).`);
            }
            if (!await loadSelectedSchedule(selectedSchedule.schedule_id)) return;
            setNotice(data.message);
        } catch (error) {
            setNotice(error.message || 'Unable to save student scores.', true);
        } finally {
            if (selectedSchedule) saveButton.disabled = false;
        }
    }

    function exportReport() {
        if (!selectedSchedule) return;
        const summary = examDetails?.summary || {};
        const rows = [
            ['GenEd Item Analysis Report'],
            ['Exam ID', `Exam #${selectedSchedule.exam_id}`],
            ['Academic Year', selectedSchedule.school_year],
            ['Semester', selectedSchedule.semester],
            ['Exam Title', selectedSchedule.exam_name],
            ['Subject Code', selectedSchedule.subject_code],
            ['GenEd Subject', selectedSchedule.subject_name],
            ['Department', selectedSchedule.department],
            ['Section', selectedSchedule.section_code],
            ['Faculty', selectedSchedule.faculty_names || 'Not recorded'],
            ['Exam Type', selectedSchedule.exam_type],
            ['Exam Date', selectedSchedule.exam_date],
            ['Total Items', examDetails?.settings?.total_items ?? 'Not recorded'],
            ['Passing Score', examDetails?.settings?.passing_score ?? 'Not recorded'],
            ['Total Students', summary.total_students ?? students.length],
            ['Scored Students', summary.scored_students ?? 0],
            ['Passed', summary.passed ?? 0],
            ['Failed', summary.failed ?? 0],
            ['Passing Rate', summary.passing_rate === null || summary.passing_rate === undefined ? 'Not available' : `${summary.passing_rate}%`],
            ['Failure Rate', summary.failure_rate === null || summary.failure_rate === undefined ? 'Not available' : `${summary.failure_rate}%`],
            ['Average Score', summary.average_score === null || summary.average_score === undefined
                ? 'Not available'
                : `${summary.average_score}/${examDetails?.settings?.total_items ?? '—'}`],
            [],
            ['Student Scores'],
            ['Student ID', 'Student Name', 'Score', 'Percentage', 'Result'],
            ...students.map(student => {
                const score = student.score === null ? null : Number(student.score);
                const totalItems = Number(examDetails?.settings?.total_items || 0);
                const passingScore = Number(examDetails?.settings?.passing_score || 0);
                return [
                    student.student_number,
                    student.student_name || 'Name not available',
                    score === null ? '' : `${score}/${totalItems}`,
                    score === null || !totalItems ? '' : `${(score / totalItems * 100).toFixed(2)}%`,
                    score === null ? 'Not entered' : score >= passingScore ? 'Passed' : 'Failed'
                ];
            }),
            [],
            ['Item Analysis'],
            ['Item No.', 'Correct', 'Incorrect', 'Difficulty Index', 'Discrimination Index', 'Remarks'],
            ['No per-question answers are recorded in the system.']
        ];
        const csv = `\uFEFF${rows.map(row => row.map(escapeCsv).join(',')).join('\r\n')}`;
        const blob = new Blob([csv], {type: 'text/csv;charset=utf-8'});
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = `gened-item-analysis-exam-${selectedSchedule.exam_id}-schedule-${selectedSchedule.schedule_id}.csv`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        window.setTimeout(() => URL.revokeObjectURL(url), 1000);
    }

    async function initialize() {
        if (!currentPage()) return;
        setNotice('Loading GenEd exam schedules...');
        try {
            const response = await fetch(apiUrl, {credentials: 'same-origin'});
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || `Unable to load GenEd exam schedules (${response.status}).`);
            }
            schedules = Array.isArray(data.schedules) ? data.schedules : [];
            filterCatalogs = data.filters || {};
            updateFilters();
            renderScheduleOptions();
            if (schedules.length) {
                setNotice(`${schedules.length} scheduled GenEd exam${schedules.length === 1 ? '' : 's'} available. Enter scores to calculate actual pass/fail results.`);
            } else {
                setNotice('No scheduled exams for active GenEd subjects were found. Create or assign exams through the existing scheduling workflow.');
            }
        } catch (error) {
            schedules = [];
            updateFilters();
            renderScheduleOptions();
            setNotice(error.message || 'Unable to load GenEd exam schedules.', true);
        }

        Object.values(filterIds).forEach(id => {
            const select = document.getElementById(id);
            if (select && select.dataset.itemAnalysisBound !== 'true') {
                select.dataset.itemAnalysisBound = 'true';
                select.addEventListener('change', () => {
                    renderScheduleOptions();
                    if (selectedSchedule && document.getElementById('genedIaSchedule')?.value) {
                        loadSelectedSchedule(selectedSchedule.schedule_id);
                    }
                });
            }
        });
        const scheduleSelect = document.getElementById('genedIaSchedule');
        if (scheduleSelect && scheduleSelect.dataset.itemAnalysisBound !== 'true') {
            scheduleSelect.dataset.itemAnalysisBound = 'true';
            scheduleSelect.addEventListener('change', event => {
                const value = event.target.value;
                if (value.startsWith('catalog:')) {
                    clearSelection();
                    const [, examType, subjectId] = value.split(':');
                    const examTypeLabel = (filterCatalogs.exam_types || [])
                        .find(item => String(item.value) === examType)?.label || examType;
                    const subject = (filterCatalogs.subjects || [])
                        .find(item => String(item.id) === subjectId);
                    setNotice(
                        `${examTypeLabel} for ${subject ? `${subject.code} - ${subject.name}` : 'this GenEd subject'} has no scheduled exam record for the selected filters.`,
                        true
                    );
                    return;
                }
                loadSelectedSchedule(value);
            });
        }
        const exportButton = document.getElementById('genedItemAnalysisExport');
        if (exportButton && exportButton.dataset.itemAnalysisBound !== 'true') {
            exportButton.dataset.itemAnalysisBound = 'true';
            exportButton.addEventListener('click', exportReport);
        }
        const saveButton = document.getElementById('genedIaSaveScores');
        if (saveButton && saveButton.dataset.itemAnalysisBound !== 'true') {
            saveButton.dataset.itemAnalysisBound = 'true';
            saveButton.addEventListener('click', saveScores);
        }
        const studentRows = document.getElementById('genedIaStudentRows');
        if (studentRows && studentRows.dataset.itemAnalysisBound !== 'true') {
            studentRows.dataset.itemAnalysisBound = 'true';
            studentRows.addEventListener('input', event => {
                if (!event.target.matches('.gened-item-analysis-score-input')) return;
                const row = event.target.closest('tr');
                updateStudentResult(event.target, row.cells[3], row.cells[4]);
            });
        }
        ['genedIaTotalItemsInput', 'genedIaPassingScoreInput'].forEach(id => {
            const input = document.getElementById(id);
            if (input && input.dataset.itemAnalysisBound !== 'true') {
                input.dataset.itemAnalysisBound = 'true';
                input.addEventListener('input', () => {
                    document.querySelectorAll('#genedIaStudentRows .gened-item-analysis-score-input').forEach(scoreInput => {
                        const row = scoreInput.closest('tr');
                        scoreInput.max = document.getElementById('genedIaTotalItemsInput').value;
                        updateStudentResult(scoreInput, row.cells[3], row.cells[4]);
                    });
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initialize);
    window.addEventListener('page:loaded', event => {
        if (event.detail?.page === 'item-analysis') initialize();
    });
})();
