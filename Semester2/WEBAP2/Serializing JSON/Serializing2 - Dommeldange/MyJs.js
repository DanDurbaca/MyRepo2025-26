let myData = null;
let currentIndexItem = 0;
$(start);

function start() {
    $("#getData").on("click", getData);
}

function getData() {
    $.getJSON("ServerAPI.php", { getDatabaseContent: 0 }, function (dataBack) {
        myData = dataBack;
        console.log(myData);
        let newDiv = $("<div>");
        let btn_Next = $("<button>next</button>");
        let btn_Prev = $("<button>prev</button>");

        $("body").html("");
        $("body").append(newDiv);
        $("body").append(btn_Next);
        $("body").append(btn_Prev);
        btn_Next.on("click", function () {
            currentIndexItem += 1;
            if (currentIndexItem >= myData.length) {
                currentIndexItem = 0;
            }
            refreshItem(newDiv, myData[currentIndexItem]);
        });

        btn_Prev.on("click", function () {
            currentIndexItem -= 1;
            if (currentIndexItem < 0) {
                currentIndexItem = myData.length - 1;
            }
            refreshItem(newDiv, myData[currentIndexItem]);
        });
        refreshItem(newDiv, myData[currentIndexItem]);
    });
}

function refreshItem(divToRefresh, refreshData) {
    divToRefresh.html("");
    let IdDiv = $("<div>");
    IdDiv.html("ID=" + refreshData.ingredientId);
    let NameDiv = $("<div>");
    NameDiv.html("Name=" + refreshData.ingredientName);
    let CaloriesDiv = $("<div>");
    CaloriesDiv.html("Calories=" + refreshData.ingredientCalories);

    divToRefresh.append(IdDiv);
    divToRefresh.append(NameDiv);
    divToRefresh.append(CaloriesDiv);
}
