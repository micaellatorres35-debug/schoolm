(function () {
  "use strict";

  const ETHICS_KEY = "researchCoor_ethics";
  const DOCUMENTS_KEY = "researchCoor_documents";
  const PROJECTS_KEY = "researchCoor_projects";
  const PROPOSALS_KEY = "researchCoor_proposals";
  const DOCUMENT_DB = "researchCoor_documentFiles";
  const DOCUMENT_STORE = "files";
  const MAX_FILE_BYTES = 25 * 1024 * 1024;
  const ALLOWED_EXTENSIONS = new Set([
    "pdf", "doc", "docx", "xls", "xlsx", "ppt", "pptx", "txt", "csv",
    "jpg", "jpeg", "png",
  ]);
  const EVIDENCE_CATEGORIES = [
    "Research Ethics Documents",
    "Informed Consent Documents",
    "Plagiarism Check Documents",
    "Compliance Evidence",
  ];
  const REQUIREMENTS = [
    ["reqApplication", "Ethics application"],
    ["reqConsent", "Informed consent forms"],
    ["reqProtocol", "Research instruments / protocol"],
    ["reqPlagiarism", "Plagiarism report"],
    ["reqPrivacy", "Data privacy / security plan"],
    ["reqOther", "Other committee requirements"],
  ];

  let ethicsRecords = [];
  let ethicsDocuments = [];
  let linkedProjects = [];
  let linkedProposals = [];
  let storageReady = true;
  let handlersBound = false;
  let documentDatabasePromise = null;

  function getRoot() {
    return document.querySelector(".ethics-compliance-page");
  }

  function escapeHtml(value) {
    return String(value ?? "").replace(/[&<>"']/g, (character) => ({
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    })[character]);
  }

  function showNotice(message, type = "") {
    const notice = document.getElementById("ethicsNotice");
    if (!notice) return;
    notice.textContent = message;
    notice.classList.toggle("is-error", type === "error");
    notice.classList.toggle("is-success", type === "success");
    notice.setAttribute("role", type === "error" ? "alert" : "status");
  }

  function readList(key, label) {
    const stored = window.localStorage.getItem(key);
    if (stored === null) return [];
    const records = JSON.parse(stored);
    if (
      !Array.isArray(records) ||
      !records.every((record) =>
        record &&
        typeof record === "object" &&
        !Array.isArray(record) &&
        typeof record.id === "string",
      )
    ) {
      throw new Error(`${label} data must be a list of records.`);
    }
    return records;
  }

  function loadEthicsData() {
    storageReady = false;
    try {
      ethicsRecords = readList(ETHICS_KEY, "Ethics");
      ethicsDocuments = readList(DOCUMENTS_KEY, "Document");
      linkedProjects = readList(PROJECTS_KEY, "Research project");
      linkedProposals = readList(PROPOSALS_KEY, "Research proposal");
      storageReady = true;
      return true;
    } catch (error) {
      console.error("Unable to load ethics and compliance records:", error);
      showNotice(`Hindi ma-load ang ethics/compliance data: ${error.message}`, "error");
      return false;
    }
  }

  function saveList(key, records) {
    window.localStorage.setItem(key, JSON.stringify(records));
  }

  function projectFor(id) {
    return linkedProjects.find((project) => String(project.id) === String(id));
  }

  function proposalFor(id) {
    return linkedProposals.find((proposal) => String(proposal.id) === String(id));
  }

  function projectLabel(record) {
    const project = projectFor(record.projectId);
    return project
      ? `${project.registrationNo || "Project"} — ${project.title || "Untitled project"}`
      : record.projectTitle || "Project record unavailable";
  }

  function proposalLabel(record) {
    const proposal = proposalFor(record.proposalId);
    return proposal
      ? `${proposal.reference || "Proposal"} — ${proposal.title || "Untitled proposal"}`
      : record.proposalId
        ? record.proposalTitle || "Proposal record unavailable"
        : "";
  }

  function effectiveClearanceStatus(record) {
    if (
      record.status === "Approved" &&
      record.expiryDate &&
      record.expiryDate < new Date().toISOString().slice(0, 10)
    ) {
      return "Expired";
    }
    return record.status || "Draft";
  }

  function dateLabel(value) {
    if (!value) return "Not set";
    const date = /^\d{4}-\d{2}-\d{2}$/.test(String(value))
      ? new Date(Number(value.slice(0, 4)), Number(value.slice(5, 7)) - 1, Number(value.slice(8, 10)))
      : new Date(value);
    return Number.isNaN(date.getTime())
      ? String(value)
      : date.toLocaleDateString(undefined, { year: "numeric", month: "short", day: "numeric" });
  }

  function dateTimeLabel(value) {
    if (!value) return "Not set";
    const date = new Date(value);
    return Number.isNaN(date.getTime())
      ? String(value)
      : date.toLocaleString(undefined, { year: "numeric", month: "short", day: "numeric", hour: "numeric", minute: "2-digit" });
  }

  function setSelectOptions(select, placeholder, records, formatRecord, selectedValue = "") {
    if (!select) return;
    select.innerHTML = `<option value="">${escapeHtml(placeholder)}</option>` + records
      .map((record) => `<option value="${escapeHtml(record.id)}">${escapeHtml(formatRecord(record))}</option>`)
      .join("");
    if (records.some((record) => String(record.id) === String(selectedValue))) {
      select.value = String(selectedValue);
    }
  }

  function refreshLinkedOptions() {
    const projectSelect = document.getElementById("ethicsProjectSelect");
    const proposalSelect = document.getElementById("ethicsProposalSelect");
    if (!projectSelect || !proposalSelect) return;
    const selectedProjectId = projectSelect.value;
    const selectedProposalId = proposalSelect.value;
    const activeProjects = linkedProjects.filter((project) => project.archived !== true);
    setSelectOptions(
      projectSelect,
      activeProjects.length ? "Select a research project" : "No active research projects found",
      activeProjects,
      (project) => `${project.registrationNo || "Project"} — ${project.title || "Untitled project"}`,
      selectedProjectId,
    );
    setSelectOptions(
      proposalSelect,
      "No proposal linked",
      linkedProposals.filter((proposal) => proposal.archived !== true),
      (proposal) => `${proposal.reference || "Proposal"} — ${proposal.title || "Untitled proposal"}`,
      selectedProposalId,
    );
  }

  function requirementCounts(record) {
    return REQUIREMENTS.reduce((counts, [key]) => {
      const status = record.requirements?.[key] || "Pending";
      if (status === "Verified" || status === "Not Applicable") counts.complete++;
      else counts.pending++;
      return counts;
    }, { complete: 0, pending: 0 });
  }

  function needsAction(record) {
    return effectiveClearanceStatus(record) === "Expired" ||
      record.policyStatus === "Non-compliant" ||
      record.consentStatus === "Pending" ||
      record.consentStatus === "Required" ||
      record.plagiarismStatus === "Action Required" ||
      record.status === "Revision Required" ||
      record.status === "Rejected" ||
      record.status === "Expired" ||
      requirementCounts(record).pending > 0;
  }

  function renderStats() {
    const active = ethicsRecords.filter((record) => record.archived !== true);
    document.getElementById("ethicsCountTotal").textContent = active.length;
    document.getElementById("ethicsCountReview").textContent =
      active.filter((record) => ["Submitted", "Under Review", "Revision Required"].includes(record.status)).length;
    document.getElementById("ethicsCountApproved").textContent =
      active.filter((record) => effectiveClearanceStatus(record) === "Approved").length;
    document.getElementById("ethicsCountAction").textContent =
      active.filter(needsAction).length;
    document.getElementById("ethicsCountArchived").textContent =
      ethicsRecords.filter((record) => record.archived === true).length;
  }

  function statusBadge(value) {
    return `<span class="ethics-status" data-status="${escapeHtml(value || "Pending")}">${escapeHtml(value || "Pending")}</span>`;
  }

  function recordActions(record) {
    return `<div class="ethics-actions">
      <button type="button" data-ethics-action="edit" data-record-id="${escapeHtml(record.id)}">Edit</button>
      <button type="button" data-ethics-action="archive" data-record-id="${escapeHtml(record.id)}">Archive</button>
    </div>`;
  }

  function renderRecords() {
    const body = document.getElementById("ethicsRecordsBody");
    const query = document.getElementById("ethicsSearch").value.trim().toLowerCase();
    const selectedStatus = document.getElementById("ethicsStatusFilter").value;
    const selectedCompliance = document.getElementById("ethicsComplianceFilter").value;
    const records = ethicsRecords.filter((record) => {
      if (record.archived === true) return false;
      const searchable = [
        projectLabel(record),
        proposalLabel(record),
        record.applicationNo,
        record.committee,
      ].join(" ").toLowerCase();
      const currentStatus = effectiveClearanceStatus(record);
      return searchable.includes(query) &&
        (!selectedStatus || currentStatus === selectedStatus) &&
        (!selectedCompliance || record.policyStatus === selectedCompliance);
    });

    body.innerHTML = records.length
      ? records.map((record) => {
        const requirements = requirementCounts(record);
        const consent = record.consentStatus || "Not Required";
        const plagiarism = record.plagiarismStatus || "Pending";
        return `<tr>
          <td data-label="Project / Application"><span class="ethics-project-title">${escapeHtml(projectLabel(record))}</span><small>${escapeHtml(record.applicationNo || "No application number")}${proposalLabel(record) ? ` · ${escapeHtml(proposalLabel(record))}` : ""}</small></td>
          <td data-label="Clearance status">${statusBadge(effectiveClearanceStatus(record))}</td>
          <td data-label="Consent">${statusBadge(consent)}</td>
          <td data-label="Plagiarism">${statusBadge(plagiarism)}${record.similarityScore !== "" && record.similarityScore !== null && record.similarityScore !== undefined ? `<small>${escapeHtml(record.similarityScore)}% similarity</small>` : ""}</td>
          <td data-label="Policy compliance">${statusBadge(record.policyStatus || "For Review")}<small>${requirements.complete}/${REQUIREMENTS.length} requirements verified</small></td>
          <td data-label="Expiry">${escapeHtml(dateLabel(record.expiryDate))}</td>
          <td data-label="Actions">${recordActions(record)}</td>
        </tr>`;
      }).join("")
      : '<tr><td colspan="7" class="ethics-empty">No records match these filters. Register an ethics clearance to begin tracking.</td></tr>';
  }

  function isEthicsDocument(record) {
    return EVIDENCE_CATEGORIES.includes(record.category);
  }

  function documentSize(value) {
    const bytes = Number(value) || 0;
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(1)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
  }

  function renderEvidence() {
    const activeBody = document.getElementById("ethicsEvidenceBody");
    const archiveBody = document.getElementById("ethicsArchiveEvidenceBody");
    const relevant = ethicsDocuments.filter(isEthicsDocument);
    const active = relevant.filter((record) => record.archived !== true);
    const archived = relevant.filter((record) => record.archived === true);
    activeBody.innerHTML = active.length
      ? active.map((record) => `<tr>
        <td data-label="Document"><strong>${escapeHtml(record.title || record.fileName)}</strong><small>${escapeHtml(record.fileName || "")}</small></td>
        <td data-label="Category">${escapeHtml(record.category)}</td>
        <td data-label="Linked project">${escapeHtml(projectLabel(record))}</td>
        <td data-label="Uploaded">${escapeHtml(dateLabel(record.uploadedAt || record.documentDate))}</td>
        <td data-label="File size">${escapeHtml(documentSize(record.fileSize))}</td>
        <td data-label="Actions"><div class="ethics-actions">
          <button type="button" data-ethics-document-action="download" data-document-id="${escapeHtml(record.id)}">Download</button>
          <button type="button" data-ethics-document-action="archive" data-document-id="${escapeHtml(record.id)}">Archive</button>
        </div></td>
      </tr>`).join("")
      : '<tr><td colspan="6" class="ethics-empty">No active ethics or compliance evidence uploaded. Add a file while registering or updating a clearance.</td></tr>';
    archiveBody.innerHTML = archived.length
      ? archived.map((record) => `<tr>
        <td data-label="Document"><strong>${escapeHtml(record.title || record.fileName)}</strong><small>${escapeHtml(record.fileName || "")}</small></td>
        <td data-label="Category">${escapeHtml(record.category)}</td>
        <td data-label="Linked project">${escapeHtml(projectLabel(record))}</td>
        <td data-label="Archived">${escapeHtml(dateLabel(record.archivedAt))}</td>
        <td data-label="Actions"><div class="ethics-actions"><button type="button" data-ethics-document-action="restore" data-document-id="${escapeHtml(record.id)}">Restore</button></div></td>
      </tr>`).join("")
      : '<tr><td colspan="5" class="ethics-empty">No archived ethics documents.</td></tr>';
  }

  function renderArchive() {
    const body = document.getElementById("ethicsArchiveBody");
    const historyBody = document.getElementById("ethicsHistoryBody");
    const archived = ethicsRecords.filter((record) => record.archived === true);
    body.innerHTML = archived.length
      ? archived.map((record) => `<tr>
        <td data-label="Project / Application"><span class="ethics-project-title">${escapeHtml(projectLabel(record))}</span><small>${escapeHtml(record.applicationNo || "No application number")}</small></td>
        <td data-label="Clearance status">${statusBadge(effectiveClearanceStatus(record))}</td>
        <td data-label="Policy compliance">${statusBadge(record.policyStatus || "For Review")}</td>
        <td data-label="Archived">${escapeHtml(dateLabel(record.archivedAt))}</td>
        <td data-label="History">${escapeHtml(record.history?.length || 0)} updates<small>Last update: ${escapeHtml(dateTimeLabel(record.updatedAt))}</small></td>
        <td data-label="Actions"><div class="ethics-actions"><button type="button" data-ethics-action="restore" data-record-id="${escapeHtml(record.id)}">Restore</button></div></td>
      </tr>`).join("")
      : '<tr><td colspan="6" class="ethics-empty">No archived ethics clearance records.</td></tr>';
    const history = ethicsRecords.flatMap((record) =>
      (record.history || []).map((entry) => ({ record, entry })),
    ).sort((left, right) =>
      String(right.entry.date || "").localeCompare(String(left.entry.date || "")),
    ).slice(0, 100);
    historyBody.innerHTML = history.length
      ? history.map(({ record, entry }) => `<tr>
        <td data-label="Project / Application">${escapeHtml(projectLabel(record))}<small>${escapeHtml(record.applicationNo || "No application number")}</small></td>
        <td data-label="Action">${escapeHtml(entry.action || "Record updated")}<small>Policy: ${escapeHtml(entry.policyStatus || "Not recorded")} · Consent: ${escapeHtml(entry.consentStatus || "Not recorded")} · Plagiarism: ${escapeHtml(entry.plagiarismStatus || "Not recorded")}</small></td>
        <td data-label="Clearance status">${statusBadge(entry.status || effectiveClearanceStatus(record))}</td>
        <td data-label="Date and time">${escapeHtml(dateTimeLabel(entry.date))}</td>
      </tr>`).join("")
      : '<tr><td colspan="4" class="ethics-empty">No compliance history recorded yet.</td></tr>';
  }

  function renderPage() {
    renderStats();
    renderRecords();
    renderEvidence();
    renderArchive();
  }

  function switchTab(name) {
    document.querySelectorAll(".ethics-tab").forEach((tab) => {
      const active = tab.dataset.ethicsTab === name;
      tab.classList.toggle("active", active);
      tab.setAttribute("aria-selected", String(active));
    });
    document.querySelectorAll(".ethics-panel").forEach((panel) => {
      const active = panel.id === `ethics-panel-${name}`;
      panel.classList.toggle("active", active);
      panel.hidden = !active;
    });
  }

  function historyEntry(action, status, details = {}) {
    return { action, status, date: new Date().toISOString(), ...details };
  }

  function resetForm() {
    const form = document.getElementById("ethicsForm");
    if (!form) return;
    form.reset();
    document.getElementById("ethicsRecordId").value = "";
    document.getElementById("ethicsFormHeading").textContent = "Register ethics clearance";
    document.getElementById("ethicsSaveButton").innerHTML = '<i class="fas fa-save" aria-hidden="true"></i> Save clearance record';
    document.querySelectorAll("[data-ethics-cancel]").forEach((button) => { button.hidden = true; });
    refreshLinkedOptions();
  }

  function editRecord(id) {
    const record = ethicsRecords.find((item) => item.id === id);
    if (!record || record.archived) return;
    const form = document.getElementById("ethicsForm");
    const values = {
      recordId: record.id,
      projectId: record.projectId,
      proposalId: record.proposalId || "",
      applicationNo: record.applicationNo || "",
      committee: record.committee || "",
      status: record.status || "Draft",
      submittedDate: record.submittedDate || "",
      decisionDate: record.decisionDate || "",
      expiryDate: record.expiryDate || "",
      consentStatus: record.consentStatus || "Not Required",
      plagiarismStatus: record.plagiarismStatus || "Pending",
      similarityScore: record.similarityScore ?? "",
      policyStatus: record.policyStatus || "For Review",
      policyReviewedDate: record.policyReviewedDate || "",
      notes: record.notes || "",
    };
    refreshLinkedOptions();
    Object.entries(values).forEach(([name, value]) => {
      const field = form.elements.namedItem(name);
      if (field) field.value = value;
    });
    REQUIREMENTS.forEach(([key]) => {
      const field = form.elements.namedItem(key);
      if (field) field.value = record.requirements?.[key] || "Pending";
    });
    document.getElementById("ethicsFormHeading").textContent = "Update ethics clearance";
    document.getElementById("ethicsSaveButton").innerHTML = '<i class="fas fa-save" aria-hidden="true"></i> Save updates';
    document.querySelectorAll("[data-ethics-cancel]").forEach((button) => { button.hidden = false; });
    switchTab("register");
    document.getElementById("ethicsProjectSelect").focus();
  }

  function openDocumentDatabase() {
    if (!("indexedDB" in window)) {
      return Promise.reject(new Error("This browser does not support secure file storage."));
    }
    if (documentDatabasePromise) return documentDatabasePromise;
    documentDatabasePromise = new Promise((resolve, reject) => {
      const request = window.indexedDB.open(DOCUMENT_DB, 1);
      request.onupgradeneeded = () => {
        const database = request.result;
        if (!database.objectStoreNames.contains(DOCUMENT_STORE)) {
          database.createObjectStore(DOCUMENT_STORE, { keyPath: "id" });
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

  function writeFile(id, file) {
    return openDocumentDatabase().then((database) => new Promise((resolve, reject) => {
      const transaction = database.transaction(DOCUMENT_STORE, "readwrite");
      transaction.objectStore(DOCUMENT_STORE).put({
        id,
        blob: file,
        name: file.name,
        type: file.type || "application/octet-stream",
        size: file.size,
        lastModified: file.lastModified,
      });
      transaction.oncomplete = resolve;
      transaction.onerror = () => reject(transaction.error || new Error("Could not store the uploaded file."));
      transaction.onabort = () => reject(transaction.error || new Error("File storage was cancelled."));
    }));
  }

  function readFile(id) {
    return openDocumentDatabase().then((database) => new Promise((resolve, reject) => {
      const request = database.transaction(DOCUMENT_STORE, "readonly").objectStore(DOCUMENT_STORE).get(id);
      request.onsuccess = () => resolve(request.result || null);
      request.onerror = () => reject(request.error || new Error("Could not retrieve the document file."));
    }));
  }

  function deleteFile(id) {
    return openDocumentDatabase().then((database) => new Promise((resolve, reject) => {
      const transaction = database.transaction(DOCUMENT_STORE, "readwrite");
      transaction.objectStore(DOCUMENT_STORE).delete(id);
      transaction.oncomplete = resolve;
      transaction.onerror = () => reject(transaction.error || new Error("Could not remove the uploaded file."));
    }));
  }

  function validateEvidence(file, title) {
    if (!file) return "";
    const extension = file.name.split(".").pop().toLowerCase();
    if (!ALLOWED_EXTENSIONS.has(extension)) return "Unsupported evidence file type.";
    if (file.size === 0) return "The selected evidence file is empty.";
    if (file.size > MAX_FILE_BYTES) return "Evidence files must be 25 MB or smaller.";
    if (!title.trim()) return "Enter an evidence title when attaching a file.";
    return "";
  }

  function validateDates(data) {
    const submitted = String(data.get("submittedDate") || "");
    const decision = String(data.get("decisionDate") || "");
    const expiry = String(data.get("expiryDate") || "");
    if (submitted && decision && decision < submitted) {
      return "The decision date must be on or after the application submission date.";
    }
    if (decision && expiry && expiry < decision) {
      return "The clearance expiry date must be on or after the decision date.";
    }
    return "";
  }

  function updateDocumentsForClearance(recordId, archived) {
    return ethicsDocuments.map((documentRecord) =>
      documentRecord.ethicsRecordId === recordId
        ? { ...documentRecord, archived, archivedAt: archived ? new Date().toISOString() : "" }
        : documentRecord,
    );
  }

  function persistClearanceAndDocuments(nextRecords, nextDocuments = ethicsDocuments) {
    const originalRecords = ethicsRecords;
    const originalDocuments = ethicsDocuments;
    try {
      saveList(ETHICS_KEY, nextRecords);
      if (nextDocuments !== ethicsDocuments) saveList(DOCUMENTS_KEY, nextDocuments);
      ethicsRecords = nextRecords;
      ethicsDocuments = nextDocuments;
      window.dispatchEvent(new CustomEvent("research-ethics-updated"));
      if (nextDocuments !== originalDocuments) {
        window.dispatchEvent(new CustomEvent("research-documents-updated"));
      }
      renderPage();
      return true;
    } catch (error) {
      console.error("Unable to save ethics and compliance records:", error);
      try {
        saveList(ETHICS_KEY, originalRecords);
        saveList(DOCUMENTS_KEY, originalDocuments);
      } catch (rollbackError) {
        console.error("Unable to restore ethics records after a save failure:", rollbackError);
      }
      showNotice("Hindi nai-save ang pagbabago. Suriin ang browser storage at subukang muli.", "error");
      return false;
    }
  }

  async function saveRecord(form) {
    if (!storageReady) {
      showNotice("Hindi maaaring mag-save habang may storage error.", "error");
      return;
    }
    const data = new FormData(form);
    const recordId = String(data.get("recordId") || "");
    const existing = recordId ? ethicsRecords.find((record) => record.id === recordId) : null;
    const projectId = String(data.get("projectId") || "");
    const project = projectFor(projectId);
    if (!project || project.archived === true) {
      showNotice("Pumili ng aktibong research project bago mag-save.", "error");
      document.getElementById("ethicsProjectSelect").focus();
      return;
    }
    const file = document.getElementById("ethicsEvidenceFile").files[0] || null;
    const evidenceTitle = String(data.get("evidenceTitle") || "").trim();
    const dateError = validateDates(data);
    if (dateError) {
      showNotice(dateError, "error");
      return;
    }
    const evidenceError = validateEvidence(file, evidenceTitle);
    if (evidenceError) {
      showNotice(evidenceError, "error");
      return;
    }

    const status = String(data.get("status") || "Draft");
    const requirements = Object.fromEntries(REQUIREMENTS.map(([key]) => [key, String(data.get(key) || "Pending")]));
    const now = new Date().toISOString();
    const id = existing?.id || `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
    const nextRecord = {
      ...(existing || {}),
      id,
      projectId,
      projectTitle: project.title || "",
      proposalId: String(data.get("proposalId") || ""),
      proposalTitle: proposalFor(String(data.get("proposalId") || ""))?.title || "",
      applicationNo: String(data.get("applicationNo") || "").trim(),
      committee: String(data.get("committee") || "").trim(),
      status,
      submittedDate: String(data.get("submittedDate") || ""),
      decisionDate: String(data.get("decisionDate") || ""),
      expiryDate: String(data.get("expiryDate") || ""),
      consentStatus: String(data.get("consentStatus") || "Not Required"),
      plagiarismStatus: String(data.get("plagiarismStatus") || "Pending"),
      similarityScore: data.get("similarityScore") === "" ? "" : Number(data.get("similarityScore")),
      policyStatus: String(data.get("policyStatus") || "For Review"),
      policyReviewedDate: String(data.get("policyReviewedDate") || ""),
      requirements,
      notes: String(data.get("notes") || "").trim(),
      archived: false,
      createdAt: existing?.createdAt || now,
      updatedAt: now,
      archivedAt: "",
      history: [
        ...(existing?.history || []),
        historyEntry(
          existing ? "Ethics and compliance record updated" : "Ethics clearance registered",
          status,
          {
            policyStatus: String(data.get("policyStatus") || "For Review"),
            consentStatus: String(data.get("consentStatus") || "Not Required"),
            plagiarismStatus: String(data.get("plagiarismStatus") || "Pending"),
            requirements,
          },
        ),
      ],
    };
    const nextRecords = existing
      ? ethicsRecords.map((record) => record.id === id ? nextRecord : record)
      : [nextRecord, ...ethicsRecords];
    let nextDocuments = ethicsDocuments;
    let evidenceId = "";
    if (file) {
      evidenceId = `${id}-evidence-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
      const documentRecord = {
        id: evidenceId,
        title: evidenceTitle,
        category: String(data.get("evidenceCategory") || EVIDENCE_CATEGORIES[0]),
        version: "",
        proposalId: nextRecord.proposalId,
        projectId,
        ethicsRecordId: id,
        documentDate: new Date().toISOString().slice(0, 10),
        uploadedAt: now,
        uploadedBy: "Research Coordinator",
        description: `Evidence for ethics application ${nextRecord.applicationNo || "without application number"}.`,
        fileName: file.name,
        fileSize: file.size,
        fileType: file.type || "application/octet-stream",
        archived: false,
        archivedAt: "",
        fileStorageId: evidenceId,
        history: [{ action: "Uploaded from Ethics and Compliance", date: now }],
      };
      nextDocuments = [documentRecord, ...ethicsDocuments];
      try {
        await writeFile(evidenceId, file);
      } catch (error) {
        console.error("Unable to store ethics evidence file:", error);
        showNotice(`Hindi nai-save ang evidence file: ${error.message}`, "error");
        return;
      }
    }

    if (!persistClearanceAndDocuments(nextRecords, nextDocuments)) {
      if (evidenceId) {
        try {
          await deleteFile(evidenceId);
        } catch (error) {
          console.error("Unable to clean up evidence after save failure:", error);
        }
      }
      return;
    }
    resetForm();
    switchTab("records");
    showNotice(file
      ? "Nai-save ang ethics record at naidagdag ang evidence sa shared Document Library."
      : "Nai-save ang ethics and compliance record.", "success");
  }

  function archiveRecord(id, archived) {
    const record = ethicsRecords.find((item) => item.id === id);
    if (!record || record.archived === archived) return;
    const prompt = archived
      ? `I-archive ang ethics record para sa "${projectLabel(record)}"?`
      : `I-restore ang ethics record para sa "${projectLabel(record)}"?`;
    if (!window.confirm(prompt)) return;
    const now = new Date().toISOString();
    const updated = {
      ...record,
      archived,
      archivedAt: archived ? now : "",
      updatedAt: now,
      history: [
        ...(record.history || []),
        historyEntry(archived ? "Ethics record archived" : "Ethics record restored", record.status),
      ],
    };
    const nextRecords = ethicsRecords.map((item) => item.id === id ? updated : item);
    const nextDocuments = updateDocumentsForClearance(id, archived);
    if (persistClearanceAndDocuments(nextRecords, nextDocuments)) {
      showNotice(archived ? "Na-archive ang ethics record at kaugnay na evidence." : "Na-restore ang ethics record at kaugnay na evidence.", "success");
    }
  }

  function archiveDocument(id, archived) {
    const record = ethicsDocuments.find((item) => item.id === id);
    if (!record || record.archived === archived) return;
    if (!window.confirm(`${archived ? "I-archive" : "I-restore"} ang "${record.title || record.fileName}"?`)) return;
    const updated = {
      ...record,
      archived,
      archivedAt: archived ? new Date().toISOString() : "",
      history: [
        ...(record.history || []),
        { action: archived ? "Ethics document archived" : "Ethics document restored", date: new Date().toISOString() },
      ],
    };
    const nextDocuments = ethicsDocuments.map((item) => item.id === id ? updated : item);
    try {
      saveList(DOCUMENTS_KEY, nextDocuments);
      ethicsDocuments = nextDocuments;
      window.dispatchEvent(new CustomEvent("research-documents-updated"));
      renderPage();
      showNotice(archived ? "Na-archive ang evidence; nasa Archive tab pa rin ito." : "Na-restore ang evidence sa Ethics Documents at shared Document Library.", "success");
    } catch (error) {
      console.error("Unable to update ethics document archive:", error);
      showNotice("Hindi na-update ang document archive. Suriin ang browser storage.", "error");
    }
  }

  async function downloadDocument(id) {
    const record = ethicsDocuments.find((item) => item.id === id);
    if (!record) return;
    try {
      const stored = await readFile(record.fileStorageId || id);
      if (!stored?.blob) {
        showNotice("Hindi makita ang file data sa browser storage.", "error");
        return;
      }
      const url = URL.createObjectURL(stored.blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = stored.name || record.fileName || "ethics-document";
      document.body.appendChild(link);
      link.click();
      link.remove();
      window.setTimeout(() => URL.revokeObjectURL(url), 60000);
      showNotice(`Downloading ${record.fileName}.`, "success");
    } catch (error) {
      console.error("Unable to download ethics document:", error);
      showNotice(`Hindi ma-download ang file: ${error.message}`, "error");
    }
  }

  function refreshConnectedProjects() {
    try {
      linkedProjects = readList(PROJECTS_KEY, "Research project");
      linkedProposals = readList(PROPOSALS_KEY, "Research proposal");
      refreshLinkedOptions();
      renderPage();
    } catch (error) {
      console.error("Unable to refresh linked research records:", error);
      showNotice(`Hindi ma-refresh ang project/proposal links: ${error.message}`, "error");
    }
  }

  function initialize() {
    const root = getRoot();
    if (!root || root.dataset.initialized === "true") return;
    root.dataset.initialized = "true";
    if (!loadEthicsData()) return;
    refreshLinkedOptions();
    renderPage();

    if (handlersBound) return;
    handlersBound = true;

    document.addEventListener("click", (event) => {
      if (!(event.target instanceof Element) || !getRoot()) return;
      const tab = event.target.closest("[data-ethics-tab]");
      if (tab && getRoot().contains(tab)) {
        switchTab(tab.dataset.ethicsTab);
        return;
      }
      if (event.target.closest("[data-ethics-new]")?.closest(".ethics-compliance-page")) {
        resetForm();
        switchTab("register");
        document.getElementById("ethicsProjectSelect").focus();
        return;
      }
      if (event.target.closest("[data-ethics-cancel]")?.closest(".ethics-compliance-page")) {
        resetForm();
        switchTab("records");
        return;
      }
      const action = event.target.closest("[data-ethics-action]");
      if (action && getRoot().contains(action)) {
        if (action.dataset.ethicsAction === "edit") editRecord(action.dataset.recordId);
        if (action.dataset.ethicsAction === "archive") archiveRecord(action.dataset.recordId, true);
        if (action.dataset.ethicsAction === "restore") archiveRecord(action.dataset.recordId, false);
        return;
      }
      const documentAction = event.target.closest("[data-ethics-document-action]");
      if (documentAction && getRoot().contains(documentAction)) {
        if (documentAction.dataset.ethicsDocumentAction === "download") downloadDocument(documentAction.dataset.documentId);
        if (documentAction.dataset.ethicsDocumentAction === "archive") archiveDocument(documentAction.dataset.documentId, true);
        if (documentAction.dataset.ethicsDocumentAction === "restore") archiveDocument(documentAction.dataset.documentId, false);
      }
    });

    document.addEventListener("input", (event) => {
      if (!getRoot() || !(event.target instanceof Element)) return;
      if (event.target.id === "ethicsSearch") renderRecords();
    });
    document.addEventListener("change", (event) => {
      if (!getRoot() || !(event.target instanceof Element)) return;
      if (["ethicsStatusFilter", "ethicsComplianceFilter"].includes(event.target.id)) renderRecords();
    });
    document.addEventListener("submit", (event) => {
      const rootElement = getRoot();
      if (!rootElement || !(event.target instanceof HTMLFormElement) ||
          event.target.id !== "ethicsForm" || !rootElement.contains(event.target)) return;
      event.preventDefault();
      saveRecord(event.target);
    });
    window.addEventListener("storage", (event) => {
      if (!getRoot()) return;
      if (event.key === ETHICS_KEY || event.key === DOCUMENTS_KEY) {
        if (loadEthicsData()) renderPage();
      }
      if (event.key === PROJECTS_KEY || event.key === PROPOSALS_KEY) refreshConnectedProjects();
    });
    window.addEventListener("research-projects-updated", refreshConnectedProjects);
    window.addEventListener("research-proposals-updated", refreshConnectedProjects);
    window.addEventListener("research-documents-updated", () => {
      if (getRoot() && loadEthicsData()) renderPage();
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initialize, { once: true });
  } else {
    initialize();
  }
  window.addEventListener("page:loaded", initialize);
})();
