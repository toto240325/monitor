
<!-- to test this file : 
http://192.168.0.147/monitor/upload_file.php
-->

<html>
    <head>
        <title>upload file</title>
    </head>
    <body>
        <?php
            /*
                Todo : 
                - check database size and cleanup oldest records to keep total size under max allowed
            */
            
            echo "test 6\n";
            //move_uploaded_file($_FILES["file"]["tmp_name"],$_FILES["file"]["name"]);
            
            
            //echo "var_dump(_POST); ------------------\n";
            //var_dump($_POST);

            function getParam($paramName, $defaultValueStr) {
                $paramValue = "";
            
                if (isset($_GET[$paramName])) { $paramValue = $paramValue . $_GET[$paramName]; }
                if (isset($_POST[$paramName])) { $paramValue = $paramValue . $_POST[$paramName]; }
            
                if ($paramValue == "") {
                    $paramValue = $defaultValueStr;
                }
                return $paramValue;    
            }
            
            //==========================================================================================================================
            
            //$dbhost = getParam("dbhost", "192.168.0.147");
            $dbhost = getParam("dbhost", "localhost");
            

            if(isset($_POST['data'])) 	
            { 
                //	echo "data : ------------------\n";
                //	echo ($_POST['data']."\n");
                
                $myData = $_POST['data'];
                //	echo $myData."\n";
                //	echo "data.type : ------------------\n";
                //	echo ($myData->type."\n");
                
                $manage = (array) json_decode($myData);
                echo "manage : \n";
		var_dump($manage);
                echo "---- end of manage : \n";

                $upload_time = $manage['upload_time'];
                $PIR_detection = $manage['PIR_detection'];
                $ultrasonic_detection = $manage['ultrasonic_detection'];
                $fileType = $manage['fileType'];
                $fileName = $manage['fileName'];
                $fileSize = $manage['fileSize'];
                $fileContent = $_POST['file'];
		echo "---------------\n";
		echo "fileName : " . $fileName . "\n";
		echo "fileSize : " . $fileSize . "\n";
		echo "fileType : " . $fileType . "\n";
            } 
            
            /**/
                //echo "all posted POST : ------------------------\n";
                //foreach ($_POST as $key => $value)
                //echo htmlspecialchars($key)." : ".htmlspecialchars($value)."<br>\n";

                
                echo "test3<p>";


            /**/
            
            
            //if(($_FILES['file']['size'] > 0) && ($_FILES['file']['size'] < 1000000000 )) // max size in bytes 1G
            if(($fileSize > 0) && ($fileSize < 1000000000 )) // max size in bytes 1G
            {
                //$fileName = $_FILES['file']['name'];
                $tmpName  = $_FILES['file']['tmp_name'];
                //$fileSize = $_FILES['file']['size'];
                //$upload_time = ;
                //$fileType = $_FILES['file']['type'];
                
                //if(isset($_GET['type'])) { $fileType = $_GET['type']; } 
                
                $fp      = fopen($tmpName, 'r');
                $content = fread($fp, filesize($tmpName));
                $content = addslashes($content);
                fclose($fp);
                
                if(!get_magic_quotes_gpc())
                {
                    $fileName = addslashes($fileName);
                }
                
                include 'connect-db.php';
                $conn = mysqli_connect($dbhost, $dbuser, $dbpass,$mydb);
                
                if(! $conn )
                {
                    echo "test2 :<p>";
                    echo "dbhost : $dbhost<p>";
                    echo "dbuser : $dbuser<p>";
                    die('Could not connect: ' . mysqli_error($conn));
                }
                else
                {
                    echo "connection OK\n";	
                }
                
		echo "!!!!!!!!!!!!!!! before insert !!!!!!!!\n";
                $content = $fileContent;
                $content = addslashes($content);
                $query = "INSERT INTO upload (name, size, type, content, upload_time, PIR_detection, ultrasonic_detection ) ".
                "VALUES ('$fileName', '$fileSize', '$fileType', '$content', '$upload_time', '$PIR_detection', '$ultrasonic_detection')";
                
                //print_r ($query);
                
                if (!mysqli_query($conn, $query))
                {
                    echo("Errorcode: " . mysqli_errno($conn) . "\n");
                    echo("Errorcode: " . mysqli_error($conn) . "\n");
                    die('Error, query failed'); 
                }
               
                //	include 'library/closedb.php';
                
                echo "<br>File $fileName uploaded<br>";
                mysqli_close($conn);
                
            }
            else 
            {
                die ('filesize is 0 !');
            }
            


        ?>
    </body>
</
>

