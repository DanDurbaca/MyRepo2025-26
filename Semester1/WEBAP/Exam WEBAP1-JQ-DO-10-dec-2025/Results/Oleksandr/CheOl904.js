$(start);

function start(){
    let input = $("<input>");
    input.attr("type","number");
    $("body").append(input);

    let colors = ["red", "blue", "yellow"];
    selectColor = $("<select>");
    for (let i = 0; i < colors.length ; i++) {

        let optionColor = $("<option>");
        optionColor.attr("value", i);
        optionColor.html(colors[i]);

        selectColor.append(optionColor);
    }
    $("body").append(selectColor);

    let createBtn = $("<button>");
    createBtn.html("Create");
    $("body").append(createBtn);


    createBtn.on("click", function(){
    for (let c =0; c<Number(input.val()) ; c++){
        let box = $("<div>");
        if(selectColor.val() == 0)
            box.attr("class","box red");
        if(selectColor.val() == 1)
            box.attr("class","box blue");
        if(selectColor.val() == 2)
            box.attr("class","box yellow");
        box.on("click", function(){
            box.remove();
        });
        $("body").append(box);
    }
   
    });

    let clearBtn = $("<button>");
    clearBtn.html("Clear");
    $("body").append(clearBtn);
    
    clearBtn.on("click", function(){
        $("body").html("");
        $(start);
    });
};

