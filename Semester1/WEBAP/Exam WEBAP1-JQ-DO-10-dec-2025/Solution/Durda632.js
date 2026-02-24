$(start);

let colorsArr = ["red", "yellow", "blue"];

function start() {
    let countBox = $("<input>");
    $("body").append(countBox);
    let colorSelect = $("<select>");
    for (let i = 0; i < colorsArr.length; i++) {
        let newColor = $("<option>");
        newColor.val(i);
        newColor.html(colorsArr[i]);
        colorSelect.append(newColor);
    }
    $("body").append(colorSelect);
    let btnCreate = $("<button>");
    btnCreate.html("Create boxes");
    $("body").append(btnCreate);
    let btnClear = $("<button>");
    btnClear.html("Restart");
    $("body").append(btnClear);
    btnCreate.on("click", function () {
        for (let i = 0; i < Number(countBox.val()); i++) {
            let newDiv = $("<div>");
            newDiv.attr("class", "box " + colorsArr[Number(colorSelect.val())]);
            newDiv.on("click", function () {
                newDiv.remove();
            });
            $("body").append(newDiv);
            console.log("Test " + i + newDiv);
        }
    });
    btnClear.on("click", function () {
        $("body").html("");
        start();
    });
}
