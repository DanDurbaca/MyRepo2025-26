<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .box {
            border: 1px solid red;
        }

        .green {
            background-color: lightgreen;
        }

        .blue {
            background-color: lightblue;
        }
    </style>
    <title>Document</title>
</head>

<body>
    <h1>Hello, World!</h1>
    <p>This is my first PHP file.</p>

    <?php
    for ($i = 1; $i <= 1111; $i++) {
        if ($i % 2 == 0) {
            $classColor = "green";
        } else {
            $classColor = "blue";
        }
    ?>
        <div class="box <?= $classColor ?>"> <?= $i ?> Hello World</div>
    <?php
    }
    ?>
</body>

</html>