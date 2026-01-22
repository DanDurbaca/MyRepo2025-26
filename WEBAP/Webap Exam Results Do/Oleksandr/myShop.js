$(start);

function start(){
    $.get("server.php", { action: "getItems" }, function (data) {
        $("#itemList").html(data);
    });

    $.get("server.php", { action: "getTable" }, function (data) {
        $("#items").html(data);
    });

    $.get("server.php", { action: "getOrderTable" }, function (data) {
        $("#orders").html(data);
    });

    $("#Order").on("click", function(){
        let $itemId =  $("#itemList").val();
        let $amount =  $("#Quantity").val();

        $.get("server.php", { action: "order",  itemId: $itemId , amount:$amount }, function (data) {
        $("#Messages").append(data);
        $("#Messages").append("<br>");
        
        $.get("server.php", { action: "getTable" }, function (data) {
        $("#items").html(data);
        });

        $.get("server.php", { action: "getOrderTable" }, function (data) {
        $("#orders").html(data);
        });

        });


    });


};