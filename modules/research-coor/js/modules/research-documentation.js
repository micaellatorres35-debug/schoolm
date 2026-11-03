import { fetchResearchProjects } from "./research-project-data.js";

const RESEARCH_DOCUMENTS_KEY = "researchCoor_documents";
const RESEARCH_DOCUMENTS_DB = "researchCoor_documentFiles";
const RESEARCH_DOCUMENTS_STORE = "files";
const RESEARCH_DOCUMENT_MAX_BYTES = 25 * 1024 * 1024;
const RESEARCH_DOCUMENT_EXTENSIONS = new Set([
  "pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "csv",
  "jpg", "jpeg", "png",
]);
const RESEARCH_DOCUMENT_CATEGORIES = [
  "Research Proposal Documents",
  "Research Manuscripts",
  "Research Chapters and Appendices",
  "Final Research Papers",
  "Supporting Research Documents",
  "Research Ethics Documents",
  "Informed Consent Documents",
  "Plagiarism Check Documents",
  "Compliance Evidence",
  "Research Progress Documents",
  "Research Accomplishment Reports",
  "Other Research Documents",
];

let researchDocuments = [];
let documentStorageReady = true;
let documentHandlersBound = false;
let documentDatabasePromise = null;
let modalDocumentId = "";
let linkedProposals = [];
let linkedProjects = [];

function documentEscape(value) {
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

function getDocumentationRoot() {
  return document.querySelector(".research-documentation-page");
}

function showDocumentationNotice(message, type = "") {
  const notice = document.getElementById("documentationNotice");
  if (!notice) return;
  notice.textContent = message;
  notice.classList.toggle("is-error", type === "error");
  notice.classList.toggle("is-success", type === "success");
  notice.setAttribute("role", type === "error" ? "alert" : "status");
}

function loadResearchDocuments() {
  documentStorageReady = true;
  try {
    const stored = window.localStorage.getItem(RESEARCH_DOCUMENTS_KEY);
    if (stored === null) {
      researchDocuments = [];
      return true;
    }
    const parsed = JSON.parse(stored);
    if (
      !Array.isArray(parsed) ||
      !parsed.every(
        (record) =>
          record &&
          typeof record === "object" &&
          !Array.isArray(record) &&
          typeof record.id === "string",
      )
    ) {
      throw new Error("Stored research document data must be a list of records.");
    }
    researchDocuments = parsed.map((record) => ({
      ...record,
      archived: record.archived === true,
      history: Array.isArray(record.history) ? record.history : [],
    }));
    return true;
  } catch (error) {
    documentStorageReady = false;
    console.error("Unable to load research documents:", error);
    showDocumentationNotice(
      "Hindi mabasa ang document records. Walang binagong data; ayusin muna ang browser storage bago mag-save.",
      "error",
    );
    return false;
  }
}

function saveResearchDocuments(nextDocuments) {
  if (!documentStorageReady) {
    showDocumentationNotice("Hindi nai-save ang document dahil may storage error.", "error");
    return false;
  }
  try {
    window.localStorage.setItem(
      RESEARCH_DOCUMENTS_KEY,
      JSON.stringify(nextDocuments),
    );
    researchDocuments = nextDocuments;
    window.dispatchEvent(new CustomEvent("research-documents-updated"));
    showDocumentationNotice("Research document record saved in this browser.", "success");
    return true;
  } catch (error) {
    console.error("Unable to save research document metadata:", error);
    showDocumentationNotice(
      "Hindi nai-save ang document record. Suriin ang browser storage at subukang muli.",
      "error",
    );
    return false;
  }
}

function openDocumentDatabase() {
  if (!("indexedDB" in window)) {
    return Promise.reject(new Error("This browser does not support IndexedDB file storage."));
  }
  if (documentDatabasePromise) return documentDatabasePromise;
  documentDatabasePromise = new Promise((resolve, reject) => {
    const request = window.indexedDB.open(RESEARCH_DOCUMENTS_DB, 1);
    request.onupgradeneeded = () => {
      const database = request.result;
      if (!database.objectStoreNames.contains(RESEARCH_DOCUMENTS_STORE)) {
        database.createObjectStore(RESEARCH_DOCUMENTS_STORE, { keyPath: "id" });
      }
    };
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => {
      documentDatabasePromise = null;
      reject(request.error || new Error("Could not open document file storage."));
    };
    request.onblocked = () => {
      documentDatabasePromise = null;
      reject(new Error("Document storage is blocked by another open tab."));
    };
  });
  return documentDatabasePromise;
}

function storeDocumentFile(id, file) {
  return openDocumentDatabase().then(
    (database) =>
      new Promise((resolve, reject) => {
        const transaction = database.transaction(RESEARCH_DOCUMENTS_STORE, "readwrite");
        transaction.objectStore(RESEARCH_DOCUMENTS_STORE).put({
          id,
          blob: file,
          name: file.name,
          type: file.type || "application/octet-stream",
          size: file.size,
          lastModified: file.lastModified,
        });
        transaction.oncomplete = () => resolve();
        transaction.onerror = () =>
          reject(transaction.error || new Error("Could not store the uploaded file."));
        transaction.onabort = () =>
          reject(transaction.error || new Error("Document file storage was cancelled."));
      }),
  );
}

function getStoredDocumentFile(id) {
  return openDocumentDatabase().then(
    (database) =>
      new Promise((resolve, reject) => {
        const request = database
          .transaction(RESEARCH_DOCUMENTS_STORE, "readonly")
          .objectStore(RESEARCH_DOCUMENTS_STORE)
          .get(id);
        request.onsuccess = () => resolve(request.result || null);
        request.onerror = () =>
          reject(request.error || new Error("Could not retrieve the document file."));
      }),
  );
}

function removeStoredDocumentFile(id) {
  return openDocumentDatabase().then(
    (database) =>
      new Promise((resolve, reject) => {
        const transaction = database.transaction(RESEARCH_DOCUMENTS_STORE, "readwrite");
        transaction.objectStore(RESEARCH_DOCUMENTS_STORE).delete(id);
        transaction.oncomplete = () => resolve();
        transaction.onerror = () =>
          reject(transaction.error || new Error("Could not clean up the replaced file."));
      }),
  );
}

function formatDocumentSize(bytes) {
  const size = Number(bytes) || 0;
  if (size < 1024) return `${size} B`;
  if (size < 1024 * 1024) return `${(size / 1024).toFixed(1)} KB`;
  return `${(size / (1024 * 1024)).toFixed(1)} MB`;
}

function formatDocumentDate(value, includeTime = false) {
  if (!value) return "Not set";
  const date = /^\d{4}-\d{2}-\d{2}$/.test(String(value))
    ? new Date(
        Number(value.slice(0, 4)),
        Number(value.slice(5, 7)) - 1,
        Number(value.slice(8, 10)),
      )
    : new Date(value);
  if (Number.isNaN(date.getTime())) return String(value);
  return date.toLocaleString(undefined, includeTime
    ? { year: "numeric", month: "short", day: "numeric", hour: "numeric", minute: "2-digit" }
    : { year: "numeric", month: "short", day: "numeric" });
}

function documentRelatedLabel(record) {
  const proposal = linkedProposals.find((item) => item.id === record.proposalId);
  const project = linkedProjects.find((item) => String(item.id) === String(record.projectId));
  const labels = [];
  if (proposal) labels.push(`Proposal: ${proposal.reference || proposal.title}`);
  else if (record.proposalTitle) labels.push(`Proposal: ${record.proposalTitle}`);
  if (project) labels.push(`Project: ${project.registrationNo || project.title}`);
  else if (record.projectTitle) labels.push(`Project: ${record.projectTitle}`);
  return labels.length ? labels.join(" · ") : "Unlinked";
}

function renderDocumentStats() {
  const active = researchDocuments.filter((record) => !record.archived);
  document.getElementById("documentCountActive").textContent = active.length;
  document.getElementById("documentCountProposals").textContent =
    active.filter((record) => record.category === RESEARCH_DOCUMENT_CATEGORIES[0]).length;
  document.getElementById("documentCountOutputs").textContent =
    active.filter((record) =>
      ["Research Manuscripts", "Research Chapters and Appendices", "Final Research Papers"].includes(record.category),
    ).length;
  document.getElementById("documentCountArchived").textContent =
    researchDocuments.filter((record) => record.archived).length;
}

function documentMatchesLinkFilter(record, filterValue) {
  if (!filterValue) return true;
  if (filterValue === "proposal") return Boolean(record.proposalId);
  if (filterValue === "project") return Boolean(record.projectId);
  return !record.proposalId && !record.projectId;
}

function renderDocumentTable() {
  const query = document.getElementById("documentSearch").value.trim().toLowerCase();
  const category = document.getElementById("documentCategoryFilter").value;
  const linkFilter = document.getElementById("documentLinkFilter").value;
  const records = researchDocuments.filter((record) => {
    if (record.archived) return false;
    const searchable = [
      record.title,
      record.fileName,
      record.category,
      record.version,
      record.description,
      documentRelatedLabel(record),
    ].join(" ").toLowerCase();
    return (
      searchable.includes(query) &&
      (!category || record.category === category) &&
      documentMatchesLinkFilter(record, linkFilter)
    );
  });
  const tbody = document.getElementById("documentationRecordsBody");
  if (!records.length) {
    tbody.innerHTML = `<tr><td colspan="6" class="documentation-empty">${
      researchDocuments.some((record) => !record.archived)
        ? "No documents match the selected filters."
        : "No research documents uploaded yet."
    }</td></tr>`;
    return;
  }
  tbody.innerHTML = records
    .map(
      (record) => `
        <tr>
          <td class="documentation-title-cell">${documentEscape(record.title)}<small>${documentEscape(record.fileName)}${record.version ? ` · ${documentEscape(record.version)}` : ""}</small></td>
          <td>${documentEscape(record.category)}</td>
          <td>${documentEscape(documentRelatedLabel(record))}</td>
          <td>${documentEscape(formatDocumentDate(record.uploadedAt))}</td>
          <td>${documentEscape(formatDocumentSize(record.fileSize))}</td>
          <td><div class="documentation-actions">
            <button type="button" data-document-action="details" data-document-id="${documentEscape(record.id)}">Details</button>
            <button type="button" data-document-action="download" data-document-id="${documentEscape(record.id)}">Download</button>
            <button type="button" data-document-action="edit" data-document-id="${documentEscape(record.id)}">Edit</button>
            <button type="button" data-document-action="archive" data-document-id="${documentEscape(record.id)}">Archive</button>
          </div></td>
        </tr>`,
    )
    .join("");
}

function renderDocumentArchive() {
  const archived = researchDocuments.filter((record) => record.archived);
  const tbody = document.getElementById("documentationArchiveBody");
  if (!archived.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="documentation-empty">No documents in the archive.</td></tr>';
    return;
  }
  tbody.innerHTML = archived
    .map(
      (record) => `
        <tr>
          <td class="documentation-title-cell">${documentEscape(record.title)}<small>${documentEscape(record.fileName)}</small></td>
          <td>${documentEscape(record.category)}</td>
          <td>${documentEscape(documentRelatedLabel(record))}</td>
          <td>${documentEscape(formatDocumentDate(record.archivedAt))}</td>
          <td><div class="documentation-actions">
            <button type="button" data-document-action="details" data-document-id="${documentEscape(record.id)}">Details</button>
            <button type="button" data-document-action="download" data-document-id="${documentEscape(record.id)}">Download</button>
            <button type="button" data-document-action="restore" data-document-id="${documentEscape(record.id)}">Restore</button>
          </div></td>
        </tr>`,
    )
    .join("");
}

async function fillDocumentLinkOptions() {
  const proposalSelect = document.getElementById("documentProposalSelect");
  const projectSelect = document.getElementById("documentProjectSelect");
  if (!proposalSelect || !projectSelect) return;
  const selectedProposalId = proposalSelect.value;
  const selectedProjectId = projectSelect.value;
  try {
    const proposals = JSON.parse(window.localStorage.getItem("researchCoor_proposals") || "[]");
    if (
      !Array.isArray(proposals) ||
      !proposals.every((item) => item && typeof item === "object" && typeof item.id === "string")
    ) {
      throw new Error("Linked proposal records must be a list.");
    }
    linkedProposals = proposals;
  } catch (error) {
    linkedProposals = [];
    console.error("Unable to load research proposal links:", error);
    showDocumentationNotice(
      "Hindi ma-load ang proposal links. Maaari pa ring mag-upload ng unlinked documents.",
      "error",
    );
  }
  try {
    linkedProjects = await fetchResearchProjects();
  } catch (error) {
    linkedProjects = [];
    console.error("Unable to load research project links:", error);
    showDocumentationNotice(error.message || "Hindi ma-load ang project links mula sa database.", "error");
  }
  proposalSelect.innerHTML =
    '<option value="">No proposal linked</option>' +
    linkedProposals.map((proposal) =>
      `<option value="${documentEscape(proposal.id)}">${documentEscape(proposal.reference || "Proposal")} — ${documentEscape(proposal.title)}</option>`,
    ).join("");
  projectSelect.innerHTML =
    '<option value="">No project linked</option>' +
    linkedProjects.filter((project) => project.archived !== true).map((project) =>
      `<option value="${documentEscape(project.id)}">${documentEscape(project.registrationNo || "Project")} — ${documentEscape(project.title)}</option>`,
    ).join("");
  if (linkedProposals.some((proposal) => proposal.id === selectedProposalId)) {
    proposalSelect.value = selectedProposalId;
  }
  if (linkedProjects.some((project) => String(project.id) === selectedProjectId && project.archived !== true)) {
    projectSelect.value = selectedProjectId;
  }
}

function renderDocumentationPage() {
  renderDocumentStats();
  renderDocumentTable();
  renderDocumentArchive();
}

function switchDocumentationTab(name) {
  document.querySelectorAll(".research-documentation-page .documentation-tab").forEach((tab) => {
    const active = tab.dataset.documentTab === name;
    tab.classList.toggle("active", active);
    tab.setAttribute("aria-selected", String(active));
  });
  document.querySelectorAll(".research-documentation-page .documentation-panel").forEach((panel) => {
    const active = panel.id === `documentation-panel-${name}`;
    panel.classList.toggle("active", active);
    panel.hidden = !active;
  });
}

function resetDocumentationForm() {
  const form = document.getElementById("documentationForm");
  if (!form) return;
  form.reset();
  document.getElementById("documentId").value = "";
  document.getElementById("documentFormHeading").textContent = "Upload Research Document";
  document.getElementById("saveDocumentButton").innerHTML =
    '<i class="fas fa-save"></i> Save Document';
  document.getElementById("documentationFile").required = true;
  document.getElementById("existingDocumentFile").hidden = true;
  document.getElementById("existingDocumentFile").textContent = "";
  document.querySelector(".documentation-section-heading [data-document-cancel]").hidden = true;
  document.querySelector(".documentation-form-actions [data-document-cancel]").hidden = false;
  fillDocumentLinkOptions();
}

function editResearchDocument(documentId) {
  const record = researchDocuments.find((item) => item.id === documentId);
  if (!record || record.archived) return;
  const form = document.getElementById("documentationForm");
  const values = {
    documentId: record.id,
    title: record.title,
    category: record.category,
    version: record.version,
    proposalId: record.proposalId || "",
    projectId: record.projectId || "",
    documentDate: record.documentDate || "",
    uploadedBy: record.uploadedBy || "",
    description: record.description || "",
  };
  Object.entries(values).forEach(([name, value]) => {
    const field = form.elements.namedItem(name);
    if (field) field.value = value;
  });
  document.getElementById("documentFormHeading").textContent = "Edit Research Document";
  document.getElementById("saveDocumentButton").innerHTML =
    '<i class="fas fa-save"></i> Save Changes';
  document.getElementById("documentationFile").required = false;
  const existingFile = document.getElementById("existingDocumentFile");
  existingFile.hidden = false;
  existingFile.textContent = `Current file: ${record.fileName} (${formatDocumentSize(record.fileSize)}). Choose a replacement if needed.`;
  document.querySelectorAll("[data-document-cancel]").forEach((button) => {
    button.hidden = false;
  });
  switchDocumentationTab("upload");
  form.elements.namedItem("title").focus();
}

function getSelectedRecord(selectElement, records) {
  return records.find((item) => item.id === selectElement.value) || null;
}

function documentHistoryEntry(action, actor = "") {
  return {
    action,
    actor,
    date: new Date().toISOString(),
  };
}

async function saveResearchDocument(form) {
  if (!documentStorageReady) {
    showDocumentationNotice("Hindi maaaring mag-save habang may storage error.", "error");
    return;
  }
  const data = new FormData(form);
  const documentId = String(data.get("documentId") || "");
  const existing = researchDocuments.find((item) => item.id === documentId);
  const file = document.getElementById("documentationFile").files[0] || null;
  if (!file && !existing) {
    showDocumentationNotice("Pumili muna ng document file bago mag-save.", "error");
    return;
  }
  if (file) {
    const extension = file.name.split(".").pop().toLowerCase();
    if (!RESEARCH_DOCUMENT_EXTENSIONS.has(extension)) {
      showDocumentationNotice("Hindi suportado ang file type na ito.", "error");
      return;
    }
    if (file.size > RESEARCH_DOCUMENT_MAX_BYTES) {
      showDocumentationNotice("Lumampas sa 25 MB ang file. Pumili ng mas maliit na file.", "error");
      return;
    }
    if (file.size === 0) {
      showDocumentationNotice("Walang laman ang file na pinili.", "error");
      return;
    }
  }
  const title = String(data.get("title") || "").trim();
  if (!title) {
    showDocumentationNotice("Ilagay ang document title.", "error");
    form.elements.namedItem("title").focus();
    return;
  }
  const proposal = getSelectedRecord(
    document.getElementById("documentProposalSelect"),
    linkedProposals,
  );
  const project = getSelectedRecord(
    document.getElementById("documentProjectSelect"),
    linkedProjects,
  );
  const fileStorageId = file
    ? `${documentId || Date.now()}-${Math.random().toString(36).slice(2, 10)}`
    : existing.fileStorageId;
  try {
    if (file) await storeDocumentFile(fileStorageId, file);
  } catch (error) {
    console.error("Unable to store uploaded research document:", error);
    showDocumentationNotice(
      `Hindi na-upload ang file: ${error.message || "Hindi available ang browser file storage."}`,
      "error",
    );
    return;
  }
  const now = new Date().toISOString();
  const record = {
    ...(existing || {}),
    id: existing?.id || `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    title,
    category: String(data.get("category") || ""),
    version: String(data.get("version") || "").trim(),
    proposalId: proposal?.id || "",
    proposalTitle: proposal?.title || "",
    projectId: project?.id || "",
    projectTitle: project?.title || "",
    documentDate: String(data.get("documentDate") || ""),
    uploadedBy: String(data.get("uploadedBy") || "").trim(),
    description: String(data.get("description") || "").trim(),
    fileStorageId,
    fileName: file?.name || existing.fileName,
    fileType: file?.type || existing.fileType,
    fileSize: file?.size ?? existing.fileSize,
    uploadedAt: file ? now.slice(0, 10) : existing.uploadedAt,
    updatedAt: now,
    archived: false,
    archivedAt: "",
    history: [
      ...(existing?.history || []),
      documentHistoryEntry(
        file
          ? existing
            ? "Document metadata edited and file replaced"
            : "Document uploaded"
          : "Document metadata edited",
        String(data.get("uploadedBy") || "").trim(),
      ),
    ],
  };
  const next = existing
    ? researchDocuments.map((item) => (item.id === existing.id ? record : item))
    : [record, ...researchDocuments];
  if (!saveResearchDocuments(next)) {
    if (file) {
      try {
        await removeStoredDocumentFile(fileStorageId);
      } catch (cleanupError) {
        console.error("Unable to remove unreferenced uploaded document:", cleanupError);
      }
    }
    return;
  }
  if (file && existing?.fileStorageId && existing.fileStorageId !== fileStorageId) {
    try {
      await removeStoredDocumentFile(existing.fileStorageId);
    } catch (cleanupError) {
      console.error("Unable to remove the replaced research document file:", cleanupError);
      showDocumentationNotice(
        "Nai-save ang bagong file, pero hindi naalis ang lumang kopya sa browser storage.",
        "error",
      );
    }
  }
  resetDocumentationForm();
  renderDocumentationPage();
  switchDocumentationTab("library");
}

async function downloadResearchDocument(documentId) {
  const record = researchDocuments.find((item) => item.id === documentId);
  if (!record) return;
  try {
    const stored = await getStoredDocumentFile(record.fileStorageId);
    if (!stored?.blob) {
      throw new Error("The file is missing from this browser's document storage.");
    }
    const url = URL.createObjectURL(stored.blob);
    const anchor = document.createElement("a");
    anchor.href = url;
    anchor.download = record.fileName || stored.name || "research-document";
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();
    window.setTimeout(() => URL.revokeObjectURL(url), 60000);
    const updated = {
      ...record,
      history: [...record.history, documentHistoryEntry("Document downloaded")],
    };
    const historySaved = saveResearchDocuments(
      researchDocuments.map((item) => (item.id === record.id ? updated : item)),
    );
    showDocumentationNotice(
      historySaved
        ? `Downloading ${record.fileName}.`
        : `Downloading ${record.fileName}; the download history could not be saved.`,
      historySaved ? "success" : "error",
    );
  } catch (error) {
    console.error("Unable to download research document:", error);
    showDocumentationNotice(
      `Hindi ma-download ang file: ${error.message || "File storage error."}`,
      "error",
    );
  }
}

function showDocumentDetails(documentId) {
  const record = researchDocuments.find((item) => item.id === documentId);
  if (!record) return;
  modalDocumentId = record.id;
  const body = document.getElementById("documentDetailsBody");
  body.innerHTML = `
    <dl class="documentation-details-grid">
      <div><dt>Title</dt><dd>${documentEscape(record.title)}</dd></div>
      <div><dt>Category</dt><dd>${documentEscape(record.category)}</dd></div>
      <div><dt>File</dt><dd>${documentEscape(record.fileName)} · ${documentEscape(formatDocumentSize(record.fileSize))}</dd></div>
      <div><dt>Version / Chapter</dt><dd>${documentEscape(record.version || "Not set")}</dd></div>
      <div><dt>Related Record</dt><dd>${documentEscape(documentRelatedLabel(record))}</dd></div>
      <div><dt>Document Date / Uploaded</dt><dd>${documentEscape(formatDocumentDate(record.documentDate))} · ${documentEscape(formatDocumentDate(record.uploadedAt))}</dd></div>
      <div><dt>Uploaded By</dt><dd>${documentEscape(record.uploadedBy || "Not set")}</dd></div>
      <div><dt>Record Status</dt><dd>${record.archived ? "Archived" : "Active"}</dd></div>
      <div class="full"><dt>Description / Notes</dt><dd>${documentEscape(record.description || "No notes recorded.")}</dd></div>
      <div class="full"><dt>Document History</dt><dd>${record.history.map((entry) => `<p>${documentEscape(formatDocumentDate(entry.date, true))} · ${documentEscape(entry.action)}${entry.actor ? ` · ${documentEscape(entry.actor)}` : ""}</p>`).join("")}</dd></div>
    </dl>`;
  document.getElementById("documentDetailsModal").classList.add("show");
  document.getElementById("documentDetailsModal").setAttribute("aria-hidden", "false");
}

function closeDocumentDetails() {
  const modal = document.getElementById("documentDetailsModal");
  modal.classList.remove("show");
  modal.setAttribute("aria-hidden", "true");
  modalDocumentId = "";
}

function updateDocumentArchive(documentId, shouldArchive) {
  const record = researchDocuments.find((item) => item.id === documentId);
  if (!record || record.archived === shouldArchive) return;
  const prompt = shouldArchive
    ? `Archive "${record.title}"? The file will remain downloadable.`
    : `Restore "${record.title}" to the document library?`;
  if (!window.confirm(prompt)) return;
  const updated = {
    ...record,
    archived: shouldArchive,
    archivedAt: shouldArchive ? new Date().toISOString() : "",
    history: [
      ...record.history,
      documentHistoryEntry(shouldArchive ? "Document archived" : "Document restored"),
    ],
  };
  if (!saveResearchDocuments(
    researchDocuments.map((item) => (item.id === record.id ? updated : item)),
  )) return;
  renderDocumentationPage();
  switchDocumentationTab(shouldArchive ? "library" : "archive");
}

async function initializeResearchDocumentationPage() {
  const root = getDocumentationRoot();
  if (!root || root.dataset.initialized === "true") return;
  root.dataset.initialized = "true";
  loadResearchDocuments();
  await fillDocumentLinkOptions();
  renderDocumentationPage();
  if (documentHandlersBound) return;
  documentHandlersBound = true;

  document.addEventListener("click", (event) => {
    const activeRoot = getDocumentationRoot();
    if (!activeRoot || !(event.target instanceof Element)) return;
    const tab = event.target.closest("[data-document-tab]");
    if (tab && activeRoot.contains(tab)) {
      switchDocumentationTab(tab.dataset.documentTab);
      return;
    }
    if (event.target.closest("[data-document-new]")?.closest(".research-documentation-page")) {
      resetDocumentationForm();
      switchDocumentationTab("upload");
      document.querySelector('#documentationForm [name="title"]').focus();
      return;
    }
    if (event.target.closest("[data-document-cancel]")?.closest(".research-documentation-page")) {
      resetDocumentationForm();
      switchDocumentationTab("library");
      return;
    }
    const action = event.target.closest("[data-document-action]");
    if (action && activeRoot.contains(action)) {
      const { documentId, documentAction } = action.dataset;
      if (documentAction === "details") showDocumentDetails(documentId);
      if (documentAction === "download") downloadResearchDocument(documentId);
      if (documentAction === "edit") editResearchDocument(documentId);
      if (documentAction === "archive") updateDocumentArchive(documentId, true);
      if (documentAction === "restore") updateDocumentArchive(documentId, false);
      return;
    }
    if (event.target.closest("[data-document-close]")) {
      closeDocumentDetails();
      return;
    }
    if (event.target.closest("[data-document-modal-download]") && modalDocumentId) {
      downloadResearchDocument(modalDocumentId);
      return;
    }
    if (event.target.id === "documentDetailsModal") closeDocumentDetails();
  });
  document.addEventListener("input", (event) => {
    if (getDocumentationRoot() && event.target instanceof Element &&
        event.target.id === "documentSearch") renderDocumentTable();
  });
  document.addEventListener("change", (event) => {
    if (!getDocumentationRoot() || !(event.target instanceof Element)) return;
    if (["documentCategoryFilter", "documentLinkFilter"].includes(event.target.id)) {
      renderDocumentTable();
    }
  });
  document.addEventListener("submit", (event) => {
    const activeRoot = getDocumentationRoot();
    if (!activeRoot || !(event.target instanceof HTMLFormElement) ||
        event.target.id !== "documentationForm" || !activeRoot.contains(event.target)) return;
    event.preventDefault();
    saveResearchDocument(event.target);
  });
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && getDocumentationRoot()) closeDocumentDetails();
  });
  window.addEventListener("storage", (event) => {
    if (!getDocumentationRoot()) return;
    if (event.key === RESEARCH_DOCUMENTS_KEY) {
      if (loadResearchDocuments()) renderDocumentationPage();
    }
    if (event.key === "researchCoor_proposals") {
      void fillDocumentLinkOptions().then(() => {
        renderDocumentTable();
        renderDocumentArchive();
      });
    }
  });
  window.addEventListener("research-projects-updated", () => {
    if (!getDocumentationRoot()) return;
    void fillDocumentLinkOptions().then(() => {
      renderDocumentTable();
      renderDocumentArchive();
    });
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initializeResearchDocumentationPage);
} else {
  initializeResearchDocumentationPage();
}
window.addEventListener("page:loaded", initializeResearchDocumentationPage);
