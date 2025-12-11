$(start);

let Options = ["Red", "Blue", "Yellow"]; //One HTML select element with three options that will display: red, blue, and yellow.

function start() {
    let inputQuantity =$("<input>");
    inputQuantity.attr("placeholder", "Quantity");
    $("body").append(inputQuantity); //One input box 

    let selectlist = $("<select>");
    for (let i = 0; i < Options.length; i++) {
        let optionitem = $("<option>");
        optionitem.attr("value", i);
        optionitem.html(Options[i]);
        selectlist.append(optionitem);
    } // Give each of the options an integer value: 0,1 and respectively 2
     
    $("body").append(selectlist);

    let btncreate = $("<button>");
    btncreate.html("Create"); // One button with the text “Create”
    $("body").append(btncreate);

    let btnclear = $("<button>");
    btnclear.html("Clear"); //One button with the text “Clear”. 
    $("body").append(btnclear);

    let Quantity = parseInt($("#inputQuantity").val());

    btncreate.on("click", function () {
        for (let a = 0; a < Quantity; a++) {
            if (Options[i] == 0){
            let cell = "<span class='cell' click>.red </span>";
            $("body").append(cell);
            }
            if (Options[i] == 1){
             $("body").append($("<div>").attr("class", "box"));
            }
            if (Options[i] == 2){
             $("body").append($("<div>").attr("class", ".yellow"));
            }
            }

    });


    btnclear.on("click", function () {
        location.reload(); //Erase all created divs and restart everything 
    });

    $(".box").on("click", function () {
    if ($(this).hasClass("boxClick")) {
      $(this).removeClass("boxClick").addClass("boxNoClick");
        //o	Add a new function that removes the box from the DOM tree when the user clicks on it
    }
});

}
