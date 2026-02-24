$(script);

function script() {
    let startingAmountNone = 50;
    let startingAmountRed = 0;
    let startingAmountBlue = 0;
    let startingAmountYellow = 0;

    let outDivRed = $("<div class='out'>");
    $("body").append(outDivRed);
    let redBox = $("<div class='box red'>");
    let redInput = $("<input disabled='true' value='0'>");
    outDivRed.append(redBox);
    outDivRed.append(redInput);

    let outDivBlue = $("<div class='out'>");
    $("body").append(outDivBlue);
    let blueBox = $("<div class='box blue'>");
    let blueInput = $("<input disabled='true' value='0'>");
    outDivBlue.append(blueBox);
    outDivBlue.append(blueInput);

    let outDivYellow = $("<div class='out'>");
    $("body").append(outDivYellow);
    let yellowBox = $("<div class='box yellow'>");
    let yellowInput = $("<input disabled='true' value='0'>");
    outDivYellow.append(yellowBox);
    outDivYellow.append(yellowInput);

    let outDivNone = $("<div class='out'>");
    $("body").append(outDivNone);
    let noneBox = $("<div class='box none'>");
    let noneInput = $("<input disabled='true' value='0'>");
    outDivNone.append(noneBox);
    outDivNone.append(noneInput);

    noneInput.attr("value", startingAmountNone);
    redInput.attr("value", startingAmountRed);
    blueInput.attr("value", startingAmountBlue);
    yellowInput.attr("value", startingAmountYellow);

    for (let x = 1; x <= 5; x++) {
        for (let y = 1; y <= 10; y++) {
            let box = $("<div class='box none'>");
            box.on("click", function () {
                if (box.attr("class") == "box none") {
                    box.attr("class", "box red");
                    redInput.attr("value", (startingAmountRed += 1));
                    noneInput.attr("value", (startingAmountNone -= 1));
                    return box;
                }

                if (box.attr("class") == "box red") {
                    box.attr("class", "box blue");
                    blueInput.attr("value", (startingAmountBlue += 1));
                    redInput.attr("value", (startingAmountRed -= 1));
                    return box;
                }

                if (box.attr("class") == "box blue") {
                    box.attr("class", "box yellow");
                    yellowInput.attr("value", (startingAmountYellow += 1));
                    blueInput.attr("value", (startingAmountBlue -= 1));
                    return box;
                }

                if (box.attr("class") == "box yellow") {
                    box.attr("class", "box none");
                    noneInput.attr("value", (startingAmountNone += 1));
                    yellowInput.attr("value", (startingAmountYellow -= 1));
                    return box;
                }

                //box.attr("class", "box red");
                /* if (box.attr("class", "box red")){
                                                                redInput.attr("value", startingAmountRed+=1);
                                                            }
                                                            if (box.attr("class", "box blue")){
                                                                redInput.attr("value", startingAmountBlue+=1);
                                                            }
                                                            if (box.attr("class", "box yellow")){
                                                                redInput.attr("value", startingAmountYellow+=1);
                                                            } */

                /* box.on("click", function(){
                                                               if (box.attr("class", "box red")){
                                                                box.attr("class", "box blue");
                                                            }  
                                                            }) */
            });
            $("body").append(box);
        }
        $("body").append("<br>");
    }
}
