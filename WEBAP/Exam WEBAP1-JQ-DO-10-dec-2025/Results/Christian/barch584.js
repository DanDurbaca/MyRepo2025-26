$(start);
let inputboxval = $("<input>");
let selectcolor = $("<select>");
let createbutton = $("<button>");
let clearbutton = $("<button>");

function start() {
    for (let i = 0; i <= 2; i++) {
        let option = $("<option>");
        option.attr("val", i);
        if (i == 0) {
            option.text("red");
        } else if (i == 1) {
            option.text("blue");
        } else if (i == 2) {
            option.text("yellow");
        }
        selectcolor.append(option);
    }
    createbutton.text("Create");
    clearbutton.text("Clear");
    $("body").append(inputboxval, selectcolor, createbutton, clearbutton);
    createbutton.on("click", function () {
        for (let i = 0; i < inputboxval.val(); i++) {
            let box = $("<div>");
            box.attr("class", "box");
            box.addClass(selectcolor.val());
            $("body").append(box);
            box.on("click", function () {
                this.remove();
            });
        }
    });
    clearbutton.on("click", function () {
        $("div").remove();
    });
}
