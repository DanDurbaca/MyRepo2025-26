$(start);

function start(){
    $.get("myShopAPI.php",{getItemList:"1"},function(databack){
        $("#itemList").append(databack);
        $("#Order").on("click",function(){$.get("myShopAPI.php",{selectItem:Number($("#itemList option:selected").val()),createOrderQuan:Number($("#Quantity").val())},function(databack){
            $("#Messages").html(databack);
            if(databack !="Out of stock" || databack !="This item does not exist"){
                $("#items").empty();
                $("#orders").empty();
                $.get("myShopAPI.php",{getItemQuantity:"1"},function(databack){
                    $("#items").html(databack);
                })
                $.get("myShopAPI.php",{getOrders:"1"},function(databack){
                    $("#orders").html(databack)
                })
            }
        })})
    });
    $.get("myShopAPI.php",{getItemQuantity:"1"},function(databack){
        $("#items").html(databack);
    })
    $.get("myShopAPI.php",{getOrders:"1"},function(databack){
        $("#orders").html(databack)
    })
}