$(start);
function createdivs(color){
    let newdiv = $("<div>");
    newdiv.attr("class", color);
    $(".out").append(newdiv);
    return 
}
function createInput(inVal){
    let newInput = $("<input>");
    newInput.attr("type", "number");
    newInput.val(inVal);
    $(".out").append(newInput);
    return 
}
function start(){
    let newDiv1 = $("<div>");
    newDiv1.attr("class", "out");
    $("body").append(newDiv1);

    let d1 = createdivs("box red");
    $(".out").append(d1);
    let d2 = createdivs("box blue");
    $(".out").append(d2);
    let d3 = createdivs("box yellow");
    $(".out").append(d3);
    let d4 = createdivs("box none");
    $(".out").append(d4);
    $("div").append("<br>");
    let i1 = createInput("0");
    $(".out").append(i1);
    let i2 = createInput("0");
    $(".out").append(i2);
    let i3 = createInput("0");
    $(".out").append(i3);
    let i4 = createInput("0");
    $(".out").append(i4);
}