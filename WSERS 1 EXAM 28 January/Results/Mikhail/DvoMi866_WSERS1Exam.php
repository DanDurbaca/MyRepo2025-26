<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
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
    <h1>Task 1:</h1>
    <form>
        <select name="numLines">
            <?php
            if(!isset($_GET["numLines"])){
                $_GET["numLines"]=0;
            }
            $source=fopen("test.csv", "r");
            $value=0;
            while (!feof($source)) {
		        $oneLine = fgets($source);
                ?><option value="<?= $value ?>" <?= ($_GET["numLines"]==$value) ? "selected":"" ?>>Show line <?= $value ?></option><?php
                $value++;
            }
            ?>
        </select>
        <input type="submit" value="Display" />
    </form>
    <?php 
    if (isset($_GET["numLines"])){
        $source=fopen("test.csv", "r");
        $arr=[];
        $smallest="";
        $biggest="";
        for($i=0;$i<$_GET["numLines"];$i++){
            fgets($source);
        }
        $oneLine = fgets($source);
		$individualValues = explode(",", $oneLine);
        for($i=0;$i<count($individualValues);$i++){
            if(!isset($arr[trim($individualValues[$i])])){
                $arr[trim($individualValues[$i])]=1;
                if($smallest==""){
                    $smallest=trim($individualValues[$i]);
                }
                if($biggest==""){
                    $biggest=trim($individualValues[$i]);
                }
            }
            else{
                $arr[trim($individualValues[$i])]++;
            }
        }
        for($i=0;$i<count($individualValues);$i++){
            for($j=0;$j<count($individualValues);$j++){
                if($arr[trim($individualValues[$i])]<$arr[trim($individualValues[$j])]&&$arr[trim($individualValues[$i])]<$arr[trim($smallest)]){
                    $smallest=trim($individualValues[$i]);
                }
            }
        }
        for($i=0;$i<count($individualValues);$i++){
            for($j=0;$j<count($individualValues);$j++){
                if($arr[trim($individualValues[$i])]>$arr[trim($individualValues[$j])]&&$arr[trim($individualValues[$i])]>$arr[trim($biggest)]){
                    $biggest=trim($individualValues[$i]);
                }
            }
        }
        echo("The number of unique values on line ".$_GET["numLines"]." is ".count($arr));
        ?></br><?php
        echo("The least frequent value on this line is ".$smallest);
        ?></br><?php
        echo("The most frequent value on this line is ".$biggest);

    }
    ?>
    <table>
        <tr>
            <?php 
            foreach($arr as $key=>$value){
                ?><td <?= ($value==$arr[$smallest])?"class='blue'":"" ?><?= ($value==$arr[$biggest])?"class='red'":"" ?>><?= $key ?>...<?= $value ?></td><?php
            }
            ?>
        </tr>
    </table>
    <h2>Task 2:</h2>
    <form>
        <input type="submit" name="showAll" value="Show statistics for all lines" />
    </form>
    <?php 
        if(isset($_GET["showAll"])){
            $source=fopen("test.csv", "r");
            $counter=0;
            while (!feof($source)){
                $arr=[];
                $smallest="";
                $biggest="";
                $oneLine = fgets($source);
                $individualValues = explode(",", $oneLine);
                for($i=0;$i<count($individualValues);$i++){
                    if(!isset($arr[trim($individualValues[$i])])){
                        $arr[trim($individualValues[$i])]=1;
                        if($smallest==""){
                            $smallest=trim($individualValues[$i]);
                        }
                        if($biggest==""){
                            $biggest=trim($individualValues[$i]);
                        }
                    }
                    else{
                        $arr[trim($individualValues[$i])]++;
                    }
                }
                for($i=0;$i<count($individualValues);$i++){
                    for($j=0;$j<count($individualValues);$j++){
                        if($arr[trim($individualValues[$i])]<$arr[trim($individualValues[$j])]&&$arr[trim($individualValues[$i])]<$arr[trim($smallest)]){
                            $smallest=trim($individualValues[$i]);
                        }
                    }
                }
                for($i=0;$i<count($individualValues);$i++){
                    for($j=0;$j<count($individualValues);$j++){
                        if($arr[trim($individualValues[$i])]>$arr[trim($individualValues[$j])]&&$arr[trim($individualValues[$i])]>$arr[trim($biggest)]){
                            $biggest=trim($individualValues[$i]);
                        }
                    }
                }
                echo("The number of unique values on line ". $counter." is ".count($arr));
                ?></br><?php
                echo("The least frequent value on this line is ".$smallest);
                ?></br><?php
                echo("The most frequent value on this line is ".$biggest);
                ?></br></br>
                <table>
                    <tr>
                        <?php 
                        foreach($arr as $key=>$value){
                            ?><td <?= ($value==$arr[$smallest])?"class='blue'":"" ?><?= ($value==$arr[$biggest])?"class='red'":"" ?>><?= $key ?>...<?= $value ?></td><?php
                        }
                        ?>
                    </tr>
                </table>
                <?php $counter++;
            }
        }
    ?>
</body>
</html>