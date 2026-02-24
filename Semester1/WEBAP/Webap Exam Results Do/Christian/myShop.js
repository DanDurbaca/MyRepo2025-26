$(start);

function start() {
  $.post(
    "myShop.php",
    { selectfruits: 1 },
    function (response) {
      let table = document.createElement("table");
      let td = document.createElement("td");
      let th = document.createElement("th");
      let th1 = document.createElement("th");
      th.textContent = "itemName";
      th1.textContent = "stock";
      table.append(td);
      td.append(th);
      td.append(th1);
      response.forEach((fruit) => {
        // for select tag
        let option = document.createElement("option");
        option.value = fruit.itemId;
        option.textContent = fruit.itemName;
        $("#itemList").append(option);

        // for table
        let tr = null;
        tr = document.createElement("tr");
        let td = document.createElement("td");
        let td1 = document.createElement("td");
        td.textContent = fruit.itemName;
        td1.textContent = fruit.stock;
        $(tr).append(td);
        $(tr).append(td1);
        $(table).append(tr);
        $("#items");
      });
      $(document.body).append(table);
    },
    "json"
  );
  $("#Order").on("click", function () {
    console.log($("#Quantity").val());
    console.log($("itemList").val());
    $.post(
      "myShop.php",
      { order: 1, quantity: $("#Quantity").val(), itemId: $("itemList").val() },
      function (response) {
        console.log(response);
      }
    );
  });
}
