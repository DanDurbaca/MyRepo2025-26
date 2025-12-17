$(start)

function start() {
    let redbox = makeColorBoxes("box red");
    let bluebox = makeColorBoxes("box blue");
    let yellowbox = makeColorBoxes("box yellow");
    let nonebox = makeColorBoxes("box none");

    let redCounter = 0;
    let blueCounter = 0;
    let yellowCounter = 0;
    let noneCounter = 0;
    for (let i = 0; i < 5; i++) {
        $("body").append("<br>")
        let boxY = $("<div>");
        boxY.attr("class", "box none")
        boxY.attr("id", "0");
        boxY.html(boxY.attr("id"));
        $("body").append(boxY);
        boxY.on("click",function(){
            id = Number(boxY.attr("id"));
            id++
            if (id == 0) {
                boxY.attr("class", "box none");
            }
            if (id == 1) {
                boxY.attr("class", "box red");
                
            }
            if (id == 2) {
                boxY.attr("class", "box blue");
            }
            if (id == 3) {
                boxY.attr("class", "box yellow");
            }
            if (id>3) {
                id = 0;
                boxY.attr("class", "box none");
            }
        })

        for (let y = 0; y < 10; y++) {
            noneCounter++
            let boxZ = $("<div>");
            boxZ.attr("class", "box none")
            boxZ.attr("id", "0");
            boxZ.html(boxZ.attr("id"));
            $("body").append(boxZ);
            boxZ.on("click",function(){
            id = Number(boxZ.attr("id"));
            id++
            if (id == 0) {
                boxZ.attr("class", "box none");
            }
            if (id == 1) {
                boxZ.attr("class", "box red");
                redCounter++
                blueCounter--
                yellowCounter--
                noneCounter--
                
            }
            if (id == 2) {
                boxZ.attr("class", "box blue");
                blueCounter++
                redCounter--
                yellowCounter--
            }
            if (id == 3) {
                boxZ.attr("class", "box yellow");
                yellowCounter++
                blueCounter--
                redCounter--
                noneCounter--
            }
            if (id>3) {
                id = 0;
                boxZ.attr("class", "box none");
                noneCounter++
                blueCounter--
                redCounter--
                yellowCounter--
            }
            })

        }
    }

}

function makeColorBoxes(name) {
    //adds label
    div = $("<div>");
    div.attr("class", name);
    div.html(" . ");

    $("body").append(div);
    //adds input
    let newInput = $("<input>");
    $(div).append(newInput);
    newInput.attr("placeholder", "0");
    newInput.attr("disabled", "true");
    //i wanted to impliment this in a better way but i didnt have enough timne , the hole thing is a manure
    //newInput.html(counter);

}

function makeBoxes() {
    //adds label
    box = $("<div>");
    box.attr("class", "box none");
    box.attr("id", "0");
    box.html(box.attr("id"));


    $("body").append(box);
}





// <div class="out">
//     <div class="box_red"></div>
//     <input placeholder="0" disabled="true">
// </div>

// <div class="out">
//     <div class="box_blue"></div>
//     <input placeholder="0" disabled="true">
// </div>

// <div class="out">
//     <div class="box_yellow"></div>
//     <input placeholder="0" disabled="true">
// </div>

// <div class="out">
//     <div class="box_none"></div>
//     <input placeholder="0" disabled="true">
// </div>
