$(start);

function start() {
    let selectOfCountries = $("<select>");
    $("body").append(selectOfCountries);
    let btnShowCities = $("<button>");
    btnShowCities.html("Show cities");
    $("body").append(btnShowCities);
    $.get("serverAPI.php", { giveListOfCountries: "1" }, function (dataBack) {
        selectOfCountries.append(dataBack);
    });
    let citiesResult = $("<div>");
    $("body").append(citiesResult);
    btnShowCities.on("click", function () {
        $.get("serverAPI.php", { giveListOfCitiesBelongingTo: selectOfCountries.val() }, function (dataBack) {
            citiesResult.html(dataBack);
        });
    });
}
