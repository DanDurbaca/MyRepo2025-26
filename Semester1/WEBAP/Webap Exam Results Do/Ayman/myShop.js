$(function () {
  const BACKEND = "./server.php";

  function loadItemList() {
    $.getJSON(BACKEND, { getItems: "getItems" }, function (items) {
      const $list = $("#itemList");
      $list.empty();
      for (const it of items) {
        $list.append(
          $("<option>", { value: it.itemId, text: it.itemName })
        );
      }
    });
  }

  function refreshTables() {
    $("#items").load(BACKEND + "?getItems=getItemsTable");
    $("#orders").load(BACKEND + "?getItems=getOrdersTable");
  }

  $("#Order").on("click", function () {
    const itemId = $("#itemList").val();
    const qtyRaw = $("#Quantity").val();
    const qty = parseInt(qtyRaw, 10);

    $.post(
      BACKEND,
      { getItems: "placeOrder", itemId: itemId, qty: isNaN(qty) ? 0 : qty },
      function (msg) {
        $("#Messages").append($("<div>").text(msg));
        refreshTables(); 
      }
    );
  });

  loadItemList();     
  refreshTables();    
});
