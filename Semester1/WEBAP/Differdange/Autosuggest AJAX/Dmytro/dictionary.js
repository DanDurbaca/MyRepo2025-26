//use on keydown - lookup at jquery and other javascript
$(start);

function start() {
    let wordSearchBar = $("<input id='wordSearch'>");
    $("body").append(wordSearchBar);
    let wordOutput = $("<li id='wordList'>");
    $("body").append(wordOutput);
    $(wordSearchBar).on("keydown", function () {
        $.get("servAPI.php", { findWords: wordSearchBar.val() }, function (dataBack) {
            $(wordOutput).html(dataBack);
        });
    });
}

function callFunc(param) {
    $("#wordSearch").val($("#" + param).html());
    $("#wordList").html("");
}
