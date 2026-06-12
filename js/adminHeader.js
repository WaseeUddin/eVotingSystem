document.addEventListener("DOMContentLoaded", function () {
  loadAdminHeader();
});

function loadAdminHeader() {
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

      if (data.user.role !== "admin") {
        return;
      }

      const adminName = data.user.full_name || "Admin User";

      document.querySelectorAll(".admin-name-text").forEach(function (item) {
        item.innerText = adminName;
      });
    })
    .catch(function (error) {
      console.log("Admin header load failed:", error);
    });
}