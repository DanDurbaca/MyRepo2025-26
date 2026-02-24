function onStart() {
  let whiteAmt = 0;
  let redAmt = 0;
  let blueAmt = 0;
  let yellowAmt = 0;
  const colors = ["red", "blue", "yellow", "white"];

  $.each(colors, function (index, color) {
    $("<div>")
      .addClass("out")
      .append(
        $("<div>").addClass("box " + color),
        $("<input>")
          .attr({
            type: "text",
            value: color !== "none" ? 0 : null,
            id: color,
            readonly: true,
          })
          .addClass(color === "none" ? "none" : "")
      )
      .appendTo("body");
  });

  for (let i = 0; i < 5; i++) {
    const row = $("<div>").addClass("row");
    for (let j = 0; j < 10; j++) {
      let boxClass = "white";

      const box = $("<div>")
        .addClass(boxClass + " box")
        .attr("data-i", i)
        .attr("data-j", j);

      if (i > 0) {
        box.on("click", function () {
          const currentColor = $(this).hasClass("red")
            ? "red"
            : $(this).hasClass("blue")
            ? "blue"
            : $(this).hasClass("yellow")
            ? "yellow"
            : $(this).addClass("white");

          if (currentColor === "white") {
            whiteAmt--;
            redAmt++;
          } else if (currentColor === "red") {
            redAmt--;
            blueAmt++;
          } else if (currentColor === "blue") {
            blueAmt--;
            yellowAmt++;
          } else {
            yellowAmt--;
            whiteAmt++;
          }

          updateColorCounts();
        });
      }

      box.appendTo(row);
    }

    $("<br>").appendTo(row);
    row.appendTo("body");
  }
}
function updateColorCounts() {
  $("#white").val(whiteAmt);
  $("#red").val(redAmt);
  $("#blue").val(blueAmt);
  $("#yellow").val(yellowAmt);
}
$(onStart);
