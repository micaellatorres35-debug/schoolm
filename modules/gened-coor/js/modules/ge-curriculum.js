function initGeCurriculumPage() {
    const root = document.getElementById('geCurriculumModule');
    if (!root || root.dataset.initialized === 'true') return;
    root.dataset.initialized = 'true';

    const state = { subjects: [], curriculums: [], prerequisites: [], mappings: [] };
    const subjectBody = document.querySelector('#geSubjectsTable tbody');
    const prerequisiteBody = document.querySelector('#gePrerequisitesTable tbody');
    const mappingBody = document.querySelector('#geMappingsTable tbody');
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

    function renderSubjects() {
        const term = document.getElementById('geSubjectSearch').value.trim().toLowerCase();
        const subjects = state.subjects.filter(subject => `${subject.code} ${subject.name} ${subject.description || ''}`.toLowerCase().includes(term));
        if (!subjects.length) return emptyRow(subjectBody, 7, 'No GE subjects found.');
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
            row.appendChild(actions);
            addAction(row, 'Edit', 'edit-subject', subject.id, 'btn btn-sm btn-outline-primary');
            addAction(row, 'Delete', 'delete-subject', subject.id, 'btn btn-sm btn-outline-danger');
            subjectBody.appendChild(row);
        });
    }

    function fillSubjectSelect(select, placeholder) {
        select.replaceChildren(new Option(placeholder, ''));
        state.subjects.forEach(subject => select.add(new Option(`${subject.code} - ${subject.name}`, subject.id)));
    }

    function renderPrerequisites() {
        if (!state.prerequisites.length) return emptyRow(prerequisiteBody, 3, 'No prerequisites mapped.');
        prerequisiteBody.replaceChildren();
        state.prerequisites.forEach(prerequisite => {
            const row = document.createElement('tr');
            addCell(row, `${prerequisite.subject_code} - ${prerequisite.subject_name}`);
            addCell(row, `${prerequisite.prerequisite_code} - ${prerequisite.prerequisite_name}`);
            const actions = document.createElement('td');
            actions.className = 'actions';
            row.appendChild(actions);
            addAction(row, 'Remove', 'delete-prerequisite', prerequisite.id, 'btn btn-sm btn-outline-danger');
            prerequisiteBody.appendChild(row);
        });
    }

    function renderMappings() {
        const curriculumId = document.getElementById('geMappingCurriculum').value;
        const mappings = state.mappings.filter(mapping => !curriculumId || String(mapping.curriculum_id) === curriculumId);
        if (!mappings.length) return emptyRow(mappingBody, 6, 'No GE subjects mapped to this curriculum.');
        mappingBody.replaceChildren();
        mappings.forEach(mapping => {
            const row = document.createElement('tr');
            addCell(row, `${mapping.course_code || 'Course'} | ${mapping.curriculum_name} (${mapping.effective_year || 'N/A'})`);
            addCell(row, `${mapping.subject_code} - ${mapping.subject_name}`);
            addCell(row, `${mapping.year_level}${mapping.year_level == 1 ? 'st' : mapping.year_level == 2 ? 'nd' : mapping.year_level == 3 ? 'rd' : 'th'} Year`);
            addCell(row, mapping.semester);
            addCell(row, mapping.units);
            const actions = document.createElement('td');
            actions.className = 'actions';
            row.appendChild(actions);
            addAction(row, 'Remove', 'delete-mapping', mapping.id, 'btn btn-sm btn-outline-danger');
            mappingBody.appendChild(row);
        });
    }

    function populateCurriculums() {
        const select = document.getElementById('geMappingCurriculum');
        select.replaceChildren(new Option('All curricula', ''));
        state.curriculums.forEach(curriculum => {
            const label = `${curriculum.course_code || 'Course'} | ${curriculum.curriculum_name} (${curriculum.effective_year || 'N/A'})`;
            select.add(new Option(label, curriculum.curriculum_id));
        });
        const mappingSelect = document.getElementById('geMappingCurriculum');
        if (mappingSelect.options.length > 1) mappingSelect.selectedIndex = 1;
    }

    async function loadData() {
        try {
            const data = await request();
            Object.assign(state, data);
            renderSubjects();
            fillSubjectSelect(document.getElementById('gePrerequisiteSubject'), 'Select GE subject');
            fillSubjectSelect(document.getElementById('gePrerequisiteRequired'), 'Select prerequisite subject');
            fillSubjectSelect(document.getElementById('geMappingSubject'), 'Select GE subject');
            populateCurriculums();
            renderPrerequisites();
            renderMappings();
        } catch (error) {
            emptyRow(subjectBody, 7, error.message);
            emptyRow(prerequisiteBody, 3, error.message);
            emptyRow(mappingBody, 6, error.message);
        }
    }

    function closeSubjectModal() {
        subjectModal.classList.remove('show');
    }

    function openSubjectModal(subject = null) {
        subjectForm.reset();
        document.getElementById('geSubjectId').value = subject?.id || '';
        document.getElementById('geSubjectCode').value = subject?.code || 'GE';
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
        button.addEventListener('click', () => {
            root.querySelectorAll('[data-ge-tab]').forEach(tab => tab.classList.toggle('active', tab === button));
            root.querySelectorAll('[data-ge-panel]').forEach(panel => panel.classList.toggle('active', panel.dataset.gePanel === button.dataset.geTab));
        });
    });

    document.getElementById('geAddSubjectBtn').addEventListener('click', () => openSubjectModal());
    document.getElementById('geSubjectSearch').addEventListener('input', renderSubjects);
    subjectModal.querySelectorAll('.modal-close').forEach(button => button.addEventListener('click', closeSubjectModal));
    subjectModal.addEventListener('click', event => {
        if (event.target === subjectModal) closeSubjectModal();
    });

    subjectBody.addEventListener('click', async event => {
        const button = event.target.closest('[data-action]');
        if (!button) return;
        const subject = state.subjects.find(item => String(item.id) === button.dataset.id);
        if (button.dataset.action === 'edit-subject') openSubjectModal(subject);
        if (button.dataset.action === 'delete-subject' && window.confirm(`Delete ${subject?.code || 'this subject'}?`)) {
            await runAction('delete_subject', { id: button.dataset.id }, 'geSubjectMessage');
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

    document.getElementById('gePrerequisiteForm').addEventListener('submit', async event => {
        event.preventDefault();
        const payload = {
            subject_id: document.getElementById('gePrerequisiteSubject').value,
            prerequisite_subject_id: document.getElementById('gePrerequisiteRequired').value
        };
        if (await runAction('save_prerequisite', payload, 'gePrerequisiteMessage')) event.target.reset();
    });

    prerequisiteBody.addEventListener('click', async event => {
        const button = event.target.closest('[data-action="delete-prerequisite"]');
        if (button) await runAction('delete_prerequisite', { id: button.dataset.id }, 'gePrerequisiteMessage');
    });

    document.getElementById('geMappingCurriculum').addEventListener('change', renderMappings);
    document.getElementById('geMappingForm').addEventListener('submit', async event => {
        event.preventDefault();
        const payload = {
            curriculum_id: document.getElementById('geMappingCurriculum').value,
            subject_id: document.getElementById('geMappingSubject').value,
            year_level: document.getElementById('geMappingYear').value,
            semester: document.getElementById('geMappingSemester').value
        };
        if (await runAction('save_mapping', payload, 'geMappingMessage')) {
            document.getElementById('geMappingSubject').value = '';
            document.getElementById('geMappingYear').value = '';
            document.getElementById('geMappingSemester').value = '';
        }
    });

    mappingBody.addEventListener('click', async event => {
        const button = event.target.closest('[data-action="delete-mapping"]');
        if (button) await runAction('delete_mapping', { id: button.dataset.id }, 'geMappingMessage');
    });

    loadData();
}

document.addEventListener('DOMContentLoaded', initGeCurriculumPage);
window.addEventListener('page:loaded', event => {
    if (event.detail?.page === 'general-education-curriculum') initGeCurriculumPage();
});