$(start);

function createInput(inputName) {
    let newInput = $("<input>");
    newInput.attr("placeholder", inputName);
    newInput.attr("type", "number");

    $("body").append(newInput);
    return newInput;
}

function validateInputs(numRows, numCols, targetX, targetY) {
    if (targetX < 1) return false;
    if (targetX > numCols) return false;
    if (targetY < 1) return false;
    if (targetY > numRows) return false;

    return true;
}

function start() {
    let rows = createInput("Rows");
    let cols = createInput("Cols");
    $("body").append("<br>");
    let tx = createInput("TargetX");
    let ty = createInput("TargetY");
    $("body").append("<br>");
    let btnCreate = $("<button>");
    btnCreate.html("Create grid");
    $("body").append(btnCreate);

    btnCreate.on("click", function () {
        // I have access to the rows,cols,tx and ty inputs:
        r = Number(rows.val());
        c = Number(cols.val());
        txNumber = Number(tx.val());
        tyNumber = Number(ty.val());

        if (!validateInputs(r, c, txNumber, tyNumber)) {
            alert("Error validating inputs");
        } else {
            let myTbl = $("<table>");
            // alert("good inputs... go on");
            $("body").html("");
            for (let i = 1; i <= r; i++) {
                let newRow = $("<tr>");
                for (let j = 1; j <= c; j++) {
                    let newCell = $("<td>");
                    newCell.html("click");
                    newCell.on("click", function () {
                        if (i == tyNumber && j == txNumber) alert("yes");
                        else alert("no");
                    });
                    newRow.append(newCell);
                }
            }
            myTbl.append(newRow);
            $("body").append(myTbl);
            let rstButton = $("<button>");
            $("body").append(rstButton);
            rstButton.html("Reset");
            rstButton.on("click", function () {
                $("body").html("");
                start();
            });
        }
    });
}
