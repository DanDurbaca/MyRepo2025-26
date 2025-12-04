$(start);

let Options = ["Carrots", "Potatoes", "Rice", "Milk"];

let listOfIngredientsCurrentRecipe = 0;

function start() {
    let inputBoxRecipeName = $("<input>");
    inputBoxRecipeName.attr("placeholder", "Recipe name");
    $("body").append(inputBoxRecipeName);

    let btnCreateRecipe = $("<button>");
    btnCreateRecipe.html("Create recipe");
    $("body").append(btnCreateRecipe);
    $("body").append("<br>");

    let myIngredientsSelect = $("<select>");
    for (let i = 0; i < Options.length; i++) {
        let newIngredientOption = $("<option>");
        newIngredientOption.attr("value", i);
        newIngredientOption.html(Options[i]);
        myIngredientsSelect.append(newIngredientOption);
    }
    $("body").append(myIngredientsSelect);

    let quantity = $("<input>");
    quantity.attr("placeholder", "Quantity");
    $("body").append(quantity);

    let btnAddToRecipe = $("<button>");
    btnAddToRecipe.html("Add to recipe");
    $("body").append(btnAddToRecipe);

    btnCreateRecipe.on("click", function () {
        if (inputBoxRecipeName.val() != "") {
            let newRecipe = $("<div>");
            let recipeTitle = $("<h1>");
            recipeTitle.html(inputBoxRecipeName.val());
            newRecipe.append(recipeTitle);
            let recipeIngredients = $("<ul>");
            newRecipe.append(recipeIngredients);
            $("body").append(newRecipe);
            listOfIngredientsCurrentRecipe = recipeIngredients;
        }
    });

    btnAddToRecipe.on("click", function () {
        if (listOfIngredientsCurrentRecipe != 0) {
            let newLi = $("<li>");
            newLi.html(Options[myIngredientsSelect.val()] + " - " + quantity.val());
            listOfIngredientsCurrentRecipe.append(newLi);
            quantity.val("");
        }
    });
}
