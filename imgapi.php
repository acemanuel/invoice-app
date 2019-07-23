<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: X-Requested-With, Authorization ,content-type,access-control-allow-headers,Access-Control-Allow-Origin,access-control-allow-methods');

require_once __DIR__ . '/classfile.php';

$sql = new sqlOps;
$file = $_FILES['file'];
//$sid = $_POST['sid'];

try {
    $upload = $sql->img_upload($file);
    if ($upload){
        $tab = 'service';
        $set = "img_url = '$upload'";
        $where = "WHERE service_id = 'b40d73404d6ebc76a8373abfef0c8071fb6d669a'";

        $upl = $sql->updateOp($tab, $set, $where);
        if($upl){
            var_dump($upl);
        }else{
            echo 'no';
        }

        $result = $upl ? ['code' => '00', 'message' => $upload] : ['code' => '01', 'message' => 'image upload failed, try again'];
    }
}

catch (Exception $error) {
    $result = ['code' => '01', 'message' => $error->getMessage()];
}
echo json_encode($result);

?>