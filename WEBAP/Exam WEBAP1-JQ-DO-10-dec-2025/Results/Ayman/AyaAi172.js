$(start);

let BoxContainer;

function start() {
    // Number od Boxes
    let BoxInput = $("<input>");
    BoxInput.attr("type", "number");
    BoxInput.attr("placeholder", "box");
    BoxInput.attr("id", "box");
    $("body").append(BoxInput);


    let Colorselct = $("<select>");
    $("body").append(Colorselct);


    let Opt = $("<option>");
    Opt.attr("value", 0);
    Opt.html("red");
    Colorselct.append(Opt);




    // Create  Box
    let Create = $("<button>");
    Create.html("Create Box");
    $("body").append(Create);

    // Clear button
    let btnClear = $("<button>");
    btnClear.html("Clear");
    $("body").append(btnClear);

    $("body").append("<hr>");

    // Container for the Boxes
    BoxContainer = $("<div>");
    $("body").append(BoxContainer);

    Create.on("click", function () {
        let box = Number(box.val());

        if (isNaN(box)) {
            alert("Please Write a number");
            return;
        }


        // Remove all previously created Boxes elements
        BoxContainer.empty();

        // Create Boxes
        let Boxes = $("<Boxes>");
        Boxes.attr("  ", "1");

        for (let b = 1; r <= Box; r++) {
            let tr = $("<tr>");
        }

        BoxContainer.append(Boxes);
    });

    btnClear.on("click", function () {
        $("#box").val("");

        BoxContainer.empty();
        Boxes = 0;
    });
}


