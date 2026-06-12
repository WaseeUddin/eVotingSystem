document.addEventListener("DOMContentLoaded", function () {
  loadAdminElections();
  setupElectionForm();
});

let allCandidates = [];

function setupElectionForm() {
  const showBtn = document.querySelector("#showCreateForm");
  const closeBtn = document.querySelector("#closeElectionForm");
  const cancelBtn = document.querySelector("#cancelElectionForm");
  const form = document.querySelector("#adminElectionForm");

  if (showBtn) {
    showBtn.addEventListener("click", function () {
      openElectionForm();
    });
  }

  if (closeBtn) {
    closeBtn.addEventListener("click", closeElectionForm);
  }

  if (cancelBtn) {
    cancelBtn.addEventListener("click", closeElectionForm);
  }

  if (form) {
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      saveElection();
    });
  }
}

function openElectionForm(election) {
  const panel = document.querySelector("#electionFormPanel");

  if (!panel) {
    return;
  }

  panel.style.display = "block";

  document.querySelector("#formHeading").innerText = election ? "Edit Election" : "Create Election";

  document.querySelector("#electionId").value = election ? election.id : "";
  document.querySelector("#electionTitle").value = election ? election.title : "";
  document.querySelector("#electionDescription").value = election ? election.description : "";
  document.querySelector("#startDatetime").value = election ? formatForInput(election.start_datetime) : "";
  document.querySelector("#endDatetime").value = election ? formatForInput(election.end_datetime) : "";
  document.querySelector("#electionType").value = election ? election.election_type : "public";

  renderCandidateCheckboxes(election ? election.candidate_ids : []);

  panel.scrollIntoView({
    behavior: "smooth",
    block: "start"
  });
}

function closeElectionForm() {
  const panel = document.querySelector("#electionFormPanel");

  if (panel) {
    panel.style.display = "none";
  }

  const form = document.querySelector("#adminElectionForm");

  if (form) {
    form.reset();
  }

  document.querySelector("#electionId").value = "";
}

function loadAdminElections() {
  fetch("backend/admin_get_elections.php?t=" + new Date().getTime(), {
    cache: "no-store"
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status !== "success") {
        document.querySelector("#electionList").innerHTML =
          "<p>Election load failed.</p>";
        return;
      }

      allCandidates = data.candidates || [];

      renderElectionList(data.elections || []);
      renderCandidateCheckboxes([]);
    })
    .catch(function (error) {
      console.log("Admin election load error:", error);
      document.querySelector("#electionList").innerHTML =
        "<p>Election load failed.</p>";
    });
}

function renderElectionList(elections) {
  const list = document.querySelector("#electionList");

  if (!list) {
    return;
  }

  if (elections.length === 0) {
    list.innerHTML = `
      <div class="election-card">
        <h3>No elections found</h3>
        <p>Create a new election to start.</p>
      </div>
    `;
    return;
  }

  list.innerHTML = "";

  elections.forEach(function (election) {
    const card = document.createElement("div");
    card.className = "election-card";

    card.innerHTML = `
      <div class="election-main">
        <h3>
          ${escapeHtml(election.title)}
          <span class="status ${election.status.toLowerCase()}">${election.status}</span>
        </h3>

        <p>${escapeHtml(election.description || "No description added.")}</p>

        <div class="date-row">
          <span><i class="fa-regular fa-calendar"></i> Start: ${formatDate(election.start_datetime)}</span>
          <span><i class="fa-regular fa-clock"></i> End: ${formatDate(election.end_datetime)}</span>
        </div>

        <p class="small">
          ${election.candidate_count} candidates registered • ${election.vote_count} votes cast • ${escapeHtml(election.election_type)}
        </p>
      </div>

      <div class="election-actions">
        <button type="button" class="edit-btn" data-id="${election.id}">
          <i class="fa-regular fa-pen-to-square"></i>
          Edit
        </button>

        <button type="button" class="delete-btn" data-id="${election.id}">
          <i class="fa-regular fa-trash-can"></i>
          Delete
        </button>
      </div>
    `;

    list.appendChild(card);

    card.querySelector(".edit-btn").addEventListener("click", function () {
      openElectionForm(election);
    });

    card.querySelector(".delete-btn").addEventListener("click", function () {
      deleteElection(election.id);
    });
  });
}

function renderCandidateCheckboxes(selectedIds) {
  const box = document.querySelector("#candidateCheckboxList");

  if (!box) {
    return;
  }

  if (!allCandidates || allCandidates.length === 0) {
    box.innerHTML = "<p>No candidates found.</p>";
    return;
  }

  selectedIds = selectedIds.map(function (id) {
    return String(id);
  });

  box.innerHTML = "";

  allCandidates.forEach(function (candidate) {
    const label = document.createElement("label");
    label.className = "candidate-check";

    const checked = selectedIds.includes(String(candidate.id)) ? "checked" : "";

    label.innerHTML = `
      <input type="checkbox" class="candidate-checkbox" value="${candidate.id}" ${checked}>
      <span>
        <b>${escapeHtml(candidate.name)}</b>
        <small>${escapeHtml(candidate.party || "Independent")}</small>
      </span>
    `;

    box.appendChild(label);
  });
}

function saveElection() {
  const id = document.querySelector("#electionId").value;
  const title = document.querySelector("#electionTitle").value.trim();
  const description = document.querySelector("#electionDescription").value.trim();
  const startDatetime = document.querySelector("#startDatetime").value;
  const endDatetime = document.querySelector("#endDatetime").value;
  const electionType = document.querySelector("#electionType").value;

  const candidateIds = [];

  document.querySelectorAll(".candidate-checkbox:checked").forEach(function (box) {
    candidateIds.push(box.value);
  });

  if (title === "") {
    alert("Please enter election title");
    return;
  }

  if (description === "") {
    alert("Please enter description");
    return;
  }

  if (startDatetime === "" || endDatetime === "") {
    alert("Please select start and end time");
    return;
  }

  if (new Date(endDatetime) <= new Date(startDatetime)) {
    alert("End time must be after start time");
    return;
  }

  fetch("backend/admin_save_election.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      id: id,
      title: title,
      description: description,
      start_datetime: startDatetime,
      end_datetime: endDatetime,
      election_type: electionType,
      candidate_ids: candidateIds
    })
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status === "success") {
        alert(data.message || "Election saved successfully");
        closeElectionForm();
        loadAdminElections();
      } else {
        alert(data.message || "Election save failed");
      }
    })
    .catch(function (error) {
      console.log("Save election error:", error);
      alert("Election save failed");
    });
}

function deleteElection(id) {
  const confirmDelete = confirm(
    "Are you sure you want to delete this election? Votes and linked candidates for this election will also be removed."
  );

  if (!confirmDelete) {
    return;
  }

  fetch("backend/admin_delete_election.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body: "id=" + encodeURIComponent(id)
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status === "success") {
        alert(data.message || "Election deleted");
        loadAdminElections();
      } else {
        alert(data.message || "Delete failed");
      }
    })
    .catch(function (error) {
      console.log("Delete election error:", error);
      alert("Delete failed");
    });
}

function formatForInput(dateText) {
  if (!dateText) {
    return "";
  }

  return String(dateText).replace(" ", "T").slice(0, 16);
}

function formatDate(dateText) {
  const date = new Date(String(dateText).replace(" ", "T"));

  if (isNaN(date.getTime())) {
    return dateText;
  }

  return date.toLocaleDateString() + ", " + date.toLocaleTimeString([], {
    hour: "2-digit",
    minute: "2-digit"
  });
}

function escapeHtml(text) {
  return String(text || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}