$(document).ready(function () {
    function loaditem(selectedId) {
        $.getJSON("myshop.php", function (items) {

            var select = $("#itemList");
            select.empty();

            for (var i = 0; i < items.length; i++) {
                select.append('<option value="' + items[i].itemId + '">' + items[i].itemName + '</option>');
            }

            if (selectedId) {
                select.val(selectedId);
            } else {
                selectedId = select.val();
            }

            for (var i = 0; i < items.length; i++) {
                if (items[i].ItemId == selectedId) {
                    showitem(items[i]);
                    break;
                }
            }
        });
    }


    $("#items").change(function () {
        var itemId = $(this).val();
        $.getJSON("WebShop.php", function (items) {
            for (var i = 0; i < items.length; i++) {
                if (items[i].itemId == itemId) {
                    showitem(items[i]);
                    break;
                }
            }
        });
    });

    $(document).on("click", "#btnOrder", function () {
        var quantity = $("#itemOrder input").val();
        var itemId = $("#items").val();

        $.post("WebShop.php", { itemId: itemId, quantity: quantity }, function (result) {
            $("#itemResult").text(result);
            loaditem(itemId); 
        });
    });

    loaditem();
});
