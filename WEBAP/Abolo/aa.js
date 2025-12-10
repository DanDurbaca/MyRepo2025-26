$(start);

function start() {
    let p = $.get("https://www.bioplanet.be/fr/produits/4812");
    $("body").append(p);
}
