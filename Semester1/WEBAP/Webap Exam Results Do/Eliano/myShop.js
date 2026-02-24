$(document).ready(function () {
    function loaditems(selectedId) {
        $.getJSON("serverside.php", function (item) {

            var select = $("#itemList");
            select.empty();

            for (var i = 0; i < items.length; i++) {
                select.append('<option value="' + items[i].itemId + '">' + items[i].itemId + '</option>');
            }
            
                selectedId = select.val();
            

            for (var i = 0; i < Items.length; i++) {
                if (Items[i].itemId == selectedId) {
                    showitem(items[i]);
                    break;
                }
            }
        });
    }


    $(document).on("click", "#btnOrder", function () {
        var itemId = $("#itemList").val();

        $.post("serverside.php", { itemId: itemId, stock: stock }, function (result) {
            $("#orders").text(result);
            loaditems(itemId); 
        });
    });

    loaditems();
});
