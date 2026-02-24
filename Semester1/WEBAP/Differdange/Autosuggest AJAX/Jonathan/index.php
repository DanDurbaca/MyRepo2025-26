<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="jquery-3.7.1.min.js"></script>
    <title>Word Suggestion</title>
    <style>
        #suggestions div {
            border: 1px solid black;
            padding: 3px;
            cursor: pointer;
            width: fit-content;
        }
        #suggestions div:hover {
            background-color: #eee;
        }
    </style>
</head>
<body>
    <input type="text" id="wordInput" />
    <div id="suggestions"></div>

    <script>
        $(document).ready(function() {
            $("#wordInput").on("keyup", function() {
                var query = $(this).val();
                if(query.length == 0) {
                    $("#suggestions").html("");
                    return;
                }
                $.get("search.php", { q: query }, function(data) {
                    $("#suggestions").html(data);
                    $("#suggestions div").click(function() {
                        $("#wordInput").val($(this).text());
                        $("#suggestions").html("");
                    });
                });
            });
        });
    </script>
</body>
</html>
