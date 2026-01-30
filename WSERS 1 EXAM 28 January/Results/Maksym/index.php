<?php
  session_start();

  $db = __DIR__ . "/db/test.csv";
  $grid = [];

  if (file_exists($db) && ($handle = fopen($db, "r")) !== FALSE) {
    while (($line = fgetcsv($handle)) !== FALSE) {
      $grid[] = $line;
    }

    fclose($handle);
  } else {
    echo "Could not open the file";
  }

  $uniqueValues = 0;

  $dbLine = isset($_POST["numLines"]) ? $_POST["numLines"] : 0;

  if ($dbLine === 0) {
    for ($i = 0; $i <= count($grid[0]); $i++) {
      foreach ($grid[0] as $char) {
        if ($grid[0][i] !== $char) {
          $uniqueValues++;
        }
      }
    }
  }

  echo $uniqueValues;
?>

<!doctype html>

<html lang="en" xmlns:mso="urn:schemas-microsoft-com:office:office" xmlns:msdt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <title>Exam</title>

  <style>
    .blue {
      background-color: lightblue;
    }

    .red {
      background-color: lightpink;
    }
  </style>
</head>

<body>
  <h2>Task 1:</h2>
    <form method="POST">
      <select name="numLines">
        <option value="0" <?php echo $dbLine === 0 ? "selected" : "" ?>>Show line 0</option>
        <option value="1" <?php echo $dbLine === 1 ? "selected" : "" ?>>Show line 1</option>
        <option value="2" <?php echo $dbLine === 2 ? "selected" : "" ?>>Show line 2</option>
        <option value="3" <?php echo $dbLine === 3 ? "selected" : "" ?>>Show line 3</option>
      </select>

      <input type="submit" value="Display" />
    </form>

        <br />The number of unique values on line 2 is 5<br />The least frequent value on this line is x<br />The most frequent value on this line is
        w <br /><br />
        <table>
            <tr>
                <td class="blue">x...1</td>

                <td>y...2</td>

                <td>z...2</td>

                <td class="blue">t...1</td>

                <td class="red">w...5</td>
            </tr>
        </table>

        <h2>Task 2:</h2>
        <form>
            <input type="submit" name="showAll" value="Show statistics for all lines" />
        </form>
    </body>
</html>
