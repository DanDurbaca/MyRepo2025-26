$(start);


function start() {
    let outDiv = $("<div>");
    outDiv.attr("class", "out");
    let redDiv = $("<div>");
    redDiv.attr("class", "box red");
    let redDivInput = $("<input>")
    redDivInput.attr("disabled", "disabled");
    redDivInput.attr("value", "0");

    let outDiv2 = $("<div>");
    outDiv2.attr("class", "out");
    let blueDiv = $("<div>");
    blueDiv.attr("class", "box blue");
    let blueDivInput = $("<input>");
    blueDivInput.attr("disabled", "disabled");
    blueDivInput.attr("value", "0");

    let outDiv3 = $("<div>");
    outDiv3.attr("class", "out");
    let yellowDiv = $("<div>");
    yellowDiv.attr("class", "box yellow");
    let yellowDivInput = $("<input>");
    yellowDivInput.attr("disabled", "disabled");
    yellowDivInput.attr("value", "0");

    let outDiv4 = $("<div>");
    outDiv4.attr("class", "out");
    let colorlessDiv = $("<div>");
    colorlessDiv.attr("class", "box none");
    let colorlessDivInput = $("<input>");
    colorlessDivInput.attr("disabled", "disabled");
    colorlessDivInput.attr("value", "0")



    $("body").append(outDiv);
    $(outDiv).append(redDiv);
    $(outDiv).append(redDivInput);

    $("body").append(outDiv2);
    $(outDiv2).append(blueDiv);
    $(outDiv2).append(blueDivInput);

    $("body").append(outDiv3);
    $(outDiv3).append(yellowDiv);
    $(outDiv3).append(yellowDivInput);

    $("body").append(outDiv4);
    $(outDiv4).append(colorlessDiv);
    $(outDiv4).append(colorlessDivInput);


    for (i = 0; i < 10;i++) {
        let colorlessRows = $("<div>");
        colorlessRows.attr("class", "box none")
        $("body").append(colorlessRows);
    }
    $("body").append("<br>");

     for (i = 0; i < 10;i++) {
        let colorlessRows = $("<div>");
        colorlessRows.attr("class", "box none")
        $("body").append(colorlessRows);
    }
    $("body").append("<br>");

    for (i = 0; i < 10;i++) {
        let colorlessRows = $("<div>");
        colorlessRows.attr("class", "box none")
        $("body").append(colorlessRows);
    }
    $("body").append("<br>");

     for (i = 0; i < 10;i++) {
        let colorlessRows = $("<div>");
        colorlessRows.attr("class", "box none")
        $("body").append(colorlessRows);
    }
     $("body").append("<br>");

     for (i = 0; i < 10;i++) {
        let colorlessRows = $("<div>");
        colorlessRows.attr("class", "box none")
        colorlessRows.on("click" , function(){
          let Colors =  ["red" ,"blue" , "yellow" , "none"]

        })
        $("body").append(colorlessRows);
    }







}