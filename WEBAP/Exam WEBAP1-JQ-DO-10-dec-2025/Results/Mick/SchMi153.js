$(start);

let Options = ["Red","Blue","Yellow"]

function start(){
    let inputbox = $("<input>");
    inputbox.attr ("type", "number");
    inputbox.attr ("id","input");
    inputbox.attr ("placeholder", "Type a number!");
    $("body").append(inputbox);

    let selectbox = $("<select>");
    for (let i = 0; i < Options.length; i++){
        let newselect = $("<option>");
        newselect.attr("value",i);
        newselect.html(Options[i]);
        selectbox.append(newselect);
    }
    $("body").append(selectbox);

    let createbutton = $("<button>");
    createbutton.html("Create");
    createbutton.attr("id", "create");
    $("body").append(createbutton);
    $("#create").on("click", createrow);

    let clearbutton = $("<button>");
    clearbutton.html("Clear");
    createbutton.attr("id", "remove");
    $("body").append(clearbutton);
    $("#remove").on("click", rmrow);
}

function createrow(){
    box = parseInt($("#input").val());
    for(let b = 1 ; b <= box; b++){
        let div = $("<div>");
        div.html("hello");
        $("body").append(div);
    }
}

function rmrow(){
    
}