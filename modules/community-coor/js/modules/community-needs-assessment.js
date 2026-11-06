const cnaCommunityData = [
    { id: 1, community: 'Brgy. San Jose', focusArea: 'Literacy', status: 'Selected', location: 'San Jose, Valencia' },
    { id: 2, community: 'Brgy. Muzon', focusArea: 'Health', status: 'Proposed', location: 'Muzon, San Jose' },
    { id: 3, community: 'Brgy. Poblacion', focusArea: 'Livelihood', status: 'Selected', location: 'Poblacion, Bahay' }
];

const cnaStatValues = {
    totalCommunities: 12,
    totalAssessments: 24,
    identifiedIssues: 18,
    totalBeneficiaries: 350
};

function cnaInitPage() {
    const root = document.getElementById('cnaRoot');
    if (!root || root.dataset.initialized === 'true') return;
    root.dataset.initialized = 'true';

    const tabs = root.querySelectorAll('.cna-tab');
    const panels = root.querySelectorAll('.cna-panel');
    const searchInput = root.querySelector('#cnaCommunitySearch');
    const tableBody = root.querySelector('#cnaCommunityTableBody');
    const summaryText = root.querySelector('#cnaSummaryText');
    const modal = root.querySelector('#cnaModal');
    const modalContent = root.querySelector('#cnaModalContent');

    function renderTable(records) {
        if (!tableBody) return;

        if (!records.length) {
            tableBody.innerHTML = '<tr><td colspan="3" class="cna-empty-row">No records found.</td></tr>';
            if (summaryText) summaryText.textContent = 'Showing 0 sample records · Page 1 of 1';
            return;
        }

        tableBody.innerHTML = records.map(record => {
            const badgeClass = record.status === 'Selected' ? 'cna-status cna-status-selected' : 'cna-status cna-status-proposed';
            return `
                <tr>
                    <td class="cna-community-name">${record.community}</td>
                    <td class="cna-focus-area">${record.focusArea}</td>
                    <td><span class="${badgeClass}">${record.status}</span></td>
                </tr>
            `;
        }).join('');

        if (summaryText) summaryText.textContent = `Showing ${records.length} sample records · Page 1 of 1`;
    }

    function filterCommunityRecords() {
        if (!searchInput) return;
        const keyword = searchInput.value.trim().toLowerCase();
        const filtered = cnaCommunityData.filter(record => {
            const haystack = `${record.community} ${record.focusArea} ${record.location}`.toLowerCase();
            return !keyword || haystack.includes(keyword);
        });

        renderTable(filtered);
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterCommunityRecords);
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.cnaTab;
            tabs.forEach(item => item.classList.toggle('active', item === tab));
            panels.forEach(panel => {
                panel.classList.toggle('active', panel.dataset.cnaPanel === target);
            });
        });
    });

    const viewDetailsBtn = root.querySelector('#cnaViewDetailsBtn');
    if (viewDetailsBtn) {
        viewDetailsBtn.addEventListener('click', () => {
            const sample = cnaCommunityData[0];
            if (modal && modalContent) {
                modalContent.innerHTML = `
                    <div class="cna-modal-item"><strong>Community:</strong> ${sample.community}</div>
                    <div class="cna-modal-item"><strong>Focus Area:</strong> ${sample.focusArea}</div>
                    <div class="cna-modal-item"><strong>Location:</strong> ${sample.location}</div>
                    <div class="cna-modal-item"><strong>Status:</strong> ${sample.status}</div>
                `;
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
            }
        });
    }

    root.addEventListener('click', event => {
        const closeTarget = event.target.closest('[data-cna-close="modal"]');
        if (closeTarget && modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        }

        const rowTarget = event.target.closest('.cna-community-name');
        if (rowTarget && modal && modalContent) {
            const name = rowTarget.textContent.trim();
            const selected = cnaCommunityData.find(item => item.community === name);
            if (!selected) return;

            modalContent.innerHTML = `
                <div class="cna-modal-item"><strong>Community:</strong> ${selected.community}</div>
                <div class="cna-modal-item"><strong>Focus Area:</strong> ${selected.focusArea}</div>
                <div class="cna-modal-item"><strong>Location:</strong> ${selected.location}</div>
                <div class="cna-modal-item"><strong>Status:</strong> ${selected.status}</div>
            `;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
        }
    });

    renderTable(cnaCommunityData);
}

window.addEventListener('page:loaded', function (event) {
    if (event.detail && event.detail.page === 'community-needs-assessment') {
        cnaInitPage();
    }
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('cnaRoot')) {
            cnaInitPage();
        }
    });
} else if (document.getElementById('cnaRoot')) {
    cnaInitPage();
}
