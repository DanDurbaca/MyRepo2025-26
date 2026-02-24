$(document).ready(function () {
    loadItems();
    refreshTables();

    $('#Order').click(function () {
        var selectedItemId = $('#itemList').val();
        var quantity = $('#Quantity').val();

        if (!selectedItemId || !quantity) {
            $('#Messages').append('<p>Please select an item and enter a quantity.</p>');
            return;
        }

        $.post('processOrder.php', {
            action: 'placeOrder',
            itemId: selectedItemId,
            quantity: quantity
        }, function (response) {
            $('#Messages').append('<p>' + response + '</p>');
            refreshTables();
        });
    });

    function loadItems() {
        $.post('processOrder.php', {
            action: 'loadItems'
        }, function (response) {
            $('#itemList').html(response);
        });
    }

    function refreshTables() {
        $.post('processOrder.php', {
            action: 'refresh'
        }, function (response) {
            var data = JSON.parse(response);
            $('#items').html(data.items);
            $('#orders').html(data.orders);
        });
    }
});
