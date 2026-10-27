document.addEventListener("click", function (e) {
  const btn = e.target.closest(".editBtn");

  if (!btn) return;

  e.preventDefault();

  let id = btn.dataset.id;

  fetch(`${BASE_URL}/question-document-monitoring/view/${id}`)
    .then((response) => response.json())
    .then((data) => {
      console.log(data);

      document.getElementById("edit_id").value = data.id;
      document.getElementById("edit_item_name").value = data.item_name;
      document.getElementById("edit_laboratory").value = data.laboratory;
      document.getElementById("edit_equipment_condition").value =
        data.equipment_condition;
      document.getElementById("edit_last_checked").value = data.last_checked;
      document.getElementById("edit_checked_by").value = data.checked_by;
      document.getElementById("edit_remarks").value = data.remarks;

      let editModal = new bootstrap.Modal(
        document.getElementById("qdEditMonitoringModal"),
      );

      editModal.show();
    })
    .catch((error) => console.error(error));
});

//view
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".viewBtn");

  if (!btn) return;

  e.preventDefault();

  let id = btn.dataset.id;

  fetch(`${BASE_URL}/question-document-monitoring/view/${id}`)
    .then((response) => response.json())
    .then((data) => {
      console.log(data);

      document.getElementById("view_item_name").value = data.item_name;
      document.getElementById("view_laboratory").value = data.laboratory;
      document.getElementById("view_condition").value =
        data.equipment_condition;
      document.getElementById("view_last_checked").value = data.last_checked;
      document.getElementById("view_checked_by").value = data.checked_by;
      document.getElementById("view_remarks").value = data.remarks;

      const viewModal = new bootstrap.Modal(
        document.getElementById("qdViewMonitoringModal"),
      );

      viewModal.show();
    })
    .catch((error) => console.error(error));
});

//delete
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".deleteBtn");

  if (!btn) return;

  e.preventDefault();

  let id = btn.dataset.id;

  if (confirm("Are you sure you want to delete this inventory?")) {
    window.location.href = `${BASE_URL}/question-document-monitoring/delete/${id}`;
  }
});
