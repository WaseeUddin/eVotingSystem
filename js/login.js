const emailInput = document.querySelector("input[type='email']");
const passwordInput = document.querySelector("input[type='password']");
const loginBtn = document.querySelector(".login-btn");

if (loginBtn) {
  loginBtn.addEventListener("click", function (event) {
    event.preventDefault();

    const email = emailInput.value.trim();
    const password = passwordInput.value.trim();
    const expectedRole = getLoginRole();

    if (email === "") {
      alert("Please enter your email");
      return;
    }

    if (!email.includes("@")) {
      alert("Please enter a valid email");
      return;
    }

    if (password === "") {
      alert("Please enter your password");
      return;
    }

    fetch("backend/login.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body:
        "email=" + encodeURIComponent(email) +
        "&password=" + encodeURIComponent(password) +
        "&expected_role=" + encodeURIComponent(expectedRole)
    })
      .then(function (response) {
        return response.json();
      })
      .then(function (data) {
        if (data.status !== "success") {
          alert(data.message || "Login failed");
          return;
        }

        localStorage.setItem("evoting_user_id", data.user_id || "");
        localStorage.setItem("evoting_user_name", data.full_name || "");
        localStorage.setItem("evoting_user_role", data.role || "");

        if (data.role === "voter") {
          window.location.href = "VoterDashboard.html";
        } else if (data.role === "candidate") {
          window.location.href = "canhome.html";
        } else if (data.role === "admin") {
          window.location.href = "AdminDashBoard.html";
        } else {
          alert("Unknown role");
        }
      })
      .catch(function (error) {
        console.log("Login error:", error);
        alert("Login failed");
      });
  });
}

function getLoginRole() {
  const page = window.location.pathname.toLowerCase();

  if (page.includes("logincandidate")) {
    return "candidate";
  }

  if (page.includes("loginadmin")) {
    return "admin";
  }

  return "voter";
}