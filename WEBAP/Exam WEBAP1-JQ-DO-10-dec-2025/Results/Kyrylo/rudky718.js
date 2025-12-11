$(start);

function start () {
    let num = $("<input>");
    num.attr("placeholder", "Number");
    num.attr("type", "number");
    $("body").append(num);

    let Options = ["red", "blue", "yellow"];

    let colorSelect = $("<select>");
    for (let item of Options) {
        let opt = $("<option>");
        opt.html(item);
        colorSelect.append(opt);
    }
    $("body").append(colorSelect);

    let createButton = $("<button>");
    createButton.html("Create");
    $("body").append(createButton); 

    let clearButton = $("<button>");
    clearButton.html("Clear");
    $("body").append(clearButton); 

    let allBoxes = [];
    createButton.on("click", function () {
        let color = colorSelect.val();
        let amount = num.val().trim();
    for (let i = 0; i < amount; i++) {
        let box = $("<div>");
        box.addClass("box");
        box.addClass(color);
        $("body").append(box);
        allBoxes.push(box);
        box.on("click", function () {
            box.removeClass("box");
            });
    }
        });
    clearButton.on("click", function () {
            $("body").html("");
            start();
        });


}