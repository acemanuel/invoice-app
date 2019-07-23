<?php
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB', 'project');
define('HOST', 'localhost');

class genOps{
    public function validatenumber($value){
        return ctype_digit($value)?true:false;
    }
    public function validatealnum($value){
        return ctype_alnum($value)?true:false;
    }
    public function validateemail($value){
        return filter_var($value, FILTER_VALIDATE_EMAIL)?true:false;
    }
}
class sqlOps extends genOps{
    private static $conn;

    public function __construct(){
        $this->connection = $this->iconnect();
        return $this->connection;
    }
    public function iconnect(){
        self::$conn = self::$conn?self::$conn:new mysqli(HOST, DB_USER, DB_PASS, DB);
        return self::$conn;
    }
    public function callquery($thequery){
        $this->qr = $this->connection->query($thequery) or die("something is wrong with ".$thequery);
        return $this->qr;
    }
    public function selectOp($col, $tab, $wh, $lim, $ord){
        $sel = "SELECT $col FROM $tab $wh $lim $ord";
        $this->result = $this->callquery($sel);
        if($this->result){
            $found=$this->result->num_rows;
            return $found>0 ? true : false;
        }
    }
    public function insertOp($tab, $col, $val){
        $ins = "INSERT INTO $tab ($col) VALUES($val)";
        $this->result = $this->callquery($ins);
        return $this->result ? true : false;
    }
    public function selNfetch($col, $tab, $where){
        $sel="SELECT $col FROM $tab $where";
        $this->result=$this->callquery($sel);
        if($this->result){
            $found=$this->result->num_rows;
            if($found>0){
                while($this->fetch=$this->result->fetch_object())
                return $this->fetch;
            }
        }
    }
    public function fetchAssoc($col, $tab, $where){
        $sel="SELECT $col FROM $tab $where";
        $this->result=$this->callquery($sel);
        if($this->result){
            $found=$this->result->num_rows;
            if($found){
                $fetcher = [];
                while($this->fetch=$this->result->fetch_assoc())
                array_push($fetcher, $this->fetch);
                return $fetcher;
            }
        }
    }
    public function updateOp($tab, $set, $where){
        $update="UPDATE $tab SET $set $where";
        $this->result=$this->callquery($update);
        return $this->result ? true : false;
    }
    public function createDB($dbname){
        $create = "CREATE DATABASE IF NOT EXISTS $dbname";
        $this->result=$this->callquery($create);
        return $this->result ? true : false;
    }
    public function selectDB($dbname){
        $sel = mysqli_select_db($this->iconnect(), $dbname);
        return $sel ? true: false;
    }
    public function createtab($tabname, $cols){
        $createtab = "CREATE TABLE IF NOT EXISTS $tabname($cols)";
        $this->result=$this->callquery($createtab);
        return $this->result ? true : false;
    }
    public function DBnTAB($dbname, $tabname, $cols){
        $sel = mysqli_select_db($this->iconnect(), $dbname);
        $createtab = "CREATE TABLE IF NOT EXISTS $tabname($cols)";
        $this->result=$this->callquery($createtab);
        return $this->result ? true : false;
    }
    public function deleteop($tab, $where){
        $delete = "DELETE FROM $tab $where";
        $this->result=$this->callquery($delete);
        return $this->result ? true : false;
    }
    public function img_upload(array ...$file) : ? string {
        if (empty($file)) {
            throw new exception('no file selected');
        }
        $image = $file;
        if ($image[0]['error'] !== 0) {
            if ($image[0]['error'] === 1)
                throw new exception('Max upload size exceeded');
            throw new exception('Image uploading error: INI Error');
        }
        // check if the file exists
        if (!file_exists($image[0]['tmp_name']))
            throw new exception('Image file is missing in the server');
        $maxFileSize = 6 * 10e6; // in bytes
        if ($image[0]['size'] > $maxFileSize)
            throw new exception('Max size limit exceeded'); 
        
            // check if uploaded file is an image
        $imageData = getimagesize($image[0]['tmp_name']);

        if (!$imageData)
            throw new exception('Invalid image');
        $mimeType = $imageData['mime'];
        // validate mime type
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($mimeType, $allowedMimeTypes))
            throw new exception('please select an image');
        
        // nice! it's a valid image
        // get file extension (ex: jpg, png) not (.jpg)
        $fileExtention = strtolower(pathinfo($image[0]['name'], PATHINFO_EXTENSION));
        // create random name for your image
        $fileName = round(microtime(true)) . mt_rand() . '.' . $fileExtention; // anyfilename.jpg
        // Create the path starting from DOCUMENT ROOT of your website
        $path = 'C:\Users\ACE\myapp\src\assets\img\uploads/' . $fileName;

        // file path in the computer - where to save it 

        $Angularpath = 'assets/img/uploads/' . $fileName;
        if (!move_uploaded_file($image[0]['tmp_name'], $path))
            throw new exception('Error in moving the uploaded file');
        return $Angularpath;
    }
}

?>