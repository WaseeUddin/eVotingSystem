document.addEventListener("DOMContentLoaded", function () {
  loadCandidateProfile();

  const addBtn = document.querySelector("#addAgendaBtn");
  const saveBtn = document.querySelector("#saveProfileBtn");
  const cancelBtn = document.querySelector("#cancelProfileBtn");
  const statement = document.querySelector("#profileStatement");

  if (addBtn) {
    addBtn.addEventListener("click", function () {
      addAgendaCard("", "");
    });
  }

  if (saveBtn) {
    saveBtn.addEventListener("click", function () {
      saveCandidateProfile();
    });
  }

  if (cancelBtn) {
    cancelBtn.addEventListener("click", function () {
      window.location.href = "canhome.html";
    });
  }

  if (statement) {
    statement.addEventListener("input", function () {
      updateCharCount();
    });
  }
});

function setText(selector, value) {
  const element = document.querySelector(selector);

  if (element) {
    element.innerText = value;
  }
}

function setValue(selector, value) {
  const element = document.querySelector(selector);

  if (element) {
    element.value = value || "";
  }
}

function money(amount) {
  return "$" + Number(amount || 0).toLocaleString();
}

function loadCandidateProfile() {
  fetch("backend/get_candidate_profile.php?t=" + new Date().getTime(), {
    cache: "no-store"
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status !== "success") {
        alert(data.message || "Profile load failed");
        window.location.href = "logincandidate.html";
        return;
      }

      const candidate = data.candidate;

      setText("#headerCandidateName", candidate.name || "Candidate");
      setText("#profileTotalRaised", money(candidate.total_raised));

      setValue("#profileName", candidate.name);
      setValue("#profileParty", candidate.party);
      setValue("#profileStatement", candidate.campaign_statement);

      updateCharCount();

      const container = document.querySelector("#agendaContainer");

      if (!container) {
        return;
      }

      container.innerHTML = "";

      if (!data.agendas || data.agendas.length === 0) {
        addAgendaCard("", "");
      } else {
        data.agendas.forEach(function (agenda) {
          addAgendaCard(agenda.title, agenda.description);
        });
      }

      updateAgendaTitle();
    })
    .catch(function (error) {
      console.log("Profile load error:", error);
      alert("Profile load failed");
    });
}

function addAgendaCard(title, description) {
  const container = document.querySelector("#agendaContainer");

  if (!container) {
    return;
  }

  const currentCount = container.querySelectorAll(".agenda-card").length;

  if (currentCount >= 7) {
    alert("You can add maximum 7 agendas");
    return;
  }

  const card = document.createElement("div");
  card.className = "agenda-card";

  card.innerHTML = `
    <span class="delete"><i class="fa-solid fa-trash"></i></span>
    <h3>Agenda ${currentCount + 1}</h3>

    <label>Title *</label>
    <input type="text" class="agenda-title" value="${escapeHtml(title)}">

    <label>Description *</label>
    <textarea class="agenda-description">${escapeHtml(description)}</textarea>
  `;

  const deleteBtn = card.querySelector(".delete");

  deleteBtn.addEventListener("click", function () {
    card.remove();
    updateAgendaNumbers();
    updateAgendaTitle();
  });

  container.appendChild(card);
  updateAgendaTitle();
}

function updateAgendaNumbers() {
  document.querySelectorAll(".agenda-card").forEach(function (card, index) {
    const title = card.querySelector("h3");

    if (title) {
      title.innerText = "Agenda " + (index + 1);
    }
  });
}

function updateAgendaTitle() {
  const count = document.querySelectorAll(".agenda-card").length;
  setText("#agendaHeading", "Key Agendas (" + count + "/7)");
}

function updateCharCount() {
  const statement = document.querySelector("#profileStatement");
  const count = document.querySelector("#charCount");

  if (statement && count) {
    count.innerText = statement.value.length + " characters";
  }
}

function saveCandidateProfile() {
  const nameBox = document.querySelector("#profileName");
  const partyBox = document.querySelector("#profileParty");
  const statementBox = document.querySelector("#profileStatement");

  const name = nameBox ? nameBox.value.trim() : "";
  const party = partyBox ? partyBox.value.trim() : "";
  const statement = statementBox ? statementBox.value.trim() : "";

  const agendas = [];

  document.querySelectorAll(".agenda-card").forEach(function (card) {
    const titleBox = card.querySelector(".agenda-title");
    const descriptionBox = card.querySelector(".agenda-description");

    const title = titleBox ? titleBox.value.trim() : "";
    const description = descriptionBox ? descriptionBox.value.trim() : "";

    if (title !== "" && description !== "") {
      agendas.push({
        title: title,
        description: description
      });
    }
  });

  if (name === "") {
    alert("Please enter your name");
    return;
  }

  if (party === "") {
    alert("Please enter your party name");
    return;
  }

  if (statement === "") {
    alert("Please enter campaign statement");
    return;
  }

  if (agendas.length === 0) {
    alert("Please add at least one agenda");
    return;
  }

  fetch("backend/update_candidate_profile.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({
      name: name,
      party: party,
      campaign_statement: statement,
      agendas: agendas
    })
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status === "success") {
        alert("Profile saved successfully");
        window.location.href = "canhome.html";
      } else {
        alert(data.message || "Profile save failed");
      }
    })
    .catch(function (error) {
      console.log("Profile save error:", error);
      alert("Profile save failed");
    });
}

function escapeHtml(text) {
  return String(text || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}