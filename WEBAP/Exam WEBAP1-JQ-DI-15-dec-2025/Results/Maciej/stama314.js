$(start);

function start() {
    let mainRED = $("<div>");
    mainRED.addClass('out');
    $("body").append(mainRED);
    let mainBLUE = $("<div>");
    mainBLUE.addClass('out');
    $("body").append(mainBLUE);
    let mainYELLOW = $("<div>");
    mainYELLOW.addClass('out');
    $("body").append(mainYELLOW);
    let mainNONE = $("<div>");
    mainNONE.addClass('out');
    $("body").append(mainNONE);

    let REDbox = $("<div>");
    REDbox.addClass('box red')
    mainRED.append(REDbox);

    let REDcounter = $("<input>");
    REDcounter.addClass('input');
    REDcounter.attr("disabled", "disabled");
    REDcounter.val("0");
    mainRED.append(REDcounter);


    let BLUEbox = $("<div>");
    BLUEbox.addClass('box blue')
    mainBLUE.append(BLUEbox);

    let BLUEcounter = $("<input>");
    BLUEcounter.addClass('input');
    BLUEcounter.attr("disabled", "disabled");
    BLUEcounter.val("0");
    mainBLUE.append(BLUEcounter);


    let YELLOWbox = $("<div>");
    YELLOWbox.addClass('box yellow')
    mainYELLOW.append(YELLOWbox);

    let YELLOWcounter = $("<input>");
    YELLOWcounter.addClass('input');
    YELLOWcounter.attr("disabled", "disabled");
    YELLOWcounter.val("0");
    mainYELLOW.append(YELLOWcounter);


    let NONEbox = $("<div>");
    NONEbox.addClass('box none')
    mainNONE.append(NONEbox);

    let NONEcounter = $("<input>");
    NONEcounter.addClass('input');
    NONEcounter.attr("disabled", "disabled");
    NONEcounter.val("50");
    mainNONE.append(NONEcounter);


    for (let r = 1; r < 6; r++) {
        $("body").append("<br>");
        for (let c = 1; c < 11; c++) {
            let box = $("<div>");
            box.addClass('box none')
            $("body").append(box);
        }
    }

    $("body").on("click", ".box", function (){
        let $thisBOX = $(this);
        if ($thisBOX.hasClass("none")) {
            $thisBOX.removeClass("none");
            $thisBOX.addClass("red");
            let CHANGEcounter = $(".red").length;
            REDcounter.val(CHANGEcounter);
        }
        if ($thisBOX.hasClass("red")) {
            $thisBOX.removeClass("red");
            $thisBOX.addClass("blue");
            let CHANGEcounter = $(".blue").length;
            BLUEcounter.val(CHANGEcounter);
        }
        if ($thisBOX.hasClass("blue")) {
            $thisBOX.removeClass("blue");
            $thisBOX.addClass("yellow");
            let CHANGEcounter = $(".yellow").length;
            YELLOWcounter.val(CHANGEcounter);
        }
        if ($thisBOX.hasClass("yellow")) {
            $thisBOX.removeClass("yellow");
            $thisBOX.addClass("none");
            let CHANGEcounter = $(".box").length;
            NONEcounter.val(CHANGEcounter);
        }
    })

}