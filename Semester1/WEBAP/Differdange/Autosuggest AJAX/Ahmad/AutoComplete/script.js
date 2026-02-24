$(start);

function start() {
    //adds elements
    let textBox = $("<input>");
    textBox.attr("type", "text").attr("name", "input").attr("id", "input");
    $("body").append(textBox);
    let sugDiv = $("<div>");
    sugDiv.attr("id", "sug");
    $("body").append(sugDiv);

    // send API call to your PHP server
    $(textBox).on("input", function () {
        $.get("wordServer.php", { giveSuggestedWord: textBox.val() + "%" }, function (dataBack) {
            sugDiv.html(dataBack);
        });
    });
}
function rename(elementString) {
    let s = $("#" + elementString).html();
    console.log(s);
    $("#input").val(s);
}
