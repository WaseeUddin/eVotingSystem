document.addEventListener("DOMContentLoaded", function () {
  loadHomeData();
});

function loadHomeData() {
  fetch("backend/get_home_data.php?t=" + new Date().getTime(), {
    cache: "no-store"
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status !== "success") {
        showHomeError();
        return;
      }

      setText("#homeVoterCount", formatCount(data.stats.voters));
      setText("#homeElectionCount", formatCount(data.stats.elections));

      setText(
        "#liveElectionText",
        data.stats.active_elections + " elections are currently active. Register now to participate!"
      );

      renderHomeElections(data.active_elections);
    })
    .catch(function (error) {
      console.log("Home data load failed:", error);
      showHomeError();
    });
}

function renderHomeElections(elections) {
  const list = document.querySelector("#homeElectionList");

  if (!list) {
    return;
  }

  if (!elections || elections.length === 0) {
    list.innerHTML = `
      <div class="election-card empty-home-card">
        <span class="badge">No Active Election</span>
        <h3>No live election right now</h3>
        <p>
          <i class="fa-regular fa-calendar"></i>
          Please check back later or login to view all elections.
        </p>

        <a href="loginvoter.html" class="vote-btn">
          <i class="fa-solid fa-right-to-bracket"></i>
          Login to View Elections
        </a>
      </div>
    `;

    setText(
      "#liveElectionText",
      "No election is currently active. Login to check upcoming elections."
    );

    return;
  }

  list.innerHTML = "";

  elections.forEach(function (election) {
    const card = document.createElement("div");
    card.className = "election-card";

    const progress = calculateProgress(election.start_datetime, election.end_datetime);

    card.innerHTML = `
      <span class="badge">Active</span>

      <h3>${escapeHtml(election.title)}</h3>

      <p>
        <i class="fa-solid fa-location-dot"></i>
        ${escapeHtml(election.election_type)}
      </p>

      <p>
        <i class="fa-regular fa-calendar"></i>
        ${formatDate(election.start_datetime)} - ${formatDate(election.end_datetime)}
      </p>

      <div class="numbers">
        <div>
          ${election.candidate_count}
          <br>
          <small>Candidates</small>
        </div>

        <div>
          ${Number(election.vote_count || 0).toLocaleString()}
          <br>
          <small>Votes Cast</small>
        </div>
      </div>

      <div class="progress">
        <div style="width:${progress}%"></div>
      </div>

      <a href="loginvoter.html" class="vote-btn">
        <i class="fa-solid fa-square-check"></i>
        Participate Now
      </a>
    `;

    list.appendChild(card);
  });
}

function calculateProgress(startText, endText) {
  const start = new Date(String(startText).replace(" ", "T")).getTime();
  const end = new Date(String(endText).replace(" ", "T")).getTime();
  const now = new Date().getTime();

  if (isNaN(start) || isNaN(end) || end <= start) {
    return 50;
  }

  const percent = ((now - start) / (end - start)) * 100;

  if (percent < 5) {
    return 5;
  }

  if (percent > 100) {
    return 100;
  }

  return Math.round(percent);
}

function formatDate(dateText) {
  const date = new Date(String(dateText).replace(" ", "T"));

  if (isNaN(date.getTime())) {
    return dateText;
  }

  return date.toLocaleDateString([], {
    month: "short",
    day: "numeric",
    year: "numeric"
  });
}

function formatCount(value) {
  const number = Number(value || 0);

  if (number >= 1000) {
    return number.toLocaleString() + "+";
  }

  return number.toString();
}

function setText(selector, value) {
  const element = document.querySelector(selector);

  if (element) {
    element.innerText = value;
  }
}

function showHomeError() {
  setText("#homeVoterCount", "0");
  setText("#homeElectionCount", "0");
  setText("#liveElectionText", "Could not load live elections right now.");

  const list = document.querySelector("#homeElectionList");

  if (list) {
    list.innerHTML = `
      <div class="election-card empty-home-card">
        <span class="badge">Error</span>
        <h3>Election data not available</h3>
        <p>Please check backend/get_home_data.php.</p>
      </div>
    `;
  }
}

function escapeHtml(text) {
  return String(text || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}