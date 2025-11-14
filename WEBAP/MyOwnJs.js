$(start);

function start() {
    $("#clickMe").on("click", doThis);
    $("#CreateList").on("click", createSelectList);
}

function createSelectList() {
    let carManufacturers = ["Volvo", "BMW", "Audi", "Dacia"];

    // appennd a select list made of these elements:
    /*
    
        <select name="Cars" id="CarIds">
            <option value="0">Volvo</option>
            <option value="1">BMW</option>
            <option value="2">Audi</option>
            <option value="3">Dacia</option>
        </select>
        */
    // in the end of the html body:

    let myCarsSelect = $("<select>");
    myCarsSelect.attr("name", "Cars");
    myCarsSelect.attr("id", "CarIds");
    for (let i = 0; i < carManufacturers.length; i++) {
        let newOption = $("<option>");
        newOption.attr("value", i);
        newOption.html(carManufacturers[i]);
        myCarsSelect.append(newOption);
    }

    $("body").append(myCarsSelect);
}

function doThis() {
    /*
1. Usage number  1 for $()
$(functionName) -> call functionName when the document has loaded

2. Second:
$("CSS_selector") -> find the html element identified by the "CSS_selector" in the DOM tree


3. Third way of using $() in JQuery:

$("<div>") -> this will CREATE a new div from JQuery

*/

    newDiv = $("<div>This is my new div inner text</div>");
    newDiv.attr("class", "redText");

    // we need to add this newDiv to the DOM tree:
    $("body").prepend(newDiv);
}
