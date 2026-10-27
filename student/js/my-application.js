document.addEventListener('DOMContentLoaded', function () {
    loadMyApplications();
});

function myappStatusClass(status) {
    return 'myapp-status-' + String(status || '').toLowerCase().replace(/\s+/g, '');
}

async function loadMyApplications() {
    const tbody = document.querySelector('#myappTable tbody');

    try {
        const response = await fetch(`${BASE_URL}/my-applications/list`);
        const result = await response.json();

        if (!result || result.success === false || !result.data || result.data.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="myapp-no-data">No Data Found</td></tr>`;
            return;
        }

        tbody.innerHTML = '';
        result.data.forEach(row => {
            tbody.innerHTML += `
                <tr>
                    <td>${row.application_number ?? '-'}</td>
                    <td>${row.scholarship_type ?? '-'}</td>
                    <td>${row.applied_at ?? '-'}</td>
                    <td><span class="myapp-status ${myappStatusClass(row.status)}">${row.status ?? '-'}</span></td>
                    <td>${row.review_notes ?? '-'}</td>
                </tr>
            `;
        });

    } catch (error) {
        console.error('Error loading applications:', error);
        tbody.innerHTML = `<tr><td colspan="5" class="myapp-no-data">Unable to load data.</td></tr>`;
    }
}