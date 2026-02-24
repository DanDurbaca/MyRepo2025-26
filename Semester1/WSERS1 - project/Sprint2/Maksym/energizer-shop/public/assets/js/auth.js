const signingButtons = document.getElementsByClassName("reg-button");
const closeButton = document.getElementsByClassName("close-button");

const signUpPanel = document.querySelector("#sign-up");
const signInPanel = document.querySelector("#sign-in");

const signUpButton = signingButtons[0];
const signInButton = signingButtons[1];

const closeSignUp = closeButton[0];
const closeSignIn = closeButton[1];

signUpButton.addEventListener("click", () => {
    signUpPanel.style.display = "flex";

    if (signInPanel.style.display === "flex") {
      signInPanel.style.display = "none";
    }
});

signInButton.addEventListener("click", () => {
  signInPanel.style.display = "flex";

  if (signUpPanel.style.display === "flex") {
      signUpPanel.style.display = "none";
  }
});

closeSignUp.addEventListener("click", () => {
  signUpPanel.style.display = "none";
});

closeSignIn.addEventListener("click", () => {
  signInPanel.style.display = "none";
});


