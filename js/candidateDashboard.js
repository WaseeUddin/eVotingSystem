document.addEventListener("DOMContentLoaded", function () {
  loadCandidateDashboard();
});

function money(amount) {
  return "$" + Number(amount || 0).toLocaleString();
}

function setText(id, value) {
  const element = document.querySelector(id);

  if (element) {
    element.innerText = value;
  }
}

function loadCandidateDashboard() {
  fetch("backend/get_candidate_dashboard.php?t=" + new Date().getTime(), {
    cache: "no-store"
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status !== "success") {
        alert(data.message || "Candidate dashboard load failed");
        return;
      }

      const candidate = data.candidate;
      const stats = data.stats;

      setText("#headerCandidateName", candidate.name);
      setText("#profileName", candidate.name);
      setText("#candidateParty", candidate.party || "Independent");
      setText("#candidateStatement", candidate.campaign_statement || "");

      setText("#totalDonations", money(stats.total_donations));
      setText("#votesReceived", stats.votes_received);
      setText("#activeCampaigns", stats.active_campaigns);
      setText("#supporters", stats.supporters);

      const agendaList = document.querySelector("#agendaList");

      if (agendaList) {
        agendaList.innerHTML = "";

        if (data.agendas.length === 0) {
          agendaList.innerHTML = "<p>No agendas added yet.</p>";
        } else {
          data.agendas.slice(0, 7).forEach(function (agenda) {
            const item = document.createElement("div");
            item.className = "agenda";

            item.innerHTML = `
              <h3>${agenda.title}</h3>
              <p>${agenda.description}</p>
            `;

            agendaList.appendChild(item);
          });
        }
      }

      const agendaCount = document.querySelector("#agendaCount");

      if (agendaCount) {
        agendaCount.innerText = "Key Agendas (" + data.agendas.length + "/7)";
      }

      const performanceList = document.querySelector("#campaignPerformanceList");

      if (performanceList) {
        performanceList.innerHTML = "";

        if (data.campaign_performance.length === 0) {
          performanceList.innerHTML = "<p>No active campaign found.</p>";
        } else {
          data.campaign_performance.forEach(function (election) {
            const card = document.createElement("div");
            card.className = "election-card";

            card.innerHTML = `
              <span class="badge">Active</span>

              <h3>${election.title}</h3>
              <p>${election.description}</p>

              <div class="vote-boxes">
                <div class="vote-box">
                  <p class="vote-label">Your Votes</p>
                  <p class="vote-number">${election.your_votes || 0}</p>
                </div>

                <div class="vote-box">
                  <p class="vote-label">Total Votes</p>
                  <p class="vote-number">${election.total_votes || 0}</p>
                </div>
              </div>
            `;

            performanceList.appendChild(card);
          });
        }
      }
    })
    .catch(function (error) {
      console.log("Candidate dashboard error:", error);
      alert("Candidate dashboard load failed");
    });
}