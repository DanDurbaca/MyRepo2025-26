$(start);

function start() {

    refreshAll();

    $("#Order").on("click", function () {

        let itemId = $("#itemList").val();
        let qty = $("#Quantity").val();

        $.ajax({
            url: "myShopServer.php",
            type: "GET",
            data: {
                action: "placeOrder",
                itemId: itemId,
                qty: qty
            },
            success: function (reply) {
                $("#Messages").append(reply + "<br>");
                $("#Quantity").val("");
                refreshAll();
            }
        });
    });
}

function refreshAll() {

    /* TASK 1 – fill select */
    $.ajax({
        url: "myShopServer.php",
        type: "GET",
        data: { action: "getItemsOptions" },
        success: function (data) {
            $("#itemList").html(data);
        }
    });

    /* TASK 1 + TASK 3 – items table */
    $.ajax({
        url: "myShopServer.php",
        type: "GET",
        data: { action: "getItemsTable" },
        success: function (data) {
            $("#items").html(data);
        }
    });

    /* TASK 3 – orders table */
    $.ajax({
        url: "myShopServer.php",
        type: "GET",
        data: { action: "getOrdersTable" },
        success: function (data) {
            $("#orders").html(data);
        }
    });
}
