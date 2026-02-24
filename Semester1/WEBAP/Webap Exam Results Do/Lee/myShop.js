$(start)

function start() {
    $("#items").append("<table><th>Item</th><th>Quantity</th></table>")

    $("#Order").on("click", function () {
    $.post("server-side.php", { 
        ItemName: $("#itemList").val(),
        quantity: $("#Quantity").val()
    },orderItem)})

    $.post("server-side.php", { display: "load" }, displaySelect)
    $.post("server-side.php", { displayItems: "load" }, displayItem)    
}

function displaySelect(items) {
    $("#itemList").append(items)
}

function displayItem(show) {
    $("#items").append(show)
}

function orderItem(result) {
    $("#Messages").html(result)
    $("#itemList").html("")
    $("#items").html("")
    $.post("server-side.php", { displayOrder: "load" }, displayOrder)
    $.post("server-side.php", { display: "load" }, displaySelect)
    $("#items").append("<table><th>Item</th><th>Quantity</th></table>")
    $.post("server-side.php", { displayItems: "load" }, displayItem)
    
}

function displayOrder(show) {
    $("#items").append(show)
}

