document.addEventListener("DOMContentLoaded", function () {
  const passwordBox = document.getElementById("password");
  const confirmBox = document.getElementById("confirm_password");

  if (passwordBox && confirmBox) {
    confirmBox.addEventListener("input", function () {
      if (passwordBox.value !== confirmBox.value) {
        confirmBox.classList.add(
          "border-red-500",
          "focus:border-red-500",
          "focus:ring-red-200",
        );
        confirmBox.classList.remove(
          "border-slate-200",
          "focus:border-indigo-500",
          "focus:ring-indigo-200/60",
          "border-green-500",
          "focus:border-green-500",
          "focus:ring-green-200",
        );
      } else {
        confirmBox.classList.add(
          "border-green-500",
          "focus:border-green-500",
          "focus:ring-green-200",
        );
        confirmBox.classList.remove(
          "border-red-500",
          "focus:border-red-500",
          "focus:ring-red-200",
          "border-slate-200",
          "focus:border-indigo-500",
          "focus:ring-indigo-200/60",
        );
      }
    });
  }
});
