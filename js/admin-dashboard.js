// ============================================
// ADMIN DASHBOARD FUNCTIONS
// Form validation and interactions
// ============================================

/**
 * Validate sit-in form fields
 * Checks if both Purpose and Lab are selected
 */
function validateForm() {
  const purpose = document.querySelector('select[name="purpose"]');
  const lab = document.querySelector('select[name="lab"]');

  if (!purpose || !lab) return;

  const purposeValue = purpose.value;
  const labValue = lab.value;
  const validationMsg = document.getElementById("validation_message");
  const submitBtn = document.getElementById("submit_btn");

  if (purposeValue && labValue) {
    if (validationMsg) validationMsg.classList.add("hidden");
    if (submitBtn) submitBtn.disabled = false;
  } else {
    if (validationMsg) validationMsg.classList.remove("hidden");
    if (submitBtn) submitBtn.disabled = true;
  }
}

// Initialize form validation when page loads
document.addEventListener("DOMContentLoaded", function () {
  validateForm();
});
