document.addEventListener("click", function (e) {
  const btn = e.target.closest(".editBtn");

  if (!btn) return;

  e.preventDefault();

  let id = btn.dataset.id;

  fetch(`${BASE_URL}/fingerprint-damage/view/${id}`)
    .then((response) => response.json())
    .then((data) => {
      console.log(data);

        document.getElementById("edit_id").value = data.id;
        document.getElementById("edit_item_name").value = data.item_name;
        document.getElementById("edit_laboratory").value = data.laboratory;
        document.getElementById("edit_issue").value = data.issue;
        document.getElementById("edit_reported_by").value = data.reported_by;
        document.getElementById("edit_date_reported").value = data.date_reported;
        document.getElementById("edit_status").value = data.status;

      let editModal = new bootstrap.Modal(
        document.getElementById("fpEditDamageModal"),
      );

      editModal.show();
    })
    .catch((error) => console.error(error));
});

document.addEventListener("click", function (e) {
  const btn = e.target.closest(".viewBtn");

  if (!btn) return;

  e.preventDefault();

  let id = btn.dataset.id;

  fetch(`${BASE_URL}/fingerprint-damage/view/${id}`)
    .then((response) => response.json())
    .then((data) => {
        document.getElementById("view_item_name").value = data.item_name;
        document.getElementById("view_laboratory").value = data.laboratory;
        document.getElementById("view_issue").value = data.issue;
        document.getElementById("view_reported_by").value = data.reported_by;
        document.getElementById("view_date_reported").value = data.date_reported;
        document.getElementById("view_status").value = data.status;

      let viewModal = new bootstrap.Modal(
        document.getElementById("fpViewDamageModal"),
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
    window.location.href = `${BASE_URL}/fingerprint-damage/delete/${id}`;
  }
});
