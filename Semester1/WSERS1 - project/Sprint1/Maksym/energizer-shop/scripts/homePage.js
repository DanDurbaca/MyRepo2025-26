document.addEventListener("DOMContentLoaded", () => {
  const title = document.getElementById("main-title");

  const titleText = title.innerHTML.trim();

  title.innerHTML = "";
  title.classList.add("typing-cursor");

  let index = 0;
  let isTag = false;
  let buffer = "";

  function typeText() {
    if (index >= titleText.length) {
      title.classList.remove("typing-cursor");

      return;
    }

    const char = titleText[index];
    buffer += char;

    if (char === "<") isTag = true;
    if (char === ">") isTag = false;

    if (isTag) {
      title.innerHTML = buffer;
      index++;

      typeText();

      return;
    }

    title.innerHTML = buffer;
    index++;

    setTimeout(typeText, 100);
  }

  typeText();
});
