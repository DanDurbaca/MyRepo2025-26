document.addEventListener("DOMContentLoaded", () => {
  const navLinks = document.querySelectorAll("nav a.nav-link");

  navLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      if (e.ctrlKey || e.shiftKey || e.metaKey || e.button === 1) {
        return;
      }

      e.preventDefault();
      const url = link.href;
      window.location.replace(url);
    });
  });
});