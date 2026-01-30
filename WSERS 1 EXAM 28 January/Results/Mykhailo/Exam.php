<!doctype html>
<html lang="en" xmlns:mso="urn:schemas-microsoft-com:office:office" xmlns:msdt="uuid:C2F41010-65B3-11d1-A29F-00AA00C14882">
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
    

</head>

    <body>
        <?php  
        $f=fopen("test.csv", "r");
        $l1=0;
        $l2=0;
        $l3=0;
        $ln=0;
         while (!feof($f)) {
        $l = fgets($f);
        $elems = explode(",", $l);
        for ($i = 0; $i < count($elems); $i++) {
            if ($elems[$i] == "") $l1++;
            else if ($elems[$i] == "") $l2++;
            else $ln++;
        }
    }
    fclose($f);
     $f = fopen("test.csv", "r");
     $x=0;
     $y=0;
     $z=0;
     $w=0;
     $t=0;
        ?>
        <h2>Task 1:</h2>
        <?php 
        $arv=[];
      while (!feof($f)) {
        $l = fgets($f);
        $elems = explode(",", $l);
         foreach ($arv as $a){
            $Dispaly=true;
          if (isset($_GET["Display"])) {
                if ($_GET["Display"] == "x" && $elems[$a] == "x"){
                    $arv["x"]++
                    
                }
                if ($_GET["Display"] == "y" && $elems[$a] == "y"){
                     $arv["y"]++
                    }

                     if ($_GET["Dispaly"] == "z" && $elems[$a] == "z"){
                         $arv["z"]++
                        }
                    
                if ($_GET["Dispalay"] == "t" && $elems[$a] == "t"){
                     $arv["t"]++
                    }
                   
                if ($_GET["Diasplay"] == "w" && ($elems[$a] != "z") && ($elems[$a] != "y") && ($elems[$a] != "x")&& ($elems[$a] != "t")){
                   $arv["w"]++ 
              }
            }
            if (isset($_GET["Unique"], $_GET["One"])) {

              
            }
            
    }
}
        ?>

        
        
        <form>
            <select name="numLines">
                <option value="0">Show line 0</option>
                <option value="1">Show line 1</option>
                <option value="2"> selected>Show line 2</option>
                <option value="3">Show line 3</option>
            </select>
            <input type="submit" value="Display" />
        </form>
        <br />The number of unique values on line 2 is 5<br />The least frequent value on this line is x<br />The most frequent value on this line is
        w <br /><br />
        <table>
            <tr>
                <td class="blue"><?= $x ?></td>

                <td><?= $y ?></td>

                <td><?= $z ?></td>

                <td class="green"><?= $t ?></td>

                <td class="red"><?= $w ?></td>
            </tr>
        </table>
        
        <h2>Task 2:</h2>
        
        <form>
            <input type="submit" name="showAll" value="Show statistics for all lines" />
        </form>
    </body>
</html>
