$(start);

function createBox(color){

    let div = $("<div>").attr({
        class: color
    });

    let input = $("<input>").attr({
        readonly: "readonly",
        value: 0
    });

    $("body").append(div);
    $("body").append(input);
    $("body").append("<br>");

    return input;
}



function createGrid(){
    for(i = 1; i <= 5; i++){
        for(j = 1; j <= 10; j++){
            let newDiv = $("<div>").attr({
                class: "box none"
            });

            $("body").append(newDiv);
        }

        $("body").append("<br>");
        
    }
}

function start(){
    $("body").html("");

    $('head').append(`
        <style>
            body { font-family: Arial, sans-serif; padding: 2px; }
            input, select, button { margin: 5px; padding: 8px; }
        </style>
    `);

    createBox("box red");
    createBox("box blue");
    createBox("box yellow");
    createBox("box none");
    
    createGrid();
}