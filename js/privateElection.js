document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector("#privateElectionForm");

  if (!form) {
    return;
  }

  form.addEventListener("submit", function (event) {
    event.preventDefault();

    const title = document.querySelector("#electionTitle").value.trim();
    const description = document.querySelector("#electionDescription").value.trim();

    const startDate = document.querySelector("#startDate").value.trim();
    const endDate = document.querySelector("#endDate").value.trim();

    const startDatetime = startDate + "T08:00";
    const endDatetime = endDate + "T20:00";

    const existingCandidates = [];

    document.querySelectorAll(".existing-candidate:checked").forEach(function (box) {
      existingCandidates.push(box.value);
    });

    const newCandidateName = document.querySelector("#newCandidateName").value.trim();
    const newCandidateParty = document.querySelector("#newCandidateParty").value.trim();
    const newCandidateStatement = document.querySelector("#newCandidateStatement").value.trim();

    let newCandidate = null;

    if (newCandidateName !== "" || newCandidateParty !== "" || newCandidateStatement !== "") {
      if (newCandidateName === "" || newCandidateParty === "") {
        alert("New candidate name and party are required");
        return;
      }

      newCandidate = {
        name: newCandidateName,
        party: newCandidateParty,
        statement: newCandidateStatement
      };
    }

    const voterNids = [];

    document.querySelectorAll(".existing-voter:checked").forEach(function (box) {
      voterNids.push(box.value);
    });

    const manualNidsText = document.querySelector("#manualNids").value.trim();

    if (manualNidsText !== "") {
      manualNidsText.split(",").forEach(function (nid) {
        nid = nid.trim();

        if (nid !== "" && !voterNids.includes(nid)) {
          voterNids.push(nid);
        }
      });
    }

    if (title === "") {
      alert("Please enter election title");
      return;
    }

    if (description.length < 2) {
      alert("Description must be at least 2 characters");
      return;
    }

    if (startDate === "" || endDate === "") {
      alert("Please select start and end date");
      return;
    }

    if (new Date(endDatetime) <= new Date(startDatetime)) {
      alert("End date must be after start date");
      return;
    }

    if (existingCandidates.length === 0 && newCandidate === null) {
      alert("Please select or add at least one candidate");
      return;
    }

    if (voterNids.length === 0) {
      alert("Please select or enter at least one eligible voter");
      return;
    }

    fetch("backend/create_private_election.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        title: title,
        description: description,
        start_datetime: startDatetime,
        end_datetime: endDatetime,
        existing_candidates: existingCandidates,
        new_candidate: newCandidate,
        voter_nids: voterNids
      })
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (data.status === "success") {
          alert("Private election created successfully");
          window.location.href = "VoterDashboard.html";
        } else {
          alert(data.message || "Election creation failed");
        }
      })
      .catch(function (error) {
        console.log("Private election create failed:", error);
        alert("Election creation failed");
      });
  });

  const selectAllBtn = document.querySelector("#selectAllVoters");
  const deselectAllBtn = document.querySelector("#deselectAllVoters");

  if (selectAllBtn) {
    selectAllBtn.addEventListener("click", function () {
      document.querySelectorAll(".existing-voter").forEach(function (box) {
        box.checked = true;
      });
    });
  }

  if (deselectAllBtn) {
    deselectAllBtn.addEventListener("click", function () {
      document.querySelectorAll(".existing-voter").forEach(function (box) {
        box.checked = false;
      });
    });
  }
});