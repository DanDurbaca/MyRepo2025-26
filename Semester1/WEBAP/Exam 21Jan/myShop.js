$(start);

function start() {
    fillInSelect();
    loadItems();
    loadOrders();
    $("#Order").on("click", placeOrder);
}

function fillInSelect() {
    $.get("serverAPI.php", { listItems: 0 }, function (dataBack) {
        $("#itemList").append(dataBack);
    });
}

function placeOrder() {
    $.post("serverAPI.php", { orderItem: $("#itemList").val(), orderQty: $("#Quantity").val() }, function (dataBack) {
        let newdiv = $("<div>");
        newdiv.append(dataBack);
        $("#Messages").append(newdiv);
        loadOrders();
        loadItems();
    });
}

function loadItems() {
    $.get("serverAPI.php", { viewStocks: 0 }, function (dataBack) {
        $("#items").html(dataBack);
    });
}

function loadOrders() {
    $.get("serverAPI.php", { viewOrders: 0 }, function (dataBack) {
        $("#orders").html(dataBack);
    });
}
