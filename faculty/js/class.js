let students = [];
let currentPeriod = 'prelim';

const excelBtn = document.getElementById('excelBtn');

loadStudents(ScheduleId, currentPeriod);
loadOverallGrades(ScheduleId);


excelBtn.addEventListener('click', function () {

    downloadExcelTemplate(ScheduleId, currentPeriod);

});


document.getElementById('importExcelBtn')
    .addEventListener('click', async function () {

        const fileInput = document.getElementById('excelFile');

        if (!fileInput.files.length) {

            Swal.fire({
                title: 'No File Selected',
                text: 'Please select an Excel file first.',
                icon: 'warning'
            });

            return;
        }

        const file = fileInput.files[0];

        const confirmation = await Swal.fire({
            title: 'Import Grades?',
            text: `Import ${currentPeriod} grades from this Excel file?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Import',
            cancelButtonText: 'Cancel'
        });

        if (!confirmation.isConfirmed) {
            return;
        }

        const formData = new FormData();

        formData.append('excel', file);
        formData.append('schedule_id', ScheduleId);
        formData.append('grading_period', currentPeriod);

        try {

            Swal.fire({
                title: 'Importing Grades...',
                text: 'Please wait.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const response = await fetch(
                `${BASE_URL}/grades/import-excel`,
                {
                    method: 'POST',
                    body: formData
                }
            );

            const result = await response.json();

            if (!result.success) {

                Swal.fire({
                    title: 'Import Failed',
                    text: result.message,
                    icon: 'error'
                });

                return;
            }

            await Swal.fire({
                title: 'Import Complete',
                text: result.message,
                icon: 'success'
            });

            loadStudents(
                ScheduleId,
                currentPeriod
            );

            fileInput.value = '';

        } catch (error) {

            console.error(error);

            Swal.fire({
                title: 'Something Went Wrong',
                text: 'Unable to import the Excel file.',
                icon: 'error'
            });
        }
    });

document.getElementById('gradePeriod').addEventListener('change', function () {

    currentPeriod = this.value;

    console.log('Current period:', currentPeriod);

    loadStudents(ScheduleId, currentPeriod);

});


async function loadStudents(scheduleId, period) {

    const response = await fetch(
        `${BASE_URL}/${scheduleId}/students?grading_period=${period}`
    );

    const students = await response.json();

    console.log(students);

    renderStudents(students);
}


function renderStudents(students) {

    const tbody = document.getElementById('show_grade_list');

    tbody.innerHTML = students.map(student => {

        const writtenWorks = student.written_works ?? '';
        const performance = student.performance ?? '';
        const exam = student.exam ?? '';

        const periodGrade = calculateGrade(
            writtenWorks,
            performance,
            exam
        );

        return `
            <tr>

                <td class="ps-4">
                    <div class="fw-semibold">
                        ${student.student_number}
                    </div>

                    <small class="text-muted">
                        ${student.first_name}
                        ${student.last_name}
                    </small>
                </td>

                <td>
                    <input
                        type="number"
                        class="form-control grade-input"
                        min="0"
                        max="100"
                        step="0.01"
                        name="grades[${student.enrollment_id}][written_works]"
                        value="${writtenWorks}"
                    >
                </td>

                <td>
                    <input
                        type="number"
                        class="form-control grade-input"
                        min="0"
                        max="100"
                        step="0.01"
                        name="grades[${student.enrollment_id}][performance]"
                        value="${performance}"
                    >
                </td>

                <td>
                    <input
                        type="number"
                        class="form-control grade-input"
                        min="0"
                        max="100"
                        step="0.01"
                        name="grades[${student.enrollment_id}][exam]"
                        value="${exam}"
                    >
                </td>

                <td class="text-center fw-bold period-grade">
                    ${periodGrade ?? '—'}
                </td>

                <td class="text-center">
                    <span class="badge text-bg-secondary">
                        No Grade
                    </span>
                </td>

            </tr>
        `;

    }).join('');

}



function calculateGrade(written, performance, exam) {

    if (
        written === '' ||
        performance === '' ||
        exam === ''
    ) {
        return null;
    }

    const writtenGrade = Number(written);
    const performanceGrade = Number(performance);
    const examGrade = Number(exam);

    const average =
        (writtenGrade + performanceGrade + examGrade) / 3;

    return average.toFixed(2);
}




document.getElementById('show_grade_list')
    .addEventListener('input', function (e) {

       
        if (!e.target.classList.contains('grade-input')) {
            return;
        }

        
        const row = e.target.closest('tr');

        
        const inputs = row.querySelectorAll('.grade-input');

        const written = inputs[0].value;
        const performance = inputs[1].value;
        const exam = inputs[2].value;

        
        const grade = calculateGrade(
            written,
            performance,
            exam
        );

        
        const gradeCell = row.querySelector('.period-grade');

        
        gradeCell.textContent = grade ?? '—';

    });



document.getElementById('gradeForm').addEventListener(
    'submit',
    async function (e) {

        e.preventDefault();

        const form = this;

      
        const result = await Swal.fire({
            title: 'Save Grades?',
            text: `Are you sure you want to save the ${currentPeriod} grades?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Save',
            cancelButtonText: 'Cancel',
            reverseButtons: true
        });

        if (!result.isConfirmed) {
            return;
        }


        const formData = new FormData(form);

        const grades = {};

        formData.forEach((value, key) => {

            const match = key.match(
                /grades\[(\d+)\]\[(written_works|performance|exam)\]/
            );

            if (!match) {
                return;
            }

            const enrollmentId = match[1];
            const field = match[2];

            if (!grades[enrollmentId]) {
                grades[enrollmentId] = {};
            }

            grades[enrollmentId][field] =
                value === ''
                    ? null
                    : Number(value);

        });


        try {

            Swal.fire({
                title: 'Saving Grades...',
                text: 'Please wait.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });


            const response = await fetch(
                BASE_URL + '/grades/save',
                {
                    method: 'POST',

                    headers: {
                        'Content-Type': 'application/json'
                    },

                    body: JSON.stringify({
                        grading_period: currentPeriod,
                        grades: grades
                    })
                }
            );


            const data = await response.json();


            if (!data.success) {

                Swal.fire({
                    title: 'Unable to Save',
                    text: data.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });

                return;
            }

            await Swal.fire({
                title: 'Grades Saved!',
                text: data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            });

            loadStudents(
                ScheduleId,
                currentPeriod
            );

            loadOverallGrades(ScheduleId);

        } catch (error) {

            console.error(error);

            Swal.fire({
                title: 'Something Went Wrong',
                text: 'Unable to save the grades. Please try again.',
                icon: 'error',
                confirmButtonText: 'OK'
            });

        }

    }
);




function downloadExcelTemplate(ScheduleId, currentPeriod) {

    const url =
        `${BASE_URL}/${ScheduleId}/grades/excel?grading_period=${currentPeriod}`;

    window.open(url, '_blank');

}



// overall grades section

async function loadOverallGrades(scheduleId) {

    try {

        const response = await fetch(
            `${BASE_URL}/${scheduleId}/overall-grades`
        );

        if (!response.ok) {
            throw new Error('Failed to load overall grades.');
        }

        const students = await response.json();

        console.log('Overall grades:', students);

        renderOverallGrades(students);

    } catch (error) {

        console.error(error);

        document.getElementById('show_overall_grade_list').innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-danger py-4">
                    Failed to load grades.
                </td>
            </tr>
        `;
    }
}   



function renderOverallGrades(students) {

    const tbody = document.getElementById(
        'show_overall_grade_list'
    );

    if (!students.length) {

        tbody.innerHTML = `
            <tr>
                <td colspan="6"
                    class="text-center text-muted py-4">
                    No students found.
                </td>
            </tr>
        `;

        return;
    }


    tbody.innerHTML = students.map(student => {

        const overall = calculateOverall(
            student.prelim,
            student.midterm,
            student.finals
        );

        const status = getGradeStatus(overall);

        const complete =
            student.prelim !== null &&
            student.prelim !== '' &&
            student.midterm !== null &&
            student.midterm !== '' &&
            student.finals !== null &&
            student.finals !== '';


        return `
            <tr>

                <td class="ps-4">

                    <div class="fw-semibold">
                        ${student.student_number}
                    </div>

                    <small class="text-muted">
                        ${student.first_name}
                        ${student.last_name}
                    </small>

                </td>


                <td class="text-center">
                    ${student.prelim ?? '—'}
                </td>


                <td class="text-center">
                    ${student.midterm ?? '—'}
                </td>


                <td class="text-center">
                    ${student.finals ?? '—'}
                </td>


                <td class="text-center fw-bold">
                    ${overall ?? '—'}
                </td>


               <td class="text-center">
                    <span class="badge ${status.className}">
                        ${status.text}
                    </span>
                </td>

            </tr>
        `;

    }).join('');
}


function getGradeStatus(overall) {

    if (overall === null) {
        return {
            text: 'Incomplete',
            className: 'text-bg-secondary'
        };
    }

    if (Number(overall) >= 75) {
        return {
            text: 'Passed',
            className: 'text-bg-success'
        };
    }

    return {
        text: 'Failed',
        className: 'text-bg-danger'
    };
}


function calculateOverall(prelim, midterm, finals) {

    if (
        prelim === null ||
        prelim === '' ||
        midterm === null ||
        midterm === '' ||
        finals === null ||
        finals === ''
    ) {
        return null;
    }

    return (
        (
            Number(prelim) +
            Number(midterm) +
            Number(finals)
        ) / 3
    ).toFixed(2);
}

// submit grades


document
    .getElementById('submitGradesBtn')
    .addEventListener('click', submitGradesToRegistrar);


async function submitGradesToRegistrar() {

    const result = await Swal.fire({
        title: 'Submit grades?',
        text: 'Once submitted, the registrar will be able to review these grades.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Submit Grades',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) {
        return;
    }


    try {

        Swal.fire({
            title: 'Submitting...',
            text: 'Please wait.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });


        const response = await fetch(
            `${BASE_URL}/${ScheduleId}/grades/submit`,
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            }
        );

        const data = await response.json();


        if (!response.ok || !data.success) {
            throw new Error(
                data.message || 'Failed to submit grades.'
            );
        }


        await Swal.fire({
            icon: 'success',
            title: 'Grades Submitted',
            text: data.message
        }); 


        loadOverallGrades(ScheduleId);


    } catch (error) {

        console.error(error);

        Swal.fire({
            icon: 'error',
            title: 'Submission Failed',
            text: error.message
        });

    }
}