$(document).ready(function () {
  refreshAll();

  $("#Order").click(function () {
    placeOrder();
  });
});

function refreshAll() {
  loadItems();
  loadOrders();
}

function loadItems() {
  $.ajax({
    url: "getItems.php",
    method: "GET",
    dataType: "json",
    success: function (data) {
      // SELECT LIST
      $("#itemList").empty();
      data.forEach(function (item) {
        $("#itemList").append(
          $("<option>").val(item.itemId).text(item.itemName),
        );
      });

      // ITEMS TABLE
      let table = $("<table border='1'>");
      table.append("<tr><th>Item</th><th>Quantity</th></tr>");

      data.forEach(function (item) {
        table.append(
          $("<tr>")
            .append($("<td>").text(item.itemName))
            .append($("<td>").text(item.stock)),
        );
      });

      $("#items").html(table); // FULL REPLACE
    },
  });
}

function loadOrders() {
  $.ajax({
    url: "getOrders.php",
    method: "GET",
    dataType: "json",
    success: function (data) {
      let table = $("<table border='1'>");
      table.append("<tr><th>Order ID</th><th>Item</th><th>Quantity</th></tr>");

      data.forEach(function (order) {
        table.append(
          $("<tr>")
            .append($("<td>").text(order.OrderId))
            .append($("<td>").text(order.itemName))
            .append($("<td>").text(order.quantity)),
        );
      });

      $("#orders").html(table); // FULL REPLACE
    },
  });
}

function placeOrder() {
  let itemId = $("#itemList").val();
  let qty = $("#Quantity").val();

  $.ajax({
    url: "placeOrder.php",
    method: "POST",
    data: {
      itemId: itemId,
      quantity: qty,
    },
    success: function (response) {
      $("#Messages").append("<p>" + response + "</p>");
      refreshAll();
    },
  });
}
