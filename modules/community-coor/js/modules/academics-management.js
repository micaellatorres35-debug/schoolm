// Read subject catalog details from DOM data island (not from window global)
function getSubjectCatalogDetails() {
  const el = document.getElementById("subjectCatalogDetailsData");
  if (!el) return {};
  try {
    return JSON.parse(el.textContent || "{}");
  } catch (e) {
    console.error("Failed to parse subjectCatalogDetailsData:", e);
    return {};
  }
}

// Generic modal helpers (created here because other modules rely on them).
// These simply toggle the "show" class on the modal element.
function openModalById(modalId) {
  try {
    const el = document.getElementById(modalId);
    if (!el) return;
    el.classList.add("show");
  } catch (e) {
    console.error("openModalById error:", e);
  }
}

function closeModalById(modalId) {
  try {
    const el = document.getElementById(modalId);
    if (!el) return;
    el.classList.remove("show");
  } catch (e) {
    console.error("closeModalById error:", e);
  }
}

// Tab Persistence - Save and restore active tab using localStorage
const ACTIVE_TAB_KEY = "academicsManagement_activeTab";

function switchToTab(tabName) {
  document.querySelectorAll(".academics-panel").forEach((panel) => {
    panel.classList.remove("active");
  });
  document.querySelectorAll(".academics-tab").forEach((t) => {
    t.classList.remove("active");
  });

  const tab = document.querySelector(`.academics-tab[data-tab="${tabName}"]`);
  if (tab) {
    tab.classList.add("active");
  }

  const panel = document.getElementById(`tab-${tabName}`);
  if (panel) {
    panel.classList.add("active");
  }

  // Save active tab to localStorage
  localStorage.setItem(ACTIVE_TAB_KEY, tabName);
}

// Restore active tab on page load
document.addEventListener("DOMContentLoaded", function () {
  const savedTab = localStorage.getItem(ACTIVE_TAB_KEY);
  if (savedTab) {
    // Give DOM a moment to fully load before switching
    setTimeout(() => {
      switchToTab(savedTab);
    }, 100);
  }
});

// Use event delegation for tab clicks so newly injected DOM elements still work
document.addEventListener("click", function (e) {
  const tab = e.target.closest(".academics-tab");
  if (!tab) return;
  // ensure the click is inside the academics tab container
  if (!tab.closest(".academics-tabs")) return;
  const tabName = tab.getAttribute("data-tab");
  if (tabName) switchToTab(tabName);
});

// Curriculum Details
function viewCurriculumDetails(programId) {
  openModalById("curriculumDetailsModal");
  document.getElementById("currProgramName").value = "Loading...";
  document.getElementById("currVersion").value = "Loading...";
  document.getElementById("currUnits").value = "Loading...";
  document.getElementById("currStatus").value = "Loading...";
  document.getElementById("subjectFlowList").innerHTML =
    '<div class="text-muted">Loading curriculum details...</div>';
  document.getElementById("enrollmentSummaryContent").innerHTML =
    '<div class="text-muted">Loading summary...</div>';

  fetch(
    `/schoolm/modules/college-coor/api/get_curriculum.php?course_id=${encodeURIComponent(programId)}`,
  )
    .then(async (response) => {
      const rawText = await response.text();
      let data = null;

      try {
        data = rawText ? JSON.parse(rawText) : null;
      } catch (parseError) {
        console.error(
          "Invalid JSON returned from get_curriculum.php:",
          rawText.substring(0, 2000),
        );
        throw new Error(
          `Invalid JSON response from server (${response.status}).`,
        );
      }

      if (!response.ok && (!data || data.success !== false)) {
        throw new Error(
          data?.message || `Request failed with status ${response.status}`,
        );
      }

      return data;
    })
    .then((data) => {
      if (!data || data.success === false) {
        document.getElementById("currProgramName").value =
          "No program data found";
        document.getElementById("currVersion").value =
          "No curriculum data found";
        document.getElementById("currUnits").value = "No curriculum data found";
        document.getElementById("currStatus").value =
          "No curriculum data found";
        document.getElementById("subjectFlowList").innerHTML =
          '<div class="text-muted">No curriculum has been assigned for this program yet.</div>';
        document.getElementById("enrollmentSummaryContent").innerHTML =
          '<div class="text-muted">No enrollment summary available.</div>';
        return;
      }

      document.getElementById("currProgramName").value =
        data.program_name || "No program data found";
      document.getElementById("currVersion").value =
        data.curriculum_name || "No curriculum data found";
      document.getElementById("currUnits").value =
        data.effective_year || "No curriculum data found";
      document.getElementById("currStatus").value =
        data.status || "No curriculum data found";

      if (data.subject_flow && data.subject_flow.length > 0) {
        const flowHtml = data.subject_flow
          .map((yearGroup) => {
            const yearLabel =
              yearGroup.year_level === 1
                ? "FIRST YEAR"
                : yearGroup.year_level === 2
                  ? "SECOND YEAR"
                  : yearGroup.year_level === 3
                    ? "THIRD YEAR"
                    : yearGroup.year_level === 4
                      ? "FOURTH YEAR"
                      : `YEAR ${yearGroup.year_level}`;

            const semestersHtml = (yearGroup.semesters || [])
              .map((semGroup) => {
                const subjectsHtml = (semGroup.subjects || [])
                  .map(
                    (subject) => `
                            <tr>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #e9ecef;">${subject.code || ""}</td>
                                <td style="padding: 6px 8px; border-bottom: 1px solid #e9ecef;">${subject.name || ""}</td>
                            </tr>`,
                  )
                  .join("");

                return `
                            <div style="margin-bottom: 10px;">
                                <strong>${semGroup.semester || "N/A"}</strong>
                                <table style="width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 13px;">
                                    <thead>
                                        <tr style="background: #e9ecef;">
                                            <th style="text-align: left; padding: 6px 8px;">Code</th>
                                            <th style="text-align: left; padding: 6px 8px;">Subject</th>
                                        </tr>
                                    </thead>
                                    <tbody>${subjectsHtml}</tbody>
                                </table>
                            </div>`;
              })
              .join("");

            return `<div style="margin-bottom: 18px;"><h6 style="margin: 0 0 8px 0; font-size: 15px; text-transform: uppercase; color: #0d6efd;">${yearLabel}</h6>${semestersHtml}</div>`;
          })
          .join("");

        document.getElementById("subjectFlowList").innerHTML = flowHtml;
      } else {
        document.getElementById("subjectFlowList").innerHTML =
          '<div class="text-muted">No subject flow available for this curriculum.</div>';
      }

      const summary = data.enrollment_summary || {};
      const summaryRows = [
        {
          label: "First Year",
          count: summary.year_levels?.first_year?.enrolled_students ?? 0,
          units: summary.year_levels?.first_year?.total_curriculum_units ?? 0,
        },
        {
          label: "Second Year",
          count: summary.year_levels?.second_year?.enrolled_students ?? 0,
          units: summary.year_levels?.second_year?.total_curriculum_units ?? 0,
        },
        {
          label: "Third Year",
          count: summary.year_levels?.third_year?.enrolled_students ?? 0,
          units: summary.year_levels?.third_year?.total_curriculum_units ?? 0,
        },
        {
          label: "Fourth Year",
          count: summary.year_levels?.fourth_year?.enrolled_students ?? 0,
          units: summary.year_levels?.fourth_year?.total_curriculum_units ?? 0,
        },
      ];

      const summaryHtml = `
                <div style="margin-bottom: 12px;">
                    <strong>Total Enrolled Students:</strong> ${Number(summary.total_enrolled_students ?? 0)}
                </div>
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #e9ecef;">
                            <th style="padding: 8px; text-align: left; border-bottom: 1px solid #dee2e6;">Year Level</th>
                            <th style="padding: 8px; text-align: left; border-bottom: 1px solid #dee2e6;">Enrolled Students</th>
                            <th style="padding: 8px; text-align: left; border-bottom: 1px solid #dee2e6;">Total Curriculum Units</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${summaryRows
                          .map(
                            (row) => `
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">${row.label}</td>
                                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">${row.count}</td>
                                <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">${row.units}</td>
                            </tr>`,
                          )
                          .join("")}
                    </tbody>
                </table>`;

      document.getElementById("enrollmentSummaryContent").innerHTML =
        summaryHtml;
    })
    .catch((error) => {
      console.error("Curriculum details fetch failed:", error);
      document.getElementById("currProgramName").value =
        "No program data found";
      document.getElementById("currVersion").value = "No curriculum data found";
      document.getElementById("currUnits").value = "No curriculum data found";
      document.getElementById("currStatus").value = "No curriculum data found";
      document.getElementById("subjectFlowList").innerHTML =
        '<div class="text-muted">Unable to load curriculum details.</div>';
      document.getElementById("enrollmentSummaryContent").innerHTML =
        '<div class="text-muted">Unable to load enrollment summary.</div>';
    });
}

function closeCurriculumModal() {
  closeModalById("curriculumDetailsModal");
}

// Subject Details
function viewSubjectDetails(subjectId) {
  const details = getSubjectCatalogDetails()[subjectId] || [];
  const subjectInfo = details.length ? details[0] : null;

  openModalById("subjectDetailsModal");
  document.getElementById("subjCode").value = subjectInfo?.code || "N/A";
  document.getElementById("subjName").value = subjectInfo?.name || "N/A";
  document.getElementById("subjUnits").value = subjectInfo?.units ?? "0";
  document.getElementById("subjLectureHours").value =
    subjectInfo?.lecture_hours ?? "0";
  document.getElementById("subjLabHours").value = subjectInfo?.lab_hours ?? "0";

  const tbody = document.getElementById("subjectAssignmentBody");
  tbody.innerHTML = "";

  if (!details.length) {
    tbody.innerHTML =
      '<tr><td colspan="7" class="text-center text-muted">No curriculum assignments found for this subject.</td></tr>';
    document.getElementById("subjectAssignmentStatus").textContent =
      "Status: Unassigned";
    return;
  }

  details.forEach((assign) => {
    const row = document.createElement("tr");
    const yearLevel =
      assign.year_level === "1"
        ? "1st Year"
        : assign.year_level === "2"
          ? "2nd Year"
          : assign.year_level === "3"
            ? "3rd Year"
            : assign.year_level === "4"
              ? "4th Year"
              : assign.year_level || "Unassigned";
    const semester = assign.semester
      ? `${assign.semester} Semester`
      : "Unassigned";
    const statusBadge = assign.curriculum_id
      ? `<span class="badge ${assign.is_active == 1 ? "bg-success" : "bg-secondary"}">${assign.is_active == 1 ? "Active" : "Inactive"}</span>`
      : '<span class="badge bg-warning text-dark">Unassigned</span>';

    row.innerHTML = `
            <td>${assign.course_code ? `${assign.course_code}` : "Unassigned"}</td>
            <td>${assign.course_name ? `${assign.course_name}` : "Unassigned"}</td>
            <td>${assign.curriculum_name ? `${assign.curriculum_name}` : "Unassigned"}</td>
            <td>${assign.effective_year ? `${assign.effective_year}` : "Unassigned"}</td>
            <td>${yearLevel}</td>
            <td>${semester}</td>
            <td>${statusBadge}</td>
        `;
    tbody.appendChild(row);
  });

  const activeCount = details.filter(
    (assign) => assign.curriculum_id && assign.is_active == 1,
  ).length;
  const inactiveCount = details.filter(
    (assign) => assign.curriculum_id && assign.is_active != 1,
  ).length;
  const statusText =
    activeCount > 0 ? "Active" : inactiveCount > 0 ? "Inactive" : "Unassigned";
  document.getElementById("subjectAssignmentStatus").innerHTML =
    `<strong>Status:</strong> <span class="badge ${statusText === "Active" ? "bg-success" : statusText === "Inactive" ? "bg-secondary" : "bg-warning text-dark"}">${statusText}</span>`;
}

function closeSubjectModal() {
  closeModalById("subjectDetailsModal");
}

// Section Management
function getSectionSemesterOptions() {
  const raw =
    document.getElementById("sectionSemesterOptionsData")?.textContent || "[]";

  try {
    return JSON.parse(raw) || [];
  } catch (error) {
    console.error("Error parsing section semester options:", error);
    return [];
  }
}

function loadSectionSchoolYears() {
  const schoolYearSelect = document.getElementById("sectionSchoolYear");
  const semesterSelect = document.getElementById("sectionSemester");

  if (schoolYearSelect) schoolYearSelect.value = "";
  if (semesterSelect) {
    semesterSelect.innerHTML = '<option value="">Select Semester</option>';
    semesterSelect.disabled = true;
  }
}

function loadSectionSemesters() {
  const schoolYearSelect = document.getElementById("sectionSchoolYear");
  const semesterSelect = document.getElementById("sectionSemester");
  const schoolYearId = String(schoolYearSelect.value || "");
  const semesterOptions = getSectionSemesterOptions();

  semesterSelect.innerHTML = '<option value="">Select Semester</option>';
  semesterSelect.disabled = true;

  if (!schoolYearId) {
    return;
  }

  const matchingSemesters = semesterOptions.filter(
    (semester) => String(semester.school_year_id) === schoolYearId,
  );

  if (matchingSemesters.length > 0) {
    semesterSelect.innerHTML =
      '<option value="">Select Semester</option>' +
      matchingSemesters
        .map(
          (semester) => `
                <option value="${semester.id}">${semester.name}</option>
            `,
        )
        .join("");
    semesterSelect.disabled = false;
  } else {
    semesterSelect.innerHTML = '<option value="">No semesters found</option>';
    semesterSelect.disabled = true;
  }
}

function openAddSectionModal() {
  const form = document.getElementById("sectionForm");
  if (!form) {
    console.error("sectionForm not found in DOM");
    return;
  }
  form.reset();

  const title = document.getElementById("sectionModalTitle");
  if (title) title.textContent = "Add Section";

  const saveBtn = document.getElementById("saveSectionBtn");
  if (saveBtn) saveBtn.disabled = false;

  loadSectionSchoolYears();
  openModalById("sectionModal");
}

function closeSectionModal() {
  closeModalById("sectionModal");
}

function assignAdviser(sectionId, sectionCode) {
  document.getElementById("adviserSectionId").value = sectionId;
  document.getElementById("adviserSectionCode").textContent =
    sectionCode || "N/A";
  document.getElementById("adviserSectionYear").textContent = "N/A";
  document.getElementById("adviserSectionSemester").textContent = "N/A";
  document.getElementById("adviserSectionSchoolYear").textContent = "N/A";
  document.getElementById("adviserCurrentAdviser").textContent = "Not Assigned";
  document.getElementById("adviserSelect").value = "";
  document.getElementById("adviserModalTitle").textContent = "Assign Adviser";
  document.getElementById("adviserActionButton").textContent = "Assign Adviser";
  openModalById("adviserModal");
}

function closeAdviserModal() {
  closeModalById("adviserModal");
}

function openAssignAdviserModal(row) {
  if (!row) return;
  const sectionId = row.dataset.sectionId || "";
  const sectionCode = row.querySelector("td")?.textContent.trim() || "N/A";
  const sectionYear = row.dataset.yearLevel || "N/A";
  const sectionSemester = row.dataset.semester || "N/A";
  const sectionSchoolYear = row.dataset.schoolYear || "N/A";
  const adviserName = row.dataset.adviser || "Not Assigned";
  const hasAdviser = adviserName !== "Not Assigned";

  document.getElementById("adviserSectionId").value = sectionId;
  document.getElementById("adviserSectionCode").textContent = sectionCode;
  document.getElementById("adviserSectionYear").textContent = sectionYear;
  document.getElementById("adviserSectionSemester").textContent =
    sectionSemester;
  document.getElementById("adviserSectionSchoolYear").textContent =
    sectionSchoolYear;
  document.getElementById("adviserCurrentAdviser").textContent = adviserName;
  document.getElementById("adviserSelect").value = "";
  document.getElementById("adviserModalTitle").textContent = hasAdviser
    ? "Reassign Adviser"
    : "Assign Adviser";
  document.getElementById("adviserActionButton").textContent = hasAdviser
    ? "Reassign Adviser"
    : "Assign Adviser";
  openModalById("adviserModal");
}

function viewSectionDetails(row) {
  if (!row) return;
  const sectionCode = row.querySelector("td")?.textContent.trim() || "N/A";
  const sectionYear = row.dataset.yearLevel || "N/A";
  const sectionSemester = row.dataset.semester || "N/A";
  const sectionSchoolYear = row.dataset.schoolYear || "N/A";
  const totalStudents = row.dataset.totalStudents || "0";
  const adviserName = row.dataset.adviser || "Not Assigned";
  const statusText = row.dataset.status || "Active";

  document.getElementById("sectionDetailsCode").textContent = sectionCode;
  document.getElementById("sectionDetailsYear").textContent = sectionYear;
  document.getElementById("sectionDetailsSemester").textContent =
    sectionSemester;
  document.getElementById("sectionDetailsSchoolYear").textContent =
    sectionSchoolYear;
  document.getElementById("sectionDetailsTotalStudents").textContent =
    totalStudents;
  document.getElementById("sectionDetailsAdviser").textContent = adviserName;
  const statusBadge = document.getElementById("sectionDetailsStatus");
  statusBadge.textContent = statusText;
  statusBadge.className =
    statusText.toLowerCase() === "inactive"
      ? "badge badge-secondary"
      : "badge badge-success";
  openModalById("sectionDetailsModal");
}

function closeSectionDetailsModal() {
  closeModalById("sectionDetailsModal");
}

function viewSectionStudents(sectionId) {
  openModalById("sectionStudentsModal");
  const sampleStudents = [
    { id: "STU-0001", name: "Maria Santos Garcia", status: "Active" },
    { id: "STU-0002", name: "Juan Carlos Reyes", status: "Active" },
    { id: "STU-0003", name: "Ana Maria Cruz", status: "Active" },
    { id: "STU-0004", name: "Miguel Fernando Lopez", status: "Active" },
    { id: "STU-0005", name: "Rosa Isabel Ocampo", status: "Active" },
  ];

  const tbody = document.getElementById("sectionStudentsList");
  tbody.innerHTML = sampleStudents
    .map(
      (std) => `
        <tr>
            <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">${std.id}</td>
            <td style="padding: 8px; border-bottom: 1px solid #dee2e6;">${std.name}</td>
            <td style="padding: 8px; border-bottom: 1px solid #dee2e6;"><span class="badge badge-success">${std.status}</span></td>
        </tr>
    `,
    )
    .join("");
}

function closeSectionStudentsModal() {
  closeModalById("sectionStudentsModal");
}

// Faculty Load Management
function openAddFacultyLoadModal() {
  document.getElementById("facultyLoadId").value = "";
  document.getElementById("facultyLoadModalTitle").textContent =
    "Assign Faculty Load";
  document.getElementById("facultySelect").value = "";
  document.getElementById("loadSectionSelect").value = "";
  document.getElementById("loadSubject").value = "";
  document.getElementById("loadUnits").value = "3";
  document.getElementById("loadSchedule").value = "";
  document.getElementById("conflictWarning").style.display = "none";
  openModalById("facultyLoadModal");
}

function editFacultyLoad(loadId) {
  document.getElementById("facultyLoadId").value = loadId;
  document.getElementById("facultyLoadModalTitle").textContent =
    "Edit Faculty Load";
  document.getElementById("facultySelect").value = "Dr. Maria Santos";
  document.getElementById("loadSectionSelect").value = "IT-1A";
  document.getElementById("loadSubject").value = "SUBJ101";
  document.getElementById("loadUnits").value = "3";
  document.getElementById("loadSchedule").value = "MWF 9:00-10:30";
  document.getElementById("conflictWarning").style.display = "none";
  openModalById("facultyLoadModal");
}

function deleteFacultyLoad(loadId) {
  if (
    confirm("Are you sure you want to delete this faculty load assignment?")
  ) {
    alert("Faculty load deleted successfully");
  }
}

function closeFacultyLoadModal() {
  closeModalById("facultyLoadModal");
}

function viewFacultyLoadDetails(
  facultyId,
  facultyName,
  teachingUnits,
  maxLoad,
  assignedSections,
  assignedSubjects,
  department,
) {
  document.getElementById("detailFacultyName").textContent =
    facultyName || "N/A";
  document.getElementById("detailDepartment").textContent = department || "N/A";
  document.getElementById("detailMaxLoad").textContent = maxLoad + " Units";
  document.getElementById("detailTeachingUnits").textContent =
    teachingUnits + " Units";

  const remainingLoad = Math.max(0, maxLoad - teachingUnits);
  document.getElementById("detailRemainingLoad").textContent =
    remainingLoad + " Units";

  document.getElementById("detailAssignedSectionsCount").textContent =
    assignedSections || 0;
  document.getElementById("detailAssignedAdviserSectionsCount").textContent = 0;
  document.getElementById("detailAssignedSubjectsCount").textContent =
    assignedSubjects || 0;

  const instructorSectionsContainer = document.getElementById(
    "detailAssignedSections",
  );
  const adviserSectionsContainer = document.getElementById(
    "detailAssignedAdviserSections",
  );
  const assignedSubjectsContainer = document.getElementById(
    "detailAssignedSubjects",
  );
  instructorSectionsContainer.innerHTML =
    '<div style="color: #6c757d;">Loading instructor assignments...</div>';
  adviserSectionsContainer.innerHTML =
    '<div style="color: #6c757d;">Loading adviser assignments...</div>';
  assignedSubjectsContainer.innerHTML =
    '<div style="color: #6c757d;">Loading assigned subjects...</div>';

  fetch(
    `/schoolm/modules/college-coor/api/get_faculty_sections.php?faculty_id=${facultyId}`,
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.sections && data.sections.length > 0) {
        const instructorSections = data.sections.filter(
          (section) => section.role === "Instructor",
        );
        const adviserSections = data.sections.filter(
          (section) => section.role === "Adviser",
        );

        function groupByYearSemester(sections) {
          return sections.reduce((groups, section) => {
            const year = section.school_year || "Unknown School Year";
            const semester = section.semester || "Unknown Semester";
            const key = `${year}||${semester}`;
            if (!groups[key]) {
              groups[key] = { year, semester, sections: [] };
            }
            groups[key].sections.push(section);
            return groups;
          }, {});
        }

        function renderGroupedSections(groups) {
          return Object.values(groups)
            .map((group) => {
              const header = `<div style="margin-bottom: 10px; font-weight: 700;">${group.year} - ${group.semester}</div>`;
              const rows = group.sections
                .map((section) => {
                  const yearLabel = getSectionYearLabel(
                    section.grade_level || "0",
                  );
                  const warning = section.period_mismatch
                    ? `<div style="color: #dc3545; margin-top: 10px; font-weight: 600;">Warning: selected semester does not belong to this section's school year.</div>`
                    : "";
                  return `
                                <div style="padding: 15px; border-bottom: 1px solid #dee2e6;">
                                    <h6 style="margin: 0 0 10px; font-weight: 700;">${section.section_code || "N/A"}</h6>
                                    <div style="margin-bottom: 8px;"><strong>Program:</strong> ${section.program_code || "N/A"}</div>
                                    <div style="margin-bottom: 8px;"><strong>Year Level:</strong> ${yearLabel}</div>
                                    <div style="margin-bottom: 8px;"><strong>Status:</strong> ${section.status || "N/A"}</div>
                                    <div style="margin-bottom: 8px;"><strong>Assigned At:</strong> ${section.assigned_at ? new Date(section.assigned_at).toLocaleString() : "N/A"}</div>
                                    ${warning}
                                </div>
                            `;
                })
                .join("");
              return `<div style="margin-bottom: 20px;">${header}${rows}</div>`;
            })
            .join("");
        }

        if (instructorSections.length > 0) {
          const instructorGroups = groupByYearSemester(instructorSections);
          instructorSectionsContainer.innerHTML =
            renderGroupedSections(instructorGroups);
        } else {
          instructorSectionsContainer.innerHTML =
            '<div style="color: #6c757d;">No instructor sections assigned.</div>';
        }

        if (adviserSections.length > 0) {
          const adviserGroups = groupByYearSemester(adviserSections);
          adviserSectionsContainer.innerHTML =
            renderGroupedSections(adviserGroups);
        } else {
          adviserSectionsContainer.innerHTML =
            '<div style="color: #6c757d;">No adviser sections assigned.</div>';
        }

        document.getElementById("detailAssignedSectionsCount").textContent =
          instructorSections.length;
        document.getElementById(
          "detailAssignedAdviserSectionsCount",
        ).textContent = adviserSections.length;
      } else {
        instructorSectionsContainer.innerHTML =
          '<div style="color: #6c757d;">No instructor sections assigned.</div>';
        adviserSectionsContainer.innerHTML =
          '<div style="color: #6c757d;">No adviser sections assigned.</div>';
        document.getElementById("detailAssignedSectionsCount").textContent = 0;
        document.getElementById(
          "detailAssignedAdviserSectionsCount",
        ).textContent = 0;
      }
    })
    .catch((error) => {
      console.error("Error loading faculty sections:", error);
      instructorSectionsContainer.innerHTML =
        '<div style="color: #6c757d;">Unable to load instructor assignments.</div>';
      adviserSectionsContainer.innerHTML =
        '<div style="color: #6c757d;">Unable to load adviser assignments.</div>';
    });

  fetch(
    `/schoolm/modules/college-coor/api/get_faculty_assignments.php?faculty_id=${facultyId}`,
  )
    .then((response) => response.json())
    .then((data) => {
      const scheduleList = document.getElementById("detailScheduleList");

      if (data.success && data.assignments && data.assignments.length > 0) {
        const grouped = data.assignments.reduce((groups, assignment) => {
          const year = assignment.school_year || "Unknown School Year";
          const semester = assignment.semester || "Unknown Semester";
          const key = `${year}||${semester}`;
          if (!groups[key]) {
            groups[key] = { year, semester, rows: [] };
          }
          groups[key].rows.push(assignment);
          return groups;
        }, {});

        assignedSubjectsContainer.innerHTML = Object.values(grouped)
          .map((group) => {
            const rowsHtml = group.rows
              .map((assignment) => {
                return `
                            <tr>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.section_code || "N/A"}</td>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.subject_code || "N/A"}</td>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.subject_name || "N/A"}</td>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">${assignment.units || 0}</td>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">${assignment.lecture_hours || 0}</td>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">${assignment.lab_hours || 0}</td>
                                <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">${assignment.assignment_status || "Active"}</td>
                            </tr>
                        `;
              })
              .join("");

            return `
                        <div style="margin-bottom: 1.25rem;">
                            <div style="font-weight: 700; margin-bottom: 0.75rem;">${group.year} - ${group.semester}</div>
                            <div style="overflow-x:auto;">
                                <table style="width:100%; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background-color: #f1f5f9;">
                                            <th style="padding: 10px; text-align:left;">Section Code</th>
                                            <th style="padding: 10px; text-align:left;">Subject Code</th>
                                            <th style="padding: 10px; text-align:left;">Subject Name</th>
                                            <th style="padding: 10px; text-align:center;">Units</th>
                                            <th style="padding: 10px; text-align:center;">Lecture Hours</th>
                                            <th style="padding: 10px; text-align:center;">Lab Hours</th>
                                            <th style="padding: 10px; text-align:center;">Assignment Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${rowsHtml}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    `;
          })
          .join("");

        document.getElementById("detailAssignedSubjectsCount").textContent =
          data.assignments.length;
      } else {
        assignedSubjectsContainer.innerHTML =
          '<div style="color: #6c757d;">No subjects assigned.</div>';
        document.getElementById("detailAssignedSubjectsCount").textContent = 0;
      }

      scheduleList.innerHTML =
        data.success && data.assignments && data.assignments.length > 0
          ? data.assignments
              .map((assignment) => {
                const status =
                  assignment.schedule_status || "Not Yet Scheduled";
                const statusBadgeClass =
                  status === "Scheduled" ? "badge-success" : "badge-warning";
                const dayOfWeek = assignment.day_of_week || "-";
                const timeRange = assignment.time_range || "-";
                const roomCode = assignment.room_code || "-";

                return `
                        <tr>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.subject_code || "N/A"}</td>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.section_code || "N/A"}</td>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;"><span class="badge ${statusBadgeClass}">${status}</span></td>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${dayOfWeek}</td>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${timeRange}</td>
                            <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${roomCode}</td>
                        </tr>
                    `;
              })
              .join("")
          : '<tr><td colspan="6" style="padding: 20px; text-align: center; color: #6c757d;">No schedules assigned</td></tr>';

      const historyContainer = document.getElementById("detailLoadHistory");
      historyContainer.innerHTML =
        '<div style="color: #6c757d;">Loading historical assignments...</div>';
      fetch(
        `/schoolm/modules/college-coor/api/get_faculty_assignments_history.php?faculty_id=${facultyId}`,
      )
        .then((response) => response.json())
        .then((historyData) => {
          if (
            historyData.success &&
            historyData.history &&
            historyData.history.length > 0
          ) {
            const groupedHistory = historyData.history.reduce(
              (groups, assignment) => {
                const year = assignment.school_year || "Unknown School Year";
                const semester = assignment.semester || "Unknown Semester";
                const section = assignment.section_code || "Unknown Section";
                const key = `${year}||${semester}`;

                if (!groups[key]) {
                  groups[key] = { year, semester, sections: {} };
                }

                if (!groups[key].sections[section]) {
                  groups[key].sections[section] = {
                    section_code: section,
                    assignments: [],
                  };
                }

                groups[key].sections[section].assignments.push(assignment);
                return groups;
              },
              {},
            );

            historyContainer.innerHTML = Object.values(groupedHistory)
              .map((group) => {
                return `
                                <div style="margin-bottom: 18px;">
                                    <div style="font-weight: 700; margin-bottom: 8px;">School Year: ${group.year} · Semester: ${group.semester}</div>
                                    ${Object.values(group.sections)
                                      .map(
                                        (sectionGroup) => `
                                        <div style="margin-left: 16px; margin-bottom: 12px;">
                                            <div style="font-weight: 600; margin-bottom: 6px;">${sectionGroup.section_code}</div>
                                            ${sectionGroup.assignments
                                              .map(
                                                (assignment) => `
                                                <div style="margin-left: 16px; margin-bottom: 8px; padding: 10px; background: #ffffff; border: 1px solid #dee2e6; border-radius: 5px; display: grid; grid-template-columns: 1fr auto; gap: 10px; align-items: center;">
                                                    <div>
                                                        <div style="font-weight: 600;">${assignment.subject_code || "N/A"} - ${assignment.subject_name || "N/A"}</div>
                                                        <div style="color: #6c757d; font-size: 0.95rem;">Section: ${assignment.section_code || "N/A"} · Units: ${assignment.units || 0} · Assigned: ${assignment.assignment_date ? new Date(assignment.assignment_date).toLocaleDateString() : "N/A"}</div>
                                                        <div style="color: #6c757d; font-size: 0.95rem;">Status: ${assignment.status || "Inactive"} · Reason: ${assignment.reason || "Academic Period Inactive"}</div>
                                                    </div>
                                                    <span style="background: #6c757d; color: #fff; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 700;">${assignment.status || "Inactive"}</span>
                                                </div>
                                            `,
                                              )
                                              .join("")}
                                        </div>
                                    `,
                                      )
                                      .join("")}
                                </div>
                            `;
              })
              .join("");
          } else {
            historyContainer.innerHTML =
              '<div style="color: #6c757d;">No historical assignments found.</div>';
          }
        })
        .catch((error) => {
          console.error("Error loading faculty load history:", error);
          historyContainer.innerHTML =
            '<div style="color: #dc3545;">Unable to load faculty load history.</div>';
        });
    })
    .catch((error) => {
      console.error("Error fetching assignments:", error);
      assignedSubjectsContainer.innerHTML =
        '<div style="color: #dc3545;">Error loading assigned subjects.</div>';
      document.getElementById("detailScheduleList").innerHTML =
        '<tr><td colspan="6" style="padding: 20px; text-align: center; color: #dc3545;">Error loading assignments</td></tr>';
    });

  openModalById("facultyLoadDetailsModal");
}

function closeFacultyLoadDetailsModal() {
  closeModalById("facultyLoadDetailsModal");
}

// Assign Faculty Load Modal Functions
let tempAssignments = [];
let currentAssignFacultyId = null;

function initializeAssignLoadModalEvents() {
  const schoolYearSelect = document.getElementById("assignLoadSchoolYear");
  const semesterSelect = document.getElementById("assignLoadSemester");

  if (schoolYearSelect.dataset.initialized === "true") {
    return;
  }

  schoolYearSelect.addEventListener("change", function () {
    loadSemestersForSchoolYear();
  });

  semesterSelect.addEventListener("change", function () {
    loadSectionsForAssignment();
  });

  schoolYearSelect.dataset.initialized = "true";
}

function openAssignFacultyLoadModal(
  facultyId,
  facultyName,
  teachingUnits,
  maxLoad,
  department,
) {
  initializeAssignLoadModalEvents();

  currentAssignFacultyId = facultyId;
  document.getElementById("assignLoadFacultyId").value = facultyId;
  document.getElementById("assignLoadFacultyName").value = facultyName;
  document.getElementById("assignLoadDepartment").value = department;

  const schoolYearSelect = document.getElementById("assignLoadSchoolYear");
  const semesterSelect = document.getElementById("assignLoadSemester");
  const sectionSelect = document.getElementById("assignLoadSection");

  schoolYearSelect.innerHTML = '<option value="">Select School Year</option>';
  semesterSelect.innerHTML = '<option value="">Select Semester</option>';
  semesterSelect.disabled = true;
  sectionSelect.innerHTML = "";
  sectionSelect.disabled = true;

  loadSchoolYears();
  openModalById("assignFacultyLoadModal");
}

function loadSchoolYears() {
  const schoolYearSelect = document.getElementById("assignLoadSchoolYear");
  const semesterSelect = document.getElementById("assignLoadSemester");
  const sectionSelect = document.getElementById("assignLoadSection");

  schoolYearSelect.innerHTML = '<option value="">Select School Year</option>';
  semesterSelect.innerHTML = '<option value="">Select Semester</option>';
  semesterSelect.disabled = true;
  sectionSelect.innerHTML = "";
  sectionSelect.disabled = true;

  fetch("/schoolm/modules/college-coor/api/get_school_years.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.school_years && data.school_years.length > 0) {
        schoolYearSelect.innerHTML =
          '<option value="">Select School Year</option>' +
          data.school_years
            .map(
              (year) => `
                        <option value="${year.id}">${year.name}</option>
                    `,
            )
            .join("");
        schoolYearSelect.value = data.school_years[0].id;
        loadSemestersForSchoolYear();
      } else {
        schoolYearSelect.innerHTML =
          '<option value="">No active school years found</option>';
      }
    })
    .catch((error) => {
      console.error("Error loading school years:", error);
      schoolYearSelect.innerHTML =
        '<option value="">Error loading school years</option>';
    });
}

function loadSemestersForSchoolYear() {
  const schoolYearSelect = document.getElementById("assignLoadSchoolYear");
  const semesterSelect = document.getElementById("assignLoadSemester");
  const sectionSelect = document.getElementById("assignLoadSection");
  const schoolYearId = schoolYearSelect.value;

  semesterSelect.innerHTML = '<option value="">Select Semester</option>';
  semesterSelect.disabled = true;
  sectionSelect.innerHTML = "";
  sectionSelect.disabled = true;

  if (!schoolYearId) {
    return;
  }

  fetch(
    `/schoolm/modules/college-coor/api/get_semesters.php?school_year_id=${schoolYearId}`,
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.semesters && data.semesters.length > 0) {
        semesterSelect.innerHTML =
          '<option value="">Select Semester</option>' +
          data.semesters
            .map(
              (semester) => `
                        <option value="${semester.id}">${semester.name}</option>
                    `,
            )
            .join("");
        semesterSelect.value = data.semesters[0].id;
        semesterSelect.disabled = false;
        loadSectionsForAssignment();
      } else {
        semesterSelect.innerHTML =
          '<option value="">No active semesters found</option>';
        semesterSelect.disabled = true;
      }
    })
    .catch((error) => {
      console.error("Error loading semesters:", error);
      semesterSelect.innerHTML =
        '<option value="">Error loading semesters</option>';
      semesterSelect.disabled = true;
    });
}

function getSectionYearLabel(yearLevel) {
  const rawValue = String(yearLevel ?? "").trim();

  if (!rawValue) {
    return "N/A";
  }

  if (!/^-?\d+(?:\.\d+)?$/.test(rawValue)) {
    return rawValue;
  }

  const year = parseInt(rawValue, 10);
  if (year === 1) return "1st Year";
  if (year === 2) return "2nd Year";
  if (year === 3) return "3rd Year";
  return `${year}th Year`;
}

function loadSectionsForAssignment() {
  const schoolYearSelect = document.getElementById("assignLoadSchoolYear");
  const semesterSelect = document.getElementById("assignLoadSemester");
  const sectionSelect = document.getElementById("assignLoadSection");
  const schoolYearId = schoolYearSelect.value;
  const semesterId = semesterSelect.value;
  const facultyId = document.getElementById("assignLoadFacultyId").value;

  sectionSelect.innerHTML = "";
  sectionSelect.disabled = true;

  if (!schoolYearId || !semesterId || !facultyId) {
    return;
  }

  fetch(
    `/schoolm/modules/college-coor/api/get_adviser_sections.php?faculty_id=${facultyId}&school_year_id=${schoolYearId}&semester_id=${semesterId}`,
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.sections && data.sections.length > 0) {
        sectionSelect.innerHTML =
          '<option value="">Select Section</option>' +
          data.sections
            .map(
              (section) => `
                        <option value="${section.id}" data-code="${section.section_code}" data-program="${section.program_code}" data-year-level="${section.grade_level}">
                            ${section.section_code} | ${section.program_code} | ${getSectionYearLabel(section.grade_level)}
                        </option>
                    `,
            )
            .join("");
        sectionSelect.disabled = false;
      } else {
        sectionSelect.innerHTML = '<option value="">No sections found</option>';
        sectionSelect.disabled = true;
      }
    })
    .catch((error) => {
      console.error("Error loading sections:", error);
      sectionSelect.innerHTML =
        '<option value="">Error loading sections</option>';
      sectionSelect.disabled = true;
    });
}

function loadSubjectsForSection() {
  const sectionId = document.getElementById("assignLoadSection").value;
  const subjectSelect = document.getElementById("assignLoadSubject");
  const saveBtn = document.querySelector(
    '#assignFacultyLoadForm button[type="submit"]',
  );
  const modalBody = document.querySelector(
    "#assignFacultyLoadModal .modal-body",
  );
  let alertEl = modalBody
    ? modalBody.querySelector("#assignSubjectAlert")
    : null;

  subjectSelect.innerHTML = '<option value="">Select Subject</option>';
  subjectSelect.disabled = true;

  if (!sectionId) {
    return;
  }

  fetch(
    `/schoolm/modules/college-coor/api/get_section_subjects.php?section_id=${sectionId}`,
  )
    .then((response) => response.json())
    .then((data) => {
      if (alertEl) {
        alertEl.remove();
        alertEl = null;
      }

      if (
        data &&
        data.success &&
        Array.isArray(data.subjects) &&
        data.subjects.length > 0
      ) {
        subjectSelect.innerHTML =
          '<option value="">Select Subject</option>' +
          data.subjects
            .map(
              (subject) => `
                        <option value="${subject.id}" data-code="${subject.code}" data-name="${subject.name}" data-units="${subject.units}">
                            ${subject.code} - ${subject.name} (${subject.units} units)
                        </option>
                    `,
            )
            .join("");
        subjectSelect.disabled = false;
        if (saveBtn) saveBtn.disabled = false;
      } else {
        if (modalBody) {
          alertEl = document.createElement("div");
          alertEl.id = "assignSubjectAlert";
          alertEl.className = "alert alert-info";
          alertEl.style.marginBottom = "10px";
          alertEl.textContent =
            data && data.message
              ? data.message
              : "No subjects are available for the active curriculum. Please wait for the latest Registrar data or contact the Registrar.";
          modalBody.insertBefore(alertEl, modalBody.firstChild);
        }
        subjectSelect.innerHTML =
          '<option value="">No subjects found for this section</option>';
        subjectSelect.disabled = true;
        if (saveBtn) saveBtn.disabled = true;
      }
    })
    .catch((error) => {
      console.error("Error loading subjects:", error);
      if (modalBody) {
        if (alertEl) {
          alertEl.remove();
        }
        alertEl = document.createElement("div");
        alertEl.id = "assignSubjectAlert";
        alertEl.className = "alert alert-warning";
        alertEl.style.marginBottom = "10px";
        alertEl.textContent = "Error loading subjects. Please try again later.";
        modalBody.insertBefore(alertEl, modalBody.firstChild);
      }
      subjectSelect.innerHTML =
        '<option value="">Error loading subjects</option>';
      subjectSelect.disabled = true;
      if (saveBtn) saveBtn.disabled = true;
    });
}

function closeAssignFacultyLoadModal() {
  closeModalById("assignFacultyLoadModal");
}

function openAssignFacultySubjectModal(
  facultyId,
  facultyName,
  teachingUnits,
  maxLoad,
  department,
) {
  document.getElementById("assignSubjectFacultyId").value = facultyId;
  document.getElementById("assignSubjectFacultyName").value = facultyName;
  document.getElementById("assignSubjectDepartment").value = department;
  document.getElementById("assignSubjectMaxLoad").value = maxLoad + " Units";
  document.getElementById("assignSubjectCurrentLoad").value =
    teachingUnits + " Units";
  document.getElementById("assignSubjectRemainingLoad").value =
    Math.max(0, maxLoad - teachingUnits) + " Units";

  const sectionSelect = document.getElementById("assignSubjectSection");
  const subjectSelect = document.getElementById("assignSubjectSubject");
  sectionSelect.innerHTML = '<option value="">Select Section</option>';
  subjectSelect.innerHTML = '<option value="">Select Subject</option>';
  subjectSelect.disabled = true;

  fetch(
    `/schoolm/modules/college-coor/api/get_instructor_sections.php?faculty_id=${facultyId}`,
  )
    .then((response) => response.json())
    .then((data) => {
      if (data.success && data.sections && data.sections.length > 0) {
        sectionSelect.innerHTML =
          '<option value="">Select Section</option>' +
          data.sections
            .map(
              (section) => `
                        <option value="${section.section_id}"
                            data-program-id="${section.program_id}"
                            data-year-level="${section.grade_level}"
                            data-semester-id="${section.semester_id}"
                            data-school-year-id="${section.school_year_id}">
                            ${section.section_code} | ${section.program_code || "N/A"} | ${getSectionYearLabel(section.grade_level)} | ${section.semester || "N/A"} | ${section.school_year || "N/A"}
                        </option>
                    `,
            )
            .join("");
        sectionSelect.disabled = false;
      } else {
        sectionSelect.innerHTML =
          '<option value="">No assigned instructor sections found</option>';
        sectionSelect.disabled = true;
      }
    })
    .catch((error) => {
      console.error("Error loading faculty sections:", error);
      sectionSelect.innerHTML =
        '<option value="">Error loading sections</option>';
      sectionSelect.disabled = true;
    });

  openModalById("assignFacultySubjectModal");
}

function closeAssignFacultySubjectModal() {
  closeModalById("assignFacultySubjectModal");
}

function loadSubjectsForAssignedSection() {
  const sectionId = document.getElementById("assignSubjectSection").value;
  const subjectSelect = document.getElementById("assignSubjectSubject");
  const saveBtn = document.querySelector(
    '#assignFacultySubjectForm button[type="submit"]',
  );
  const modalBody = document.querySelector(
    "#assignFacultySubjectModal .modal-body",
  );
  let alertEl = modalBody
    ? modalBody.querySelector("#assignSubjectAlert")
    : null;

  subjectSelect.innerHTML = '<option value="">Select Subject</option>';
  subjectSelect.disabled = true;

  if (!sectionId) {
    return;
  }

  fetch(
    `/schoolm/modules/college-coor/api/get_section_subjects.php?section_id=${sectionId}`,
  )
    .then((response) => response.json())
    .then((data) => {
      if (alertEl) {
        alertEl.remove();
        alertEl = null;
      }

      if (
        data &&
        data.success &&
        Array.isArray(data.subjects) &&
        data.subjects.length > 0
      ) {
        subjectSelect.innerHTML =
          '<option value="">Select Subject</option>' +
          data.subjects
            .map(
              (subject) => `
                        <option value="${subject.id}" data-code="${subject.code}" data-name="${subject.name}" data-units="${subject.units}">
                            ${subject.code} - ${subject.name} (${subject.units} units)
                        </option>
                    `,
            )
            .join("");
        subjectSelect.disabled = false;
        if (saveBtn) saveBtn.disabled = false;
      } else {
        if (modalBody) {
          alertEl = document.createElement("div");
          alertEl.id = "assignSubjectAlert";
          alertEl.className = "alert alert-info";
          alertEl.style.marginBottom = "10px";
          alertEl.textContent =
            data && data.message
              ? data.message
              : "No subjects are available for the active curriculum. Please wait for the latest Registrar data or contact the Registrar.";
          modalBody.insertBefore(alertEl, modalBody.querySelector("div"));
        }
        subjectSelect.innerHTML =
          '<option value="">No subjects found for this section</option>';
        subjectSelect.disabled = true;
        if (saveBtn) saveBtn.disabled = true;
      }
    })
    .catch((error) => {
      console.error("Error loading subjects:", error);
      if (modalBody) {
        if (alertEl) {
          alertEl.remove();
        }
        alertEl = document.createElement("div");
        alertEl.id = "assignSubjectAlert";
        alertEl.className = "alert alert-warning";
        alertEl.style.marginBottom = "10px";
        alertEl.textContent = "Error loading subjects. Please try again later.";
        modalBody.insertBefore(alertEl, modalBody.querySelector("div"));
      }
      subjectSelect.innerHTML =
        '<option value="">Error loading subjects</option>';
      subjectSelect.disabled = true;
      if (saveBtn) saveBtn.disabled = true;
    });
}

function addAssignmentToTemp() {
  const sectionSelect = document.getElementById("assignLoadSection");
  const subjectSelect = document.getElementById("assignLoadSubject");
  const sectionId = sectionSelect.value;
  const subjectId = subjectSelect.value;

  if (!sectionId || !subjectId) {
    alert("Please select both section and subject");
    return;
  }

  const sectionOption = sectionSelect.options[sectionSelect.selectedIndex];
  const sectionCode =
    sectionOption.getAttribute("data-code") || sectionOption.text || "N/A";

  const subjectOption = subjectSelect.options[subjectSelect.selectedIndex];
  const subjectCode = subjectOption.getAttribute("data-code") || "N/A";
  const subjectName = subjectOption.getAttribute("data-name") || "N/A";
  const units = parseInt(subjectOption.getAttribute("data-units")) || 1;

  const isDuplicate = tempAssignments.some(
    (item) => item.sectionId === sectionId && item.subjectId === subjectId,
  );

  if (isDuplicate) {
    alert("This subject is already assigned to this section");
    return;
  }

  tempAssignments.push({
    sectionId,
    sectionCode,
    subjectId,
    subjectCode,
    subjectName,
    units,
  });

  const tbody = document.getElementById("assignmentTableBody");
  const rowIndex = tempAssignments.length - 1;
  const row = document.createElement("tr");
  row.id = `assignmentRow_${rowIndex}`;
  row.innerHTML = `
        <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${sectionCode}</td>
        <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${subjectCode} - ${subjectName}</td>
        <td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6; font-weight: 600;">${units}</td>
        <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">
            <button type="button" class="btn btn-sm btn-danger" onclick="removeAssignmentFromTemp(${rowIndex})" style="padding: 4px 10px; font-size: 12px;">
                Remove
            </button>
        </td>
    `;
  tbody.appendChild(row);

  updateRunningTotal();

  document.getElementById("assignLoadSection").value = "";
  document.getElementById("assignLoadSubject").value = "";
}

function removeAssignmentFromTemp(index) {
  tempAssignments.splice(index, 1);

  const tbody = document.getElementById("assignmentTableBody");
  tbody.innerHTML = "";

  if (tempAssignments.length === 0) {
    tbody.innerHTML =
      '<tr><td colspan="4" style="padding: 20px; text-align: center; color: #6c757d;">No assignments added yet</td></tr>';
  } else {
    tempAssignments.forEach((assignment, idx) => {
      const row = document.createElement("tr");
      row.id = `assignmentRow_${idx}`;
      row.innerHTML = `
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.sectionCode}</td>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">${assignment.subjectCode} - ${assignment.subjectName}</td>
                <td style="padding: 12px; text-align: center; border-bottom: 1px solid #dee2e6; font-weight: 600;">${assignment.units}</td>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6; text-align: center;">
                    <button type="button" class="btn btn-sm btn-danger" onclick="removeAssignmentFromTemp(${idx})" style="padding: 4px 10px; font-size: 12px;">
                        Remove
                    </button>
                </td>
            `;
      tbody.appendChild(row);
    });
  }

  updateRunningTotal();
}

function updateRunningTotal() {
  const total = tempAssignments.reduce(
    (sum, assignment) => sum + assignment.units,
    0,
  );
  document.getElementById("runningTotalUnits").textContent = total;
}

// Form submissions
function initializeAllSearches() {
  attachTableSearch("searchPrograms", "programsTable");
  attachSubjectCatalogSearch();
  attachSectionSearch();
  attachTableSearch("searchFaculty", "facultyTable");
}

function attachTableSearch(searchInputId, tableId) {
  const searchInput = document.getElementById(searchInputId);
  const table = document.getElementById(tableId);

  if (!searchInput || !table) return;

  const tbody = table.querySelector("tbody");
  if (!tbody) return;

  const originalRows = Array.from(tbody.querySelectorAll("tr")).filter(
    (row) => !row.classList.contains("no-data"),
  );

  if (originalRows.length === 0) return;

  function performSearch() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const semesterSelect = document.getElementById("filterSectionSemester");
    const yearSelect = document.getElementById("filterSectionYear");
    const semesterFilter = semesterSelect ? semesterSelect.value : "";
    const yearFilter = yearSelect ? yearSelect.value : "";

    let visibleCount = 0;

    originalRows.forEach((row) => {
      const cells = row.querySelectorAll("td");

      let matchesSearch = false;
      if (!searchTerm) {
        matchesSearch = true;
      } else {
        matchesSearch = Array.from(cells).some((cell) =>
          cell.textContent.toLowerCase().includes(searchTerm),
        );
      }

      let matchesSemester = true;
      if (semesterFilter) {
        const cellSemester = (cells[3]?.textContent || "").toLowerCase();
        if (semesterFilter.toLowerCase().includes("1st")) {
          matchesSemester = cellSemester.includes("1st");
        } else if (semesterFilter.toLowerCase().includes("2nd")) {
          matchesSemester = cellSemester.includes("2nd");
        } else {
          matchesSemester = cellSemester.includes(semesterFilter.toLowerCase());
        }
      }

      let matchesYear = true;
      if (yearFilter) {
        const cellYear = (cells[2]?.textContent || "").toLowerCase();
        matchesYear = cellYear.includes(yearFilter.toLowerCase());
      }

      const visible = matchesSearch && matchesSemester && matchesYear;
      row.style.display = visible ? "" : "none";
      if (visible) visibleCount++;
    });

    if (visibleCount === 0 && (searchTerm || semesterFilter || yearFilter)) {
      const noDataRow = tbody.querySelector("tr.no-data");
      if (!noDataRow) {
        const newRow = document.createElement("tr");
        newRow.className = "no-data";
        const cellCount = originalRows[0]?.querySelectorAll("td").length || 6;
        let message = "No results found";
        if (searchTerm) message = `No results found for "${searchTerm}"`;
        newRow.innerHTML = `<td colspan="${cellCount}" style="text-align: center; padding: 20px; color: #999;">${message}</td>`;

        originalRows.forEach((row) => tbody.removeChild(row));
        tbody.appendChild(newRow);

        tbody.dataset.noDataRow = "true";
      }
    } else if (visibleCount > 0) {
      const noDataRow = tbody.querySelector("tr.no-data");
      if (noDataRow) noDataRow.remove();

      tbody.innerHTML = "";
      const filteredRows = originalRows.filter((row) => {
        const cells = row.querySelectorAll("td");
        let matchesSearch =
          !searchTerm ||
          Array.from(cells).some((cell) =>
            cell.textContent.toLowerCase().includes(searchTerm),
          );

        let matchesSemester = true;
        if (semesterFilter) {
          const cellSemester = (cells[3]?.textContent || "").toLowerCase();
          if (semesterFilter.toLowerCase().includes("1st"))
            matchesSemester = cellSemester.includes("1st");
          else if (semesterFilter.toLowerCase().includes("2nd"))
            matchesSemester = cellSemester.includes("2nd");
          else
            matchesSemester = cellSemester.includes(
              semesterFilter.toLowerCase(),
            );
        }

        let matchesYear = true;
        if (yearFilter) {
          const cellYear = (cells[2]?.textContent || "").toLowerCase();
          matchesYear = cellYear.includes(yearFilter.toLowerCase());
        }

        return matchesSearch && matchesSemester && matchesYear;
      });
      filteredRows.forEach((row) => tbody.appendChild(row));
    }
  }

  searchInput.addEventListener("keyup", performSearch);
  searchInput.addEventListener("change", performSearch);

  const semSel = document.getElementById("filterSectionSemester");
  const yearSel = document.getElementById("filterSectionYear");
  if (semSel) semSel.addEventListener("change", performSearch);
  if (yearSel) yearSel.addEventListener("change", performSearch);
}

function attachSectionSearch() {
  const searchInput = document.getElementById("searchSections");
  const table = document.getElementById("sectionsTable");
  const programSelect = document.getElementById("filterSectionProgram");
  const semesterSelect = document.getElementById("filterSectionSemester");
  const yearSelect = document.getElementById("filterSectionYear");
  const resetButton = document.getElementById("resetSectionFilters");

  if (!searchInput || !table) return;

  const tbody = table.querySelector("tbody");
  if (!tbody) return;

  const originalRows = Array.from(tbody.querySelectorAll("tr")).filter(
    (row) => !row.classList.contains("no-data"),
  );

  function performSearch() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const programValue = programSelect?.value.toLowerCase().trim() || "";
    const semesterValue = semesterSelect?.value.toLowerCase().trim() || "";
    const yearValue = yearSelect?.value.toLowerCase().trim() || "";

    let visibleCount = 0;
    originalRows.forEach((row) => {
      const data = row.dataset;
      const rowText = row.textContent.toLowerCase();
      const matchesSearch = !searchTerm || rowText.includes(searchTerm);
      const matchesProgram =
        !programValue ||
        (data.program || "").toLowerCase().includes(programValue);
      const matchesSemester =
        !semesterValue ||
        (data.semester || "").toLowerCase().includes(semesterValue);
      const matchesYear =
        !yearValue || (data.yearLevel || "").toLowerCase().includes(yearValue);
      const visible =
        matchesSearch && matchesProgram && matchesSemester && matchesYear;

      row.style.display = visible ? "" : "none";
      if (visible) visibleCount++;
    });

    const noDataRow = tbody.querySelector("tr.no-data");
    if (visibleCount === 0) {
      if (!noDataRow) {
        const newRow = document.createElement("tr");
        newRow.className = "no-data";
        newRow.innerHTML =
          '<td colspan="8" class="text-center text-muted" style="padding: 20px;">No sections match the current filters.</td>';
        tbody.appendChild(newRow);
      }
    } else if (noDataRow) {
      noDataRow.remove();
    }
  }

  searchInput.addEventListener("keyup", performSearch);
  searchInput.addEventListener("change", performSearch);
  [programSelect, semesterSelect, yearSelect].forEach((select) => {
    if (select) select.addEventListener("change", performSearch);
  });

  if (resetButton) {
    resetButton.addEventListener("click", () => {
      searchInput.value = "";
      if (programSelect) programSelect.value = "";
      if (semesterSelect) semesterSelect.value = "";
      if (yearSelect) yearSelect.value = "";
      performSearch();
    });
  }
}

function attachSubjectCatalogSearch() {
  const searchInput = document.getElementById("searchSubjects");
  const table = document.getElementById("subjectsTable");
  const courseFilter = document.getElementById("filterSubjectCourse");
  const yearFilter = document.getElementById("filterSubjectYear");
  const semesterFilter = document.getElementById("filterSubjectSemester");
  const statusFilter = document.getElementById("filterSubjectStatus");
  const resetButton = document.getElementById("resetSubjectFilters");

  if (!searchInput || !table) return;
  const tbody = table.querySelector("tbody");
  if (!tbody) return;
  const originalRows = Array.from(tbody.querySelectorAll("tr")).filter(
    (row) => !row.classList.contains("no-data"),
  );

  function performSearch() {
    const term = searchInput.value.toLowerCase().trim();
    const courseValue = courseFilter ? courseFilter.value.toLowerCase() : "";
    const yearValue = yearFilter ? yearFilter.value.toLowerCase() : "";
    const semValue = semesterFilter ? semesterFilter.value.toLowerCase() : "";
    const statusValue = statusFilter ? statusFilter.value.toLowerCase() : "";

    let visibleCount = 0;
    tbody.innerHTML = "";

    originalRows.forEach((row) => {
      const cells = Array.from(row.querySelectorAll("td"));
      const rowText = cells
        .map((cell) => cell.textContent.toLowerCase())
        .join(" ");
      const matchesSearch = !term || rowText.includes(term);
      const matchesCourse = !courseValue || rowText.includes(courseValue);
      const yearText = (() => {
        const year = row.dataset.yearLevel || "";
        if (year === "1") return "1st";
        if (year === "2") return "2nd";
        if (year === "3") return "3rd";
        if (year === "4") return "4th";
        return year.toLowerCase();
      })();
      const matchesYear = !yearValue || yearText.includes(yearValue);
      const matchesSemester =
        !semValue || row.dataset.semester.toLowerCase().includes(semValue);
      const matchesStatus =
        !statusValue || row.dataset.status.toLowerCase() === statusValue;

      if (
        matchesSearch &&
        matchesCourse &&
        matchesYear &&
        matchesSemester &&
        matchesStatus
      ) {
        tbody.appendChild(row);
        visibleCount += 1;
      }
    });

    if (visibleCount === 0) {
      const noDataRow = document.createElement("tr");
      noDataRow.className = "no-data";
      noDataRow.innerHTML =
        '<td colspan="10" style="text-align: center; padding: 20px; color: #999;">No subjects match the active filters.</td>';
      tbody.appendChild(noDataRow);
    }
  }

  searchInput.addEventListener("input", performSearch);
  if (courseFilter) courseFilter.addEventListener("change", performSearch);
  if (yearFilter) yearFilter.addEventListener("change", performSearch);
  if (semesterFilter) semesterFilter.addEventListener("change", performSearch);
  if (statusFilter) statusFilter.addEventListener("change", performSearch);
  if (resetButton) {
    resetButton.addEventListener("click", () => {
      if (searchInput) searchInput.value = "";
      if (courseFilter) courseFilter.value = "";
      if (yearFilter) yearFilter.value = "";
      if (semesterFilter) semesterFilter.value = "";
      if (statusFilter) statusFilter.value = "";
      performSearch();
    });
  }
}

// Faculty load dynamic loading and auto-refresh
function escapeHtml(text) {
  if (!text) return "";
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function calculateLoadStatus(loadStatusString) {
  const badgeMap = {
    Underloaded: "badge-warning",
    "Fully Loaded": "badge-success",
    Overloaded: "badge-danger",
  };
  const status = loadStatusString || "Underloaded";
  return { status: status, badge: badgeMap[status] || "badge-secondary" };
}

function loadFacultyLoadData() {
  const tbody = document.getElementById("facultyTable")?.querySelector("tbody");
  if (!tbody) return;

  tbody.innerHTML =
    '<tr><td colspan="8" class="text-center text-muted"><i class="fas fa-spinner fa-spin"></i> Loading faculty load...</td></tr>';

  fetch("/schoolm/modules/college-coor/api/get_faculty_load.php")
    .then((response) => {
      if (!response.ok) throw new Error("API request failed");
      return response.json();
    })
    .then((facultyLoads) => {
      if (!facultyLoads || facultyLoads.length === 0) {
        tbody.innerHTML =
          '<tr><td colspan="8" class="text-center text-muted">No faculty loads found</td></tr>';
        return;
      }

      tbody.innerHTML = facultyLoads
        .map((fac) => {
          const assignedSections = parseInt(fac.assigned_sections) || 0;
          const assignedSubjects = parseInt(fac.assigned_subjects) || 0;
          const teachingUnits = parseInt(fac.teaching_units) || 0;
          const maxLoad = parseInt(fac.max_load) || 15;
          const loadStatusData = calculateLoadStatus(fac.load_status);

          return `
                    <tr data-faculty-id="${fac.id}" data-faculty-name="${escapeHtml(fac.faculty_name)}" data-max-load="${maxLoad}" data-teaching-units="${teachingUnits}" data-assigned-sections="${assignedSections}" data-assigned-subjects="${assignedSubjects}" data-department="${escapeHtml(fac.department)}">
                        <td>${escapeHtml(fac.faculty_name)}</td>
                        <td>${escapeHtml(fac.department)}</td>
                        <td class="faculty-assigned-sections">${assignedSections}</td>
                        <td class="faculty-assigned-subjects">${assignedSubjects}</td>
                        <td class="faculty-teaching-units">${teachingUnits}</td>
                        <td>${maxLoad}</td>
                        <td class="faculty-load-status"><span class="badge ${loadStatusData.badge}">${loadStatusData.status}</span></td>
                        <td>
                            <div class="actions">
                                <button class="btn btn-sm btn-info" onclick="openAssignFacultyLoadModal('${fac.id}', '${escapeHtml(fac.faculty_name)}', '${teachingUnits}', '${maxLoad}', '${escapeHtml(fac.department)}')">
                                    Assign Instructor
                                </button>
                                <button class="btn btn-sm btn-info" onclick="openAssignFacultySubjectModal('${fac.id}', '${escapeHtml(fac.faculty_name)}', '${teachingUnits}', '${maxLoad}', '${escapeHtml(fac.department)}')">
                                    Assign Subjects
                                </button>
                                <button class="btn btn-sm btn-info" onclick="viewFacultyLoadDetails('${fac.id}', '${escapeHtml(fac.faculty_name)}', '${teachingUnits}', '${maxLoad}', '${assignedSections}', '${assignedSubjects}', '${escapeHtml(fac.department)}')">
                                    View Details
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
        })
        .join("");
    })
    .catch((error) => {
      console.error("Error loading faculty load:", error);
      tbody.innerHTML =
        '<tr><td colspan="8" class="text-center text-danger">Failed to load faculty load data</td></tr>';
    });
}

function updateFacultyLoadValues(facultyId) {
  const row = document.querySelector(`tr[data-faculty-id="${facultyId}"]`);
  if (!row) return;

  fetch(
    `/schoolm/modules/college-coor/api/get_faculty_load.php?faculty_id=${facultyId}`,
  )
    .then((response) => response.json())
    .then((data) => {
      if (data && (Array.isArray(data) ? data[0] : data)) {
        const fac = Array.isArray(data) ? data[0] : data;
        const classesAssigned = parseInt(fac.classes_assigned) || 0;
        const totalUnits = parseInt(fac.total_units) || 0;
        const maxLoad = parseInt(fac.max_load) || 15;
        const loadStatusData = calculateLoadStatus(fac.load_status);

        const classesCell = row.querySelector(".faculty-classes-assigned");
        if (classesCell) classesCell.textContent = classesAssigned;

        const totalUnitsCell = row.querySelector(".faculty-total-units");
        if (totalUnitsCell) totalUnitsCell.textContent = totalUnits;

        const statusCell = row.querySelector(".faculty-load-status");
        if (statusCell) {
          statusCell.innerHTML = `<span class="badge ${loadStatusData.badge}">${loadStatusData.status}</span>`; 
        }
      }
    })
    .catch((error) =>
      console.error("Error updating faculty load values:", error),
    );
}

function refreshFacultyLoad() {
  loadFacultyLoadData();
}

document.addEventListener("DOMContentLoaded", function () {
  setTimeout(initializeAllSearches, 100);
  initAcademicsManagementBindings();
  loadFacultyLoadData();
});

// facultyTab convenience listener removed; loadFacultyLoadData is triggered via page:loaded

window.addEventListener("page:loaded", function (e) {
  if (e.detail && e.detail.page === "academics-management") {
    setTimeout(initializeAllSearches, 100);
    initAcademicsManagementBindings();
    loadFacultyLoadData();
  }
});

window.addEventListener("page:loaded", function (e) {
  if (e.detail && e.detail.page === "class-scheduling") {
    sessionStorage.setItem("refreshFacultyLoad", "true");
  }
});

window.addEventListener("page:loaded", function (e) {
  if (e.detail && e.detail.page === "academics-management") {
    if (sessionStorage.getItem("refreshFacultyLoad") === "true") {
      sessionStorage.removeItem("refreshFacultyLoad");
      setTimeout(loadFacultyLoadData, 100);
    }
  }
});
// element-specific bindings moved to initAcademicsManagementBindings()

function bindOnce(el, flag, event, handler) {
  if (!el) return;
  const key = `bound_${flag}`;
  if (el.dataset[key]) return;
  el.dataset[key] = "true";
  el.addEventListener(event, handler);
}

function initAcademicsManagementBindings() {
  // assignSubjectSection change
  bindOnce(
    document.getElementById("assignSubjectSection"),
    "assignSubjectSection_change",
    "change",
    loadSubjectsForAssignedSection,
  );

  // sectionSchoolYear change
  bindOnce(
    document.getElementById("sectionSchoolYear"),
    "sectionSchoolYear_change",
    "change",
    loadSectionSemesters,
  );

  // sectionForm submit
  bindOnce(
    document.getElementById("sectionForm"),
    "sectionForm_submit",
    "submit",
    function (e) {
      e.preventDefault();

      const saveButton = document.getElementById("saveSectionBtn");
      const sectionCode = document.getElementById("sectionCode").value.trim();
      const gradeLevel = document.getElementById("sectionGradeLevel").value.trim();
      const programId = document.getElementById("sectionProgram").value;
      const schoolYearId = document.getElementById("sectionSchoolYear").value;
      const semesterId = document.getElementById("sectionSemester").value;

      if (
        !sectionCode ||
        !gradeLevel ||
        !programId ||
        !schoolYearId ||
        !semesterId
      ) {
        alert("Please fill in all required fields");
        return;
      }

      saveButton.disabled = true;

      const formData = new FormData();
      formData.append("section_code", sectionCode);
      formData.append("grade_level", gradeLevel);
      formData.append("program_id", programId);
      formData.append("school_year_id", schoolYearId);
      formData.append("semester_id", semesterId);

      fetch("/schoolm/modules/college-coor/api/add_section.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            alert(data.message);
            closeSectionModal();
            location.reload();
          } else {
            alert("Error: " + (data.message || "Failed to add section"));
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("An error occurred while adding the section");
        })
        .finally(() => {
          saveButton.disabled = false;
        });
    },
  );

  // adviserForm submit
  bindOnce(
    document.getElementById("adviserForm"),
    "adviserForm_submit",
    "submit",
    function (e) {
      e.preventDefault();

      const sectionId = document.getElementById("adviserSectionId").value;
      const adviserSelect = document.getElementById("adviserSelect");
      const facultyId =
        adviserSelect.options[adviserSelect.selectedIndex].getAttribute(
          "data-id",
        );

      if (!sectionId || !facultyId) {
        alert("Please select a valid adviser");
        return;
      }

      const formData = new FormData();
      formData.append("section_id", sectionId);
      formData.append("faculty_id", facultyId);

      fetch("/schoolm/modules/college-coor/api/assign_adviser.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error(
              "Network response was not ok - Status: " + response.status,
            );
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            alert(data.message);
            closeAdviserModal();
            location.reload();
          } else {
            alert("Error: " + data.message);
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Error assigning adviser: " + error.message);
        });
    },
  );

  // facultyLoadForm submit
  bindOnce(
    document.getElementById("facultyLoadForm"),
    "facultyLoadForm_submit",
    "submit",
    function (e) {
      e.preventDefault();
      alert("Faculty load assigned successfully");

      setTimeout(() => {
        loadFacultyLoadData();
      }, 500);

      closeFacultyLoadModal();
    },
  );
  // assignFacultySubjectForm submit
  bindOnce(
    document.getElementById("assignFacultySubjectForm"),
    "assignFacultySubjectForm_submit",
    "submit",
    function (e) {
      e.preventDefault(); // stops the redirect to the dashboard

      const facultyId = document.getElementById("assignSubjectFacultyId").value;
      const sectionSel = document.getElementById("assignSubjectSection");
      const subjectSel = document.getElementById("assignSubjectSubject");
      const sectionId = sectionSel.value;
      const subjectId = subjectSel.value;

      if (!facultyId || !sectionId || !subjectId) {
        alert("Please select both a section and a subject");
        return;
      }

      const sectionOpt = sectionSel.options[sectionSel.selectedIndex];
      const submitBtn = this.querySelector('button[type="submit"]');
      if (submitBtn) submitBtn.disabled = true;

      const formData = new FormData();
      formData.append("faculty_id", facultyId);
      formData.append("section_id", sectionId);
      formData.append("subject_id", subjectId);
      formData.append("semester_id", sectionOpt.dataset.semesterId || "");
      formData.append("school_year_id", sectionOpt.dataset.schoolYearId || "");

      // TODO: replace with your real endpoint

      fetch(
        "/schoolm/modules/college-coor/api/assign_subject_to_faculty.php",
        {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            faculty_id: parseInt(facultyId, 10),
            assignments: [
              {
                section_id: parseInt(sectionId, 10),
                subject_id: parseInt(subjectId, 10),
              },
            ],
          }),
        },
      )
        .then((response) => response.json()) // the API sends JSON even on 400/401, so read it
        .then((data) => {
          if (data.success) {
            alert(data.message || "Subject assigned successfully");
            closeAssignFacultySubjectModal();
            loadFacultyLoadData();
          } else {
            alert("Error: " + (data.message || "Failed to assign subject"));
          }
        })
        .catch((error) => {
          console.error("Error assigning subject:", error);
          alert("Error assigning subject: " + error.message);
        })
        .finally(() => {
          if (submitBtn) submitBtn.disabled = false;
        });
    },
  );
}

// element-specific bindings are initialized via initAcademicsManagementBindings()

window.addEventListener("click", (e) => {
  if (e.target.classList.contains("modal")) {
    e.target.classList.remove("show");
  }
});

function exposeGlobal(name, value) {
  try {
    window[name] = value;
  } catch (e) {
    console.error(`Failed to expose "${name}" to window:`, e);
  }
}

// Expose globals individually so a missing identifier doesn't break all exposures
exposeGlobal("ACTIVE_TAB_KEY", ACTIVE_TAB_KEY);
exposeGlobal("switchToTab", switchToTab);
exposeGlobal("closeModalById", closeModalById);
exposeGlobal("openModalById", openModalById);
exposeGlobal("viewCurriculumDetails", viewCurriculumDetails);
exposeGlobal("closeCurriculumModal", closeCurriculumModal);
exposeGlobal("viewSubjectDetails", viewSubjectDetails);
exposeGlobal("closeSubjectModal", closeSubjectModal);
exposeGlobal("loadSectionSchoolYears", loadSectionSchoolYears);
exposeGlobal("loadSectionSemesters", loadSectionSemesters);
exposeGlobal("openAddSectionModal", openAddSectionModal);
exposeGlobal("closeSectionModal", closeSectionModal);
exposeGlobal("assignAdviser", assignAdviser);
exposeGlobal("closeAdviserModal", closeAdviserModal);
exposeGlobal("openAssignAdviserModal", openAssignAdviserModal);
exposeGlobal("viewSectionDetails", viewSectionDetails);
exposeGlobal("closeSectionDetailsModal", closeSectionDetailsModal);
exposeGlobal("viewSectionStudents", viewSectionStudents);
exposeGlobal("closeSectionStudentsModal", closeSectionStudentsModal);
exposeGlobal("openAddFacultyLoadModal", openAddFacultyLoadModal);
exposeGlobal("editFacultyLoad", editFacultyLoad);
exposeGlobal("deleteFacultyLoad", deleteFacultyLoad);
exposeGlobal("closeFacultyLoadModal", closeFacultyLoadModal);
exposeGlobal("viewFacultyLoadDetails", viewFacultyLoadDetails);
exposeGlobal("closeFacultyLoadDetailsModal", closeFacultyLoadDetailsModal);
exposeGlobal(
  "initializeAssignLoadModalEvents",
  initializeAssignLoadModalEvents,
);
exposeGlobal("openAssignFacultyLoadModal", openAssignFacultyLoadModal);
exposeGlobal("loadSchoolYears", loadSchoolYears);
exposeGlobal("loadSemestersForSchoolYear", loadSemestersForSchoolYear);
exposeGlobal("getSectionYearLabel", getSectionYearLabel);
exposeGlobal("loadSectionsForAssignment", loadSectionsForAssignment);
exposeGlobal("loadSubjectsForSection", loadSubjectsForSection);
exposeGlobal("closeAssignFacultyLoadModal", closeAssignFacultyLoadModal);
exposeGlobal("openAssignFacultySubjectModal", openAssignFacultySubjectModal);
exposeGlobal("closeAssignFacultySubjectModal", closeAssignFacultySubjectModal);
exposeGlobal("loadSubjectsForAssignedSection", loadSubjectsForAssignedSection);
exposeGlobal("addAssignmentToTemp", addAssignmentToTemp);
exposeGlobal("removeAssignmentFromTemp", removeAssignmentFromTemp);
exposeGlobal("updateRunningTotal", updateRunningTotal);
exposeGlobal("loadFacultyLoadData", loadFacultyLoadData);
exposeGlobal("updateFacultyLoadValues", updateFacultyLoadValues);
exposeGlobal("refreshFacultyLoad", refreshFacultyLoad);
exposeGlobal("initializeAllSearches", initializeAllSearches);
exposeGlobal("attachTableSearch", attachTableSearch);
exposeGlobal("attachSectionSearch", attachSectionSearch);
exposeGlobal("attachSubjectCatalogSearch", attachSubjectCatalogSearch);