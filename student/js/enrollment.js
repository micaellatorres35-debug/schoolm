document.addEventListener('DOMContentLoaded', function () {
 
    const printBtn = document.getElementById('printBtn');

    printBtn.addEventListener('click',function(){

        window.open(
        `${BASE_URL}/pdf/${Id}/cor`,
        '_blank'
      );

    });

    loadEnrolledId(Id);

});


async function loadEnrolledId(Id) {
    try {
        const response = await fetch(`${BASE_URL}/enrollment/${Id}/subject-enrolled`);

        const data = await response.json();

        const tbody = document.querySelector('#gradesTable tbody');

        tbody.innerHTML = '';

        if (!data || data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-muted">
                        No Data Found.
                    </td>
                </tr>
            `;
            return;
        }

        data.forEach(row => {
            tbody.innerHTML += `
                <tr>
                    <td class="ps-3">${row.subject_code ?? '-'}</td>

                    <td>${row.subject_name ?? '-'}</td>

                    <td>${row.section_code ?? '-'}</td>

                    <td class="text-center">
                        ${row.subject_unit ?? '-'}
                    </td>

                    <td>
                        ${row.adviser_first_name ?? ''} 
                        ${row.adviser_last_name ?? ''}
                    </td>

                    <td class="text-center">
                        ${row.enrollment_status == 'enrolled' ? 'Enrolled' : '-'}
                    </td>
                </tr>
            `;
        });

    } catch (error) {
        console.error('Error loading grades:', error);
    }
}