$(start);

function start() {
    $("#LoadPeople").on("click", function () {
        // we need to start an AJAX request:
        $.get("LoadPeople.php", { cityName: $("#cityNameFilter").val() }, callbackFromServer);
    });

    let counter = 0;
    setInterval(function () {
        console.log("Another second passed " + counter);
        counter++;
    }, 1000);
}

function callbackFromServer(responseFromServer) {
    // this function is called when the $.get request has finished and we are
    // given back what the server replied into the responseFromServer parameter
    $("#resultingPpl").html(responseFromServer);
}
