$(start)

function start () {

    let div1 = $('<div class="out"></div>');
    div1.append('<div class="box red"></div>');
    div1.append('<input type="text" disabled value="0">');
    $('body').append(div1);

    let div2 = $('<div class="out"></div>');
    div2.append('<div class="box blue"></div>');
    div2.append('<input type="text" disabled value="0">');
    $('body').append(div2);

    let div3 = $('<div class="out"></div>');
    div3.append('<div class="box yellow"></div>');
    div3.append('<input type="text" disabled value="0">');
    $('body').append(div3);
    
    let div4 = $('<div class="out"></div>');
    div4.append('<div class="box none"></div>');
    div4.append('<input type="text" disabled value="0">');
    $('body').append(div4);
    
    let grid = $('<div>');
    grid.css('display', 'grid');
    grid.css('grid-template-columns', 'repeat(10, 1fr)');  //ensure that has margins
    grid.css('gap', '0'); 

    
    for (let i = 0; i < 50; i++) {
        let box = $('<div class="box none"></div>');
        grid.append(box);
    }
    
    $('body').append(grid);
    
    let counts = {
        'none': 50,
        'red': 0,
        'blue': 0,
        'yellow': 0
    };
    
    function updateCounts() {
        $('.out').each(function(index) {
            let input = $(this).find('input');
            switch(index) {
                case 0: 
                    input.val(counts['red']);
                    break;
                case 1: 
                    input.val(counts['blue']);
                    break;
                case 2: 
                    input.val(counts['yellow']);
                    break;
                case 3: 
                    input.val(counts['none']);
                    break;
            }
        });
    }

    updateCounts();

    $('.box').click(function() {
        let currentClass = $(this).attr('class');
        let currentColor = currentClass.split(' ')[1]; 
        
        if (currentColor === 'none') {
            counts['none']--;
        } else if (currentColor === 'red') {
            counts['red']--;
        } else if (currentColor === 'blue') {
            counts['blue']--;
        } else if (currentColor === 'yellow') {
            counts['yellow']--;
        }
        
        let nextColor;
        switch(currentColor) {
            case 'none':
                nextColor = 'red';
                break;
            case 'red':
                nextColor = 'blue';
                break;
            case 'blue':
                nextColor = 'yellow';
                break;
            case 'yellow':
                nextColor = 'none';
                break;
        }

        counts[nextColor]++;

        $(this).removeClass(currentColor);
        $(this).addClass(nextColor);

        updateCounts();
    });
};
