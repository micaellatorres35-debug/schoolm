const RESEARCH_PROPOSALS_KEY = "researchCoor_proposals";
const PROPOSAL_STATUSES = [
  "Draft",
  "Submitted",
  "Under Review",
  "Revision Requested",
  "Resubmitted",
  "Approved",
  "Rejected",
];

let researchProposals = [];
let proposalStorageReady = true;
let proposalHandlersBound = false;

function proposalEscape(value) {
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

function getProposalRoot() {
  return document.querySelector(".research-proposal-page");
}

function showProposalNotice(message, type = "") {
  const notice = document.getElementById("proposalNotice");
  if (!notice) return;
  notice.textContent = message;
  notice.classList.toggle("is-error", type === "error");
  notice.classList.toggle("is-success", type === "success");
  notice.setAttribute("role", type === "error" ? "alert" : "status");
}

function loadResearchProposals() {
  proposalStorageReady = true;
  try {
    const stored = window.localStorage.getItem(RESEARCH_PROPOSALS_KEY);
    if (stored === null) {
      researchProposals = [];
      return true;
    }
    const parsed = JSON.parse(stored);
    if (
      !Array.isArray(parsed) ||
      !parsed.every(
        (proposal) =>
          proposal &&
          typeof proposal === "object" &&
          !Array.isArray(proposal) &&
          typeof proposal.id === "string",
      )
    ) {
      throw new Error("Stored research proposal data must be a list of records.");
    }
    researchProposals = parsed.map((proposal) => ({
      ...proposal,
      abstract: String(proposal.abstract || ""),
      objectives: String(proposal.objectives || ""),
      methodology: String(proposal.methodology || ""),
      researchers: Array.isArray(proposal.researchers)
        ? proposal.researchers
        : String(proposal.researchers || "")
            .split(/\r?\n/)
            .map((name) => name.trim())
            .filter(Boolean),
      history: Array.isArray(proposal.history) ? proposal.history : [],
      assessments: Array.isArray(proposal.assessments)
        ? proposal.assessments
        : [],
    }));
    return true;
  } catch (error) {
    proposalStorageReady = false;
    console.error("Unable to load research proposals from browser storage:", error);
    showProposalNotice(
      "Hindi mabasa ang proposal records. Walang binagong data; ayusin muna ang browser storage bago mag-save.",
      "error",
    );
    return false;
  }
}

function persistResearchProposals(nextProposals) {
  if (!proposalStorageReady) {
    showProposalNotice("Hindi nai-save ang proposal dahil may storage error.", "error");
    return false;
  }
  try {
    window.localStorage.setItem(
      RESEARCH_PROPOSALS_KEY,
      JSON.stringify(nextProposals),
    );
    researchProposals = nextProposals;
    showProposalNotice("Proposal records saved in this browser.", "success");
    window.dispatchEvent(new CustomEvent("research-proposals-updated"));
    return true;
  } catch (error) {
    console.error("Unable to save research proposals in browser storage:", error);
    showProposalNotice(
      "Hindi nai-save ang proposal. Suriin ang browser storage at subukang muli.",
      "error",
    );
    return false;
  }
}

function proposalDateLabel(value) {
  if (!value) return "Not set";
  const date = new Date(`${value}T00:00:00`);
  return Number.isNaN(date.getTime())
    ? String(value)
    : date.toLocaleDateString(undefined, {
        year: "numeric",
        month: "short",
        day: "numeric",
      });
}

function proposalStatusClass(status) {
  return `proposal-status-${String(status || "Draft")
    .toLowerCase()
    .replace(/\s+/g, "-")}`;
}

function safeProposalUrl(value) {
  try {
    const url = new URL(String(value || ""));
    return ["http:", "https:"].includes(url.protocol) ? url.href : "";
  } catch {
    return "";
  }
}

function findProposal(proposalId) {
  return researchProposals.find((proposal) => proposal.id === proposalId);
}

function formatProposalReference(sequence) {
  return `RSP-${new Date().getFullYear()}-${String(sequence).padStart(4, "0")}`;
}

function nextProposalReference() {
  const prefix = `RSP-${new Date().getFullYear()}-`;
  const lastSequence = researchProposals.reduce((highest, proposal) => {
    const reference = String(proposal.reference || "");
    if (!reference.startsWith(prefix)) return highest;
    const sequence = Number(reference.slice(prefix.length));
    return Number.isFinite(sequence) ? Math.max(highest, sequence) : highest;
  }, 0);
  return formatProposalReference(lastSequence + 1);
}

function createProposalHistoryEntry(action, status, details = {}) {
  return {
    id: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    action,
    status,
    date: new Date().toISOString(),
    ...details,
  };
}

function renderProposalStats() {
  const active = researchProposals;
  document.getElementById("proposalCountTotal").textContent = active.length;
  document.getElementById("proposalCountReview").textContent = active.filter(
    (proposal) =>
      ["Submitted", "Under Review", "Resubmitted"].includes(proposal.status),
  ).length;
  document.getElementById("proposalCountRevision").textContent =
    active.filter((proposal) => proposal.status === "Revision Requested").length;
  document.getElementById("proposalCountApproved").textContent =
    active.filter((proposal) => proposal.status === "Approved").length;
  document.getElementById("proposalCountRejected").textContent =
    active.filter((proposal) => proposal.status === "Rejected").length;
}

function renderProposalRecords() {
  const tbody = document.getElementById("proposalRecordsBody");
  const query = document.getElementById("proposalSearch").value.trim().toLowerCase();
  const status = document.getElementById("proposalStatusFilter").value;
  const category = document.getElementById("proposalCategoryFilter").value;
  const filtered = researchProposals.filter((proposal) => {
    const searchable = [
      proposal.reference,
      proposal.title,
      proposal.leader,
      proposal.email,
      ...(proposal.researchers || []),
    ]
      .join(" ")
      .toLowerCase();
    return (
      searchable.includes(query) &&
      (!status || proposal.status === status) &&
      (!category || proposal.category === category)
    );
  });
  if (!filtered.length) {
    tbody.innerHTML = `<tr><td colspan="7" class="proposal-empty">${
      researchProposals.length
        ? "No proposals match the selected filters."
        : "No proposals registered yet."
    }</td></tr>`;
    return;
  }
  tbody.innerHTML = filtered
    .map(
      (proposal) => `
        <tr>
          <td>${proposalEscape(proposal.reference)}</td>
          <td class="proposal-title-cell">${proposalEscape(proposal.title)}<small>${proposalEscape(proposal.classification || "Unclassified")}</small></td>
          <td>${proposalEscape(proposal.leader)}</td>
          <td>${proposalEscape(proposal.category)}</td>
          <td>${proposalEscape(proposalDateLabel(proposal.lastSubmittedAt || proposal.submittedAt))}</td>
          <td><span class="proposal-status ${proposalStatusClass(proposal.status)}">${proposalEscape(proposal.status)}</span></td>
          <td><div class="proposal-actions">
            <button type="button" data-proposal-action="details" data-proposal-id="${proposalEscape(proposal.id)}">Details</button>
            ${["Approved", "Rejected"].includes(proposal.status) ? "" : `<button type="button" data-proposal-action="edit" data-proposal-id="${proposalEscape(proposal.id)}">Edit</button><button type="button" data-proposal-action="review" data-proposal-id="${proposalEscape(proposal.id)}">Review</button>`}
            <button type="button" data-proposal-action="history" data-proposal-id="${proposalEscape(proposal.id)}">History</button>
          </div></td>
        </tr>`,
    )
    .join("");
}

function renderProposalSelectors(selectedReviewId = "", selectedHistoryId = "") {
  const reviewSelect = document.getElementById("proposalReviewSelect");
  const historySelect = document.getElementById("proposalHistorySelect");
  const options = researchProposals
    .map(
      (proposal) =>
        `<option value="${proposalEscape(proposal.id)}">${proposalEscape(proposal.reference)} — ${proposalEscape(proposal.title)}</option>`,
    )
    .join("");
  reviewSelect.innerHTML =
    '<option value="">Select a proposal for review</option>' + options;
  historySelect.innerHTML =
    '<option value="">Select a proposal</option>' + options;
  if (findProposal(selectedReviewId)) reviewSelect.value = selectedReviewId;
  if (findProposal(selectedHistoryId)) historySelect.value = selectedHistoryId;
  renderProposalReviewSummary(reviewSelect.value);
  renderProposalHistory(historySelect.value);
}

function renderProposalReviewSummary(proposalId) {
  const summary = document.getElementById("proposalReviewSummary");
  const form = document.getElementById("proposalReviewForm");
  const proposal = findProposal(proposalId);
  if (!proposal) {
    summary.innerHTML = '<p class="proposal-empty">Select a proposal to review.</p>';
    form.reset();
    form.elements.namedItem("proposalId").value = "";
    return;
  }
  summary.innerHTML = `
    <h3>${proposalEscape(proposal.title)}</h3>
    <p>${proposalEscape(proposal.reference)} · <span class="proposal-status ${proposalStatusClass(proposal.status)}">${proposalEscape(proposal.status)}</span></p>
    <dl>
      <div><dt>Proponent</dt><dd>${proposalEscape(proposal.leader)}</dd></div>
      <div><dt>Category</dt><dd>${proposalEscape(proposal.category)} / ${proposalEscape(proposal.classification || "Unclassified")}</dd></div>
      <div><dt>Submitted</dt><dd>${proposalEscape(proposalDateLabel(proposal.submittedAt))}</dd></div>
      <div><dt>Budget</dt><dd>${proposal.budget ? proposalEscape(Number(proposal.budget).toLocaleString()) : "Not specified"}</dd></div>
      <div class="full"><dt>Abstract</dt><dd>${proposalEscape(proposal.abstract)}</dd></div>
      ${proposal.revisionInstructions ? `<div class="full"><dt>Revision Instructions</dt><dd>${proposalEscape(proposal.revisionInstructions)}</dd></div>` : ""}
    </dl>`;
  form.reset();
  form.elements.namedItem("proposalId").value = proposal.id;
  const latest = proposal.assessments[proposal.assessments.length - 1];
  if (latest) {
    ["relevance", "methodologyScore", "feasibility", "reviewer", "comments", "revisionInstructions", "assessmentLink"].forEach((name) => {
      const field = form.elements.namedItem(name);
      if (field && latest[name] !== undefined) field.value = latest[name];
    });
  }
}

function renderProposalHistory(proposalId) {
  const content = document.getElementById("proposalHistoryContent");
  const proposal = findProposal(proposalId);
  if (!proposal) {
    content.innerHTML = '<p class="proposal-empty">Choose a proposal to view its record history.</p>';
    return;
  }
  const history = [...proposal.history].reverse();
  content.innerHTML = `
    <div class="proposal-history-heading">
      <h3>${proposalEscape(proposal.title)}</h3>
      <p>${proposalEscape(proposal.reference)} · ${proposalEscape(proposal.status)}</p>
      ${safeProposalUrl(proposal.documentLink) ? `<a href="${proposalEscape(safeProposalUrl(proposal.documentLink))}" target="_blank" rel="noopener noreferrer">Open supporting document</a>` : ""}
    </div>
    <ol class="proposal-timeline">
      ${history.length ? history.map((entry) => `
        <li><strong>${proposalEscape(entry.action)}</strong>
          <span>${proposalEscape(entry.status || "")}${entry.actor ? ` · ${proposalEscape(entry.actor)}` : ""}</span>
          <time datetime="${proposalEscape(entry.date)}">${proposalEscape(new Date(entry.date).toLocaleString())}</time>
          ${entry.details ? `<p>${proposalEscape(entry.details)}</p>` : ""}
          ${safeProposalUrl(entry.documentLink) ? `<a href="${proposalEscape(safeProposalUrl(entry.documentLink))}" target="_blank" rel="noopener noreferrer">Open recorded document</a>` : ""}
        </li>`).join("") : '<li class="proposal-empty">No history entries recorded.</li>'}
    </ol>`;
}

function renderProposalPage(selectedReviewId = "", selectedHistoryId = "") {
  renderProposalStats();
  renderProposalRecords();
  renderProposalSelectors(selectedReviewId, selectedHistoryId);
}

function switchProposalTab(tabName) {
  document.querySelectorAll(".research-proposal-page .proposal-tab").forEach((tab) => {
    const active = tab.dataset.proposalTab === tabName;
    tab.classList.toggle("active", active);
    tab.setAttribute("aria-selected", String(active));
  });
  document.querySelectorAll(".research-proposal-page .proposal-panel").forEach((panel) => {
    const active = panel.id === `proposal-panel-${tabName}`;
    panel.classList.toggle("active", active);
    panel.hidden = !active;
  });
}

function resetProposalForm() {
  const form = document.getElementById("proposalForm");
  if (!form) return;
  form.reset();
  document.getElementById("proposalId").value = "";
  document.getElementById("proposalFormHeading").textContent =
    "Research Proposal Registration";
  document.getElementById("proposalSubmitButton").textContent = "Submit Proposal";
  document.querySelector("[data-proposal-cancel]").hidden = true;
}

function editProposal(proposalId) {
  const proposal = findProposal(proposalId);
  if (!proposal || ["Approved", "Rejected"].includes(proposal.status)) return;
  const form = document.getElementById("proposalForm");
  const values = {
    proposalId: proposal.id,
    title: proposal.title,
    abstract: proposal.abstract,
    category: proposal.category,
    classification: proposal.classification,
    objectives: proposal.objectives,
    methodology: proposal.methodology,
    leader: proposal.leader,
    email: proposal.email,
    department: proposal.department,
    adviser: proposal.adviser,
    researchers: (proposal.researchers || []).join("\n"),
    startDate: proposal.startDate,
    targetDate: proposal.targetDate,
    budget: proposal.budget,
    fundingSource: proposal.fundingSource,
    documentLink: proposal.documentLink,
  };
  Object.entries(values).forEach(([name, value]) => {
    const field = form.elements.namedItem(name);
    if (field) field.value = value || "";
  });
  document.getElementById("proposalFormHeading").textContent =
    proposal.status === "Revision Requested"
      ? "Revise and Resubmit Proposal"
      : "Edit Research Proposal";
  document.getElementById("proposalSubmitButton").textContent =
    proposal.status === "Revision Requested" ? "Resubmit Proposal" : "Save and Submit";
  document.querySelector("[data-proposal-cancel]").hidden = false;
  switchProposalTab("registration");
  form.elements.namedItem("title").focus();
}

function validateProposalDates(startDate, targetDate) {
  if (startDate && targetDate && targetDate < startDate) {
    const field = document.querySelector('#proposalForm [name="targetDate"]');
    field.setCustomValidity("Target completion date must be on or after the start date.");
    field.reportValidity();
    return false;
  }
  document
    .querySelector('#proposalForm [name="targetDate"]')
    .setCustomValidity("");
  return true;
}

function saveProposal(form, saveAsDraft) {
  if (!proposalStorageReady) {
    showProposalNotice("Hindi maaaring mag-save habang may storage error.", "error");
    return;
  }
  const data = new FormData(form);
  const proposalId = String(data.get("proposalId") || "");
  const existing = proposalId ? findProposal(proposalId) : null;
  if (existing && ["Approved", "Rejected"].includes(existing.status)) {
    showProposalNotice("Hindi na maaaring baguhin ang pinal na proposal decision.", "error");
    return;
  }
  const startDate = String(data.get("startDate") || "");
  const targetDate = String(data.get("targetDate") || "");
  if (!validateProposalDates(startDate, targetDate)) return;
  const documentLink = String(data.get("documentLink") || "").trim();
  if (documentLink && !safeProposalUrl(documentLink)) {
    showProposalNotice("Supporting document link must use http:// or https://.", "error");
    form.elements.namedItem("documentLink").focus();
    return;
  }
  const title = String(data.get("title") || "").trim();
  if (!title) {
    showProposalNotice("Ilagay ang research title bago mag-save bilang draft.", "error");
    form.elements.namedItem("title").focus();
    return;
  }
  const priorStatus = existing?.status || "Draft";
  const status = saveAsDraft
    ? existing && priorStatus !== "Draft"
      ? priorStatus
      : "Draft"
    : priorStatus === "Revision Requested"
      ? "Resubmitted"
      : "Submitted";
  const now = new Date().toISOString();
  const action = saveAsDraft
    ? existing
      ? "Proposal draft updated"
      : "Proposal saved as draft"
    : priorStatus === "Revision Requested"
      ? "Proposal resubmitted"
      : existing
        ? "Proposal updated and submitted"
        : "Proposal submitted";
  const proposal = {
    ...(existing || {}),
    id: existing?.id || `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    reference: existing?.reference || nextProposalReference(),
    title,
    abstract: String(data.get("abstract") || "").trim(),
    category: String(data.get("category") || ""),
    classification: String(data.get("classification") || ""),
    objectives: String(data.get("objectives") || "").trim(),
    methodology: String(data.get("methodology") || "").trim(),
    leader: String(data.get("leader") || "").trim(),
    email: String(data.get("email") || "").trim(),
    department: String(data.get("department") || "").trim(),
    adviser: String(data.get("adviser") || "").trim(),
    researchers: String(data.get("researchers") || "")
      .split(/\r?\n/)
      .map((name) => name.trim())
      .filter(Boolean),
    startDate,
    targetDate,
    budget: String(data.get("budget") || ""),
    fundingSource: String(data.get("fundingSource") || "").trim(),
    documentLink,
    status,
    createdAt: existing?.createdAt || now,
    submittedAt: saveAsDraft
      ? existing?.submittedAt || ""
      : existing?.submittedAt || now.slice(0, 10),
    lastSubmittedAt: saveAsDraft
      ? existing?.lastSubmittedAt || ""
      : now.slice(0, 10),
    revisionInstructions: saveAsDraft ? existing?.revisionInstructions || "" : "",
    history: [
      ...(existing?.history || []),
      createProposalHistoryEntry(action, status, {
        actor: String(data.get("leader") || "").trim(),
        details: saveAsDraft
          ? "Registration details saved."
          : status === "Resubmitted"
            ? "Updated proposal submitted for another evaluation."
            : "Proposal submission recorded.",
        documentLink: String(data.get("documentLink") || "").trim(),
      }),
    ],
    assessments: existing?.assessments || [],
  };
  const next = existing
    ? researchProposals.map((item) => (item.id === existing.id ? proposal : item))
    : [proposal, ...researchProposals];
  if (!persistResearchProposals(next)) return;
  resetProposalForm();
  renderProposalPage(proposal.id, proposal.id);
  switchProposalTab("records");
}

function startProposalReview(proposalId) {
  const proposal = findProposal(proposalId);
  if (!proposal || !["Submitted", "Under Review", "Resubmitted"].includes(proposal.status)) {
    showProposalNotice("Only submitted or resubmitted proposals can be reviewed.", "error");
    return;
  }
  if (proposal.status !== "Under Review") {
    const updated = {
      ...proposal,
      status: "Under Review",
      history: [
        ...proposal.history,
        createProposalHistoryEntry("Proposal review started", "Under Review"),
      ],
    };
    if (!persistResearchProposals(
      researchProposals.map((item) => (item.id === proposalId ? updated : item)),
    )) return;
  }
  renderProposalPage(proposalId, proposalId);
  switchProposalTab("review");
}

function saveProposalAssessment(form) {
  const data = new FormData(form);
  const proposalId = String(data.get("proposalId") || "");
  const proposal = findProposal(proposalId);
  const decision = String(data.get("decision") || "");
  const comments = String(data.get("comments") || "").trim();
  const revisionInstructions = String(data.get("revisionInstructions") || "").trim();
  if (!proposal || !["Under Review", "Submitted", "Resubmitted"].includes(proposal.status)) {
    showProposalNotice("Select a submitted proposal before saving an assessment.", "error");
    return;
  }
  if (!PROPOSAL_STATUSES.includes(decision) ||
      !["Revision Requested", "Approved", "Rejected"].includes(decision) ||
      !comments ||
      (decision === "Revision Requested" && !revisionInstructions)) {
    showProposalNotice(
      "Pumili ng assessment result at maglagay ng comments. Kailangan ang revision instructions kung magre-request ng revision.",
      "error",
    );
    return;
  }
  const assessmentLink = String(data.get("assessmentLink") || "").trim();
  if (assessmentLink && !safeProposalUrl(assessmentLink)) {
    showProposalNotice("Assessment document link must use http:// or https://.", "error");
    form.elements.namedItem("assessmentLink").focus();
    return;
  }
  const assessment = {
    date: new Date().toISOString(),
    decision,
    reviewer: String(data.get("reviewer") || "").trim(),
    relevance: String(data.get("relevance") || ""),
    methodologyScore: String(data.get("methodologyScore") || ""),
    feasibility: String(data.get("feasibility") || ""),
    comments,
    revisionInstructions,
    assessmentLink,
  };
  if (!assessment.reviewer) {
    showProposalNotice("Ilagay ang pangalan ng reviewer / evaluator.", "error");
    return;
  }
  const updated = {
    ...proposal,
    status: decision,
    revisionInstructions: decision === "Revision Requested" ? revisionInstructions : "",
    assessments: [...proposal.assessments, assessment],
    history: [
      ...proposal.history,
      createProposalHistoryEntry(`Assessment result: ${decision}`, decision, {
        actor: assessment.reviewer,
        details: comments,
        documentLink: assessment.assessmentLink,
      }),
    ],
  };
  if (!persistResearchProposals(
    researchProposals.map((item) => (item.id === proposalId ? updated : item)),
  )) return;
  renderProposalPage(proposalId, proposalId);
  switchProposalTab("review");
}

function showProposalDetails(proposalId) {
  const proposal = findProposal(proposalId);
  if (!proposal) return;
  const details = document.getElementById("proposalDetailsBody");
  const latest = proposal.assessments[proposal.assessments.length - 1];
  details.innerHTML = `
    <dl class="proposal-details-grid">
      <div><dt>Reference / Status</dt><dd>${proposalEscape(proposal.reference)} · ${proposalEscape(proposal.status)}</dd></div>
      <div><dt>Category / Classification</dt><dd>${proposalEscape(proposal.category)} / ${proposalEscape(proposal.classification)}</dd></div>
      <div class="full"><dt>Title</dt><dd>${proposalEscape(proposal.title)}</dd></div>
      <div class="full"><dt>Abstract</dt><dd>${proposalEscape(proposal.abstract)}</dd></div>
      <div><dt>Project Leader</dt><dd>${proposalEscape(proposal.leader)}</dd></div>
      <div><dt>Email</dt><dd>${proposalEscape(proposal.email)}</dd></div>
      <div><dt>Department / Unit</dt><dd>${proposalEscape(proposal.department || "Not set")}</dd></div>
      <div><dt>Research Adviser</dt><dd>${proposalEscape(proposal.adviser || "Not assigned")}</dd></div>
      <div><dt>Planned Timeline</dt><dd>${proposalEscape(proposalDateLabel(proposal.startDate))} – ${proposalEscape(proposalDateLabel(proposal.targetDate))}</dd></div>
      <div><dt>Latest Submission</dt><dd>${proposalEscape(proposalDateLabel(proposal.lastSubmittedAt || proposal.submittedAt))}</dd></div>
      <div><dt>Requested Budget</dt><dd>${proposal.budget ? proposalEscape(Number(proposal.budget).toLocaleString()) : "Not specified"}</dd></div>
      <div class="full"><dt>Objectives</dt><dd>${proposalEscape(proposal.objectives || "Not recorded")}</dd></div>
      <div class="full"><dt>Methodology / Description</dt><dd>${proposalEscape(proposal.methodology || "Not recorded")}</dd></div>
      <div class="full"><dt>Co-researchers</dt><dd>${proposal.researchers.length ? proposal.researchers.map(proposalEscape).join(", ") : "None recorded"}</dd></div>
      <div class="full"><dt>Latest Assessment</dt><dd>${latest ? `${proposalEscape(latest.decision)} — ${proposalEscape(latest.comments)}` : "No review recorded"}</dd></div>
      <div class="full"><dt>Supporting Document</dt><dd>${safeProposalUrl(proposal.documentLink) ? `<a href="${proposalEscape(safeProposalUrl(proposal.documentLink))}" target="_blank" rel="noopener noreferrer">Open proposal document</a>` : "No document link recorded"}</dd></div>
    </dl>`;
  document.getElementById("proposalDetailsModal").classList.add("show");
  document.getElementById("proposalDetailsModal").setAttribute("aria-hidden", "false");
}

function closeProposalDetails() {
  const modal = document.getElementById("proposalDetailsModal");
  modal.classList.remove("show");
  modal.setAttribute("aria-hidden", "true");
}

function initializeResearchProposalPage() {
  const root = getProposalRoot();
  if (!root || root.dataset.initialized === "true") return;
  root.dataset.initialized = "true";
  loadResearchProposals();
  renderProposalPage();
  if (proposalHandlersBound) return;
  proposalHandlersBound = true;

  document.addEventListener("click", (event) => {
    const activeRoot = getProposalRoot();
    if (!activeRoot || !(event.target instanceof Element)) return;
    const tab = event.target.closest("[data-proposal-tab]");
    if (tab && activeRoot.contains(tab)) {
      switchProposalTab(tab.dataset.proposalTab);
      return;
    }
    if (event.target.closest("[data-proposal-new]")?.closest(".research-proposal-page")) {
      resetProposalForm();
      switchProposalTab("registration");
      document.querySelector('#proposalForm [name="title"]').focus();
      return;
    }
    if (event.target.closest("[data-proposal-save-draft]")?.closest(".research-proposal-page")) {
      const form = document.getElementById("proposalForm");
      saveProposal(form, true);
      return;
    }
    if (event.target.closest("[data-proposal-cancel]")?.closest(".research-proposal-page")) {
      resetProposalForm();
      return;
    }
    const action = event.target.closest("[data-proposal-action]");
    if (action && activeRoot.contains(action)) {
      const { proposalId, proposalAction } = action.dataset;
      if (proposalAction === "details") showProposalDetails(proposalId);
      if (proposalAction === "edit") editProposal(proposalId);
      if (proposalAction === "review") startProposalReview(proposalId);
      if (proposalAction === "history") {
        renderProposalSelectors("", proposalId);
        switchProposalTab("history");
      }
      return;
    }
    if (event.target.closest("[data-proposal-close]")) {
      closeProposalDetails();
      return;
    }
    if (event.target.id === "proposalDetailsModal") closeProposalDetails();
  });

  document.addEventListener("input", (event) => {
    if (!getProposalRoot() || !(event.target instanceof Element)) return;
    if (event.target.id === "proposalSearch") renderProposalRecords();
  });
  document.addEventListener("change", (event) => {
    if (!getProposalRoot() || !(event.target instanceof Element)) return;
    if (
      ["proposalStatusFilter", "proposalCategoryFilter"].includes(event.target.id)
    ) renderProposalRecords();
    if (event.target.id === "proposalReviewSelect") {
      renderProposalReviewSummary(event.target.value);
    }
    if (event.target.id === "proposalHistorySelect") {
      renderProposalHistory(event.target.value);
    }
  });
  document.addEventListener("submit", (event) => {
    const activeRoot = getProposalRoot();
    if (!activeRoot || !(event.target instanceof HTMLFormElement) ||
        !activeRoot.contains(event.target)) return;
    if (event.target.id === "proposalForm") {
      event.preventDefault();
      saveProposal(event.target, false);
    } else if (event.target.id === "proposalReviewForm") {
      event.preventDefault();
      saveProposalAssessment(event.target);
    }
  });
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && getProposalRoot()) closeProposalDetails();
  });
  window.addEventListener("storage", (event) => {
    if (event.key === RESEARCH_PROPOSALS_KEY && getProposalRoot()) {
      if (loadResearchProposals()) renderProposalPage();
    }
  });
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initializeResearchProposalPage);
} else {
  initializeResearchProposalPage();
}
window.addEventListener("page:loaded", initializeResearchProposalPage);
