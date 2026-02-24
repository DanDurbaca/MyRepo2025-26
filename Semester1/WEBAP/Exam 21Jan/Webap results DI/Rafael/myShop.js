$(start);

function start(){
    let order = $("#Order");
    order.attr("onclick", "order()");

    $.get("api.php", { FetchItems: "1" }, function(databack){
        let itemList = $("#itemList");
        itemList.append(databack);
    });

    $.get("api.php", { TableItems: "1" }, function(databack){
        let items = $("#items");
        items.append("<table>");
        items.append("<tr><th>Item</th><th>Quantity</th></tr>");
        items.append(databack);
    });

    $.get("api.php", { FetchOrders: "1" }, function(databack){
        let orders = $("#orders");
        orders.append("<br />")
        orders.append("<table>");
        orders.append("<tr><th>OrderId</th><th>ItemId</th><th>Quantity</th></tr>");
        orders.append(databack);
    })
}

function order(){
    let itemList = $("#itemList");
    let quantity = $("#Quantity");

    $.get("api.php", { SendItem: itemList.val(), SendQuantity: quantity.val() }, function(databack){
        let message = $("#Messages");
        message.html(databack);
    })
}