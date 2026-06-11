document.addEventListener("DOMContentLoaded", function () {
  loadCandidateCampaign();
});

function money(amount) {
  return "$" + Number(amount || 0).toLocaleString();
}

function moneyDecimal(amount) {
  return "$" + Number(amount || 0).toFixed(2);
}

function setText(selector, value) {
  const element = document.querySelector(selector);

  if (element) {
    element.innerText = value;
  }
}

function showMessage(message) {
  const recentBox = document.querySelector("#recentDonations");

  if (recentBox) {
    recentBox.innerHTML = `
      <div class="empty">
        <div class="empty-icon"><i class="fa-solid fa-circle-info"></i></div>
        <p class="empty-text">${message}</p>
      </div>
    `;
  }
}

function loadCandidateCampaign() {
  fetch("backend/get_candidate_campaign.php?t=" + new Date().getTime(), {
    cache: "no-store"
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status !== "success") {
        showMessage(data.message || "Campaign data load failed");
        return;
      }

      const candidate = data.candidate;
      const campaign = data.campaign;

      setText("#headerCandidateName", candidate.name || "Candidate");

      setText("#totalRaised", money(campaign.total_raised));
      setText("#totalDonors", campaign.total_donors);
      setText("#avgDonation", moneyDecimal(campaign.avg_donation));

      setText("#progressPercent", Number(campaign.progress).toFixed(1) + "%");
      setText("#progressAmount", money(campaign.total_raised));
      setText("#goalAmount", money(campaign.goal));
      setText("#thisMonthAmount", money(campaign.this_month));
      setText("#remainingAmount", money(campaign.remaining));

      const progressFill = document.querySelector("#campaignProgressFill");
      const smallFill = document.querySelector("#smallProgressFill");

      if (progressFill) {
        progressFill.style.width = campaign.progress + "%";
      }

      if (smallFill) {
        smallFill.style.width = campaign.progress + "%";
      }

      const recentBox = document.querySelector("#recentDonations");

      if (!recentBox) {
        return;
      }

      recentBox.innerHTML = "";

      if (!campaign.recent_donations || campaign.recent_donations.length === 0) {
        recentBox.innerHTML = `
          <div class="empty">
            <div class="empty-icon"><i class="fa-solid fa-hand-holding-usd"></i></div>
            <p class="empty-text">No donations received yet</p>
          </div>
        `;
        return;
      }

      campaign.recent_donations.forEach(function (donation) {
        const donorName = donation.donor_name || "Anonymous donor";

        const item = document.createElement("div");
        item.className = "agenda";

        item.innerHTML = `
          <h3>${donorName}</h3>
          <p>${money(donation.amount)} donated</p>
        `;

        recentBox.appendChild(item);
      });
    })
    .catch(function (error) {
      console.log("Campaign load error:", error);
      showMessage("Campaign data load failed");
    });
}