$(start);

let Options = ["Red", "Blue", "Yellow"];

let amount = 0;

function start() {
    let inputName = $("<input type='number'>");
    inputName.attr("placeholder", "Quantity");
    $("body").html(inputName);

    let mycolorsSelect = $("<select>");
    for (let i = 0; i < Options.length; i++) {
        let mycolorsOption = $("<option>");
        mycolorsOption.attr("value", i);
        mycolorsOption.html(Options[i]);
        mycolorsSelect.append(mycolorsOption);
    }
    $("body").append(mycolorsSelect);

    let create = $("<button>");
    create.html("Create");
    $("body").append(create);

    let clear = $("<button>");
    clear.html("Clear");
    $("body").append(clear);

    clear.on("click", function () {
        $(start);
    });

    create.on("click", function () {
        for (let a = 0; a < inputName.val(); a++) {
            if (mycolorsSelect.val() == 0) {
                let newbox = $("<p class='red'>");
                newbox.html("<br>");
                $("body").append(newbox);

                newbox.click(function () {
                    $("body").html($(start));
                });
            } else if (mycolorsSelect.val() == 2) {
                let newbox = $("<p class='yellow'>");
                newbox.html("<br>");
                for (let a = 0; a < inputName.val(); a++) {
                    $("body").append(newbox);

                    newbox.click(function () {
                        $("body").html($(start));
                    });
                }
            } else if (mycolorsSelect.val() == 1) {
                let newbox = $("<p class='blue'>");

                newbox.html("<br>");
                for (let a = 0; a < inputName.val(); a++) {
                    $("body").append(newbox);
                    newbox.click(function () {
                        $("body").html($(start));
                    });
                }
            }
        }
    });
}
