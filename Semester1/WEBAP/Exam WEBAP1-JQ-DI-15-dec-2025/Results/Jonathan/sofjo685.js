$(document).ready(function () {
    const colors = ['red', 'blue', 'yellow', 'none'];
    const colorCounts = { red: 0, blue: 0, yellow: 0, none: 0 };
    colors.forEach(color => {
        const $div = $('<div class="out">');
        $div.append(`<div class="box ${color}"></div>`);
        $div.append(`<input type="text" value="0" disabled>`);
        $('body').append($div);
    });

    const grid = $('<div>');
    for (let i = 0; i < 5; i++) {
        for (let j = 0; j < 10; j++) {
            grid.append('<div class="box none"></div>');
        }
        grid.append('<br>');
    }

    $('body').append(grid);
    function updateCounter(color, delta) {
        const input = $(`.out:has(.box.${color}) input`);
        const current = parseInt(input.val());
        const newValue = current + delta;
        input.val(newValue);
        colorCounts[color] = newValue;
    }
    updateCounter('red', 0);
    updateCounter('blue', 0);
    updateCounter('yellow', 0);
    updateCounter('none', 50);

    $('body').on('click', '.box', function () {
        const currentClass = $(this).attr('class');
        const color = currentClass.split(' ')[1];

        const colorOrder = ['none', 'red', 'blue', 'yellow'];
        const currentIndex = colorOrder.indexOf(color);
        const nextIndex = (currentIndex + 1) % colorOrder.length;
        const nextColor = colorOrder[nextIndex];

        $(this).removeClass(color).addClass(nextColor);
        if (color !== 'none') {
            updateCounter(color, -1);
        }
        if (nextColor !== 'none') {
            updateCounter(nextColor, 1);
        }
        if (color == 'none') {
            updateCounter(color, -1);
        }
        if (nextColor == 'none') {
            updateCounter(nextColor, 1);
        }
    });
});