$(start);

function start() {
    $("#LoadPeople").on("click", function () {
        // we need to start an AJAX request:
        $.get("LoadPeople.php", { cityName: $("#cityNameFilter").val() }, callbackFromServer);
    });
}

function callbackFromServer(responseFromServer) {
    // this function is called when the $.get request has finished and we are
    // given back what the server replied into the responseFromServer parameter
    $("#resultingPpl").html(responseFromServer);
}
