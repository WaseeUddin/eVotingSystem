document.addEventListener("DOMContentLoaded", function () {
  updateHeaderFromStorage();
  updateHeaderFromServer();

  setTimeout(updateHeaderFromServer, 500);
  setTimeout(updateHeaderFromServer, 1500);
});

function updateHeaderFromStorage() {
  const savedName =
    localStorage.getItem("evoting_user_name") ||
    localStorage.getItem("loggedUserName");

  const savedRole =
    localStorage.getItem("evoting_user_role") ||
    localStorage.getItem("loggedUserRole");

  if (savedName && savedRole === "voter") {
    putVoterName(savedName);
  }
}

function updateHeaderFromServer() {
  fetch("backend/get_current_user.php?t=" + new Date().getTime(), {
    cache: "no-store"
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (data) {
      if (data.status !== "success" || !data.user) {
        return;
      }

      if (data.user.role !== "voter") {
        return;
      }

      localStorage.setItem("evoting_user_id", data.user.id);
      localStorage.setItem("evoting_user_name", data.user.full_name);
      localStorage.setItem("evoting_user_role", data.user.role);

      putVoterName(data.user.full_name);
    })
    .catch(function (error) {
      console.log("Voter header load failed:", error);
    });
}

function putVoterName(name) {
  const finalName = name || "Voter";

  document.querySelectorAll(".voter-name-text").forEach(function (item) {
    item.innerText = finalName;
  });

  document.querySelectorAll("#voterNameHeader").forEach(function (item) {
    item.innerText = finalName;
  });

  document.querySelectorAll(".actions .user-btn").forEach(function (button) {
    button.innerHTML =
      '<i class="fa-solid fa-heart"></i>' +
      '<span class="voter-name-text">' + escapeText(finalName) + '</span>' +
      '<span class="role">(Voter)</span>';
  });

  const welcome = document.querySelector("#voterWelcome");

  if (welcome) {
    welcome.innerText =
      "Welcome back, " + finalName + "! Exercise your democratic rights.";
  }
}

function escapeText(text) {
  return String(text || "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}