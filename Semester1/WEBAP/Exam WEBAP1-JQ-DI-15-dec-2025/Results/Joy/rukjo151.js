$(start);
function start(){
    let redOut = $("<div>");
    redOut.attr("class", "out");
    $("body").append(redOut);
    let boxRed = $("<div>");
    boxRed.attr("class", "box red");
    $("body").append(boxRed);
    let redInput = $("<input>");
    redInput.attr("value", "0");
    $("body").append(redInput);

    let blueOut = $("<div>");
    blueOut.attr("class", "out");
    $("body").append(blueOut);
    let boxBlue = $("<div>");
    boxBlue.attr("class", "box blue");
    $("body").append(boxBlue);
    let blueInput = $("<input>");
    blueInput.attr("value", "0");
    $("body").append(blueInput);

    let yellowOut = $("<div>");
    yellowOut.attr("class", "out");
    $("body").append(yellowOut);
    let boxYellow = $("<div>");
    boxYellow.attr("class", "box yellow");
    $("body").append(boxYellow);
    let yellowInput = $("<input>");
    yellowInput.attr("value", "0");
    $("body").append(yellowInput);

    let noneOut = $("<div>");
    noneOut.attr("class", "out");
    $("body").append(noneOut);
    let boxNone = $("<div>");
    boxNone.attr("class", "box none");
    $("body").append(boxNone);
    let noneInput = $("<input>");
    noneInput.attr("value", "0");
    $("body").append(noneInput);
    
    let gridNone = $("<grid>");
    $("body").append(gridNone);
    for(let i = 0; i <= 50; i++){
        let gridDiv = ("<div>");
        gridDiv.html(i);
        gridDiv.attr("class", "box none");
        $("body").append(gridDiv);
    }
    
}
