import {
  fetchResearchProjectData,
  saveResearchProject,
} from "./research-project-data.js";

let researchProjects = [];
let researchStudents = [];
let researchOptions = { categories: [], classifications: [], statuses: [] };
let researchDataReady = false;
let researchHandlersBound = false;

function researchEscape(value) {
  return String(value ?? "").replace(/[&<>"']/g, (character) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#039;",
  })[character]);
}

function showResearchNotice(message, type = "") {
  const notice = document.getElementById("researchStorageNotice");
  if (!notice) return;
  notice.textContent = message;
  notice.classList.toggle("is-error", type === "error");
  notice.classList.toggle("is-success", type === "success");
  notice.setAttribute("role", type === "error" ? "alert" : "status");
}

function optionLabel(value) {
  return ({
    "Apllied research": "Applied Research",
    "Intitutional research": "Institutional Research",
  })[value] || value.replace(/\b[a-z]/g, (character) => character.toUpperCase());
}

function fillSelect(select, values, placeholder, selectedValue = "") {
  select.innerHTML = `<option value="">${researchEscape(placeholder)}</option>` +
    values.map((value) =>
      `<option value="${researchEscape(value)}">${researchEscape(optionLabel(value))}</option>`,
    ).join("");
  if (values.includes(selectedValue)) select.value = selectedValue;
}

function renderResearchOptions() {
  const form = document.getElementById("researchProjectForm");
  const current = Object.fromEntries(
    ["category", "classification", "status"].map((name) => [
      name,
      form.elements.namedItem(name).value,
    ]),
  );
  fillSelect(document.getElementById("researchCategory"), researchOptions.categories, "Select category", current.category);
  fillSelect(document.getElementById("researchClassification"), researchOptions.classifications, "Select classification", current.classification);
  fillSelect(document.getElementById("researchStatus"), researchOptions.statuses, "Select status", current.status || "Submitted");
  fillSelect(document.getElementById("researchCategoryFilter"), researchOptions.categories, "All Categories", document.getElementById("researchCategoryFilter").value);
  fillSelect(document.getElementById("researchClassificationFilter"), researchOptions.classifications, "All Classifications", document.getElementById("researchClassificationFilter").value);
  fillSelect(document.getElementById("researchStatusFilter"), researchOptions.statuses, "All Statuses", document.getElementById("researchStatusFilter").value);

  const studentSelect = document.getElementById("researchStudentId");
  const selectedStudentId = studentSelect.value;
  studentSelect.innerHTML = `<option value="">${researchStudents.length ? "Select a student" : "No student records found"}</option>` +
    researchStudents.map((student) =>
      `<option value="${researchEscape(student.id)}">${researchEscape(student.name || `Student #${student.id}`)} · ${researchEscape(student.studentNumber || `ID ${student.id}`)}</option>`,
    ).join("");
  if (researchStudents.some((student) => String(student.id) === selectedStudentId)) {
    studentSelect.value = selectedStudentId;
  }
}

async function loadResearchProjects() {
  try {
    const data = await fetchResearchProjectData();
    researchProjects = data.projects;
    researchStudents = data.students;
    researchOptions = data.options;
    researchDataReady = true;
    renderResearchOptions();
    renderResearchTable();
    renderResearchStats();
    window.dispatchEvent(new CustomEvent("research-projects-updated"));
    return true;
  } catch (error) {
    researchDataReady = false;
    console.error("Unable to load research projects from rc_projects:", error);
    showResearchNotice(error.message || "Hindi ma-load ang research projects mula sa database.", "error");
    const studentSelect = document.getElementById("researchStudentId");
    if (studentSelect) {
      studentSelect.innerHTML = '<option value="">Unable to load students</option>';
    }
    document.getElementById("researchProjectsBody").innerHTML =
      '<tr><td colspan="7" class="empty-state">Hindi ma-load ang research projects. Suriin ang database connection at i-refresh ang page.</td></tr>';
    return false;
  }
}

function findResearchProject(id) {
  return researchProjects.find((project) => String(project.id) === String(id));
}

function switchResearchTab(name) {
  document.querySelectorAll(".research-project-page .research-tab").forEach((tab) => {
    const active = tab.dataset.projectTab === name;
    tab.classList.toggle("active", active);
    tab.setAttribute("aria-selected", String(active));
  });
  document.querySelectorAll(".research-project-page .research-panel").forEach((panel) => {
    const active = panel.id === `research-panel-${name}`;
    panel.classList.toggle("active", active);
    panel.hidden = !active;
  });
}

function renderResearchStats() {
  document.getElementById("researchTotalCount").textContent = researchProjects.length;
  document.getElementById("researchReviewCount").textContent =
    researchProjects.filter((project) => project.status === "Under Review").length;
  document.getElementById("researchOngoingCount").textContent =
    researchProjects.filter((project) => project.status === "Ongoing").length;
  document.getElementById("researchCompletedCount").textContent =
    researchProjects.filter((project) => project.status === "Completed").length;
}

function renderResearchTable() {
  const tbody = document.getElementById("researchProjectsBody");
  const query = document.getElementById("researchProjectSearch").value.trim().toLowerCase();
  const category = document.getElementById("researchCategoryFilter").value;
  const classification = document.getElementById("researchClassificationFilter").value;
  const status = document.getElementById("researchStatusFilter").value;
  const projects = researchProjects.filter((project) => {
    const searchable = [
      project.registrationNo,
      project.title,
      project.investigator,
    ].join(" ").toLowerCase();
    return searchable.includes(query) &&
      (!category || project.category === category) &&
      (!classification || project.classification === classification) &&
      (!status || project.status === status);
  });

  if (!projects.length) {
    const message = researchProjects.length
      ? "No projects match the selected filters."
      : "No research projects registered yet.";
    tbody.innerHTML = `<tr><td colspan="7" class="empty-state">${message}</td></tr>`;
    return;
  }

  tbody.innerHTML = projects.map((project) => `
    <tr>
      <td data-label="Registration No.">${researchEscape(project.registrationNo)}</td>
      <td class="research-title-cell" data-label="Project Title">${researchEscape(project.title)}</td>
      <td data-label="Category / Classification">${researchEscape(optionLabel(project.category))}<small>${researchEscape(optionLabel(project.classification))}</small></td>
      <td data-label="Project Leader">${researchEscape(project.investigator)}</td>
      <td data-label="Timeline">${researchEscape(project.timeline || "Not set")}</td>
      <td data-label="Status"><span class="research-status status-${researchEscape(String(project.status || "Draft").toLowerCase().replace(/\s+/g, "-"))}">${researchEscape(project.status || "Draft")}</span></td>
      <td class="research-project-actions-cell" data-label="Actions">
        <div class="research-actions">
          <button type="button" data-project-action="details" data-project-id="${researchEscape(project.id)}">Details</button>
          <button type="button" data-project-action="edit" data-project-id="${researchEscape(project.id)}">Edit</button>
        </div>
      </td>
    </tr>`).join("");
}

function resetResearchForm() {
  const form = document.getElementById("researchProjectForm");
  form.reset();
  document.getElementById("researchProjectId").value = "";
  document.getElementById("researchRegistrationHeading").textContent = "Research Project Registration";
  document.getElementById("saveResearchProjectButton").innerHTML = '<i class="fas fa-save"></i> Register Project';
  document.querySelector("[data-project-cancel-edit]").hidden = true;
  const status = form.elements.namedItem("status");
  status.value = researchOptions.statuses.includes("Submitted") ? "Submitted" : researchOptions.statuses[0] || "";
}

function editResearchProject(id) {
  const project = findResearchProject(id);
  if (!project) return;
  const form = document.getElementById("researchProjectForm");
  form.elements.namedItem("projectId").value = project.id;
  form.elements.namedItem("title").value = project.title;
  form.elements.namedItem("category").value = project.category;
  form.elements.namedItem("classification").value = project.classification;
  form.elements.namedItem("studentId").value = project.studentId;
  form.elements.namedItem("timeline").value = project.timeline || "";
  form.elements.namedItem("status").value = project.status || "";
  document.getElementById("researchRegistrationHeading").textContent = "Edit Research Project";
  document.getElementById("saveResearchProjectButton").innerHTML = '<i class="fas fa-save"></i> Save Changes';
  document.querySelector("[data-project-cancel-edit]").hidden = false;
  switchResearchTab("registration");
  document.getElementById("researchTitle").focus();
}

function showProjectDetails(id) {
  const project = findResearchProject(id);
  if (!project) return;
  document.getElementById("researchProjectDetailsBody").innerHTML = `
    <dl class="details-grid">
      <div><dt>Registration Number</dt><dd>${researchEscape(project.registrationNo)}</dd></div>
      <div><dt>Status</dt><dd>${researchEscape(project.status || "Draft")}</dd></div>
      <div class="detail-full"><dt>Research Title</dt><dd>${researchEscape(project.title)}</dd></div>
      <div><dt>Research Category</dt><dd>${researchEscape(optionLabel(project.category))}</dd></div>
      <div><dt>Classification</dt><dd>${researchEscape(optionLabel(project.classification))}</dd></div>
      <div><dt>Project Leader</dt><dd>${researchEscape(project.investigator)}</dd></div>
      <div><dt>Timeline</dt><dd>${researchEscape(project.timeline || "Not set")}</dd></div>
      <div><dt>Registered</dt><dd>${researchEscape(project.registeredAt || "Not set")}</dd></div>
    </dl>`;
  const modal = document.getElementById("researchProjectDetailsModal");
  modal.classList.add("show");
  modal.setAttribute("aria-hidden", "false");
}

function closeProjectDetails() {
  const modal = document.getElementById("researchProjectDetailsModal");
  modal.classList.remove("show");
  modal.setAttribute("aria-hidden", "true");
}

async function submitResearchProject(form) {
  if (!researchDataReady) {
    showResearchNotice("Hindi makakapag-save habang hindi konektado sa database.", "error");
    return;
  }

  const projectId = form.elements.namedItem("projectId").value;
  const project = {
    id: projectId ? Number(projectId) : undefined,
    title: form.elements.namedItem("title").value.trim(),
    category: form.elements.namedItem("category").value,
    classification: form.elements.namedItem("classification").value,
    studentId: Number(form.elements.namedItem("studentId").value),
    timeline: form.elements.namedItem("timeline").value.trim(),
    status: form.elements.namedItem("status").value,
  };
  if (!researchStudents.some((student) => Number(student.id) === project.studentId)) {
    showResearchNotice("Pumili ng active student bilang Project Leader.", "error");
    return;
  }

  const saveButton = document.getElementById("saveResearchProjectButton");
  saveButton.disabled = true;
  try {
    await saveResearchProject(project);
    if (!await loadResearchProjects()) return;
    resetResearchForm();
    switchResearchTab("overview");
    showResearchNotice(
      projectId ? "Na-update ang research project sa rc_projects." : "Na-save ang research project sa rc_projects.",
      "success",
    );
  } catch (error) {
    console.error("Unable to save research project to rc_projects:", error);
    showResearchNotice(error.message || "Hindi nai-save ang research project.", "error");
  } finally {
    saveButton.disabled = false;
  }
}

function initializeResearchProjectPage() {
  const root = document.querySelector(".research-project-page");
  if (!root || root.dataset.initialized === "true") return;
  root.dataset.initialized = "true";
  void loadResearchProjects();

  if (researchHandlersBound) return;
  researchHandlersBound = true;
  document.addEventListener("click", (event) => {
    if (!(event.target instanceof Element)) return;
    const rootElement = document.querySelector(".research-project-page");
    if (!rootElement || !rootElement.contains(event.target)) return;

    const tab = event.target.closest("[data-project-tab]");
    if (tab) {
      if (tab.dataset.projectTab === "registration") void loadResearchProjects();
      switchResearchTab(tab.dataset.projectTab);
      if (tab.dataset.projectTab === "registration") document.getElementById("researchTitle").focus();
      return;
    }
    if (event.target.closest("[data-project-open-registration]")) {
      resetResearchForm();
      void loadResearchProjects();
      switchResearchTab("registration");
      document.getElementById("researchTitle").focus();
      return;
    }
    if (event.target.closest("[data-project-cancel-edit]")) {
      resetResearchForm();
      switchResearchTab("overview");
      return;
    }
    const action = event.target.closest("[data-project-action]");
    if (action) {
      if (action.dataset.projectAction === "edit") editResearchProject(action.dataset.projectId);
      if (action.dataset.projectAction === "details") showProjectDetails(action.dataset.projectId);
      return;
    }
    if (event.target.closest("[data-close-project-details]") ||
        (event.target.id === "researchProjectDetailsModal")) {
      closeProjectDetails();
    }
  });

  document.addEventListener("input", (event) => {
    if (event.target instanceof Element &&
        event.target.closest(".research-project-page") &&
        event.target.id === "researchProjectSearch") {
      renderResearchTable();
    }
  });
  document.addEventListener("change", (event) => {
    if (event.target instanceof Element &&
        event.target.closest(".research-project-page") &&
        ["researchCategoryFilter", "researchClassificationFilter", "researchStatusFilter"].includes(event.target.id)) {
      renderResearchTable();
    }
  });
  document.addEventListener("submit", (event) => {
    if (!(event.target instanceof HTMLFormElement) || event.target.id !== "researchProjectForm") return;
    event.preventDefault();
    void submitResearchProject(event.target);
  });
  document.addEventListener("reset", (event) => {
    if (event.target instanceof HTMLFormElement && event.target.id === "researchProjectForm") {
      window.setTimeout(resetResearchForm, 0);
    }
  });
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && document.querySelector(".research-project-page")) closeProjectDetails();
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initializeResearchProjectPage);
} else {
  initializeResearchProjectPage();
}
window.addEventListener("page:loaded", initializeResearchProjectPage);
