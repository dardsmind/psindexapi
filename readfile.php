<?php
include_once("modules/mod_crypt.php");

$_file    =    isset($_GET['file'])? $_GET['file']:"";
$fname   =    isset($_GET['fname'])? $_GET['fname']:"";

$file = UnzipStr($_file);

output_file($file, $fname, $mime_type='');

function output_file($file, $name, $mime_type='')
{
     /*
     This function takes a path to a file to output ($file),
     the filename that the browser will see ($name) and
     the MIME type of the file ($mime_type, optional).

     If you want to do something on download abort/finish,
     register_shutdown_function('function_name');
     */
     //echo $file;

     if(trim($name=="")){
         $f_extension = strtolower(substr(strrchr($file,"."),1));
         $f_ename=GenerateRandomNumber(16);
         $name=$f_ename.".".$f_extension;
     }
     //die('File not found or inaccessible!');
     $size = filesize($file);
     $name = rawurldecode($name);

     /* Figure out the MIME type (if not specified) */
     $known_mime_types=array(
         "pdf" => "application/pdf",
         "txt" => "text/plain",
         "html" => "text/html",
         "htm" => "text/html",
        "exe" => "application/octet-stream",
        "zip" => "application/zip",
        "doc" => "application/msword",
        "xls" => "application/vnd.ms-excel",
        "ppt" => "application/vnd.ms-powerpoint",
        "gif" => "image/gif",
        "png" => "image/png",
        "jpeg"=> "image/jpg",
        "jpg" =>  "image/jpg",
        "php" => "text/plain"
     );

     $content_desp="inline";
     if($mime_type==''){
         $file_extension = strtolower(substr(strrchr($file,"."),1));
         if(array_key_exists($file_extension, $known_mime_types)){
            $mime_type=$known_mime_types[$file_extension];
         } else {
            $mime_type="application/force-download";
            $content_desp="attachment";
         };
     };

     //$mime_type="application/pdf";
     @ob_end_clean(); //turn off output buffering to decrease cpu usage

     // required for IE, otherwise Content-Disposition may be ignored
    if(ini_get('zlib.output_compression'))
    ini_set('zlib.output_compression', 'Off');

    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: '.$content_desp.'; filename="'.$name.'"');
    //header('Content-Disposition: attachment; filename="'.$name.'"');
    header("Content-Transfer-Encoding: binary");
    header('Accept-Ranges: bytes');
     /* The three lines below basically make the
        download non-cacheable */
    header("Cache-control: private");
    header('Pragma: private');
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    $new_length=$size;
    header("Content-Length: ".$size);

     /* output the file itself */
     $chunksize = 1*(1024*1024); //you may want to change this
     $bytes_send = 0;
     if ($file = fopen($file, 'r'))
     {
        //if(isset($_SERVER['HTTP_RANGE']))
        //fseek($file, $range);
        while(!feof($file) && (!connection_aborted()) && ($bytes_send<$new_length))
        {
            $buffer = fread($file, $chunksize);
            print($buffer); //echo($buffer); // is also possible
            flush();
            $bytes_send += strlen($buffer);
        }
         fclose($file);
     } else die('Error - can not open file.');
     die();
}

function GenerateRandomNumber($len = 8){
    $pass = '';
    $lchar = 0;
    $char = 0;
    for($i = 0; $i < $len; $i++){
        while($char == $lchar){
            $char = rand(48, 109);
            if($char > 57) $char += 7;
            if($char > 90) $char += 6;
        }
        $pass .= chr($char);
        $lchar = $char;
    }
    return $pass;
}
?>
