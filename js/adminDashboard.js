document.addEventListener("DOMContentLoaded", function () {
  loadAdminDashboard();
});

function setText(selector, value) {
  const element = document.querySelector(selector);

  if (element) {
    element.innerText = value;
  }
}

function escapeHtml(text) {
  return String(text || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function loadAdminDashboard() {
  fetch("backend/get_admin_dashboard.php?t=" + new Date().getTime(), {
    cache: "no-store"
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status !== "success") {
        showAdminError(data.message || "Admin dashboard load failed");
        return;
      }

      const stats = data.stats;

      setText("#totalElections", stats.total_elections);
      setText("#activeElectionText", stats.active_elections + " currently active");

      setText("#totalVotes", stats.total_votes);

      setText("#candidateCount", stats.candidates);
      setText("#candidateText", "Registered candidates");

      setText("#anomalyCount", stats.unresolved_anomalies);
      setText("#unresolvedAnomalyText", stats.unresolved_anomalies + " unresolved issues");

      renderActiveElections(data.active_elections);
      renderAnomalies(data.anomalies);
    })
    .catch(function (error) {
      console.log("Admin dashboard load error:", error);
      showAdminError("Admin dashboard load failed");
    });
}

function renderActiveElections(elections) {
  const list = document.querySelector("#activeElectionList");

  if (!list) {
    return;
  }

  if (!elections || elections.length === 0) {
    list.innerHTML = `
      <div class="empty">
        <div class="icon">
          <i class="fa-solid fa-calendar-xmark"></i>
        </div>
        <p>No active elections</p>
        <small>Create or open an election to accept votes</small>
      </div>
    `;
    return;
  }

  list.innerHTML = "";

  elections.forEach(function (election) {
    const item = document.createElement("div");
    item.className = "election";

    item.innerHTML = `
      <span class="live">Live</span>

      <h3>${escapeHtml(election.title)}</h3>

      <p class="small">
        ${escapeHtml(election.description || "No description added.")}
      </p>

      <div class="bottom">
        <span>${election.vote_count} votes cast</span>
        <span>${election.hours_remaining}h remaining</span>
      </div>
    `;

    list.appendChild(item);
  });
}

function renderAnomalies(anomalies) {
  const list = document.querySelector("#adminAnomalyList");

  if (!list) {
    return;
  }

  if (!anomalies || anomalies.length === 0) {
    list.innerHTML = `
      <div class="empty">
        <div class="icon">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <p>No unresolved anomalies</p>
        <small>System running smoothly</small>
      </div>
    `;
    return;
  }

  list.innerHTML = "";

  anomalies.forEach(function (anomaly) {
    const item = document.createElement("div");
    item.className = "election";

    item.innerHTML = `
      <span class="live">Issue</span>

      <h3>${escapeHtml(anomaly.type || "Anomaly")}</h3>

      <p class="small">
        ${escapeHtml(anomaly.description || "No description added.")}
      </p>

      <div class="bottom">
        <span>${escapeHtml(anomaly.election_title || "Unknown election")}</span>
        <span>${escapeHtml(anomaly.reported_at || "")}</span>
      </div>
    `;

    list.appendChild(item);
  });
}

function showAdminError(message) {
  const activeBox = document.querySelector("#activeElectionList");
  const anomalyBox = document.querySelector("#adminAnomalyList");

  if (activeBox) {
    activeBox.innerHTML = `
      <div class="empty">
        <p>${escapeHtml(message)}</p>
      </div>
    `;
  }

  if (anomalyBox) {
    anomalyBox.innerHTML = `
      <div class="empty">
        <p>${escapeHtml(message)}</p>
      </div>
    `;
  }
}