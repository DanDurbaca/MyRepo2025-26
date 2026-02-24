function createBox(boxColor) {
    let newBox = $("<div>");
    newBox.attr("class", boxColor);
    $("body").append(newBox);
    return newBox;
}

function createInput(inClass) {
    let newInput = $("<input>");
    newInput.attr("class", inClass);
    newInput.attr("disabled", 1);
    newInput.val(0);
    $("body").append(newInput);
    return newInput;
}

function editBox(boxName,inClass){
    boxedit = boxName;
    boxedit.attr("class", inClass);
    return boxedit;
}

$(start);

function start() {
    let redBox = createBox("red box");
    let redIn = createInput("box input");
    $("body").append("<br>");
    let blueBox = createBox("blue box");
    let blueIn = createInput("box input");
    $("body").append("<br>");
    let yellowBox = createBox("yellow box");
    let yellIn = createInput("box input");
    $("body").append("<br>");
    let nan = createBox("box none");
    let nanIn = createInput("box input");
    nanIn.val(50);
    $("body").append("<br>");
    for(y=1;y<5;y++){
        let box = createBox("box none");
        box.on("click",function(){
                box.attr("class","red box");
                redIn.val(Number(redIn.val())+1);
                nanIn.val(Number(nanIn.val())-1);
            })
        for(x=1;x<10;x++){
            let box = createBox("box none");
            box.on("click",function(){
                box.attr("class","red box");
                redIn.val(Number(redIn.val())+1);
                nanIn.val(Number(nanIn.val())-1);
                box = redBox
            })
        }
        $("body").append("<br>");
    }
}
