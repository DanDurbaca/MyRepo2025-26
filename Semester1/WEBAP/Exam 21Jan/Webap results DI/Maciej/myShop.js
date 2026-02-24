$(start);

function start(){

    
    $.get("serverAPI.php",{giveListOfItems:"1"},function(databack){
        let selectOptions = $("<option>");
        $("#itemList").append(databack);
        $("#itemList").append(selectOptions);
    });

    $.get("serverAPI.php",{giveListOfItems:"1"},function(databack){
        /*let myTable = $("<table>");
            $("body").html("");
            let titleRow = $("<tr>");
            titleRow.html("");
            for (let i = 1; i <= RNumber; i++) {
                let newRow = $("<tr>");
                for (let j = 1; j <= CNumber; j++) {
                    let newColumn = $("<td>");
                    newColumn.html("");
                    newRow.append(newColumn);
                }
                myTable.append(newRow);
            }
            $("body").append(myTable);
        */
        $("#items").append(databack);
    });



}