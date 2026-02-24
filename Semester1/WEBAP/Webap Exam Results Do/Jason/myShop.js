  let itemId = 0;
  
  $.get("WebServer.php", { itemId: itemId }, function (replyFromServer) {
           
            $("#itemList").html("");
           
            replyFromServer.forEach(function(Items) {
            $("#itemList").append(`<option value="${Items.itemId}">${Items.itemName}</option>`);
        });
    }, "json");
           
     $("#itemList").change(function() {
      
        var itemId = $(this).val();
   
        $.get("WebShop.php", { action: "getQuantity", itemId: itemId }, function(replyFromServer) {
            
            if (replyFromServer.quantity == 0) {
               
                $("#ItemData").html("Out of stock");
                $("#ItemData").html(""); 
            } else {
               
                $("#ItemData").html(replyFromServer.quantity + " in stock");
           
                $("#ItemData").html(`<input type="number" id="orderQty" min="1" value="1"><button id="orderBtn">Order</button>`);
            }
        }, "json"); 
    });
    
   $(document).on("click", "#Order", function() {
      
        var itemId = $("#itemList").val();
        var qty = parseInt($("#orderQty").val());
        
        if (isNaN(qty) || qty <= 0) {
            $("#OrderResult").html("Please enter a valid order");
            return; 
        }
        
        $.post("WebShop.php", { action: "placeOrder", itemId: itemId, qty: qty }, function(replyFromServer) {
            
            $("#OrderResult").html(replyFromServer.message); 
            
            $("#itemList").trigger("change");
        }, "json"); 
    });