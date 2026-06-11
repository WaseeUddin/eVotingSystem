const submitBtn = document.querySelector(".submit");
const selects = document.querySelectorAll("select");
const textarea = document.querySelector("textarea");

submitBtn.addEventListener("click", function (e) {
  e.preventDefault();

  if (selects[0].selectedIndex === 0) {
    alert("Please select related election");
    return;
  }

  if (selects[1].selectedIndex === 0) {
    alert("Please select anomaly type");
    return;
  }

  if (textarea.value.trim().length < 10) {
    alert("Description must be at least 10 characters");
    return;
  }

  alert("Anomaly reported successfully");
  window.location.href = "Anomaly_Detection_andRepoting.html";
});