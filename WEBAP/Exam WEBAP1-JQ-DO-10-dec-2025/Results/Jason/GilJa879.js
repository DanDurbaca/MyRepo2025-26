$(start);

function start() {
    input1 = $("<input>");
    input1.attr("type", "number");
    input1.attr("id", "A");
    input1.attr("placeholder", "Number");
    $("body").append(input1);

    let select = $("<select>").attr("id", "mySelect");

    select.append($("<option>").val("red").text("Red"));
    select.append($("<option>").val("blue").text("Blue"));
    select.append($("<option>").val("yellow").text("Yellow"));

    $("body").append(select);

    Createbtn = $("<button>");
    Createbtn.attr("id", "cbtn");
    Createbtn.html("createblock");
    $("body").append(Createbtn);
    $("#cbtn").on("click", createblock);

    clearbtn = $("<button>");
    clearbtn.attr("id", "clbtn");
    clearbtn.html("Clear");
    $("body").append(clearbtn);
    $("#clbtn").on("click", clear);
}

function createblock() {
    let rows = parseInt($("#A").val());
    let boxcolor = $("#mySelect").val();

    for (let i = 0; i < rows; i++) {
        newDiv = $("<div>");
        newDiv.html("test");
        newDiv.attr("class", "box");
        newDiv.attr("class", boxcolor);
        $("body").append(newDiv);
    }
}

function clear() {
    start();
}
