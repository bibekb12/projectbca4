document
  .getElementById("saleForm")
  .addEventListener("submit", function (event) {
    event.preventDefault();

    // Assuming an AJAX call or some form submission logic here
    // On successful save:
    clearForm();
    showSuccessMessage();
  });

function clearForm() {
  document.getElementById("saleForm").reset();
}

function showSuccessMessage() {
  const successMessage = document.getElementById("successMessage");
  successMessage.style.display = "block";
  setTimeout(() => {
    successMessage.style.display = "none";
  }, 3000);
}
