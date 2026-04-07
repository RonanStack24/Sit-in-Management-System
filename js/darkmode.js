// Global Dark Mode System - Must run IMMEDIATELY
(function () {
  // Apply dark mode immediately (before page renders)
  const isDark = localStorage.getItem("darkMode") === "true";
  if (isDark) {
    document.documentElement.classList.add("dark");
  }

  // Initialize dark mode on page load
  function initDarkMode() {
    const isDark = localStorage.getItem("darkMode") === "true";
    const html = document.documentElement;

    if (isDark) {
      html.classList.add("dark");
    } else {
      html.classList.remove("dark");
    }
    updateIcons();
  }

  // Update sun/moon icons
  function updateIcons() {
    const sunIcon = document.getElementById("sunIcon");
    const moonIcon = document.getElementById("moonIcon");
    const isDark = document.documentElement.classList.contains("dark");

    if (sunIcon && moonIcon) {
      if (isDark) {
        sunIcon.classList.remove("hidden");
        moonIcon.classList.add("hidden");
      } else {
        sunIcon.classList.add("hidden");
        moonIcon.classList.remove("hidden");
      }
    }
  }

  // Toggle dark mode globally
  function toggleDarkMode() {
    const html = document.documentElement;
    const isDark = html.classList.contains("dark");

    if (isDark) {
      html.classList.remove("dark");
      localStorage.setItem("darkMode", "false");
    } else {
      html.classList.add("dark");
      localStorage.setItem("darkMode", "true");
    }
    updateIcons();
  }

  // Initialize on DOM ready
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initDarkMode);
  } else {
    initDarkMode();
  }

  // Attach toggle to button click - with delay if needed
  function attachToggle() {
    const toggle = document.getElementById("darkModeToggle");
    if (toggle) {
      toggle.addEventListener("click", toggleDarkMode);
    } else {
      // Retry in 100ms if button not found yet
      setTimeout(attachToggle, 100);
    }
  }

  attachToggle();

  // Expose globally for manual use
  window.toggleDarkMode = toggleDarkMode;
})();
