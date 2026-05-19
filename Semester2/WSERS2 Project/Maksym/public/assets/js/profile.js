const profilePicture = document.querySelector("#profile-picture");
const profilePanel = document.querySelector("#profile-panel");

profilePanel.style.display = "none";

profilePicture.addEventListener("click", () => {
  if (profilePanel.style.display === "none") {
    profilePanel.style.display = "flex";
  } else if (profilePanel.style.display === "flex") {
    profilePanel.style.display = "none";
  }
});
