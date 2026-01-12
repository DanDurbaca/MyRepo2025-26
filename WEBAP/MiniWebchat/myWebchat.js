$(start);
let currentUser = null;
let lastMessageSeenId = 0;

function start() {
    let userNameInput = $("<input>");
    userNameInput.attr("placeholder", "Please type your username:");
    $("body").append(userNameInput);
    let btnStartChat = $("<button>");
    btnStartChat.html("Start chat");
    $("body").append(btnStartChat);
    btnStartChat.on("click", function () {
        $("body").html("");
        currentUser = userNameInput.val();
        $.post("ServerAPI.php", { UserName: currentUser }, function (dataBack) {
            // nothing to do when the server replies !!
            console.log(dataBack);
        });
        let messagesDiv = $("<div>");
        messagesDiv.attr("class", "myMessages");
        $("body").append(messagesDiv);
        let newMessageInput = $("<input>");
        newMessageInput.attr("placeholder", "new message");
        $("body").append(newMessageInput);
        let sendMessgeBtn = $("<button>");
        sendMessgeBtn.html("Send");
        $("body").append(sendMessgeBtn);
        sendMessgeBtn.on("click", function () {
            let messageToSend = newMessageInput.val();
            newMessageInput.val("");
            $.post("serverAPI.php", { user: currentUser, message: messageToSend }, function (dataBack) {
                // callback from the server:
                console.log(dataBack);
            });
        });
        setInterval(function () {
            $.get("serverAPI.php", { lastSeenMessage: lastMessageSeenId }, function (databaBack) {
                messagesDiv.append(databaBack);
                let lastMessagediv = $(".myMessages div:last-child span");
                lastMessageSeenId = Number(lastMessagediv.html());
                console.log(lastMessageSeenId);
            });
            // we need to CHANGE the lastMessageSeenId
        }, 2000);
    });
}
