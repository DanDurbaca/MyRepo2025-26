$(start);

function start() {
    $("#GetData").on("click", FetchDataFromServer);
}

function FetchDataFromServer() {
    $.getJSON("serverAPI.php", { dummyParam: "dummyValue" }, function (dataJSON) {
        console.log(dataJSON);

        let currentItemIdx = 0;

        let newDiv = $("<div>");
        $("body").append(newDiv);
        let IngredientDiv = $("<div>");
        newDiv.append(IngredientDiv);
        let backBtn = $("<button>Previous ingredient</button>");
        let nextBtn = $("<button>Next ingredient</button>");
        newDiv.append(backBtn);
        newDiv.append(nextBtn);
        backBtn.on("click", function () {
            currentItemIdx -= 1;
            if (currentItemIdx < 0) {
                currentItemIdx = dataJSON.length - 1;
            }
            printIngredient(dataJSON[currentItemIdx], IngredientDiv);
        });
        nextBtn.on("click", function () {
            currentItemIdx += 1;
            if (currentItemIdx > dataJSON.length) {
                currentItemIdx = 0;
            }
            printIngredient(dataJSON[currentItemIdx], IngredientDiv);
        });
        printIngredient(dataJSON[currentItemIdx], IngredientDiv);
    });
}

function printIngredient(whatToPrint, whereToPrint) {
    let stringToPrint = whatToPrint.ingredientId + " " + whatToPrint.ingredientName + " " + whatToPrint.ingredientCalories;
    whereToPrint.html(stringToPrint);
}
