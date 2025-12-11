$(start);

let Options = ["red", "blue", "yellow"];

let newBox = $("<div>");

function start() {

    let inputBoxName = $("<input>");
    inputBoxName.attr("placeholder", "Type number");
    inputBoxName.attr("type", "integer");
    $("body").append(inputBoxName);

    let myColoursSelect = $("<select>");

    for (let i = 0; i < Options.length; i++) {

        let myColoursOption = $("<option>");
        myColoursOption.attr("value", i);
        myColoursOption.html(Options[i]);

        myColoursSelect.append(myColoursOption);
    }


    $("body").append(myColoursSelect);

    let btnCreate = $("<button>");
    btnCreate.html("Create");
    $("body").append(btnCreate);

    let btnClear = $("<button>");
    btnClear.html("Clear");
    $("body").append(btnClear);

    $("body").append("<br>");

    btnClear.on("click", function () {
        inputBoxName.val("");
        newBox.html("");
        newBox.remove("");

    })

    btnCreate.on("click", function () {

        for (let i = 0; i < inputBoxName; i++) {
            if (inputBoxName.val() = "red" ) {
                let newBox = $("<div>");
                box.add("box");
                box.add("red");
                $("body").append(newBox);
                newBox.on("click", function () {
                inputBoxName.val("");
                newBox.html("");
                newBox.remove("");
    }
)
            }
            else {
                 if (inputBoxName.val() = "blue" ) {
                let newBox = $("<div>");
                box.add("box");
                box.add("blue");
                $("body").append(newBox);
                newBox.on("click", function () {
                inputBoxName.val("");
                newBox.html("");
                newBox.remove("");
    }
)
                 }
                 else {
                if (inputBoxName.val() = "yellow" ) {
                let newBox = $("<div>");
                box.add("box");
                box.add("yellow");
                $("body").append(newBox);
                newBox.on("click", function () {
                inputBoxName.val("");
                newBox.html("");
                newBox.remove("");
    }
)
                 }
                 }
            }
        }

    });

}