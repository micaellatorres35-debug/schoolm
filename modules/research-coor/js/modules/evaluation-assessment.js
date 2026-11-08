const EVALUATION_API = new URL("../../api/research_evaluations.php", import.meta.url);

let evaluationData = { projects: [], forms: [], evaluations: [], options: {} };
let evaluationReady = false;
let evaluationHandlersBound = false;

function evaluationEscape(value) {
  return String(value ?? "").replace(/[&<>"']/g, (character) => ({
    "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;",
  })[character]);
}

function getEvaluationRoot() {
  return document.querySelector(".evaluation-page");
}

function showEvaluationNotice(message, type = "") {
  const notice = document.getElementById("evaluationNotice");
  if (!notice) return;
  notice.textContent = message;
  notice.classList.toggle("is-error", type === "error");
  notice.classList.toggle("is-success", type === "success");
  notice.setAttribute("role", type === "error" ? "alert" : "status");
}

async function evaluationRequest(method = "GET", data = null) {
  const response = await fetch(EVALUATION_API, {
    method,
    headers: { Accept: "application/json", ...(data ? { "Content-Type": "application/json" } : {}) },
    credentials: "same-origin",
    ...(data ? { body: JSON.stringify(data) } : {}),
  });
  let payload;
  try {
    payload = await response.json();
  } catch (error) {
    throw new Error("Hindi mabasa ang sagot ng evaluation server.");
  }
  if (!response.ok || payload.success !== true) {
    throw new Error(payload.message || "Hindi makumpleto ang evaluation request.");
  }
  return payload;
}

function evaluationDateLabel(value) {
  if (!value) return "Not set";
  const date = new Date(`${value}T00:00:00`);
  return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString(undefined, { year: "numeric", month: "short", day: "numeric" });
}

function switchEvaluationTab(name) {
  document.querySelectorAll(".evaluation-page .evaluation-tab").forEach((tab) => {
    const active = tab.dataset.evaluationTab === name;
    tab.classList.toggle("active", active);
    tab.setAttribute("aria-selected", String(active));
  });
  document.querySelectorAll(".evaluation-page .evaluation-panel").forEach((panel) => {
    const active = panel.id === `evaluation-panel-${name}`;
    panel.classList.toggle("active", active);
    panel.hidden = !active;
  });
}

function renderEvaluationSelects() {
  const form = document.getElementById("panelEvaluationForm");
  const projectSelect = form.elements.namedItem("projectId");
  const formSelect = form.elements.namedItem("formId");
  const selectedProject = projectSelect.value;
  const selectedForm = formSelect.value;
  projectSelect.innerHTML = '<option value="">Select a research project</option>' + evaluationData.projects.map((project) =>
    `<option value="${project.id}">${evaluationEscape(project.registrationNo)} · ${evaluationEscape(project.title)}</option>`,
  ).join("");
  formSelect.innerHTML = '<option value="">Select an evaluation form</option>' + evaluationData.forms.map((item) =>
    `<option value="${item.id}">${evaluationEscape(item.name)} · ${evaluationEscape(item.stage)}</option>`,
  ).join("");
  if (evaluationData.projects.some((project) => String(project.id) === selectedProject)) projectSelect.value = selectedProject;
  if (evaluationData.forms.some((item) => String(item.id) === selectedForm)) formSelect.value = selectedForm;
  renderEvaluationCriteria();
}

function renderEvaluationCriteria(existingResults = null) {
  const form = document.getElementById("panelEvaluationForm");
  const selectedForm = evaluationData.forms.find((item) => String(item.id) === form.elements.namedItem("formId").value);
  const container = document.getElementById("evaluationCriteriaContainer");
  if (!selectedForm) {
    container.innerHTML = '<p class="evaluation-criteria-empty">Choose an evaluation form to rate its criteria.</p>';
    form.elements.namedItem("score").value = "";
    return;
  }
  const priorResults = existingResults || [];
  container.innerHTML = `<h4>Evaluation Criteria <small>Rate each item from 1 (needs improvement) to 5 (excellent).</small></h4>` +
    selectedForm.criteria.map((criterion, index) => {
      const previous = priorResults.find((result) => result.criterion === criterion);
      return `<label class="evaluation-criterion"><span>${evaluationEscape(criterion)}</span><select class="evaluation-control" name="criterionRating_${index}" required><option value="">Select rating</option>${[1, 2, 3, 4, 5].map((rating) => `<option value="${rating}" ${previous && Number(previous.rating) === rating ? "selected" : ""}>${rating}</option>`).join("")}</select></label>`;
    }).join("");
  updateEvaluationScore();
}

function updateEvaluationScore() {
  const form = document.getElementById("panelEvaluationForm");
  const selectedForm = evaluationData.forms.find((item) => String(item.id) === form.elements.namedItem("formId").value);
  const scoreField = form.elements.namedItem("score");
  if (!selectedForm || !selectedForm.criteria.length) {
    scoreField.value = "";
    return;
  }
  const ratings = selectedForm.criteria.map((_, index) => Number(form.elements.namedItem(`criterionRating_${index}`)?.value || 0));
  scoreField.value = ratings.every((rating) => rating >= 1 && rating <= 5)
    ? (ratings.reduce((sum, rating) => sum + rating, 0) / ratings.length * 20).toFixed(2)
    : "";
}

function renderEvaluationStats() {
  const records = evaluationData.evaluations;
  document.getElementById("evaluationCountTotal").textContent = records.length;
  document.getElementById("evaluationCountPending").textContent = records.filter((item) => item.recommendation === "Pending").length;
  document.getElementById("evaluationCountRevision").textContent = records.filter((item) => item.revisionStatus === "Pending" || item.revisionStatus === "In Progress" || item.revisionStatus === "Overdue" || item.recommendation === "Passed with Revisions" || item.recommendation === "Re-defense Required").length;
  document.getElementById("evaluationCountOverdue").textContent = records.filter((item) => item.revisionOverdue).length;
  document.getElementById("evaluationCountPassed").textContent = records.filter((item) => item.defenseOutcome === "Passed").length;
}

function renderEvaluationRecords() {
  const tbody = document.getElementById("evaluationRecordsBody");
  const query = document.getElementById("evaluationSearch").value.trim().toLowerCase();
  const outcome = document.getElementById("evaluationOutcomeFilter").value;
  const revision = document.getElementById("evaluationRevisionFilter").value;
  const records = evaluationData.evaluations.filter((item) => {
    const searchable = `${item.registrationNo} ${item.projectTitle} ${item.panelMember} ${item.panelRole}`.toLowerCase();
    const shownRevision = item.revisionOverdue ? "Overdue" : item.revisionStatus;
    return searchable.includes(query) && (!outcome || item.recommendation === outcome) && (!revision || shownRevision === revision);
  });
  if (!records.length) {
    tbody.innerHTML = `<tr><td colspan="8" class="evaluation-empty">${evaluationData.evaluations.length ? "No records match the selected filters." : "No panel results recorded yet. Use Record Evaluation to add one."}</td></tr>`;
    return;
  }
  tbody.innerHTML = records.map((item) => {
    const revision = item.revisionOverdue ? "Overdue" : item.revisionStatus;
    return `<tr>
      <td><span class="evaluation-project-title">${evaluationEscape(item.projectTitle)}</span><small>${evaluationEscape(item.registrationNo)} · ${evaluationEscape(item.projectStatus)}</small></td>
      <td>${evaluationEscape(item.panelMember)}<small>${evaluationEscape(item.panelRole || "Panel member")} · ${evaluationDateLabel(item.evaluationDate)}</small></td>
      <td>${evaluationEscape(item.formName || "Form removed")}<small>${evaluationEscape(item.formStage || "")}</small></td>
      <td>${item.score === null ? "—" : `${evaluationEscape(item.score)}%`}</td>
      <td><span class="evaluation-status" data-status="${evaluationEscape(item.recommendation)}">${evaluationEscape(item.recommendation)}</span></td>
      <td><span class="evaluation-status" data-status="${evaluationEscape(revision)}">${evaluationEscape(revision)}</span>${item.revisionDueDate ? `<small>Due ${evaluationDateLabel(item.revisionDueDate)}</small>` : ""}</td>
      <td><span class="evaluation-status" data-status="${evaluationEscape(item.defenseOutcome)}">${evaluationEscape(item.defenseOutcome)}</span>${item.defenseDate ? `<small>${evaluationDateLabel(item.defenseDate)}</small>` : ""}</td>
      <td><div class="evaluation-actions"><button type="button" data-evaluation-action="details" data-evaluation-id="${item.id}">Details</button><button type="button" data-evaluation-action="edit" data-evaluation-id="${item.id}">Edit</button><button type="button" data-evaluation-action="delete" data-evaluation-id="${item.id}">Delete</button></div></td>
    </tr>`;
  }).join("");
}

function renderEvaluationForms() {
  const container = document.getElementById("evaluationTemplatesList");
  if (!evaluationData.forms.length) {
    container.innerHTML = '<p class="evaluation-empty">No evaluation forms available.</p>';
    return;
  }
  container.innerHTML = evaluationData.forms.map((form) => `
    <article class="evaluation-template-card">
      <div><span class="evaluation-stage">${evaluationEscape(form.stage)}</span><h4>${evaluationEscape(form.name)}</h4><ul>${form.criteria.map((criterion) => `<li>${evaluationEscape(criterion)}</li>`).join("")}</ul></div>
      <div class="evaluation-actions"><button type="button" data-template-action="edit" data-template-id="${form.id}">Edit</button><button type="button" data-template-action="delete" data-template-id="${form.id}">Delete</button></div>
    </article>`).join("");
}

function renderEvaluationData() {
  renderEvaluationSelects();
  renderEvaluationStats();
  renderEvaluationRecords();
  renderEvaluationForms();
}

async function loadEvaluationData() {
  try {
    evaluationData = await evaluationRequest();
    evaluationReady = true;
    renderEvaluationData();
    return true;
  } catch (error) {
    evaluationReady = false;
    console.error("Unable to load research evaluation data:", error);
    showEvaluationNotice(error.message || "Hindi ma-load ang evaluation data.", "error");
    document.getElementById("evaluationRecordsBody").innerHTML = '<tr><td colspan="8" class="evaluation-empty">Hindi ma-load ang records. Suriin ang database connection at i-refresh ang page.</td></tr>';
    document.getElementById("evaluationTemplatesList").innerHTML = '<p class="evaluation-empty">Hindi ma-load ang evaluation forms.</p>';
    return false;
  }
}

function resetEvaluationForm() {
  const form = document.getElementById("panelEvaluationForm");
  form.reset();
  finalizeEvaluationFormReset();
}

function finalizeEvaluationFormReset() {
  const form = document.getElementById("panelEvaluationForm");
  form.elements.namedItem("id").value = "";
  document.getElementById("evaluationFormHeading").textContent = "Record Panel Evaluation";
  document.querySelector("[data-evaluation-cancel]").hidden = true;
  renderEvaluationCriteria();
}

function editEvaluation(record) {
  const form = document.getElementById("panelEvaluationForm");
  Object.entries({
    id: record.id, projectId: record.projectId, formId: record.formId, panelMember: record.panelMember,
    panelRole: record.panelRole, evaluationDate: record.evaluationDate, score: record.score ?? "",
    recommendation: record.recommendation, comments: record.comments, recommendations: record.recommendations,
    revisionRequirements: record.revisionRequirements, revisionStatus: record.revisionStatus,
    revisionDueDate: record.revisionDueDate || "", defenseDate: record.defenseDate || "", defenseOutcome: record.defenseOutcome,
  }).forEach(([name, value]) => { form.elements.namedItem(name).value = value; });
  renderEvaluationCriteria(record.criteriaResults);
  document.getElementById("evaluationFormHeading").textContent = "Update Panel Evaluation";
  form.querySelector('[data-evaluation-cancel]').hidden = false;
  switchEvaluationTab("record");
}

function resetEvaluationTemplate() {
  const form = document.getElementById("evaluationTemplateForm");
  form.reset();
  finalizeEvaluationTemplateReset();
}

function finalizeEvaluationTemplateReset() {
  const form = document.getElementById("evaluationTemplateForm");
  form.elements.namedItem("id").value = "";
  document.getElementById("evaluationTemplateHeading").textContent = "Create Evaluation Form";
}

function editEvaluationTemplate(template) {
  const form = document.getElementById("evaluationTemplateForm");
  form.elements.namedItem("id").value = template.id;
  form.elements.namedItem("name").value = template.name;
  form.elements.namedItem("stage").value = template.stage;
  form.elements.namedItem("criteria").value = template.criteria.join("\n");
  document.getElementById("evaluationTemplateHeading").textContent = "Update Evaluation Form";
  form.elements.namedItem("name").focus();
}

async function saveEvaluation(event) {
  event.preventDefault();
  const form = event.currentTarget;
  if (!evaluationReady) return showEvaluationNotice("Hindi available ang database; hindi nai-save ang evaluation.", "error");
  const values = Object.fromEntries(new FormData(form).entries());
  const isUpdate = Boolean(values.id);
  const selectedForm = evaluationData.forms.find((item) => String(item.id) === values.formId);
  values.criteriaResults = selectedForm ? selectedForm.criteria.map((criterion, index) => ({
    criterion,
    rating: values[`criterionRating_${index}`] || "",
  })) : [];
  try {
    await evaluationRequest(isUpdate ? "PUT" : "POST", { type: "evaluation", ...values });
    showEvaluationNotice("Nai-save ang panel evaluation sa research database.", "success");
    resetEvaluationForm();
    switchEvaluationTab("records");
    await loadEvaluationData();
  } catch (error) {
    showEvaluationNotice(error.message, "error");
  }
}

async function saveEvaluationTemplate(event) {
  event.preventDefault();
  const form = event.currentTarget;
  if (!evaluationReady) return showEvaluationNotice("Hindi available ang database; hindi nai-save ang form.", "error");
  const values = Object.fromEntries(new FormData(form).entries());
  const isUpdate = Boolean(values.id);
  values.criteria = values.criteria.split(/\r?\n/).map((criterion) => criterion.trim()).filter(Boolean);
  try {
    await evaluationRequest(isUpdate ? "PUT" : "POST", { type: "form", ...values });
    showEvaluationNotice("Nai-save ang evaluation form.", "success");
    resetEvaluationTemplate();
    await loadEvaluationData();
  } catch (error) {
    showEvaluationNotice(error.message, "error");
  }
}

function showEvaluationDetails(record) {
  const detail = [
    `Project: ${record.projectTitle} (${record.registrationNo})`,
    `Panel member: ${record.panelMember}${record.panelRole ? ` — ${record.panelRole}` : ""}`,
    `Form: ${record.formName || "Form removed"}${record.formStage ? ` (${record.formStage})` : ""}`,
    `Evaluation date: ${evaluationDateLabel(record.evaluationDate)}`,
    `Score: ${record.score === null ? "Not entered" : `${record.score}%`}`,
    `Criteria: ${record.criteriaResults.map((item) => `${item.criterion}: ${item.rating}/5`).join("; ") || "None"}`,
    `Recommendation: ${record.recommendation}`,
    `Comments: ${record.comments || "None"}`,
    `Recommendations: ${record.recommendations || "None"}`,
    `Revision requirements: ${record.revisionRequirements || "None"}`,
    `Revision status: ${record.revisionOverdue ? "Overdue" : record.revisionStatus}`,
    `Revision due: ${evaluationDateLabel(record.revisionDueDate)}`,
    `Defense: ${record.defenseOutcome}${record.defenseDate ? ` on ${evaluationDateLabel(record.defenseDate)}` : ""}`,
  ];
  window.alert(detail.join("\n"));
}

async function deleteEvaluationRecord(type, id) {
  if (!window.confirm(type === "form" ? "Delete this evaluation form?" : "Delete this panel evaluation record?")) return;
  try {
    await evaluationRequest("DELETE", { type, id });
    showEvaluationNotice(type === "form" ? "Nai-delete ang evaluation form." : "Nai-delete ang evaluation record.", "success");
    await loadEvaluationData();
  } catch (error) {
    showEvaluationNotice(error.message, "error");
  }
}

function bindEvaluationHandlers() {
  if (evaluationHandlersBound) return;
  evaluationHandlersBound = true;
  document.addEventListener("click", (event) => {
    if (!event.target.closest(".evaluation-page")) return;
    const tab = event.target.closest("[data-evaluation-tab]");
    if (tab) switchEvaluationTab(tab.dataset.evaluationTab);
    if (event.target.closest("[data-evaluation-cancel]")) resetEvaluationForm();
    const action = event.target.closest("[data-evaluation-action]");
    if (action) {
      const record = evaluationData.evaluations.find((item) => String(item.id) === action.dataset.evaluationId);
      if (!record) return;
      if (action.dataset.evaluationAction === "edit") editEvaluation(record);
      if (action.dataset.evaluationAction === "details") showEvaluationDetails(record);
      if (action.dataset.evaluationAction === "delete") deleteEvaluationRecord("evaluation", record.id);
    }
    const templateAction = event.target.closest("[data-template-action]");
    if (templateAction) {
      const template = evaluationData.forms.find((item) => String(item.id) === templateAction.dataset.templateId);
      if (!template) return;
      if (templateAction.dataset.templateAction === "edit") editEvaluationTemplate(template);
      if (templateAction.dataset.templateAction === "delete") deleteEvaluationRecord("form", template.id);
    }
  });
  document.addEventListener("input", (event) => {
    if (event.target.closest(".evaluation-page") && event.target.id === "evaluationSearch") renderEvaluationRecords();
  });
  document.addEventListener("change", (event) => {
    if (event.target.closest("#panelEvaluationForm") && event.target.name === "formId") renderEvaluationCriteria();
    if (event.target.closest("#panelEvaluationForm") && event.target.name.startsWith("criterionRating_")) updateEvaluationScore();
    if (event.target.closest(".evaluation-page") && ["evaluationOutcomeFilter", "evaluationRevisionFilter"].includes(event.target.id)) renderEvaluationRecords();
  });
  document.addEventListener("submit", (event) => {
    if (event.target.id === "panelEvaluationForm") saveEvaluation(event);
    if (event.target.id === "evaluationTemplateForm") saveEvaluationTemplate(event);
  });
  document.addEventListener("reset", (event) => {
    if (event.target.id === "panelEvaluationForm") setTimeout(finalizeEvaluationFormReset, 0);
    if (event.target.id === "evaluationTemplateForm") setTimeout(finalizeEvaluationTemplateReset, 0);
  });
}

function initializeEvaluationPage() {
  if (!getEvaluationRoot()) return;
  bindEvaluationHandlers();
  loadEvaluationData();
}

document.addEventListener("DOMContentLoaded", initializeEvaluationPage);
window.addEventListener("page:loaded", initializeEvaluationPage);
