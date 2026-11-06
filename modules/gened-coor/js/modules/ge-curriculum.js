function initGeCurriculumPage() {
    const root = document.getElementById('geCurriculumModule');
    if (!root || root.dataset.initialized === 'true') return;
    root.dataset.initialized = 'true';

    const state = { subjects: [] };
    const subjectBody = document.querySelector('#geSubjectsTable tbody');
    const subjectModal = document.getElementById('geSubjectModal');
    const subjectForm = document.getElementById('geSubjectForm');

    async function request(action = null, payload = null) {
        const options = payload === null ? {} : {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action, ...payload })
        };
        const response = await fetch(new URL('api/ge_curriculum.php', window.location.href), options);
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Unable to load General Education curriculum data.');
        }
        return data;
    }

    function setMessage(id, message, isError = false) {
        const element = document.getElementById(id);
        if (!element) return;
        element.textContent = message;
        element.classList.toggle('text-danger', isError);
        element.classList.toggle('text-muted', !isError);
    }

    function addCell(row, value) {
        const cell = document.createElement('td');
        cell.textContent = value === null || value === undefined || value === '' ? 'N/A' : String(value);
        row.appendChild(cell);
        return cell;
    }

    function addAction(row, label, action, id, className = 'btn btn-sm btn-outline-secondary') {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.dataset.action = action;
        button.dataset.id = String(id);
        button.textContent = label;
        row.lastElementChild.appendChild(button);
    }

    function emptyRow(body, colspan, message) {
        body.replaceChildren();
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = colspan;
        cell.className = 'text-center text-muted';
        cell.textContent = message;
        row.appendChild(cell);
        body.appendChild(row);
    }

    function subjectCategory(code) {
        const normalizedCode = String(code || '').trim().toUpperCase();
        if (normalizedCode.startsWith('FIL ')) return 'filipino';
        if (normalizedCode.startsWith('NSTP ')) return 'nstp';
        if (normalizedCode.startsWith('PE ')) return 'pe';
        return 'general-education';
    }

    function renderSubjectTotals() {
        const activeSubjects = state.subjects.filter(subject => !subject.archived_at);
        const totals = {
            geTotalSubjects: activeSubjects.length,
            geTotalUnits: activeSubjects.reduce((sum, subject) => sum + (Number(subject.units) || 0), 0),
            geTotalLecture: activeSubjects.reduce((sum, subject) => sum + (Number(subject.lecture_hours) || 0), 0),
            geTotalLaboratory: activeSubjects.reduce((sum, subject) => sum + (Number(subject.lab_hours) || 0), 0)
        };
        Object.entries(totals).forEach(([id, value]) => {
            document.getElementById(id).textContent = String(value);
        });
    }

    function renderSubjects() {
        const term = document.getElementById('geSubjectSearch').value.trim().toLowerCase();
        const selectedCategory = document.getElementById('geSubjectCategoryFilter').value;
        const selectedStatus = document.getElementById('geSubjectStatusFilter').value;
        const subjects = state.subjects.filter(subject => {
            const category = subjectCategory(subject.code);
            const status = subject.archived_at ? 'archived' : 'active';
            const matchesSearch = `${subject.code} ${subject.name} ${subject.description || ''}`.toLowerCase().includes(term);
            return matchesSearch
                && (selectedCategory === 'all' || selectedCategory === category)
                && (selectedStatus === 'all' || selectedStatus === status);
        });
        if (!subjects.length) return emptyRow(subjectBody, 7, 'No GenEd subjects found.');
        subjectBody.replaceChildren();
        subjects.forEach(subject => {
            const row = document.createElement('tr');
            addCell(row, subject.code);
            addCell(row, subject.name);
            addCell(row, subject.description);
            addCell(row, subject.units);
            addCell(row, subject.lecture_hours);
            addCell(row, subject.lab_hours);
            const actions = document.createElement('td');
            actions.className = 'actions';
            const statusBadge = document.createElement('span');
            statusBadge.className = `badge ${subject.archived_at ? 'badge-secondary' : 'badge-info'}`;
            statusBadge.textContent = subject.archived_at ? 'Archived' : 'Active';
            actions.appendChild(statusBadge);
            row.appendChild(actions);
            if (subject.archived_at) {
                addAction(row, 'Restore', 'restore-subject', subject.id, 'btn btn-sm btn-outline-primary');
            } else {
                addAction(row, 'Edit', 'edit-subject', subject.id, 'btn btn-sm btn-outline-primary');
                addAction(row, 'Archive', 'archive-subject', subject.id, 'btn btn-sm btn-outline-danger');
            }
            subjectBody.appendChild(row);
        });
    }

    function setActiveTab(tabName) {
        root.querySelectorAll('[data-ge-tab]').forEach(tab => {
            const isActive = tab.dataset.geTab === tabName;
            tab.classList.toggle('active', isActive);
            tab.setAttribute('aria-selected', String(isActive));
        });
        root.querySelectorAll('[data-ge-panel]').forEach(panel => {
            panel.classList.toggle('active', panel.dataset.gePanel === tabName);
        });
        root.querySelectorAll('[data-ge-summary]').forEach(summary => {
            summary.hidden = summary.dataset.geSummary !== tabName;
        });
        root.querySelectorAll('[data-ge-filters]').forEach(filters => {
            filters.hidden = filters.dataset.geFilters !== tabName;
        });
        document.querySelectorAll('[data-ge-header-action]').forEach(action => {
            action.hidden = action.dataset.geHeaderAction !== tabName;
        });
    }

    async function loadData() {
        try {
            const data = await request();
            Object.assign(state, data);
            renderSubjectTotals();
            renderSubjects();
        } catch (error) {
            ['geTotalSubjects', 'geTotalUnits', 'geTotalLecture', 'geTotalLaboratory'].forEach(id => {
                document.getElementById(id).textContent = '--';
            });
            emptyRow(subjectBody, 7, error.message);
        }
    }

    function closeSubjectModal() {
        subjectModal.classList.remove('show');
    }

    function openSubjectModal(subject = null) {
        subjectForm.reset();
        document.getElementById('geSubjectId').value = subject?.id || '';
        document.getElementById('geSubjectCode').value = subject?.code || '';
        document.getElementById('geSubjectName').value = subject?.name || '';
        document.getElementById('geSubjectDescription').value = subject?.description || '';
        document.getElementById('geSubjectUnits').value = subject?.units ?? 3;
        document.getElementById('geSubjectLecture').value = subject?.lecture_hours ?? 3;
        document.getElementById('geSubjectLab').value = subject?.lab_hours ?? 0;
        document.getElementById('geSubjectModalTitle').textContent = subject ? 'Edit GE Subject' : 'Add GE Subject';
        setMessage('geSubjectMessage', '');
        subjectModal.classList.add('show');
    }

    async function runAction(action, payload, messageId) {
        try {
            const result = await request(action, payload);
            setMessage(messageId, result.message || 'Saved.');
            await loadData();
            return true;
        } catch (error) {
            setMessage(messageId, error.message, true);
            return false;
        }
    }

    root.querySelectorAll('[data-ge-tab]').forEach(button => {
        button.addEventListener('click', () => setActiveTab(button.dataset.geTab));
    });

    document.getElementById('geAddSubjectBtn').addEventListener('click', () => openSubjectModal());
    document.getElementById('geAssignFacultyBtn').addEventListener('click', () => {
        setActiveTab('assignments');
        document.getElementById('geFacultyAssignmentSearch').focus();
    });
    document.getElementById('geSubjectSearch').addEventListener('input', renderSubjects);
    document.getElementById('geSubjectCategoryFilter').addEventListener('change', renderSubjects);
    document.getElementById('geSubjectStatusFilter').addEventListener('change', renderSubjects);
    subjectModal.querySelectorAll('.modal-close').forEach(button => button.addEventListener('click', closeSubjectModal));
    subjectModal.addEventListener('click', event => {
        if (event.target === subjectModal) closeSubjectModal();
    });

    subjectBody.addEventListener('click', async event => {
        const button = event.target.closest('[data-action]');
        if (!button) return;
        const subject = state.subjects.find(item => String(item.id) === button.dataset.id);
        if (button.dataset.action === 'edit-subject') openSubjectModal(subject);
        if (button.dataset.action === 'archive-subject' && window.confirm(`Archive ${subject?.code || 'this subject'}? It will be hidden from active GE subject lists, but existing assignments and curriculum mappings will be preserved.`)) {
            await runAction('archive_subject', { id: button.dataset.id }, 'geSubjectMessage');
        }
        if (button.dataset.action === 'restore-subject' && window.confirm(`Restore ${subject?.code || 'this subject'} to the active curriculum?`)) {
            await runAction('restore_subject', { id: button.dataset.id }, 'geSubjectMessage');
        }
    });

    subjectForm.addEventListener('submit', async event => {
        event.preventDefault();
        const payload = {
            id: document.getElementById('geSubjectId').value,
            code: document.getElementById('geSubjectCode').value,
            name: document.getElementById('geSubjectName').value,
            description: document.getElementById('geSubjectDescription').value,
            units: document.getElementById('geSubjectUnits').value,
            lecture_hours: document.getElementById('geSubjectLecture').value,
            lab_hours: document.getElementById('geSubjectLab').value
        };
        if (await runAction('save_subject', payload, 'geSubjectMessage')) closeSubjectModal();
    });

    // ---- Faculty Assignments (moved from Faculty Management) ----
    function initGeFacultyAssignments() {
        const assignmentTable = document.getElementById('facultyAssignmentsTable');
        const sectionsModal = document.getElementById('fmAssignSectionsModal');
        const subjectModal = document.getElementById('fmAssignSubjectModal');
        const assignmentsViewModal = document.getElementById('fmFacultyAssignmentsViewModal');
        const sectionsForm = document.getElementById('fmAssignSectionsForm');
        const subjectForm = document.getElementById('fmAssignSubjectForm');
        const assignmentApiUrl = endpoint => new URL(`api/${endpoint}`, window.location.href).toString();
        let facultyLoads = [];

        async function requestAssignmentData(endpoint, options = {}) {
            const response = await fetch(assignmentApiUrl(endpoint), options);
            const data = await response.json();
            if (!response.ok || data.success === false || data.error) {
                throw new Error(data.message || data.error || 'Unable to complete faculty assignment request.');
            }
            return data;
        }

        function renderFacultyAssignmentSummary() {
            const totals = {
                geTotalFaculty: facultyLoads.length,
                geTotalAssignedSections: facultyLoads.reduce((sum, faculty) => sum + (Number(faculty.assigned_sections) || 0), 0),
                geTotalAssignedSubjects: facultyLoads.reduce((sum, faculty) => sum + (Number(faculty.assigned_subjects) || 0), 0),
                geUnderloadedFaculty: facultyLoads.filter(faculty => faculty.load_status === 'Underloaded').length
            };
            Object.entries(totals).forEach(([id, value]) => {
                document.getElementById(id).textContent = String(value);
            });
        }

        function updateDepartmentFilter() {
            const departmentFilter = document.getElementById('geFacultyDepartmentFilter');
            const selectedDepartment = departmentFilter.value;
            const departments = [...new Set(facultyLoads.map(faculty => faculty.department || 'Department not assigned'))].sort((a, b) => a.localeCompare(b));
            departmentFilter.replaceChildren(new Option('All Departments', 'all'));
            departments.forEach(department => departmentFilter.add(new Option(department, department)));
            departmentFilter.value = departments.includes(selectedDepartment) ? selectedDepartment : 'all';
        }

        function renderFacultyAssignmentRows() {
            const tbody = assignmentTable?.querySelector('tbody');
            if (!tbody) return;
            tbody.replaceChildren();

            const searchTerm = document.getElementById('geFacultyAssignmentSearch').value.trim().toLowerCase();
            const selectedDepartment = document.getElementById('geFacultyDepartmentFilter').value;
            const selectedLoadStatus = document.getElementById('geFacultyLoadFilter').value;
            const visibleFaculty = facultyLoads.filter(faculty => {
                const department = faculty.department || 'Department not assigned';
                const loadStatus = faculty.load_status || 'Underloaded';
                const searchText = `${faculty.faculty_name || ''} ${department} ${loadStatus}`.toLowerCase();
                return searchText.includes(searchTerm)
                    && (selectedDepartment === 'all' || selectedDepartment === department)
                    && (selectedLoadStatus === 'all' || selectedLoadStatus === loadStatus);
            });

            if (visibleFaculty.length === 0) {
                const row = document.createElement('tr');
                const cell = document.createElement('td');
                cell.colSpan = 7;
                cell.className = 'text-center text-muted';
                cell.textContent = facultyLoads.length ? 'No faculty assignments match your filters.' : 'No faculty records available for assignment.';
                row.appendChild(cell);
                tbody.appendChild(row);
                return;
            }

            visibleFaculty.forEach(faculty => {
                const row = document.createElement('tr');
                const values = [
                    faculty.faculty_name || 'N/A',
                    faculty.department || 'Department not assigned',
                    faculty.assigned_sections ?? 0,
                    faculty.assigned_subjects ?? 0,
                    `${faculty.teaching_units ?? 0} / ${faculty.max_load ?? 15}`,
                    faculty.load_status || 'Underloaded'
                ];
                values.forEach(value => {
                    const cell = document.createElement('td');
                    cell.textContent = String(value);
                    row.appendChild(cell);
                });

                const actions = document.createElement('td');
                actions.className = 'actions';
                [
                    ['View', 'fmViewAssignments', 'btn btn-sm btn-info'],
                    ['Assign Sections', 'fmAssignSections', 'btn btn-sm btn-outline-primary'],
                    ['Assign Subject', 'fmAssignSubject', 'btn btn-sm btn-outline-secondary']
                ].forEach(([label, action, className]) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = className;
                    button.dataset[action] = 'true';
                    button.dataset.facultyId = String(faculty.id);
                    button.dataset.facultyName = faculty.faculty_name || '';
                    button.textContent = label;
                    actions.appendChild(button);
                });
                row.appendChild(actions);
                tbody.appendChild(row);
            });
        }

        async function reloadFacultyAssignments() {
            if (!assignmentTable) return;
            const tbody = assignmentTable.querySelector('tbody');
            if (tbody) {
                const row = document.createElement('tr');
                row.innerHTML = '<td colspan="7" class="text-center text-muted">Refreshing assignments...</td>';
                tbody.replaceChildren(row);
            }
            try {
                facultyLoads = await requestAssignmentData('get_faculty_load.php');
                if (!Array.isArray(facultyLoads)) {
                    throw new Error('Unable to load faculty assignment records.');
                }
                updateDepartmentFilter();
                renderFacultyAssignmentSummary();
                renderFacultyAssignmentRows();
            } catch (error) {
                Object.values({
                    faculty: 'geTotalFaculty',
                    sections: 'geTotalAssignedSections',
                    subjects: 'geTotalAssignedSubjects',
                    underloaded: 'geUnderloadedFaculty'
                }).forEach(id => {
                    document.getElementById(id).textContent = '--';
                });
                facultyLoads = [];
                renderFacultyAssignmentRows();
                if (tbody?.firstElementChild) {
                    tbody.firstElementChild.firstElementChild.textContent = error.message;
                    tbody.firstElementChild.firstElementChild.classList.add('text-danger');
                }
            }
        }

        async function openFacultyAssignments(button) {
            if (!assignmentsViewModal) return;
            const facultyName = button.dataset.facultyName || 'Faculty';
            document.getElementById('fmFacultyAssignmentsViewName').textContent = facultyName;
            const tbody = document.getElementById('fmFacultyAssignmentsViewBody');
            tbody.replaceChildren();
            const loadingRow = tbody.insertRow();
            const loadingCell = loadingRow.insertCell();
            loadingCell.colSpan = 8;
            loadingCell.className = 'text-center text-muted';
            loadingCell.textContent = 'Loading assignments...';
            assignmentsViewModal.classList.add('show');

            try {
                const facultyId = button.dataset.facultyId || '';
                const data = await requestAssignmentData(`get_faculty_assignments.php?faculty_id=${encodeURIComponent(facultyId)}`);
                tbody.replaceChildren();
                if (!data.assignments?.length) {
                    const row = tbody.insertRow();
                    const cell = row.insertCell();
                    cell.colSpan = 8;
                    cell.className = 'text-center text-muted';
                    cell.textContent = 'No subject assignments found for this faculty.';
                    return;
                }

                data.assignments.forEach(assignment => {
                    const row = tbody.insertRow();
                    [
                        assignment.section_code,
                        [assignment.subject_code, assignment.subject_name].filter(Boolean).join(' - '),
                        assignment.units,
                        [assignment.semester, assignment.school_year].filter(Boolean).join(' / '),
                        assignment.day_of_week,
                        assignment.time_range,
                        assignment.room_code,
                        assignment.schedule_status
                    ].forEach(value => {
                        row.insertCell().textContent = value === null || value === undefined || value === '' ? 'N/A' : String(value);
                    });
                });
            } catch (error) {
                tbody.replaceChildren();
                const row = tbody.insertRow();
                const cell = row.insertCell();
                cell.colSpan = 8;
                cell.className = 'text-center text-danger';
                cell.textContent = error.message || 'Unable to load faculty assignments.';
            }
        }

        function setAssignmentMessage(elementId, message, isError = false) {
            const element = document.getElementById(elementId);
            if (!element) return;
            element.textContent = message;
            element.classList.toggle('text-danger', isError);
            element.classList.toggle('text-muted', !isError);
        }

        function closeAssignmentModal(modal) {
            modal?.classList.remove('show');
        }

        async function loadAssignmentSections() {
            const schoolYearId = document.getElementById('fmAssignSectionsSchoolYear')?.value;
            const semesterId = document.getElementById('fmAssignSectionsSemester')?.value;
            const facultyId = document.getElementById('fmAssignSectionsFacultyId')?.value;
            const sectionSelect = document.getElementById('fmAssignSectionsList');
            if (!sectionSelect) return;
            sectionSelect.replaceChildren(new Option('Loading sections...', ''));
            sectionSelect.disabled = true;
            if (!schoolYearId || !semesterId || !facultyId) return;

            try {
                const query = new URLSearchParams({ school_year_id: schoolYearId, semester_id: semesterId, faculty_id: facultyId });
                const data = await requestAssignmentData(`get_adviser_sections.php?${query}`);
                sectionSelect.replaceChildren();
                if (!data.sections?.length) {
                    sectionSelect.add(new Option('No unassigned sections for this period', ''));
                    return;
                }
                data.sections.forEach(section => {
                    const label = [section.section_code, section.program_code, section.grade_level].filter(Boolean).join(' | ');
                    sectionSelect.add(new Option(label, section.id));
                });
                sectionSelect.disabled = false;
                setAssignmentMessage('fmAssignSectionsMessage', '');
            } catch (error) {
                sectionSelect.replaceChildren(new Option('Unable to load sections', ''));
                setAssignmentMessage('fmAssignSectionsMessage', error.message, true);
            }
        }

        async function loadAssignmentSemesters() {
            const schoolYearId = document.getElementById('fmAssignSectionsSchoolYear')?.value;
            const semesterSelect = document.getElementById('fmAssignSectionsSemester');
            const sectionSelect = document.getElementById('fmAssignSectionsList');
            if (!semesterSelect || !sectionSelect) return;
            semesterSelect.replaceChildren(new Option('Loading semesters...', ''));
            semesterSelect.disabled = true;
            sectionSelect.replaceChildren(new Option('Select semester first', ''));
            sectionSelect.disabled = true;
            if (!schoolYearId) return;

            try {
                const data = await requestAssignmentData(`get_semesters.php?school_year_id=${encodeURIComponent(schoolYearId)}`);
                semesterSelect.replaceChildren(new Option('Select semester', ''));
                (data.semesters || []).forEach(semester => semesterSelect.add(new Option(semester.name, semester.id)));
                if (!data.semesters?.length) {
                    semesterSelect.replaceChildren(new Option('No active semesters', ''));
                    return;
                }
                semesterSelect.disabled = false;
            } catch (error) {
                semesterSelect.replaceChildren(new Option('Unable to load semesters', ''));
                setAssignmentMessage('fmAssignSectionsMessage', error.message, true);
            }
        }

        async function openAssignSections(button) {
            if (!sectionsModal) return;
            document.getElementById('fmAssignSectionsFacultyId').value = button.dataset.facultyId || '';
            document.getElementById('fmAssignSectionsFacultyName').value = button.dataset.facultyName || '';
            setAssignmentMessage('fmAssignSectionsMessage', '');
            const yearSelect = document.getElementById('fmAssignSectionsSchoolYear');
            const semesterSelect = document.getElementById('fmAssignSectionsSemester');
            const sectionSelect = document.getElementById('fmAssignSectionsList');
            yearSelect.replaceChildren(new Option('Loading school years...', ''));
            yearSelect.disabled = true;
            semesterSelect.replaceChildren(new Option('Select school year first', ''));
            semesterSelect.disabled = true;
            sectionSelect.replaceChildren(new Option('Select semester first', ''));
            sectionSelect.disabled = true;
            sectionsModal.classList.add('show');

            try {
                const data = await requestAssignmentData('get_school_years.php');
                yearSelect.replaceChildren(new Option('Select school year', ''));
                (data.school_years || []).forEach(year => yearSelect.add(new Option(year.name, year.id)));
                if (!data.school_years?.length) {
                    yearSelect.replaceChildren(new Option('No active school years', ''));
                    return;
                }
                yearSelect.disabled = false;
                yearSelect.value = String(data.school_years[0].id);
                await loadAssignmentSemesters();
            } catch (error) {
                yearSelect.replaceChildren(new Option('Unable to load school years', ''));
                setAssignmentMessage('fmAssignSectionsMessage', error.message, true);
            }
        }

        async function loadSubjectOptions(sectionId) {
            const subjectSelect = document.getElementById('fmAssignSubjectSubject');
            const submitButton = subjectForm?.querySelector('button[type="submit"]');
            if (!subjectSelect) return;
            subjectSelect.replaceChildren(new Option('Loading subjects...', ''));
            subjectSelect.disabled = true;
            if (submitButton) submitButton.disabled = true;
            if (!sectionId) return;

            try {
                const data = await requestAssignmentData(`get_section_subjects.php?section_id=${encodeURIComponent(sectionId)}`);
                subjectSelect.replaceChildren(new Option('Select subject', ''));
                (data.subjects || []).forEach(subject => {
                    subjectSelect.add(new Option(`${subject.code} - ${subject.name} (${subject.units} units)`, subject.id));
                });
                if (!data.subjects?.length) {
                    subjectSelect.replaceChildren(new Option(data.message || 'No curriculum subjects found', ''));
                    setAssignmentMessage('fmAssignSubjectMessage', data.message || 'No curriculum subjects found for this section.');
                    return;
                }
                subjectSelect.disabled = false;
                if (submitButton) submitButton.disabled = false;
                setAssignmentMessage('fmAssignSubjectMessage', '');
            } catch (error) {
                subjectSelect.replaceChildren(new Option('Unable to load subjects', ''));
                setAssignmentMessage('fmAssignSubjectMessage', error.message, true);
            }
        }

        async function openAssignSubject(button) {
            if (!subjectModal) return;
            document.getElementById('fmAssignSubjectFacultyId').value = button.dataset.facultyId || '';
            document.getElementById('fmAssignSubjectFacultyName').value = button.dataset.facultyName || '';
            setAssignmentMessage('fmAssignSubjectMessage', '');
            const sectionSelect = document.getElementById('fmAssignSubjectSection');
            const subjectSelect = document.getElementById('fmAssignSubjectSubject');
            const submitButton = subjectForm?.querySelector('button[type="submit"]');
            sectionSelect.replaceChildren(new Option('Loading assigned sections...', ''));
            sectionSelect.disabled = true;
            subjectSelect.replaceChildren(new Option('Select a section first', ''));
            subjectSelect.disabled = true;
            if (submitButton) submitButton.disabled = true;
            subjectModal.classList.add('show');

            try {
                const data = await requestAssignmentData(`get_instructor_sections.php?faculty_id=${encodeURIComponent(button.dataset.facultyId || '')}`);
                sectionSelect.replaceChildren(new Option('Select section', ''));
                (data.sections || []).forEach(section => {
                    const label = [section.section_code, section.program_code, section.semester, section.school_year].filter(Boolean).join(' | ');
                    sectionSelect.add(new Option(label, section.section_id));
                });
                if (!data.sections?.length) {
                    sectionSelect.replaceChildren(new Option('Assign faculty to a section first', ''));
                    setAssignmentMessage('fmAssignSubjectMessage', 'Assign this faculty member to a section before assigning subjects.');
                    return;
                }
                sectionSelect.disabled = false;
            } catch (error) {
                sectionSelect.replaceChildren(new Option('Unable to load sections', ''));
                setAssignmentMessage('fmAssignSubjectMessage', error.message, true);
            }
        }

        if (assignmentTable && !assignmentTable.dataset.handlersBound) {
            assignmentTable.dataset.handlersBound = 'true';
            assignmentTable.addEventListener('click', event => {
                const viewButton = event.target.closest('[data-fm-view-assignments]');
                const sectionsButton = event.target.closest('[data-fm-assign-sections]');
                const subjectButton = event.target.closest('[data-fm-assign-subject]');
                if (viewButton) openFacultyAssignments(viewButton);
                if (sectionsButton) openAssignSections(sectionsButton);
                if (subjectButton) openAssignSubject(subjectButton);
            });
        }

        document.getElementById('geFacultyAssignmentSearch').addEventListener('input', renderFacultyAssignmentRows);
        document.getElementById('geFacultyDepartmentFilter').addEventListener('change', renderFacultyAssignmentRows);
        document.getElementById('geFacultyLoadFilter').addEventListener('change', renderFacultyAssignmentRows);

        if (sectionsForm && !sectionsForm.dataset.assignmentBound) {
            sectionsForm.dataset.assignmentBound = 'true';
            document.getElementById('fmAssignSectionsSchoolYear').addEventListener('change', loadAssignmentSemesters);
            document.getElementById('fmAssignSectionsSemester').addEventListener('change', loadAssignmentSections);
            sectionsForm.addEventListener('submit', async event => {
                event.preventDefault();
                const sectionIds = Array.from(document.getElementById('fmAssignSectionsList').selectedOptions).map(option => option.value).filter(Boolean);
                const facultyId = document.getElementById('fmAssignSectionsFacultyId').value;
                const submitButton = sectionsForm.querySelector('button[type="submit"]');
                if (!facultyId || !sectionIds.length) {
                    setAssignmentMessage('fmAssignSectionsMessage', 'Select at least one section.', true);
                    return;
                }
                submitButton.disabled = true;
                try {
                    const result = await requestAssignmentData('add_section_faculty.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ faculty_id: facultyId, section_ids: sectionIds })
                    });
                    setAssignmentMessage('fmAssignSectionsMessage', result.message || 'Section assignments saved.');
                    closeAssignmentModal(sectionsModal);
                    await reloadFacultyAssignments();
                } catch (error) {
                    setAssignmentMessage('fmAssignSectionsMessage', error.message, true);
                } finally {
                    submitButton.disabled = false;
                }
            });
        }

        if (subjectForm && !subjectForm.dataset.assignmentBound) {
            subjectForm.dataset.assignmentBound = 'true';
            document.getElementById('fmAssignSubjectSection').addEventListener('change', event => loadSubjectOptions(event.target.value));
            subjectForm.addEventListener('submit', async event => {
                event.preventDefault();
                const facultyId = document.getElementById('fmAssignSubjectFacultyId').value;
                const sectionId = document.getElementById('fmAssignSubjectSection').value;
                const subjectId = document.getElementById('fmAssignSubjectSubject').value;
                const submitButton = subjectForm.querySelector('button[type="submit"]');
                if (!facultyId || !sectionId || !subjectId) {
                    setAssignmentMessage('fmAssignSubjectMessage', 'Select an assigned section and curriculum subject.', true);
                    return;
                }
                submitButton.disabled = true;
                try {
                    const result = await requestAssignmentData('assign_subject_to_faculty.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ faculty_id: facultyId, assignments: [{ section_id: sectionId, subject_id: subjectId }] })
                    });
                    setAssignmentMessage('fmAssignSubjectMessage', result.message || 'Subject assignment saved.');
                    closeAssignmentModal(subjectModal);
                    await reloadFacultyAssignments();
                } catch (error) {
                    setAssignmentMessage('fmAssignSubjectMessage', error.message, true);
                } finally {
                    submitButton.disabled = false;
                }
            });
        }

        [sectionsModal, subjectModal, assignmentsViewModal].forEach(modal => {
            if (!modal || modal.dataset.closeBound) return;
            modal.dataset.closeBound = 'true';
            modal.querySelectorAll('.modal-close').forEach(button => {
                button.addEventListener('click', () => closeAssignmentModal(modal));
            });
            modal.addEventListener('click', event => {
                if (event.target === modal) closeAssignmentModal(modal);
            });
        });

        reloadFacultyAssignments();
    }

    setActiveTab('assignments');
    initGeFacultyAssignments();
    loadData();
}

document.addEventListener('DOMContentLoaded', initGeCurriculumPage);
window.addEventListener('page:loaded', event => {
    if (event.detail?.page === 'general-education-curriculum') initGeCurriculumPage();
});