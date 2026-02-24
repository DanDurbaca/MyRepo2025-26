$(document).ready(function () {

    loadItems();
    loadOrders();

    function loadItems() {
        $.get("api.php", { action: "getItems" }, function (data) {
            let res = JSON.parse(data);

            $("#itemList").empty();
            res.forEach(i => {
                $("#itemList").append(
                    `<option value="${i.itemId}">${i.itemName}</option>`
                );
            });

            let html = "<table border='1'><tr><th>Item</th><th>Stock</th></tr>";
            res.forEach(i => {
                html += `<tr><td>${i.itemName}</td><td>${i.stock}</td></tr>`;
            });
            html += "</table>";

            $("#items").html(html);
        });
    }

    function loadOrders() {
        $.get("api.php", { action: "getOrders" }, function (data) {
            let res = JSON.parse(data);

            let html = "<table border='1'><tr><th>OrderId</th><th>ItemId</th><th>Qty</th></tr>";
            res.forEach(o => {
                html += `<tr><td>${o.OrderId}</td><td>${o.itemId}</td><td>${o.quantity}</td></tr>`;
            });
            html += "</table>";

            $("#orders").html(html);
        });
    }

    $("#Order").click(function () {
        let itemId = $("#itemList").val();
        let qty = $("#Quantity").val();

        $.post("api.php", {
            action: "order",
            itemId: itemId,
            quantity: qty
        }, function (msg) {

            $("#Messages").append(msg + "<br>");

            loadItems();
            loadOrders();
        });
    });

});
