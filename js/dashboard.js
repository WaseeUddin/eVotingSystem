document.addEventListener("DOMContentLoaded", function () {
  loadVoterDashboard();
  loadSupportCandidates();
});

function money(amount) {
  return "$" + Number(amount || 0).toLocaleString();
}

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

function loadVoterDashboard() {
  const electionList = document.querySelector("#activeElectionList");

  fetch("backend/get_voter_dashboard.php?t=" + new Date().getTime(), {
    cache: "no-store"
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (!electionList) {
        return;
      }

      if (data.status !== "success") {
        electionList.innerHTML = `
          <div class="election nice-election">
            <h4>No active election right now</h4>
            <p>Please check the Elections page or create a new election.</p>
            <a href="Election.html" class="vote-btn">View Elections</a>
          </div>
        `;
        return;
      }

      const voter = data.voter;
      const stats = data.stats;

      if (voter && voter.full_name) {
        setText("#voterWelcome", "Welcome back, " + voter.full_name + "! Exercise your democratic rights.");
      }

      setText("#activeElectionCount", stats.active_elections || 0);
      setText("#votesCastCount", stats.votes_cast || 0);

      electionList.innerHTML = "";

      if (!data.active_elections || data.active_elections.length === 0) {
        electionList.innerHTML = `
          <div class="election nice-election">
            <div class="election-top-line">
              <h4>No active election right now</h4>
              <span class="badge">Empty</span>
            </div>

            <p>When an election is open for voting, it will appear here.</p>

            <div class="election-actions">
              <a href="Election.html" class="vote-btn">View Elections</a>
            </div>
          </div>
        `;
        return;
      }

      data.active_elections.forEach(function (election) {
        const box = document.createElement("div");
        box.className = "election nice-election";

        box.innerHTML = `
          <div class="election-top-line">
            <h4>${escapeHtml(election.title)}</h4>
            <span class="badge">Active</span>
          </div>

          <p>${escapeHtml(election.description || "No description added.")}</p>

          <div class="election-actions">
            <a href="Election.html" class="vote-btn">Vote Now</a>
          </div>
        `;

        electionList.appendChild(box);
      });
    })
    .catch(function (error) {
      console.log("Voter dashboard load error:", error);

      if (electionList) {
        electionList.innerHTML = `
          <div class="election nice-election">
            <h4>No active election right now</h4>
            <p>Election data could not be loaded.</p>
            <a href="Election.html" class="vote-btn">View Elections</a>
          </div>
        `;
      }
    });
}

function loadSupportCandidates() {
  const list = document.querySelector("#supportCandidateList");

  if (!list) {
    return;
  }

  list.innerHTML = "<p>Loading candidates...</p>";

  fetch("backend/get_candidates.php?t=" + new Date().getTime(), {
    cache: "no-store"
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status !== "success") {
        list.innerHTML = "<p>Could not load candidates.</p>";
        return;
      }

      if (!data.candidates || data.candidates.length === 0) {
        list.innerHTML = "<p>No candidates found.</p>";
        setText("#candidateCount", 0);
        return;
      }

      setText("#candidateCount", data.candidates.length);

      list.innerHTML = "";

      data.candidates.forEach(function (candidate) {
        const card = document.createElement("div");
        card.className = "candidate";
        card.setAttribute("data-candidate-id", candidate.id);

        card.innerHTML = `
          <h4>${escapeHtml(candidate.name)}</h4>
          <p>${escapeHtml(candidate.party || "Independent")}</p>
          <p>Raised: <strong>${money(candidate.total_raised)}</strong></p>

          <a href="Donate_to_Candi.html" class="donate">
            <i class="fa-solid fa-dollar-sign"></i> Donate
          </a>
        `;

        list.appendChild(card);
      });
    })
    .catch(function (error) {
      console.log("Candidate load error:", error);
      list.innerHTML = "<p>Candidate load failed.</p>";
    });
}