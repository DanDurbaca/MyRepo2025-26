$(start);

let colorsArr = ["none", "red", "blue", "yellow"];
let countColors = [0, 0, 0, 0];
function createResultForColor(colorParam) {
    let myOutputResult = $("<div>");
    myOutputResult.attr("class", "out");
    let color = $("<div>");
    color.attr("class", "box " + colorParam);
    myOutputResult.append(color);
    let countInput = $("<input>");
    countInput.attr("disabled", "true");
    countInput.val(0);
    myOutputResult.append(countInput);
    $("body").append(myOutputResult);
    return countInput;
}

function start() {
    let countRed = createResultForColor("red");
    let countBlue = createResultForColor("blue");
    let countYellow = createResultForColor("yellow");
    let countNone = createResultForColor("none");
    for (let i = 0; i < 5; i++) {
        for (let j = 0; j < 10; j++) {
            let newDiv = $("<div>");
            let curColor = 0;
            countColors[curColor]++;
            newDiv.attr("class", "box " + colorsArr[curColor]);
            newDiv.on("click", function () {
                prevColor = curColor;
                curColor++;
                if (curColor == colorsArr.length) curColor = 0;
                countColors[curColor]++;
                countColors[prevColor]--;
                countRed.val(countColors[1]);
                countBlue.val(countColors[2]);
                countYellow.val(countColors[3]);
                countNone.val(countColors[0]);
                newDiv.attr("class", "box " + colorsArr[curColor]);
            });
            $("body").append(newDiv);
        }
        $("body").append("<br>");
    }
    countNone.val(countColors[0]);
}
