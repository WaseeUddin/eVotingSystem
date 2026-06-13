const submitBtn = document.querySelector(".submit");

if (submitBtn) {

submitBtn.addEventListener("click", function (event) {

event.preventDefault();

const role = getRegisterRole();

const fullName = document.querySelector("#fullName");
const nid = document.querySelector("#nid");
const dob = document.querySelector("#dob");
const gender = document.querySelector("#gender");
const email = document.querySelector("#email");
const phone = document.querySelector("#phone");
const password = document.querySelector("#password");
const confirmPassword = document.querySelector("#confirmPassword");

const party = document.querySelector("#party");
const campaignStatement = document.querySelector("#campaignStatement");

const nidFile = document.querySelector("#nidFile");

if (!fullName || fullName.value.trim() === "") {
  alert("Please enter full name");
  return;
}

if (!nid || nid.value.trim().length < 10) {
  alert("Please enter a valid NID number");
  return;
}

if (!dob || dob.value === "") {
  alert("Please select date of birth");
  return;
}

if (!gender || gender.value === "") {
  alert("Please select gender");
  return;
}

if (!email || !email.value.includes("@")) {
  alert("Please enter a valid email");
  return;
}

if (!phone || phone.value.trim() === "") {
  alert("Please enter phone number");
  return;
}

if (!password || password.value.length < 8) {
  alert("Password must be at least 8 characters");
  return;
}

if (!confirmPassword || password.value !== confirmPassword.value) {
  alert("Passwords do not match");
  return;
}

if (role === "candidate") {
  if (!party || party.value.trim() === "") {
    alert("Please enter party name");
    return;
  }
}

const formData = new FormData();

if (nidFile && nidFile.files.length > 0) {
  formData.append("nidFile", nidFile.files[0]);
}

formData.append("full_name", fullName.value.trim());
formData.append("nid", nid.value.trim());
formData.append("dob", dob.value);
formData.append("gender", gender.value);
formData.append("email", email.value.trim());
formData.append("phone", phone.value.trim());
formData.append("password", password.value);
formData.append("role", role);

if (role === "candidate") {
  formData.append("party", party.value.trim());

  formData.append(
    "campaign_statement",
    campaignStatement
      ? campaignStatement.value.trim()
      : ""
  );
}

fetch("backend/register.php", {
  method: "POST",
  body: formData
})

.then(response => response.json())

.then(data => {

  if (data.status !== "success") {
    alert(data.message || "Registration failed");
    return;
  }

  alert("Registration successful. Please login now.");

  if (data.role === "candidate") {
    window.location.href = "logincandidate.html";
  }
  else if (data.role === "admin") {
    window.location.href = "loginadmin.html";
  }
  else {
    window.location.href = "loginvoter.html";
  }

})

.catch(error => {

  console.error("Register error:", error);

  alert("Registration failed");

});


});

}

function getRegisterRole() {

const page = window.location.pathname.toLowerCase();

if (page.includes("regiscandidate")) {
return "candidate";
}

if (page.includes("regisadmin")) {
return "admin";
}

return "voter";
}
