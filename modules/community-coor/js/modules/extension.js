// Sample data for programs and proposals
const epmPrograms = [
  {
    id: 1,
    title: 'Community Literacy Program',
    category: 'Literacy',
    status: 'Ongoing',
    beneficiaries: 180,
    period: 'Jan 2026 - Jun 2026',
    actions: ['View', 'Edit', 'Delete']
  },
  {
    id: 2,
    title: 'Barangay Health Awareness Drive',
    category: 'Health',
    status: 'Approved',
    beneficiaries: 120,
    period: 'Feb 2026 - May 2026',
    actions: ['View', 'Edit', 'Delete']
  },
  {
    id: 3,
    title: 'Livelihood Skills Training',
    category: 'Livelihood',
    status: 'Planning',
    beneficiaries: 90,
    period: 'Mar 2026 - Aug 2026',
    actions: ['View', 'Edit', 'Delete']
  },
  {
    id: 4,
    title: 'Coastal Clean-Up Campaign',
    category: 'Environmental',
    status: 'Completed',
    beneficiaries: 150,
    period: 'Nov 2025 - Jan 2026',
    actions: ['View', 'Edit', 'Delete']
  },
  {
    id: 5,
    title: 'Community Development Outreach',
    category: 'Community Development',
    status: 'Proposed',
    beneficiaries: 200,
    period: 'Apr 2026 - Oct 2026',
    actions: ['View', 'Edit', 'Delete']
  }
];

const epmProposals = [
  {
    id: 1,
    title: 'Digital Skills for Youth',
    category: 'Other Outreach',
    status: 'Proposed',
    leadOffice: 'Community Relations Office'
  },
  {
    id: 2,
    title: 'Nutrition and Wellness Campaign',
    category: 'Health',
    status: 'Approved',
    leadOffice: 'Health Services Unit'
  },
  {
    id: 3,
    title: 'Sustainable Farming Support',
    category: 'Livelihood',
    status: 'Planning',
    leadOffice: 'Extension Coordination Desk'
  }
];

// Status badge classes
function getStatusClass(status) {
  const normalized = (status || '').toLowerCase();
  if (normalized === 'ongoing') return 'epm-status epm-status-ongoing';
  if (normalized === 'approved') return 'epm-status epm-status-approved';
  if (normalized === 'completed') return 'epm-status epm-status-completed';
  if (normalized === 'planning' || normalized === 'proposed') return 'epm-status epm-status-planning';
  if (normalized === 'cancelled') return 'epm-status epm-status-cancelled';
  return 'epm-status epm-status-default';
}

// Render program table
function renderProgramTable() {
  const tableBody = document.getElementById('epmProgramTableBody');
  if (!tableBody) return;

  const query = (document.getElementById('epmProgramSearch')?.value || '').trim().toLowerCase();
  const category = document.getElementById('epmCategoryFilter')?.value || 'All Categories';
  const status = document.getElementById('epmStatusFilter')?.value || 'All Status';

  const filtered = epmPrograms.filter(program => {
    const matchesQuery = !query || program.title.toLowerCase().includes(query);
    const matchesCategory = category === 'All Categories' || program.category === category;
    const matchesStatus = status === 'All Status' || program.status === status;
    return matchesQuery && matchesCategory && matchesStatus;
  });

  if (!filtered.length) {
    tableBody.innerHTML = '<tr><td colspan="6" class="epm-empty-row">No matching programs found.</td></tr>';
    return;
  }

  tableBody.innerHTML = filtered.map(program => `
    <tr>
      <td>${program.title}</td>
      <td>${program.category}</td>
      <td><span class="${getStatusClass(program.status)}">${program.status}</span></td>
      <td>${program.beneficiaries}</td>
      <td>${program.period}</td>
      <td>
        <div class="epm-action-group">
          <button type="button" class="epm-btn-link">View</button>
          <button type="button" class="epm-btn-link">Edit</button>
          <button type="button" class="epm-btn-link epm-btn-danger">Delete</button>
        </div>
      </td>
    </tr>
  `).join('');
}

// Render proposal table
function renderProposalTable() {
  const tableBody = document.getElementById('epmProposalTableBody');
  if (!tableBody) return;

  tableBody.innerHTML = epmProposals.map(proposal => `
    <tr>
      <td>${proposal.title}</td>
      <td>${proposal.category}</td>
      <td><span class="${getStatusClass(proposal.status)}">${proposal.status}</span></td>
      <td>${proposal.leadOffice}</td>
      <td>
        <div class="epm-action-group">
          <button type="button" class="epm-btn-link">View</button>
          <button type="button" class="epm-btn-link">Edit</button>
        </div>
      </td>
    </tr>
  `).join('');
}

// Initialize EPM tabs and event listeners
function initEpmTabs() {
  const root = document.getElementById('epmRoot');
  if (!root || root.dataset.initialized === 'true') return;
  root.dataset.initialized = 'true';

  const tabs = root.querySelectorAll('.epm-tab');
  const panels = root.querySelectorAll('.epm-panel');

  tabs.forEach(tab => {
    tab.addEventListener('click', () => {
      const target = tab.dataset.epmTab;
      tabs.forEach(item => item.classList.toggle('active', item === tab));
      panels.forEach(panel => {
        panel.classList.toggle('active', panel.dataset.epmPanel === target);
      });
    });
  });

  const searchInput = document.getElementById('epmProgramSearch');
  const categoryFilter = document.getElementById('epmCategoryFilter');
  const statusFilter = document.getElementById('epmStatusFilter');
  [searchInput, categoryFilter, statusFilter].forEach(element => {
    if (element) {
      element.addEventListener('input', renderProgramTable);
      element.addEventListener('change', renderProgramTable);
    }
  });

  const proposalModal = document.getElementById('epmProposalModal');
  const openProposalButton = root.querySelector('.epm-open-proposal-modal');
  if (openProposalButton && proposalModal) {
    openProposalButton.addEventListener('click', () => {
      proposalModal.classList.add('is-open');
      proposalModal.setAttribute('aria-hidden', 'false');
    });
  }

  root.addEventListener('click', event => {
    const closeTrigger = event.target.closest('[data-epm-close="modal"]');
    if (closeTrigger && proposalModal) {
      proposalModal.classList.remove('is-open');
      proposalModal.setAttribute('aria-hidden', 'true');
    }
  });

  renderProgramTable();
  renderProposalTable();
}

function ensureEpmInitialized() {
  const root = document.getElementById('epmRoot');
  if (!root) return;

  if (root.dataset.initialized === 'true') {
    renderProgramTable();
    renderProposalTable();
    return;
  }

  initEpmTabs();
}

// Page load event
window.addEventListener('page:loaded', function () {
  ensureEpmInitialized();
});

// DOMContentLoaded event for initial tab initialization
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', function () {
    ensureEpmInitialized();
  });
} else {
  ensureEpmInitialized();
}