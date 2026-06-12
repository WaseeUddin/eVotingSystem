document.addEventListener("DOMContentLoaded", function () {
  loadLiveResults();

  const select = document.querySelector("#resultElectionSelect");

  if (select) {
    select.addEventListener("change", function () {
      loadLiveResults(select.value);
    });
  }
});

function loadLiveResults(electionId) {
  let url = "backend/admin_get_results.php?t=" + new Date().getTime();

  if (electionId) {
    url += "&election_id=" + encodeURIComponent(electionId);
  }

  fetch(url, {
    cache: "no-store"
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      console.log("Live results data:", data);

      if (data.status !== "success") {
        alert(data.message || "Result load failed");
        return;
      }

      renderElectionOptions(data.elections, data.election);
      renderSummary(data);
      renderVoteDistribution(data);
      renderTimeline(data.election);
    })
    .catch(function (error) {
      console.log("Live result load error:", error);
      alert("Live result load failed");
    });
}

function renderElectionOptions(elections, selectedElection) {
  const select = document.querySelector("#resultElectionSelect");

  if (!select) {
    return;
  }

  select.innerHTML = "";

  if (!elections || elections.length === 0) {
    const option = document.createElement("option");
    option.value = "";
    option.innerText = "No elections found";
    select.appendChild(option);
    return;
  }

  elections.forEach(function (election) {
    const option = document.createElement("option");
    option.value = election.id;
    option.innerText = election.title + " - " + election.status;

    if (selectedElection && String(election.id) === String(selectedElection.id)) {
      option.selected = true;
    }

    select.appendChild(option);
  });
}

function renderSummary(data) {
  const election = data.election;
  const leading = data.leading_candidate;

  setText("#totalVotesCast", data.total_votes || 0);

  if (leading) {
    setText("#leadingCandidateName", leading.name);
    setText("#leadingCandidatePercent", leading.percent + "% of votes");
  } else {
    setText("#leadingCandidateName", "No votes yet");
    setText("#leadingCandidatePercent", "0% of votes");
  }

  if (election) {
    setText("#electionStatusBadge", election.status.toUpperCase());
    setText("#electionStatusDate", formatDateOnly(election.start_datetime));

    const badge = document.querySelector("#electionStatusBadge");

    if (badge) {
      badge.className = "status " + election.status.toLowerCase();
    }
  }
}

function renderVoteDistribution(data) {
  const election = data.election;
  const list = document.querySelector("#resultList");

  if (!list) {
    return;
  }

  if (election) {
    setText("#voteDistributionTitle", "Counting results for " + election.title);
  }

  if (!data.results || data.results.length === 0) {
    list.innerHTML = "<p>No candidates found for this election.</p>";
    return;
  }

  list.innerHTML = "";

  data.results.forEach(function (candidate, index) {
    const row = document.createElement("div");
    row.className = "candidate-row";

    row.innerHTML = `
      <div class="candidate-top">
        <div class="rank">
          ${index + 1}
        </div>

        <div>
          <div class="name">${escapeHtml(candidate.name)}</div>
          <div class="party">${escapeHtml(candidate.party || "Independent")}</div>
        </div>

        <div class="vote-count">
          ${candidate.vote_count}
          <div class="percent">
            ${candidate.percent}%
          </div>
        </div>
      </div>

      <div class="bar">
        <div class="bar-fill" style="width:${candidate.percent}%"></div>
      </div>
    `;

    list.appendChild(row);
  });
}

function renderTimeline(election) {
  if (!election) {
    return;
  }

  setText("#timelineStart", formatDate(election.start_datetime));
  setText("#timelineEnd", formatDate(election.end_datetime));

  const now = new Date();
  const start = new Date(String(election.start_datetime).replace(" ", "T"));
  const end = new Date(String(election.end_datetime).replace(" ", "T"));

  setText("#startBadge", now >= start ? "Started" : "Pending");

  if (now < start) {
    setText("#endBadge", "Pending");
  } else if (now >= start && now <= end) {
    setText("#endBadge", "Open");
  } else {
    setText("#endBadge", "Ended");
  }

  setText("#certBadge", now > end ? "Ready" : "Pending");
}

function setText(selector, value) {
  const element = document.querySelector(selector);

  if (element) {
    element.innerText = value;
  }
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

function formatDateOnly(dateText) {
  const date = new Date(String(dateText).replace(" ", "T"));

  if (isNaN(date.getTime())) {
    return dateText;
  }

  return date.toLocaleDateString();
}

function escapeHtml(text) {
  return String(text || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}