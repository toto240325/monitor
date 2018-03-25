<html>
    
    
    <head>
        <title>Download File From MySQL 5</title>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
        
        
    </head>
    
    <script src="http://ajax.googleapis.com/ajax/libs/angularjs/1.4.8/angular.min.js"></script>
    
    <body>
        
        <?php

        echo "<style>
        table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        } 
        td {
        text-align: center;
        }
			</style>";
        
        echo "<h1>Pictures of Loki eating</h1>";
        echo "<a href='index.html'>Back to menu</a><p>";
        
        echo "dirname : " . dirname("c:/users/derruer/") . PHP_EOL;

        //echo "display_startup_errors : ";
        //echo var_dump(ini_get('display_startup_errors'));
        
        //echo "<br>log_errors : ";
        //echo var_dump(ini_get('log_errors'));
        
        $hosting = 'p702';

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
        
        require 'connect-db.php';
        
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass,$mydb);
        
        $query = "SELECT id, name, content, type, upload_time, size, PIR_detection, ultrasonic_detection FROM upload order by upload_time desc,type limit 30";
        $result = mysqli_query( $conn, $query ) or die('Error, query failed');
        
        if(mysqli_num_rows($result) == 0)
        {
            echo "Database is empty <br>";
        } 
        else
        {
            echo "nb results : ".mysqli_num_rows($result)."<p>";
            
            $prev_upload_time = 0;
            
            echo "<table border=0 style='width:100%'>";
            echo "<tr>
            ";
            $i=0;
            while ($row = $result->fetch_assoc())
            {
                $i+=1;
                $id = $row['id'];
                $name = $row['name'];
                $content = $row['content'];
                $upload_time = $row['upload_time'];
                $type = $row['type'];
                $size = $row['size'];
                $PIR_detection = $row['PIR_detection'];
                $ultrasonic_detection = $row['ultrasonic_detection'];
                

                /*   
                $myfile = fopen("C:\Users\derruer\mydata\projects\htdocs\monitor\pi-client\myvid$i.mp4", "w");
                fwrite ($myfile,$content);
                fclose($myfile);
                */


                if (($prev_upload_time !=0) and ($prev_upload_time != $upload_time)) {
                    echo "</tr>
                    <tr>";
                }
                $prev_upload_time = $upload_time;
                
                echo "<td>";
                
                echo "id = ".$id." and name=".$name." and size=".$size." and PIR_detection=".$PIR_detection."<p>";
                //echo "type = ".$type
                echo "upload_time = ".$upload_time."<p>";
                
                if ($name == "video.mp4") 
                {
                    
                    /*
                        ?>						
                        
                        <video width="320" height="240" controls>
                        <source src="render_video.php?id=113" type="video/mp4">
                        Your browser does not support the video tag.
                        </video>
                        <?php
                    */
                    
                    echo '<video width="320" height="240" controls>
                    <source src="render-mp4.php?id='.$id.'" type="video/mp4">
                    Your browser does not support the video tag.
                    </video>
                    ';
                    
                    //					<source src="video.mp4" type="video/mp4">
                    //					<source src='.$content.' type="video/mp4">
                    //					<source src="/tmp/myvid240325.mp4" type="video/mp4">
                    
                    /*
                        $myfile = fopen("/tmp/myvid240325.mp4", "wb")or die("Unable to open file!");
                        fwrite($myfile, stripslashes($content));
                        fclose($myfile);
                        
                        echo '<video width="320" height="240" controls>
                        <source src='.stripslashes($content).' type="video/mp4">
                        <source src="/tmp/myvid240325.mp4" type="video/mp4">
                        Error: Video Not working
                        </object>
                        </video> ';
                    */
                    
                }
                elseif ($name != "video.h264")
                {
                    echo '<img src="data:image/jpeg;base64,'.base64_encode( $content).'"/>
                    ';
                }
                
                
                echo"<br>";
                
                if ($PIR_detection)
                {
                    echo '<img src="green.gif"/>';
                }
                else
                {
                    echo '<img src="red.gif"/>';
                }
                echo '   ';
                
                if ($ultrasonic_detection)
                {
                    echo '<img src="green.gif"/>';
                }
                else
                {
                    echo '<img src="red.gif"/>';
                }
                echo "</td>
                ";
                /*				
                    ?>
                    
                    <a href="download.php?id=
                    <?php echo $id;?>
                    ">
                    <?php echo $name;?>
                    </a> <br>
                    
                    <?php 
                    
                */
                   
            }
            echo "</tr>
            </table>";   
        }
        mysqli_close($conn);
        
        //		?>
    </body>
</html>

