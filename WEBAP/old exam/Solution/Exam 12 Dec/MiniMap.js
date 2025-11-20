$(start);

function validate(rows, cols, tx, ty) {
    rows = Number(rows);
    cols = Number(cols);
    tx = Number(tx);
    ty = Number(ty);
    if (rows <= 0) return false;
    if (cols <= 0) return false;
    if (tx <= 0) return false;
    if (ty <= 0) return false;
    if (tx >= cols) return false;
    if (ty >= rows) return false;

    return true;
}

function start() {
    $("body").html("");
    let divProps = $("<div>");
    let columns = $("<input>");
    columns.attr("type", "number");
    let rows = $("<input>");
    rows.attr("type", "number");
    divProps.append(rows);
    divProps.append(columns);

    let divStart = $("<div>");
    let targetX = $("<input>");
    targetX.attr("type", "number");
    let targetY = $("<input>");
    targetY.attr("type", "number");
    divStart.append(targetX);
    divStart.append(targetY);
    let btnGo = $("<button>");
    btnGo.html("Create grid");
    columns.attr("placeholder", "Cols");
    rows.attr("placeholder", "Rows");
    targetX.attr("placeholder", "Target-X");
    targetY.attr("placeholder", "Target-Y");

    btnGo.on("click", function () {
        if (!validate(rows.val(), columns.val(), targetX.val(), targetY.val())) alert("Wrong input given");
        else {
            divProps.remove();
            divStart.remove();
            btnGo.remove();
            let newTbl = $("<table>");
            for (let i = 0; i < rows.val(); i++) {
                let newRow = $("<tr>");
                for (let j = 0; j < columns.val(); j++) {
                    let curCell = $("<td>");
                    curCell.html("click");
                    curCell.on("click", function () {
                        if (i + 1 == targetX.val() && j + 1 == targetY.val()) alert("yes");
                        else alert("no");
                    });
                    newRow.append(curCell);
                }
                newTbl.append(newRow);
            }
            $("body").append(newTbl);
            let btnReset = $("<button>");
            btnReset.html("Reset");
            btnReset.on("click", start);
            $("body").append(btnReset);
        }
    });

    $("body").append(divProps);
    $("body").append(divStart);
    $("body").append(btnGo);
}
