$(start)


let colors = ["red", "blue", "yellow"]




function start() {


    let inputbox = $("<input>");
    inputbox.attr("placeholder", "Quantity");
    $("body").append(inputbox);

    let btncreate = $("<button>"); // This button should create the boxes
    btncreate.html("Create");
    $("body").append(btncreate);
    $("body").append("<br>");

    let btnclear = $("<button>"); // This button should clear the boxes
    btnclear.html("Clear");
    $("body").append(btnclear);
    $("body").append("<br>");

    let selectlist = $("<select>");
    for (let i = 0; i < colors.length; i++) {
        let colorsitem = $("<option>");
        colorsitem.attr("value", i);
        colorsitem.html(colors[i]);
        selectlist.append(colorsitem);
    }
    $("body").append(selectlist);




    btncreate.on("click", function () {
        if (inputbox.val() == "") {
            let newcubes = $("<div>");
            let cubes = $("<p>");
            cubes.html(inputbox.val());
            newrecubes.append(cubes);
            let colorcubes = $("<ul>");
            newcubes.append(colorcubes);
            $("body").append(newcubes);
            currentboxes = colorcubes;
}
    });
    btncreate.on("click", function () {
        if (currentboxes = !0) {
            let newli = $("<li>");
            newli.html(Options[selectlist.val()] + " - " + quantityinput.val());
            currentboxes.append(newli);

            quantityinput.val("");
        }

    });
}
