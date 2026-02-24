$(start);

function start(){
 
    $.getJSON("server-side.php" , function(data){
     let products = $items;   

     let itemList = $("#itemList");
    for(i = 0 ; i < products.length ; i++){
        let newProduct = $("<option>")
        newProduct.attr("id", products[i]);
        newProduct.html(products[i]);
        itemList.append(newProduct);
    }
    $("body").append(itemList);

    })
    
    }