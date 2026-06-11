document.addEventListener("DOMContentLoaded", function () {
  loadVotePage();
});

function getElectionId() {
  const params = new URLSearchParams(window.location.search);
  return params.get("election_id");
}

function escapeHtml(text) {
  return String(text || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function loadVotePage() {
  const electionId = getElectionId();
  const list = document.querySelector("#voteCandidateList");

  if (!electionId) {
    list.innerHTML = "<p>Election ID not found.</p>";
    return;
  }

  fetch("backend/get_vote_page.php?election_id=" + encodeURIComponent(electionId) + "&t=" + new Date().getTime(), {
    cache: "no-store"
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status !== "success") {
        list.innerHTML = `
          <div class="alert">
            <span class="icon">⚠</span>
            ${escapeHtml(data.message || "Vote page load failed")}
          </div>
        `;
        return;
      }

      document.querySelector("#electionTitle").innerText = data.election.title;
      document.querySelector("#electionDescription").innerText = data.election.description;

      if (data.already_voted) {
        list.innerHTML = `
          <div class="alert">
            <span class="icon">✓</span>
            You have already voted in this election.
          </div>
        `;
        return;
      }

      if (!data.candidates || data.candidates.length === 0) {
        list.innerHTML = "<p>No candidates found for this election.</p>";
        return;
      }

      list.innerHTML = "";

      data.candidates.forEach(function (candidate) {
        const card = document.createElement("div");
        card.className = "candidate";

        card.innerHTML = `
          <h4>${escapeHtml(candidate.name)}</h4>
          <p>${escapeHtml(candidate.party || "Independent")}</p>
          <p>${escapeHtml(candidate.campaign_statement || "No campaign statement added.")}</p>

          <button type="button" class="vote-btn vote-candidate-btn" data-candidate-id="${candidate.id}">
            Vote for ${escapeHtml(candidate.name)}
          </button>
        `;

        list.appendChild(card);
      });

      setupVoteButtons(electionId);
    })
    .catch(function (error) {
      console.log("Vote page load error:", error);
      list.innerHTML = "<p>Vote page load failed.</p>";
    });
}

function setupVoteButtons(electionId) {
  const buttons = document.querySelectorAll(".vote-candidate-btn");

  buttons.forEach(function (button) {
    button.addEventListener("click", function () {
      const candidateId = button.getAttribute("data-candidate-id");

      const confirmVote = confirm("Are you sure you want to vote for this candidate?");

      if (!confirmVote) {
        return;
      }

      submitVote(electionId, candidateId);
    });
  });
}

function submitVote(electionId, candidateId) {
  fetch("backend/vote.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded"
    },
    body:
      "election_id=" + encodeURIComponent(electionId) +
      "&candidate_id=" + encodeURIComponent(candidateId)
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status === "success") {
        alert("Vote submitted successfully");
        window.location.href = "Election.html";
        return;
      }

      alert(data.message || "Vote failed");
    })
    .catch(function (error) {
      console.log("Vote submit error:", error);
      alert("Vote failed");
    });
}