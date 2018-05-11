<?php

/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables.
 *
 * @author
 *
 * @see URL Tutorial link
 */
class DbHandler
{
    private $conn;

    public function __construct()
    {
        require_once dirname(__FILE__).'/DbConnect.php';
        // opening db connection
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /* ------------- `users` table method ------------------ */

    /**
     * Creating new user.
     *
     * @param string $name     User full name
     * @param string $email    User login email id
     * @param string $password User login password
     */

    //dating website

    //messages
    public function checkchatroom($fromid, $toid)
    {
        $check = $this->conn->prepare('SELECT id from chat_rooms where (from_user_id=? and to_user_id=?) or (from_user_id=? and to_user_id=?)');
        $check->bind_param('iiii', $fromid, $toid, $toid, $fromid);

        $check->execute();
        $check->bind_result($chatid);

        $check->fetch();

        //$check->store_result();
        $checknr = $check->num_rows;
        $check->close();
        $chatroomid = 0;
        if ($chatid) {
            $chatroomid = $chatid;
        } else {
            $nowtime = date('Y-m-d H:i:s');
            $addchat = $this->conn->prepare('INSERT into chat_rooms (from_user_id, to_user_id, created_at) values(?, ?, ?)');

            $addchat->bind_param('iis', $fromid, $toid, $nowtime);

            $addchat->execute();

            $chatroomid = $addchat->insert_id;
            $addchat->close();
        }

        return $chatroomid;
    }

    public function getuserchats($id)
    {
        $getchats = $this->conn->prepare('SELECT chat_rooms.*,concat(fromu.first_name," ",fromu.last_name) as fromuser, concat(tou.first_name," ",tou.last_name) as touser, (case when messages.status=2 then SUBSTRING_INDEX(GROUP_CONCAT(messages.message ORDER BY messages.id desc), ",",1) else "" end) as lastmsg, (sum(case WHEN messages.status = 2 THEN 1 else 0 end)) as urm from chat_rooms join users as fromu on fromu.id=chat_rooms.from_user_id join users as tou on tou.id=chat_rooms.to_user_id left join messages on messages.chatroom_id=chat_rooms.id where chat_rooms.from_user_id=? or chat_rooms.to_user_id=? GROUP BY chat_rooms.id order by chat_rooms.updated_at desc');
        $getchats->bind_param('ii', $id, $id);
        // SELECT chat_rooms.*, SUBSTRING_INDEX(GROUP_CONCAT(messages.message ORDER BY messages.id desc), ',',1) as lastmsg FROM chat_rooms join messages on messages.chatroom_id=chat_rooms.id GROUP by chat_rooms.id
        $getchats->execute();

        $getchats->bind_result($cid, $fromid, $toid, $cdate, $updatetime, $fun, $tun, $lastmsg, $unread);
        $chatsare = array();
        while ($getchats->fetch()) {
            if ($fromid == $id) {
                $tousername = $tun;
                $nfrom = $fromid;
                $nto = $toid;
            } else {
                $tousername = $fun;
                $nfrom = $toid;
                $nto = $fromid;
            }
            if ($updatetime > 0) {
                $finaldt = date('d-m-Y h:i A', strtotime($updatetime));
            } else {
                $finaldt = date('d-m-Y h:i A', strtotime($cdate));
            }
            $submsg = substr($lastmsg, 0, 65);
            array_push($chatsare, array('id' => $cid, 'fromid' => $nfrom, 'toid' => $nto, 'cdate' => $finaldt, 'tousername' => $tousername, 'lastmsg' => $submsg, 'unread' => $unread));
        }
        $getchats->close();

        return $chatsare;
    }

    public function getservice_location()
    {
        $comments = $this->conn->prepare('SELECT state_latlon.* FROM state_latlon');
        $result = $comments->execute();
        $comments->bind_result($id, $statename, $statelat, $statelon);
        $response = array();
        if ($result) {
            while ($comments->fetch()) {
                $cmt['id'] = $id;
                $cmt['statename'] = $statename;
                $cmt['statelat'] = $statelat;
                $cmt['statelon'] = $statelon;
                array_push($response, $cmt);
            }
        }
        $comments->close();
        if ($response) {
            return $response;
        } else {
            return null;
        }
    }

    public function getchatmlist($id, $page, $perpage)
    {
        $gettotal = $this->conn->prepare('SELECT chat_rooms.*,concat(fromu.first_name," ",fromu.last_name) as fromuser, concat(tou.first_name," ",tou.last_name) as touser, SUBSTRING_INDEX(GROUP_CONCAT(messages.message ORDER BY messages.id desc), ",",1) as lastmsg from chat_rooms join users as fromu on fromu.id=chat_rooms.from_user_id join users as tou on tou.id=chat_rooms.to_user_id left join messages on messages.chatroom_id=chat_rooms.id where chat_rooms.from_user_id=? or chat_rooms.to_user_id=? GROUP BY chat_rooms.id order by chat_rooms.updated_at desc');
        $gettotal->bind_param('ii', $id, $id);
        $gettotal->execute();
        $gettotal->store_result();
        $totalchats = $gettotal->num_rows;
        $gettotal->close();

        $start = ($page) * $perpage;
        $getchats = $this->conn->prepare('SELECT chat_rooms.*,concat(fromu.first_name," ",fromu.last_name) as fromuser, concat(tou.first_name," ",tou.last_name) as touser, SUBSTRING_INDEX(GROUP_CONCAT(messages.message ORDER BY messages.id desc), ",",1) as lastmsg, (sum(case WHEN messages.status = 2 THEN 1 else 0 end)) as urm from chat_rooms join users as fromu on fromu.id=chat_rooms.from_user_id join users as tou on tou.id=chat_rooms.to_user_id join messages on messages.chatroom_id=chat_rooms.id where chat_rooms.from_user_id=? or chat_rooms.to_user_id=? GROUP BY chat_rooms.id order by chat_rooms.updated_at desc limit ?,?');
        $getchats->bind_param('iiii', $id, $id, $start, $perpage);
        // SELECT chat_rooms.*, SUBSTRING_INDEX(GROUP_CONCAT(messages.message ORDER BY messages.id desc), ',',1) as lastmsg FROM chat_rooms join messages on messages.chatroom_id=chat_rooms.id GROUP by chat_rooms.id
        $getchats->execute();

        $getchats->bind_result($cid, $fromid, $toid, $cdate, $updatetime, $fun, $tun, $lastmsg, $unread);
        $chatsare = array();
        while ($getchats->fetch()) {
            if ($fromid == $id) {
                $tousername = $tun;
                $nfrom = $fromid;
                $nto = $toid;
            } else {
                $tousername = $fun;
                $nfrom = $toid;
                $nto = $fromid;
            }
            if ($updatetime > 0) {
                $finaldt = date('d-m-Y h:i A', strtotime($updatetime));
            } else {
                $finaldt = date('d-m-Y h:i A', strtotime($cdate));
            }
            $submsg = substr($lastmsg, 0, 65);
            array_push($chatsare, array('id' => $cid, 'fromid' => $nfrom, 'toid' => $nto, 'cdate' => $finaldt, 'tousername' => $tousername, 'lastmsg' => $submsg, 'unread' => $unread));
        }
        $getchats->close();

        $response['items'] = $chatsare;

        $response['incomplete_results'] = false;

        $response['total_count'] = $totalchats;

        return $response;
    }

    public function getchatmessages($cid, $uid)
    {
        $getchats = $this->conn->prepare('SELECT * from messages where status=2 and chatroom_id=?');
        $getchats->bind_param('i', $cid);

        $getchats->execute();

        $getchats->bind_result($id, $chatid, $fromid, $toid, $message, $stat, $sentat);
        $chatsare = array();
        while ($getchats->fetch()) {
            if ($fromid == $uid) {
                $flag = 1;
            } else {
                $flag = 2;
            }
            $cadate = date('d-m-Y h:i A', strtotime($sentat));
            array_push($chatsare, array('id' => $id, 'fromid' => $fromid, 'toid' => $toid, 'message' => $message, 'stat' => $stat, 'sdate' => $sentat, 'flag' => $flag));
        }
        $getchats->close();

        return $chatsare;
    }

    public function addnewmsg($fromid, $toid, $chatid, $msgtxt)
    {
        $nowtime = date('Y-m-d H:i:s');

        $addmsg = $this->conn->prepare('INSERT into messages (chatroom_id, from_user_id, to_user_id, message, status, sent_at) values('.$chatid.', '.$fromid.', '.$toid.", '".$msgtxt."', 2, '".$nowtime."')");

        // $addmsg->bind_param('iiisis', $chatid, $fromid, $toid, $msgtxt, 1, $nowtime);

        $added = $addmsg->execute();
        $addmsg->close();

        if ($added) {
            // return "UPDATE chat_rooms set updated_at='".$nowtime."' where id='".$chatid."'";
            $updatecr = $this->conn->prepare("UPDATE chat_rooms set updated_at='".$nowtime."' where id='".$chatid."'");
            // $updatecr->bind_param('si', $nowtime, $chatid);
            $updatecr->execute();
            $updatecr->close();
            $fntime = date('d-m-y h:i A', strtotime($nowtime));

            return array('sdate' => $fntime, 'message' => $msgtxt);
        } else {
            return 0;
        }
    }

    public function updatechatmsg($cid)
    {
        $upmsg = $this->conn->prepare('UPDATE messages set status=1 where chatroom_id=?');
        $upmsg->bind_param('i', $cid);

        $upstat = $upmsg->execute();
        $upmsg->close();

        if ($upstat) {
            return 1;
        } else {
            return 0;
        }
    }

    public function checklastfive($cid, $fromid)
    {
        $disabled = false;
        $count = 0;
        $response = array();

        $getmsg = $this->conn->prepare('SELECT from_user_id from messages where chatroom_id=? order by id desc limit 5');
        $getmsg->bind_param('i', $cid);

        $getmsg->execute();
        $getmsg->bind_result($fromdbid);
        while ($getmsg->fetch()) {
            if ($fromdbid == $fromid) {
                // $disabled = false;
                ++$count;
            }
        }
        if (5 == $count) {
            $disabled = true;
        }
        $getmsg->close();

        $response['stat'] = $disabled;
        $response['count'] = $count;

        return $response;
    }

    public function admincmsgs($cid, $start)
    {
        $sfrom = $start;
        $gmtotal = $this->conn->prepare("SELECT messages.from_user_id, messages.to_user_id, messages.message, messages.sent_at, concat(fromu.first_name,' ',fromu.last_name) as fromuser, concat(tou.first_name,' ',tou.last_name) as touser from messages join users as fromu on fromu.id=messages.from_user_id join users as tou on tou.id=messages.to_user_id where messages.chatroom_id=? order by messages.id desc");
        $gmtotal->bind_param('i', $cid);
        $gmtotal->execute();
        $gmtotal->store_result();
        $total = $gmtotal->num_rows;
        $gmtotal->close();

        $getft = $this->conn->prepare('SELECT from_user_id, to_user_id from chat_rooms where id=?');
        $getft->bind_param('i', $cid);
        $getft->execute();
        $getft->bind_result($fromcu, $tocu);
        $getft->fetch();
        $getft->close();

        $start = $start * 10;
        $messages = array();

        $getamsg = $this->conn->prepare("SELECT messages.from_user_id as fromid, messages.to_user_id as toid, messages.message, messages.sent_at, concat(fromu.first_name,' ',fromu.last_name) as fromuser, concat(tou.first_name,' ',tou.last_name) as touser from messages join users as fromu on fromu.id=messages.from_user_id join users as tou on tou.id=messages.to_user_id where messages.chatroom_id=? order by messages.id desc limit ?,10");
        $getamsg->bind_param('ii', $cid, $start);

        $getamsg->execute();

        $getamsg->bind_result($fromid, $toid, $msg, $sdate, $fromuser, $touser);

        while ($getamsg->fetch()) {
            array_push($messages, array('fromid' => $fromid, 'toid' => $toid, 'sdate' => $sdate, 'msg' => $msg, 'fromuser' => $fromuser, 'touser' => $touser));
        }
        $getamsg->close();

        $sfrom = $sfrom + 1;
        $sfrom = $sfrom * 10;

        if ($total > $sfrom) {
            $more = true;
        } else {
            $more = false;
        }
        $response['msgs'] = $messages;
        $response['loadmore'] = $more;
        $response['fromcu'] = $fromcu;
        $response['tocu'] = $tocu;

        return $response;
    }

    public function unreadcountmsg($uid)
    {
        $retarray = array('error' => true, 'message' => 'Something went wrong', 'data' => 0);

        $gunread = $this->conn->prepare('SELECT (sum(case when status=2 then 1 else 0 end)) as uncount from messages where to_user_id=?');
        $gunread->bind_param('i', $uid);
        $res = $gunread->execute();

        $gunread->bind_result($unread);

        if ($res) {
            $gunread->fetch();
            $retarray['error'] = false;
            $retarray['message'] = 'Success';
            if (null == $unread) {
                $unread = 0;
            }
            $retarray['data'] = $unread;
        } else {
            $retarray['error'] = false;
            $retarray['message'] = 'Could not get count';
            $retarray['data'] = 0;
        }

        return $retarray;
    }

    //messages

    public function addToWallet($id, $amount)
    {
        $nowtime = date('Y-m-d H:i:s');

        $retarray = array('error' => true, 'message' => 'Something went wrong', 'data' => 0);

        $getuptime = $this->conn->prepare('SELECT updated_time from users where id=?');
        $getuptime->bind_param('i', $id);
        $getuptime->execute();
        $getuptime->bind_result($uptime);
        $getuptime->fetch();
        $getuptime->close();

        if ($uptime > 0) {
            $addamount = $this->conn->prepare('UPDATE users set wallet_amount=wallet_amount+? where id=?');
        } else {
            $addamount = $this->conn->prepare("UPDATE users set wallet_amount=wallet_amount+?, pause_time=2, updated_time='".$nowtime."' where id=?");
        }

        $addamount->bind_param('ii', $amount, $id);

        $addamount->execute();

        if ($addamount) {
            $retarray['error'] = false;
            $retarray['message'] = 'Amount added';
        }

        return $retarray;
    }

    public function adminLogin($email, $pass)
    {
        require_once 'PassHash.php';

        $getpass = $this->conn->prepare('SELECT password from admin where email=?');
        $getpass->bind_param('s', $email);
        $getpass->execute();
        $getpass->bind_result($gcurrentpass);
        $getpass->fetch();
        $currentpass = $gcurrentpass;
        $checkbothpass = PassHash::check_password($currentpass, $pass);
        $getpass->close();
        $response = array();

        //return $checkbothpass;
        if (true == $checkbothpass) {
            $stmt = $this->conn->prepare('SELECT id, username, email, last_login from admin where email=? and password=?');
            $stmt->bind_param('ss', $email, $currentpass);

            //return $stmt->execute();
            $res = $stmt->execute();
            //return $res;
            if ($res) {
                $stmt->bind_result($aid, $aname, $aemail, $lastlogin);
                $stmt->fetch();
                //return $aid;
                $stmt->store_result();
                $num_rowsadmin = $stmt->num_rows;
                //return $num_rowsadmin;
                if (!empty($aid)) {
                    //return $aid;
                    $response['adminid'] = $aid;
                    $response['adminname'] = $aname;
                    $response['adminemail'] = $aemail;
                    $response['lastlogin'] = $lastlogin;
                    $response['subadmin'] = false;
                }
            }

            $stmt->close();
            //echo $stmt->__toString();die();

            if (!empty($aid)) {
                $response['stat'] = 1;
            } else {
                $response['stat'] = 0;
            }
        } else {
            $getpasssub = $this->conn->prepare('SELECT password from sub_admins where email=?');
            $getpasssub->bind_param('s', $email);
            $getpasssub->execute();
            $getpasssub->bind_result($gcurrentpasssub);
            $getpasssub->fetch();
            $currentpasssub = $gcurrentpasssub;
            $checkbothpass = PassHash::check_password($currentpasssub, $pass);
            $getpasssub->close();
            $stmt = $this->conn->prepare('SELECT id, name, email, privileges from sub_admins where email=? and password=?');
            $stmt->bind_param('ss', $email, $currentpasssub);

            //return $stmt->execute();
            $res = $stmt->execute();
            //return $res;
            if ($res) {
                $stmt->bind_result($aid, $aname, $aemail, $prev);
                $stmt->fetch();
                //return $aid;
                $stmt->store_result();
                $num_rowsadmin = $stmt->num_rows;
                //return $num_rowsadmin;
                if (!empty($aid)) {
                    //return $aid;
                    $response['adminid'] = $aid;
                    $response['adminname'] = $aname;
                    $response['adminemail'] = $aemail;
                    $response['prev'] = unserialize($prev);
                    $response['subadmin'] = true;
                }
            }

            $stmt->close();
            //echo $stmt->__toString();die();

            if (!empty($aid)) {
                $response['stat'] = 1;
            } else {
                $response['stat'] = 0;
            }
        }

        return $response;
    }

    public function addsubadmin($name, $email, $pass, $prev)
    {
        require_once 'PassHash.php';
        $return = array('status' => 0, 'message' => 'Something went wrong');

        $checksubadmin = $this->conn->prepare('SELECT id from sub_admins where email = ?');
        $checksubadmin->bind_param('s', $email);

        $checksubadmin->execute();
        $checksubadmin->bind_result($id);
        $checksubadmin->store_result();
        $checksno = $checksubadmin->num_rows;
        $checksubadmin->close();
        $new_passhash = PassHash::hash($pass);
        if (0 == $checksno) {
            $addsubadmin = $this->conn->prepare('INSERT into sub_admins (name, email, password, privileges) values(?,?,?,?)');
            $addsubadmin->bind_param('ssss', $name, $email, $new_passhash, $prev);

            $adds = $addsubadmin->execute();
            $addsubadmin->close();
            $return['status'] = 1;
            $return['message'] = 'Sub admin succesfully added';
        } else {
            $return['status'] = 0;
            $return['message'] = 'Email already exists';
        }

        return $return;
    }

    public function monthlyUserUpdate()
    {
        $mfees = 0;
        $hfees = 0;

        $stmt = $this->conn->prepare('SELECT monthly_fees, highlight_fees from general_settings');

        $res = $stmt->execute();
        $stmt->bind_result($monthly_fees, $highlightfees);
        $stmt->fetch();
        if ($res) {
            $mfees = $monthly_fees;
            $hfees = $highlightfees;
        }

        $stmt->close();

        $usersar = array();
        $getallusers = $this->conn->prepare("SELECT id, highlight, status, admin_status, pause_time, wallet_amount from users where status=3 and admin_status=1 and pause_time=1 and gender='Female'");
        $getallusersres = $getallusers->execute();
        $getallusers->bind_result($userid, $highlight, $status, $admin_status, $pausetime, $walletamount);

        while ($getallusers->fetch()) {
            array_push($usersar, array('userid' => $userid, 'highlight' => $highlight, 'status' => $status, 'admin_status' => $admin_status, 'pausetime' => $pausetime, 'walletamount' => $walletamount));
        }

        $getallusers->close();
        foreach ($usersar as $user) {
            if ($user['walletamount'] > $mfees) {
                $trdate = date('Y-m-d H:i:s');

                $newtr = $this->conn->prepare('INSERT into transactions (user_id, amount, type, to_id, remarks, transaction_time) values('.$user['userid'].",'".$mfees."',1,'a1','User fees','".$trdate."')");

                if ($newtr) { // assuming $mysqli is the connection
                    $trres = $newtr->execute();
                // any additional code you need would go here.
                } else {
                    $error = $this->conn->errno.' '.$this->conn->error;
                    echo $error; // 1054 Unknown column 'foo' in 'field list'
                }

                $newtr->close();

                $nowtime = date('Y-m-d H:i:s');

                $users = $this->conn->prepare('UPDATE users set wallet_amount=wallet_amount-'.$mfees.", updated_time='".$nowtime."' where id=".$user['userid'].' ');
                $userupdate = $users->execute();

                $users->close();

                if (1 == $user['highlight']) {
                    if ($user['walletamount'] > $hfees) {
                        $newtr = $this->conn->prepare('INSERT into transactions (user_id, amount, type, to_id, remarks, transaction_time) values('.$user['userid'].",'".$hfees."',1,'a1','User fees for highlight profile','".$trdate."')");

                        if ($newtr) {
                            $trres = $newtr->execute();
                        // any additional code you need would go here.
                        } else {
                            $error = $this->conn->errno.' '.$this->conn->error;
                            echo $error;
                        }

                        $newtr->close();

                        $nowtime = date('Y-m-d H:i:s');

                        $users = $this->conn->prepare('UPDATE users set wallet_amount=wallet_amount-'.$hfees.", updated_time='".$nowtime."' where id=".$user['userid'].' ');
                        $userupdate = $users->execute();

                        $users->close();
                    } else {
                        $users = $this->conn->prepare('UPDATE users set highlight=0 where id='.$user['userid'].' ');
                        $userupdate = $users->execute();

                        $users->close();
                    }
                }
            } else {
                $users = $this->conn->prepare('UPDATE users set admin_status=2,pause_time=2 where id='.$user['userid'].' ');
                $userupdate = $users->execute();

                $users->close();
            }
        }

        if ($userupdate) {
            return 1;
        } else {
            return 0;
        }
    }

    public function adminForgotPass($email)
    {
        require_once 'PassHash.php';
        $response = array();

        //return $email;
        $stmt = $this->conn->prepare('SELECT id, email from admin where email=?');
        $stmt->bind_param('s', $email);

        //return $stmt->execute();
        $res = $stmt->execute();
        //return $res;
        if ($res) {
            $stmt->bind_result($aid, $aemail);
            $stmt->fetch();
            //return $aid;
            $aminid = $aid;
            $stmt->store_result();
            $num_rowsadmin = $stmt->num_rows;
            $stmt->close();
            //return $num_rowsadmin;
            if (!empty($aminid)) {
                //return $aid;
                $length = 8;
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
                $new_pass = substr(str_shuffle($chars), 0, $length);
                $new_passhash = PassHash::hash($new_pass);

                $update = $this->conn->prepare('UPDATE admin set password=? where email=?');
                $update->bind_param('ss', $new_passhash, $aemail);
                $result = $update->execute();

                $update->close();
                $response['adminid'] = $aminid;
                $response['newpasshash'] = $new_passhash;
                $response['newpass'] = $new_pass;
                $response['adminemail'] = $aemail;
            }
        }
        //echo $stmt->__toString();die();

        if (!empty($aid)) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function updatehighlight($uid, $stat)
    {
        $retar = array('error' => true, 'message' => 'Something went wrong', 'data' => array());

        $nowtime = date('Y-m-d H:i:s');

        if (false == $stat) {
            $pausetime = 1;
            $updateh = $this->conn->prepare('UPDATE users set pause_time=? where id=?');
            $updateh->bind_param('ii', $pausetime, $uid);
        } elseif (true == $stat) {
            $pausetime = 2;
            $updateh = $this->conn->prepare('UPDATE users set pause_time=?, pausedat_time=? where id=?');
            $updateh->bind_param('isi', $pausetime, $nowtime, $uid);
        }

        $upstat = $updateh->execute();
        $updateh->close();

        $getuptime = $this->conn->prepare('SELECT updated_time from users where id=?');
        $getuptime->bind_param('i', $uid);
        $getuptime->execute();
        $getuptime->bind_result($updatetime);
        $getuptime->fetch();
        $getuptime->close();

        if ($upstat) {
            $dar = array();

            $dar['uptime'] = date('M j, Y H:i:s', strtotime('+1 day', strtotime($updatetime)));
            $dar['curdate'] = date('M j, Y H:i:s');

            $retar['error'] = false;
            $retar['message'] = 'Success';
            $retar['data'] = $dar;
        }

        return $retar;
    }

    public function getSiteInfo()
    {
        $sitinfo = $this->conn->prepare('SELECT * from general_settings');
        //$sitinfo->bind_param("i", $id);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $supportemail, $imgprice, $commission, $favimg, $logoimg, $boyimg, $girlimg, $blogurl, $fburl, $tweeturl, $youtubeurl, $instaurl, $linkedinurl, $dribbleurl, $googlepurl, $followtitle, $followdesc, $cptext, $visitors, $monfees, $highlightfees);
        $sitinfo->fetch();
        if ($res) {
            $response['id'] = $id;
            $response['supportemail'] = $supportemail;
            $response['imgprice'] = $imgprice;
            $response['commission'] = $commission;
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
            $response['highlightfees'] = $highlightfees;
            $response['dailyfees'] = $monfees;
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();

        return $response;
    }

    public function getAdminInfo($id)
    {
        $sitinfo = $this->conn->prepare('SELECT id, username, email, password, admin_id from admin where id=? ');
        $sitinfo->bind_param('i', $id);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $username, $email, $password, $adminid);
        $sitinfo->fetch();
        if ($res) {
            $response['id'] = $id;
            $response['username'] = $username;
            $response['email'] = $email;

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();

        return $response;
    }

    public function getAdminNotify($id, $lastlogin)
    {
        $sitinfo = $this->conn->prepare('SELECT sum(case when type=1 then 1 else 0 end) as newusers, sum(case when type=2 then 1 else 0 end) as newcomments from admin_notifications where added_at>?');
        $sitinfo->bind_param('s', $lastlogin);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($newuser, $newcomments);
        $sitinfo->fetch();

        if ($res) {
            $response['newuser'] = $newuser;
            $response['newcomments'] = $newcomments;

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();

        return $response;
    }

    public function updateUserLogin($id)
    {
        $nowdate = date('Y-m-d H:i:s');

        $sitinfo = $this->conn->prepare('UPDATE users set lastlogin=? where id=?');
        $sitinfo->bind_param('si', $nowdate, $id);
        $res = $sitinfo->execute();
        $sitinfo->close();

        if ($res) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function tempupdatefunction()
    {
        $nowdate = date('Y-m-d H:i:s', strtotime('-3 weeks'));
        $response = array();
        for ($i = 32; $i < 93; ++$i) {
            $sitinfo = $this->conn->prepare('UPDATE users set lastlogin=? where id=?');
            $sitinfo->bind_param('si', $nowdate, $i);
            $res = $sitinfo->execute();
            $sitinfo->close();

            if ($res) {
                array_push($response, 1);
            } else {
                array_push($response, 0);
            }
        }

        return $response;
    }

    public function addComment($id, $gid, $comment, $name)
    {
        if (!empty($id)) {
            $sitinfo = $this->conn->prepare('INSERT into comments (girl_id, user_id, description) values(?, ?, ?)');
            $sitinfo->bind_param('iis', $gid, $id, $comment);
        } else {
            $sitinfo = $this->conn->prepare('INSERT into comments (girl_id, addedby, description) values(?, ?, ?)');
            $sitinfo->bind_param('iss', $gid, $name, $comment);
        }

        // var_dump("INSERT into comments (girl_id, user_id, description) values(".$gid.", ".$id.", ".$comment.")");
        // die();

        $res = $sitinfo->execute();
        $sitinfo->fetch();
        $sitinfo->close();

        $updatescore = $this->conn->prepare('UPDATE users set comment=comment+1 where id=?');
        $updatescore->bind_param('i', $gid);
        $res2 = $updatescore->execute();
        $updatescore->fetch();
        $updatescore->close();
        // echo 'res'.$res.'res2'.$res2;
        // die();

        if ($res && $res2) {
            $response['stat'] = 1;

            $getadmintoken = $this->getFcmToken();
            $retresp['notify'] = $this->sendnotification($getadmintoken, 'There is a new comment.', 'New comment');
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function addComplaint($id, $gid, $comment, $name)
    {
        if (!empty($id)) {
            $sitinfo = $this->conn->prepare('INSERT into complaints (girl_id, user_id, description) values(?, ?, ?)');
            $sitinfo->bind_param('iis', $gid, $id, $comment);
        } else {
            $sitinfo = $this->conn->prepare('INSERT into complaints (girl_id, addedby, description) values(?, ?, ?)');
            $sitinfo->bind_param('iss', $gid, $name, $comment);
        }

        $res = $sitinfo->execute();
        $sitinfo->fetch();
        $sitinfo->close();

        $updatescore = $this->conn->prepare('UPDATE users set complaint=complaint+1 where id=?');
        $updatescore->bind_param('i', $gid);
        $res2 = $updatescore->execute();
        $updatescore->fetch();
        $updatescore->close();

        if ($res && $res2) {
            $response['stat'] = 1;

            $getadmintoken = $this->getFcmToken();
            $retresp['notify'] = $this->sendnotification($getadmintoken, 'There is a new complaint.', 'New complaint');
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function adminEarnings()
    {
        $sitinfo = $this->conn->prepare("SELECT sum(case when to_id='a1' and type=1 then amount else 0 end) from transactions");

        $res = $sitinfo->execute();
        $sitinfo->bind_result($total);
        $sitinfo->fetch();
        if ($res) {
            $response['total'] = $total;

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();

        return $response;
    }

    public function totalVisitors()
    {
        $sitinfo = $this->conn->prepare('SELECT total_visitors from general_settings');

        $res = $sitinfo->execute();
        $sitinfo->bind_result($total);
        $sitinfo->fetch();
        if ($res) {
            $response['total'] = $total;

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();

        return $response;
    }

    public function adminEarningsByYear($year)
    {
        $sitinfo = $this->conn->prepare("SELECT id, amount, transaction_time from transactions where YEAR(transaction_time)=? and to_id='a1' and type='1' ");
        $sitinfo->bind_param('s', $year);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $amount, $ttime);
        if ($res) {
            $response['stat'] = 1;
            $yearly_user = array();

            while ($sitinfo->fetch()) {
                $key = date('n', strtotime($ttime));
                $yearly_user[$key][] = (int) $amount;
            }

            for ($i = 1; $i < 13; ++$i) {
                if (!array_key_exists($i, $yearly_user)) {
                    $yearly_user[$i] = array(0);
                }
            }

            $response['data'] = array(1 => array_sum($yearly_user[1]),
                2 => array_sum($yearly_user[2]),
                3 => array_sum($yearly_user[3]),
                4 => array_sum($yearly_user[4]),
                5 => array_sum($yearly_user[5]),
                6 => array_sum($yearly_user[6]),
                7 => array_sum($yearly_user[7]),
                8 => array_sum($yearly_user[8]),
                9 => array_sum($yearly_user[9]),
                10 => array_sum($yearly_user[10]),
                11 => array_sum($yearly_user[11]),
                12 => array_sum($yearly_user[12]),
            );
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();

        return $response;
    }

    public function getUsersList($sort, $sfield, $page, $perpage, $stat, $searchq, $type)
    {
        $where = 'where status=3 ';

        if (!empty($stat)) {
            $where .= ' and admin_status = '.$stat;
        }
        if (!empty($type)) {
            $where .= " and gender = '".$type."'";
        }
        if (!empty($searchq)) {
            $where .= " and (first_name like '%".$searchq."%' || last_name like '%".$searchq."%' || gender like '%".$searchq."%' || age like '%".$searchq."%' || email like '%".$searchq."%' || phone like '%".$searchq."%')";
        }

        $gettotal = $this->conn->prepare('SELECT id, gender, first_name, last_name, email, phone, location, age, images,admin_status, wallet_amount from users '.$where.'');

        $gettotal->execute();
        $gettotal->store_result();
        $totalusers = $gettotal->num_rows;
        $gettotal->close();

        if ('srno' == $sfield) {
            $sfield = 'id';
        }
        if ('username' == $sfield) {
            $sfield = 'first_name';
        }

        $start = ($page - 1) * $perpage;

        $sitinfo = $this->conn->prepare('SELECT id, gender, first_name, last_name, email, phone, location, age, status, images, admin_status, wallet_amount from users '.$where.' order by '.$sfield.' '.$sort.' limit ?,?');
        $sitinfo->bind_param('ii', $start, $perpage);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $gender, $fname, $lname, $email, $phone, $location, $age, $status, $images, $adminstat, $walletamount);
        $response['users'] = array();
        if ($res) {
            $uc = 0;
            while ($sitinfo->fetch()) {
                ++$uc;
                if (!empty($images)) {
                    $imageuser = unserialize($images);
                    if (count($imageuser) > 0) {
                        $fimguser = $imageuser[0];
                    } else {
                        $fimguser = '';
                    }
                } else {
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
                $user['wallet_amount'] = $walletamount;

                array_push($response['users'], $user);
            }

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();
        $response['total'] = $totalusers;

        return $response;
    }

    public function geetsubadminlist($sort, $sfield, $page, $perpage, $searchq)
    {
        $where = 'where 1';

        if (!empty($searchq)) {
            $where .= " and (name like '%".$searchq."%' || email like '%".$searchq."%')";
        }

        $gettotal = $this->conn->prepare('SELECT id, name, email from sub_admins '.$where.'');

        $gettotal->execute();
        $gettotal->store_result();
        $totalusers = $gettotal->num_rows;
        $gettotal->close();

        $start = ($page - 1) * $perpage;

        $sitinfo = $this->conn->prepare('SELECT id, name, email from sub_admins '.$where.' order by '.$sfield.' '.$sort.' limit ?,?');
        $sitinfo->bind_param('ii', $start, $perpage);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $name, $email);
        $response['users'] = array();
        if ($res) {
            while ($sitinfo->fetch()) {
                $user['id'] = $id;
                $user['name'] = $name;
                $user['email'] = $email;

                array_push($response['users'], $user);
            }

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();
        $response['total'] = $totalusers;

        return $response;
    }

    public function getTransactionList($sort, $sfield, $page, $perpage, $uid, $searchq, $type, $daterange)
    {
        $where = 'where 1';

        if (!empty($uid)) {
            $where .= ' and (user_id = '.$uid.' or to_id='.$uid.')';
        }
        if (!empty($type)) {
            $where .= " and type = '".$type."'";
        }
        if (!empty($daterange)) {
            $getdate = explode('/', $daterange);
            $from = date('Y-m-d', strtotime(trim($getdate[0])));
            $to = date('Y-m-d', strtotime(trim($getdate[1])));

            $where .= " and (transaction_time >= '".$from."' and transaction_time <= '".$to."')";
        }
        if (!empty($searchq)) {
            $where .= " and (fromu.first_name like '%".$searchq."%' || fromu.last_name like '%".$searchq."%' || tou.first_name like '%".$searchq."%' || tou.last_name like '%".$searchq."%' || froma.username like '%".$searchq."%' || toa.username like '%".$searchq."%' || amount like '%".$searchq."%' || remarks like '%".$searchq."%')";
        }

        $gettotal = $this->conn->prepare("SELECT t.*, fromu.first_name as fufn, fromu.last_name as fuln, tou.first_name as tufn, tou.last_name as tuln, froma.username as fadmin, toa.username as tadmin, (case when t.to_id='a1' then t.amount when t.type=3 then t.totalamount-t.amount when t.type=1 and t.user_id=null then t.amount else 0 end) as adminearn from transactions as t left join users as fromu on t.user_id=fromu.id left join users as tou on t.to_id=tou.id left join admin as froma on froma.admin_id=t.user_id left join admin as toa on toa.admin_id=t.to_id ".$where.'');

        $gettotal->execute();
        $gettotal->store_result();
        $totalusers = $gettotal->num_rows;
        $gettotal->close();

        if ('tdate' == $sfield) {
            $sfield = 't.transaction_time';
        }
        if ('ttime' == $sfield) {
            $sfield = 't.transaction_time';
        }

        $start = ($page - 1) * $perpage;

        $sitinfo = $this->conn->prepare("SELECT t.*, fromu.first_name as fufn, fromu.last_name as fuln, tou.first_name as tufn, tou.last_name as tuln, froma.username as fadmin, toa.username as tadmin,(case when t.to_id='a1' then t.amount when t.type=3 then t.totalamount-t.amount when t.type=1 and t.user_id=null then t.amount else 0 end) as adminearn from transactions as t left join users as fromu on t.user_id=fromu.id left join users as tou on t.to_id=tou.id left join admin as froma on t.user_id=froma.admin_id left join admin as toa on t.to_id=toa.admin_id ".$where.' order by '.$sfield.' '.$sort.' limit ?,?');
        $sitinfo->bind_param('ii', $start, $perpage);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $userid, $amount, $commission, $totalam, $type, $toid, $remarks, $ttime, $fromufn, $fromuln, $toufn, $touln, $froma, $toa, $adminearn);
        $response['users'] = array();
        if ($res) {
            $uc = 0;
            while ($sitinfo->fetch()) {
                ++$uc;

                $user['srno'] = $uc;
                $user['id'] = $id;
                $user['from_uid'] = $userid;
                if (!empty($fromufn)) {
                    $user['from_user'] = $fromufn.' '.$fromuln;
                } else {
                    $user['from_user'] = '';
                }

                $user['to_uid'] = $toid;

                if (!empty($toufn)) {
                    $user['to_user'] = $toufn.' '.$touln;
                } else {
                    $user['to_user'] = '';
                }

                $user['from_admin'] = $froma;
                $user['to_admin'] = $toa;
                $user['tdate'] = date('n-j-Y', strtotime($ttime));
                $user['ttime'] = date('h:i A', strtotime($ttime));
                $user['type'] = $type;
                $user['amount'] = $amount;
                $user['remarks'] = $remarks;
                $user['adearn'] = $adminearn;

                array_push($response['users'], $user);
            }

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();
        $response['total'] = $totalusers;

        return $response;
    }

    public function adminchats($sort, $sfield, $page, $perpage, $uid)
    {
        $where = 'where 1';

        if (!empty($uid)) {
            $where .= ' and (chat_rooms.from_user_id='.$uid.' or chat_rooms.to_user_id='.$uid.')';
        }

        $gettotal = $this->conn->prepare('SELECT chat_rooms.*,concat(fromu.first_name," ",fromu.last_name) as fromuser, concat(tou.first_name," ",tou.last_name) as touser, SUBSTRING_INDEX(GROUP_CONCAT(messages.message ORDER BY messages.id desc), ",",1) as lastmsg from chat_rooms join users as fromu on fromu.id=chat_rooms.from_user_id join users as tou on tou.id=chat_rooms.to_user_id left join messages on messages.chatroom_id=chat_rooms.id '.$where.' GROUP BY chat_rooms.id order by chat_rooms.updated_at desc');

        // return 'SELECT chat_rooms.*,concat(fromu.first_name," ",fromu.last_name) as fromuser, concat(tou.first_name," ",tou.last_name) as touser, SUBSTRING_INDEX(GROUP_CONCAT(messages.message ORDER BY messages.id desc), ",",1) as lastmsg from chat_rooms join users as fromu on fromu.id=chat_rooms.from_user_id join users as tou on tou.id=chat_rooms.to_user_id left join messages on messages.chatroom_id=chat_rooms.id '.$where.' GROUP BY chat_rooms.id order by chat_rooms.updated_at desc';

        $gettotal->execute();
        $gettotal->store_result();
        $totalusers = $gettotal->num_rows;
        $gettotal->close();

        if ('tdate' == $sfield) {
            $sfield = 't.transaction_time';
        }
        if ('ttime' == $sfield) {
            $sfield = 't.transaction_time';
        }

        $start = ($page - 1) * $perpage;

        $sitinfo = $this->conn->prepare('SELECT chat_rooms.*,concat(fromu.first_name," ",fromu.last_name) as fromuser, concat(tou.first_name," ",tou.last_name) as touser, SUBSTRING_INDEX(GROUP_CONCAT(messages.message ORDER BY messages.id desc), ",",1) as lastmsg from chat_rooms join users as fromu on fromu.id=chat_rooms.from_user_id join users as tou on tou.id=chat_rooms.to_user_id left join messages on messages.chatroom_id=chat_rooms.id '.$where.' GROUP BY chat_rooms.id order by chat_rooms.updated_at desc limit ?,?');
        $sitinfo->bind_param('ii', $start, $perpage);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $fromid, $toid, $cdate, $update, $fromuser, $touser, $lastmsg);
        $response['users'] = array();
        if ($res) {
            while ($sitinfo->fetch()) {
                $user['id'] = $id;
                $user['fromuser'] = $fromuser;
                $user['touser'] = $touser;
                if (strlen($lastmsg) > 40) {
                    $user['lastmsg'] = substr($lastmsg, 0, 40).'...';
                } else {
                    $user['lastmsg'] = $lastmsg;
                }

                if ($update > 0) {
                    $mtime = date('m-d-Y', strtotime($update));
                } else {
                    $mtime = date('m-d-Y', strtotime($cdate));
                }
                $user['cdate'] = $mtime;

                array_push($response['users'], $user);
            }

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();
        $response['total'] = $totalusers;

        return $response;
    }

    public function userTransactions($sort, $order, $page, $perpage, $uid, $fromdate, $todate)
    {
        $where = 'where (user_id='.$uid.' or to_id='.$uid.')';
        $where .= " and !(remarks='Picture unlock payment' and type=2)";
        if ('empty' !== $fromdate) {
            $fromdate = str_replace('%20', ' ', $fromdate);
            $newfromdate = date('Y-m-d', strtotime($fromdate)).' 00:00:00';
            $where .= "and transaction_time >= '".$newfromdate."'";
        }
        if ('empty' !== $todate) {
            $todate = str_replace('%20', ' ', $todate);
            $newtodate = date('Y-m-d', strtotime($todate)).' 24:00:00';
            $where .= "and transaction_time <= '".$newtodate."'";
        }
        // echo $fromdate;
        // exit();
        // echo "SELECT * from transactions ".$where." order by ".$sort." ".$order." ";
        // exit();
        $gettotal = $this->conn->prepare('SELECT * from transactions '.$where.' order by '.$sort.' '.$order.' ');

        $gettotal->execute();
        $gettotal->store_result();
        $totalusers = $gettotal->num_rows;
        $gettotal->close();

        $start = ($page) * $perpage;

        $sitinfo = $this->conn->prepare('SELECT * from transactions '.$where.' order by '.$sort.' '.$order.' limit ?,?');
        $sitinfo->bind_param('ii', $start, $perpage);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $userid, $amount, $commission, $totalam, $type, $toid, $remarks, $ttime);
        $response['items'] = array();
        if ($res) {
            while ($sitinfo->fetch()) {
                if (1 == $type && 'User fees' == $remarks) {
                    $ftype = 'Debit';
                } elseif (1 == $type && 'User fees' != $remarks) {
                    $ftype = 'Credit';
                } elseif (2 == $type) {
                    $ftype = 'Debit';
                } else {
                    $ftype = 'Earnings';
                }
                $user['trdate'] = date('n-j-Y', strtotime($ttime));
                $user['trtime'] = date('h:i A', strtotime($ttime));
                $user['type'] = $ftype;
                $user['money'] = $amount;
                $user['remark'] = $remarks;

                array_push($response['items'], $user);
            }

            $response['incomplete_results'] = false;
        } else {
            $response['incomplete_results'] = true;
        }
        $sitinfo->close();
        $response['total_count'] = $totalusers;

        return $response;
    }

    public function getGirlEarning($sort, $sfield, $page, $perpage, $uid, $searchq, $type)
    {
        $where = ' ';

        if (!empty($uid)) {
            $where .= ' and users.id = '.$uid.' ';
        }
        if (!empty($type)) {
            if (1 == $type) {
                $where .= ' and users.earnings=0';
            }
            if (2 == $type) {
                $where .= ' and users.earnings>0';
            }
        }
        // if(!empty($daterange)){
        //   $getdate = explode("/", $daterange);
        //   $from = date("Y-m-d",strtotime(trim($getdate[0])));
        //   $to = date("Y-m-d",strtotime(trim($getdate[1])));
        //
        //   $where .= " and (transaction_time >= '".$from."' and transaction_time <= '".$to."')";
        // }
        if (!empty($searchq)) {
            $where .= " and (users.first_name like '%".$searchq."%' || users.last_name like '%".$searchq."%')";
        }

        $gettotal = $this->conn->prepare('SELECT GROUP_CONCAT(users.id) as nonids, users.id, users.first_name, users.last_name, (case when users.earnings>0 then 1 else 0 end) as pstat, transactions.commission, users.earnings as useram, SUM(case when transactions.type=3 then transactions.totalamount else 0 end) as total, (SUM(case when transactions.type=3 then transactions.totalamount else 0 end)-SUM(case when transactions.type=3 then transactions.amount else 0 end)) as remam from users left join transactions on users.id=transactions.to_id where users.status=3 '.$where.' GROUP by users.id ORDER BY pstat desc,transactions.transaction_time DESC');

        $gettotal->execute();
        $gettotal->store_result();
        $totalusers = $gettotal->num_rows;
        $gettotal->close();

        $start = ($page - 1) * $perpage;

        // SELECT GROUP_CONCAT(users.id) as nonids, users.id, users.first_name, users.last_name, (case when users.earnings>0 then 1 else 0 end) as pstat, transactions.commission, SUM(case when transactions.type=3 then transactions.amount else 0 end) as useram, SUM(case when transactions.type=3 then transactions.totalamount else 0 end) as total, (SUM(case when transactions.type=3 then transactions.totalamount else 0 end)-SUM(case when transactions.type=3 then transactions.amount else 0 end)) as remam from users left join transactions on users.id=transactions.to_id GROUP by users.id ORDER BY pstat desc,transactions.transaction_time DESC

        $sitinfo = $this->conn->prepare('SELECT GROUP_CONCAT(users.id) as nonids, users.id, users.first_name, users.last_name, (case when users.earnings>0 then 1 else 0 end) as pstat, transactions.commission, users.earnings as useram, SUM(case when transactions.type=3 then transactions.totalamount else 0 end) as total, (SUM(case when transactions.type=3 then transactions.totalamount else 0 end)-SUM(case when transactions.type=3 then transactions.amount else 0 end)) as remam from users left join transactions on users.id=transactions.to_id where 1 '.$where.' GROUP by users.id ORDER BY pstat desc,transactions.transaction_time DESC limit ?,?');

        $sitinfo->bind_param('ii', $start, $perpage);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($noid, $id, $fname, $lname, $pstat, $com, $useram, $total, $remam);
        $response['users'] = array();
        if ($res) {
            while ($sitinfo->fetch()) {
                $user['id'] = $id;
                $user['first_name'] = $fname.' '.$lname;
                $user['pstat'] = $pstat;
                $user['useram'] = $useram;
                $user['total'] = $total;
                $user['remam'] = $remam;

                array_push($response['users'], $user);
            }

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();
        $response['total'] = $totalusers;

        return $response;
    }

    public function getGirlProfiles($sort, $sfield, $page, $perpage, $stat, $searchq)
    {
        $where = "where gender='Female' and status=2";

        if (!empty($stat)) {
            $where .= ' and status = '.$stat;
        }
        if (!empty($searchq)) {
            $where .= " and (first_name like '%".$searchq."%' || last_name like '%".$searchq."%' || gender like '%".$searchq."%' || age like '%".$searchq."%' || email like '%".$searchq."%' || phone like '%".$searchq."%')";
        }

        $gettotal = $this->conn->prepare('SELECT id, gender, first_name, last_name, email, phone, location, age, images from users '.$where.'');

        $gettotal->execute();
        $gettotal->store_result();
        $totalusers = $gettotal->num_rows;
        $gettotal->close();

        if ('srno' == $sfield) {
            $sfield = 'id';
        }
        if ('username' == $sfield) {
            $sfield = 'first_name';
        }

        $start = ($page - 1) * $perpage;

        $sitinfo = $this->conn->prepare('SELECT id, gender, first_name, last_name, email, phone, location, age, status, images from users '.$where.' order by '.$sfield.' '.$sort.' limit ?,?');
        $sitinfo->bind_param('ii', $start, $perpage);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $gender, $fname, $lname, $email, $phone, $location, $age, $status, $images);
        $response['users'] = array();
        if ($res) {
            $uc = 0;
            while ($sitinfo->fetch()) {
                ++$uc;
                if (!empty($images)) {
                    $imageuser = unserialize($images);
                    if (count($imageuser) > 0) {
                        $fimguser = $imageuser[0];
                    } else {
                        $fimguser = '';
                    }
                } else {
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
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();
        $response['total'] = $totalusers;

        return $response;
    }

    public function getInactiveList($sort, $sfield, $page, $perpage, $stat, $searchq)
    {
        $where = 'where status=3 and ((NOW() - INTERVAL 14 DAY)>lastlogin)';

        if (!empty($stat)) {
            $where .= ' and status = '.$stat;
        }
        if (!empty($searchq)) {
            $where .= " and (first_name like '%".$searchq."%' || last_name like '%".$searchq."%' || gender like '%".$searchq."%' || age like '%".$searchq."%' || email like '%".$searchq."%' || phone like '%".$searchq."%')";
        }

        $gettotal = $this->conn->prepare('SELECT id, first_name, last_name, admin_status, lastlogin from users '.$where.'');

        $gettotal->execute();
        $gettotal->store_result();
        $totalusers = $gettotal->num_rows;
        $gettotal->close();

        if ('srno' == $sfield) {
            $sfield = 'id';
        }
        if ('username' == $sfield) {
            $sfield = 'first_name';
        }

        $start = ($page - 1) * $perpage;

        $sitinfo = $this->conn->prepare('SELECT id, first_name, last_name, admin_status, lastlogin from users '.$where.' order by '.$sfield.' '.$sort.' limit ?,?');
        $sitinfo->bind_param('ii', $start, $perpage);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $fname, $lname, $adminstat, $lastlogin);
        $response['users'] = array();
        if ($res) {
            while ($sitinfo->fetch()) {
                $user['id'] = $id;

                $user['username'] = $fname.' '.$lname;
                $user['admin_status'] = $adminstat;
                $user['lastlogin'] = date('m-d-Y', strtotime($lastlogin));

                array_push($response['users'], $user);
            }

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();
        $response['total'] = $totalusers;

        return $response;
    }

    public function getHomeUsersList($sort, $sfield, $page, $perpage)
    {
        $gettotal = $this->conn->prepare('SELECT id, gender, first_name, last_name, email, phone, location, age, images from users');
        $gettotal->execute();
        $gettotal->store_result();
        $totalusers = $gettotal->num_rows;
        $gettotal->close();

        if ('srno' == $sfield) {
            $sfield = 'id';
        }
        if ('username' == $sfield) {
            $sfield = 'first_name';
        }

        $start = ($page - 1) * $perpage;

        $sitinfo = $this->conn->prepare("SELECT id, gender, first_name, last_name, email, phone, location, age, status, images from users where gender='Female' order by ".$sfield.' '.$sort.' limit ?,?');
        $sitinfo->bind_param('ii', $start, $perpage);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $gender, $fname, $lname, $email, $phone, $location, $age, $status, $images);
        $response['users'] = array();
        if ($res) {
            $uc = 0;
            while ($sitinfo->fetch()) {
                ++$uc;
                if (!empty($images)) {
                    $imageuser = unserialize($images);
                    if (count($imageuser) > 0) {
                        $fimguser = $imageuser[0];
                    } else {
                        $fimguser = '';
                    }
                } else {
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
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();
        $response['total'] = $totalusers;

        return $response;
    }

    public function getCmsPages($sort, $sfield, $page, $perpage)
    {
        $gettotal = $this->conn->prepare('SELECT id, title from cms_pages');
        $gettotal->execute();
        $gettotal->store_result();
        $totalusers = $gettotal->num_rows;
        $gettotal->close();

        $start = ($page - 1) * $perpage;

        $sitinfo = $this->conn->prepare('SELECT id, title from cms_pages order by '.$sfield.' '.$sort.' limit ?,?');
        $sitinfo->bind_param('ii', $start, $perpage);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $title);
        $response['users'] = array();
        if ($res) {
            $uc = 0;
            while ($sitinfo->fetch()) {
                ++$uc;

                $user['srno'] = $uc;
                $user['id'] = $id;
                $user['title'] = $title;

                array_push($response['users'], $user);
            }

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();
        $response['total'] = $totalusers;

        return $response;
    }

    public function cmslist()
    {
        $stmt = $this->conn->prepare('SELECT * from cms_pages');
        if ($stmt->execute()) {
            $cmslist = $stmt->get_result();
            $stmt->close();

            return $cmslist;
        } else {
            return null;
        }
    }

    public function getrecentlisting()
    {
        $stmt = $this->conn->prepare("SELECT id,first_name,last_name,username,location,age,highlight,status,images from users where gender='Female' and status=3 and admin_status=1 and pause_time=1 and wallet_amount>0 ORDER BY id DESC limit 3");
        if ($stmt->execute()) {
            $stmt->bind_result($id, $first_name, $last_name, $username, $location, $age, $highlight, $status, $images);
            $response['highlight'] = array();
            while ($stmt->fetch()) {
                $user = array();
                $user['id'] = $id;
                $user['name'] = $first_name.' '.$last_name;
                $user['username'] = $username;
                $user['location'] = $location;
                $user['highlight'] = $highlight;
                $user['status'] = $status;
                $user['age'] = $age;
                $user['profile_pic'] = unserialize($images);
                array_push($response['highlight'], $user);
            }
            $stmt->close();

            return $response['highlight'];
        } else {
            return null;
        }
    }

    public function getsuburbs($state)
    {
        $stmt = $this->conn->prepare("SELECT distinct suburb from users where loc_state='".$state."'");
        if ($stmt->execute()) {
            $stmt->bind_result($sarea);
            $response = array();
            while ($stmt->fetch()) {
                $sar = array();
                $sar['id'] = $sarea;
                $sar['name'] = $sarea;
                array_push($response, $sar);
            }
            $stmt->close();

            return $response;
        } else {
            return null;
        }
    }

    public function gethighlightprofile()
    {
        $stmt = $this->conn->prepare("SELECT id,first_name,last_name,username,weight,height,gender,age,email,phone,sexual,skype,whatsapp,viber,wechat,location,service_location,aboutme,highlight,status,images, comment, complaint from users where gender='Female' and highlight=1 and status=3 and admin_status=1 and pause_time=1 and wallet_amount>0");
        if ($stmt->execute()) {
            $stmt->bind_result($id, $first_name, $last_name, $username, $weight, $height, $gender, $age, $email, $phone, $sexual, $skype, $whatsapp, $viber, $wechat, $location, $service_location, $aboutme, $highlight, $status, $images, $comment, $complaint);
            $response['highlight'] = array();
            while ($stmt->fetch()) {
                $user = array();
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
                $user['profile_pic'] = unserialize($images);
                $user['comment'] = $comment;
                $user['complaint'] = $complaint;
                array_push($response['highlight'], $user);
            }
            $stmt->close();

            return $response['highlight'];
        } else {
            return null;
        }
    }

    public function checkWallet($uid)
    {
        $stlatlon = $this->conn->prepare('SELECT wallet_amount from users where id=?');
        $stlatlon->bind_param('i', $uid);
        $res = $stlatlon->execute();
        $stlatlon->bind_result($walletamount);
        $stlatlon->fetch();
        $stlatlon->close();
        $retval = array();
        if ($res) {
            $retval['stat'] = 1;
            $retval['amount'] = $walletamount;
        } else {
            $retval['stat'] = 2;
        }

        return $retval;
    }

    public function profileList($state, $name, $height, $weight, $lat, $long, $rad, $subs)
    {
        $where = '';
        $R = 3959;
        if (!empty($rad)) {
            if (!empty($lat) && !empty($long)) {
                $maxLat = $lat + rad2deg($rad / $R);
                $minLat = $lat - rad2deg($rad / $R);
                $maxLon = $long + rad2deg(asin($rad / $R) / cos(deg2rad($lat)));
                $minLon = $long - rad2deg(asin($rad / $R) / cos(deg2rad($lat)));
                //$where .= ' and acos(sin('.$lat.')*sin(radians(loc_lat)) + cos('.$lat.')*cos(radians(loc_lat))*cos(radians(loc_long)-'.$long.')) * 3958.756 < '.$rad.' ';
                $where .= ' and loc_lat Between '.$minLat.' And '.$maxLat.' And loc_long Between '.$minLon.' And '.$maxLon.' ';
            } else {
                $stlatlon = $this->conn->prepare("SELECT statelat, statelon from state_latlon where statename like '".$state."'");

                $stlatlon->execute();
                $stlatlon->bind_result($slat, $slon);
                $stlatlon->fetch();
                $stlatlon->close();

                $maxLat = $slat + rad2deg($rad / $R);
                $minLat = $slat - rad2deg($rad / $R);
                $maxLon = $slon + rad2deg(asin($rad / $R) / cos(deg2rad($slat)));
                $minLon = $slon - rad2deg(asin($rad / $R) / cos(deg2rad($slat)));

                // $where .= ' and loc_lat <= '.$maxLat.' And loc_long >= '.$minLon.'';

                $where .= ' and loc_lat Between '.$minLat.' And '.$maxLat.' And loc_long Between '.$minLon.' And '.$maxLon.' ';
            }
        }
        if (!empty($name)) {
            $where .= ' and first_name like "%'.$name.'%" ';
        }
        if (!empty($height)) {
            $where .= ' and height >= "'.$height.'"';
        }
        if (!empty($weight)) {
            $where .= ' and weight > "'.$weight.'"';
        }
        //   SELECT id,first_name,last_name,username,weight,height,gender,age,email,phone,sexual,skype,whatsapp,viber,wechat,location,service_location,aboutme,highlight,status,images, comment, complaint from users where gender='Female' and status=3 and admin_status=1 and pause_time=1 and wallet_amount>0 and loc_state like '%South Australia%' and loc_lat Between -38.694154884796 And -33.194347315204 And loc_long Between 136.10038668379 And 142.89633431621
        //
        // SELECT id,first_name,last_name,username,weight,height,gender,age,email,phone,sexual,skype,whatsapp,viber,wechat,location,service_location,aboutme,highlight,status,images, comment, complaint from users where gender='Female' and status=3 and admin_status=1 and pause_time=1 and wallet_amount>0 and loc_state like '%South Australia%' and loc_lat Between -32.750136784796 And -27.250329215204 And loc_long Between 133.03260883495 And 139.38569516505
        // if(count($subs) > 0){
        //   $substring = implode(',', $subs);
        //   $where .= ' and suburb in ('.$substring.')';
        // }
        if ($subs) {
            $where .= ' and suburb like "%'.$subs.'%"';
        }

        $stmt = $this->conn->prepare("SELECT id,first_name,last_name,username,weight,height,gender,age,email,phone,sexual,skype,whatsapp,viber,wechat,location,service_location,aboutme,highlight,status,images, comment, complaint from users where gender='Female' and status=3 and admin_status=1 and pause_time=1 and wallet_amount>0 and loc_state like '%".$state."%'".$where.' ');

        // acos(sin(:lat)*sin(radians(Lat)) + cos(:lat)*cos(radians(Lat))*cos(radians(Lon)-:lon)) * :R < :rad
        if ($stmt->execute()) {
            $stmt->bind_result($id, $first_name, $last_name, $username, $weight, $height, $gender, $age, $email, $phone, $sexual, $skype, $whatsapp, $viber, $wechat, $location, $service_location, $aboutme, $highlight, $status, $images, $comment, $complaint);
            $response['profiles'] = array();
            //var_dump($id);

            while ($stmt->fetch()) {
                $user = array();
                $user['id'] = $id;
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
                $user['profile_pic'] = unserialize($images);
                $user['comments'] = $comment;
                $user['complaint'] = $complaint;
                array_push($response['profiles'], $user);
            }

            $stmt->close();

            return $response['profiles'];
        } else {
            return null;
        }
    }

    public function countcomments($id = '')
    {
        // code...
        $csearch = $this->conn->prepare('SELECT * from comments WHERE girl_id = ? ');
        $csearch->bind_param('i', $id);
        $csearch->execute();
        $csearch->store_result();
        $num_rows = $csearch->num_rows;
        $csearch->close();
        if ($num_rows) {
            return $num_rows;
        } else {
            return null;
        }

        //exit;
    }

    public function updateustat($id, $stat)
    {
        $response = array();

        $update = $this->conn->prepare('UPDATE users set status=? where id=?');
        $update->bind_param('ss', $stat, $id);
        $result = $update->execute();

        $update->close();

        if ($result) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function payForUnlock($id, $amount, $unlockid)
    {
        $response = array();
        $siteinfo = $this->getSiteInfo();

        $commssion = $siteinfo['commission'];

        $update = $this->conn->prepare('UPDATE users set wallet_amount=wallet_amount-? where id=?');
        $update->bind_param('di', $amount, $id);
        $result = $update->execute();
        $update->close();

        $nowtime = date('Y-m-d H:i:s');

        $earningamount = $amount - ($amount * $commssion) / 100;

        $addearinings = $this->conn->prepare('UPDATE users set earnings=earnings+? where id=?');
        $addearinings->bind_param('di', $earningamount, $unlockid);
        $resultearning = $addearinings->execute();
        $addearinings->close();

        $logins = $this->conn->prepare("INSERT INTO transactions (user_id, amount, type, to_id, remarks, transaction_time) values ('".$id."',".$amount.",2, '".$unlockid."','Picture unlock payment','".$nowtime."')");
        // var_dump("INSERT INTO transactions (user_id, amount, type, to_id, remarks, transaction_time) values ('".$id."',".$amount.",2, '".$unlockid."','Picture unlock payment','".$nowtime."')");
        // die();
        // var_dump($logins);
        // die();
        $resins = $logins->execute();
        $logins->close();

        $earninglog = $this->conn->prepare("INSERT INTO transactions (user_id, amount, commission, totalamount, type, to_id, remarks, transaction_time) values ('".$unlockid."',".$earningamount.', '.$commssion.', '.$amount.",3, '".$unlockid."','Picture unlock payment','".$nowtime."')");
        $earninglogres = $earninglog->execute();
        $earninglog->close();
        // echo 'res'.$result.'resins'.$resins.'researn'.$resultearning.'log'.$earninglogres;
        // die();
        if ($result && $resins && $resultearning && $earninglogres) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function updateadminustat($id, $stat)
    {
        $response = array();

        $update = $this->conn->prepare('UPDATE users set admin_status=? where id=?');
        $update->bind_param('ss', $stat, $id);
        $result = $update->execute();

        $update->close();

        if ($result) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function deleteUser($ids = array())
    {
        $idsare = implode(',', $ids);
        $delete = $this->conn->prepare('DELETE from users where id in ('.$idsare.')');
        //$delete->bind_param($idsare);

        $res = $delete->execute();
        $delete->close();

        if ($res) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
    }

    public function deletesubadmin($ids = array())
    {
        $idsare = implode(',', $ids);
        $delete = $this->conn->prepare('DELETE from sub_admins where id in ('.$idsare.')');
        //$delete->bind_param($idsare);

        $res = $delete->execute();
        $delete->close();

        if ($res) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
    }

    public function inactiveAct($ids = array())
    {
        $idsare = implode(',', $ids);
        $delete = $this->conn->prepare('UPDATE users set admin_status=2 where id in ('.$idsare.')');
        //$delete->bind_param($idsare);

        $res = $delete->execute();
        $delete->close();

        if ($res) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
    }

    public function transferToWallet($ids = array())
    {
        $idsare = $ids;

        foreach ($idsare as $uid) {
            $uearning = 0;
            $getearning = $this->conn->prepare('SELECT earnings from users where id='.$uid.' ');
            //$delete->bind_param($idsare);
            $resge = $getearning->execute();
            $getearning->bind_result($uearning);
            $getearning->fetch();
            $getearning->close();

            $delete = $this->conn->prepare('UPDATE users set wallet_amount=wallet_amount+earnings, earnings=0 where id='.$uid.' ');
            //$delete->bind_param($idsare);
            $resupuser = $delete->execute();
            $delete->close();

            $nowtime = date('Y-m-d H:i:s');

            $delete = $this->conn->prepare("INSERT into transactions (user_id, amount, type, to_id, remarks, 	transaction_time) values('a1', ".$uearning.', 1, '.$uid.", 'Earnings tranfered to wallet', '".$nowtime."')");
            //$delete->bind_param($idsare);
            $resaddlog = $delete->execute();
            $delete->close();
        }

        if ($resaddlog && $resupuser && $resge) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
    }

    public function markasPaid($ids)
    {
        $idsare = $ids;

        $delete = $this->conn->prepare('UPDATE users set earnings=0 where id='.$idsare.' ');
        //$delete->bind_param($idsare);
        $resupuser = $delete->execute();
        $delete->close();

        if ($resupuser) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
    }

    public function addVisitor($ids)
    {
        $delete = $this->conn->prepare("SELECT * from userips where userip='".$ids."'");
        //$delete->bind_param($idsare);
        $resupuser = $delete->execute();
        $delete->store_result();
        $num_rows = $delete->num_rows;
        $delete->close();
        if ($num_rows > 0) {
            $response['stat'] = 0;
        } else {
            $addvisitor = $this->conn->prepare("INSERT into userips (userip) values ('".$ids."')");
            //$delete->bind_param($idsare);
            $addvisitor->execute();

            $addvisitor->close();

            $updategs = $this->conn->prepare('UPDATE general_settings set total_visitors=total_visitors+1');
            //$delete->bind_param($idsare);
            $updategs->execute();

            $updategs->close();

            $response['stat'] = 1;
        }

        return $response;
    }

    public function deleteComplaint($id)
    {
        $delete = $this->conn->prepare('DELETE from complaints where id = ?');
        $delete->bind_param('i', $id);

        $res = $delete->execute();
        $delete->close();

        if ($res) {
            $response['stat'] = 1;
            $response['res'] = $res;
        } else {
            $response['stat'] = 0;
            $response['res'] = $res;
        }

        return $response;
    }

    public function deleteComment($id)
    {
        $delete = $this->conn->prepare('DELETE from comments where id = ?');
        $delete->bind_param('i', $id);

        $res = $delete->execute();
        $delete->close();

        if ($res) {
            $response['stat'] = 1;
            $response['res'] = $res;
        } else {
            $response['stat'] = 0;
            $response['res'] = $res;
        }

        return $response;
    }

    public function deleteCmsPage($ids = array())
    {
        $idsare = implode(',', $ids);
        $delete = $this->conn->prepare('DELETE from cms_pages where id in ('.$idsare.')');
        //$delete->bind_param($idsare);

        $res = $delete->execute();
        $delete->close();

        if ($res) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
    }

    public function updateAdmin($id, $username, $email, $password)
    {
        require_once 'PassHash.php';
        $response = array();
        if (!empty($password)) {
            $new_passhash = PassHash::hash($password);
            $update = $this->conn->prepare('UPDATE admin set username=?, password=?, email=? where id=?');
            $update->bind_param('ssss', $username, $new_passhash, $email, $id);
        } else {
            $update = $this->conn->prepare('UPDATE admin set username=?, email=? where id=?');
            $update->bind_param('sss', $username, $email, $id);
        }

        $result = $update->execute();

        $update->close();
        if ($result) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function subprofileupdate($id, $username, $email, $password)
    {
        require_once 'PassHash.php';
        $response = array();
        if (!empty($password)) {
            $new_passhash = PassHash::hash($password);
            $update = $this->conn->prepare('UPDATE sub_admins set name=?, password=?, email=? where id=?');
            $update->bind_param('ssss', $username, $new_passhash, $email, $id);
        } else {
            $update = $this->conn->prepare('UPDATE sub_admins set name=?, email=? where id=?');
            $update->bind_param('sss', $username, $email, $id);
        }

        $result = $update->execute();

        $update->close();
        if ($result) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function updateAtoken($token, $id)
    {
        $response = array();

        $update = $this->conn->prepare('UPDATE admin set fcm_token=? where id=?');
        $update->bind_param('ss', $token, $id);

        $result = $update->execute();

        $update->close();
        if ($result) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function updateAlogout($id)
    {
        $response = array();
        $nowdate = date('Y-m-d H:i:s');
        $update = $this->conn->prepare('UPDATE admin set last_login=? where id=?');
        $update->bind_param('ss', $nowdate, $id);

        $result = $update->execute();

        $update->close();
        if ($result) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function updateGensets($semail, $imgprice, $highprice, $commission, $dailyamount, $logoimage, $favimage, $fboyimg, $fgirlimg)
    {
        $response = array();

        $update = $this->conn->prepare('UPDATE general_settings set support_email=?, imgprice=?, highlight_fees=?, commission=?, monthly_fees=?, favicon_icon=?, logo_img=?, boy_defimg=?, girl_defimg=?');
        $update->bind_param('sddddssss', $semail, $imgprice, $highprice, $commission, $dailyamount, $favimage, $logoimage, $fboyimg, $fgirlimg);

        $result = $update->execute();

        $update->close();
        if ($result) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function updateFooter($instaurl, $fburl, $linkedinurl, $tweeturl, $youtubeurl, $dribbleurl, $googlepurl, $blogurl, $fustitle, $fusdesc, $cptext)
    {
        $response = array();

        $update = $this->conn->prepare('UPDATE general_settings set blog_url=?, facebook_url=?, twitter_url=?, youtub_url=?, instagram_url=?, linkedinurl=?, dribbleurl=?, googlepurl=?, follow_title=?, follow_desc=?, copyright_text=?');
        $update->bind_param('sssssssssss', $blogurl, $fburl, $tweeturl, $youtubeurl, $instaurl, $linkedinurl, $dribbleurl, $googlepurl, $fustitle, $fusdesc, $cptext);

        $result = $update->execute();

        $update->close();
        if ($result) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function getUserById($id)
    {
        $sitinfo = $this->conn->prepare('SELECT * from users where id=?');
        $sitinfo->bind_param('i', $id);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $first_name, $last_name, $username, $weight, $height, $gender, $age, $email, $phone, $sexual, $skype, $whatsapp, $viber, $wechat, $location, $state, $suburb, $lat, $lon, $service_location, $aboutme, $videos, $highlight, $oldpt, $password, $status, $admin_status, $pausetime, $walletamount, $updatedtime, $pausedattime, $earnings, $lastlogin, $api_key, $images, $comment, $complaint, $verificationcode);
        $sitinfo->fetch();
        if ($res) {
            $user['id'] = $id;
            $user['name'] = $first_name.' '.$last_name;
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['username'] = $username;
            $user['weight'] = $weight;
            $user['height'] = $height;
            $user['age'] = $age;
            $user['gender'] = $gender;
            $user['email'] = $email;
            $pt = explode('+91', $phone);
            if (isset($pt[1])) {
                $user['phone'] = $pt[1];
            } else {
                $user['phone'] = $phone;
            }

            $user['sexual'] = $sexual;
            $user['skype'] = $skype;
            $user['whatsapp'] = $whatsapp;
            $user['viber'] = $viber;
            $user['wechat'] = $wechat;
            $user['location'] = $location;
            $user['state'] = $state;
            $user['suburb'] = $suburb;
            $user['lat'] = $lat;
            $user['lon'] = $lon;
            $user['service_location'] = $service_location;
            $user['aboutme'] = $aboutme;
            $user['highlight'] = $highlight;
            $user['walletamount'] = $walletamount;
            if ($updatedtime > 0) {
                $user['updated_time'] = date('M j, Y H:i:s', strtotime('+1 day', strtotime($updatedtime)));
            } else {
                $user['updated_time'] = false;
            }
            $user['curdate'] = date('M j, Y H:i:s');
            // Feb 28, 2018 14:52:25
            $user['pausedat_time'] = $pausedattime;
            $user['earnings'] = $earnings;
            $user['status'] = $status;
            $user['pausetime'] = $pausetime;
            $user['profile_pic'] = unserialize($images);
            $user['videos'] = unserialize($videos);

            $user['stat'] = 1;
        } else {
            $user['stat'] = 0;
        }
        $sitinfo->close();

        return $user;
    }

    public function getsubadmin($id)
    {
        $sitinfo = $this->conn->prepare('SELECT * from sub_admins where id=?');
        $sitinfo->bind_param('i', $id);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $name, $email, $password, $prev);
        $sitinfo->fetch();
        if ($res) {
            $user['id'] = $id;
            $user['name'] = $name;
            $user['email'] = $email;
            $user['privileges'] = unserialize($prev);
            $user['stat'] = 1;
        } else {
            $user['stat'] = 0;
        }
        $sitinfo->close();

        return $user;
    }

    public function updatesubadmin($uid, $user)
    {
        require_once 'PassHash.php';

        $return = array('status' => 0, 'message' => 'Something went wrong');

        $checkemail = $this->conn->prepare('SELECT id from sub_admins where email=? and id != ?');
        $checkemail->bind_param('si', $user['email'], $uid);
        $checkemail->execute();
        $checkemail->bind_result($id);
        $checkemail->fetch();
        $checkemail->close();

        if (!empty($id)) {
            $return['status'] = 0;
            $return['message'] = 'Email already exists';
        } else {
            $prev = serialize($user['privileges']);

            if (empty($user['password'])) {
                $update = $this->conn->prepare('UPDATE sub_admins set name=?, email=?, privileges=? where id=?');
                $update->bind_param('sssi', $user['firstName'], $user['email'], $prev, $uid);
            } else {
                $new_passhash = PassHash::hash($user['password']);

                $update = $this->conn->prepare('UPDATE sub_admins set name=?, email=?, password=?, privileges=? where id=?');
                $update->bind_param('ssssi', $user['firstName'], $user['email'], $new_passhash, $prev, $uid);
            }

            $ups = $update->execute();
            $update->close();

            if ($ups) {
                $return['status'] = 1;
                $return['message'] = 'Succesfully updated';
            } else {
                $return['status'] = 0;
                $return['message'] = 'Could not update information at this time';
            }
        }

        return $return;
    }

    public function getptestimonials($id = '')
    {
        // code...
        $testimonialdata = $this->conn->prepare('SELECT * from testimonials WHERE userid= ? ');
        $testimonialdata->bind_param('i', $id);
        $res = $testimonialdata->execute();
        $testimonialdata->bind_result($id, $userid, $user_number, $nickname, $comment);

        $response['testimonials'] = array();
        if ($res) {
            while ($testimonialdata->fetch()) {
                $testimonial['id'] = $id;
                $testimonial['userid'] = $userid;
                $testimonial['nickname'] = $nickname;
                $testimonial['comment'] = $comment;
                array_push($response['testimonials'], $testimonial);
            }
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $testimonialdata->close();

        return $response;
    }

    public function searchTestonomy($id, $number)
    {
        // code...
        $testimonialdata = $this->conn->prepare('SELECT * from testimonials WHERE userid='.$id." and user_number='".$number."' ");
        // $testimonialdata->bind_param("is", $id, $number);
        $res = $testimonialdata->execute();
        $testimonialdata->bind_result($id, $userid, $user_number, $nickname, $comment);

        $response['testimonials'] = array();
        if ($res) {
            while ($testimonialdata->fetch()) {
                $testimonial['id'] = $id;
                $testimonial['userid'] = $userid;
                $testimonial['nickname'] = $nickname;
                $testimonial['comment'] = $comment;
                array_push($response['testimonials'], $testimonial);
            }
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $testimonialdata->close();

        return $response;
    }

    public function getcomments($id = '')
    {
        // code...
        $comments = $this->conn->prepare('SELECT comments.*,users.first_name,users.last_name,users.username,users.images FROM comments left JOIN users ON comments.user_id = users.id WHERE girl_id=?');
        $comments->bind_param('i', $id);
        $result = $comments->execute();
        $comments->bind_result($id, $girl_id, $user_id, $addedby, $date, $description, $first_name, $last_name, $username, $images);
        $response = array();
        if ($result) {
            while ($comments->fetch()) {
                $cmt['id'] = $id;
                $cmt['user_id'] = $user_id;
                $cmt['girl_id'] = $girl_id;
                $cmt['date'] = $date;
                $cmt['description'] = $description;
                $cmt['first_name'] = $first_name;
                $cmt['last_name'] = $last_name;
                $cmt['username'] = $username;
                $cmt['addedby'] = $addedby;
                $cmt['images'] = unserialize($images);
                array_push($response, $cmt);
            }
        }
        $comments->close();
        if ($response) {
            return $response;
        } else {
            return null;
        }
    }

    public function getcomplaints($id = '')
    {
        // code...
        $comments = $this->conn->prepare('SELECT complaints.*,users.first_name,users.last_name,users.username,users.images FROM complaints left JOIN users ON complaints.user_id = users.id WHERE girl_id=?');
        $comments->bind_param('i', $id);
        $result = $comments->execute();
        $comments->bind_result($id, $girl_id, $user_id, $addedby, $description, $date, $first_name, $last_name, $username, $images);
        $response = array();
        if ($result) {
            while ($comments->fetch()) {
                $cmt['id'] = $id;
                $cmt['user_id'] = $user_id;
                $cmt['girl_id'] = $girl_id;
                $cmt['date'] = $date;
                $cmt['description'] = $description;
                $cmt['first_name'] = $first_name;
                $cmt['last_name'] = $last_name;
                $cmt['username'] = $username;
                $cmt['addedby'] = $addedby;
                $cmt['images'] = unserialize($images);
                array_push($response, $cmt);
            }
        }
        $comments->close();
        if ($response) {
            return $response;
        } else {
            return null;
        }
    }

    public function getAllUsers()
    {
        $sitinfo = $this->conn->prepare('SELECT id, first_name, last_name from users ');
        //$sitinfo->bind_param("i", $id);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $firstname, $lastname);

        $response['users'] = array();
        if ($res) {
            while ($sitinfo->fetch()) {
                $user['id'] = $id;
                $user['name'] = $firstname.' '.$lastname;

                array_push($response['users'], $user);
            }

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();

        return $response;
    }

    public function getGirlUsers()
    {
        $sitinfo = $this->conn->prepare("SELECT id, first_name, last_name from users where gender='Female'");
        //$sitinfo->bind_param("i", $id);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $firstname, $lastname);

        $response['users'] = array();
        if ($res) {
            while ($sitinfo->fetch()) {
                $user['id'] = $id;
                $user['name'] = $firstname.' '.$lastname;

                array_push($response['users'], $user);
            }

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();

        return $response;
    }

    public function getCmsPage($id)
    {
        $sitinfo = $this->conn->prepare('SELECT * from cms_pages where id=?');
        $sitinfo->bind_param('i', $id);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $title, $handle, $bannerimage, $headerimg, $postimg, $desc, $htitle, $ftdesc, $location, $callus, $seotitle, $seometa, $seokw, $seoana, $seodesc, $disstat);
        $sitinfo->fetch();
        if ($res) {
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
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();

        return $response;
    }

    public function checkUserWithId($email, $id)
    {
        $stmt = $this->conn->prepare('SELECT id from users WHERE  email = ? and id != ?');
        $stmt->bind_param('ss', $email, $id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();

        return $num_rows > 0;
    }

    public function updateUserById($firstname, $lastname, $email, $phone, $age, $sex, $location, $service, $aboutme, $status, $activation, $password, $profile, $gender, $id, $lat, $long, $suburb, $state)
    {
        require_once 'PassHash.php';
        $response = array();

        if (!$this->checkUserWithId($email, $id)) {
            // if($gender == 'boy'){
            //   $fg = 'Male';
            // } else{
            //   $fg = 'Female';
            // }
            if (!empty($password)) {
                $new_passhash = PassHash::hash($password);
                $update = $this->conn->prepare('UPDATE users set first_name=?, last_name=?, password=?, email=?, gender=?, phone=?, location=?, loc_state=?, suburb=?, loc_lat=?, loc_long=?, service_location=?, sexual=?, age=?, aboutme=?, status=?, images=? where id=?');
                $update->bind_param('ssssssssssssssssss', $firstname, $lastname, $new_passhash, $email, $gender, $phone, $location, $state, $suburb, $lat, $long, $service, $sex, $age, $aboutme, $status, $profile, $id);
            } else {
                $update = $this->conn->prepare('UPDATE users set first_name=?, last_name=?, email=?, gender=?, phone=?, location=?, loc_state=?, suburb=?, loc_lat=?, loc_long=?, service_location=?, sexual=?, age=?, aboutme=?, status=?, images=? where id=?');
                $update->bind_param('sssssssssssssssss', $firstname, $lastname, $email, $gender, $phone, $location, $state, $suburb, $lat, $long, $service, $sex, $age, $aboutme, $status, $profile, $id);
            }

            $result = $update->execute();

            $update->close();

            if ($result) {
                $response['stat'] = 1;
            } else {
                $response['stat'] = 0;
            }
        } else {
            $response['stat'] = 2;
        }

        return $response;
    }

    /**
     * Updating task.
     *
     * @param string $task_id id of the task
     * @param string $task    task text
     * @param string $status  task status
     */
    public function updateUser($userid, $firstname, $lastname, $username, $weight, $height, $gender, $age, $email, $phone, $sex, $skype, $whatsapp, $viber, $wechat, $location, $service, $aboutme, $highlight, $pausetime, $profile, $videos, $state, $suburb, $lat, $lon)
    {
        $aboutme = addslashes($aboutme);
        $height = addslashes($height);
        $stmt = $this->conn->prepare("UPDATE users SET first_name='".$firstname."',last_name='".$lastname."',username='".$username."',weight='".$weight."',height='".$height."',gender='".$gender."', age=".$age.",email='".$email."', phone='".$phone."',sexual='".$sex."',skype='".$skype."',whatsapp='".$whatsapp."',viber='".$viber."',wechat='".$wechat."',location='".$location."', loc_state='".$state."', suburb='".$suburb."', loc_lat='".$lat."', loc_long='".$lon."', service_location='".$service."', aboutme='".$aboutme."', videos='".$videos."', highlight='".$highlight."', pause_time='".$pausetime."', images='".$profile."' WHERE id= ? ");
        // videos='".$videos."',
        $stmt->bind_param('d', $userid);

        $result = $stmt->execute();

        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();
        if ($result) {
            $retval = 1;
        } else {
            $retval = 0;
        }

        return $retval;
    }

    public function updateptime($uid, $ptime)
    {
        $stmt = $this->conn->prepare("UPDATE users SET pausedat_time='".$ptime."' WHERE id= ? ");
        // videos='".$videos."',
        $stmt->bind_param('i', $uid);

        $result = $stmt->execute();

        $stmt->close();
        if ($result) {
            $retval = 1;
        } else {
            $retval = 0;
        }

        return $retval;
    }

    public function getFrontPackages($gender)
    {
        if ('Female' == $gender) {
            $for = 2;
        } else {
            $for = 1;
        }

        $sitinfo = $this->conn->prepare('SELECT * from packages where package_for=?');
        $sitinfo->bind_param('i', $for);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $name, $desc, $bonus, $price, $pfor);

        if ($res) {
            $response['packages'] = array();

            while ($sitinfo->fetch()) {
                $user['id'] = $id;
                $user['name'] = $name;
                $user['desc'] = $desc;
                $user['bonus'] = $bonus;
                $user['price'] = $price;

                array_push($response['packages'], $user);
            }

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();

        return $response;
    }

    public function updatePage($id, $title, $handle, $desc, $seotitle, $seokey, $seodesc, $bannerimg, $postimg, $headerimg, $ftdesc, $htitle, $location, $callus)
    {
        $response = array();

        $update = $this->conn->prepare('UPDATE cms_pages set title=?, handle=?, banner_image=?, headerimg=?, postimg=?, detail=?, htitle=?, ft_detail=?, location=?, call_us=?, seo_title=?, seo_keywords=?, seo_detail=? where id=?');
        $update->bind_param('ssssssssssssss', $title, $handle, $bannerimg, $headerimg, $postimg, $desc, $htitle, $ftdesc, $location, $callus, $seotitle, $seokey, $seodesc, $id);

        $result = $update->execute();

        $update->close();

        if ($result) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function addNewPackage($packagefor, $packagename, $desc, $bonus, $price)
    {
        $response = array();

        $update = $this->conn->prepare('INSERT INTO packages (name, package_desc, bonus, price, package_for) values(?, ?, ?, ?, ?)');
        $update->bind_param('sssss', $packagename, $desc, $bonus, $price, $packagefor);

        $result = $update->execute();

        $update->close();

        if ($result) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function getAllPackages($sort, $sfield, $page, $perpage)
    {
        $gettotal = $this->conn->prepare('SELECT * from packages');
        $gettotal->execute();
        $gettotal->store_result();
        $totalusers = $gettotal->num_rows;
        $gettotal->close();

        $start = ($page - 1) * $perpage;

        $sitinfo = $this->conn->prepare('SELECT * from packages order by '.$sfield.' '.$sort.' limit ?,?');
        $sitinfo->bind_param('ii', $start, $perpage);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $name, $desc, $bonus, $price, $pfor);
        $response['users'] = array();
        if ($res) {
            $uc = 0;
            while ($sitinfo->fetch()) {
                ++$uc;

                $user['srno'] = $uc;
                $user['id'] = $id;
                $user['name'] = $name;
                $user['bonus'] = $bonus;
                $user['price'] = $price;
                $user['pfor'] = $pfor;

                array_push($response['users'], $user);
            }

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();
        $response['total'] = $totalusers;

        return $response;
    }

    public function deletePackages($ids = array())
    {
        $idsare = implode(',', $ids);
        $delete = $this->conn->prepare('DELETE from packages where id in ('.$idsare.')');
        //$delete->bind_param($idsare);

        $res = $delete->execute();
        $delete->close();

        if ($res) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
    }

    public function getPackage($id)
    {
        $sitinfo = $this->conn->prepare('SELECT * from packages where id=?');
        $sitinfo->bind_param('i', $id);
        $res = $sitinfo->execute();
        $sitinfo->bind_result($id, $name, $desc, $bonus, $price, $pfor);
        $sitinfo->fetch();
        if ($res) {
            $response['id'] = $id;
            $response['packagename'] = $name;
            $response['desc'] = $desc;
            $response['bonus'] = $bonus;
            $response['price'] = $price;
            $response['packagefor'] = $pfor;

            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }
        $sitinfo->close();

        return $response;
    }

    public function updatePackage($id, $packagefor, $packagename, $desc, $bonus, $price)
    {
        $response = array();

        $update = $this->conn->prepare('UPDATE packages set name=?, package_desc=?, bonus=?, price=?, package_for=? where id=?');
        $update->bind_param('ssssss', $packagename, $desc, $bonus, $price, $packagefor, $id);

        $result = $update->execute();

        $update->close();

        if ($result) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    // Front side data
    public function createUser($firstname, $lastname, $email, $phone, $age, $sex, $location, $state, $service, $aboutme, $status, $activation, $password, $profile, $gender, $adminadd, $lat, $long, $suburb)
    {
        require_once 'PassHash.php';
        $response = array();

        // First check if user already existed in db
        if (!$this->isUserExists($email)) {
            // Generating password hash
            $password_hash = PassHash::hash($password);

            // Generating API key
            $api_key = $this->generateApiKey();
            $verification_code = rand(1000, 9999);
            $phonetemp = $phone;
            $phonesend = $phonetemp;
            $phone = $phone;
            $phoneMessage = 'Your Dating website verification code is : '.$verification_code;
            if (!$adminadd) {
                $resp = $this->sendMessage($phonesend, $phoneMessage);
            }

            // insert query
            $adminstat = 1;
            $pt = 1;
            $stmt = $this->conn->prepare('INSERT INTO users (gender, first_name,last_name,email,phone, location,loc_state,suburb,loc_lat,loc_long,service_location,height,age,aboutme,password,status, admin_status, pause_time, api_key,verificationcode,images) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->bind_param('ssssssssssssdsssddsds', $gender, $firstname, $lastname, $email, $phone, $location, $state, $suburb, $lat, $long, $service, $sex, $age, $aboutme, $password_hash, $status, $adminstat, $pt, $activation, $verification_code, $profile);

            $result = $stmt->execute();
            $stmt->close();

            // Check for successful insertion
            if ($adminadd) {
                if ($result) {
                    // User successfully inserted
                    return USER_CREATED_SUCCESSFULLY;
                } else {
                    // Failed to create user
                    return USER_CREATE_FAILED;
                }
            } else {
                if ($result) {
                    // User successfully inserted
                    $retresp['status'] = USER_CREATED_SUCCESSFULLY;
                    $retresp['user'] = array('firstname' => $firstname, 'email' => $email, 'vcode' => $verification_code);

                    $getadmintoken = $this->getFcmToken();
                    // User successfully inserted
                    $retresp['notify'] = $this->sendnotification($getadmintoken, 'New user has registered.', 'New user registered');

                    return $retresp;
                } else {
                    // Failed to create user
                    $retresp['status'] = USER_CREATE_FAILED;
                    $retresp['phonesend'] = $phonesend;

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

    public function getFcmToken()
    {
        $stmt = $this->conn->prepare('SELECT fcm_token FROM admin WHERE id = 1');
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($fcmtoken);
            $stmt->fetch();
            $token = $fcmtoken;

            $stmt->close();

            return $token;
        } else {
            return null;
        }
    }

    public function sendnotification($token_array, $message, $title)
    {
        // curl https://fcm.googleapis.com/fcm/send -H "Content-Type: application/json" -H "Authorization: key=AAAAnWrZ6Xs:APA91bFM1h9IKQYuFLdUUiw4r74Aqcv3ULZDuvWTAwehGe-XMMAt_u9fxFVi2g0Dp2hdnK8ujaGHPo7lXP8xzcuXKHr05phArFwKYgN3Q4SqeQ4hp2g7aM56QiUlMjdFI5g1uilJZEr6" -d '{ "notification": {"title": "Test title", "body": "Test Body", "click_action" : "https://angularfirebase.com"},"to" : "eufdmTGgJ74:APA91bG148GwfuJ4z1Y67ZKsIsk170Fo9K34_cFG18s4o1LJh0BA-qwb1r2XTgIFteszd5ASf_8kE_ege53j_z-S6v_ILI5RQqsHLN8P5h6U9PL3joBVBHJupWiIWrUaTpp8ukgX5E0i"}'

        $API_ACCESS_KEY = 'AAAAnWrZ6Xs:APA91bFM1h9IKQYuFLdUUiw4r74Aqcv3ULZDuvWTAwehGe-XMMAt_u9fxFVi2g0Dp2hdnK8ujaGHPo7lXP8xzcuXKHr05phArFwKYgN3Q4SqeQ4hp2g7aM56QiUlMjdFI5g1uilJZEr6';

        // Set POST variables
        $url = 'https://fcm.googleapis.com/fcm/send';

        $fields = array(
            'to' => $token_array,
            'notification' => array('title' => $title, 'body' => $message),
        );

        $headers = array(
            'Authorization: key='.$API_ACCESS_KEY,
            'Content-Type: application/json',
        );

        // Open connection
        // print_r($headers);
        $ch = curl_init();

        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

        // Execute post
        $result = curl_exec($ch);
        //print_r($result);exit();
        if (false === $result) {
            //die('Curl failed: ' . curl_error($ch));
            return $result;
        }

        // Close connection
        curl_close($ch);

        if ('New user registered' == $title) {
            $type = 1;
            $stat = 1;
        } elseif ('New comment' == $title) {
            $type = 2;
            $stat = 1;
        } else {
            $type = 3;
            $stat = 1;
        }
        $noedate = date('Y-m-d H:i:s');

        $stmt = $this->conn->prepare('INSERT INTO admin_notifications (notification_text, type, status, added_at) values(?, ?, ?, ?)');
        $stmt->bind_param('sdds', $message, $type, $stat, $noedate);
        $result = $stmt->execute();
        $stmt->close();

        return $result;
    }

    /**
     * Checking user login.
     *
     * @param string $email    User login email id
     * @param string $password User login password
     *
     * @return bool User login status success/fail
     */
    public function sendMessage($phone, $message)
    {
        include_once './vendor/autoload.php';
        $account_sid = 'AC7cd943f372b6a1872eace265af042ee8';
        $auth_token = '86ea649d49f34168f44cd509295451f3';

        $client = new Twilio\Rest\Client($account_sid, $auth_token);

        try {
            $message_tw = $client->account->messages->create(array(
                'To' => $phone,
                'From' => '+61418655602',
                'Body' => $message,
            ));
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function insertverificationcode($userid)
    {
        /* $stmt = $this->conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param('ss',$password_hash,$userid);
    $stmt->execute();
    $num_affected_rows = $stmt->affected_rows;
    $stmt->close();
    return $num_affected_rows > 0;*/
    }

    public function checkLogin($email, $upassword)
    {
        // fetching user by email
        $stmt = $this->conn->prepare('SELECT password FROM users WHERE email = ?');

        $stmt->bind_param('s', $email);

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
                return true;
            } else {
                // user password is incorrect
                return false;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return false;
        }
    }

    /**
     * Fetching user by email.
     *
     * @param string $email User email id
     */
    public function getUserByEmail($email)
    {
        $stmt = $this->conn->prepare('SELECT  id,first_name,last_name,email,phone,gender,location,service_location,sexual,age,aboutme,password,status, admin_status,api_key,images FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id, $first_name, $last_name, $email, $phone, $gender, $location, $service_location, $sexual, $age, $aboutme, $password, $status, $adminstat, $api_key, $images);
            $stmt->fetch();
            $user = array();
            $user['id'] = $id;
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['email'] = $email;
            $user['phone'] = $phone;
            $user['gender'] = $gender;
            $user['location'] = $location;
            $user['service_location'] = $service_location;
            $user['sexual'] = $sexual;
            $user['age'] = $age;
            $user['aboutme'] = $aboutme;
            $user['password'] = $password;
            $user['api_key'] = $api_key;
            $user['status'] = $status;
            $user['adminstat'] = $adminstat;
            $user['images'] = unserialize($images);

            $stmt->close();

            return $user;
        } else {
            return null;
        }
    }

    public function resendcode($email = '')
    {
        // code...
        $stmt = $this->conn->prepare('SELECT  id,email,phone,status FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id, $email, $phone, $status);
            $stmt->fetch();
            $user = array();
            $user['id'] = $id;
            $user['email'] = $email;
            $user['phone'] = $phone;

            $user['status'] = $status;
            $verification_code = rand(1000, 9999);
            $phone = $user['phone'];

            $phoneMessage = 'Your Dating website verification code is : '.$verification_code;
            $resp = $this->sendMessage($phone, $phoneMessage);
            $user['sendmessage'] = $resp;
            $user['verification_code'] = $verification_code;
            $stmt->close();

            return $user;
        } else {
            return null;
        }
    }

    public function updatecode($id = '', $code = '')
    {
        // code...
        $response = array();

        $update = $this->conn->prepare('UPDATE users set verificationcode=? where id=?');
        $update->bind_param('ss', $code, $id);
        $result = $update->execute();

        $update->close();

        if ($result) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    /**
     * Fetching user by email.
     *
     * @param string $email User email id
     */
    public function getverify($email, $textverify)
    {
        $stmt = $this->conn->prepare('SELECT  id,first_name,last_name,email,phone,location,service_location,sexual,gender,age,aboutme,password,status,api_key,images FROM users WHERE email = ? AND verificationcode = ?');
        $stmt->bind_param('ss', $email, $textverify);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id, $first_name, $last_name, $email, $phone, $location, $service_location, $sexual, $gender, $age, $aboutme, $password, $status, $api_key, $images);

            $stmt->store_result();
            $numrows = $stmt->num_rows;

            if (1 == $numrows) {
                $user = array();
                $stmt->fetch();
                $user['id'] = $id;
                //echo $id;
                $user['first_name'] = $first_name;
                $user['last_name'] = $last_name;
                $user['email'] = $email;
                $user['phone'] = $phone;
                $user['location'] = $location;
                $user['service_location'] = $service_location;
                $user['sexual'] = $sexual;
                $user['gender'] = $gender;
                $user['age'] = $age;
                $user['aboutme'] = $aboutme;
                $user['password'] = $password;
                $user['api_key'] = $api_key;
                $user['images'] = $images;

                return $user;
            } else {
                return null;
            }
            $stmt->close();
        } else {
            return null;
        }
    }

    public function updatepassword($userid, $password)
    {
        require_once 'PassHash.php';
        // Generating password hash
        $password_hash = PassHash::hash($password);

        $stmt = $this->conn->prepare('UPDATE users SET password=? WHERE id=?');
        $stmt->bind_param('ss', $password_hash, $userid);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();

        return $num_affected_rows > 0;
    }

    public function addNewTestinomy($girlid, $boynumber, $name, $testinomy)
    {
        $response = array();

        $update = $this->conn->prepare('INSERT INTO testimonials (userid, user_number, nickname, comment) values(?, ?, ?, ?)');
        $update->bind_param('ssss', $girlid, $boynumber, $name, $testinomy);

        $result = $update->execute();

        $update->close();

        if ($result) {
            $response['stat'] = 1;
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    public function updatePayment($userid, $amount, $type, $timedone, $highlight)
    {
        $response = array();
        $nowtime = date('Y-m-d H:i:s');
        $update = $this->conn->prepare('INSERT INTO transactions (amount, type, to_id, transaction_time) values(?, ?, ?, ?)');
        $update->bind_param('diss', $amount, $type, $userid, $timedone);

        $result = $update->execute();

        $update->close();

        if ($result) {
            $getrega = $this->conn->prepare('SELECT monthly_fees from general_settings');
            $getrega->execute();
            $getrega->bind_result($monfees);
            $getrega->fetch();
            $getrega->close();
            $amount = $amount - $monfees;

            $stmt = $this->conn->prepare('UPDATE users set wallet_amount=wallet_amount+'.$amount.', highlight='.$highlight.",updated_time='".$nowtime."' where id=?");
            $stmt->bind_param('i', $userid);
            $result2 = $stmt->execute();
            $stmt->close();

            $getwallet = $this->conn->prepare('SELECT  wallet_amount FROM users WHERE id = ?');
            $getwallet->bind_param('i', $userid);
            if ($getwallet->execute()) {
                // $user = $stmt->get_result()->fetch_assoc();
                $getwallet->bind_result($wamount);
                $getwallet->fetch();

                $response['wallet'] = $wamount;

                $getwallet->close();
            }

            if ($result2) {
                $response['stat'] = 1;
            } else {
                $response['stat'] = 0;
            }
        } else {
            $response['stat'] = 0;
        }

        return $response;
    }

    //end dating website

    public function createSocialUser($firstname, $email = '', $socialid, $phone, $status)
    {
        require_once 'PassHash.php';
        $response = array();
        $name = explode(' ', $firstname);
        $fname = $name[0];
        $lname = '';
        if (!empty($name[1])) {
            $lname = $name[1];
        }
        // First check if user already existed in db
        if (!$this->isUserExists($socialid)) {
            // Generating API key
            $api_key = $this->generateApiKey();

            // insert query
            $stmt = $this->conn->prepare('INSERT INTO users (first_name,last_name,email,api_key,phone,status) values(?,?,?,?,?,?)');
            $stmt->bind_param('ssssss', $fname, $lname, $email, $socialid, $phone, $status);

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

    public function loginSocial($firstname, $email = '', $socialid, $phone, $status)
    {
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

    public function submitContact($firstname, $lastname, $email, $phone)
    {
        // First check if user already existed in db
        // insert query
        $stmt = $this->conn->prepare('INSERT INTO leads (user_name,user_email,lead_type) values(?,?,1)');
        $username = $firstname.$lastname;
        $stmt->bind_param('ss', $username, $email);
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

    public function submitsellContact($firstname, $lastname, $email, $phone)
    {
        // First check if user already existed in db
        // insert query
        $stmt = $this->conn->prepare('INSERT INTO leads (user_name,user_email,lead_type) values(?,?,2)');
        $username = $firstname.$lastname;
        $stmt->bind_param('ss', $username, $email);
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

    public function submitmorehome($firstname, $lastname, $email, $phone)
    {
        // First check if user already existed in db
        // insert query
        $stmt = $this->conn->prepare('INSERT INTO leads (user_name,user_email,lead_type) values(?,?,3)');
        $username = $firstname.$lastname;
        $stmt->bind_param('ss', $username, $email);
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

    public function submitreqshowing($firstname, $lastname, $email, $phone)
    {
        // First check if user already existed in db
        // insert query
        $stmt = $this->conn->prepare('INSERT INTO leads (user_name,user_email,lead_type) values(?,?,6)');
        $username = $firstname.$lastname;
        $stmt->bind_param('ss', $username, $email);
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

    public function addSaveSearch($string, $emailfreq, $title, $dateadd, $userid, $objval)
    {
        $retar = array();
        $csearch = $this->conn->prepare('SELECT id from saved_searches WHERE title = ?');
        $csearch->bind_param('s', $title);
        $csearch->execute();
        $csearch->store_result();
        $num_rows = $csearch->num_rows;
        $csearch->close();
        if ($num_rows > 0) {
            $retar['val'] = 5;

            return $retar;
        } else {
            // insert query
            $stmt = $this->conn->prepare('INSERT INTO saved_searches (user_id,search_url,title,efrequecy,modelobj,dateadded) values(?,?,?,?,?,?)');
            $stmt->bind_param('ississ', $userid, $string, $title, $emailfreq, $objval, $dateadd);
            $result = $stmt->execute();
            $id = $stmt->insert_id;
            $stmt->close();
            // Check for successful insertion
            if ($result) {
                // User successfully inserted
                $retar['val'] = USER_CREATED_SUCCESSFULLY;
                $retar['id'] = $id;

                return $retar;
            } else {
                // Failed to create user
                $retar['val'] = USER_CREATE_FAILED;

                return $retar;
            }
        }
    }

    public function editSaveSearch($string, $emailfreq, $title, $dateadd, $userid, $ssid, $objval)
    {
        $retar = array();
        // insert query
        $stmt = $this->conn->prepare('UPDATE saved_searches set user_id=?, search_url=?, title=?, efrequecy=?, modelobj=? where id=?');
        $stmt->bind_param('issisi', $userid, $string, $title, $emailfreq, $objval, $ssid);
        $result = $stmt->execute();
        $id = $stmt->insert_id;
        $stmt->close();
        // Check for successful insertion
        if ($result) {
            // User successfully inserted
            $retar['val'] = USER_CREATED_SUCCESSFULLY;
            $retar['id'] = $id;

            return $retar;
        } else {
            // Failed to create user
            $retar['val'] = USER_CREATE_FAILED;

            return $retar;
        }
    }

    public function getSavedSearch($id)
    {
        $stmt = $this->conn->prepare('SELECT * FROM saved_searches WHERE id = ?');
        $stmt->bind_param('d', $id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id, $first_name, $last_name, $email, $profile_pic, $jval, $agent_bio);
            $stmt->fetch();
            $agent = array();
            $agent['id'] = $id;
            $agent['user_id'] = $first_name;
            $agent['search_url'] = $last_name;
            $agent['title'] = $email;
            $agent['efreq'] = $profile_pic;
            $agent['mval'] = json_decode(unserialize($jval));
            //  var_dump(json_decode(unserialize($jval)));
            //  exit();
            $agent['dadded'] = $agent_bio;

            $stmt->close();

            return $agent;
        } else {
            return null;
        }
    }

    public function getAllsSearch($userid)
    {
        $stmt = $this->conn->prepare('SELECT * from saved_searches where user_id=? order by id desc');
        /* select count(*) msgcount, id from messages where parent_id >0 GROUP BY parent_id ORDER BY lastreply_date DESC */
        $stmt->bind_param('d', $userid);
        if ($stmt->execute()) {
            $messagelist = $stmt->get_result();
            $stmt->close();

            return $messagelist;
        } else {
            return null;
        }
    }

    public function deleteSavedSearch($subject_id)
    {
        $stmt = $this->conn->prepare('delete from saved_searches WHERE id =?');
        $stmt->bind_param('i', $subject_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();

        return $num_affected_rows > 0;
    }

    public function inpersonAssessment($firstname, $lastname, $email, $phone)
    {
        // First check if user already existed in db
        // insert query
        $stmt = $this->conn->prepare('INSERT INTO leads (user_name,user_email,lead_type) values(?,?,5)');
        $username = $firstname.$lastname;
        $stmt->bind_param('ss', $username, $email);
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

    public function submitFinanceLead($firstname, $lastname, $email, $phone)
    {
        // First check if user already existed in db
        // insert query
        $stmt = $this->conn->prepare('INSERT INTO leads (user_name,user_email,lead_type) values(?,?,4)');
        $username = $firstname.$lastname;
        $stmt->bind_param('ss', $username, $email);
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
     * Checking for duplicate user by email address.
     *
     * @param string $email email to check in db
     *
     * @return bool
     */
    public function verifypassword($encrypt)
    {
        $stmt = $this->conn->prepare('SELECT password FROM users where md5(1290*3+id) = ? ');
        $stmt->bind_param('s', $encrypt);
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
        $stmt = $this->conn->prepare('SELECT email,activation FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);

        $stmt->execute();

        $stmt->bind_result($email, $activation);

        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Found user with the email
            // Now verify the password

            $stmt->fetch();

            $stmt->close();

            if ($activation == $uactivation) {
                // User email is correct
                $stmt = $this->conn->prepare('UPDATE users SET status=1 WHERE email=?');
                $stmt->bind_param('s', $email);
                $stmt->execute();
                $num_affected_rows = $stmt->affected_rows;
                $stmt->close();

                return $num_affected_rows > 0;
                // User email is correct
                return true;
            } else {
                // user email is incorrect
                return false;
            }
        } else {
            $stmt->close();

            // user not existed with the email
            return false;
        }
    }

    /**
     * Checking for duplicate user by email address.
     *
     * @param string $email email to check in db
     *
     * @return bool
     */
    private function isUserExists($email)
    {
        $stmt = $this->conn->prepare('SELECT id from users WHERE  email = ? or api_key = ?');
        $stmt->bind_param('ss', $email, $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();

        return $num_rows > 0;
    }

    public function addtofavorite($userid, $propid, $image, $address, $link)
    {
        $stmt = $this->conn->prepare('INSERT INTO favorite_properties ( userid,propertyid,image, address,link) values(?,?,?,?,?)');
        $stmt->bind_param('sssss', $userid, $propid, $image, $address, $link);
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
     * Fetching user api key.
     *
     * @param string $user_id user id primary key in user table
     */
    public function getApiKeyById($user_id)
    {
        $stmt = $this->conn->prepare('SELECT api_key FROM users WHERE id = ?');
        $stmt->bind_param('i', $user_id);
        if ($stmt->execute()) {
            // TODO
            $stmt->bind_result($api_key);
            $stmt->close();

            return $api_key;
        } else {
            return null;
        }
    }

    /**
     * Fetching user id by api key.
     *
     * @param string $api_key user api key
     */
    public function getUserId($api_key)
    {
        $stmt = $this->conn->prepare('SELECT id FROM users WHERE api_key = ?');
        $stmt->bind_param('s', $api_key);
        if ($stmt->execute()) {
            $stmt->bind_result($user_id);
            $stmt->fetch();
            // TODO
            // $user_id = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            return $user_id;
        } else {
            return null;
        }
    }

    /**
     * Validating user api key
     * If the api key is there in db, it is a valid key.
     *
     * @param string $api_key user api key
     *
     * @return bool
     */
    public function isValidApiKey($api_key)
    {
        $stmt = $this->conn->prepare('SELECT id from users WHERE api_key = ?');
        $stmt->bind_param('s', $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        $stmt->close();

        return $num_rows > 0;
    }

    /**
     * Generating random Unique MD5 String for user Api key.
     */
    private function generateApiKey()
    {
        return md5(uniqid(rand(), true));
    }

    /**
     * Fetching page content.
     *
     * @param string $p_id id of the user
     */
    public function getStaticPage($handle)
    {
        $stmt = $this->conn->prepare('SELECT id,title,handle,banner_image,detail,seo_title,seo_meta,seo_keywords,seo_analytics,seo_detail FROM cms_pages WHERE id = ? or handle=?');
        $stmt->bind_param('ss', $handle, $handle);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id, $title, $handle, $banner_image, $detail, $seo_title, $seo_meta, $seo_keywords, $seo_analytics, $seo_detail);
            $stmt->fetch();
            $pages = array();
            $pages['id'] = $id;
            $pages['title'] = $title;
            $pages['handle'] = $handle;
            $pages['banner_image'] = $banner_image;
            $pages['detail'] = $detail;

            $pages['seo_title'] = $seo_title;
            $pages['seo_meta'] = $seo_meta;
            $pages['seo_keywords'] = $seo_keywords;
            $pages['seo_analytics'] = $seo_analytics;
            $pages['seo_detail'] = $seo_detail;
            $stmt->close();

            return $pages;
        } else {
            return null;
        }
    }

    public function getAgentDetail($agent_id)
    {
        $stmt = $this->conn->prepare('SELECT id,first_name,last_name,email,profile_pic,agent_bio,website_url,cell_phone,introvideo,handle,options,shortcode FROM agents WHERE id = ?');
        $stmt->bind_param('d', $agent_id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id, $first_name, $last_name, $email, $profile_pic, $agent_bio, $website_url, $cell_phone, $introvideo, $handle, $options, $shortcode);
            $stmt->fetch();
            $agent = array();
            $agent['id'] = $id;
            $agent['first_name'] = $first_name;
            $agent['last_name'] = $last_name;
            $agent['email'] = $email;
            $agent['profile_pic'] = $profile_pic;
            $agent['agent_bio'] = $agent_bio;
            $agent['website_url'] = $website_url;
            $agent['cell_phone'] = $cell_phone;
            $agent['introvideo'] = $introvideo;
            $agent['handle'] = $handle;
            $agent['options'] = unserialize($options);
            $agent['shortcode'] = unserialize($shortcode);
            $stmt->close();

            return $agent;
        } else {
            return null;
        }
    }

    public function getAgentByHandle($handle)
    {
        $stmt = $this->conn->prepare('SELECT id,first_name,last_name,email,profile_pic,agent_bio,website_url,cell_phone,introvideo,handle,options,shortcode FROM agents WHERE handle = ?');

        $stmt->bind_param('s', $handle);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id, $first_name, $last_name, $email, $profile_pic, $agent_bio, $website_url, $cell_phone, $introvideo, $handle, $options, $shortcode);
            $stmt->fetch();
            $agent = array();
            $agent['id'] = $id;
            $agent['first_name'] = $first_name;
            $agent['last_name'] = $last_name;
            $agent['email'] = $email;
            $agent['profile_pic'] = $profile_pic;
            $agent['agent_bio'] = $agent_bio;
            $agent['website_url'] = $website_url;
            $agent['cell_phone'] = $cell_phone;
            $agent['introvideo'] = $introvideo;
            $agent['handle'] = $handle;
            $agent['options'] = unserialize($options);
            $agent['shortcode'] = unserialize($shortcode);
            $stmt->close();

            return $agent;
        } else {
            return null;
        }
    }

    public function getRecent($handle)
    {
        $stmt = $this->conn->prepare('SELECT * FROM recent_listings WHERE handle = ?');

        $stmt->bind_param('s', $handle);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id, $name, $youtubelink, $handle, $header, $address, $mlsno, $virtuallink, $pdf, $listprice, $sqft, $beds, $baths, $neighbourhood, $agentid, $shortcode, $image);
            $stmt->fetch();
            $agent = array();
            $agent['id'] = $id;
            $agent['name'] = $name;
            $agent['youtubelink'] = $youtubelink;
            $agent['handle'] = $handle;
            $agent['header'] = $header;
            $agent['address'] = $address;
            $agent['mlsno'] = $mlsno;
            $agent['virtuallink'] = $virtuallink;
            $agent['pdf'] = $pdf;
            $agent['listprice'] = $listprice;
            $agent['sqft'] = $sqft;
            $agent['beds'] = $beds;
            $agent['baths'] = $baths;
            $agent['neighbourhood'] = $neighbourhood;
            $agent['agent'] = $agentid;
            $agent['shortcode'] = unserialize($shortcode);
            $agent['imagedesc'] = unserialize($image);
            $stmt->close();
            //print_r($agent);exit();
            return $agent;
        } else {
            return null;
        }
    }

    /**
     * Fetching all user setting.
     */
    public function getallcmspages()
    {
        $stmt2 = $this->conn->prepare('SELECT title,handle FROM cms_pages WHERE display_stat=1 and sidebar=0');
        if ($stmt2->execute()) {
            $result = $stmt2->get_result();
            while ($cmspage = $result->fetch_assoc()) {
                $cmspages[] = $cmspage;
            }

            return $cmspages;
            $stmt2->close();
        } else {
            return null;
        }
    }

    //16832241_1455754304475945_2849531600654505919_n.jpg
    public function getTestimonials()
    {
        $stmt = $this->conn->prepare('SELECT * FROM testimonials');
        if ($stmt->execute()) {
            $testimonials = $stmt->get_result();
            $stmt->close();
            // var_dump($testimonials);
            // exit();
            return $testimonials;
        } else {
            return null;
        }
    }

    public function getsingletestimonials($tid)
    {
        $stmt = $this->conn->prepare('SELECT * FROM testimonials where id=?');
        $stmt->bind_param('d', $tid);
        if ($stmt->execute()) {
            $stmt->bind_result($id, $name, $image, $content);
            $stmt->fetch();
            $singletest = array();
            $singletest['id'] = $id;
            $singletest['name'] = $name;
            $singletest['image'] = $image;
            $singletest['content'] = trim(htmlspecialchars_decode(strip_tags($content)), '\"');
            $stmt->close();
            // var_dump($singletest);
            // exit();
            return $singletest;
        } else {
            return null;
        }
    }

    public function getRecentBlogs()
    {
        $stmt = $this->conn->prepare('SELECT `id`,`title`,`handle`,`image` FROM `blogposts` ORDER BY `blogposts`.`date_added` DESC LIMIT 0,6');
        if ($stmt->execute()) {
            $blogs = $stmt->get_result();
            $stmt->close();

            return $blogs;
        } else {
            return null;
        }
    }

    public function getAllSetting()
    {
        $stmt = $this->conn->prepare('SELECT * FROM front_settings');
        if ($stmt->execute()) {
            $setting = $stmt->get_result();
            $stmt->close();

            return $setting;
        } else {
            return null;
        }
    }

    public function getAllAgents($pno, $limit)
    {
        $page = $pno * $limit;
        $stmt = $this->conn->prepare('SELECT * FROM agents WHERE agent_status=0 and owner=0 limit ?,?');
        $stmt->bind_param('dd', $page, $limit);

        if ($stmt->execute()) {
            $agents = $stmt->get_result();
            $stmt->close();

            return $agents;
        } else {
            return null;
        }
    }

    public function getOwners()
    {
        $stmt = $this->conn->prepare('SELECT * FROM agents WHERE agent_status=0 and owner=1');

        if ($stmt->execute()) {
            $agents = $stmt->get_result();
            $stmt->close();

            return $agents;
        } else {
            return null;
        }
    }

    public function getSidePages()
    {
        $stmt = $this->conn->prepare('SELECT * FROM cms_pages WHERE sidebar=1');

        if ($stmt->execute()) {
            $agents = $stmt->get_result();
            $stmt->close();

            return $agents;
        } else {
            return null;
        }
    }

    public function getAgents()
    {
        $stmt = $this->conn->prepare('SELECT * FROM agents WHERE agent_status=0 and owner=0');

        if ($stmt->execute()) {
            $agents = $stmt->get_result();
            $stmt->close();

            return $agents;
        } else {
            return null;
        }
    }

    public function getHomeAgents()
    {
        $idstat = $this->conn->prepare('SELECT id FROM agents WHERE agent_status=0 and owner=0');
        $idstat->execute();
        $ids = $idstat->get_result();

        $agentid = array();

        while ($id = $ids->fetch_assoc()) {
            $agentid[] = $id['id'];
        }
        $idstat->close();

        $randomids = array_rand($agentid, 4);
        $id1 = $agentid[$randomids[0]];
        $id2 = $agentid[$randomids[1]];
        $id3 = $agentid[$randomids[2]];
        $id4 = $agentid[$randomids[3]];
        $stmt = $this->conn->prepare('SELECT * FROM agents WHERE agent_status=0 and owner=0 and id in (?,?,?,?)');
        $stmt->bind_param('ssss', $id1, $id2, $id3, $id4);
        if ($stmt->execute()) {
            $agents = $stmt->get_result();
            $stmt->close();

            return $agents;
        } else {
            return null;
        }
    }

    public function getAllListing()
    {
        $stmt = $this->conn->prepare('SELECT * FROM recent_listings');
        if ($stmt->execute()) {
            $listing = $stmt->get_result();
            $stmt->close();

            return $listing;
        } else {
            return null;
        }
    }

    public function getStaticBlocks()
    {
        $stmt = $this->conn->prepare('SELECT * FROM static_blocks ');
        if ($stmt->execute()) {
            $blocks = $stmt->get_result();
            $stmt->close();

            return $blocks;
        } else {
            return null;
        }
    }

    public function getAllMessages($userid)
    {
        $stmt = $this->conn->prepare("select messages.*,concat(users.first_name,' ',users.last_name) as senderuser,admins.user as senderadmin,concat(ruser.first_name,' ',ruser.last_name) as receiveruser,radmin.user as receiveradmin from messages left join users on users.id=messages.sender_id LEFT JOIN admins on messages.sender_admin_id=admins.id LEFT JOIN users as ruser on ruser.id=messages.receiver_id LEFT JOIN admins as radmin on radmin.id=messages.receiver_admin_id where messages.sender_id=? AND messages.parent_id=0 ORDER BY messages.lastreply_date DESC ");
        /* select count(*) msgcount, id from messages where parent_id >0 GROUP BY parent_id ORDER BY lastreply_date DESC */
        $stmt->bind_param('d', $userid);
        if ($stmt->execute()) {
            $messagelist = $stmt->get_result();
            $stmt->close();

            return $messagelist;
        } else {
            return null;
        }
    }

    public function getMessageDetail($sub_id)
    {
        $stmt = $this->conn->prepare("select messages.*,concat(users.first_name,' ',users.last_name) as senderuser,admins.user as senderadmin,concat(ruser.first_name,' ',ruser.last_name) as receiveruser,radmin.user as receiveradmin from messages left join users on users.id=messages.sender_id LEFT JOIN admins on messages.sender_admin_id=admins.id LEFT JOIN users as ruser on ruser.id=messages.receiver_id LEFT JOIN admins as radmin on radmin.id=messages.receiver_admin_id where messages.parent_id=? OR messages.id=? ORDER BY messages.id asc ");
        $stmt->bind_param('dd', $sub_id, $sub_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $stmt->close();

            return $result;
        } else {
            return null;
        }
    }

    public function getAdminReply($sub_id)
    {
        $stmt = $this->conn->prepare("select messages.*,concat(users.first_name,' ',users.last_name) as senderuser,admins.user as senderadmin,concat(ruser.first_name,' ',ruser.last_name) as receiveruser,radmin.user as receiveradmin from messages left join users on users.id=messages.sender_id LEFT JOIN admins on messages.sender_admin_id=admins.id LEFT JOIN users as ruser on ruser.id=messages.receiver_id LEFT JOIN admins as radmin on radmin.id=messages.receiver_admin_id where (messages.parent_id=? OR messages.id=?) ORDER BY messages.id asc ");
        //select messages.*,concat(users.first_name,' ',users.last_name) as senderuser,admins.user as senderadmin,concat(ruser.first_name,' ',ruser.last_name) as receiveruser,radmin.user as receiveradmin from messages left join users on users.id=messages.sender_id LEFT JOIN admins on messages.sender_admin_id=admins.id LEFT JOIN users as ruser on ruser.id=messages.receiver_id LEFT JOIN admins as radmin on radmin.id=messages.receiver_admin_id where (messages.parent_id=? OR messages.id=?) and messages.sender_admin_id!=0 ORDER BY messages.id asc
        $stmt->bind_param('dd', $sub_id, $sub_id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $stmt->close();

            return $result;
        } else {
            return null;
        }
    }

    public function submitMessage($userid, $subject, $messagebody)
    {
        // insert query
        $datem = date('Y-m-d H:i:s');

        $stmt = $this->conn->prepare('insert into messages (sender_id, receiver_admin_id, subject,message,create_date,  lastreply_date) values(?,1,?,?,?,?)');
        $stmt->bind_param('issss', $userid, $subject, $messagebody, $datem, $datem);
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

    public function submitReply($userid, $replydata, $subjectid, $msgsub)
    {
        // insert query
        $datem = date('Y-m-d H:i:s');
        $stmt = $this->conn->prepare('insert into messages (sender_id, receiver_admin_id, subject,message,parent_id,create_date,lastreply_date) values(?,1,?,?,?,?,?)');
        $stmt->bind_param('isssss', $userid, $msgsub, $replydata, $subjectid, $datem, $datem);
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
     * Fetching all user tasks.
     *
     * @param string $user_id id of the user
     */
    public function getUser($u_id)
    {
        $stmt = $this->conn->prepare('SELECT id,first_name,last_name,email,api_key,newsletter,phone,secondemail,cellphone,workphone,fax,streetaddress,city,state,zipcode,country,matchingsaved,concerningupdate FROM users WHERE id = ?');
        $stmt->bind_param('d', $u_id);
        if ($stmt->execute()) {
            // $user = $stmt->get_result()->fetch_assoc();
            $stmt->bind_result($id, $first_name, $last_name, $email, $apikey, $newsletter, $phone, $secondemail, $cellphone, $workphone, $fax, $streetaddress, $city, $state, $zipcode, $country, $matchingsaved, $concerningupdate);
            $stmt->fetch();
            $user = array();
            $user['id'] = $id;
            $user['firstName'] = $first_name;
            $user['lastName'] = $last_name;
            $user['email'] = $email;
            $user['secondemail'] = $secondemail;
            //~ $user["newsletter"] = $newsletter;
            $user['apikey'] = $apikey;
            $user['streetadd'] = $streetaddress;
            $user['city'] = $city;
            $user['state'] = $state;
            $user['zip'] = $zipcode;
            $user['country'] = $country;
            $user['phone'] = $phone;
            $user['cellphone'] = $cellphone;
            $user['workphone'] = $workphone;
            $user['fax'] = $fax;
            $user['matchingsaved'] = $matchingsaved;
            $user['concerningupdate'] = $concerningupdate;
            $stmt->close();

            return $user;
        } else {
            return null;
        }
    }

    public function getFooterSign()
    {
        $stmt = $this->conn->prepare('SELECT * FROM front_settings');
        if ($stmt->execute()) {
            $setting = $stmt->get_result()->fetch_assoc();
            $agentid = $setting['agent_signature'];
            $stmt1 = $this->conn->prepare('SELECT email_sign FROM agents WHERE id = ?');
            $stmt1->bind_param('d', $agentid);
            if ($stmt1->execute()) {
                // $user = $stmt->get_result()->fetch_assoc();
                $stmt1->bind_result($email_sign);
                $stmt1->fetch();
                $email_sign = $email_sign;
                $stmt1->close();

                return $email_sign;
            } else {
                return null;
            }
        } else {
            return null;
        }
        //return 1;
    }

    /**
     * Deleting a task.
     *
     * @param string $task_id id of the task to delete
     */
    public function deleteSubject($subject_id)
    {
        $stmt = $this->conn->prepare('delete from messages WHERE id =? or parent_id=?');
        $stmt->bind_param('ii', $subject_id, $subject_id);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();

        return $num_affected_rows > 0;
    }

    public function removefavorite($user_id, $propid)
    {
        $stmt = $this->conn->prepare('delete from favorite_properties WHERE     userid =? AND propertyid=?');
        $stmt->bind_param('ii', $user_id, $propid);
        $stmt->execute();
        $num_affected_rows = $stmt->affected_rows;
        $stmt->close();

        return $num_affected_rows > 0;
    }

    public function getallfav($userid)
    {
        $stmt = $this->conn->prepare('SELECT * FROM favorite_properties WHERE userid=?');
        $stmt->bind_param('d', $userid);
        if ($stmt->execute()) {
            $favs = $stmt->get_result();
            $stmt->close();

            return $favs;
        } else {
            return null;
        }
    }
}
