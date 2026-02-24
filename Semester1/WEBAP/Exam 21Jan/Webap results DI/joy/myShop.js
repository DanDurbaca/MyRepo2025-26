$(start);

function start(){
   $.get("server-API.php",{ giveListOfItems: "1" } , function (dataBack) {
    $("#itemList").append(dataBack);
})}