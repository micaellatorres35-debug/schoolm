  // ─── Department Select Helper (exported for role.js and position.js) ──────────

  export function getFreshDepartmentSelect() {
      const departmentSelect = document.getElementById('department');
      if (!departmentSelect) return null;

      if (departmentSelect.dataset.init) return departmentSelect;

      const fresh = departmentSelect.cloneNode(true);
      fresh.dataset.init = 'true';
      departmentSelect.parentNode.replaceChild(fresh, departmentSelect);
      return fresh;
  }

  // The scheduling page is injected with innerHTML during page navigation, so
  // handle Faculty Load changes once at document level.
  document.addEventListener('change', function (event) {
      if (event.target.id !== 'faculty_load_id') return;

        const facultyLoad = event.target;
        const selectedOption = facultyLoad.selectedOptions[0];
        const subjectSelect = document.getElementById('subject_id');
        const sectionSelect = document.getElementById('section_id');
        const facultySelect = document.getElementById('faculty_id');
        const semesterSelect = document.getElementById('semester_id');
        const schoolYearSelect = document.getElementById('school_year_id');

      console.log('Selected Faculty Load:', selectedOption?.value);
      console.log('Faculty:', selectedOption?.dataset.faculty);
      console.log('Subject:', selectedOption?.dataset.subject);
      console.log('Section:', selectedOption?.dataset.section);
      console.log('Semester:', selectedOption?.dataset.semester);
      console.log('School Year:', selectedOption?.dataset.schoolYear);
      console.log(subjectSelect);
      console.log(sectionSelect);
      console.log(facultySelect);
      console.log(semesterSelect);
      console.log(schoolYearSelect);

        if (!selectedOption || !selectedOption.value || !subjectSelect || !sectionSelect || !facultySelect || !semesterSelect || !schoolYearSelect) return;

        const subjectId = selectedOption.dataset.subject || '';
        const sectionId = selectedOption.dataset.section || '';
        const facultyId = selectedOption.dataset.faculty || '';
        const semesterId = selectedOption.dataset.semester || '';
        const schoolYearId = selectedOption.dataset.schoolYear || '';

        subjectSelect.value = subjectId;
        sectionSelect.value = sectionId;
        facultySelect.value = facultyId;
        semesterSelect.value = semesterId;
        schoolYearSelect.value = schoolYearId;

          // These values are controlled by Faculty Load. They remain visible,
          // but cannot be changed independently after a load is selected.
          [subjectSelect, sectionSelect, facultySelect, semesterSelect, schoolYearSelect].forEach(function (select) {
            select.disabled = true;
            select.setAttribute('aria-readonly', 'true');
          });

      console.log(subjectSelect.value);
      console.log(sectionSelect.value);
      console.log(facultySelect.value);
      console.log(semesterSelect.value);
      console.log(schoolYearSelect.value);

        document.querySelectorAll('#subject_id option').forEach(function (option) {
          console.log(option.value, option.text);
        });
        document.querySelectorAll('#section_id option').forEach(function (option) {
          console.log(option.value, option.text);
        });
        document.querySelectorAll('#faculty_id option').forEach(function (option) {
          console.log(option.value, option.text);
        });

      if (window.jQuery) {
          $('#subject_id').val(subjectId).trigger('change');
          $('#section_id').val(sectionId).trigger('change');
          $('#faculty_id').val(facultyId).trigger('change');
          $('#semester_id').val(semesterId).trigger('change');
          $('#school_year_id').val(schoolYearId).trigger('change');
        }
        });

        // The scheduling page is injected dynamically and initForms() may clone
        // its form. Delegate Schedule Type changes from document so replacing
        // #schedule_type cannot remove this handler.
        document.addEventListener('change', function (event) {
          const isAddType = event.target.id === 'schedule_type';
          const isEditType = event.target.id === 'edit_schedule_type';
          if (!isAddType && !isEditType) return;

          const prefix = isEditType ? 'edit_' : '';
          const isBreak = event.target.value === 'Break Time';
          const room = document.getElementById(prefix + 'room_id');
          const facultyLoad = document.getElementById(prefix + 'faculty_load_id');
          const faculty = document.getElementById(prefix + 'faculty_id');
          const subject = document.getElementById(prefix + 'subject_id');
          const section = document.getElementById(prefix + (isEditType ? 'grade_section_id' : 'section_id'));
          const semester = document.getElementById(prefix + 'semester_id');
          const schoolYear = document.getElementById(prefix + 'school_year_id');
          const facultyLoadField = isAddType ? document.getElementById('add-faculty-load-field') : null;

          console.log('[Schedule UI] delegated Schedule Type change', {
            prefix: prefix,
            isBreak: isBreak,
            scheduleType: event.target,
            facultyLoadField: facultyLoadField,
            facultyLoad: faculty,
            assignedFaculty: faculty
          });

          if (facultyLoadField) {
            facultyLoadField.classList.toggle('is-hidden', isBreak);
          }
          if (room) {
            // Break Time may optionally use a room; keep its value submittable.
            room.disabled = false;
            room.required = !isBreak;
          }
          if (facultyLoad) {
            facultyLoad.disabled = isBreak;
            facultyLoad.required = !isBreak;
          }
          if (faculty) {
            // Break Time chooses a faculty directly. Class faculty is derived
            // from Faculty Load and therefore remains read-only.
            faculty.disabled = !isBreak;
            faculty.required = isBreak;
            faculty.classList.toggle('readonly-select', !isBreak);
            faculty.setAttribute('aria-readonly', isBreak ? 'false' : 'true');
            faculty.tabIndex = isBreak ? 0 : -1;
          }
          if (subject) {
            subject.disabled = true;
            subject.required = false;
          }
          if (section) {
            section.disabled = true;
            section.required = false;
          }
          if (semester) {
            semester.disabled = false;
            semester.required = true;
            semester.classList.toggle('readonly-select', isBreak);
          }
          if (schoolYear) {
            schoolYear.disabled = false;
            schoolYear.required = true;
            schoolYear.classList.toggle('readonly-select', isBreak);
          }

          if (isAddType && isBreak) {
            if (facultyLoad) facultyLoad.value = '';
            if (subject) subject.value = '';
            if (section) section.value = '';
            if (semester) semester.value = String(window.activeSemesterId || semester.value);
            if (schoolYear) schoolYear.value = String(window.activeSchoolYearId || schoolYear.value);
            if (faculty) {
              // Only rebuild the faculty options if we have a populated list.
              var hasFacultyList = Array.isArray(window.scheduleAllFaculty) && window.scheduleAllFaculty.length;
              if (hasFacultyList) {
                faculty.innerHTML = '<option value="">Select Faculty</option>';
                window.scheduleAllFaculty.forEach(function (facultyItem) {
                  const option = document.createElement('option');
                  option.value = facultyItem.id;
                  option.textContent = facultyItem.last_name + ', ' + facultyItem.first_name + ' (' + facultyItem.faculty_code + ')';
                  faculty.appendChild(option);
                });
                faculty.value = '';
              } else {
                // Keep server-rendered options if no client-side list is available.
                console.warn('scheduleAllFaculty not available; keeping existing faculty options');
              }
            }
          }
        });

          window.addEventListener('page:loaded', function (event) {
              if (event.detail?.page !== 'class-scheduling') return;

              // If the server embedded a preloaded faculty JSON blob, parse it
              // and populate window.scheduleAllFaculty so dynamic handlers can use it.
              try {
                const blob = document.getElementById('preload-scheduleAllFaculty');
                if (blob && blob.textContent) {
                  try {
                    window.scheduleAllFaculty = JSON.parse(blob.textContent);
                    console.log('Loaded preload scheduleAllFaculty:', window.scheduleAllFaculty);
                  } catch (err) {
                    console.error('Failed to parse preload-scheduleAllFaculty JSON', err);
                  }
                }
              } catch (e) {
                // ignore
              }

              const scheduleType = document.getElementById('schedule_type');
              if (scheduleType) scheduleType.dispatchEvent(new Event('change'));

              const editScheduleType = document.getElementById('edit_schedule_type');
              if (editScheduleType) editScheduleType.dispatchEvent(new Event('change'));
          });

  // ─── Main ─────────────────────────────────────────────────────────────────────

  document.addEventListener('DOMContentLoaded', function () {

    // ─── Page Fetching ───────────────────────────────────────────────────────────

    function fetchPage(page, push = true) {
      const container = document.querySelector('.container');
      if (!container) return;

      fetch(`page-loader.php?page=${encodeURIComponent(page)}`, { credentials: 'same-origin' })
        .then(function (response) {
          if (!response.ok) throw new Error('Network error');
          const rendered = response.headers.get('X-Rendered-Page') || page;
          return response.text().then(function (html) {
            return { html: html, rendered: rendered };
          });
        })
        .then(function (result) {
          container.innerHTML = result.html;
          updateActiveLink(result.rendered);

          if (push) {
            history.pushState({ page: result.rendered }, '', '?page=' + encodeURIComponent(result.rendered));
          }

          reinitPage(result.rendered);
        })
        .catch(function (err) {
          console.error('Page switch failed', err);
        });
    }

    // ─── Active Link ─────────────────────────────────────────────────────────────

    function updateActiveLink(page) {
      document.querySelectorAll('.menu-link, .active-menu-link').forEach(function (el) {
        el.className = 'menu-link';
      });
      var a = document.querySelector('[data-page="' + page + '"]');
      if (a) a.className = 'active-menu-link';
    }

    // ─── Reinitialize Page Scripts ────────────────────────────────────────────────

    function reinitPage(page) {
      initTabs();
      initForms();
      attachAssignSectionsFormHandler();

      window.dispatchEvent(new CustomEvent('page:loaded', { detail: { page: page } }));
    }

    function attachAssignSectionsFormHandler() {
      const assignSectionsForm = document.getElementById('assignFacultyLoadForm');
      if (!assignSectionsForm) return;
      if (assignSectionsForm.dataset.customSubmitAttached) return;

      assignSectionsForm.dataset.customSubmitAttached = 'true';
      console.log('Custom Assign Sections handler attached:', assignSectionsForm);

      assignSectionsForm.addEventListener('submit', async function (e) {
        console.log('Assign Sections submit event fired');
        e.preventDefault();

        const facultyId = document.getElementById('assignLoadFacultyId')?.value;
        const schoolYearId = document.getElementById('assignLoadSchoolYear')?.value;
        const semesterId = document.getElementById('assignLoadSemester')?.value;
        const sectionSelect = document.getElementById('assignLoadSection');
        const selectedSections = sectionSelect ? Array.from(sectionSelect.selectedOptions).map(function (opt) { return opt.value; }).filter(Boolean) : [];

        if (!facultyId) {
          alert('Please select a faculty member');
          return;
        }
        if (!schoolYearId) {
          alert('Please select School Year');
          return;
        }
        if (!semesterId) {
          alert('Please select Semester');
          return;
        }
        if (!selectedSections.length) {
          alert('Please select at least one section');
          return;
        }

        try {
          const duplicateCheckResponse = await fetch(`/modules/college-coor/api/get_faculty_sections.php?faculty_id=${facultyId}`);
          const duplicateCheckData = await duplicateCheckResponse.json();
          if (duplicateCheckData.success && Array.isArray(duplicateCheckData.sections)) {
            const activeDuplicates = duplicateCheckData.sections.filter(section => {
              return section.role === 'Instructor'
                && (section.status === 'Active' || section.status === null || section.status === undefined)
                && section.school_year_id == schoolYearId
                && section.semester_id == semesterId
                && selectedSections.includes(section.section_id.toString());
            });

            if (activeDuplicates.length > 0) {
              alert('This faculty is already assigned to this section for the selected school year and semester.');
              return;
            }
          }
        } catch (checkError) {
          console.error('Error checking duplicate assignments:', checkError);
        }

        const payload = {
          faculty_id: facultyId,
          school_year_id: schoolYearId,
          semester_id: semesterId,
          section_ids: selectedSections
        };

        fetch('/modules/college-coor/api/add_section_faculty.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          credentials: 'same-origin',
          body: JSON.stringify(payload)
        })
          .then(function (response) {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
          })
          .then(function (data) {
            if (data.success) {
              alert('Section assignments saved successfully');
              if (typeof window.closeAssignFacultyLoadModal === 'function') {
                window.closeAssignFacultyLoadModal();
              }
              if (typeof window.loadFacultyLoadData === 'function') {
                window.loadFacultyLoadData();
              } else {
                location.reload();
              }
            } else {
              alert('Error: ' + (data.message || 'Failed to save section assignments'));
            }
          })
          .catch(function (err) {
            console.error('Error saving section assignments:', err);
            alert('Error saving section assignments: ' + err.message);
          });
      });
    }

    // ─── Tab Switcher ─────────────────────────────────────────────────────────────

    function initTabs() {
      const tabItems = document.querySelectorAll('.tab-item');
      const tabContents = document.querySelectorAll('.tab-content');

      if (!tabItems.length) return;

      tabItems.forEach(function (tab) {
        tab.addEventListener('click', function () {
          tabItems.forEach(function (t) { t.classList.remove('active'); });
          tabContents.forEach(function (c) { c.classList.remove('active'); });

          tab.classList.add('active');
          const target = document.getElementById(tab.getAttribute('data-tab'));
          if (target) target.classList.add('active');
        });
      });
    }

    // ─── Form Submissions ─────────────────────────────────────────────────────────

    function initForms() {
      const forms = document.querySelectorAll('form:not([data-custom-submit])');

      forms.forEach(function (form) {
        const fresh = form.cloneNode(true);
        form.parentNode.replaceChild(fresh, form);

        fresh.addEventListener('submit', function (e) {
          e.preventDefault();
          const formData = new FormData(fresh);
          const action = fresh.getAttribute('action') || window.location.href;

          fetch(action, {
            method: fresh.getAttribute('method') || 'POST',
            body: formData,
            credentials: 'same-origin'
          })
            .then(function (response) {
              if (!response.ok) throw new Error('Form submission failed');
              return response.text();
            })
            .then(function (result) {
              console.log('Form submitted successfully', result);
              const current = new URL(location).searchParams.get('page') || 'dashboard-overview';
              fetchPage(current, false);
            })
            .catch(function (err) {
              console.error('Form error', err);
            });
        });
      });
    }

    // ─── Sidebar Click Intercept ──────────────────────────────────────────────────

    document.body.addEventListener('click', function (e) {
      var a = e.target.closest('a[data-page]');
      if (!a) return;
      e.preventDefault();
      fetchPage(a.getAttribute('data-page'));
    });

    // ─── Back / Forward ───────────────────────────────────────────────────────────

    window.addEventListener('popstate', function (e) {
      var page = (e.state && e.state.page) || new URL(location).searchParams.get('page') || 'dashboard-overview';
      fetchPage(page, false);
    });

    // ─── Initial Load ─────────────────────────────────────────────────────────────

    var initial = new URL(location).searchParams.get('page') || 'dashboard-overview';
    updateActiveLink(initial);
    initTabs();
    initForms();
    attachAssignSectionsFormHandler();

    window.addEventListener('page:loaded', function () {
      attachAssignSectionsFormHandler();
    });

  });

    // Reapply the selected Faculty Load values immediately before submission so
    // the Add Schedule form cannot submit mismatched dependent values.
    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!form || !form.querySelector || !form.querySelector('button[name="add_schedule"]')) return;

        const facultyLoad = form.querySelector('#faculty_load_id');
        const scheduleType = form.querySelector('#schedule_type');
        if (!facultyLoad || scheduleType?.value === 'Break Time') return;

        const selectedOption = facultyLoad.selectedOptions[0];
        if (!selectedOption || !selectedOption.value) {
          event.preventDefault();
          alert('Please select a Faculty Load.');
          return;
        }

        const values = {
          subject_id: selectedOption.dataset.subject || '',
          section_id: selectedOption.dataset.section || '',
          faculty_id: selectedOption.dataset.faculty || '',
          semester_id: selectedOption.dataset.semester || '',
          school_year_id: selectedOption.dataset.schoolYear || ''
        };

        Object.keys(values).forEach(function (id) {
          const select = form.querySelector('#' + id);
          if (select) select.value = values[id];
        });
    });