<?php


if(isset($_GET['id'])) 
{
    // if id is set then get the file with the id from database
    
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
    
    $id    = $_GET['id'];
    
    $query = "SELECT name, type, size, content " .
    "FROM upload WHERE id = '$id'";
    
    $result = mysqli_query($conn, $query) or die('Error, query failed');
    
    list($name, $type, $size, $content) = mysqli_fetch_array($result);
    
    header("Content-length: $size");
    header("Content-type: $type");
    header("Content-Disposition: attachment; filename=$name");
    echo $content;
    
    mysqli_close($conn);
    
    exit;
}



$arquivo_caminho = 'a.mp4';

if (is_file($arquivo_caminho)){
    header("Content-type: video/mp4"); // change mimetype

    if (isset($_SERVER['HTTP_RANGE'])){ // do it for any device that supports byte-ranges not only iPhone
        rangeDownload($arquivo_caminho);
        } else {
        header("Content-length: " . filesize($arquivo_caminho));
        readfile($arquivo_caminho);
    }
}
else {
    echo ("failed !");
} // fim do if

function rangeDownload($file){
    $fp = @fopen($file, 'rb');

    $size   = filesize($file); // File size
    $length = $size;           // Content length
    $start  = 0;               // Start byte
    $end    = $size - 1;       // End byte
    // Now that we've gotten so far without errors we send the accept range header
    /* At the moment we only support single ranges.
        * Multiple ranges requires some more work to ensure it works correctly
        * and comply with the spesifications: http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
        *
        * Multirange support annouces itself with:
        * header('Accept-Ranges: bytes');
        *
        * Multirange content must be sent with multipart/byteranges mediatype,
        * (mediatype = mimetype)
        * as well as a boundry header to indicate the various chunks of data.
    */
    header("Accept-Ranges: 0-$length");
    // header('Accept-Ranges: bytes');
    // multipart/byteranges
    // http://www.w3.org/Protocols/rfc2616/rfc2616-sec19.html#sec19.2
    if (isset($_SERVER['HTTP_RANGE'])){
        $c_start = $start;
        $c_end   = $end;

        // Extract the range string
        list(, $range) = explode('=', $_SERVER['HTTP_RANGE'], 2);
        // Make sure the client hasn't sent us a multibyte range
        if (strpos($range, ',') !== false){
            // (?) Shoud this be issued here, or should the first
            // range be used? Or should the header be ignored and
            // we output the whole content?
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            // (?) Echo some info to the client?
            exit;
        } // fim do if
        // If the range starts with an '-' we start from the beginning
        // If not, we forward the file pointer
        // And make sure to get the end byte if spesified
        if ($range{0} == '-'){
            // The n-number of the last bytes is requested
            $c_start = $size - substr($range, 1);
            } else {
            $range  = explode('-', $range);
            $c_start = $range[0];
            $c_end   = (isset($range[1]) && is_numeric($range[1])) ? $range[1] : $size;
        } // fim do if
        /* Check the range and make sure it's treated according to the specs.
            * http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
        */
        // End bytes can not be larger than $end.
        $c_end = ($c_end > $end) ? $end : $c_end;
        // Validate the requested range and return an error if it's not correct.
        if ($c_start > $c_end || $c_start > $size - 1 || $c_end >= $size){
            header('HTTP/1.1 416 Requested Range Not Satisfiable');
            header("Content-Range: bytes $start-$end/$size");
            // (?) Echo some info to the client?
            exit;
        } // fim do if

        $start  = $c_start;
        $end    = $c_end;
        $length = $end - $start + 1; // Calculate new content length
        fseek($fp, $start);
        header('HTTP/1.1 206 Partial Content');
    } // fim do if

    // Notify the client the byte range we'll be outputting
    header("Content-Range: bytes $start-$end/$size");
    header("Content-Length: $length");

        // Start buffered download
        $buffer = 1024 * 8;
        while(!feof($fp) && ($p = ftell($fp)) <= $end){
    if ($p + $buffer > $end){
    // In case we're only outputtin a chunk, make sure we don't
    // read past the length
    $buffer = $end - $p + 1;
    } // fim do if

    set_time_limit(0); // Reset time limit for big files
echo fread($fp, $buffer);
flush(); // Free up memory. Otherwise large files will trigger PHP's memory limit.
} // fim do while

fclose($fp);
    } // fim do function

?>