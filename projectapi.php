<?php

header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Headers: X-Requested-With, Authorrization, content-type, access-control-allow-origin, access-control-allow-methods, access-control-allow-headers");

require("classfile.php");
$sql = new sqlOps;
$post = json_decode(file_get_contents('php://input'),TRUE);

use Firebase\JWT\JWT;
require("firejwt/JWT.php");
define("SECRET_KEY", "learningjwt");

function encrypt_jwt($issuer, $audience, $user){
    $key = SECRET_KEY;
    $token = array(
        "iss"=>$issuer, "aud"=>$audience, "id"=>$user, "iat"=>time(), "nbf"=>time()
    );
    $hu = new JWT; $done = $hu::encode($token, $key);
    return $done;
}

function readtoken($token){
    $JWread = new JWT;
    $decode = jwt::decode($token, "learningjwt", array('HS256'));
    $decoded_array = (array)$decode;
    $user = $decoded_array['id'];
    return $user;
}

if($post['mykey'] == 'a1' || $post['mykey'] == 'a2' || $post['mykey'] == 'a3' || $post['mykey'] == 'a4' || $post['mykey'] == 'a5' || $post['mykey'] == 'a6' || $post['mykey'] == 'a7' || $post['mykey'] == 'a8' || $post['mykey'] == 'a9' || $post['mykey'] == 'a10' || $post['mykey'] == 'a11' || $post['mykey'] == 'a12' || $post['mykey'] == 'a13' || $post['mykey'] == 'a14' || $post['mykey'] == 'a15' || $post['mykey'] == 'a16' || $post['mykey'] == 'a17' || $post['mykey'] == 'a18' || $post['mykey'] == 'a19' || $post['mykey'] == 'a20'){
    $code = '';
    $cookie = '';
    $name = '';
    $message='';

    //for signup. default status code is 0
    if($post['mykey'] == 'a1'){
        foreach ($post as $key => $value) {
            if(empty($value)){
                $code = '01'; $message = $key.' cannot be empty';
            }else{
                if($key == 'email'){
                    if($sql->validateemail($value) == false){
                        $code = '01'; $message = 'enter a valid email address'; $cookie = 'ok'; $name = '';
                    }
                }elseif($key == 'password'){
                    if(strlen($value) < 6){
                        $code = '01'; $message = $key.' must be at least six characters'; $cookie = ''; $name = '';
                    }elseif($value != $post['confirmpassword']){
                        $code = '01'; $message = $key.'s do not match'; $cookie = ''; $name = '';
                    }
                }
            }
        }
        if($code != '01'){
            $col = 'email';
            $tab = 'users';
            $where = "WHERE email = '".$post['email']."'";

            $sel = $sql->selectOp($col, $tab, $where, '', '');
            if($sel){
                $code = '02'; $message = 'email is already in use';
            }else{
                $sid = sha1($post['email'].time());
                $enc_pass = password_hash($post['password'], PASSWORD_BCRYPT);
                $col = "email, password, service_id";
                $value = "'".$post['email']."', '".$enc_pass."', '".$sid."'";
                $tab = 'users';

                $ins = $sql->insertOp($tab, $col, $value);
                if($ins){
                    $s_col = "service_id";
                    $s_value = "'".$sid."'";
                    $s_tab = 'service';

                    $serv = $sql->insertOp($s_tab, $s_col, $s_value);
                    $code = '00'; $message = 'account creation successfull'; $cookie = ''; $name = '';
                }
            }
        }
    }

    //for login
    if($post['mykey'] == 'a2'){
        foreach ($post as $key => $value) {
            if(empty($value)){
                $code = '01'; $message = $key.' cannot be empty';
            }else{
                if($key == 'email'){
                    if($sql->validateemail($value) == false){
                        $code = '01'; $message = 'enter a valid email'; $cookie = ''; $name = '';
                    }
                }
            }
        }
        if($code != '01'){
            $tab = 'users';
            $col =  'email, password, status';
            $where = "WHERE email = '".$post['email']."'";
            $selfetch = $sql->selNfetch($col, $tab, $where, '', '');

            if(!$selfetch){
                $code = '02'; $message = 'Invalid login';
            }elseif(password_verify($post['password'], $selfetch->password)){
                $issuer = "http://localhost:4200";
                $audience = "http://localhost:4200/dashboard";
                $user = "'".$post['email']."'";
                $varJWT = encrypt_jwt($issuer, $audience, $user);
                $code = '00'; $message = 'Login Successful'; $cookie = $varJWT; $name = $user;

                if($selfetch->status == 0){
                    $code = '00'; $message = $selfetch->status; $cookie = $varJWT; $name = $user;
                }
            }else{
                $code = '02'; $message = 'Invalid login'; $cookie = ''; $name = '';
            }
        } 
    }

    //for creating of service. status code is updated to 1
    if($post['mykey'] == 'a3'){
        if(isset($post['readCookie'])){
            $read = readtoken($post['readCookie']);
            if($read == true){
                $sid = $post['sid'];
                $newdb = substr($sid, 0, 4).substr($sid, -4, 4);

                foreach ($post as $key => $value) {
                    if(empty($value) && $key != 'vat'){
                    $code = '01'; $message = $key.' cannot be empty';
                    }
                    else{
                        // if($key == 'name' || $key == 'address'){
                        //     if($sql->validatealnum($value) == false){
                        //         $code = '01'; $message = $key.' can only be alphanumeric';
                        //     }
                        // }
                        if($key == 'phone'){
                            if($sql->validatenumber($value) == false){
                                $code = '01'; $message = $key.' can only be numeric';
                            }elseif(strlen($value) != 11){
                                $code = '01'; $message = $key.' is invalid';
                            }
                        }
                    }
                }
                if($code != '01'){
                    $col = 'service_url';
                    $tab = 'service';
                    $where = "WHERE service_url = '".$post['url']."'";

                    $sel = $sql->selectOp($col, $tab, $where, '', '');
                    if($sel == null){
                        $tab = 'service';
                        $set = "service_name = '".$post['service_name']."', service_url = '".$post['url']."', address = '".$post['address']."', phone = '".$post['phone']."', vat = '".$post['vat']."'";
                        $where = "WHERE service_id = '".$post['sid']."'";

                        $update = $sql->updateOp($tab, $set, $where);
                        if($update){
                            $code = '00'; $message = 'profile updated';
                        }

                        $createdb = $sql->createDB($newdb);

                        if($createdb){
                            $code='00'; $message='service created successfully';
                        }
                        $tabStat = 'users';
                        $setStat = "status = 1";
                        $whereStat = "WHERE service_id = '".$post['sid']."'";

                        $updateStat = $sql->updateOp($tabStat, $setStat, $whereStat);

                        $tabname = 'invoices';
                        $cols = "
                            id int(100) not null auto_increment primary key, invoice_no varchar(60) not null, invoice_description varchar(100) not null, item_description varchar(2000) not null, quantity varchar(1000) not null, price varchar(1000) not null, amount varchar(1000) not null, total varchar(100) not null, date varchar(100) not null, client varchar(100) not null, status int(1) not null
                        ";

                        $dbntab = $sql->DBnTAB($newdb, $tabname, $cols);
                        if($dbntab){
                            $code = '00'; $message = $newdb.' updated';
                        }else{
                            $code = '02'; $message = $newdb.' not found!';
                        }

                        $tabname = 'clients';
                        $cols = "
                            id int(100) not null AUTO_INCREMENT PRIMARY KEY, name varchar(100) not null, email varchar(100) not null, phone varchar(100) not null, address varchar(100) not null, status int(1) not null
                        ";
                        $dbntab = $sql->DBnTAB($newdb, $tabname, $cols);
                        if($dbntab){
                            $code = '00'; $message = $newdb.' updated';
                        }else{
                            $code = '02'; $message = $newdb.' not found!';
                        }
                    }else{
                        $code="02"; $message="url already exists";
                    }
                }
            }else{
                $code = '02'; $message = 'error! please login again';
            }
        }else{
            $code = '02'; $message = 'error! please login';
        }
    }

    //for creating of invoice
    if($post['mykey'] == 'a4'){
        if(isset($post['readCookie'])){
            $read = readtoken($post['readCookie']);
            if($read == true){
                if(empty($post['client'])){
                    $code = '01'; $message = 'Select a client';
                }elseif(empty($post['invoice_description'])){
                    $code = '01'; $message = 'enter invoice_description';
                }
                if($code != '01'){
                    $mydb = $post['db'];
                    $db = substr($mydb, 0, 4).substr($mydb, -4, 4);

                    $seldb = $sql->selectDB($db);
                    if($seldb){
                        $code = '00'; $message = $db;
                    }else{
                        $code = '02'; $message = $db.' not found';
                    }

                    $col = "invoice_no, invoice_description, item_description, quantity, price, amount, total, date, client, status";
                    $value = "'".$post['invoice_no']."', '".$post['invoice_description']."', '".$post['description']."', '".$post['quantity']."', '".$post['price']."', '".$post['amount']."', '".$post['total']."', '".date('m-d-y')."', '".$post['client']."', '1'";
                    $tab = 'invoices';

                    $ins = $sql->insertOp($tab, $col, $value);

                    if($ins){
                        $code = '00'; $message = 'invoicing successfull';
                    }else{
                        $code = '02'; $message = 'invoicing failed';
                    }
                }
            }else{
                $code = '02'; $message = 'error! please login again';
            }
        }else{
            $code = '02'; $message = 'error! please login';
        }   
    }

    //fetches all user data from db using email
    if($post['mykey'] == 'a5'){
        $col = '*';
        $tab = 'users';
        $where = "WHERE email = '".$post['email']."'";

        $fetch = $sql->selNfetch($col, $tab, $where);

        if($fetch){
            $code = '00'; $message = $fetch;
        }else{
            $code = '02'; $message = 'unable to fetch';
        }
    }

    //fetches all invoices from db as an asscoiative array with exemption of those with status code as 0
    if($post['mykey'] == 'a6'){
        $mydb = $post['db'];
        $db = substr($mydb, 0, 4).substr($mydb, -4, 4);

        $seldb = $sql->selectDB($db);
        if($seldb){
            $code = '00'; $message = $db;
        }else{
            $code = '02'; $message = $db.' not found';
        }

        $col = 'invoice_no, date, client';
        $tab = 'invoices';
        $where = 'WHERE status != 0';

        $fetch = $sql->fetchAssoc($col, $tab, $where);

        if($fetch){
            $code = '00'; $message = $fetch;
        }else{
            $code = '02'; $message = '';
        }
    }
    
    //fetches invoice data from db
    if($post['mykey'] == 'a7'){
        $mydb = $post['db'];
        $db = substr($mydb, 0, 4).substr($mydb, -4, 4);

        $seldb = $sql->selectDB($db);
        if($seldb){
            $code = '00'; $message = $db;
        }else{
            $code = '02'; $message = $db.' not found';
        }

        $col = '*';
        $tab = 'invoices';
        $where  = "WHERE invoice_no = '".$post['invoice_no']."'";

        $fetch = $sql->fetchAssoc($col, $tab, $where);

        if($fetch){
            $code = '00'; $message = $fetch;
        }else{
            $code = '02'; $message = 'invoice not found';
        }
    }

    //fetches all service data from db using id
    if($post['mykey'] == 'a8'){
        $col = '*';
        $tab = 'service';
        $where = "WHERE service_id = '".$post['sid']."'";

        $fetch = $sql->selNfetch($col, $tab, $where);

        if($fetch){
            $code = '00'; $message = $fetch;
        }else{
            $code = '02'; $message = 'unable to fetch';
        }
    }

    //fetches all user data from db using id
    if($post['mykey'] == 'a9'){
        $col = '*';
        $tab = 'users';
        $where = "WHERE service_id = '".$post['sid']."'";

        $fetch = $sql->selNfetch($col, $tab, $where);

        if($fetch){
            $code = '00'; $message = $fetch;
        }else{
            $code = '02'; $message = 'unable to fetch';
        }
    }

    //for adding of clients
    if($post['mykey'] == 'a10'){
        if(isset($post['readCookie'])){
            $read = readtoken($post['readCookie']);
            if($read == true){
                foreach ($post as $key => $value) {
                    if(empty($value) && $key != 'phone' && $key != 'address'){
                        $code = '01'; $message = $key.' cannot be empty';
                    }else{
                        if($key == 'email'){
                            if($sql->validateemail($value) == false){
                                $code = '01'; $message = 'enter a valid email address';
                            }
                        }elseif($key == 'phone'){
                            if(!empty($value)){
                                if($sql->validatenumber($value) == false){
                                    $code = '01'; $message = 'phone can only be numeric';
                                }elseif(strlen($value) != 11){
                                    $code = '01'; $message = $key.' is invalid';
                                }
                            }
                        }
                    }
                }
                if($code != '01'){
                    $mydb = $post['db'];
                    $db = substr($mydb, 0, 4).substr($mydb, -4, 4);

                    $seldb = $sql->selectDB($db);
                    if($seldb){
                        $code = '00'; $message = $db;
                    }else{
                        $code = '02'; $message = $db.' not found';
                    }

                    $col = 'email, status';
                    $tab = 'clients';
                    $where = "WHERE email = '".$post['email']."'";

                    $sel = $sql->selectOp($col, $tab, $where, '', '');
                    if($sel){
                        $col = 'email, status';
                        $tab = 'clients';
                        $where = "WHERE email = '".$post['email']."'";
                        $selnfetch = $sql->selNfetch($col, $tab, $where);
                        if($selnfetch->status != 0){
                            $code = '02'; $message = 'Client has already been added';
                        }else{
                            $tab = "clients";
                            $col = "name, email, phone, address, status";
                            $val = "'".$post['name']."', '".$post['email']."', '".$post['phone']."', '".$post['address']."', '1'";

                            $ins = $sql->insertOp($tab, $col, $val);

                            if($ins){
                                $code = '00'; $message = 'client added successfully';
                            }else{
                                $code = '01'; $message = 'client update unsuccessfull';
                            }
                        }
                    }else{
                        $tab = "clients";
                        $col = "name, email, phone, address, status";
                        $val = "'".$post['name']."', '".$post['email']."', '".$post['phone']."', '".$post['address']."', '1'";

                        $ins = $sql->insertOp($tab, $col, $val);

                        if($ins){
                            $code = '00'; $message = 'client added successfully';
                        }else{
                            $code = '01'; $message = 'client update unsuccessfull';
                        }
                    }
                }
            }else{
                $code = '02'; $message = 'error! please login again';
            }
        }else{
            $code = '02'; $message = 'error! please login';
        }
    }

    //fetches all clients from db where status is not 0
    if($post['mykey'] == 'a11'){
        $mydb = $post['db'];
        $db = substr($mydb, 0, 4).substr($mydb, -4, 4);

        $seldb = $sql->selectDB($db);
        if($seldb){
            $code = '00'; $message = $db;
        }else{
            $code = '02'; $message = $db.' not found';
        }

        $col = '*';
        $tab = 'clients';
        $where = 'WHERE status != 0';

        $fetch = $sql->fetchAssoc($col, $tab, $where);

        if($fetch){
            $code = '00'; $message = $fetch;
        }else{
            $code = '02'; $message = '';
        }
    }

    //fetches names of clients from db
    if($post['mykey'] == 'a12'){
        $mydb = $post['db'];
        $db = substr($mydb, 0, 4).substr($mydb, -4, 4);

        $seldb = $sql->selectDB($db);
        if($seldb){
            $code = '00'; $message = $db;
        }else{
            $code = '02'; $message = $db.' not found';
        }

        $col = 'name';
        $tab = 'clients';
        $where = "WHERE status != 0";

        $fetch = $sql->fetchAssoc($col, $tab, $where);

        if($fetch){
            $code = '00'; $message = $fetch;
        }else{
            $code = '02'; $message = '';
        }
    }

    //for marking a client as deleted. the clients' status is updated from 1 to 0
    if($post['mykey'] == 'a13'){
        if(isset($post['readCookie'])){
            $read = readtoken($post['readCookie']);
            if($read == true){
                $mydb = $post['db'];
                $db = substr($mydb, 0, 4).substr($mydb, -4, 4);

                $seldb = $sql->selectDB($db);
                if($seldb){
                    $code = '00'; $message = $db;
                }else{
                    $code = '02'; $message = $db.' not found';
                }

                $tab = 'clients';
                $set = 'status = 0';
                $where = "WHERE email = '".$post['email']."'";

                $del = $sql->updateOp($tab, $set, $where);
                if($del){
                    $code = '00'; $message = 'client deleted successfully';
                }
                else{
                    $code = '01'; $message = 'client not found';
                }
            }else{
                $code = '02'; $message = 'error! please login again';
            }
        }else{
            $code = '02'; $message = 'error! please login';
        }
    }

    //for marking an invoice as deleted. the invoice's status is updated from 1 to 0
    if($post['mykey'] == 'a14'){
        if(isset($post['readCookie'])){
            $read = readtoken($post['readCookie']);
            if($read == true){
                $mydb = $post['db'];
                $db = substr($mydb, 0, 4).substr($mydb, -4, 4);

                $seldb = $sql->selectDB($db);
                if($seldb){
                    $code = '00'; $message = $db;
                }else{
                    $code = '02'; $message = $db.' not found';
                }

                $tab = 'invoices';
                $set = 'status = 0';
                $where = "WHERE invoice_no = '".$post['invoice_no']."'";

                $del = $sql->updateOp($tab, $set, $where);
                if($del){
                    $code = '00'; $message = 'invoice deleted successfully';
                }
                else{
                    $code = '01'; $message = 'invoice not found';
                }
            }else{
                $code = '02'; $message = 'error! please login again';
            }
        }else{
            $code = '02'; $message = 'error! please login';
        }
    }

    //for fetching all invoices from db
    if($post['mykey'] == 'a15'){
        $mydb = $post['db'];
        $db = substr($mydb, 0, 4).substr($mydb, -4, 4);

        $seldb = $sql->selectDB($db);
        if($seldb){
            $code = '00'; $message = $db;
        }else{
            $code = '02'; $message = $db.' not found';
        }

        $col = 'invoice_no, date, client';
        $tab = 'invoices';

        $fetch = $sql->fetchAssoc($col, $tab, '');

        if($fetch){
            $code = '00'; $message = $fetch;
        }else{
            $code = '02'; $message = '';
        }
    }

    //for getting values for settings
    if($post['mykey'] == 'a16'){
        $sid = $post['sid'];

        $col = '*';
        $tab = 'service';
        $where = "WHERE service_id = '$sid'";

        $get = $sql->selNfetch($col, $tab, $where);

        $ecol = 'email';
        $etab = 'users';
        $ewhere = "WHERE service_id = '$sid'";

        $eget = $sql->selNfetch($ecol, $etab, $ewhere);

        if($get && $eget){
            $code = '00'; $message = $get; $name = $eget;
        }
    }

    //for settings update
    if($post['mykey'] == 'a17'){
        if(isset($post['readCookie'])){
            $read = readtoken($post['readCookie']);
            if($read == true){
                $sid = $post['sid'];

                $etab = 'users';
                $eset = "email = '".$post['email']."'";
                $ewhere = "WHERE service_id = '$sid'";

                $eupd = $sql->updateOp($etab, $eset, $ewhere);

                $tab = 'service';
                $set = "service_name = '".$post['service_name']."', service_url = '".$post['service_url']."', address = '".$post['address']."', phone = '".$post['phone']."', vat = '".$post['vat']."'";
                $where = "WHERE service_id = '$sid'";

                $upd = $sql->updateOp($tab, $set, $where);

                if($eupd && $upd){
                    $code = '00'; $message = 'Update saved successfully';
                }else{
                    $code = '01'; $message = 'Update Failed';
                }
            }else{
                $code = '02'; $message = 'error! please login again';
            }
        }else{
            $code = '02'; $message = 'error! please login';
        }        
    }

    if($post['mykey'] == 'a18'){
        if(isset($post['readCookie'])){
            $read = readtoken($post['readCookie']);
            if($read == true){
                $sid = $post['sid'];

                $col = 'password';
                $tab = 'users';
                $where = "WHERE service_id = '$sid'";

                $sel = $sql->selNfetch($col, $tab, $where);

                if(password_verify($post['cpassword'], $sel->password)){
                    if(strlen($post['npassword']) < 6){
                        $code = '01'; $message = 'password must be up to six(6) characters';
                    }elseif($post['npassword'] === $post['cfpassword']){
                        $newpass = password_hash($post['npassword'], PASSWORD_BCRYPT);

                        $tab = 'users';
                        $set = "password = '$newpass'";
                        $where = "WHERE service_id = '$sid'";

                        $upd = $sql->updateOp($tab, $set, $where);

                        if($upd){
                            $code = '00'; $message = 'password changed successfully';
                        }else{
                            $code = '02'; $message = 'password change failed';
                        }
                    }else{
                        $code = '01'; $message = 'passwords do not match';
                    }
                }else{
                    $code = '01'; $message = 'Current Password is incorrect';
                }
            }else{
                $code = '02'; $message = 'error! please login again';
            }
        }else{
            $code = '02'; $message = 'error! please login';
        }
    }

    if($post['mykey'] == 'a19'){
        if(isset($post['readCookie'])){
            $read = readtoken($post['readCookie']);
            if($read == true){
                $email = $post['email'];
                if(empty($email)){
                    $code = '01'; $message = 'you have not entered any email';
                }else{
                    if($sql->validateemail($email) == false){
                        $code = '01'; $message = 'enter a valid email';
                    }else{
                        $mydb = $post['db'];
                        $db = substr($mydb, 0, 4).substr($mydb, -4, 4);

                        $seldb = $sql->selectDB($db);
                        if($seldb){
                            $code = '00'; $message = $db;
                        }else{
                            $code = '02'; $message = $db.' not found';
                        }

                        $col = '*';
                        $tab = 'clients';
                        $where = "WHERE email = '".$post['email']."' && status != 0";

                        $get = $sql->selNfetch($col, $tab, $where);
                        if($get){
                            $code = '00'; $message = $get;
                        }else{
                            $code = '01'; $message = 'customer not found';
                        }
                    }
                }
            }else{
                $code = '02'; $message = 'error! please login again';
            }
        }else{
            $code = '02'; $message = 'error! please login';
        }
    }
    
    //for editing client info
    if($post['mykey'] == 'a20'){
        if(isset($post['readCookie'])){
            $read = readtoken($post['readCookie']);
            if($read == true){
                foreach ($post as $key => $value) {
                    if(empty($value) && $key != 'phone' && $key != 'address'){
                        $code = '01'; $message = $key.' cannot be empty';
                    }else{
                        if($key == 'email'){
                            if($sql->validateemail($value) == false){
                                $code = '01'; $message = 'enter a valid email address';
                            }
                        }elseif($key == 'phone'){
                            if(!empty($value)){
                                if($sql->validatenumber($value) == false){
                                    $code = '01'; $message = 'phone can only be numeric';
                                }elseif(strlen($value) != 11){
                                    $code = '01'; $message = $key.' is invalid';
                                }
                            }
                        }
                    }
                }
                if($code != '01'){
                    $mydb = $post['db'];
                    $db = substr($mydb, 0, 4).substr($mydb, -4, 4);

                    $seldb = $sql->selectDB($db);
                    if($seldb){
                        $code = '00'; $message = $db;
                    }else{
                        $code = '02'; $message = $db.' not found';
                    }

                    
                        // $col = 'email, status';
                        // $tab = 'clients';
                        // $where = "WHERE email = '".$post['email']."'";
                        // $selnfetch = $sql->selNfetch($col, $tab, $where);
                        // if($selnfetch->status != 0){
                        //     $code = '02'; $message = 'Client has already been added';
                        // }else{
                        //     $tab = "clients";
                        //     $set = "name = '".$post['name']."', email = '".$post['email']."', phone = '".$post['phone']."', address = '".$post['address']."'";
                        //     $where = "WHERE email = '".$post['email']."'";

                        //     $upd = $sql->updateOp($tab, $set, $where);

                        //     if($upd){
                        //         $code = '00'; $message = 'client added successfully';
                        //     }else{
                        //         $code = '01'; $message = 'client update unsuccessfull';
                        //     }
                        // }
                    
                        $tab = "clients";
                        $set = "name = '".$post['name']."', email = '".$post['email']."', phone = '".$post['phone']."', address = '".$post['address']."'";
                        $where = "WHERE email = '".$post['target_email']."' && status != 0";

                        $upd = $sql->updateOp($tab, $set, $where);

                        if($upd){
                            $code = '00'; $message = 'client added successfully';
                        }else{
                            $code = '01'; $message = 'client update unsuccessfull';
                        }
                    
                }
            }else{
                $code = '02'; $message = 'error! please login again';
            }
        }else{
            $code = '02'; $message = 'error! please login';
        }
    }
}

echo json_encode(['code'=>$code, 'message'=>$message, 'cookie'=>$cookie, 'user'=>$name]);

?>