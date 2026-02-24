$(start);

function start() {
    // search bar
    let searchBar = $("<input>");
    searchBar.attr("list", "browsers");
    $("body").append(searchBar);

    // datalist
    let dataList = $("<datalist>");
    dataList.attr("id", "browsers");
    $("body").append(dataList);

    //when the user types something, get suggestions from the server
    searchBar.on("input", function () {
        //get request to the server with the current value of the search bar
        $.get(
            "serverAPI.php",
            { giveListOfSuggestions: searchBar.val() },
            //callback function to handle the server's response
            function (answer) {
                //fill out the datalist with the received suggestions
                dataList.html(answer);
            }
        );
    });
}
