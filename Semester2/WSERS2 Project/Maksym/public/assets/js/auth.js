const signingButtons = document.getElementsByClassName("reg-button");
const closeButton = document.getElementsByClassName("close-button");

const signUpPanel = document.querySelector("#sign-up");
const signInPanel = document.querySelector("#sign-in");

const errorPanel = document.querySelector("#errors");
const closeError = document.querySelector("#error-close");

const signUpButton = signingButtons[0];
const signInButton = signingButtons[1];

const closeSignUp = closeButton[0];
const closeSignIn = closeButton[1];

signUpPanel.style.display = "none";
signInPanel.style.display = "none";

signUpButton.addEventListener("click", () => {
  if (signUpPanel.style.display === "none") {
    signUpPanel.style.display = "flex";
    localStorage.setItem("signUpVisible", "true");
  } else if (signUpPanel.style.display === "flex") {
    signUpPanel.style.display = "none";
    localStorage.setItem("signUpVisible", "false");
  }
});

signInButton.addEventListener("click", () => {
  if (signInPanel.style.display === "none") {
    signInPanel.style.display = "flex";
    localStorage.setItem("signInVisible", "true");
  } else if (signInPanel.style.display === "flex") {
    signInPanel.style.display = "none";
    localStorage.setItem("signInVisible", "false");
  }
});

closeSignUp.addEventListener("click", () => {
  signUpPanel.style.display = "none";
  localStorage.setItem("signUpVisible", "false");
});

closeSignIn.addEventListener("click", () => {
  signInPanel.style.display = "none";
  localStorage.setItem("signInVisible", "false");
});

if (errorPanel !==  null) {
  closeError.addEventListener("click", () => {
    errorPanel.style.display = "none";
  });
}

window.onload = () => {
  const isSignUpVisible = localStorage.getItem("signUpVisible");
  const isSignInVisible = localStorage.getItem("signInVisible");

  if (isSignUpVisible === "true") signUpPanel.style.display = "flex";

  if (isSignInVisible === "true") signInPanel.style.display = "flex";
}
