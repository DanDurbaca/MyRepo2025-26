$(start);

let currentFruit = null;
let quantityorder = null;

function start() {

// TASK 1

    $.get("server.php", function (data) {

        // The server returns <option> elements
        $("#itemList").html(data);
    });

        $.get("Secondserver.php", function (data) {

        // The server returns <option> elements
        $("#items").html(data);
    });

    $("#Order").on("click", function () {


        let itemId  = $("#itemList").val();
        let stock = parseInt($("#Quantity").val());

        // $.get("server.php", { itemId: itemId}, function (data){
        //     let availability = parseInt(data);

        //     if (availability === 0) {
        //         $("#orders").html("Out of stock");
        //     }
        // }

        $.post("server.php", { itemId: itemId, stock : stock  }, function (reply) {
            $("#Messages").html(reply);
         });


}
