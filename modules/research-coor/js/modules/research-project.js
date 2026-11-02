const RESEARCH_PROJECTS_STORAGE_KEY = "researchCoor_projects";
const RESEARCH_STATUSES = [
  "Draft",
  "Submitted",
  "Under Review",
  "Approved",
  "Ongoing",
  "Completed",
  "Rejected",
];
const MILESTONE_STATUSES = ["Pending", "In Progress", "Completed", "Delayed"];

let researchProjects = [];
let researchStorageReady = true;
let researchProjectHandlersBound = false;
let resettingResearchProjectForm = false;

function researchEscape(value) {
  return String(value ?? "").replace(/[&<>"']/g, (character) => {
    const entities = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return entities[character];
  });
}

function getResearchRoot() {
  return document.querySelector(".research-project-page");
}

function showResearchNotice(message, type = "") {
  const notice = document.getElementById("researchStorageNotice");
  if (!notice) return;
  notice.textContent = message;
  notice.classList.toggle("is-error", type === "error");
  notice.classList.toggle("is-success", type === "success");
  notice.setAttribute("role", type === "error" ? "alert" : "status");
}

function loadResearchProjects() {
  researchStorageReady = true;
  try {
    const storedProjects = window.localStorage.getItem(
      RESEARCH_PROJECTS_STORAGE_KEY,
    );
    if (storedProjects === null) {
      researchProjects = [];
      return true;
    }

    const parsedProjects = JSON.parse(storedProjects);
    if (!Array.isArray(parsedProjects)) {
      throw new Error("Stored research project data must be a list.");
    }

    researchProjects = parsedProjects.map((project) => ({
      ...project,
      abstract: String(project.abstract || ""),
      classification: String(project.classification || "Other"),
      leaderEmail: String(project.leaderEmail || ""),
      leaderContact: String(project.leaderContact || ""),
      leaderDepartment: String(project.leaderDepartment || ""),
      researchers: Array.isArray(project.researchers)
        ? project.researchers
        : String(project.researchers || "")
            .split(/\r?\n/)
            .map((researcher) => researcher.trim())
            .filter(Boolean),
      adviser: String(project.adviser || ""),
      adviserEmail: String(project.adviserEmail || ""),
      archived: project.archived === true,
      milestones: Array.isArray(project.milestones) ? project.milestones : [],
      monitoringLogs: Array.isArray(project.monitoringLogs)
        ? project.monitoringLogs
        : [],
    }));
    return true;
  } catch (error) {
    researchStorageReady = false;
    console.error("Unable to load research projects from browser storage:", error);
    showResearchNotice(
      "Hindi mabasa ang naka-save na project data. Walang binagong records; ayusin muna ang browser storage bago mag-register o mag-monitor.",
      "error",
    );
    return false;
  }
}

function persistResearchProjects(nextProjects) {
  if (!researchStorageReady) {
    showResearchNotice(
      "Hindi nai-save ang pagbabago dahil hindi mabasa ang browser storage.",
      "error",
    );
    return false;
  }

  try {
    window.localStorage.setItem(
      RESEARCH_PROJECTS_STORAGE_KEY,
      JSON.stringify(nextProjects),
    );
    researchProjects = nextProjects;
    showResearchNotice("Project records saved in this browser.", "success");
    window.dispatchEvent(new CustomEvent("research-projects-updated"));
    return true;
  } catch (error) {
    console.error("Unable to save research projects in browser storage:", error);
    showResearchNotice(
      "Hindi nai-save ang project. Suriin ang browser storage at subukang muli.",
      "error",
    );
    return false;
  }
}

function researchStatusClass(status) {
  return `status-${String(status || "Draft")
    .toLowerCase()
    .replace(/\s+/g, "-")}`;
}

function formatResearchDate(dateValue) {
  if (!dateValue) return "Not set";
  const parts = dateValue.split("-");
  if (parts.length !== 3) return dateValue;
  const date = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
  if (Number.isNaN(date.getTime())) return dateValue;
  return date.toLocaleDateString(undefined, {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
}

function localDateString() {
  const now = new Date();
  const month = String(now.getMonth() + 1).padStart(2, "0");
  const day = String(now.getDate()).padStart(2, "0");
  return `${now.getFullYear()}-${month}-${day}`;
}

function findResearchProject(projectId) {
  return researchProjects.find((project) => project.id === projectId);
}

function renderResearchStats() {
  const activeProjects = researchProjects.filter((project) => !project.archived);
  document.getElementById("researchTotalCount").textContent =
    activeProjects.length;
  document.getElementById("researchReviewCount").textContent =
    activeProjects.filter((project) => project.status === "Under Review")
      .length;
  document.getElementById("researchOngoingCount").textContent =
    activeProjects.filter((project) => project.status === "Ongoing").length;
  document.getElementById("researchCompletedCount").textContent =
    activeProjects.filter((project) => project.status === "Completed").length;
}

function renderResearchTable() {
  const tbody = document.getElementById("researchProjectsBody");
  const query = document
    .getElementById("researchProjectSearch")
    .value.trim()
    .toLowerCase();
  const category = document.getElementById("researchCategoryFilter").value;
  const classification = document.getElementById(
    "researchClassificationFilter",
  ).value;
  const status = document.getElementById("researchStatusFilter").value;
  const filteredProjects = researchProjects.filter((project) => {
    const searchable = [
      project.registrationNo,
      project.title,
      project.investigator,
      project.classification,
      project.adviser,
      ...(project.researchers || []),
    ]
      .join(" ")
      .toLowerCase();
    return (
      !project.archived &&
      searchable.includes(query) &&
      (!category || project.category === category) &&
      (!classification || project.classification === classification) &&
      (!status || project.status === status)
    );
  });

  if (!filteredProjects.length) {
    const hasActiveProjects = researchProjects.some((project) => !project.archived);
    const message = hasActiveProjects
      ? "No projects match the selected filters."
      : researchProjects.length
        ? "No active projects. View Project Archive to restore an archived project."
        : "No research projects registered yet.";
    tbody.innerHTML = `<tr><td colspan="8" class="empty-state">${message}</td></tr>`;
    return;
  }

  tbody.innerHTML = filteredProjects
    .map(
      (project) => `
        <tr>
          <td data-label="Registration No.">${researchEscape(project.registrationNo)}</td>
          <td class="research-title-cell" data-label="Project Title">${researchEscape(project.title)}<small>${researchEscape(project.description.slice(0, 100))}${project.description.length > 100 ? "…" : ""}</small></td>
          <td data-label="Category / Classification">${researchEscape(project.category)}<small>${researchEscape(project.classification || "Other")}</small></td>
          <td data-label="Project Leader">${researchEscape(project.investigator)}</td>
          <td data-label="Timeline">${researchEscape(formatResearchDate(project.startDate))}<small>to ${researchEscape(formatResearchDate(project.targetDate))}</small></td>
          <td data-label="Status"><span class="research-status ${researchStatusClass(project.status)}">${researchEscape(project.status)}</span></td>
          <td data-label="Progress">
            <div class="research-progress">
              <div class="research-progress-track" aria-label="${Number(project.progress) || 0}% complete"><span style="width:${Math.min(100, Math.max(0, Number(project.progress) || 0))}%"></span></div>
              <small>${Math.min(100, Math.max(0, Number(project.progress) || 0))}% complete</small>
            </div>
          </td>
          <td class="research-project-actions-cell" data-label="Actions">
            <div class="research-actions">
              <button type="button" data-project-action="details" data-project-id="${researchEscape(project.id)}">Details</button>
              <button type="button" data-project-action="edit" data-project-id="${researchEscape(project.id)}">Edit</button>
              <button type="button" data-project-action="monitor" data-project-id="${researchEscape(project.id)}">Monitor</button>
              <button type="button" data-project-action="archive" data-project-id="${researchEscape(project.id)}">Archive</button>
            </div>
          </td>
        </tr>`,
    )
    .join("");
}

function renderArchivedProjects() {
  const tbody = document.getElementById("researchArchivedProjectsBody");
  const archivedProjects = researchProjects.filter((project) => project.archived);
  if (!archivedProjects.length) {
    tbody.innerHTML =
      '<tr><td colspan="5" class="empty-state">No projects in the archive.</td></tr>';
    return;
  }
  tbody.innerHTML = archivedProjects
    .map(
      (project) => `
        <tr>
          <td data-label="Registration No.">${researchEscape(project.registrationNo)}</td>
          <td class="research-title-cell" data-label="Project Title">${researchEscape(project.title)}</td>
          <td data-label="Project Leader">${researchEscape(project.investigator)}</td>
          <td data-label="Archived On">${researchEscape(formatResearchDate(project.archivedAt))}</td>
          <td class="research-project-actions-cell" data-label="Actions"><div class="research-actions">
            <button type="button" data-project-action="details" data-project-id="${researchEscape(project.id)}">Details</button>
            <button type="button" data-project-action="restore" data-project-id="${researchEscape(project.id)}">Restore</button>
          </div></td>
        </tr>`,
    )
    .join("");
}

function renderMonitoringProjectOptions(selectedId = "") {
  const select = document.getElementById("monitoringProjectSelect");
  select.innerHTML = '<option value="">Select a registered project</option>';
  researchProjects.filter((project) => !project.archived).forEach((project) => {
    const option = document.createElement("option");
    option.value = project.id;
    option.textContent = `${project.registrationNo} — ${project.title}`;
    select.appendChild(option);
  });
  if (selectedId && findResearchProject(selectedId) && !findResearchProject(selectedId).archived) {
    select.value = selectedId;
  }
  renderMonitoringDetails(select.value);
}

function renderMonitoringDetails(projectId) {
  const summary = document.getElementById("monitoringProjectSummary");
  const detail = document.getElementById("researchMonitoringDetail");
  const project = findResearchProject(projectId);

  if (!project) {
    summary.innerHTML =
      '<div class="empty-state">Register or select a project to view its timeline and monitoring details.</div>';
    detail.innerHTML =
      '<div class="empty-state">Project milestone and monitoring details will appear here.</div>';
    return;
  }

  const milestones = Array.isArray(project.milestones) ? project.milestones : [];
  summary.innerHTML = `
    <h4>${researchEscape(project.title)}</h4>
    <p class="summary-registration">${researchEscape(project.registrationNo)}</p>
    <dl>
      <div><dt>Category</dt><dd>${researchEscape(project.category)}</dd></div>
      <div><dt>Classification</dt><dd>${researchEscape(project.classification || "Other")}</dd></div>
      <div><dt>Status</dt><dd><span class="research-status ${researchStatusClass(project.status)}">${researchEscape(project.status)}</span></dd></div>
      <div><dt>Project Leader</dt><dd>${researchEscape(project.investigator)}</dd></div>
      <div><dt>Research Adviser</dt><dd>${researchEscape(project.adviser || "Not assigned")}</dd></div>
      <div><dt>Timeline</dt><dd>${researchEscape(formatResearchDate(project.startDate))} – ${researchEscape(formatResearchDate(project.targetDate))}</dd></div>
      <div><dt>Progress</dt><dd>${Math.min(100, Math.max(0, Number(project.progress) || 0))}%</dd></div>
      <div><dt>Completion Date</dt><dd>${researchEscape(formatResearchDate(project.completionDate))}</dd></div>
    </dl>`;

  const timelineItems = milestones.length
    ? milestones
        .map(
          (milestone) => `
            <li class="timeline-item ${milestone.status === "Completed" ? "is-complete" : ""}">
              <strong>${researchEscape(milestone.title)}</strong>
              <small>${researchEscape(formatResearchDate(milestone.date))} · ${researchEscape(milestone.status || "Pending")}</small>
            </li>`,
        )
        .join("")
    : '<li class="empty-state">No milestones recorded.</li>';
  const milestoneStatusFields = milestones
    .map(
      (milestone, index) => `
        <label class="milestone-status-row">
          <span>${researchEscape(milestone.title)} · ${researchEscape(formatResearchDate(milestone.date))}</span>
          <select class="form-select" data-milestone-index="${index}" aria-label="Status for ${researchEscape(milestone.title)}">
            ${MILESTONE_STATUSES.map(
              (milestoneStatus) =>
                `<option value="${milestoneStatus}" ${milestone.status === milestoneStatus ? "selected" : ""}>${milestoneStatus}</option>`,
            ).join("")}
          </select>
        </label>`,
    )
    .join("");
  const logs = Array.isArray(project.monitoringLogs) ? project.monitoringLogs : [];
  const logsMarkup = logs.length
    ? `<div class="research-log-list"><h4>Monitoring History</h4>${[...logs]
        .reverse()
        .slice(0, 5)
        .map(
          (log) =>
            `<article class="research-log-item"><time>${researchEscape(log.date)}</time><p>${researchEscape(log.note)}</p></article>`,
        )
        .join("")}</div>`
    : "";

  detail.innerHTML = `
    <h4>Project Timeline</h4>
    <ol class="timeline-list">${timelineItems}</ol>
    <form class="research-monitoring-form" id="researchMonitoringForm" data-project-id="${researchEscape(project.id)}">
      <div class="research-monitoring-fields">
        <div class="form-field">
          <label for="monitoringStatus">Project Status</label>
          <select id="monitoringStatus" class="form-select" name="status">
            ${RESEARCH_STATUSES.map(
              (projectStatus) =>
                `<option value="${projectStatus}" ${project.status === projectStatus ? "selected" : ""}>${projectStatus}</option>`,
            ).join("")}
          </select>
        </div>
        <div class="form-field">
          <label for="monitoringProgress">Overall Progress</label>
          <div class="progress-input-row">
            <input id="monitoringProgress" type="range" min="0" max="100" step="5" value="${Math.min(100, Math.max(0, Number(project.progress) || 0))}">
            <span class="progress-value" id="monitoringProgressValue">${Math.min(100, Math.max(0, Number(project.progress) || 0))}%</span>
          </div>
        </div>
      </div>
      <div class="form-field">
        <label>Milestone Status</label>
        <div class="milestone-status-list">${milestoneStatusFields || '<p class="text-muted">No milestones to update.</p>'}</div>
      </div>
      <div class="form-field">
        <label for="monitoringNote">Monitoring Note</label>
        <textarea id="monitoringNote" class="form-control" rows="3" maxlength="1500" placeholder="Record a progress update, concern, or next action."></textarea>
      </div>
      <div class="form-field">
        <label for="monitoringCompletionDate">Completion Date</label>
        <input id="monitoringCompletionDate" class="form-control" type="date" value="${researchEscape(project.completionDate || "")}">
      </div>
      <div class="form-field">
        <label for="monitoringCompletionNotes">Completion Monitoring / Outcome</label>
        <textarea id="monitoringCompletionNotes" class="form-control" rows="3" maxlength="2000" placeholder="Record the final outputs, completion notes, or remaining follow-up.">${researchEscape(project.completionNotes || "")}</textarea>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Monitoring Update</button>
    </form>
    ${logsMarkup}`;
}

function renderResearchPage(selectedProjectId = "") {
  renderResearchStats();
  renderResearchTable();
  renderArchivedProjects();
  renderMonitoringProjectOptions(
    selectedProjectId || document.getElementById("monitoringProjectSelect").value,
  );
}

function switchResearchTab(tabName) {
  document.querySelectorAll(".research-project-page .research-tab").forEach((tab) => {
    const isActive = tab.dataset.projectTab === tabName;
    tab.classList.toggle("active", isActive);
    tab.setAttribute("aria-selected", String(isActive));
  });
  document.querySelectorAll(".research-project-page .research-panel").forEach((panel) => {
    const isActive = panel.id === `research-panel-${tabName}`;
    panel.classList.toggle("active", isActive);
    panel.hidden = !isActive;
  });
}

function openProjectDetails(projectId) {
  const project = findResearchProject(projectId);
  if (!project) return;
  const milestoneMarkup = (project.milestones || [])
    .map(
      (milestone) =>
        `<li>${researchEscape(milestone.title)} — ${researchEscape(formatResearchDate(milestone.date))} (${researchEscape(milestone.status || "Pending")})</li>`,
    )
    .join("");
  const modal = document.getElementById("researchProjectDetailsModal");
  document.getElementById("researchProjectDetailsBody").innerHTML = `
    <dl class="details-grid">
      <div><dt>Registration Number</dt><dd>${researchEscape(project.registrationNo)}</dd></div>
      <div><dt>Status</dt><dd><span class="research-status ${researchStatusClass(project.status)}">${researchEscape(project.status)}</span></dd></div>
      <div class="detail-full"><dt>Research Title</dt><dd>${researchEscape(project.title)}</dd></div>
      <div class="detail-full"><dt>Description</dt><dd>${researchEscape(project.description)}</dd></div>
      <div class="detail-full"><dt>Abstract</dt><dd>${researchEscape(project.abstract || "No abstract recorded.")}</dd></div>
      <div><dt>Research Category</dt><dd>${researchEscape(project.category)}</dd></div>
      <div><dt>Classification</dt><dd>${researchEscape(project.classification || "Other")}</dd></div>
      <div><dt>Project Leader</dt><dd>${researchEscape(project.investigator)}</dd></div>
      <div><dt>Leader Email</dt><dd>${researchEscape(project.leaderEmail || "Not set")}</dd></div>
      <div><dt>Contact Number</dt><dd>${researchEscape(project.leaderContact || "Not set")}</dd></div>
      <div><dt>Department / Unit</dt><dd>${researchEscape(project.leaderDepartment || "Not set")}</dd></div>
      <div><dt>Research Adviser</dt><dd>${researchEscape(project.adviser || "Not assigned")}</dd></div>
      <div><dt>Adviser Email</dt><dd>${researchEscape(project.adviserEmail || "Not set")}</dd></div>
      <div><dt>Registered</dt><dd>${researchEscape(formatResearchDate(project.registeredAt))}</dd></div>
      <div><dt>Timeline</dt><dd>${researchEscape(formatResearchDate(project.startDate))} – ${researchEscape(formatResearchDate(project.targetDate))}</dd></div>
      <div><dt>Progress</dt><dd>${Math.min(100, Math.max(0, Number(project.progress) || 0))}%</dd></div>
      <div><dt>Completion Date</dt><dd>${researchEscape(formatResearchDate(project.completionDate))}</dd></div>
      <div class="detail-full"><dt>Assigned Researchers</dt><dd>${project.researchers?.length ? `<ul>${project.researchers.map((researcher) => `<li>${researchEscape(researcher)}</li>`).join("")}</ul>` : "No additional researchers assigned."}</dd></div>
      <div class="detail-full"><dt>Completion Notes</dt><dd>${researchEscape(project.completionNotes || "No completion notes recorded.")}</dd></div>
      <div class="detail-full"><dt>Milestones</dt><dd>${milestoneMarkup ? `<ul>${milestoneMarkup}</ul>` : "No milestones recorded."}</dd></div>
    </dl>`;
  modal.classList.add("show");
  modal.setAttribute("aria-hidden", "false");
}

function nextResearchRegistrationNumber() {
  const year = new Date().getFullYear();
  const prefix = `RSP-${year}-`;
  const numbers = researchProjects
    .filter((project) => String(project.registrationNo).startsWith(prefix))
    .map((project) => Number(String(project.registrationNo).slice(prefix.length)))
    .filter(Number.isFinite);
  const nextNumber = Math.max(0, ...numbers) + 1;
  return `${prefix}${String(nextNumber).padStart(4, "0")}`;
}

function addResearchMilestoneRow() {
  const container = document.getElementById("researchMilestones");
  const row = document.createElement("div");
  row.className = "research-milestone-row";
  row.innerHTML = `
    <label class="sr-only">Milestone title</label>
    <input type="text" class="form-control milestone-title" maxlength="160" placeholder="e.g. Data collection" required>
    <label class="sr-only">Milestone target date</label>
    <input type="date" class="form-control milestone-date" aria-label="Milestone target date" required>
    <button type="button" class="milestone-remove" aria-label="Remove milestone" title="Remove milestone">&times;</button>`;
  container.appendChild(row);
  row.querySelector(".milestone-title").focus();
}

function resetResearchRegistrationForm() {
  const form = document.getElementById("researchProjectForm");
  if (!form) return;
  resettingResearchProjectForm = true;
  form.reset();
  resettingResearchProjectForm = false;
  document.getElementById("researchProjectId").value = "";
  document.getElementById("researchRegistrationHeading").textContent =
    "Research Project Registration";
  document.getElementById("saveResearchProjectButton").innerHTML =
    '<i class="fas fa-save"></i> Register Project';
  document.querySelector("[data-project-cancel-edit]").hidden = true;
  document.getElementById("researchMilestones").innerHTML = `
    <div class="research-milestone-row">
      <label class="sr-only">Milestone title</label>
      <input type="text" class="form-control milestone-title" maxlength="160" placeholder="e.g. Literature review" required>
      <label class="sr-only">Milestone target date</label>
      <input type="date" class="form-control milestone-date" aria-label="Milestone target date" required>
      <button type="button" class="milestone-remove" aria-label="Remove milestone" title="Remove milestone">&times;</button>
    </div>`;
  document.getElementById("researchStartDate").min = localDateString();
  document.getElementById("researchTargetDate").min = localDateString();
}

function editResearchProject(projectId) {
  const project = findResearchProject(projectId);
  if (!project || project.archived) return;
  const form = document.getElementById("researchProjectForm");
  const formValues = {
    projectId: project.id,
    title: project.title,
    description: project.description,
    abstract: project.abstract || "",
    investigator: project.investigator,
    leaderEmail: project.leaderEmail || "",
    leaderContact: project.leaderContact || "",
    leaderDepartment: project.leaderDepartment || "",
    researchers: (project.researchers || []).join("\n"),
    adviser: project.adviser || "",
    adviserEmail: project.adviserEmail || "",
    category: project.category,
    classification: project.classification || "Other",
    status: project.status,
    startDate: project.startDate,
    targetDate: project.targetDate,
  };
  Object.entries(formValues).forEach(([name, value]) => {
    const field = form.elements.namedItem(name);
    if (field) field.value = value;
  });
  document.getElementById("researchMilestones").innerHTML = project.milestones
    .map(
      (milestone) => `
        <div class="research-milestone-row">
          <label class="sr-only">Milestone title</label>
          <input type="text" class="form-control milestone-title" maxlength="160" placeholder="e.g. Data collection" value="${researchEscape(milestone.title)}" required>
          <label class="sr-only">Milestone target date</label>
          <input type="date" class="form-control milestone-date" aria-label="Milestone target date" value="${researchEscape(milestone.date)}" required>
          <button type="button" class="milestone-remove" aria-label="Remove milestone" title="Remove milestone">&times;</button>
        </div>`,
    )
    .join("");
  document.getElementById("researchRegistrationHeading").textContent =
    "Edit Research Project";
  document.getElementById("saveResearchProjectButton").innerHTML =
    '<i class="fas fa-save"></i> Save Changes';
  document.querySelector("[data-project-cancel-edit]").hidden = false;
  switchResearchTab("registration");
  document.getElementById("researchTitle").focus();
}

function registerResearchProject(form) {
  if (!researchStorageReady) {
    showResearchNotice(
      "Hindi maaaring mag-register habang hindi mabasa ang browser storage.",
      "error",
    );
    return;
  }

  const formData = new FormData(form);
  const projectId = String(formData.get("projectId") || "");
  const existingProject = projectId ? findResearchProject(projectId) : null;
  const startDate = String(formData.get("startDate") || "");
  const targetDate = String(formData.get("targetDate") || "");
  const milestoneRows = [...document.querySelectorAll("#researchMilestones .research-milestone-row")];
  const milestones = milestoneRows.map((row) => {
    const title = row.querySelector(".milestone-title").value.trim();
    const date = row.querySelector(".milestone-date").value;
    const priorMilestone = existingProject?.milestones.find(
      (milestone) =>
        milestone.title.toLowerCase() === title.toLowerCase() &&
        milestone.date === date,
    );
    return { title, date, status: priorMilestone?.status || "Pending" };
  });
  const invalidMilestone = milestones.find(
    (milestone) =>
      !milestone.title ||
      !milestone.date ||
      milestone.date < startDate ||
      milestone.date > targetDate,
  );

  if (targetDate < startDate) {
    document.getElementById("researchTargetDate").setCustomValidity(
      "Target completion must be on or after the planned start date.",
    );
    document.getElementById("researchTargetDate").reportValidity();
    return;
  }
  document.getElementById("researchTargetDate").setCustomValidity("");

  if (!milestones.length || invalidMilestone) {
    showResearchNotice(
      "Magdagdag ng kahit isang milestone at tiyaking pasok ang bawat target date sa project timeline.",
      "error",
    );
    const invalidRow = milestoneRows.find((row) => {
      const date = row.querySelector(".milestone-date").value;
      return (
        !row.querySelector(".milestone-title").value.trim() ||
        !date ||
        date < startDate ||
        date > targetDate
      );
    });
    if (invalidRow) {
      const dateInput = invalidRow.querySelector(".milestone-date");
      dateInput.setCustomValidity(
        "Milestone date must be within the project timeline.",
      );
      dateInput.reportValidity();
      dateInput.addEventListener(
        "input",
        () => dateInput.setCustomValidity(""),
        { once: true },
      );
    }
    return;
  }

  const status = String(formData.get("status") || "Submitted");
  const project = {
    ...(existingProject || {}),
    id: existingProject?.id || `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    registrationNo: existingProject?.registrationNo || nextResearchRegistrationNumber(),
    title: String(formData.get("title") || "").trim(),
    description: String(formData.get("description") || "").trim(),
    abstract: String(formData.get("abstract") || "").trim(),
    investigator: String(formData.get("investigator") || "").trim(),
    leaderEmail: String(formData.get("leaderEmail") || "").trim(),
    leaderContact: String(formData.get("leaderContact") || "").trim(),
    leaderDepartment: String(formData.get("leaderDepartment") || "").trim(),
    researchers: String(formData.get("researchers") || "")
      .split(/\r?\n/)
      .map((researcher) => researcher.trim())
      .filter(Boolean),
    adviser: String(formData.get("adviser") || "").trim(),
    adviserEmail: String(formData.get("adviserEmail") || "").trim(),
    category: String(formData.get("category") || ""),
    classification: String(formData.get("classification") || "Other"),
    status,
    registeredAt: existingProject?.registeredAt || localDateString(),
    startDate,
    targetDate,
    progress:
      status === "Completed"
        ? 100
        : existingProject?.progress || 0,
    completionDate:
      status === "Completed"
        ? existingProject?.completionDate || localDateString()
        : existingProject?.completionDate || "",
    milestones,
    monitoringLogs: existingProject?.monitoringLogs || [],
  };
  const nextProjects = existingProject
    ? researchProjects.map((storedProject) =>
        storedProject.id === existingProject.id ? project : storedProject,
      )
    : [project, ...researchProjects];
  if (!persistResearchProjects(nextProjects)) return;

  renderResearchPage(project.id);
  resetResearchRegistrationForm();
  switchResearchTab("overview");
}

function saveMonitoringUpdate(form) {
  if (!researchStorageReady) {
    showResearchNotice(
      "Hindi maaaring mag-save ng monitoring update habang hindi mabasa ang browser storage.",
      "error",
    );
    return;
  }
  const projectId = form.dataset.projectId;
  const existingProject = findResearchProject(projectId);
  if (!existingProject) {
    showResearchNotice("Hindi na makita ang napiling research project.", "error");
    return;
  }

  const status = document.getElementById("monitoringStatus").value;
  const progress = status === "Completed"
    ? 100
    : Number(document.getElementById("monitoringProgress").value);
  const note = document.getElementById("monitoringNote").value.trim();
  const completionNotes = document
    .getElementById("monitoringCompletionNotes")
    .value.trim();
  const completionDate = document.getElementById("monitoringCompletionDate").value;
  const updatedProject = {
    ...existingProject,
    status,
    progress,
    completionDate:
      status === "Completed"
        ? completionDate || existingProject.completionDate || localDateString()
        : existingProject.completionDate || "",
    completionNotes,
    milestones: existingProject.milestones.map((milestone, index) => ({
      ...milestone,
      status:
        form.querySelector(`[data-milestone-index="${index}"]`)?.value ||
        milestone.status ||
        "Pending",
    })),
    monitoringLogs: note
      ? [
          ...(existingProject.monitoringLogs || []),
          { date: new Date().toLocaleString(), note },
        ]
      : existingProject.monitoringLogs || [],
  };
  const nextProjects = researchProjects.map((project) =>
    project.id === projectId ? updatedProject : project,
  );
  if (persistResearchProjects(nextProjects)) renderResearchPage(projectId);
}

function updateProjectArchive(projectId, shouldArchive) {
  const project = findResearchProject(projectId);
  if (!project || project.archived === shouldArchive) return;
  const confirmation = shouldArchive
    ? `Archive "${project.title}"? You can restore it later.`
    : `Restore "${project.title}" to active projects?`;
  if (!window.confirm(confirmation)) return;
  const updatedProject = shouldArchive
    ? { ...project, archived: true, archivedAt: localDateString() }
    : { ...project, archived: false, archivedAt: "" };
  const nextProjects = researchProjects.map((storedProject) =>
    storedProject.id === projectId ? updatedProject : storedProject,
  );
  if (persistResearchProjects(nextProjects)) {
    renderResearchPage();
    switchResearchTab(shouldArchive ? "overview" : "archive");
    showResearchNotice(
      shouldArchive
        ? "Project archived. It is available in Project Archive."
        : "Project restored to the active project list.",
      "success",
    );
  }
}

function closeProjectDetails() {
  const modal = document.getElementById("researchProjectDetailsModal");
  modal.classList.remove("show");
  modal.setAttribute("aria-hidden", "true");
}

function initializeResearchProjectPage() {
  const root = getResearchRoot();
  if (!root || root.dataset.initialized === "true") return;
  root.dataset.initialized = "true";
  loadResearchProjects();
  document.getElementById("researchStartDate").min = localDateString();
  document.getElementById("researchTargetDate").min = localDateString();
  renderResearchPage();

  if (researchProjectHandlersBound) return;
  researchProjectHandlersBound = true;

  document.addEventListener("click", (event) => {
    const activeRoot = getResearchRoot();
    if (!activeRoot || !(event.target instanceof Element)) return;
    const tab = event.target.closest("[data-project-tab]");
    if (tab && activeRoot.contains(tab)) {
      switchResearchTab(tab.dataset.projectTab);
      if (tab.dataset.projectTab === "registration") {
        document.getElementById("researchTitle").focus();
      }
      return;
    }

    const openRegistration = event.target.closest("[data-project-open-registration]");
    if (openRegistration && activeRoot.contains(openRegistration)) {
      resetResearchRegistrationForm();
      switchResearchTab("registration");
      document.getElementById("researchTitle").focus();
      return;
    }

    const action = event.target.closest("[data-project-action]");
    if (action && activeRoot.contains(action)) {
      const { projectId, projectAction } = action.dataset;
      if (projectAction === "details") {
        openProjectDetails(projectId);
      } else if (projectAction === "monitor") {
        switchResearchTab("monitoring");
        renderMonitoringProjectOptions(projectId);
      } else if (projectAction === "edit") {
        editResearchProject(projectId);
      } else if (projectAction === "archive") {
        updateProjectArchive(projectId, true);
      } else if (projectAction === "restore") {
        updateProjectArchive(projectId, false);
      }
      return;
    }

    if (event.target.closest("[data-close-project-details]") &&
        activeRoot.contains(event.target.closest("[data-close-project-details]"))) {
      closeProjectDetails();
      return;
    }

    if (event.target.classList.contains("research-modal") &&
        event.target.id === "researchProjectDetailsModal") {
      closeProjectDetails();
      return;
    }

    if (event.target.closest("#addResearchMilestone") &&
        activeRoot.contains(event.target.closest("#addResearchMilestone"))) {
      addResearchMilestoneRow();
      return;
    }

    if (event.target.closest("[data-project-cancel-edit]") &&
        activeRoot.contains(event.target.closest("[data-project-cancel-edit]"))) {
      resetResearchRegistrationForm();
      return;
    }

    const removeMilestone = event.target.closest(".milestone-remove");
    if (removeMilestone && activeRoot.contains(removeMilestone)) {
      const rows = document.querySelectorAll(
        "#researchMilestones .research-milestone-row",
      );
      if (rows.length === 1) {
        showResearchNotice("Hindi maaaring alisin ang huling milestone.", "error");
        return;
      }
      removeMilestone.closest(".research-milestone-row").remove();
    }
  });

  document.addEventListener("input", (event) => {
    const activeRoot = getResearchRoot();
    if (!activeRoot || !(event.target instanceof Element)) return;
    if (
      event.target.id === "researchProjectSearch" ||
      event.target.id === "researchCategoryFilter" ||
      event.target.id === "researchClassificationFilter" ||
      event.target.id === "researchStatusFilter"
    ) {
      renderResearchTable();
    }
    if (event.target.id === "monitoringProgress" &&
        activeRoot.contains(event.target)) {
      document.getElementById("monitoringProgressValue").textContent =
        `${event.target.value}%`;
    }
  });

  document.addEventListener("change", (event) => {
    const activeRoot = getResearchRoot();
    if (!activeRoot || !(event.target instanceof Element)) return;
    if (
      event.target.id === "researchCategoryFilter" ||
      event.target.id === "researchClassificationFilter" ||
      event.target.id === "researchStatusFilter"
    ) {
      renderResearchTable();
    }
    if (event.target.id === "monitoringProjectSelect") {
      renderMonitoringDetails(event.target.value);
    }
  });

  document.addEventListener("submit", (event) => {
    const activeRoot = getResearchRoot();
    if (!activeRoot || !(event.target instanceof HTMLFormElement) ||
        !activeRoot.contains(event.target)) return;
    if (event.target.id === "researchProjectForm") {
      event.preventDefault();
      registerResearchProject(event.target);
    }
    if (event.target.id === "researchMonitoringForm") {
      event.preventDefault();
      saveMonitoringUpdate(event.target);
    }
  });

  document.addEventListener("reset", (event) => {
    if (!(event.target instanceof HTMLFormElement) ||
        event.target.id !== "researchProjectForm" ||
        resettingResearchProjectForm) return;
    window.setTimeout(resetResearchRegistrationForm, 0);
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && getResearchRoot()) closeProjectDetails();
  });

  window.addEventListener("storage", (event) => {
    if (event.key !== RESEARCH_PROJECTS_STORAGE_KEY || !getResearchRoot()) return;
    if (loadResearchProjects()) renderResearchPage();
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initializeResearchProjectPage);
} else {
  initializeResearchProjectPage();
}
window.addEventListener("page:loaded", initializeResearchProjectPage);
