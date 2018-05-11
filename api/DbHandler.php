<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author
 * @link URL Tutorial link
 */
class DbHandler {

    private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `users` table method ------------------ */

    /**
     * Creating new user
     * @param String $name User full name
     * @param String $email User login email id
     * @param String $password User login password
     */

     //dating website

     public function adminLogin($email, $pass){
       require_once 'PassHash.php';

       $getpass = $this->conn->prepare("SELECT password from admin where email=?");
       $getpass->bind_param("s", $email);
       $getpass->execute();
       $getpass->bind_result($gcurrentpass);
       $getpass->fetch();
       $currentpass = $gcurrentpass;
       $checkbothpass = PassHash::check_password($currentpass, $pass);
       $getpass->close();
       $response = array();

       //return $checkbothpass;
       if($checkbothpass == true){
       $stmt = $this->conn->prepare("SELECT id, username, email from admin where email=? and password=?");
       $stmt->bind_param("ss", $email, $currentpass);

       //return $stmt->execute();
       $res = $stmt->execute();
       //return $res;
       if($res){
         $stmt->bind_result($aid, $aname, $aemail);
         $stmt->fetch();
         //return $aid;
         $stmt->store_result();
         $num_rowsadmin = $stmt->num_rows;
         //return $num_rowsadmin;
         if(!empty($aid)){
           //return $aid;
           $response['adminid'] = $aid;
           $response['adminname'] = $aname;
           $response['adminemail'] = $aemail;
         }
       }

       $stmt->close();
       //echo $stmt->__toString();die();

       if(!empty($aid)){
          $response['stat'] = 1;
       } else{
         $response['stat'] = 0;
       }
     } else{
       $response['stat'] = 0;
     }
       return $response;
     }

     public function monthlyUserUpdate(){
       $mfees = 0;

       $stmt = $this->conn->prepare("SELECT monthly_fees from general_settings");

       $res = $stmt->execute();
       $stmt->bind_result($monthly_fees);
       $stmt->fetch();
       if($res){
         $mfees = $monthly_fees;
       }

       $stmt->close();

       $usersar = array();
       $getallusers = $this->conn->prepare("SELECT id, status, admin_status, pause_time, wallet_amount from users where status=3 and admin_status=1 and pause_time=1");
       $getallusersres = $getallusers->execute();
       $getallusers->bind_result($userid, $status, $admin_status, $pausetime, $walletamount);

       while($getallusers->fetch()){
          array_push($usersar,array('userid'=>$userid, 'status'=>$status, 'admin_status'=>$admin_status, 'pausetime'=>$pausetime, 'walletamount'=>$walletamount));
       }

       $getallusers->close();
       foreach ($usersar as $user) {
         if($user['walletamount'] > $mfees){
           $trdate = date('Y-m-d H:i:s');

           $newtr = $this->conn->prepare("INSERT into transactions (user_id, amount, type, to_id, remarks, transaction_time) values(".$user['userid'].",'".$mfees."',1,'a1','User fees','".$trdate."')");

            if($newtr) { // assuming $mysqli is the connection

              $trres = $newtr->execute();
              // any additional code you need would go here.
            } else {
              $error = $this->conn->errno . ' ' . $this->conn->error;
              echo $error; // 1054 Unknown column 'foo' in 'field list'
            }

           $newtr->close();

           $users = $this->conn->prepare("UPDATE users set wallet_amount=wallet_amount-".$mfees." where id=".$userid." ");

         } else{
           $users = $this->conn->prepare("UPDATE users set admin_status=2,pause_time=2 where id=".$userid." ");
         }
       }


       $userupdate = $users->execute();

       $users->close();


       if($userupdate){
         return 1;
       } else{
         return 0;
       }
     }

     public function adminForgotPass($email){
       require_once 'PassHash.php';
       $response = array();

       //return $email;
       $stmt = $this->conn->prepare("SELECT id, email from admin where email=?");
       $stmt->bind_param("s", $email);

       //return $stmt->execute();
       $res = $stmt->execute();
       //return $res;
       if($res){
         $stmt->bind_result($aid, $aemail);
         $stmt->fetch();
         //return $aid;
         $aminid = $aid;
         $stmt->store_result();
         $num_rowsadmin = $stmt->num_rows;
         $stmt->close();
         //return $num_rowsadmin;
         if(!empty($aminid)){
           //return $aid;
           $length=8;
           $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
           $new_pass = substr(str_shuffle($chars),0,$length);
           $new_passhash = PassHash::hash($new_pass);

           $update = $this->conn->prepare("UPDATE admin set password=? where email=?");
           $update->bind_param("ss", $new_passhash,$aemail);
           $result = $update->execute();

           $update->close();
           $response['adminid'] = $aminid;
           $response['newpasshash'] = $new_passhash;
           $response['newpass'] = $new_pass;
           $response['adminemail'] = $aemail;
         }
       }
       //echo $stmt->__toString();die();

       if(!empty($aid)){
          $response['stat'] = 1;
       } else{
         $response['stat'] = 0;
       }
       return $response;
     }

public function getSiteInfo(){
  $sitinfo = $this->conn->prepare("SELECT * from general_settings");
  //$sitinfo->bind_param("i", $id);
  $res = $sitinfo->execute();
  $sitinfo->bind_result($id, $supportemail, $favimg, $logoimg, $boyimg, $girlimg, $blogurl, $fburl, $tweeturl, $youtubeurl, $instaurl, $linkedinurl, $dribbleurl, $googlepurl, $followtitle, $followdesc, $cptext,$visitors, $monfees);
  $sitinfo->fetch();
  if($res){
    $response['id'] = $id;
    $response['supportemail'] = $supportemail;
    $response['favimg'] = $favimg;
    $response['logoimg'] = $logoimg;
    $response['boyimg'] = $boyimg;
    $response['girlimg'] = $girlimg;
    $response['blogurl'] = $blogurl;
    $response['fburl'] = ($fburl);
    $response['tweeturl'] = $tweeturl;
    $response['youtubeurl'] = $youtubeurl;
    $response['instaurl'] = $instaurl;
    $response['linkedinurl'] = $linkedinurl;
    $response['dribbleurl'] = $dribbleurl;
    $response['googlepurl'] = $googlepurl;
    $response['fustitle'] = $followtitle;
    $response['fusdesc'] = $followdesc;
    $response['cptext'] = $cptext;
    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();

  return $response;
}

public function getAdminInfo($id){
  $sitinfo = $this->conn->prepare("SELECT * from admin where id=?");
  $sitinfo->bind_param("i", $id);
  $res = $sitinfo->execute();
  $sitinfo->bind_result($id, $username, $email, $password, $adminid);
  $sitinfo->fetch();
  if($res){
    $response['id'] = $id;
    $response['username'] = $username;
    $response['email'] = $email;

    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();

  return $response;
}

public function adminEarnings(){
  $sitinfo = $this->conn->prepare("SELECT sum(case when to_id='a1' and type=1 then amount else 0 end) from transactions");

  $res = $sitinfo->execute();
  $sitinfo->bind_result($total);
  $sitinfo->fetch();
  if($res){
    $response['total'] = $total;

    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();

  return $response;
}

public function totalVisitors(){
  $sitinfo = $this->conn->prepare("SELECT total_visitors from general_settings");

  $res = $sitinfo->execute();
  $sitinfo->bind_result($total);
  $sitinfo->fetch();
  if($res){
    $response['total'] = $total;

    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();

  return $response;
}

public function adminEarningsByYear($year){
  $sitinfo = $this->conn->prepare("SELECT id, amount, transaction_time from transactions where YEAR(transaction_time)=? and to_id='a1' and type='1' ");
  $sitinfo->bind_param("s", $year);
  $res = $sitinfo->execute();
  $sitinfo->bind_result($id, $amount, $ttime);
  if($res){

    $response['stat'] = 1;
    $yearly_user = array();

    while ($sitinfo->fetch()){
      $key = date('n', strtotime($ttime));
      $yearly_user[$key][] = (int)$amount;
    }

    for($i=1;$i<13;$i++){
      if(!array_key_exists($i,$yearly_user)){
        $yearly_user[$i] = array(0);
      }
    }

    $response['data'] = array(1=>array_sum($yearly_user[1]),
                              2=>array_sum($yearly_user[2]),
                              3=>array_sum($yearly_user[3]),
                              4=>array_sum($yearly_user[4]),
                              5=>array_sum($yearly_user[5]),
                              6=>array_sum($yearly_user[6]),
                              7=>array_sum($yearly_user[7]),
                              8=>array_sum($yearly_user[8]),
                              9=>array_sum($yearly_user[9]),
                              10=>array_sum($yearly_user[10]),
                              11=>array_sum($yearly_user[11]),
                              12=>array_sum($yearly_user[12])
                             );
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();

  return $response;
}

public function getUsersList($sort, $sfield, $page, $perpage, $stat, $searchq, $type){
  $where = "where status=3 ";

  if(!empty($stat)){
    $where .= " and admin_status = ".$stat;
  }
  if(!empty($type)){
    $where .= " and gender = '".$type."'";
  }
  if(!empty($searchq)){
    $where .= " and (first_name like '%".$searchq."%' || last_name like '%".$searchq."%' || gender like '%".$searchq."%' || age like '%".$searchq."%' || email like '%".$searchq."%' || phone like '%".$searchq."%')";
  }

  $gettotal = $this->conn->prepare("SELECT id, gender, first_name, last_name, email, phone, location, age, images,admin_status from users ".$where."");

  $gettotal->execute();
  $gettotal->store_result();
  $totalusers = $gettotal->num_rows;
  $gettotal->close();

  if($sfield == 'srno'){
    $sfield = 'id';
  }
  if($sfield == 'username'){
    $sfield = 'first_name';
  }

  $start = ($page-1)*$perpage;

  $sitinfo = $this->conn->prepare("SELECT id, gender, first_name, last_name, email, phone, location, age, status, images, admin_status from users ".$where." order by ".$sfield." ".$sort." limit ?,?");
  $sitinfo->bind_param("ii", $start, $perpage);
  $res = $sitinfo->execute();
  $sitinfo->bind_result($id, $gender, $fname, $lname, $email, $phone, $location, $age, $status, $images, $adminstat);
  $response['users'] = array();
  if($res){
    $uc = 0;
    while ($sitinfo->fetch()){
      $uc++;
    if(!empty($images)){
      $imageuser = unserialize($images);
      if(count($imageuser) > 0){
      $fimguser = $imageuser[0];
    } else{
      $fimguser = '';
    }
    } else{
      $fimguser = '';
    }
    $user['srno'] = $uc;
    $user['id'] = $id;
    $user['gender'] = $gender;
    $user['username'] = $fname.' '.$lname;
    $user['email'] = $email;
    $user['phone'] = $phone;
    $user['location'] = $location;
    $user['age'] = $age;
    $user['status'] = $status;
    $user['profile_pic'] = $fimguser;
    $user['admin_status'] = $adminstat;
    array_push($response['users'], $user);
  }

    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();
  $response['total'] = $totalusers;
  return $response;
}

public function getTransactionList($sort, $sfield, $page, $perpage, $uid, $searchq, $type, $daterange){
  $where = "where 1";

  if(!empty($uid)){
    $where .= " and (user_id = ".$uid." or to_id=".$uid.")";
  }
  if(!empty($type)){
    $where .= " and type = '".$type."'";
  }
  if(!empty($daterange)){
    $getdate = explode("/", $daterange);
    $from = date("Y-m-d",strtotime(trim($getdate[0])));
    $to = date("Y-m-d",strtotime(trim($getdate[1])));

    $where .= " and (transaction_time >= '".$from."' and transaction_time <= '".$to."')";
  }
  if(!empty($searchq)){
    $where .= " and (fromu.first_name like '%".$searchq."%' || fromu.last_name like '%".$searchq."%' || tou.first_name like '%".$searchq."%' || tou.last_name like '%".$searchq."%' || froma.username like '%".$searchq."%' || toa.username like '%".$searchq."%' || amount like '%".$searchq."%' || remarks like '%".$searchq."%')";
  }

  $gettotal = $this->conn->prepare("SELECT t.*, fromu.first_name as fufn, fromu.last_name as fuln, tou.first_name as tufn, tou.last_name as tuln, froma.username as fadmin, toa.username as tadmin from transactions as t left join users as fromu on t.user_id=fromu.id left join users as tou on t.to_id=tou.id left join admin as froma on froma.admin_id=t.user_id left join admin as toa on toa.admin_id=t.to_id ".$where."");

  $gettotal->execute();
  $gettotal->store_result();
  $totalusers = $gettotal->num_rows;
  $gettotal->close();

  if($sfield == 'tdate'){
    $sfield = 't.transaction_time';
  }
  if($sfield == 'ttime'){
    $sfield = 't.transaction_time';
  }

  $start = ($page-1)*$perpage;

  $sitinfo = $this->conn->prepare("SELECT t.*, fromu.first_name as fufn, fromu.last_name as fuln, tou.first_name as tufn, tou.last_name as tuln, froma.username as fadmin, toa.username as tadmin from transactions as t left join users as fromu on t.user_id=fromu.id left join users as tou on t.to_id=tou.id left join admin as froma on t.user_id=froma.admin_id left join admin as toa on t.to_id=toa.admin_id ".$where." order by ".$sfield." ".$sort." limit ?,?");
  $sitinfo->bind_param("ii", $start, $perpage);
  $res = $sitinfo->execute();
  $sitinfo->bind_result($id, $userid, $amount, $type, $toid, $remarks, $ttime, $fromufn, $fromuln, $toufn, $touln, $froma, $toa);
  $response['users'] = array();
  if($res){
    $uc = 0;
    while ($sitinfo->fetch()){
      $uc++;

    $user['srno'] = $uc;
    $user['id'] = $id;
    $user['from_uid'] = $userid;
    if(!empty($fromufn)){
      $user['from_user'] = $fromufn." ".$fromuln;
    } else{
      $user['from_user'] = '';
    }

    $user['to_uid'] = $toid;

    if(!empty($toufn)){
      $user['to_user'] = $toufn." ".$touln;
    } else{
      $user['to_user'] = '';
    }

    $user['from_admin'] = $froma;
    $user['to_admin'] = $toa;
    $user['tdate'] = date("n-j-Y",strtotime($ttime));
    $user['ttime'] = date("h:i A",strtotime($ttime));
    $user['type'] = $type;
    $user['amount'] = $amount;
    $user['remarks'] = $remarks;

    array_push($response['users'], $user);
  }

    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();
  $response['total'] = $totalusers;
  return $response;
}

public function getGirlProfiles($sort, $sfield, $page, $perpage, $stat, $searchq){
  $where = "where gender='Female' and status!=1";

  if(!empty($stat)){
    $where .= " and status = ".$stat;
  }
  if(!empty($searchq)){
    $where .= " and (first_name like '%".$searchq."%' || last_name like '%".$searchq."%' || gender like '%".$searchq."%' || age like '%".$searchq."%' || email like '%".$searchq."%' || phone like '%".$searchq."%')";
  }

  $gettotal = $this->conn->prepare("SELECT id, gender, first_name, last_name, email, phone, location, age, images from users ".$where."");

  $gettotal->execute();
  $gettotal->store_result();
  $totalusers = $gettotal->num_rows;
  $gettotal->close();

  if($sfield == 'srno'){
    $sfield = 'id';
  }
  if($sfield == 'username'){
    $sfield = 'first_name';
  }

  $start = ($page-1)*$perpage;

  $sitinfo = $this->conn->prepare("SELECT id, gender, first_name, last_name, email, phone, location, age, status, images from users ".$where." order by ".$sfield." ".$sort." limit ?,?");
  $sitinfo->bind_param("ii", $start, $perpage);
  $res = $sitinfo->execute();
  $sitinfo->bind_result($id, $gender, $fname, $lname, $email, $phone, $location, $age, $status, $images);
  $response['users'] = array();
  if($res){
    $uc = 0;
    while ($sitinfo->fetch()){
      $uc++;
    if(!empty($images)){
      $imageuser = unserialize($images);
      if(count($imageuser) > 0){
      $fimguser = $imageuser[0];
    } else{
      $fimguser = '';
    }
    } else{
      $fimguser = '';
    }
    $user['srno'] = $uc;
    $user['id'] = $id;
    $user['gender'] = $gender;
    $user['username'] = $fname.' '.$lname;
    $user['email'] = $email;
    $user['phone'] = $phone;
    $user['location'] = $location;
    $user['age'] = $age;
    $user['status'] = $status;
    $user['profile_pic'] = $fimguser;
    array_push($response['users'], $user);
  }

    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();
  $response['total'] = $totalusers;
  return $response;
}

public function getHomeUsersList($sort, $sfield, $page, $perpage){
  $gettotal = $this->conn->prepare("SELECT id, gender, first_name, last_name, email, phone, location, age, images from users");
  $gettotal->execute();
  $gettotal->store_result();
  $totalusers = $gettotal->num_rows;
  $gettotal->close();

  if($sfield == 'srno'){
    $sfield = 'id';
  }
  if($sfield == 'username'){
    $sfield = 'first_name';
  }


  $start = ($page-1)*$perpage;

  $sitinfo = $this->conn->prepare("SELECT id, gender, first_name, last_name, email, phone, location, age, status, images from users where gender='Female' order by ".$sfield." ".$sort." limit ?,?");
  $sitinfo->bind_param("ii", $start, $perpage);
  $res = $sitinfo->execute();
  $sitinfo->bind_result($id, $gender, $fname, $lname, $email, $phone, $location, $age, $status, $images);
  $response['users'] = array();
  if($res){
    $uc = 0;
    while ($sitinfo->fetch()){
      $uc++;
    if(!empty($images)){
      $imageuser = unserialize($images);
      if(count($imageuser) > 0){
      $fimguser = $imageuser[0];
    } else{
      $fimguser = '';
    }
    } else{
      $fimguser = '';
    }
    $user['srno'] = $uc;
    $user['id'] = $id;
    $user['gender'] = $gender;
    $user['username'] = $fname.' '.$lname;
    $user['email'] = $email;
    $user['phone'] = $phone;
    $user['location'] = $location;
    $user['age'] = $age;
    $user['status'] = $status;
    $user['profile_pic'] = $fimguser;
    array_push($response['users'], $user);
  }

    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();
  $response['total'] = $totalusers;
  return $response;
}

public function getCmsPages($sort, $sfield, $page, $perpage){
  $gettotal = $this->conn->prepare("SELECT id, title from cms_pages");
  $gettotal->execute();
  $gettotal->store_result();
  $totalusers = $gettotal->num_rows;
  $gettotal->close();


  $start = ($page-1)*$perpage;

  $sitinfo = $this->conn->prepare("SELECT id, title from cms_pages order by ".$sfield." ".$sort." limit ?,?");
  $sitinfo->bind_param("ii", $start, $perpage);
  $res = $sitinfo->execute();
  $sitinfo->bind_result($id, $title);
  $response['users'] = array();
  if($res){
    $uc = 0;
    while ($sitinfo->fetch()){
      $uc++;

    $user['srno'] = $uc;
    $user['id'] = $id;
    $user['title'] = $title;

    array_push($response['users'], $user);
  }

    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();
  $response['total'] = $totalusers;
  return $response;
}

public function cmslist(){
  $stmt = $this->conn->prepare("SELECT * from cms_pages");
 if ($stmt->execute()) {
            $cmslist = $stmt->get_result();
           $stmt->close();
           return $cmslist;
       } else {
           return NULL;
       }
}

public function getrecentlisting(){

  $stmt = $this->conn->prepare("SELECT id,first_name,last_name,username,location,highlight,status,images from users where gender='Female' and status=3 and admin_status=1 and pause_time=1 ORDER BY id DESC limit 3");
  if($stmt->execute())
  {
    $stmt->bind_result($id,$first_name,$last_name,$username,$location,$highlight,$status,$images);
     $response['highlight'] = array();
  while ($stmt->fetch()){
    $user=array();
    $user['id'] = $id;
    $user['name'] = $first_name.' '.$last_name;
    $user['username'] = $username;
    $user['location'] = $location;
    $user['highlight'] = $highlight;
    $user['status'] = $status;
    $user['profile_pic'] =unserialize($images);
     array_push($response['highlight'], $user);
   }
    $stmt->close();
    return $response['highlight'];

  }
  else
  {
    return NULL;
  }
}

public function gethighlightprofile(){

  $stmt = $this->conn->prepare("SELECT id,first_name,last_name,username,weight,height,gender,age,email,phone,sexual,skype,whatsapp,viber,wechat,location,service_location,aboutme,highlight,status,images from users where gender='Female' and highlight=1 and status=3 and admin_status=1 and pause_time=1");
  if($stmt->execute())
  {
    $stmt->bind_result($id,$first_name,$last_name,$username,$weight,$height,$gender,$age,$email,$phone,$sexual,$skype,$whatsapp,$viber,$wechat,$location,$service_location,$aboutme,$highlight,$status,$images);
     $response['highlight'] = array();
  while ($stmt->fetch()){
    $user=array();
    $user['id'] = $id;
    $user['name'] = $first_name.' '.$last_name;
    $user['username'] = $username;
    $user['weight'] = $weight;
    $user['height'] = $height;
    $user['age'] = $age;
    $user['email'] = $email;
    $user['phone'] = $phone;
    $user['sexual'] = $sexual;
    $user['skype'] = $skype;
    $user['whatsapp'] = $whatsapp;
    $user['viber'] = $viber;
    $user['wechat'] = $wechat;
    $user['location'] = $location;
    $user['service_location'] = $service_location;
    $user['aboutme'] = $aboutme;
    $user['highlight'] = $highlight;
    $user['status'] = $status;
    $user['profile_pic'] =unserialize($images);
     array_push($response['highlight'], $user);
   }
    $stmt->close();
    return $response['highlight'];

  }
  else
  {
    return NULL;
  }
}

public function profileList(){

  $stmt = $this->conn->prepare("SELECT id,first_name,last_name,username,weight,height,gender,age,email,phone,sexual,skype,whatsapp,viber,wechat,location,service_location,aboutme,highlight,status,images from users where gender='Female' and status=3 and admin_status=1 and pause_time=1");
  if($stmt->execute())
  {
    $stmt->bind_result($id,$first_name,$last_name,$username,$weight,$height,$gender,$age,$email,$phone,$sexual,$skype,$whatsapp,$viber,$wechat,$location,$service_location,$aboutme,$highlight,$status,$images);
     $response['profiles'] = array();
     //var_dump($id);

  while ($stmt->fetch()){
    $user=array();
    $user['id']=$id;
    //$count=this->countcomments($id);
    $user['name'] = $first_name.' '.$last_name;
    $user['username'] = $username;
    $user['weight'] = $weight;
    $user['height'] = $height;
    $user['age'] = $age;
    $user['email'] = $email;
    $user['phone'] = $phone;
    $user['sexual'] = $sexual;
    $user['skype'] = $skype;
    $user['whatsapp'] = $whatsapp;
    $user['viber'] = $viber;
    $user['wechat'] = $wechat;
    $user['location'] = $location;
    $user['service_location'] = $service_location;
    $user['aboutme'] = $aboutme;
    $user['highlight'] = $highlight;
    $user['status'] = $status;
    $user['profile_pic'] =unserialize($images);
    array_push($response['profiles'], $user);
   }

    $stmt->close();
    return $response['profiles'];
  }
  else
  {
    return NULL;
  }
}

public function countcomments($id='')
{

  # code...
   $csearch = $this->conn->prepare("SELECT * from comments WHERE girl_id = ? ");
      $csearch->bind_param("i",$id);
      $csearch->execute();
      $csearch->store_result();
      $num_rows = $csearch->num_rows;
      $csearch->close();
      if($num_rows)
      {
          return $num_rows;
      }
      else
      {
        return null;
      }

      //exit;
}

public function updateustat($id, $stat){
  $response = array();

  $update = $this->conn->prepare("UPDATE users set status=? where id=?");
  $update->bind_param("ss", $stat,$id);
  $result = $update->execute();

  $update->close();

  if($result){
     $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  return $response;
}

public function updateadminustat($id, $stat){
  $response = array();

  $update = $this->conn->prepare("UPDATE users set admin_status=? where id=?");
  $update->bind_param("ss", $stat,$id);
  $result = $update->execute();

  $update->close();

  if($result){
     $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  return $response;
}

public function deleteUser($ids=array()){
  $idsare = implode(',',$ids);
  $delete = $this->conn->prepare("DELETE from users where id in (".$idsare.")");
  //$delete->bind_param($idsare);

  $res = $delete->execute();
  $delete->close();

  if($res){
    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
}

public function deleteCmsPage($ids=array()){
  $idsare = implode(',',$ids);
  $delete = $this->conn->prepare("DELETE from cms_pages where id in (".$idsare.")");
  //$delete->bind_param($idsare);

  $res = $delete->execute();
  $delete->close();

  if($res){
    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
}

public function updateAdmin($id, $username, $email, $password){
  require_once 'PassHash.php';
  $response = array();
      if(!empty($password)){
        $new_passhash = PassHash::hash($password);
        $update = $this->conn->prepare("UPDATE admin set username=?, password=?, email=? where id=?");
        $update->bind_param("ssss", $username, $new_passhash, $email, $id);
      } else{
        $update = $this->conn->prepare("UPDATE admin set username=?, email=? where id=?");
        $update->bind_param("sss", $username, $email, $id);
      }

      $result = $update->execute();

      $update->close();
      if($result){
        $response['stat'] = 1;
      } else{
        $response['stat'] = 0;
      }

  return $response;
}

public function updateGensets($semail, $logoimage, $favimage, $fboyimg, $fgirlimg){

  $response = array();

  $update = $this->conn->prepare("UPDATE general_settings set support_email=?, favicon_icon=?, logo_img=?, boy_defimg=?, girl_defimg=?");
  $update->bind_param("sssss", $semail, $favimage, $logoimage, $fboyimg, $fgirlimg);

  $result = $update->execute();

  $update->close();
  if($result){
    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }

  return $response;
}

public function updateFooter($instaurl, $fburl, $linkedinurl, $tweeturl, $youtubeurl, $dribbleurl, $googlepurl, $blogurl, $fustitle, $fusdesc, $cptext){

  $response = array();

  $update = $this->conn->prepare("UPDATE general_settings set blog_url=?, facebook_url=?, twitter_url=?, youtub_url=?, instagram_url=?, linkedinurl=?, dribbleurl=?, googlepurl=?, follow_title=?, follow_desc=?, copyright_text=?");
  $update->bind_param("sssssssssss", $blogurl, $fburl, $tweeturl, $youtubeurl, $instaurl, $linkedinurl, $dribbleurl, $googlepurl, $fustitle, $fusdesc, $cptext);


  $result = $update->execute();

  $update->close();
  if($result){
    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }

  return $response;
}

public function getUserById($id){
  $sitinfo = $this->conn->prepare("SELECT * from users where id=?");
  $sitinfo->bind_param("i", $id);
  $res = $sitinfo->execute();
  $sitinfo->bind_result($id,$first_name,$last_name,$username,$weight,$height,$gender, $age, $email, $phone, $sexual, $skype, $whatsapp, $viber, $wechat, $location, $service_location, $aboutme, $videos, $highlight, $oldpt, $password, $status, $admin_status, $pausetime, $walletamount, $api_key, $images, $verificationcode);
  $sitinfo->fetch();
  if($res){
    $user['id'] = $id;
    $user['name'] = $first_name.' '.$last_name;
    $user['first_name']=$first_name;
    $user['last_name']=$last_name;
    $user['username'] = $username;
    $user['weight'] = $weight;
    $user['height'] = $height;
    $user['age'] = $age;
    $user['gender']=$gender;
    $user['email'] = $email;
    $pt = explode('+91',$phone);
    if(isset($pt[1])){
      $user['phone'] = $pt[1];
    } else{
      $user['phone'] = $phone;
    }

    $user['sexual'] = $sexual;
    $user['skype'] = $skype;
    $user['whatsapp'] = $whatsapp;
    $user['viber'] = $viber;
    $user['wechat'] = $wechat;
    $user['location'] = $location;
    $user['service_location'] = $service_location;
    $user['aboutme'] = $aboutme;
    $user['highlight'] = $highlight;
    $user['walletamount'] = $walletamount;
    $user['status'] = $status;
    $user['pausetime'] = $pausetime;
    $user['profile_pic'] =unserialize($images);
    $user['videos'] =unserialize($videos);

    $user['stat'] = 1;
  } else{
    $user['stat'] = 0;
  }
  $sitinfo->close();

  return $user;
}

public function getptestimonials($id='')
{
  # code...
  $testimonialdata = $this->conn->prepare("SELECT * from testimonials WHERE userid= ? ");
  $testimonialdata->bind_param("i", $id);
  $res = $testimonialdata->execute();
  $testimonialdata->bind_result($id,$userid, $user_number,$nickname, $comment);

  $response['testimonials'] = array();
  if($res){
    while ($testimonialdata->fetch()){
      $testimonial['id'] = $id;
      $testimonial['userid'] = $userid;
      $testimonial['nickname'] = $nickname;
      $testimonial['comment'] = $comment;
      array_push($response['testimonials'], $testimonial);
    }
      $response['stat'] = 1;
    }else{
      $response['stat'] = 0;
    }
    $testimonialdata->close();
    return $response;
}
public function searchTestonomy($id, $number)
{
  # code...
  $testimonialdata = $this->conn->prepare("SELECT * from testimonials WHERE userid=".$id." and user_number='".$number."' ");
  // $testimonialdata->bind_param("is", $id, $number);
  $res = $testimonialdata->execute();
  $testimonialdata->bind_result($id,$userid, $user_number,$nickname, $comment);

  $response['testimonials'] = array();
  if($res){
    while ($testimonialdata->fetch()){
      $testimonial['id'] = $id;
      $testimonial['userid'] = $userid;
      $testimonial['nickname'] = $nickname;
      $testimonial['comment'] = $comment;
      array_push($response['testimonials'], $testimonial);
    }
      $response['stat'] = 1;
    }else{
      $response['stat'] = 0;
    }
    $testimonialdata->close();
    return $response;
}

public function getcomments($id='')
{
  # code...
   $comments = $this->conn->prepare("SELECT comments.*,users.first_name,users.last_name,users.username,users.images FROM comments JOIN users ON comments.user_id = users.id WHERE girl_id=?");
      $comments->bind_param("i",$id);
      $result=$comments->execute();
      $comments->bind_result($id,$girl_id,$user_id,$date,$description,$first_name,$last_name,$username,$images);
      $response = array();
      if($result)
      {
        while ($comments->fetch()){
      $cmt['id'] = $id;
      $cmt['user_id'] = $user_id;
      $cmt['girl_id'] = $girl_id;
      $cmt['date'] = $date;
      $cmt['description'] = $description;
      $cmt['first_name'] = $first_name;
      $cmt['last_name'] = $last_name;
      $cmt['username'] = $username;
      $cmt['images'] = unserialize($images);
      array_push($response, $cmt);
    }
      }
      $comments->close();
      if($response)
      {
          return $response;
      }
      else
      {
        return null;
      }
}

public function getAllUsers(){
  $sitinfo = $this->conn->prepare("SELECT id, first_name, last_name from users ");
  //$sitinfo->bind_param("i", $id);
  $res = $sitinfo->execute();
  $sitinfo->bind_result($id, $firstname, $lastname);

  $response['users'] = array();
  if($res){
    while ($sitinfo->fetch()){
      $user['id'] = $id;
      $user['name'] = $firstname." ".$lastname;

      array_push($response['users'], $user);
    }

    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();

  return $response;
}

public function getCmsPage($id){
  $sitinfo = $this->conn->prepare("SELECT * from cms_pages where id=?");
  $sitinfo->bind_param("i", $id);
  $res = $sitinfo->execute();
  $sitinfo->bind_result($id, $title, $handle, $bannerimage, $headerimg, $postimg, $desc, $htitle, $ftdesc, $location, $callus, $seotitle, $seometa, $seokw, $seoana, $seodesc, $disstat);
  $sitinfo->fetch();
  if($res){
    $response['id'] = $id;
    $response['title'] = $title;
    $response['handle'] = $handle;
    $response['bannerimg'] = $bannerimage;
    $response['headerimg'] = $headerimg;
    $response['pimg'] = $postimg;
    $response['desc'] = $desc;
    $response['htitle'] = $htitle;
    $response['ftdesc'] = $ftdesc;
    $response['location'] = $location;
    $response['callus'] = $callus;
    $response['seotitle'] = $seotitle;
    $response['seokey'] = $seokw;
    $response['seodesc'] = $seodesc;

    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();

  return $response;
}

public function checkUserWithId($email, $id){
  $stmt = $this->conn->prepare("SELECT id from users WHERE  email = ? and id != ?");
  $stmt->bind_param("ss", $email,$id);
  $stmt->execute();
  $stmt->store_result();
  $num_rows = $stmt->num_rows;
  $stmt->close();
  return $num_rows > 0;
}

public function updateUserById($firstname,$lastname ,$email,$phone, $age,$sex,$location,$service,$aboutme,$status,$activation,$password,$profile,$gender, $id){
  require_once 'PassHash.php';
  $response = array();

  if ( !$this->checkUserWithId($email, $id) ) {
    if($gender == 'boy'){
      $fg = 'Male';
    } else{
      $fg = 'Female';
    }
    if(!empty($password)){

      $new_passhash = PassHash::hash($password);
      $update = $this->conn->prepare("UPDATE users set first_name=?, last_name=?, password=?, email=?, gender=?, phone=?, location=?, service_location=?, sexual=?, age=?, aboutme=?, status=?, images=? where id=?");
      $update->bind_param("ssssssssssssss", $firstname, $lastname, $new_passhash, $email, $fg, $phone, $location, $service, $sex, $age, $aboutme, $status, $profile, $id);

    } else{

      $update = $this->conn->prepare("UPDATE users set first_name=?, last_name=?, email=?, gender=?, phone=?, location=?, service_location=?, sexual=?, age=?, aboutme=?, status=?, images=? where id=?");
      $update->bind_param("sssssssssssss", $firstname, $lastname, $email, $fg, $phone, $location, $service, $sex, $age, $aboutme, $status, $profile, $id);

    }

    $result = $update->execute();

    $update->close();

    if($result){

      $response['stat'] = 1;

    } else{

      $response['stat'] = 0;

    }

  } else{
    $response['stat'] = 2;
  }

  return $response;
}
  /**
     * Updating task
     * @param String $task_id id of the task
     * @param String $task task text
     * @param String $status task status
     */
    public function updateUser($userid,$firstname,$lastname,$username,$weight,$height,$gender,$age,$email,$phone,$sex,$skype,$whatsapp,$viber,$wechat,$location,$service,$aboutme,$highlight,$pausetime,$profile, $videos) {
      $aboutme=addslashes($aboutme);
      $height = addslashes($height);
        $stmt = $this->conn->prepare("UPDATE users SET first_name='".$firstname."',last_name='".$lastname."',username='".$username."',weight='".$weight."',height='".$height."',gender='".$gender."', age=".$age.",email='".$email."', phone='".$phone."',sexual='".$sex."',skype='".$skype."',whatsapp='".$whatsapp."',viber='".$viber."',wechat='".$wechat."',location='".$location."',service_location='".$service."',aboutme='".$aboutme."', videos='".$videos."', highlight='".$highlight."',pause_time='".$pausetime."',images='".$profile."' WHERE id= ? ");
        // videos='".$videos."',
        $stmt->bind_param('d',$userid);

    $result = $stmt->execute();

        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        if($result){
          $retval = 1;
        } else{
          $retval = 0;
        }
        return $retval;
    }

    public function getFrontPackages($gender){
      if($gender == 'Female'){
        $for = 2;
      } else{
        $for = 1;
      }

      $sitinfo = $this->conn->prepare("SELECT * from packages where package_for=?");
      $sitinfo->bind_param("i", $for);
      $res = $sitinfo->execute();
      $sitinfo->bind_result($id, $name, $desc, $bonus, $price, $pfor);

      if($res){
        $response['packages'] = array();

        while ($sitinfo->fetch()){

          $user['id'] = $id;
          $user['name'] = $name;
          $user['desc'] = $desc;
          $user['bonus'] = $bonus;
          $user['price'] = $price;

          array_push($response['packages'], $user);

      }

        $response['stat'] = 1;
      } else{
        $response['stat'] = 0;
      }
      $sitinfo->close();
      return $response;
    }

public function updatePage($id, $title, $handle, $desc, $seotitle, $seokey, $seodesc, $bannerimg, $postimg, $headerimg, $ftdesc, $htitle, $location, $callus){
  $response = array();

    $update = $this->conn->prepare("UPDATE cms_pages set title=?, handle=?, banner_image=?, headerimg=?, postimg=?, detail=?, htitle=?, ft_detail=?, location=?, call_us=?, seo_title=?, seo_keywords=?, seo_detail=? where id=?");
    $update->bind_param("ssssssssssssss", $title, $handle, $bannerimg, $headerimg, $postimg, $desc, $htitle, $ftdesc, $location, $callus, $seotitle, $seokey, $seodesc, $id);


    $result = $update->execute();

    $update->close();

    if($result){

      $response['stat'] = 1;

    } else{

      $response['stat'] = 0;

    }

  return $response;
}

public function addNewPackage($packagefor, $packagename, $desc, $bonus, $price){
  $response = array();

    $update = $this->conn->prepare("INSERT INTO packages (name, package_desc, bonus, price, package_for) values(?, ?, ?, ?, ?)");
    $update->bind_param("sssss", $packagename, $desc, $bonus, $price, $packagefor);

    $result = $update->execute();

    $update->close();

    if($result){

      $response['stat'] = 1;

    } else{

      $response['stat'] = 0;

    }

  return $response;
}

public function getAllPackages($sort, $sfield, $page, $perpage){
  $gettotal = $this->conn->prepare("SELECT * from packages");
  $gettotal->execute();
  $gettotal->store_result();
  $totalusers = $gettotal->num_rows;
  $gettotal->close();


  $start = ($page-1)*$perpage;

  $sitinfo = $this->conn->prepare("SELECT * from packages order by ".$sfield." ".$sort." limit ?,?");
  $sitinfo->bind_param("ii", $start, $perpage);
  $res = $sitinfo->execute();
  $sitinfo->bind_result($id, $name, $desc, $bonus, $price, $pfor);
  $response['users'] = array();
  if($res){
    $uc = 0;
    while ($sitinfo->fetch()){
      $uc++;

    $user['srno'] = $uc;
    $user['id'] = $id;
    $user['name'] = $name;
    $user['bonus'] = $bonus;
    $user['price'] = $price;
    $user['pfor'] = $pfor;

    array_push($response['users'], $user);
  }

    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();
  $response['total'] = $totalusers;
  return $response;
}

public function deletePackages($ids=array()){
  $idsare = implode(',',$ids);
  $delete = $this->conn->prepare("DELETE from packages where id in (".$idsare.")");
  //$delete->bind_param($idsare);

  $res = $delete->execute();
  $delete->close();

  if($res){
    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
}

public function getPackage($id){
  $sitinfo = $this->conn->prepare("SELECT * from packages where id=?");
  $sitinfo->bind_param("i", $id);
  $res = $sitinfo->execute();
  $sitinfo->bind_result($id, $name, $desc, $bonus, $price, $pfor);
  $sitinfo->fetch();
  if($res){
    $response['id'] = $id;
    $response['packagename'] = $name;
    $response['desc'] = $desc;
    $response['bonus'] = $bonus;
    $response['price'] = $price;
    $response['packagefor'] = $pfor;

    $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  $sitinfo->close();

  return $response;
}

public function updatePackage($id, $packagefor, $packagename, $desc, $bonus, $price){
  $response = array();

    $update = $this->conn->prepare("UPDATE packages set name=?, package_desc=?, bonus=?, price=?, package_for=? where id=?");
    $update->bind_param("ssssss", $packagename, $desc, $bonus, $price, $packagefor, $id);

    $result = $update->execute();

    $update->close();

    if($result){

      $response['stat'] = 1;

    } else{

      $response['stat'] = 0;

    }

  return $response;
}

// Front side data
 public function createUser($firstname,$lastname ,$email,$phone, $age,$sex,$location,$service,$aboutme,$status,$activation,$password,$profile, $gender, $adminadd) {
        require_once 'PassHash.php';
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);

            // Generating API key
            $api_key = $this->generateApiKey();
            $verification_code = rand(1000, 9999);
            $phonetemp = preg_replace('/\D+/', '', $phone);
            $phonesend='+91'.$phonetemp;
            $phone = '+91'.$phone;
            $phoneMessage = "Your Dating website verification code is : " . $verification_code;
            if(!$adminadd){
              $resp=$this->sendMessage($phonesend,$phoneMessage);
            }

            // insert query
            $adminstat = 1;
            $pt = 1;
            $stmt = $this->conn->prepare("INSERT INTO users (gender, first_name,last_name,email,phone, location,service_location,sexual,age,aboutme,password,status, admin_status, pause_time, api_key,verificationcode,images) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param("ssssssssdsssddsds", $gender, $firstname, $lastname, $email, $phone, $location, $service, $sex, $age, $aboutme, $password_hash, $status, $adminstat, $pt, $activation, $verification_code, $profile);
            $result = $stmt->execute();
            $stmt->close();

            // Check for successful insertion
            if($adminadd){
              if ($result) {
                  // User successfully inserted

                  return USER_CREATED_SUCCESSFULLY;
              } else {
                  // Failed to create user

                  return USER_CREATE_FAILED;
              }
            } else{
              if ($result & $resp) {
                  // User successfully inserted
                  $retresp['status'] = USER_CREATED_SUCCESSFULLY;
                  $retresp['user'] = array('firstname'=>$firstname, 'email'=>$email);
                  return $retresp;

              } else {
                  // Failed to create user
                  $retresp['status'] = USER_CREATE_FAILED;
                  return $retresp;
              }
            }

        } else {
            // User with same email already existed in the db
            $retresp['status'] = USER_ALREADY_EXISTED;
            return $retresp;
        }
        return $response;
    }

     /**
     * Checking user login
     * @param String $email User login email id
     * @param String $password User login password
     * @return boolean User login status success/fail
     */
public function sendMessage($phone, $message) {

   include_once('../twilio-php-master/Services/Twilio.php');
   $account_sid = 'AC7cd943f372b6a1872eace265af042ee8';
   $auth_token = '86ea649d49f34168f44cd509295451f3';

   $client = new Services_Twilio($account_sid, $auth_token);

   try {
      $message_tw = $client->account->messages->create(array(
          'To' => $phone,
          'From' => "+61418655602",
          'Body' => $message,
      ));
   } catch (Exception $e) {
      return false;
   }

   return true;
}
public function insertverificationcode ($userid){

         /* $stmt = $this->conn->prepare("UPDATE users SET password=? WHERE id=?");
          $stmt->bind_param('ss',$password_hash,$userid);
          $stmt->execute();
          $num_affected_rows = $stmt->affected_rows;
          $stmt->close();
          return $num_affected_rows > 0;*/
    }

public function checkLogin($email, $upassword) {
        // fetching user by email
        $stmt = $this->conn->prepare("SELECT password FROM users WHERE email = ?");

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->bind_result($password);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if (PassHash::check_password($password, $upassword)) {
                // User password is correct
                return TRUE;
            } else {
                // user password is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
    }

    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT  id,first_name,last_name,email,phone,gender,location,service_location,sexual,age,aboutme,password,status,admin_status,api_key,images FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id,$first_name,$last_name,$email,$phone,$gender,$location,$service_location,$sexual,$age,$aboutme,$password,$status,$adminstatus,$api_key,$images);
            $stmt->fetch();
            $user = array();
            $user["id"] = $id;
            $user["first_name"] = $first_name;
            $user["last_name"] = $last_name;
            $user["email"] =$email;
            $user["phone"] = $phone;
            $user['gender']=$gender;
            $user["location"] = $location;
            $user["service_location"] = $service_location;
            $user["sexual"] = $sexual;
            $user["age"] = $age;
            $user["aboutme"] = $aboutme;
            $user["password"] = $password;
            $user["api_key"] = $api_key;
            $user["status"]=$status;
            $user['adminstat'] = $adminstatus;
            $user["images"] = unserialize($images);

            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }

public function resendcode($email='')
{
  # code...
   $stmt = $this->conn->prepare("SELECT  id,email,phone,status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id,$email,$phone,$status);
            $stmt->fetch();
            $user = array();
            $user["id"] = $id;
            $user["email"] =$email;
            $user["phone"] = $phone;

            $user["status"]=$status;
            $verification_code = rand(1000, 9999);
            $phone = $user["phone"];

            $phoneMessage = "Your Dating website verification code is : " . $verification_code;
            $resp=$this->sendMessage($phone,$phoneMessage);
            $user["sendmessage"]=$resp;
            $user["verification_code"]=$verification_code;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
}

public function updatecode($id='',$code='')
{
  # code...
   $response = array();

  $update = $this->conn->prepare("UPDATE users set verificationcode=? where id=?");
  $update->bind_param("ss", $code,$id);
  $result = $update->execute();

  $update->close();

  if($result){
     $response['stat'] = 1;
  } else{
    $response['stat'] = 0;
  }
  return $response;
}
    /**
     * Fetching user by email
     * @param String $email User email id
     */
    public function getverify($email,$textverify) {
        $stmt = $this->conn->prepare("SELECT  id,first_name,last_name,email,phone,location,service_location,sexual,gender,age,aboutme,password,status,api_key,images FROM users WHERE email = ? AND verificationcode = ?");
        $stmt->bind_param("ss", $email,$textverify);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id,$first_name,$last_name,$email,$phone,$location,$service_location,$sexual, $gender,$age,$aboutme,$password,$status,$api_key,$images);
            $stmt->fetch();
            $user = array();

            $user["id"] = $id;
            //echo $id;
            $user["first_name"] = $first_name;
            $user["last_name"] = $last_name;
            $user["email"] =$email;
            $user["phone"] = $phone;
            $user["location"] = $location;
            $user["service_location"] = $service_location;
            $user["sexual"] = $sexual;
            $user['gender'] = $gender;
            $user["age"] = $age;
            $user["aboutme"] = $aboutme;
            $user["password"] = $password;
            $user["api_key"] = $api_key;
            $user["images"] = $images;

            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }
    public function updatepassword ($userid,$password){
           require_once 'PassHash.php';
          // Generating password hash
          $password_hash = PassHash::hash($password);

          $stmt = $this->conn->prepare("UPDATE users SET password=? WHERE id=?");
          $stmt->bind_param('ss',$password_hash,$userid);
          $stmt->execute();
          $num_affected_rows = $stmt->affected_rows;
          $stmt->close();
          return $num_affected_rows > 0;
    }


    public function addNewTestinomy($girlid, $boynumber, $name, $testinomy){
      $response = array();

        $update = $this->conn->prepare("INSERT INTO testimonials (userid, user_number, nickname, comment) values(?, ?, ?, ?)");
        $update->bind_param("ssss", $girlid, $boynumber, $name, $testinomy);

        $result = $update->execute();

        $update->close();

        if($result){

          $response['stat'] = 1;

        } else{

          $response['stat'] = 0;

        }

      return $response;
    }

    public function updatePayment($userid, $amount, $type, $timedone, $highlight){
      $response = array();

        $update = $this->conn->prepare("INSERT INTO transactions (amount, type, to_id, transaction_time) values(?, ?, ?, ?)");
        $update->bind_param("diss", $amount, $type, $userid, $timedone);

        $result = $update->execute();

        $update->close();

        if($result){

          $stmt = $this->conn->prepare("UPDATE users set wallet_amount=wallet_amount+".$amount.", highlight=".$highlight." where id=?");
          $stmt->bind_param("i",$userid);
          $result2 = $stmt->execute();
          $stmt->close();

          $getwallet = $this->conn->prepare("SELECT  wallet_amount FROM users WHERE id = ?");
          $getwallet->bind_param("i", $userid);
          if ($getwallet->execute()) {
              // $user = $stmt->get_result()->fetch_assoc();
              $getwallet->bind_result($wamount);
              $getwallet->fetch();

              $response['wallet'] = $wamount;

              $getwallet->close();
          }

          if($result2){
            $response['stat'] = 1;
          } else{
            $response['stat'] = 0;
          }

        } else{

          $response['stat'] = 0;

        }

      return $response;
    }

     //end dating website

     public function createSocialUser($firstname,$email='',$socialid,$phone,$status) {
        require_once 'PassHash.php';
        $response = array();
$name = explode(" ",$firstname);
$fname = $name[0];
$lname = '';
if(!empty($name[1])){
  $lname = $name[1];
}
        // First check if user already existed in db
        if (!$this->isUserExists($socialid)) {
            // Generating API key
            $api_key = $this->generateApiKey();

            // insert query
            $stmt = $this->conn->prepare("INSERT INTO users (first_name,last_name,email,api_key,phone,status) values(?,?,?,?,?,?)");
            $stmt->bind_param("ssssss", $fname,$lname,$email,$socialid,$phone,$status);

            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                $retar = array();
                $id = $this->getUserId($socialid);
                $retar['id'] = $id;
                $user = $this->getUser($id);
                $retar['apikey'] = $user['apikey'];
                $retar['stat'] = USER_CREATED_SUCCESSFULLY;
                return $retar;
            } else {
                // Failed to create user
                $response['stat'] = USER_CREATE_FAILED;
                return $response;
            }
        } else {
            // User with same email already existed in the db
            $response['stat'] = USER_ALREADY_EXISTED;
            return $response;
        }
        return $response;
    }

    public function loginSocial($firstname,$email='',$socialid,$phone,$status) {
       require_once 'PassHash.php';
       $response = array();

       // First check if user already existed in db
       if ($this->isUserExists($socialid)) {
               // User successfully inserted
               $retar = array();
               $id = $this->getUserId($socialid);
               $retar['id'] = $id;
               $user = $this->getUser($id);
               $retar['apikey'] = $user['apikey'];
               $retar['stat'] = USER_CREATED_SUCCESSFULLY;
               return $retar;

       } else {
           // User with same email already existed in the db
           $response['stat'] = USER_ALREADY_EXISTED;
           return $response;
       }
       return $response;
   }




    public function submitContact($firstname,$lastname ,$email,$phone) {

        // First check if user already existed in db
            // insert query
            $stmt = $this->conn->prepare("INSERT INTO leads (user_name,user_email,lead_type) values(?,?,1)");
            $username=$firstname.$lastname;
            $stmt->bind_param("ss",$username ,$email);
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
    }
    public function submitsellContact($firstname,$lastname ,$email,$phone) {

        // First check if user already existed in db
            // insert query
            $stmt = $this->conn->prepare("INSERT INTO leads (user_name,user_email,lead_type) values(?,?,2)");
            $username=$firstname.$lastname;
            $stmt->bind_param("ss",$username ,$email);
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
    }

    public function submitmorehome($firstname,$lastname ,$email,$phone) {

        // First check if user already existed in db
            // insert query
            $stmt = $this->conn->prepare("INSERT INTO leads (user_name,user_email,lead_type) values(?,?,3)");
            $username=$firstname.$lastname;
            $stmt->bind_param("ss",$username ,$email);
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
    }

    public function submitreqshowing($firstname,$lastname ,$email,$phone) {

        // First check if user already existed in db
            // insert query
            $stmt = $this->conn->prepare("INSERT INTO leads (user_name,user_email,lead_type) values(?,?,6)");
            $username=$firstname.$lastname;
            $stmt->bind_param("ss",$username ,$email);
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
    }

    public function addSaveSearch($string,$emailfreq ,$title,$dateadd,$userid,$objval) {
      $retar = array();
      $csearch = $this->conn->prepare("SELECT id from saved_searches WHERE title = ?");
      $csearch->bind_param("s", $title);
      $csearch->execute();
      $csearch->store_result();
      $num_rows = $csearch->num_rows;
      $csearch->close();
      if($num_rows > 0){
        $retar['val']=5;
        return $retar;
      } else{
            // insert query
            $stmt = $this->conn->prepare("INSERT INTO saved_searches (user_id,search_url,title,efrequecy,modelobj,dateadded) values(?,?,?,?,?,?)");
            $stmt->bind_param("ississ",$userid,$string ,$title,$emailfreq,$objval,$dateadd);
            $result = $stmt->execute();
            $id = $stmt->insert_id;
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                $retar['val']=USER_CREATED_SUCCESSFULLY;
                $retar['id']=$id;
                return $retar;
            } else {
                // Failed to create user
                $retar['val']=USER_CREATE_FAILED;
                return $retar;
            }
          }
    }

    public function editSaveSearch($string,$emailfreq ,$title,$dateadd,$userid,$ssid,$objval) {
      $retar = array();
            // insert query
            $stmt = $this->conn->prepare("UPDATE saved_searches set user_id=?, search_url=?, title=?, efrequecy=?, modelobj=? where id=?");
            $stmt->bind_param("issisi",$userid,$string ,$title,$emailfreq,$objval,$ssid);
            $result = $stmt->execute();
            $id = $stmt->insert_id;
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                $retar['val']=USER_CREATED_SUCCESSFULLY;
                $retar['id']=$id;
                return $retar;
            } else {
                // Failed to create user
                $retar['val']=USER_CREATE_FAILED;
                return $retar;
            }

    }

    public function getSavedSearch($id) {
   $stmt = $this->conn->prepare("SELECT * FROM saved_searches WHERE id = ?");
       $stmt->bind_param("d", $id);
       if ($stmt->execute()) {
           // $user = $stmt->get_result()->fetch_assoc();
           $stmt->bind_result($id,$first_name, $last_name, $email,$profile_pic,$jval,$agent_bio);
           $stmt->fetch();
           $agent = array();
           $agent["id"] = $id;
           $agent["user_id"] = $first_name;
           $agent["search_url"] = $last_name;
           $agent["title"] =$email;
           $agent["efreq"] = $profile_pic;
           $agent["mval"] = json_decode(unserialize($jval));
          //  var_dump(json_decode(unserialize($jval)));
          //  exit();
           $agent["dadded"] = $agent_bio;

           $stmt->close();
           return $agent;
       } else {
           return NULL;
       }
   }

   public function getAllsSearch($userid) {
   $stmt = $this->conn->prepare("SELECT * from saved_searches where user_id=? order by id desc");
   /* select count(*) msgcount, id from messages where parent_id >0 GROUP BY parent_id ORDER BY lastreply_date DESC */
   $stmt->bind_param("d", $userid);
       if ($stmt->execute()) {
            $messagelist = $stmt->get_result();
           $stmt->close();
           return $messagelist;
       } else {
           return NULL;
       }
   }

   public function deleteSavedSearch($subject_id) {
       $stmt = $this->conn->prepare("delete from saved_searches WHERE id =?");
       $stmt->bind_param("i", $subject_id);
       $stmt->execute();
       $num_affected_rows = $stmt->affected_rows;
       $stmt->close();
       return $num_affected_rows > 0;
   }

    public function inpersonAssessment($firstname,$lastname ,$email,$phone) {
        // First check if user already existed in db
            // insert query
            $stmt = $this->conn->prepare("INSERT INTO leads (user_name,user_email,lead_type) values(?,?,5)");
            $username=$firstname.$lastname;
            $stmt->bind_param("ss",$username ,$email);
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
    }

    public function submitFinanceLead($firstname,$lastname ,$email,$phone) {

        // First check if user already existed in db
            // insert query
            $stmt = $this->conn->prepare("INSERT INTO leads (user_name,user_email,lead_type) values(?,?,4)");
            $username=$firstname.$lastname;
            $stmt->bind_param("ss",$username ,$email);
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // lead successfully inserted
                return FINANCE_LEAD_ADDED;
            } else {
                // Failed to add lead
                return FINANCE_LEAD_FAILED;
            }
    }
 /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    public function verifypassword($encrypt){
    $stmt = $this->conn->prepare("SELECT password FROM users where md5(1290*3+id) = ? ");
        $stmt->bind_param("s", $encrypt);
        $stmt->execute();
        $stmt->bind_result($password);
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }


    public function verifyEmail($email, $uactivation)
    {
     // fetching user by email
        $stmt = $this->conn->prepare("SELECT email,activation FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);

        $stmt->execute();

        $stmt->bind_result($email,$activation);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if ($activation == $uactivation) {
        // User email is correct
          $stmt = $this->conn->prepare("UPDATE users SET status=1 WHERE email=?");
          $stmt->bind_param('s',$email);
          $stmt->execute();
          $num_affected_rows = $stmt->affected_rows;
          $stmt->close();
          return $num_affected_rows > 0;
                // User email is correct
                return TRUE;
            } else {
                // user email is incorrect
                return FALSE;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return FALSE;
        }
  }


    /**
     * Checking for duplicate user by email address
     * @param String $email email to check in db
     * @return boolean
     */
    private function isUserExists($email) {
        $stmt = $this->conn->prepare("SELECT id from users WHERE  email = ? or api_key = ?");
        $stmt->bind_param("ss", $email,$email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }



    public function addtofavorite($userid,$propid,$image,$address,$link){

            $stmt = $this->conn->prepare("INSERT INTO favorite_properties ( userid,propertyid,image, address,link) values(?,?,?,?,?)");
                $stmt->bind_param("sssss",$userid ,$propid,$image, $address,$link);
                $result = $stmt->execute();
                $stmt->close();
                // Check for successful insertion
                if ($result) {
                    // User successfully inserted
                    return USER_CREATED_SUCCESSFULLY;
                } else {
                    // Failed to create user
                    return USER_CREATE_FAILED;
                }
        }
    /**
     * Fetching user api key
     * @param String $user_id user id primary key in user table
     */
    public function getApiKeyById($user_id) {
        $stmt = $this->conn->prepare("SELECT api_key FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            // TODO
            $stmt->bind_result($api_key);
            $stmt->close();
            return $api_key;
        } else {
            return NULL;
        }
    }

    /**
     * Fetching user id by api key
     * @param String $api_key user api key
     */
    public function getUserId($api_key) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
            // TODO
            // $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return $user_id;
        } else {
            return NULL;
        }
    }

    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key
     * @param String $api_key user api key
     * @return boolean
     */
    public function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT id from users WHERE api_key = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();
        return $num_rows > 0;
    }

    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

     /**
     * Fetching page content
     * @param String $p_id id of the user
     */
    public function getStaticPage($handle) {
    $stmt = $this->conn->prepare("SELECT id,title,handle,banner_image,detail,seo_title,seo_meta,seo_keywords,seo_analytics,seo_detail FROM cms_pages WHERE id = ? or handle=?");
        $stmt->bind_param("ss", $handle,$handle);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id,$title, $handle, $banner_image, $detail, $seo_title, $seo_meta, $seo_keywords, $seo_analytics, $seo_detail);
            $stmt->fetch();
            $pages = array();
            $pages["id"] = $id;
            $pages["title"] = $title;
            $pages["handle"] = $handle;
            $pages["banner_image"] =$banner_image;
            $pages["detail"] = $detail;

            $pages["seo_title"] = $seo_title;
            $pages["seo_meta"] = $seo_meta;
            $pages["seo_keywords"] = $seo_keywords;
            $pages["seo_analytics"] = $seo_analytics;
            $pages["seo_detail"] = $seo_detail;
            $stmt->close();
            return $pages;
        } else {
            return NULL;
        }
    }

     public function getAgentDetail($agent_id) {
    $stmt = $this->conn->prepare("SELECT id,first_name,last_name,email,profile_pic,agent_bio,website_url,cell_phone,introvideo,handle,options,shortcode FROM agents WHERE id = ?");
        $stmt->bind_param("d", $agent_id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id,$first_name, $last_name, $email,$profile_pic,$agent_bio,$website_url,$cell_phone,$introvideo,$handle,$options,$shortcode);
            $stmt->fetch();
            $agent = array();
            $agent["id"] = $id;
            $agent["first_name"] = $first_name;
            $agent["last_name"] = $last_name;
            $agent["email"] =$email;
            $agent["profile_pic"] = $profile_pic;
            $agent["agent_bio"] = $agent_bio;
            $agent["website_url"] = $website_url;
            $agent["cell_phone"] = $cell_phone;
            $agent["introvideo"] = $introvideo;
            $agent["handle"] = $handle;
            $agent["options"] = unserialize($options);
            $agent["shortcode"] = unserialize($shortcode);
            $stmt->close();
            return $agent;
        } else {
            return NULL;
        }
    }

    public function getAgentByHandle($handle) {
   $stmt = $this->conn->prepare("SELECT id,first_name,last_name,email,profile_pic,agent_bio,website_url,cell_phone,introvideo,handle,options,shortcode FROM agents WHERE handle = ?");

       $stmt->bind_param("s", $handle);
       if ($stmt->execute()) {
           // $user = $stmt->get_result()->fetch_assoc();
           $stmt->bind_result($id,$first_name, $last_name, $email,$profile_pic,$agent_bio,$website_url,$cell_phone,$introvideo,$handle,$options,$shortcode);
           $stmt->fetch();
           $agent = array();
           $agent["id"] = $id;
           $agent["first_name"] = $first_name;
           $agent["last_name"] = $last_name;
           $agent["email"] =$email;
           $agent["profile_pic"] = $profile_pic;
           $agent["agent_bio"] = $agent_bio;
           $agent["website_url"] = $website_url;
           $agent["cell_phone"] = $cell_phone;
           $agent["introvideo"] = $introvideo;
           $agent["handle"] = $handle;
           $agent["options"] = unserialize($options);
           $agent["shortcode"] = unserialize($shortcode);
           $stmt->close();
           return $agent;
       } else {
           return NULL;
       }
   }


   public function getRecent($handle) {
  $stmt = $this->conn->prepare("SELECT * FROM recent_listings WHERE handle = ?");

      $stmt->bind_param("s", $handle);
      if ($stmt->execute()) {
          // $user = $stmt->get_result()->fetch_assoc();
          $stmt->bind_result($id,$name, $youtubelink, $handle, $header, $address, $mlsno, $virtuallink, $pdf, $listprice, $sqft, $beds, $baths, $neighbourhood, $agentid, $shortcode, $image);
          $stmt->fetch();
          $agent = array();
          $agent["id"] = $id;
          $agent["name"] = $name;
          $agent["youtubelink"] = $youtubelink;
          $agent["handle"] =$handle;
          $agent["header"] = $header;
          $agent["address"] = $address;
          $agent["mlsno"] = $mlsno;
          $agent["virtuallink"] = $virtuallink;
          $agent["pdf"] = $pdf;
          $agent["listprice"] = $listprice;
          $agent["sqft"] = $sqft;
          $agent["beds"] = $beds;
          $agent["baths"] = $baths;
          $agent["neighbourhood"] = $neighbourhood;
          $agent["agent"] = $agentid;
          $agent["shortcode"] = unserialize($shortcode);
          $agent["imagedesc"] = unserialize($image);
          $stmt->close();
          //print_r($agent);exit();
          return $agent;

      } else {
          return NULL;
      }
  }

     /**
     * Fetching all user setting
     *
     */
     public function getallcmspages()
     {
    $stmt2 = $this->conn->prepare("SELECT title,handle FROM cms_pages WHERE display_stat=1 and sidebar=0");
    if ($stmt2->execute()) {
      $result=$stmt2->get_result();
      while ($cmspage = $result->fetch_assoc()) {
        $cmspages[]=$cmspage;
      }
      return $cmspages;
      $stmt2->close();
    }else {
       return NULL;
    }
  }
  //16832241_1455754304475945_2849531600654505919_n.jpg
  public function getTestimonials() {
    $stmt = $this->conn->prepare("SELECT * FROM testimonials");
        if ($stmt->execute()) {
             $testimonials = $stmt->get_result();
            $stmt->close();
            // var_dump($testimonials);
            // exit();
            return $testimonials;
        } else {
            return NULL;
        }
    }
    public function getsingletestimonials($tid) {
        $stmt = $this->conn->prepare("SELECT * FROM testimonials where id=?");
        $stmt->bind_param("d", $tid);
        if ($stmt->execute()) {
        $stmt->bind_result($id,$name, $image, $content);
          $stmt->fetch();
          $singletest = array();
          $singletest["id"] = $id;
          $singletest["name"] = $name;
          $singletest["image"] = $image;
          $singletest["content"] =trim(htmlspecialchars_decode(strip_tags($content)), '\"');
          $stmt->close();
           // var_dump($singletest);
            // exit();
            return $singletest;
        } else {
            return NULL;
        }
    }

  public function getRecentBlogs() {
    $stmt = $this->conn->prepare("SELECT `id`,`title`,`handle`,`image` FROM `blogposts` ORDER BY `blogposts`.`date_added` DESC LIMIT 0,6");
        if ($stmt->execute()) {
             $blogs = $stmt->get_result();
            $stmt->close();
            return $blogs;
        } else {
            return NULL;
        }
    }

    public function getAllSetting() {
    $stmt = $this->conn->prepare("SELECT * FROM front_settings");
        if ($stmt->execute()) {
             $setting = $stmt->get_result();
            $stmt->close();
            return $setting;
        } else {
            return NULL;
        }
    }

     public function getAllAgents($pno,$limit) {
       $page = $pno*$limit;
        $stmt = $this->conn->prepare("SELECT * FROM agents WHERE agent_status=0 and owner=0 limit ?,?");
        $stmt->bind_param("dd", $page,$limit);

        if ($stmt->execute()) {
             $agents = $stmt->get_result();
            $stmt->close();
            return $agents;
        } else {
            return NULL;
        }
    }

    public function getOwners() {
       $stmt = $this->conn->prepare("SELECT * FROM agents WHERE agent_status=0 and owner=1");

       if ($stmt->execute()) {
            $agents = $stmt->get_result();
           $stmt->close();
           return $agents;
       } else {
           return NULL;
       }
   }

   public function getSidePages() {
      $stmt = $this->conn->prepare("SELECT * FROM cms_pages WHERE sidebar=1");

      if ($stmt->execute()) {
           $agents = $stmt->get_result();
          $stmt->close();
          return $agents;
      } else {
          return NULL;
      }
  }

   public function getAgents() {
      $stmt = $this->conn->prepare("SELECT * FROM agents WHERE agent_status=0 and owner=0");

      if ($stmt->execute()) {
           $agents = $stmt->get_result();
          $stmt->close();
          return $agents;
      } else {
          return NULL;
      }
  }

  public function getHomeAgents() {

    $idstat = $this->conn->prepare("SELECT id FROM agents WHERE agent_status=0 and owner=0");
    $idstat->execute();
    $ids = $idstat->get_result();

    $agentid = array();

    while($id = $ids->fetch_assoc()){
      $agentid[] = $id['id'];
    }
    $idstat->close();

    $randomids = array_rand($agentid,4);
    $id1=$agentid[$randomids[0]];
    $id2=$agentid[$randomids[1]];
    $id3=$agentid[$randomids[2]];
    $id4=$agentid[$randomids[3]];
     $stmt = $this->conn->prepare("SELECT * FROM agents WHERE agent_status=0 and owner=0 and id in (?,?,?,?)");
$stmt->bind_param("ssss", $id1,$id2,$id3,$id4);
     if ($stmt->execute()) {
          $agents = $stmt->get_result();
         $stmt->close();
         return $agents;
     } else {
         return NULL;
     }
 }

     public function getAllListing() {
    $stmt = $this->conn->prepare("SELECT * FROM recent_listings");
        if ($stmt->execute()) {
             $listing = $stmt->get_result();
            $stmt->close();
            return $listing;
        } else {
            return NULL;
        }
    }
     public function getStaticBlocks() {
    $stmt = $this->conn->prepare("SELECT * FROM static_blocks ");
        if ($stmt->execute()) {
             $blocks = $stmt->get_result();
            $stmt->close();
            return $blocks;
        } else {
            return NULL;
        }
    }
    public function getAllMessages($userid) {
    $stmt = $this->conn->prepare("select messages.*,concat(users.first_name,' ',users.last_name) as senderuser,admins.user as senderadmin,concat(ruser.first_name,' ',ruser.last_name) as receiveruser,radmin.user as receiveradmin from messages left join users on users.id=messages.sender_id LEFT JOIN admins on messages.sender_admin_id=admins.id LEFT JOIN users as ruser on ruser.id=messages.receiver_id LEFT JOIN admins as radmin on radmin.id=messages.receiver_admin_id where messages.sender_id=? AND messages.parent_id=0 ORDER BY messages.lastreply_date DESC ");
    /* select count(*) msgcount, id from messages where parent_id >0 GROUP BY parent_id ORDER BY lastreply_date DESC */
    $stmt->bind_param("d", $userid);
        if ($stmt->execute()) {
             $messagelist = $stmt->get_result();
            $stmt->close();
            return $messagelist;
        } else {
            return NULL;
        }
    }
    public function getMessageDetail($sub_id) {
    $stmt = $this->conn->prepare("select messages.*,concat(users.first_name,' ',users.last_name) as senderuser,admins.user as senderadmin,concat(ruser.first_name,' ',ruser.last_name) as receiveruser,radmin.user as receiveradmin from messages left join users on users.id=messages.sender_id LEFT JOIN admins on messages.sender_admin_id=admins.id LEFT JOIN users as ruser on ruser.id=messages.receiver_id LEFT JOIN admins as radmin on radmin.id=messages.receiver_admin_id where messages.parent_id=? OR messages.id=? ORDER BY messages.id asc ");
        $stmt->bind_param("dd", $sub_id,$sub_id);
        if ($stmt->execute()) {
            $result=$stmt->get_result();
            $stmt->close();
            return $result;
        } else {
            return NULL;
        }
    }

     public function getAdminReply($sub_id) {
    $stmt = $this->conn->prepare("select messages.*,concat(users.first_name,' ',users.last_name) as senderuser,admins.user as senderadmin,concat(ruser.first_name,' ',ruser.last_name) as receiveruser,radmin.user as receiveradmin from messages left join users on users.id=messages.sender_id LEFT JOIN admins on messages.sender_admin_id=admins.id LEFT JOIN users as ruser on ruser.id=messages.receiver_id LEFT JOIN admins as radmin on radmin.id=messages.receiver_admin_id where (messages.parent_id=? OR messages.id=?) ORDER BY messages.id asc ");
    //select messages.*,concat(users.first_name,' ',users.last_name) as senderuser,admins.user as senderadmin,concat(ruser.first_name,' ',ruser.last_name) as receiveruser,radmin.user as receiveradmin from messages left join users on users.id=messages.sender_id LEFT JOIN admins on messages.sender_admin_id=admins.id LEFT JOIN users as ruser on ruser.id=messages.receiver_id LEFT JOIN admins as radmin on radmin.id=messages.receiver_admin_id where (messages.parent_id=? OR messages.id=?) and messages.sender_admin_id!=0 ORDER BY messages.id asc
        $stmt->bind_param("dd", $sub_id,$sub_id);
        if ($stmt->execute()) {
            $result=$stmt->get_result();
            $stmt->close();
            return $result;
        } else {
            return NULL;
        }
    }

      public function submitMessage($userid,$subject,$messagebody) {
            // insert query
             $datem = date("Y-m-d H:i:s");

            $stmt = $this->conn->prepare("insert into messages (sender_id, receiver_admin_id, subject,message,create_date,  lastreply_date) values(?,1,?,?,?,?)");
            $stmt->bind_param("issss",$userid,$subject,$messagebody, $datem,$datem);
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
    }
     public function submitReply($userid,$replydata,$subjectid,$msgsub){
            // insert query
             $datem = date("Y-m-d H:i:s");
            $stmt = $this->conn->prepare("insert into messages (sender_id, receiver_admin_id, subject,message,parent_id,create_date,lastreply_date) values(?,1,?,?,?,?,?)");
            $stmt->bind_param("isssss",$userid,$msgsub,$replydata,$subjectid, $datem,$datem);
            $result = $stmt->execute();
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                return USER_CREATED_SUCCESSFULLY;
            } else {
                // Failed to create user
                return USER_CREATE_FAILED;
            }
    }



    /**
     * Fetching all user tasks
     * @param String $user_id id of the user
     */
    public function getUser($u_id) {
    $stmt = $this->conn->prepare("SELECT id,first_name,last_name,email,api_key,newsletter,phone,secondemail,cellphone,workphone,fax,streetaddress,city,state,zipcode,country,matchingsaved,concerningupdate FROM users WHERE id = ?");
        $stmt->bind_param("d", $u_id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id,$first_name, $last_name, $email,$apikey,$newsletter,$phone,$secondemail,$cellphone,$workphone,$fax,$streetaddress,$city,$state,$zipcode,$country,$matchingsaved,$concerningupdate);
            $stmt->fetch();
            $user = array();
            $user["id"] = $id;
            $user["firstName"] = $first_name;
            $user["lastName"] = $last_name;
            $user["email"] =$email;
            $user["secondemail"] = $secondemail;
            //~ $user["newsletter"] = $newsletter;
            $user["apikey"] = $apikey;
            $user["streetadd"] = $streetaddress;
            $user["city"] = $city;
            $user["state"] = $state;
            $user["zip"] = $zipcode;
            $user["country"] = $country;
            $user["phone"] = $phone;
            $user["cellphone"] = $cellphone;
            $user["workphone"] = $workphone;
            $user["fax"] = $fax;
            $user["matchingsaved"] = $matchingsaved;
            $user["concerningupdate"] = $concerningupdate;
            $stmt->close();
            return $user;
        } else {
            return NULL;
        }
    }



    public function getFooterSign()
    {
    $stmt = $this->conn->prepare("SELECT * FROM front_settings");
        if ($stmt->execute()) {
             $setting = $stmt->get_result()->fetch_assoc();
             $agentid=$setting['agent_signature'];
             $stmt1 = $this->conn->prepare("SELECT email_sign FROM agents WHERE id = ?");
       $stmt1->bind_param("d", $agentid);
      if ($stmt1->execute()) {
        // $user = $stmt->get_result()->fetch_assoc();
        $stmt1->bind_result($email_sign);
        $stmt1->fetch();
        $email_sign = $email_sign;
        $stmt1->close();
         return $email_sign;
      }
      else
      {
        return null;
      }

        } else {
            return NULL;
        }
    //return 1;
  }

    /**
     * Deleting a task
     * @param String $task_id id of the task to delete
     */
    public function deleteSubject($subject_id) {
        $stmt = $this->conn->prepare("delete from messages WHERE id =? or parent_id=?");
        $stmt->bind_param("ii", $subject_id, $subject_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }

    public function removefavorite($user_id,$propid) {
        $stmt = $this->conn->prepare("delete from favorite_properties WHERE     userid =? AND propertyid=?");
        $stmt->bind_param("ii", $user_id, $propid);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        return $num_affected_rows > 0;
    }
      public function getallfav($userid) {
        $stmt = $this->conn->prepare("SELECT * FROM favorite_properties WHERE userid=?");
        $stmt->bind_param("d", $userid);
        if ($stmt->execute()) {
            $favs = $stmt->get_result();
            $stmt->close();
            return $favs;
          } else {
              return NULL;
          }
    }
}
?>
