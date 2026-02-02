$(start);

function start(){
    let orderBtn = $("#Order");
    let input = $("#Quantity");

    $.get("serverAPI.php",{giveListOfItems:"1"},function(dataBack){
        $("#itemList").append(dataBack);
        //console.log(dataBack)
    });

    let table = $("<table>");
        $("#items").append(table);

    $.get("serverAPI.php",{giveListOfEverything:"1"},function(dataBack){
        $(table).append(dataBack);
    });

    $("#itemList").on("change",function(){$.get("serverAPI.php",{giveNumberOfItems:$("#itemList option:selected").val()}, function(databack){
        number = databack;
        if(number > 0){
            $("#orders").empty();
            $(orderBtn).on("click",function(){$.get("serverAPI.php",{createOrder:Number($(input).val()),orderedItemAvailibility:$("#itemList option:selected").val()},function(databack){
                $("#orders").html(databack);
            })}
        )}
        else{
            $("#orders").empty();
        }
    }
    )})





}