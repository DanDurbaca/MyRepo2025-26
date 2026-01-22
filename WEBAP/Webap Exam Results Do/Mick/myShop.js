$(document).ready(function() {
    $.get("server-side.php", { action: "getItems"}, function(data){
        $("#itemList").html("");
        data.forEach(function(item){
            $("#itemList").append(`<option value="${item.itemId}">${item.itemName}</option>`);
        });
    }, "json");

    
});