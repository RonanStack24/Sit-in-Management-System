// ============================================
// UTILITY FUNCTIONS
// Used across all pages
// ============================================

/**
 * Show a toast notification at bottom-right
 * @param {string} msg - Message to display
 */
function showToast(msg) {
  const toast = document.getElementById("toast");
  if (!toast) return;

  document.getElementById("toast-msg").textContent = msg;
  toast.classList.remove("hidden");
  toast.classList.add("flex");

  setTimeout(function () {
    toast.classList.add("opacity-0", "transition-opacity", "duration-500");
    setTimeout(function () {
      toast.classList.add("hidden");
      toast.classList.remove("flex", "opacity-0");
    }, 500);
  }, 3500);
}

/**
 * Show a toast notification with type (success/error)
 * @param {string} msg - Message to display
 * @param {string} type - Type: 'success' or 'error'
 */
function showToastWithType(msg, type = "success") {
  const toast = document.getElementById("toast");
  if (!toast) return;

  const toastMsg = document.getElementById("toast-msg");
  toastMsg.textContent = msg;

  if (type === "success") {
    toast.className =
      "fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-green-500 text-white px-5 py-3 rounded-xl shadow-lg font-semibold text-sm";
  } else {
    toast.className =
      "fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-red-500 text-white px-5 py-3 rounded-xl shadow-lg font-semibold text-sm";
  }

  toast.classList.add("flex");
  toast.classList.remove("hidden");

  setTimeout(() => {
    toast.classList.add("hidden");
    toast.classList.remove("flex");
  }, 3500);
}
