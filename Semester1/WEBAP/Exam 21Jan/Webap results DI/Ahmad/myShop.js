$(start)
function start() {
    let selectOfItems = $("#itemList");
    let placeOrder = $("#Order");
    let items = $("#items");
    let table = $("<table>");
    items.append(table);
    let msg = $("#Messages");
    let qty = $("#Quantity");
    let orders = $("#orders");

    placeOrder.on("click", function () {
 
        // $.get("serverApi.php", { checkItem: selectedItem }, function (dataBack) {
        //     if (dataBack == 1) {

        //     }
        //     else {
        //         msg.html("This item does not exis");
        //     }
        // })
        $.get("serverApi.php", { checkStock: selectOfItems.val() }, function (dataBack) {
            if (dataBack < qty) {
                msg.html("Out of stock");
                console.log(dataBack);
            }
            else {

            }
        })
        $.post("serverApi.php", { writeOrder: selectOfItems.val()}, function (dataBack) {
            orders.append(dataBack);
        })
        orders.html("Apples " + qty.val());
    })
    $.get("serverApi.php", { giveListOFItems: "1" }, function (dataBack) {
        selectOfItems.append(dataBack);
    })

    $.get("serverApi.php", { giveItems: "1" }, function (dataBack) {
        table.append(dataBack);
    })
    // let citiesResult= $("<div>");
    // $("body").append(citiesResult);   
    // btnShowCities.on("click",function(){
    //     $.get("serverApi.php",{giveListOfCitiesBelongingTo:selectOfCountries.val()},function(dataBack){
    //         citiesResult.html(dataBack);
    //     })
    // })
}
