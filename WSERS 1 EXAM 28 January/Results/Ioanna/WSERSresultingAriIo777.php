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
        <h2>Task 1:</h2>
        <form>

        

            <?php

            //im not sure how to do it the way you explained so im trying to explain my thought proccess with notes PS sorry its a mess :(

        $file =  fopen ("test.csv", "r");
            
            while ($feof != $file) { 
                $line = fgets($file);
                $letters = explode(",", $line);
                for ($i=0, $i<count($letters), $i++) {

                   //$i=number of lines
                    $display = false;


                    if ($_GET["numLines"] == [$i] && $_GET["showAll"=["Show+statistics+for+all+lines"]]) { //so it shows only if youre trying to display that line or all of them
                    $display = true;

                        for ($b=0, $b<count($letters), $b++) {
                            if ([$letters[$b]]=[$letters[$i]]) { //trying to find how many match ? (this wouldnt work but i dont know how else to do it)
                                $item[$b]++;
                            }
                        $item[$b]--; //removing one because it has to match itself at least once 

                        }
                    }
                }
                

            }

            


            
        
            ?>


            <select name="numLines">
                <option value="0">Show line 0</option>
                <option value="1">Show line 1</option>
                <option value="2" selected>Show line 2</option>
                <option value="3">Show line 3</option>
            </select>
            <input type="submit" value="Display" />
        </form>
        
        <table>
            <tr>
                
            </tr>
        </table>

        <h2>Task 2:</h2>
        <form>
            <input type="submit" name="showAll" value="Show statistics for all lines" />
        </form>
    </body>
</html>
