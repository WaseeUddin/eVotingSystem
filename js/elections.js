document.addEventListener("DOMContentLoaded", function () {
  loadElections();
});

function loadElections() {
  const electionList = document.querySelector("#electionList");

  if (!electionList) {
    return;
  }

  electionList.innerHTML = `
    <section class="card">
      <p class="description">Loading elections...</p>
    </section>
  `;

  fetch("backend/get_elections.php?t=" + new Date().getTime(), {
    cache: "no-store"
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status !== "success") {
        electionList.innerHTML = `
          <section class="card">
            <div class="card-title">Election load failed</div>
            <p class="description">${data.message || "Could not load elections."}</p>
          </section>
        `;
        return;
      }

      if (!data.elections || data.elections.length === 0) {
        electionList.innerHTML = `
          <section class="card">
            <div class="card-title">No elections found</div>
            <p class="description">
              No public or private election is available for your account right now.
            </p>
          </section>
        `;
        return;
      }

      electionList.innerHTML = "";

      data.elections.forEach(function (election) {
        const card = document.createElement("section");
        card.className = "card";

        let actionHtml = "";

        if (election.status === "Active") {
          actionHtml = `
            <a href="VoteNow.html?election_id=${election.id}" class="vote-btn">
              Vote Now
            </a>
          `;
        } else if (election.status === "Upcoming") {
          actionHtml = `
            <div class="alert">
              <span class="icon">⚠</span>
              This election is not open yet.
            </div>
          `;
        } else {
          actionHtml = `
            <div class="alert">
              <span class="icon">✓</span>
              This election has ended.
            </div>
          `;
        }

        card.innerHTML = `
          <div class="card-title">
            ${escapeHtml(election.title)}
            <span class="badge">${election.status}</span>
          </div>

          <p class="description">
            ${escapeHtml(election.description || "No description added.")}
          </p>

          <div class="dates">
            <span>Start: ${formatDate(election.start_datetime)}</span>
            <span>End: ${formatDate(election.end_datetime)}</span>
          </div>

          <p class="description">
            Type: ${escapeHtml(election.election_type)}
          </p>

          ${actionHtml}
        `;

        electionList.appendChild(card);
      });
    })
    .catch(function (error) {
      console.log("Election load failed:", error);

      electionList.innerHTML = `
        <section class="card">
          <div class="card-title">Election load failed</div>
          <p class="description">Please check backend/get_elections.php.</p>
        </section>
      `;
    });
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