$(start);

function start() {
    $.get("https://www.spuerkeess.lu/fr/particuliers/", backCall);
}

function backCall(dataBack) {
    $("body").append(dataBack);
}
