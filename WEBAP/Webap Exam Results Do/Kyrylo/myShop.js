$(start);
function start() {
   $.get("server.php", { action: "getItems" }, function (data) {
        $("#itemList").html(data);
    });

    $.get("server.php", { action: "showItems" }, function (data) {
        $("#items").html(data);
    });

    
}