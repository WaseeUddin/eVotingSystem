document.addEventListener("DOMContentLoaded", function () {
  loadCandidatesForDonation();
});

function money(amount) {
  return "$" + Number(amount || 0).toLocaleString();
}

function loadCandidatesForDonation() {
  const grid = document.querySelector("#candidateGrid");

  if (!grid) {
    return;
  }

  grid.innerHTML = "<p>Loading candidates...</p>";

  fetch("backend/get_candidates.php?t=" + new Date().getTime(), {
    cache: "no-store"
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status !== "success") {
        grid.innerHTML = "<p>Could not load candidates.</p>";
        return;
      }

      if (data.candidates.length === 0) {
        grid.innerHTML = "<p>No candidates found.</p>";
        return;
      }

      grid.innerHTML = "";

      data.candidates.forEach(function (candidate) {
        const card = document.createElement("div");
        card.className = "card";
        card.setAttribute("data-candidate-id", candidate.id);

        const statement = candidate.campaign_statement || "No campaign statement added yet.";
        const party = candidate.party || "Independent";
        const agendaCount = candidate.agenda_count || 0;

        card.innerHTML = `
          <div class="trend">↗</div>

          <p class="name">
            ${candidate.name}
            <span class="heart"><i class="fa-solid fa-heart"></i></span>
          </p>

          <p class="party">${party}</p>

          <p class="desc">${statement}</p>

          <div class="raised-box">
            <div class="raised-row">
              <span>Total Raised:</span>
              <strong class="candidate-total">${money(candidate.total_raised)}</strong>
            </div>
            <div class="bar">
              <div class="fill" style="width:${getProgress(candidate.total_raised)}%;"></div>
            </div>
          </div>

          <div class="line"></div>

          <p class="label">Donation Amount</p>

          <div class="donation-row">
            <div class="input-box">
              <i class="fa-solid fa-dollar-sign"></i>
              <input type="number" class="donation-amount" placeholder="Enter amount">
            </div>

            <button class="donate-btn" type="button">Donate</button>
          </div>

          <div class="amounts">
            <button type="button">$25</button>
            <button type="button">$50</button>
            <button type="button">$100</button>
            <button type="button">$250</button>
          </div>

          <p class="footer">${agendaCount} key agendas • View full campaign details</p>
        `;

        grid.appendChild(card);
      });

      setupDonationButtons();
    })
    .catch(function (error) {
      console.log("Candidate load error:", error);
      grid.innerHTML = "<p>Candidate load failed.</p>";
    });
}

function setupDonationButtons() {
  const cards = document.querySelectorAll(".card[data-candidate-id]");

  cards.forEach(function (card) {
    const amountInput = card.querySelector(".donation-amount");
    const donateBtn = card.querySelector(".donate-btn");
    const amountButtons = card.querySelectorAll(".amounts button");

    amountButtons.forEach(function (button) {
      button.addEventListener("click", function () {
        amountInput.value = button.innerText.replace("$", "");
      });
    });

    donateBtn.addEventListener("click", function () {
      const candidateId = card.getAttribute("data-candidate-id");
      const amount = parseFloat(amountInput.value);

      if (isNaN(amount) || amount <= 0) {
        alert("Enter a valid amount");
        return;
      }

      fetch("backend/donation.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded"
        },
        body:
          "candidate_id=" + encodeURIComponent(candidateId) +
          "&amount=" + encodeURIComponent(amount)
      })
        .then(function (response) {
          return response.json();
        })
        .then(function (data) {
          if (data.status !== "success") {
            alert(data.message || "Donation failed");
            return;
          }

          amountInput.value = "";

          const totalBox = card.querySelector(".candidate-total");
          const fill = card.querySelector(".fill");

          if (totalBox) {
            totalBox.innerText = money(data.total_raised);
          }

          if (fill) {
            fill.style.width = getProgress(data.total_raised) + "%";
          }

          alert("Donation successful");
        })
        .catch(function (error) {
          console.log("Donation error:", error);
          alert("Donation failed");
        });
    });
  });
}

function getProgress(totalRaised) {
  const goal = 100000;
  const percent = (Number(totalRaised || 0) / goal) * 100;

  if (percent > 100) {
    return 100;
  }

  return percent;
}