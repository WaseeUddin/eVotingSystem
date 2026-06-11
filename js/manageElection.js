const submit = document.querySelector(".submit");
const titleInput = document.querySelector("input[type='text']");
const description = document.querySelector("textarea");
const dateInputs = document.querySelectorAll("input[type='text']");
const checkboxes = document.querySelectorAll("input[type='checkbox']");

submit.addEventListener("click", function (e) {
  e.preventDefault();

  if (titleInput.value.trim() === "") {
    alert("Please enter election title");
    return;
  }

  if (description.value.trim().length < 10) {
    alert("Description must be at least 10 characters");
    return;
  }

  if (dateInputs[1].value.trim() === "" || dateInputs[2].value.trim() === "") {
    alert("Please enter start and end date");
    return;
  }

  let selectedCandidate = false;

  checkboxes.forEach(function (box) {
    if (box.checked) {
      selectedCandidate = true;
    }
  });

  if (!selectedCandidate) {
    alert("Please select at least one candidate");
    return;
  }

  alert("Election created successfully");
  window.location.href = "ManageElections.html";
});