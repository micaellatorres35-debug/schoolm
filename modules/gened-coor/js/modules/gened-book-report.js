(() => {
    let catalog = {periods: [], departments: [], assignments: []};
    let sampleSemester = '1st Semester, AY 2026–2027';
    const records = [
        ['BSCRIM','4102','FIL 1','Kontekstwalisadong Komunikasyon sa Filipino','Noli Me Tangere','Prof. Reyes',32,32,23],
        ['BSCRIM','4102','GE 7','Ethics','Nicomachean Ethics','Prof. Reyes',32,32,20],
        ['BSCRIM','4102','GE 4','Purposive Communication','Crucial Conversations','Prof. Reyes',32,32,27],
        ['BSCRIM','4103','FIL 2','Filipino sa iba’t ibang Disiplina','Mga Kuwento ni Lola Basyang','Prof. Santos',30,30,25],
        ['BSCRIM','4103','GE 5','Readings in the Philippine History','The Philippines: A Past Revisited','Prof. Santos',30,30,22],
        ['BSIT','1101','GE 1','Understanding the Self','Atomic Habits','Prof. Garcia',38,38,31],
        ['BSIT','1101','GE Elec 1','Living in IT Era','Weapons of Math Destruction','Prof. Garcia',38,38,28],
        ['BSIT','1101','GE 2','Mathematics in the Modern World','How Not to Be Wrong','Prof. Garcia',38,38,26],
        ['BSIT','1102','GE 3','Science, Technology and Society','Silent Spring','Prof. Cruz',35,35,27],
        ['BSIT','1102','GE Elec 3','Technopreneurship','Zero to One','Prof. Cruz',35,35,24],
        ['BSBA','2201','GE 4','Purposive Communication','How to Win Friends and Influence People','Prof. Mendoza',40,40,34],
        ['BSBA','2201','GE Elec 3','Technopreneurship','The Lean Startup','Prof. Mendoza',40,40,30],
        ['BSBA','2201','GE 8','The Contemporary World','Globalization and Its Discontents','Prof. Mendoza',40,40,29],
        ['BSBA','2202','GE 7','Ethics','Justice: What’s the Right Thing to Do?','Prof. Lopez',36,36,32],
        ['BSED','3301','GE 9','The Life and Works of Jose Rizal','Noli Me Tangere','Prof. Bautista',42,42,36],
        ['BSED','3301','FIL 1','Kontekstwalisadong Komunikasyon sa Filipino','Dekada ’70','Prof. Bautista',42,42,33],
        ['BSED','3302','FIL 3','Sosyedad at Literatura','Banaag at Sikat','Prof. Aquino',34,34,29],
        ['BSED','3302','GE 6','Art Appreciation','Why Is That Art?','Prof. Aquino',34,34,25],
        ['BSHM','1201','GE 6','Art Appreciation','The Story of Art','Prof. Diaz',28,28,22],
        ['BSHM','1201','GE Elec 2','Reading Visual Arts','Understanding Comics','Prof. Diaz',28,28,19],
        ['BSN','2101','GE 8','The Contemporary World','The World Is Flat','Prof. Ramos',32,32,26],
        ['BSN','2101','GE 3','Science, Technology and Society','The Immortal Life of Henrietta Lacks','Prof. Ramos',32,32,30],
        ['BSN','2101','PE 1','Physical Fitness 1','Spark','Prof. Ramos',32,32,28]
    ].map((row, index) => ({
        id: index + 1,
        department: row[0],
        section: row[1],
        subject: row[2],
        subjectName: row[3],
        book: row[4],
        teacher: row[5],
        students: row[6],
        given: row[7],
        claimed: row[8],
        semester: sampleSemester,
        status: 'Active'
    }));
    const bookSamples = records.map(record => ({
        book: record.book,
        claimRate: record.given > 0 ? record.claimed / record.given : 0
    }));
    let editingId = null;
    let nextId = records.length + 1;
    let keyboardHandlerBound = false;
    const get = id => document.getElementById(id);

    function periodKey(period) {
        return `${period.school_year_id}:${period.semester_id}`;
    }

    function periodLabel(period) {
        return `${period.semester}, AY ${period.school_year}`;
    }

    function replaceOptions(select, placeholder, options, disabled = false) {
        select.replaceChildren(new Option(placeholder, ''));
        options.forEach(option => select.appendChild(new Option(option.label, option.value)));
        select.disabled = disabled;
    }

    function loadCatalogOptions() {
        const periodFilter = get('genedBookReportSemester');
        const departmentFilter = get('genedBookReportDepartment');
        replaceOptions(periodFilter, 'All Semesters', catalog.periods.map(period => ({
            value: periodLabel(period),
            label: periodLabel(period)
        })));
        replaceOptions(departmentFilter, 'All', catalog.departments.map(department => ({
            value: department.code,
            label: `${department.code} - ${department.name}`
        })));

        const form = get('genedBookReportForm');
        const periodSelect = get('bookReportFormPeriod');
        replaceOptions(periodSelect, 'Select semester', catalog.periods.map(period => ({
            value: periodKey(period),
            label: periodLabel(period)
        })), catalog.periods.length === 0);
        if (catalog.periods.length) {
            const periodHasAssignments = period => catalog.assignments.some(assignment =>
                periodKey(assignment) === periodKey(period)
            );
            const activePeriod = catalog.periods.find(period =>
                Number(period.school_year_active) === 1 && Number(period.semester_active) === 1
                && periodHasAssignments(period)
            ) || catalog.periods.find(periodHasAssignments) || catalog.periods[0];
            periodSelect.value = periodKey(activePeriod);
            sampleSemester = periodLabel(activePeriod);
            records.forEach(record => { record.semester = sampleSemester; });
        }

        const departmentSelect = form.elements.department;
        const formDepartments = [...new Map(catalog.assignments.map(item => [
            String(item.department_id),
            {value: String(item.department_id), label: `${item.department_code} - ${item.department_name}`}
        ])).values()];
        replaceOptions(departmentSelect, 'Select department', formDepartments, formDepartments.length === 0);
        populateSections();
    }

    function buildSampleAssignments() {
        records.splice(0, records.length, ...catalog.assignments.map((assignment, index) => {
            const sample = bookSamples[index % bookSamples.length];
            const students = Number(assignment.enrolled_students) || 0;
            const given = students;
            return {
                id: index + 1,
                facultyLoadId: Number(assignment.faculty_load_id),
                department: assignment.department_code,
                section: assignment.section_code,
                subject: assignment.subject_code,
                subjectName: assignment.subject_name,
                book: sample.book,
                teacher: assignment.faculty_name,
                students,
                given,
                claimed: Math.min(given, Math.round(given * sample.claimRate)),
                semester: periodLabel(assignment),
                status: 'Active'
            };
        }));
        nextId = records.length + 1;
    }

    function selectedPeriodAssignments() {
        const key = get('bookReportFormPeriod').value;
        return catalog.assignments.filter(item => `${item.school_year_id}:${item.semester_id}` === key);
    }

    function populateSections(selectedSection = '') {
        const departmentId = get('bookReportFormDepartment').value;
        const sectionSelect = get('bookReportFormSection');
        const available = selectedPeriodAssignments().filter(item =>
            String(item.department_id) === departmentId
        );
        const sections = [...new Map(available.map(item => [
            String(item.section_id),
            {
                value: String(item.section_id),
                label: `${item.section_code}${item.grade_level ? ` · ${item.grade_level}` : ''}`
            }
        ])).values()];
        replaceOptions(sectionSelect, sections.length ? 'Select section' : 'No assigned GenEd sections', sections, sections.length === 0);
        if (sections.some(option => option.value === String(selectedSection))) sectionSelect.value = String(selectedSection);
        populateSubjects();
    }

    function populateSubjects(selectedSubject = '') {
        const departmentId = get('bookReportFormDepartment').value;
        const sectionId = get('bookReportFormSection').value;
        const available = selectedPeriodAssignments().filter(item =>
            String(item.department_id) === departmentId && String(item.section_id) === sectionId
        );
        const subjects = [...new Map(available.map(item => [
            String(item.subject_id),
            {value: String(item.subject_id), label: `${item.subject_code} - ${item.subject_name}`}
        ])).values()];
        const subjectSelect = get('bookReportFormSubject');
        replaceOptions(subjectSelect, subjects.length ? 'Select GenEd subject' : 'No assigned GenEd subjects', subjects, subjects.length === 0);
        if (subjects.some(option => option.value === String(selectedSubject))) subjectSelect.value = String(selectedSubject);
        populateFaculty();
    }

    function populateFaculty(selectedFaculty = '') {
        const departmentId = get('bookReportFormDepartment').value;
        const sectionId = get('bookReportFormSection').value;
        const subjectId = get('bookReportFormSubject').value;
        const available = selectedPeriodAssignments().filter(item =>
            String(item.department_id) === departmentId
            && String(item.section_id) === sectionId
            && String(item.subject_id) === subjectId
        );
        const faculty = [...new Map(available.map(item => [
            String(item.faculty_id),
            {value: String(item.faculty_id), label: item.faculty_name}
        ])).values()];
        const facultySelect = get('bookReportFormFaculty');
        replaceOptions(facultySelect, faculty.length ? 'Select assigned faculty' : 'No assigned faculty', faculty, faculty.length === 0);
        if (faculty.some(option => option.value === String(selectedFaculty))) facultySelect.value = String(selectedFaculty);
        updateEnrolledStudents();
    }

    function selectedAssignment() {
        const departmentId = get('bookReportFormDepartment').value;
        const sectionId = get('bookReportFormSection').value;
        const subjectId = get('bookReportFormSubject').value;
        const facultyId = get('bookReportFormFaculty').value;
        return selectedPeriodAssignments().find(item =>
            String(item.department_id) === departmentId
            && String(item.section_id) === sectionId
            && String(item.subject_id) === subjectId
            && String(item.faculty_id) === facultyId
        ) || null;
    }

    function updateEnrolledStudents() {
        const assignment = selectedAssignment();
        get('bookReportFormStudents').value = assignment ? String(assignment.enrolled_students) : '';
    }

    function filtered() {
        const term = get('genedBookReportSemester').value;
        const status = get('genedBookReportStatus').value;
        const department = get('genedBookReportDepartment').value;
        const query = get('genedBookReportSearch').value.trim().toLocaleLowerCase();
        return records.filter(item =>
            (!term || item.semester === term)
            && (!status || item.status === status)
            && (!department || item.department === department)
            && (!query || [item.department,item.section,item.subject,item.subjectName,item.book,item.teacher,item.status]
                .some(value => value.toLocaleLowerCase().includes(query)))
        );
    }

    function cell(row, value, className = '') {
        const td = row.insertCell();
        td.textContent = String(value);
        if (className) td.className = className;
        return td;
    }

    function render() {
        const shown = filtered();
        const body = get('genedBookReportRows');
        body.replaceChildren();
        if (!shown.length) {
            const row = body.insertRow();
            const td = row.insertCell();
            td.colSpan = 11;
            td.className = 'gened-book-report-empty';
            td.textContent = 'Walang records para sa napiling filters.';
        } else {
            shown.forEach(item => {
                const row = body.insertRow();
                row.dataset.id = item.id;
                cell(row, item.department);
                cell(row, item.section);
                const subject = row.insertCell();
                subject.className = 'book-report-subject';
                const code = document.createElement('strong');
                code.textContent = item.subject;
                const name = document.createElement('small');
                name.textContent = item.subjectName;
                subject.append(code, name);
                cell(row, item.book);
                cell(row, item.teacher);
                cell(row, item.students);
                cell(row, item.given);
                cell(row, item.claimed);
                cell(row, Math.max(0, item.given - item.claimed));
                const status = row.insertCell();
                const badge = document.createElement('span');
                badge.className = `book-report-status${item.status === 'Archived' ? ' is-archived' : ''}`;
                badge.textContent = item.status;
                status.appendChild(badge);
                const actions = row.insertCell();
                actions.className = 'gened-book-report-actions';
                [
                    ['View','view',''],
                    ['Update','edit',''],
                    [item.status === 'Active' ? 'Archive' : 'Restore','archive','book-report-archive']
                ].forEach(([label, action, className]) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.action = action;
                    button.className = className;
                    button.textContent = label;
                    actions.appendChild(button);
                });
            });
        }
        updateSummary(shown);
        updateDepartments(shown);
    }

    function updateSummary(shown) {
        const given = shown.reduce((sum, item) => sum + item.given, 0);
        const claimed = shown.reduce((sum, item) => sum + item.claimed, 0);
        get('genedBooksDistributed').textContent = String(given);
        get('genedBooksClaimed').textContent = String(claimed);
        get('genedBooksUnclaimed').textContent = String(Math.max(0, given - claimed));
        get('genedBookRecordCount').textContent = String(shown.length);
        get('genedBookTitleCount').textContent = String(new Set(shown.map(item => item.book)).size);
    }

    function updateDepartments(shown) {
        const output = get('genedBookReportPage').querySelector('.gened-book-report-departments');
        output.replaceChildren();
        catalog.departments.forEach(department => {
            const span = document.createElement('span');
            span.append(`${department.code}: `);
            const strong = document.createElement('strong');
            strong.textContent = String(shown.filter(item => item.department === department.code)
                .reduce((sum, item) => sum + item.claimed, 0));
            span.appendChild(strong);
            output.appendChild(span);
        });
    }

    function close(modal) {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    }

    function openEdit(item = null) {
        const form = get('genedBookReportForm');
        if (!catalog.assignments.length) {
            const notice = get('genedBookReportCatalogNotice');
            notice.textContent = 'Walang GenEd faculty-load assignment. Magtalaga muna ng GenEd faculty, section, at subject.';
            notice.classList.add('is-error');
            return;
        }
        form.reset();
        get('genedBookReportFormError').textContent = '';
        editingId = item?.id ?? null;
        get('genedBookReportModalTitle').textContent = item ? 'I-update ang Book Assignment' : 'Mag-assign ng Books';
        if (item) {
            form.elements.book.value = item.book;
            form.elements.given.value = item.given;
            form.elements.claimed.value = item.claimed;
            const assignment = catalog.assignments.find(candidate =>
                candidate.department_code === item.department
                && candidate.section_code === item.section
                && candidate.subject_code === item.subject
                && candidate.faculty_name === item.teacher
            ) || catalog.assignments[0];
            get('bookReportFormPeriod').value = `${assignment.school_year_id}:${assignment.semester_id}`;
            form.elements.department.value = String(assignment.department_id);
            populateSections(assignment.section_id);
            populateSubjects(assignment.subject_id);
            populateFaculty(assignment.faculty_id);
        } else {
            form.elements.department.value = '';
            populateSections();
        }
        const modal = get('genedBookReportModal');
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        form.elements.department.focus();
    }

    function showView(item) {
        const container = get('genedBookReportViewDetails');
        container.replaceChildren();
        [
            ['Semester',item.semester],['Department',item.department],['Section',item.section],
            ['Subject',`${item.subject} — ${item.subjectName}`],['Book Title',item.book],['Teacher',item.teacher],
            ['Students',item.students],['Binigay',item.given],['Nagbayad / kumuha',item.claimed],
            ['Di kinuha',Math.max(0,item.given-item.claimed)],['Status',item.status]
        ].forEach(([label,value]) => {
            const box = document.createElement('div');
            const title = document.createElement('span');
            title.textContent = label;
            const detail = document.createElement('strong');
            detail.textContent = String(value);
            box.append(title,detail);
            container.appendChild(box);
        });
        const modal = get('genedBookReportViewModal');
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden','false');
    }

    async function initialize() {
        const page = get('genedBookReportPage');
        if (!page || page.dataset.initialized === 'true') return;
        page.dataset.initialized = 'true';
        const notice = get('genedBookReportCatalogNotice');
        try {
            const response = await fetch(
                new URL('api/gened_book_report_catalog.php', window.location.href),
                {credentials: 'same-origin'}
            );
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || `Unable to load GenEd assignments (${response.status}).`);
            }
            catalog = {
                periods: Array.isArray(data.periods) ? data.periods : [],
                departments: Array.isArray(data.departments) ? data.departments : [],
                assignments: Array.isArray(data.assignments) ? data.assignments : []
            };
            loadCatalogOptions();
            buildSampleAssignments();
            if (catalog.assignments.length) {
                notice.textContent = 'Ang department, section, subject, faculty, at bilang ng enrolled students ay mula sa GenEd assignments. Pamagat ng libro at bilang ng kumuha ay sample data lamang.';
                notice.classList.remove('is-error');
            } else {
                notice.textContent = 'Walang available na GenEd faculty-load assignment. Hindi makakapili ng department, section, subject, o faculty hanggang may maitalagang GenEd load.';
                notice.classList.add('is-error');
            }
        } catch (error) {
            notice.textContent = error.message || 'Hindi ma-load ang GenEd assignment catalog.';
            notice.classList.add('is-error');
            get('genedBookReportAdd').disabled = true;
        }
        render();
        ['genedBookReportSemester','genedBookReportStatus','genedBookReportDepartment'].forEach(id => get(id).addEventListener('change',render));
        get('genedBookReportSearch').addEventListener('input',render);
        get('genedBookReportAdd').addEventListener('click',() => openEdit());
        get('bookReportFormPeriod').addEventListener('change', () => {
            get('bookReportFormDepartment').value = '';
            populateSections();
        });
        get('bookReportFormDepartment').addEventListener('change', () => populateSections());
        get('bookReportFormSection').addEventListener('change', () => populateSubjects());
        get('bookReportFormSubject').addEventListener('change', () => populateFaculty());
        get('bookReportFormFaculty').addEventListener('change', updateEnrolledStudents);
        get('genedBookReportRows').addEventListener('click',event => {
            const button = event.target.closest('button[data-action]');
            const item = records.find(record => String(record.id) === button?.closest('tr')?.dataset.id);
            if (!button || !item) return;
            if (button.dataset.action === 'view') showView(item);
            if (button.dataset.action === 'edit') openEdit(item);
            if (button.dataset.action === 'archive') {
                item.status = item.status === 'Active' ? 'Archived' : 'Active';
                render();
            }
        });
        const editModal = get('genedBookReportModal');
        const viewModal = get('genedBookReportViewModal');
        document.querySelectorAll('[data-book-report-close]').forEach(button => button.addEventListener('click',() => close(editModal)));
        document.querySelectorAll('[data-book-report-view-close]').forEach(button => button.addEventListener('click',() => close(viewModal)));
        [editModal,viewModal].forEach(modal => modal.addEventListener('click',event => {
            if (event.target === modal) close(modal);
        }));
        if (!keyboardHandlerBound) {
            keyboardHandlerBound = true;
            document.addEventListener('keydown',event => {
                if (event.key === 'Escape') {
                    close(get('genedBookReportModal'));
                    close(get('genedBookReportViewModal'));
                }
            });
        }
        get('genedBookReportForm').addEventListener('submit',event => {
            event.preventDefault();
            const form = event.currentTarget;
            const values = Object.fromEntries(new FormData(form).entries());
            const students = Number(values.students);
            const given = Number(values.given);
            const claimed = Number(values.claimed);
            const error = get('genedBookReportFormError');
            const assignment = selectedAssignment();
            const selectedPeriod = catalog.periods.find(period => periodKey(period) === values.period);
            if (!assignment || !selectedPeriod) {
                error.textContent = 'Pumili ng valid na GenEd department, section, subject, faculty, at semester.';
                return;
            }
            if (claimed > given || given > students) {
                error.textContent = claimed > given
                    ? 'Hindi puwedeng mas mataas ang nagbayad/kumuha kaysa sa bilang ng binigay.'
                    : 'Hindi puwedeng mas mataas ang bilang ng binigay kaysa sa bilang ng estudyante.';
                return;
            }
            const item = {
                id: editingId || nextId++,
                semester: periodLabel(selectedPeriod),
                department: assignment.department_code,
                section: assignment.section_code,
                subject: assignment.subject_code,
                subjectName: assignment.subject_name,
                book: values.book.trim(),
                teacher: assignment.faculty_name,
                students: Number(assignment.enrolled_students),
                given,claimed,
                status: records.find(record => record.id === editingId)?.status || 'Active'
            };
            const index = records.findIndex(record => record.id === editingId);
            if (index < 0) records.push(item);
            else records[index] = item;
            close(editModal);
            render();
        });
    }

    document.addEventListener('DOMContentLoaded',initialize);
    window.addEventListener('page:loaded',event => {
        if (event.detail?.page === 'gened-book-report') initialize();
    });
})();
