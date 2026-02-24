<!doctype html>
<html lang="en" xmlns:mso="urn:schemas-microsoft-com:office:office"
    xmlns:msdt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title></title>
    <style>
        .blue {
            background-color: lightblue;
        }

        .red {
            background-color: lightpink;
        }
    </style>

    <!--[if gte mso 9]><xml>
<mso:CustomDocumentProperties>
<mso:display_urn_x003a_schemas-microsoft-com_x003a_office_x003a_office_x0023_Editor msdt:dt="string">DURBACA Dan</mso:display_urn_x003a_schemas-microsoft-com_x003a_office_x003a_office_x0023_Editor>
<mso:Order msdt:dt="string">12591000.0000000</mso:Order>
<mso:ComplianceAssetId msdt:dt="string"></mso:ComplianceAssetId>
<mso:_ExtendedDescription msdt:dt="string"></mso:_ExtendedDescription>
<mso:display_urn_x003a_schemas-microsoft-com_x003a_office_x003a_office_x0023_Author msdt:dt="string">DURBACA Dan</mso:display_urn_x003a_schemas-microsoft-com_x003a_office_x003a_office_x0023_Author>
<mso:TriggerFlowInfo msdt:dt="string"></mso:TriggerFlowInfo>
<mso:ContentTypeId msdt:dt="string">0x01010011B4E716D0B6E84C8B474E637401A33D</mso:ContentTypeId>
<mso:_SourceUrl msdt:dt="string"></mso:_SourceUrl>
<mso:_SharedFileIndex msdt:dt="string"></mso:_SharedFileIndex>
</mso:CustomDocumentProperties>
</xml><![endif]-->
</head>

<body>
    <?php
    $file = fopen("test1.csv", "r");
    $arr = [];

    for ($j = 0; ($row = fgetcsv($file, 0, ',')) !== false; ++$j) {
        $arr[$j] = [];
        for ($i = 0; $i < sizeof($row); ++$i) {
            $arr[$j][$i] = trim($row[$i]);
        }
    }
    fclose($file);

    $option = $_GET["numLines"] ?? "0";
    $option2 = isset($_GET["showAll"]);
    $valFr = [];

    for ($i = 0; $i < sizeof($arr[$option]); ++$i) {
        $valFr[$arr[$option][$i]] = 0;
    }
    for ($i = 0; $i < sizeof($arr[$option]); ++$i) {
        ++$valFr[$arr[$option][$i]];
    }

    $countUnique = 0;
    $lf = "";
    $mf = "";
    $temp = 0;

    foreach ($valFr as $key => $val) {
        if ($val === 1) {
            $lf = $key;
            ++$countUnique;
        }
        if ($val > $temp) {
            $temp = $val;
            $mf = $key;
        }
    }
    ?>
    <h2>Task 1:</h2>
    <form>
        <select name="numLines">
            <option value="0" <?php if ($option === "0") {
                print "selected";
            } ?>>Show line 0</option>
            <option value="1" <?php if ($option === "1") {
                print "selected";
            } ?>>Show line 1</option>
            <option value="2" <?php if ($option === "2") {
                print "selected";
            } ?>>Show line 2</option>
            <option value="3" <?php if ($option === "3") {
                print "selected";
            } ?>>Show line 3</option>
        </select>
        <input type="submit" value="Display" />
    </form>
    <br />
    The number of unique values on line <?php print $option ?> is <?php print $countUnique ?><br />
    The least frequent value on this line is <?php print $lf ?><br />
    The most frequent value on this line is <?php print $mf ?> <br /><br />
    <table>
        <tr>
            <?php foreach ($valFr as $key => $val) {
                $class = "";
                if ($val === 1)
                    $class = "class = 'blue'";
                else if ($val === $temp)
                    $class = "class = 'red'";

                print "<td " . $class . ">" . $key . "..." . $val . "</td>";
            } ?>
        </tr>
    </table>

    <h2>Task 2:</h2>
    <form>
        <input type="submit" name="showAll" value="Show statistics for all lines" />
    </form>
</body>

</html>