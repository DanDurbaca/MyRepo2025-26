$(start);
function start() {
let Options = ["Red", "Blue", "Yellow"];
mkBtn = $("<button>");
mkBtn.attr("id", "start")
mkBtn.html("Create")

rmBtn = $("<button>");
rmBtn.attr("id", "clear")
rmBtn.html("Clear")

color = $("<select>")
color.attr("id", "color")

inputQuantity = $("<input>");
inputQuantity.attr("type", "number")
inputQuantity.attr("id", "quantity")

for (i = 0; i < Options.length; i++) {
    opt = $("<option>")
    opt.html(Options[i])
    opt.attr("value", Options[i])
    color.append(opt)
}

$("body").append(inputQuantity,color,mkBtn,rmBtn)
$("#start").on("click", add)
$("#clear").on("click", remove)

function add() {
    let color = $("#color").val()
    let number = parseFloat($("#quantity").val())
    for (let i = 0; i < number; i++) {
        newSquare = $("<div>");
        newSquare.attr("class", "box")
        if (color == "Red") {
            newSquare.attr("id", "red")
            newSquare.css("background-color", "red")
        }
        else if (color == "Yellow") {
            newSquare.attr("id", "yellow")
            newSquare.css("background-color", "yellow")
            
        }

        else if (color == "Blue") {
            newSquare.attr("id", "blue")
            newSquare.css("background-color", "blue")
            newSquare.attr("onclick", "removeBox")
        }
        $("body").append(newSquare)
    }
}

function remove() {
    $("div").remove("")
}
}


