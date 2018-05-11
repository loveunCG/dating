<?php

require_once './include/DbHandler.php';
require_once './include/PassHash.php';
require './vendor/autoload.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim([
    'settings' => [
        'determineRouteBeforeAppMiddleware' => true,
    ],
]);
$app->add(new \CorsSlim\CorsSlim());

// User id from db - Global Variable
$user_id = null;
/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header.
 */
function authenticate(Slim\Route $route)
{
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = Slim\Slim::getInstance();

    // Verifying Authorization Header
    if (isset($headers['authorization'])) {
        $db = new DbHandler();

        // get the api key
        $api_key = $headers['authorization'];
        // validating api key
        if (!$db->isValidApiKey($api_key)) {
            // api key is not present in users table
            $response['error'] = true;
            $response['message'] = 'Access Denied. Invalid Api key';
            echoRespnse(401, $response);

            $app->stop();
        } else {
            global $user_id;
            // get user primary key id
            $user_id = $db->getUserId($api_key);
        }
    } else {
        // api key is missing in header
        $response['error'] = true;
        $response['message'] = 'Api key is misssing';
        echoRespnse(400, $response);
        $app->stop();
    }
}

//dating website
//messages

$app->post('/checkchatroom', function () use ($app) {
    $data = json_decode($app->request()->getBody());

    $fromid = $data->fromid;
    $toid = $data->toid;

    $db = new DbHandler();
    $res = $db->checkchatroom($fromid, $toid);

    if ($res) {
        $response['chatid'] = $res;
        $response['error'] = false;
    } else {
        $response['error'] = true;
    }
    echoRespnse(200, $response);
});

$app->get('/getuserchats/:id', function ($id) {
    $db = new DbHandler();
    $res = $db->getuserchats($id);

    if (!empty($res)) {
        $response['chatsare'] = $res;
        $response['error'] = false;
    } else {
        $response['error'] = true;
    }
    echoRespnse(200, $response);
});

$app->get('/getservice', function () {
    $db = new DbHandler();
    $res = $db->getservice_location();
    if (!empty($res)) {
        $response['location'] = $res;
        $response['error'] = false;
    } else {
        $response['error'] = true;
    }
    echoRespnse(200, $response);
});

$app->get('/getchatmlist/:id/:page/:perpage', function ($id, $page, $perpage) {
    $db = new DbHandler();
    $res = $db->getchatmlist($id, $page, $perpage);

    $response['total_count'] = $res['total_count'];
    $response['incomplete_results'] = $res['incomplete_results'];
    $response['items'] = $res['items'];

    echoRespnse(200, $response);
});

$app->get('/getmessages/:id/:uid', function ($id, $uid) {
    $db = new DbHandler();
    $res = $db->getchatmessages($id, $uid);

    if (!empty($res)) {
        $response['messages'] = $res;
        $response['error'] = false;
    } else {
        $response['error'] = true;
    }
    echoRespnse(200, $response);
});

$app->get('/updatechatmsg/:cid', function ($cid) {
    $db = new DbHandler();
    $res = $db->updatechatmsg($cid);

    if (!empty($res)) {
        $response['messages'] = $res;
        $response['error'] = false;
    } else {
        $response['error'] = true;
    }
    echoRespnse(200, $response);
});

$app->post('/sendmessage', function () use ($app) {
    $data = json_decode($app->request()->getBody());

    $chatid = $data->chatid;
    $fromid = $data->fromid;
    $toid = $data->toid;
    $msgtxt = $data->msg;

    $db = new DbHandler();
    $res = $db->addnewmsg($fromid, $toid, $chatid, $msgtxt);

    if ($res) {
        $response['data'] = $res;
        $response['error'] = false;
    } else {
        $response['error'] = true;
    }
    echoRespnse(200, $response);
});

$app->get('/checklastfive/:cid/:fromid', function ($cid, $fromid) {
    $db = new DbHandler();
    $res = $db->checklastfive($cid, $fromid);

    if (!empty($res)) {
        $response['stat'] = $res['stat'];
        $response['error'] = false;
    } else {
        $response['error'] = true;
    }
    echoRespnse(200, $response);
});

$app->get('/admincmsgs/:cid/:start', function ($cid, $start) {
    $db = new DbHandler();
    $res = $db->admincmsgs($cid, $start);
    $response = array();

    if ($res) {
        $response['error'] = false;
        $response['data'] = $res;
    } else {
        $response['error'] = true;
    }
    echoRespnse(200, $response);
});

$app->get('/getunreadcount/:uid', function ($uid) {
    $db = new DbHandler();
    $res = $db->unreadcountmsg($uid);

    echoRespnse(200, $res);
});

//messages
$app->post('/adminLogin', function () use ($app) {
    // check for required params
    //verifyRequiredParams(array('name', 'email', 'password'));
    $user = json_decode($app->request()->getBody());

    $email = $user->email;
    $pass = $user->password;

    //$providerdata = $user->providerData;
    $db = new DbHandler();
    $res = $db->adminLogin($email, $pass);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['email'] = $res['adminemail'];
        $response['fullName'] = $res['adminname'];
        $response['token'] = $res['adminid'];
        if ($res['subadmin']) {
            $response['prev'] = $res['prev'];
        } else {
            $response['lastlogin'] = $res['lastlogin'];
        }
        $response['subadmin'] = $res['subadmin'];

        $response['message'] = 'Success';

        return echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Email or password is incorrect';
        echoRespnse(204, $response);
    }
    // echo json response
});

$app->post('/addsubadmin', function () use ($app) {
    $data = json_decode($app->request->getBody(), true);

    // echoRespnse(200, $data);
    $prev = serialize($data['privileges']);

    $db = new DbHandler();
    $res = $db->addsubadmin($data['firstName'], $data['email'], $data['password'], $prev);

    echoRespnse(200, $res);
});

$app->post('/forgotPassAdmin', function () use ($app) {
    $user = json_decode($app->request()->getBody());

    $email = $user->email;

    $db = new DbHandler();
    $res = $db->adminForgotPass($email);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $to = $email;
        $subject = 'About forget Password';
        $message = 'Your Login Detail is as follow: <br>'.'EmailID = '.$email.'<br>Password = '.$res['newpass'];
        //$message .="<br/>"."Your Username : ".$ans[0]['user'];
        $headers = 'MIME-Version: 1.0'."\r\n";
        $headers .= 'Content-type:text/html;charset=iso-8859-1'."\r\n";
        $headers .= 'From: Dating <prabhasolutionsdeveloper@gmail.com>'."\r\n";
        mail($to, $subject, $message, $headers);
        $response['error'] = false;
        $response['email'] = $res['adminemail'];
        $response['token'] = $res['adminid'];
        $response['message'] = 'Success';

        return echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Email or password is incorrect';
        echoRespnse(204, $response);
    }
});

$app->post('/addtowallet', function () use ($app) {
    $data = json_decode($app->request()->getBody());

    $db = new DbHandler();
    $res = $db->addToWallet($data->id, $data->amount);

    echoRespnse(200, $res);
});

$app->get('/siteInfo', function () {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();

    // fetch task
    $result = $db->getSiteInfo();

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->get('/getadminearning', function () {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();

    // fetch task
    $result = $db->adminEarnings();

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->get('/totalvisitors', function () {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();

    // fetch task
    $result = $db->totalVisitors();

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->get('/adminInfo/:id', function ($id) {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();

    // fetch task
    $result = $db->getAdminInfo($id);

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->post('/getadminnotify', function () use ($app) {
    //  global $user_id;
    $response = array();
    $user = json_decode($app->request()->getBody());
    $db = new DbHandler();

    // fetch task
    $result = $db->getAdminNotify($user->id, $user->lastlogin);

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->post('/updatelogin', function () use ($app) {
    //  global $user_id;
    $response = array();
    $user = json_decode($app->request()->getBody());
    $db = new DbHandler();

    // fetch task
    $result = $db->updateUserLogin($user->uid);

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;

        echoRespnse(200, $response);
    } else {
        $response['error'] = true;

        echoRespnse(404, $response);
    }
});

$app->post('/addcomment', function () use ($app) {
    //  global $user_id;
    $response = array();
    $user = json_decode($app->request()->getBody());
    $db = new DbHandler();

    if (isset($user->username) && !empty($user->username)) {
        $uid = 0;
        $name = $user->username;
    } else {
        $uid = $user->uid;
        $name = '';
    }
    // fetch task
    $result = $db->addComment($uid, $user->girlid, $user->comment, $name);

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;

        echoRespnse(200, $response);
    } else {
        $response['error'] = true;

        echoRespnse(200, $response);
    }
});

$app->post('/addcomplaint', function () use ($app) {
    //  global $user_id;
    $response = array();
    $user = json_decode($app->request()->getBody());
    $db = new DbHandler();

    if (isset($user->username) && !empty($user->username)) {
        $uid = 0;
        $name = $user->username;
    } else {
        $uid = $user->uid;
        $name = '';
    }

    // fetch task
    $result = $db->addComplaint($uid, $user->girlid, $user->complaint, $name);

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;

        echoRespnse(200, $response);
    } else {
        $response['error'] = true;

        echoRespnse(404, $response);
    }
});

$app->post('/updateProfile', function () use ($app) {
    $user = json_decode($app->request()->getBody(), true);
    // $response['linkedinurl'] = $linkedinurl;
    // $response['dribbleurl'] = $dribbleurl;
    // $response['googlepurl'] = $googlepurl;
    $id = $user['adminid'];
    $username = $user['username'];
    $password = '';
    if (isset($user['password']) && !empty($user['password'])) {
        $password = $user['password'];
    }
    $email = $user['email'];

    $db = new DbHandler();
    $res = $db->updateAdmin($id, $username, $email, $password);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(204, $response);
    }
});

$app->post('/changehighlight', function () use ($app) {
    $data = json_decode($app->request()->getBody(), true);

    $db = new DbHandler();
    $res = $db->updatehighlight($data['uid'], $data['stat']);

    echoRespnse(200, $res);
});

$app->post('/subprofileupdate', function () use ($app) {
    $user = json_decode($app->request()->getBody(), true);
    // $response['linkedinurl'] = $linkedinurl;
    // $response['dribbleurl'] = $dribbleurl;
    // $response['googlepurl'] = $googlepurl;
    $id = $user['adminid'];
    $username = $user['username'];
    $password = '';
    if (isset($user['password']) && !empty($user['password'])) {
        $password = $user['password'];
    }
    $email = $user['email'];

    $db = new DbHandler();
    $res = $db->subprofileupdate($id, $username, $email, $password);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(204, $response);
    }
});

$app->post('/updateatoken', function () use ($app) {
    $user = json_decode($app->request()->getBody(), true);
    // $response['linkedinurl'] = $linkedinurl;
    // $response['dribbleurl'] = $dribbleurl;
    // $response['googlepurl'] = $googlepurl;
    $token = $user['token'];
    $id = $user['aid'];

    $db = new DbHandler();
    $res = $db->updateAtoken($token, $id);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(204, $response);
    }
});

$app->post('/updateGenSets', function () use ($app) {
    $user = json_decode($app->request()->getBody(), true);

    $semail = $user['supportemail'];
    $imgprice = $user['imgprice'];
    $commission = $user['commission'];
    $highprice = $user['highlightprice'];
    $dailyamount = $user['dailyamount'];

    if (!is_null($user['logoimg'])) {
        $logo = $user['logoimg'];
    } else {
        $logo = array();
    }
    if (!empty($user['favimg'])) {
        $fav = $user['favimg'];
    } else {
        $fav = array();
    }
    if (!empty($user['boydefimg'])) {
        $boyimg = $user['boydefimg'];
    } else {
        $boyimg = array();
    }
    if (!empty($user['girldefimg'])) {
        $girlimg = $user['girldefimg'];
    } else {
        $girlimg = array();
    }

    $oldlogo = $user['logoimgold'];
    $favold = $user['favimgold'];
    $oldoybimg = $user['boydefimgold'];
    $oldgirlimg = $user['girldefimgold'];

    $logoimage = '';
    $favimage = '';
    $fboyimg = '';
    $fgirlimg = '';
    //define('UPLOAD_DIR', '/var/www/html/projects/Dating/uploads/');
    define('UPLOAD_DIR', '../../uploads/');

    if (count($logo) > 0) {
        foreach ($logo as $key => $values) {
            $image_parts = explode(';base64,', $values);
            $image_type_aux = explode('image/', $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = UPLOAD_DIR.$key;
            file_put_contents($file, $image_base64);
            $logoimage = $key;
        }
    } else {
        $logoimage = $oldlogo;
    }

    if (count($fav) > 0) {
        foreach ($fav as $key => $values) {
            $image_parts = explode(';base64,', $values);
            $image_type_aux = explode('image/', $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = UPLOAD_DIR.$key;
            file_put_contents($file, $image_base64);
            $favimage = $key;
        }
    } else {
        $favimage = $favold;
    }

    if (count($boyimg) > 0) {
        foreach ($boyimg as $key => $values) {
            $image_parts = explode(';base64,', $values);
            $image_type_aux = explode('image/', $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = UPLOAD_DIR.$key;
            file_put_contents($file, $image_base64);
            $fboyimg = $key;
        }
    } else {
        $fboyimg = $oldoybimg;
    }

    if (count($girlimg) > 0) {
        foreach ($girlimg as $key => $values) {
            $image_parts = explode(';base64,', $values);
            $image_type_aux = explode('image/', $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = UPLOAD_DIR.$key;
            file_put_contents($file, $image_base64);
            $fgirlimg = $key;
        }
    } else {
        $fgirlimg = $oldgirlimg;
    }

    $db = new DbHandler();
    $res = $db->updateGensets($semail, $imgprice, $highprice, $commission, $dailyamount, $logoimage, $favimage, $fboyimg, $fgirlimg);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(204, $response);
    }
});

$app->post('/getgirlearnings', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //return echoRespnse(200, $user);
    $page = $user['datatable']['pagination']['page'];
    $perpage = $user['datatable']['pagination']['perpage'];
    $sort = $user['datatable']['sort']['sort'];
    $sfield = $user['datatable']['sort']['field'];

    $uid = '';
    if (isset($user['datatable']['query']['userid']) && !empty($user['datatable']['query']['userid'])) {
        $uid = $user['datatable']['query']['userid'];
    }
    $searchq = '';
    if (isset($user['datatable']['query']['generalSearch']) && !empty($user['datatable']['query']['generalSearch'])) {
        $searchq = $user['datatable']['query']['generalSearch'];
    }
    if (empty($sfield)) {
        $sfield = 'id';
    }
    if (empty($sort)) {
        $sort = 'desc';
    }
    $type = '';
    if (isset($user['datatable']['query']['Type']) && !empty($user['datatable']['query']['Type'])) {
        $type = $user['datatable']['query']['Type'];
    }
    // $daterange = '';
    // if(isset($user['datatable']['query']['daterange']) && !empty($user['datatable']['query']['daterange'])){
    //   $daterange = $user['datatable']['query']['daterange'];
    // }

    $db = new DbHandler();
    $res = $db->getGirlEarning($sort, $sfield, $page, $perpage, $uid, $searchq, $type);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['data'] = $res['users'];
        $response['meta']['page'] = (int) $page;
        $pagec = ceil($res['total'] / $perpage);
        $response['meta']['pages'] = (int) $pagec;
        $response['meta']['perpage'] = (int) $perpage;
        $response['meta']['total'] = $res['total'];
        $response['meta']['sort'] = $sort;
        $response['meta']['field'] = $sfield;

        //$response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(204, $response);
    }
});

$app->post('/updatefooter', function () use ($app) {
    $user = json_decode($app->request()->getBody(), true);

    $instaurl = $user['instaurl'];
    $fburl = $user['fburl'];
    $linkedinurl = $user['linkedinurl'];
    $tweeturl = $user['tweeturl'];
    $youtubeurl = $user['youtubeurl'];
    $dribbleurl = $user['dribbleurl'];
    $googlepurl = $user['googlepurl'];
    $blogurl = $user['blogurl'];
    $fustitle = $user['fustitle'];
    $fusdesc = $user['fusdesc'];
    $cptext = $user['cptext'];

    $db = new DbHandler();
    $res = $db->updateFooter($instaurl, $fburl, $linkedinurl, $tweeturl, $youtubeurl, $dribbleurl, $googlepurl, $blogurl, $fustitle, $fusdesc, $cptext);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(204, $response);
    }
});

$app->post('/userstatus', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //$user = $app->request()->getBody();
    // $response['linkedinurl'] = $linkedinurl;
    // $response['dribbleurl'] = $dribbleurl;
    // $response['googlepurl'] = $googlepurl;
    $id = $user['userids'];
    $stat = $user['stat'];

    //return echoRespnse(200, $user);
    $db = new DbHandler();
    $res = $db->updateadminustat($id, $stat);

    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'Updated';
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
    }
    echoRespnse(200, $response);
});

$app->post('/profilestat', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //$user = $app->request()->getBody();
    // $response['linkedinurl'] = $linkedinurl;
    // $response['dribbleurl'] = $dribbleurl;
    // $response['googlepurl'] = $googlepurl;
    $id = $user['userids'];
    $stat = $user['stat'];

    //return echoRespnse(200, $user);
    $db = new DbHandler();
    $res = $db->updateustat($id, $stat);

    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'Updated';
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
    }
    echoRespnse(200, $response);
});

$app->post('/getuserlist', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //return echoRespnse(200, $user);
    $page = $user['datatable']['pagination']['page'];
    $perpage = $user['datatable']['pagination']['perpage'];
    $sort = 'desc';
    $sfield = 'id';

    if (!empty($user['datatable']['sort']['sort'])) {
        $sort = $user['datatable']['sort']['sort'];
    }
    if (!empty($user['datatable']['sort']['field'])) {
        $sfield = $user['datatable']['sort']['field'];
    }

    $stat = '';
    if (isset($user['datatable']['query']['Status']) && !empty($user['datatable']['query']['Status'])) {
        $stat = $user['datatable']['query']['Status'];
    }
    $searchq = '';
    if (isset($user['datatable']['query']['generalSearch']) && !empty($user['datatable']['query']['generalSearch'])) {
        $searchq = $user['datatable']['query']['generalSearch'];
    }
    $type = '';
    if (isset($user['datatable']['query']['Type']) && !empty($user['datatable']['query']['Type'])) {
        $type = $user['datatable']['query']['Type'];
    }

    $db = new DbHandler();
    $res = $db->getUsersList($sort, $sfield, $page, $perpage, $stat, $searchq, $type);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['data'] = $res['users'];
        $response['meta']['page'] = (int) $page;
        $pagec = ceil($res['total'] / $perpage);
        $response['meta']['pages'] = (int) $pagec;
        $response['meta']['perpage'] = (int) $perpage;
        $response['meta']['total'] = $res['total'];
        $response['meta']['sort'] = $sort;
        $response['meta']['field'] = $sfield;

        //$response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Email or password is incorrect';
        echoRespnse(204, $response);
    }
});

$app->post('/getsubadmins', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //return echoRespnse(200, $user);
    $page = $user['datatable']['pagination']['page'];
    $perpage = $user['datatable']['pagination']['perpage'];
    $sort = 'desc';
    $sfield = 'id';

    // if(!empty($user['datatable']['sort']['sort'])){
    //   $sort = $user['datatable']['sort']['sort'];
    // }
    // if(!empty($user['datatable']['sort']['field'])){
    //   $sfield = $user['datatable']['sort']['field'];
    // }

    $searchq = '';
    if (isset($user['datatable']['query']['generalSearch']) && !empty($user['datatable']['query']['generalSearch'])) {
        $searchq = $user['datatable']['query']['generalSearch'];
    }

    $db = new DbHandler();
    $res = $db->geetsubadminlist($sort, $sfield, $page, $perpage, $searchq);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['data'] = $res['users'];
        $response['meta']['page'] = (int) $page;
        $pagec = ceil($res['total'] / $perpage);
        $response['meta']['pages'] = (int) $pagec;
        $response['meta']['perpage'] = (int) $perpage;
        $response['meta']['total'] = $res['total'];
        $response['meta']['sort'] = $sort;
        $response['meta']['field'] = $sfield;

        //$response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Email or password is incorrect';
        echoRespnse(204, $response);
    }
});

$app->post('/gettransactionlist', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //return echoRespnse(200, $user);
    $page = $user['datatable']['pagination']['page'];
    $perpage = $user['datatable']['pagination']['perpage'];
    $sort = $user['datatable']['sort']['sort'];
    $sfield = $user['datatable']['sort']['field'];

    $uid = '';
    if (isset($user['datatable']['query']['userid']) && !empty($user['datatable']['query']['userid'])) {
        $uid = $user['datatable']['query']['userid'];
    }
    $searchq = '';
    if (isset($user['datatable']['query']['generalSearch']) && !empty($user['datatable']['query']['generalSearch'])) {
        $searchq = $user['datatable']['query']['generalSearch'];
    }
    $type = '';
    if (isset($user['datatable']['query']['Type']) && !empty($user['datatable']['query']['Type'])) {
        $type = $user['datatable']['query']['Type'];
    }
    $daterange = '';
    if (isset($user['datatable']['query']['daterange']) && !empty($user['datatable']['query']['daterange'])) {
        $daterange = $user['datatable']['query']['daterange'];
    }

    $db = new DbHandler();
    $res = $db->getTransactionList($sort, $sfield, $page, $perpage, $uid, $searchq, $type, $daterange);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['data'] = $res['users'];
        $response['meta']['page'] = (int) $page;
        $pagec = ceil($res['total'] / $perpage);
        $response['meta']['pages'] = (int) $pagec;
        $response['meta']['perpage'] = (int) $perpage;
        $response['meta']['total'] = $res['total'];
        $response['meta']['sort'] = $sort;
        $response['meta']['field'] = $sfield;

        //$response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Email or password is incorrect';
        echoRespnse(204, $response);
    }
});

$app->post('/adminchats', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //return echoRespnse(200, $user);
    $page = $user['datatable']['pagination']['page'];
    $perpage = $user['datatable']['pagination']['perpage'];
    $sort = $user['datatable']['sort']['sort'];
    $sfield = $user['datatable']['sort']['field'];

    $uid = '';
    if (isset($user['datatable']['query']['userid']) && !empty($user['datatable']['query']['userid'])) {
        $uid = $user['datatable']['query']['userid'];
    }
    $searchq = '';
    if (isset($user['datatable']['query']['generalSearch']) && !empty($user['datatable']['query']['generalSearch'])) {
        $searchq = $user['datatable']['query']['generalSearch'];
    }
    $type = '';
    if (isset($user['datatable']['query']['Type']) && !empty($user['datatable']['query']['Type'])) {
        $type = $user['datatable']['query']['Type'];
    }
    $daterange = '';
    if (isset($user['datatable']['query']['daterange']) && !empty($user['datatable']['query']['daterange'])) {
        $daterange = $user['datatable']['query']['daterange'];
    }

    $db = new DbHandler();
    $res = $db->adminchats($sort, $sfield, $page, $perpage, $uid);
    // print_r($res);
    // return echoRespnse(200,$res);
    // exit();
    if (1 == $res['stat']) {
        $response['data'] = $res['users'];
        $response['meta']['page'] = (int) $page;
        $pagec = ceil($res['total'] / $perpage);
        $response['meta']['pages'] = (int) $pagec;
        $response['meta']['perpage'] = (int) $perpage;
        $response['meta']['total'] = $res['total'];
        $response['meta']['sort'] = $sort;
        $response['meta']['field'] = $sfield;

        //$response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Email or password is incorrect';
        echoRespnse(204, $response);
    }
});

$app->get('/usertransactions/:sort/:order/:page/:perpage/:uid/:fromdate/:todate', function ($sort, $order, $page, $perpage, $uid, $fromdate, $todate) {
    //parse_str($app->request()->getBody(),$user);

    if ('trdate' == $sort) {
        $sort = 'transaction_time';
    }
    if ('trtime' == $sort) {
        $sort = 'transaction_time';
    }
    if ('money' == $sort) {
        $sort = 'amount';
    }

    $db = new DbHandler();
    $res = $db->userTransactions($sort, $order, $page, $perpage, $uid, $fromdate, $todate);
    //print_r($res);
    //return echoRespnse(200,$res);
    $response['total_count'] = $res['total_count'];
    $response['incomplete_results'] = $res['incomplete_results'];
    $response['items'] = $res['items'];
    echoRespnse(200, $response);
});

$app->post('/getgirlprpofiles', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //return echoRespnse(200, $user);
    $page = $user['datatable']['pagination']['page'];
    $perpage = $user['datatable']['pagination']['perpage'];
    $sort = $user['datatable']['sort']['sort'];
    $sfield = $user['datatable']['sort']['field'];
    if (empty($sfield)) {
        $sfield = 'id';
    }
    if (empty($sort)) {
        $sort = 'desc';
    }
    $stat = '';
    if (isset($user['datatable']['query']['Status']) && !empty($user['datatable']['query']['Status'])) {
        $stat = $user['datatable']['query']['Status'];
    }
    $searchq = '';
    if (isset($user['datatable']['query']['generalSearch']) && !empty($user['datatable']['query']['generalSearch'])) {
        $searchq = $user['datatable']['query']['generalSearch'];
    }

    $db = new DbHandler();
    $res = $db->getGirlProfiles($sort, $sfield, $page, $perpage, $stat, $searchq);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['data'] = $res['users'];
        $response['meta']['page'] = (int) $page;
        $pagec = ceil($res['total'] / $perpage);
        $response['meta']['pages'] = (int) $pagec;
        $response['meta']['perpage'] = (int) $perpage;
        $response['meta']['total'] = $res['total'];
        $response['meta']['sort'] = $sort;
        $response['meta']['field'] = $sfield;

        //$response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Email or password is incorrect';
        echoRespnse(204, $response);
    }
});

$app->post('/getinactivelist', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //return echoRespnse(200, $user);
    $page = $user['datatable']['pagination']['page'];
    $perpage = $user['datatable']['pagination']['perpage'];
    $sort = $user['datatable']['sort']['sort'];
    $sfield = $user['datatable']['sort']['field'];
    if (empty($sfield)) {
        $sfield = 'id';
    }
    if (empty($sort)) {
        $sort = 'desc';
    }
    $stat = '';
    if (isset($user['datatable']['query']['Status']) && !empty($user['datatable']['query']['Status'])) {
        $stat = $user['datatable']['query']['Status'];
    }
    $searchq = '';
    if (isset($user['datatable']['query']['generalSearch']) && !empty($user['datatable']['query']['generalSearch'])) {
        $searchq = $user['datatable']['query']['generalSearch'];
    }

    $db = new DbHandler();
    $res = $db->getInactiveList($sort, $sfield, $page, $perpage, $stat, $searchq);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['data'] = $res['users'];
        $response['meta']['page'] = (int) $page;
        $pagec = ceil($res['total'] / $perpage);
        $response['meta']['pages'] = (int) $pagec;
        $response['meta']['perpage'] = (int) $perpage;
        $response['meta']['total'] = $res['total'];
        $response['meta']['sort'] = $sort;
        $response['meta']['field'] = $sfield;

        //$response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Email or password is incorrect';
        echoRespnse(204, $response);
    }
});

$app->post('/gethomeuserlist', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //return echoRespnse(200, $user);
    $page = $user['datatable']['pagination']['page'];
    $perpage = $user['datatable']['pagination']['perpage'];
    $sort = $user['datatable']['sort']['sort'];
    $sfield = $user['datatable']['sort']['field'];

    $db = new DbHandler();
    $res = $db->getHomeUsersList('desc', 'id', 1, 10);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['data'] = $res['users'];
        $response['meta']['page'] = (int) $page;
        $pagec = ceil($res['total'] / $perpage);
        $response['meta']['pages'] = (int) $pagec;
        $response['meta']['perpage'] = (int) $perpage;
        $response['meta']['total'] = $res['total'];
        $response['meta']['sort'] = $sort;
        $response['meta']['field'] = $sfield;

        //$response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Email or password is incorrect';
        echoRespnse(204, $response);
    }
});

$app->post('/deleteuser', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //$user = $app->request()->getBody();
    $uids = $user['userids'];
    //return echoRespnse(200, $uids);

    $db = new DbHandler();
    $res = $db->deleteUser($uids);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'User deleted successfully';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(204, $response);
    }
});

$app->post('/deletesubadmin', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //$user = $app->request()->getBody();
    $uids = $user['userids'];
    //return echoRespnse(200, $uids);

    $db = new DbHandler();
    $res = $db->deletesubadmin($uids);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'User deleted successfully';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(200, $response);
    }
});

$app->post('/inactiveact', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //$user = $app->request()->getBody();
    $uids = $user['userids'];
    //return echoRespnse(200, $uids);

    $db = new DbHandler();
    $res = $db->inactiveAct($uids);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'User deactivated successfully';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(204, $response);
    }
});

$app->post('/transfertowallet', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //$user = $app->request()->getBody();
    $uids = $user['userids'];
    //return echoRespnse(200, $uids);

    $db = new DbHandler();
    $res = $db->transferToWallet($uids);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'Transfered successfully';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(204, $response);
    }
});

$app->post('/markpaid', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //$user = $app->request()->getBody();
    $uids = $user['userids'];
    //return echoRespnse(200, $uids);

    $db = new DbHandler();
    $res = $db->markasPaid($uids);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'Transfered successfully';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(200, $response);
    }
});

$app->post('/addvisitor', function () use ($app) {
    //parse_str($app->request()->getBody(),$user);
    $user = json_decode($app->request()->getBody());
    $uids = $user->ip;
    //return echoRespnse(200, $uids);

    $db = new DbHandler();
    $res = $db->addVisitor($uids);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'New added';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(200, $response);
    }
});

$app->get('/deletecomplaint/:id', function ($id) {
    $db = new DbHandler();
    $res = $db->deleteComplaint($id);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['res'] = $res['res'];
        $response['message'] = 'Complaint deleted';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['res'] = $res['res'];
        $response['message'] = 'Something went wrong';
        echoRespnse(200, $response);
    }
});

$app->get('/deletecomment/:id', function ($id) {
    $db = new DbHandler();
    $res = $db->deleteComment($id);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'Complaint deleted';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(200, $response);
    }
});

$app->post('/deletecmspage', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //$user = $app->request()->getBody();
    $uids = $user['userids'];
    //return echoRespnse(200, $uids);

    $db = new DbHandler();
    $res = $db->deleteCmsPage($uids);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'User deleted successfully';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(204, $response);
    }
});

$app->post('/updatesubadmin', function () use ($app) {
    // parse_str($app->request()->getBody(),$user);
    $user = json_decode($app->request()->getBody(), true);
    $uid = $user['uid'];
    //return echoRespnse(200, $uids);

    $db = new DbHandler();
    $res = $db->updatesubadmin($uid, $user);
    //print_r($res);
    //return echoRespnse(200,$res);
    echoRespnse(200, $res);
});

$app->get('/getuser/:id', function ($id) {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();

    // fetch task
    $result = $db->getUserById($id);

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->get('/getsubadmin/:id', function ($id) {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();

    // fetch task
    $result = $db->getsubadmin($id);

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->get('/updatelogout/:id', function ($id) {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();

    // fetch task
    $result = $db->updateAlogout($id);

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->get('/getallusers', function () {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();

    // fetch task
    $result = $db->getAllUsers();

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result['users'];
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->get('/getgirlusers', function () {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();

    // fetch task
    $result = $db->getGirlUsers();

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result['users'];
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->post('/updateuser', function () use ($app) {
    // check for required params
    //verifyRequiredParams(array('name', 'email', 'password'));
    $user = json_decode($app->request()->getBody(), true);
    $response = array();
    $images = array();
    $fimages = array();
    $db = new DbHandler();

    $siteinfo = $db->getSiteInfo();
    $imgprice = $siteinfo['imgprice'];

    $id = $user['id'];
    if ('Female' == $user['regtype']) {
        $firstname = $user['first_name'];
        $lastname = $user['last_name'];
        $email = $user['email'];
        $phone = $user['phone'];
        $age = $user['age'];
        $sex = '';

        $location = $user['location'];
        $lat = $user['lat'];
        $long = $user['lon'];
        $suburb = $user['suburb'];
        $state = $user['state'];
        $service = $user['service_location'];
        $aboutme = $user['aboutme'];
        $status = $user['status'];
        $password = '';
        if (isset($user['password'])) {
            $password = $user['password'];
        }

        $profileimages = $user['images'];
        $oldimages = $user['oldimages'];
        $gender = $user['regtype'];
        define('UPLOAD_DIR', '../../uploads/profile/');
        if (count($profileimages) > 0) {
            $imgc = 0;
            foreach ($profileimages as $key => $values) {
                $image_parts = explode(';base64,', $values['data']);
                $image_type_aux = explode('image/', $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $file = UPLOAD_DIR.$key;
                file_put_contents($file, $image_base64);
                if (isset($values['enlock'])) {
                    $images[] = array('image' => $key, 'price' => $imgprice, 'lock' => $values['enlock']);
                } else {
                    $images[] = array('image' => $key, 'price' => 0, 'lock' => false);
                }
            }
            foreach ($oldimages as $img) {
                array_push($images, $img);
            }
            for ($i = 0; $i < count($images); ++$i) {
                if (5 == $imgc) {
                    break;
                } else {
                    array_push($fimages, $images[$i]);
                }
                ++$imgc;
            }

            $profile = serialize($fimages);
            $response['newimages'] = true;
            $response['imgsare'] = $fimages;
        } else {
            $profile = serialize($oldimages);
            $response['newimages'] = false;
        }
    } else {
        $firstname = $user['first_name'];
        $lastname = $user['last_name'];
        $email = $user['email'];
        $phone = $user['phone'];
        $age = $user['age'];
        $sex = '';
        $location = '';
        $service = '';
        $aboutme = '';
        $status = $user['status'];

        $password = '';
        if (isset($user['password'])) {
            $password = $user['password'];
        }
        $lat = '';
        $long = '';
        $suburb = '';
        $profile = '';
        $state = '';

        $profileimages = $user['images'];
        $oldimages = $user['oldimages'];

        define('UPLOAD_DIR', '../../uploads/profile/');
        if (count($profileimages) > 0) {
            $imgc = 0;
            foreach ($profileimages as $key => $values) {
                $image_parts = explode(';base64,', $values['data']);
                $image_type_aux = explode('image/', $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $file = UPLOAD_DIR.$key;
                file_put_contents($file, $image_base64);
                $images[] = array('image' => $key, 'price' => 0, 'lock' => 0);
            }

            $profile = serialize($images);
            $response['newimages'] = true;
            $response['imgsare'] = $images;
        } else {
            $profile = serialize($oldimages);
            $response['newimages'] = false;
        }

        $gender = $user['regtype'];
    }
    $activation = md5($email.time());

    $res = $db->updateUserById($firstname, $lastname, $email, $phone, $age, $sex, $location, $service, $aboutme, $status, $activation, $password, $profile, $gender, $id, $lat, $long, $suburb, $state);

    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['code'] = 1;
        $response['message'] = 'Updated.';
    } elseif (2 == $res['stat']) {
        $response['error'] = true;
        $response['code'] = 2;
        $response['message'] = 'User exists.';
    } else {
        $response['error'] = true;
        $response['code'] = 3;
        $response['message'] = 'Something went wrong.';
    }
    // echo json response
    echoRespnse(200, $response);
});

$app->post('/updatecmspage', function () use ($app) {
    // check for required params
    //verifyRequiredParams(array('name', 'email', 'password'));
    $user = json_decode($app->request()->getBody(), true);
    $response = array();
    $postimg = '';
    $headerimg = '';
    $bannerimg = '';

    $id = $user['id'];

    $title = $user['title'];
    $handle = $user['handle'];
    $desc = $user['desc'];

    $ftdesc = '';
    if (isset($user['ftdesc'])) {
        $ftdesc = $user['ftdesc'];
    }
    $htitle = '';
    if (isset($user['htitle'])) {
        $htitle = $user['htitle'];
    }
    $location = '';
    if (isset($user['location'])) {
        $location = $user['location'];
    }
    $callus = '';
    if (isset($user['callus'])) {
        $callus = $user['callus'];
    }

    $seotitle = $user['seotitle'];
    $seodesc = $user['seodesc'];
    $seokey = $user['seokey'];

    $newheaderimg = $user['headerimg'];
    $oldheaderimg = $user['oldheaderimg'];

    $newpostimg = $user['postimg'];
    $oldpostimg = $user['oldpostimg'];

    $newbannerimg = $user['bannerimg'];
    $oldbannerimg = $user['oldbannerimg'];

    define('UPLOAD_DIR', '../../uploads/cmspages/');

    if (count($newheaderimg) > 0) {
        $ghimg = '';
        foreach ($newheaderimg as $key => $values) {
            $image_parts = explode(';base64,', $values);
            $image_type_aux = explode('image/', $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = UPLOAD_DIR.$key;
            file_put_contents($file, $image_base64);
            $ghimg = $key;
        }

        $headerimg = $ghimg;
        $response['headerimg'] = $headerimg;
    } else {
        $headerimg = $oldheaderimg;
        $response['headerimg'] = $headerimg;
    }

    if (count($newpostimg) > 0) {
        $gpimg = '';
        foreach ($newpostimg as $key => $values) {
            $image_parts = explode(';base64,', $values);
            $image_type_aux = explode('image/', $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = UPLOAD_DIR.$key;
            file_put_contents($file, $image_base64);
            $gpimg = $key;
        }

        $postimg = $gpimg;
        $response['postimg'] = $postimg;
    } else {
        $postimg = $oldpostimg;
        $response['postimg'] = $postimg;
    }

    if (count($newbannerimg) > 0) {
        $gbimg = '';
        foreach ($newbannerimg as $key => $values) {
            $image_parts = explode(';base64,', $values);
            $image_type_aux = explode('image/', $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = UPLOAD_DIR.$key;
            file_put_contents($file, $image_base64);
            $gbimg = $key;
        }

        $bannerimg = $gbimg;
        $response['bannerimg'] = $bannerimg;
    } else {
        $bannerimg = $oldbannerimg;
        $response['bannerimg'] = $bannerimg;
    }

    $db = new DbHandler();

    $res = $db->updatePage($id, $title, $handle, $desc, $seotitle, $seokey, $seodesc, $bannerimg, $postimg, $headerimg, $ftdesc, $htitle, $location, $callus);

    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['code'] = 1;
        $response['message'] = 'Updated.';
    } else {
        $response['error'] = true;
        $response['code'] = 3;
        $response['message'] = 'Something went wrong.';
    }
    // echo json response
    echoRespnse(200, $response);
});

$app->post('/getcmspages', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //return echoRespnse(200, $user);
    $page = $user['datatable']['pagination']['page'];
    $perpage = $user['datatable']['pagination']['perpage'];
    $sort = $user['datatable']['sort']['sort'];
    $sfield = $user['datatable']['sort']['field'];

    $db = new DbHandler();
    $res = $db->getCmsPages($sort, $sfield, $page, $perpage);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['data'] = $res['users'];
        $response['meta']['page'] = (int) $page;
        $pagec = ceil($res['total'] / $perpage);
        $response['meta']['pages'] = (int) $pagec;
        $response['meta']['perpage'] = (int) $perpage;
        $response['meta']['total'] = $res['total'];
        $response['meta']['sort'] = $sort;
        $response['meta']['field'] = $sfield;

        //$response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Email or password is incorrect';
        echoRespnse(204, $response);
    }
});

$app->get('/getcmspage/:id', function ($id) {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();

    // fetch task
    $result = $db->getCmsPage($id);

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->post('/getearnings', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //return echoRespnse(200, $user);
    $year = $user['year'];

    $db = new DbHandler();
    $res = $db->adminEarningsByYear($year);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['data'] = $res['data'];

        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'no data';
        echoRespnse(200, $response);
    }
});

$app->post('/addpackage', function () use ($app) {
    $user = json_decode($app->request()->getBody(), true);

    $packagefor = $user['packagefor'];
    $packagename = $user['packagename'];
    $desc = $user['desc'];
    $bonus = $user['bonus'];
    $price = $user['price'];

    $db = new DbHandler();
    $res = $db->addNewPackage($packagefor, $packagename, $desc, $bonus, $price);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(204, $response);
    }
});

$app->post('/getpackages', function () use ($app) {
    //  global $user_id;
    parse_str($app->request()->getBody(), $user);
    //return echoRespnse(200, $user);
    $page = $user['datatable']['pagination']['page'];
    $perpage = $user['datatable']['pagination']['perpage'];
    $sort = $user['datatable']['sort']['sort'];
    $sfield = $user['datatable']['sort']['field'];

    $db = new DbHandler();
    $res = $db->getAllPackages($sort, $sfield, $page, $perpage);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['data'] = $res['users'];
        $response['meta']['page'] = (int) $page;
        $pagec = ceil($res['total'] / $perpage);
        $response['meta']['pages'] = (int) $pagec;
        $response['meta']['perpage'] = (int) $perpage;
        $response['meta']['total'] = $res['total'];
        $response['meta']['sort'] = $sort;
        $response['meta']['field'] = $sfield;

        //$response['message'] = 'Success';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Email or password is incorrect';
        echoRespnse(204, $response);
    }
});

$app->post('/deletepackage', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    //$user = $app->request()->getBody();
    $uids = $user['userids'];
    //return echoRespnse(200, $uids);

    $db = new DbHandler();
    $res = $db->deletePackages($uids);
    //print_r($res);
    //return echoRespnse(200,$res);
    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['message'] = 'Package deleted successfully';
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(204, $response);
    }
});

$app->get('/getpackage/:id', function ($id) {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();

    // fetch task
    $result = $db->getPackage($id);

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->post('/updatepackage', function () use ($app) {
    // check for required params
    //verifyRequiredParams(array('name', 'email', 'password'));
    $user = json_decode($app->request()->getBody(), true);
    $response = array();

    $id = $user['id'];

    $packagefor = $user['packagefor'];
    $packagename = $user['packagename'];
    $desc = $user['desc'];
    $bonus = $user['bonus'];
    $price = $user['price'];

    $db = new DbHandler();

    $res = $db->updatePackage($id, $packagefor, $packagename, $desc, $bonus, $price);

    if (1 == $res['stat']) {
        $response['error'] = false;
        $response['code'] = 1;
        $response['message'] = 'Updated.';
    } else {
        $response['error'] = true;
        $response['code'] = 3;
        $response['message'] = 'Something went wrong.';
    }
    // echo json response
    echoRespnse(200, $response);
});

//Frontside api
$app->get('/cmslist', function () {
    $response = array();
    $db = new DbHandler();
    // fetch task
    $result = $db->cmslist();

    $response['error'] = false;
    $response['data'] = array();
    // looping through result and preparing array
    while ($page = $result->fetch_assoc()) {
        $response['data'][] = $page;
    }
    echoRespnse(200, $response);
});

$app->get('/setting', function () {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();

    // fetch task
    $result = $db->getSiteInfo();

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->post('/profilelist', function () use ($app) {
    //  global $user_id;
    $response = array();
    $profiledata = array();
    $db = new DbHandler();
    // fetch task
    $user = json_decode($app->request()->getBody(), true);

    $name = '';
    if (isset($user['name'])) {
        $name = $user['name'];
    }
    $height = '';
    if (isset($user['height'])) {
        $height = $user['height'];
    }
    $weight = '';
    if (isset($user['weight'])) {
        $weight = $user['weight'];
    }
    $rad = '';
    if (isset($user['radius'])) {
        $rad = $user['radius'];
    }
    $subs = '';

    // if(!empty($user['suburb']) && count($user['suburb']) > 0){
    //   foreach($user['suburb'] as $sv){
    //     if($sv != ''){
    //       $fpv = "'".$sv."'";
    //       array_push($subs, $fpv);
    //     }
    //   }
    // }

    if (isset($user['suburb']) && !empty($user['suburb'])) {
        $subs = $user['suburb'];
    }

    $result = $db->profileList($user['state'], $name, $height, $weight, $user['lat'], $user['long'], $rad, $subs);

    foreach ($result as $profile) {
        // code...
        // $count=$db->countcomments($profile['id']);
        // $profile['comments']=$count;
        array_push($profiledata, $profile);
    }
    //print_r($profiledata);
    //
    if (null != $result) {
        $response['error'] = false;
        $response['data'] = $profiledata;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'The requested resource empty';
        echoRespnse(200, $response);
    }
});

$app->get('/highlightprofile', function () {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();
    // fetch task
    $result = $db->gethighlightprofile();
    if (null != $result) {
        $response['error'] = false;
        $random_keys = array_rand($result, 1);
        //print_r($random_keys);
        $data = $result[$random_keys];
        $response['data'] = $result[$random_keys];
        // $count=$db->countcomments($data['id']);
        // $response["count"]=$count;
        //echo $count;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'The requested resource empty';
        echoRespnse(200, $response);
    }
});

$app->get('/checkunlockstat/:id', function ($id) {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();
    // fetch task
    $result = $db->checkWallet($id);
    if (null != $result) {
        $response['error'] = false;
        $response['stat'] = $result;
        //echo $count;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Now enough money';
        echoRespnse(200, $response);
    }
});

$app->post('/payforunlock', function () use ($app) {
    //  global $user_id;
    $user = json_decode($app->request()->getBody(), true);

    $response = array();
    $db = new DbHandler();
    // fetch task
    $result = $db->payForUnlock($user['id'], $user['amount'], $user['unlockid']);
    if (null != $result && 0 != $result['stat']) {
        $response['error'] = false;
        $response['stat'] = 1;
        //echo $count;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Something went wrong';
        echoRespnse(200, $response);
    }
});

$app->get('/recentlisting', function () {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();
    // fetch task
    $result = $db->getrecentlisting();
    if (null != $result) {
        $response['error'] = false;
        $response['data'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'The requested resource empty';
        echoRespnse(200, $response);
    }
});

$app->get('/getsuburbs/:state', function ($state) {
    //  global $user_id;
    $response = array();
    $db = new DbHandler();
    // fetch task
    $result = $db->getsuburbs($state);
    if (null != $result) {
        $response['error'] = false;
        $response['data'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'The requested resource empty';
        echoRespnse(200, $response);
    }
});

/*
 * User Registration
 * url - /register
 * method - POST
 * params - name, email, password
 */

$app->post('/register', function () use ($app) {
    // check for required params
    //verifyRequiredParams(array('name', 'email', 'password'));
    $user = json_decode($app->request()->getBody());
    $response = array();
    $images = array();
    $adminadd = false;

    $db = new DbHandler();

    $siteinfo = $db->getSiteInfo();
    $imgprice = $siteinfo['imgprice'];

    if (isset($user->adminadd)) {
        $adminadd = $user->adminadd;
    }
    define('UPLOAD_DIR', '../../uploads/profile/');
    if ('Female' == $user->regtype) {
        $firstname = $user->firstName;
        $lastname = $user->lastName;
        $email = $user->email;
        $phone = $user->phone;
        $age = $user->age;
        $sex = '';
        if (isset($user->sex)) {
            $sex = $user->sex;
        }

        $lat = $user->lat;
        $long = $user->long;
        $suburb = $user->suburb;

        $location = $user->searchControl;
        $state = '';
        if (isset($user->locstate) && !empty($user->locstate)) {
            $state = $user->locstate;
        }
        $service = $user->service;
        $aboutme = $user->aboutme;
        $status = 1;
        if (isset($user->status)) {
            $status = $user->status;
        }
        /*$status = $user->status;*/
        $password = $user->password;
        $profileimages = $user->images;
        $gender = $user->regtype;
        foreach ($profileimages as $key => $values) {
            //print_r($values->data);
            //print_r($values->price);
            //print_r($values->enlock);
            $image_parts = explode(';base64,', $values->data);
            $image_type_aux = explode('image/', $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = UPLOAD_DIR.$key;
            file_put_contents($file, $image_base64);
            if (isset($values->enlock)) {
                $images[] = array('image' => $key, 'price' => $imgprice, 'lock' => $values->enlock);
            } else {
                $images[] = array('image' => $key, 'price' => 0, 'lock' => 0);
            }
        }
        $profile = serialize($images);
    } else {
        $firstname = $user->firstName;
        //$lastname = $user->lastName;
        $lastname = '';
        $email = $user->email;
        $phone = $user->phone;
        $age = $user->age;
        $sex = '';
        $location = '';
        $state = '';
        $service = '';
        $aboutme = '';
        $lat = '';
        $long = '';
        $suburb = '';
        $status = 1;
        $password = $user->password;
        $profileimages = $user->images;
        $gender = $user->regtype;

        foreach ($profileimages as $key => $values) {
            $image_parts = explode(';base64,', $values->data);
            $image_type_aux = explode('image/', $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = UPLOAD_DIR.$key;
            file_put_contents($file, $image_base64);
            $images[] = array('image' => $key, 'price' => 0, 'lock' => 0);
        }
        $profile = serialize($images);
    }

    $activation = md5($email.time());

    $res = $db->createUser($firstname, $lastname, $email, $phone, $age, $sex, $location, $state, $service, $aboutme, $status, $activation, $password, $profile, $gender, $adminadd, $lat, $long, $suburb);

    if (USER_CREATED_SUCCESSFULLY == $res['status']) {
        signupsuccess($res['user']['email'], $res['user']['firstname']);
        sendverifybyemail($res['user']['email'], $res['user']['firstname'], $res['user']['vcode']);

        $response['error'] = false;
        $response['code'] = 1;
        $response['message'] = 'You are successfully registered.';
    } elseif (USER_CREATE_FAILED == $res['status']) {
        $response['error'] = true;
        $response['code'] = 2;
        $response['res'] = $res;
        $response['message'] = 'Oops! An error occurred while registereing';
    } elseif (USER_ALREADY_EXISTED == $res['status']) {
        $response['error'] = true;
        $response['code'] = 3;
        $response['message'] = 'Sorry, this email already exist';
    }
    // echo json response
    echoRespnse(201, $response);
});

$app->post('/updateprofile/:id', function ($userid) use ($app) {
    // check for required params
    //verifyRequiredParams(array('name', 'email', 'password'));
    $user = json_decode($app->request()->getBody(), true);
    $response = array();
    $response['resp'] = array();
    $images = array();
    $fimages = array();

    $videos = serialize(array());
    if (isset($user['videos'])) {
        $videos = serialize($user['videos']);
    }

    $db = new DbHandler();
    $gensets = $db->getSiteInfo();
    $imgprice = $gensets['imgprice'];

    define('UPLOAD_DIR', '../../uploads/profile/');
    if ('Female' == $user['gender']) {
        $firstname = $user['firstName'];
        $lastname = $user['lastName'];
        $email = $user['email'];
        $phone = $user['phone'];
        $age = $user['age'];
        $sex = $user['sex'];
        $location = $user['location'];
        $state = $user['locstate'];
        $suburb = $user['suburb'];
        $lat = $user['lat'];
        $lon = $user['lon'];
        $service = $user['service'];
        $aboutme = $user['aboutme'];

        $weight = $user['weight'];
        if (false == $user['pausetime']) {
            $pausetime = 1;
        } elseif (true == $user['pausetime']) {
            $pausetime = 2;
            $ptime = $user['dateremains'];
            $db->updateptime($userid, $ptime);
        }

        $height = $user['height'];
        $username = $user['username'];
        $highlight = $user['highlight'];

        $wechat = $user['wechat'];
        $whatsapp = $user['whatsapp'];
        $skype = $user['skype'];
        $viber = $user['viber'];

        if (isset($user['password']) && !empty($user['password'])) {
            $password = $user['password'];
            $response['resp'] = $db->updatepassword($userid, $password);
        }
        $oldimages = $user['oldimages'];
        $profileimages = $user['images'];
        $gender = $user['gender'];
        if (count($profileimages) > 0) {
            if ($oldimages) {
                foreach ($oldimages as $img) {
                    array_push($images, $img);
                }
            }
            $imgc = 0;
            foreach ($profileimages as $key => $values) {
                $image_parts = explode(';base64,', $values['data']);
                $image_type_aux = explode('image/', $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $file = UPLOAD_DIR.$key;
                file_put_contents($file, $image_base64);

                if ($values['enlock']) {
                    $images[] = array('image' => $key, 'price' => $imgprice, 'lock' => $values['enlock']);
                } else {
                    $images[] = array('image' => $key, 'price' => 0, 'lock' => $values['enlock']);
                }
            }

            for ($i = 0; $i < count($images); ++$i) {
                if (5 == $imgc) {
                    break;
                } else {
                    array_push($fimages, $images[$i]);
                }
                ++$imgc;
            }
            $profile = serialize($fimages);
            $response['newimages'] = true;
            $response['imgsare'] = $fimages;
        } else {
            $profile = serialize($oldimages);
            $response['newimages'] = false;
        }
    } else {
        $firstname = $user['firstName'];
        //$lastname = $user['lastName'];
        $lastname = '';
        $email = $user['email'];
        $phone = $user['phone'];
        $age = $user['age'];
        if (isset($user['password']) && !empty($user['password'])) {
            $password = $user['password'];
            $response['resp'] = $db->updatepassword($userid, $password);
        }
        $profileimages = $user['images'];
        $oldimages = $user['oldimages'];
        $gender = $user['gender'];
        $sex = '';
        $location = '';
        $service = '';
        $aboutme = '';
        $weight = '';
        $pausetime = 1;
        $height = '';
        $username = '';
        $highlight = '';
        $wechat = '';
        $whatsapp = '';
        $skype = '';
        $viber = '';
        $state = '';
        $suburb = '';
        $lat = '';
        $lon = '';

        if (count($profileimages) > 0) {
            foreach ($profileimages as $key => $values) {
                $image_parts = explode(';base64,', $values);
                $image_type_aux = explode('image/', $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $file = UPLOAD_DIR.$key;
                file_put_contents($file, $image_base64);
                $images[] = $key;
            }
            $profile = serialize($images);
        } else {
            $profile = serialize($oldimages);
            $response['newimages'] = false;
        }
    }

    //$activation=md5($email.time());

    $res = $db->updateUser($userid, $firstname, $lastname, $username, $weight, $height, $gender, $age, $email, $phone, $sex, $skype, $whatsapp, $viber, $wechat, $location, $service, $aboutme, $highlight, $pausetime, $profile, $videos, $state, $suburb, $lat, $lon);

    if (1 == $res) {
        $response['error'] = false;
        $response['code'] = 1;
        $response['message'] = 'Profile updated successfully.';
    } elseif (0 == $res) {
        $response['error'] = true;
        $response['code'] = 2;
        $response['message'] = 'Oops! An error occurred while registereing';
    } elseif (USER_ALREADY_EXISTED == $res) {
        $response['error'] = true;
        $response['code'] = 3;
        $response['message'] = 'Sorry, this email already existed';
    }
    // echo json response
    echoRespnse(201, $response);
});

$app->post('/changefirstimg', function () use ($app) {
    // check for required params
    //verifyRequiredParams(array('name', 'email', 'password'));
    $user = json_decode($app->request()->getBody());

    $profileimages = $user->images;

    define('UPLOAD_DIR', '../../uploads/profile/');

    if (count($profileimages) > 0) {
        $imgc = 0;
        foreach ($profileimages as $key => $values) {
            $image_parts = explode(';base64,', $values->data);
            $image_type_aux = explode('image/', $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = UPLOAD_DIR.$key;
            file_put_contents($file, $image_base64);
            $images = array('image' => $key, 'price' => $values->price, 'lock' => $values->enlock);
        }
        $response['newimage'] = true;
        $response['imgsare'] = $images;
        $response['error'] = false;
    } else {
        $response['newimage'] = false;
        $response['error'] = false;
    }
    // echo json response
    echoRespnse(201, $response);
});

function base64_to_jpeg($base64_string, $output_file)
{
    // open the output file for writing
    $ifp = fopen($output_file, 'wb');

    // split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = explode(',', $base64_string);

    // we could add validation here with ensuring count( $data ) > 1
    fwrite($ifp, base64_decode($data[1]));

    // clean up the file resource
    fclose($ifp);

    return $output_file;
}

/*
 * User Forgot
 * url - /forgot
 * method - POST
 * params - email
 */
$app->post('/forgot', function () use ($app) {
    // check for required params
    //verifyRequiredParams(array('username', 'password'));
    $login_data = json_decode($app->request()->getBody());
    // reading post params
    $email = $login_data->email;
    $response = array();
    $db = new DbHandler();
    // check for correct email
    $user = $db->getUserByEmail($email);
    if (null != $user) {
        $response['error'] = false;
        $sendemail = forgotemail($user['email'], $user['id'], $user['first_name']);
        $response['status'] = $sendemail;
    } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = 'Account not found please signup now!!';
    }
    echoRespnse(200, $response);
});

/*
 * User verify
 * url - /verify
 * method - POST
 * params - email
 */
$app->post('/verify', function () use ($app) {
    // check for required params
    //verifyRequiredParams(array('username', 'password'));
    $login_data = json_decode($app->request()->getBody());
    // reading post params
    $email = $login_data->email;
    $textverify = $login_data->textverify;
    $response = array();
    $db = new DbHandler();
    // check for correct email
    $user = $db->getverify($email, $textverify);
    // echoRespnse(200, $user);
    // exit();
    if (null != $user) {
        if ('Male' == $user['gender']) {
            $responseresult = $db->updateustat($user['id'], 3);
        } else {
            $responseresult = $db->updateustat($user['id'], 2);
        }
        $response['userinfo'] = $db->getUserByEmail($user['email']);
        $response['error'] = false;
        $response['upstat'] = $responseresult;
        $response['gender'] = $user['gender'];
    // $sendemail=forgotemail($user['email'],$user["id"],$user["first_name"]);
        //$response['status'] = $sendemail;
    } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = 'Activation code is wrong or account not found, please signup now!';
    }
    echoRespnse(200, $response);
});

/*
 * User verify
 * url - /verify
 * method - POST
 * params - email
 */
$app->post('/resendcode', function () use ($app) {
    // check for required params
    //verifyRequiredParams(array('username', 'password'));
    $login_data = json_decode($app->request()->getBody());
    // reading post params
    $email = $login_data->email;
    $response = array();
    $db = new DbHandler();
    // check for correct email
    $user = $db->resendcode($email);
    //echo $user['sendmessage'];
    if (false != $user['sendmessage']) {
        $responseresult = $db->updatecode($user['id'], $user['verification_code']);
        $response['error'] = false;
        $response['status'] = $responseresult['stat'];
    } else {
        // unknown error occurred
        $response['error'] = true;
        $response['message'] = 'Resend verification code failed.please try again later.';
    }
    echoRespnse(200, $response);
});

/*
 * User Login
 * url - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function () use ($app) {
    // check for required params
    //verifyRequiredParams(array('username', 'password'));
    $login_data = json_decode($app->request()->getBody());
    // reading post params
    $email = $login_data->email;
    $password = $login_data->password;
    $response = array();

    $db = new DbHandler();
    // check for correct email and password
    if ($db->checkLogin($email, $password)) {
        // get the user by email
        $user = $db->getUserByEmail($email);

        if (null != $user) {
            if (1 == $user['adminstat']) {
                if (3 == $user['status'] || 2 == $user['status']) {
                    $response['activation'] = true;
                    $response['error'] = false;
                    $response['message'] = 'You are successfully Logged In';
                    $response['id'] = $user['id'];
                    $response['first_name'] = $user['first_name'];
                    $response['last_name'] = $user['last_name'];
                    $response['email'] = $user['email'];
                    $response['phone'] = $user['phone'];
                    $response['gender'] = $user['gender'];
                    $response['apiKey'] = $user['api_key'];
                    $response['location'] = $user['location'];
                    $response['service_location'] = $user['service_location'];
                    $response['sexual'] = $user['sexual'];
                    $response['age'] = $user['age'];
                    $response['aboutme'] = $user['aboutme'];
                    $response['password'] = $user['password'];
                    $response['images'] = $user['images'];
                } else {
                    if (1 == $user['status']) {
                        $response['error'] = true;
                        $response['activation'] = false;
                    } elseif (4 == $user['status']) {
                        $response['error'] = true;
                        $response['message'] = 'Admin has rejected your profile request.';
                    }
                }
            } else {
                $response['error'] = true;
                $response['message'] = 'Admin has deactivated your profile.';
            }
        } else {
            // unknown error occurred
            $response['error'] = true;
            $response['activation'] = true;
            $response['message'] = 'User not found.you need to create an account or you need to verified.';
        }
    } else {
        // user credentials are wrong
        $response['error'] = true;
        $response['message'] = 'Login failed. Incorrect credentials';
    }

    echoRespnse(200, $response);
});

$app->post('/girl', function () use ($app) {
    // check for required params
    //verifyRequiredParams(array('username', 'password'));
    $login_data = json_decode($app->request()->getBody());
    // reading post params
    $girlid = $login_data->id;
    $response = array();
    $db = new DbHandler();
    // fetch task
    $result = $db->getUserById($girlid);
    $testimonials = $db->getptestimonials($girlid);
    $comments = $db->getcomments($girlid);
    $complaints = $db->getcomplaints($girlid);
    $response['comments'] = $comments;
    $response['complaints'] = $complaints;

    if (1 == $testimonials['stat']) {
        $response['testimonials'] = $testimonials['testimonials'];
    }
    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->get('/getfrontpackages/:gender', function ($gender) {
    $response = array();
    $db = new DbHandler();
    // fetch task
    $result = $db->getFrontPackages($gender);

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->post('/boy', function () use ($app) {
    // check for required params
    //verifyRequiredParams(array('username', 'password'));
    $login_data = json_decode($app->request()->getBody());
    // reading post params
    $girlid = $login_data->id;
    $response = array();
    $db = new DbHandler();
    // fetch task
    $result = $db->getUserById($girlid);
    $testimonials = $db->getptestimonials($girlid);
    $comments = $db->getcomments($girlid);
    $response['comments'] = $comments;

    if (1 == $testimonials['stat']) {
        $response['testimonials'] = $testimonials['testimonials'];
    }
    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;
        $response['sdata'] = $result;
        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = "The requested resource doesn't exists";
        echoRespnse(404, $response);
    }
});

$app->get('/tesinomials/:id', function ($id) {
    $response = array();
    $db = new DbHandler();
    // fetch task
    $testimonials = $db->getptestimonials($id);

    if (1 == $testimonials['stat']) {
        $response['testimonials'] = $testimonials['testimonials'];
        $response['error'] = false;
    } else {
        $response['error'] = true;
    }

    echoRespnse(200, $response);
});

$app->get('/getcomments/:id', function ($id) {
    $response = array();
    $db = new DbHandler();
    // fetch task
    $comments = $db->getcomments($id);

    if (count($comments) > 0) {
        $response['comments'] = $comments;
        $response['error'] = false;
    } else {
        $response['error'] = true;
    }

    echoRespnse(200, $response);
});

$app->get('/getcomplaints/:id', function ($id) {
    $response = array();
    $db = new DbHandler();
    // fetch task
    $complaints = $db->getcomplaints($id);

    if (count($complaints) > 0) {
        $response['complaints'] = $complaints;
        $response['error'] = false;
    } else {
        $response['error'] = true;
    }

    echoRespnse(200, $response);
});

$app->post('/searchtestinomy', function () use ($app) {
    $response = array();
    $data = json_decode($app->request()->getBody(), true);

    $id = $data['userid'];
    $number = $data['number'];
    $db = new DbHandler();
    // fetch task
    $testimonials = $db->searchTestonomy($id, $number);

    if (1 == $testimonials['stat']) {
        $response['testimonials'] = $testimonials['testimonials'];
        $response['error'] = false;
    } else {
        $response['error'] = true;
    }

    echoRespnse(200, $response);
});

$app->post('/uploadvideo', function () use ($app) {
    $videofile = json_decode($app->request()->getBody(), true);
    $response = array();

    define('UPLOAD_DIR', '../../uploads/videos/');

    if (!empty($videofile['value'])) {
        $file = UPLOAD_DIR.$videofile['name'];
        file_put_contents($file, base64_decode($videofile['value']));

        $response['error'] = false;
        $response['upvlink'] = '../../uploads/videos/'.$videofile['name'];
    } else {
        $response['error'] = false;
    }
    echoRespnse(200, $response);
});

$app->post('/addtestinomy', function () use ($app) {
    // check for required params
    //verifyRequiredParams(array('username', 'password'));
    $data = json_decode($app->request()->getBody());
    // reading post params
    $girlid = $data->userid;
    $boynumber = $data->number;
    $name = $data->username;
    $testinomy = $data->testinomial;

    $response = array();
    $db = new DbHandler();
    // fetch task
    $result = $db->addNewTestinomy($girlid, $boynumber, $name, $testinomy);

    if (null != $result && 1 == $result['stat']) {
        $response['error'] = false;

        echoRespnse(200, $response);
    } else {
        $response['error'] = true;

        echoRespnse(404, $response);
    }
});

$app->post('/paymentupdate', function () use ($app) {
    parse_str($app->request()->getBody(), $user);
    // echoRespnse(200, $user);

    $response = array();
    $userid = $user['userid'];
    $paymentstate = $user['paydata']['state'];
    $bonus = $user['bonus'];
    $highlight = $user['highlight'];

    if ('approved' == $paymentstate) {
        //var_dump($paymentstate);
        $amount = $user['paydata']['transactions'][0]['amount']['total'] + $bonus;
        $type = 1;
        $timedone = date('Y-m-d H:i:s', strtotime($user['paydata']['transactions'][0]['related_resources'][0]['sale']['update_time']));

        $db = new DbHandler();
        $res = $db->updatePayment($userid, $amount, $type, $timedone, $highlight);
        //print_r($res);

        if (1 == $res['stat']) {
            $response['error'] = false;
            $response['wallet'] = $res['wallet'];
            $response['message'] = 'Payment successfull, amount has been added to your wallet.';
        } else {
            $response['error'] = true;
            $response['message'] = 'Something went wrong while updateing wallet information.';
        }

        echoRespnse(200, $response);
    } else {
        $response['error'] = true;
        $response['message'] = 'Payment is not approved. Try again.';
        echoRespnse(200, $response);
    }
});

//end dating website

/**
 * Forgot email send.
 */
function forgotemail($email, $userid, $firstname)
{
    $encrypt = md5(1290 * 3 + $userid);
    $message = 'Your password reset link send to your e-mail address.';
    $to = $email;
    $subject = 'Forget Password';
    $from = 'no-reply@dating.com';

    $body = 'Click here to reset your password <a href="http://pickadove.com/api/reset.php?userid='.$userid.'&encrypt='.$encrypt.'&action=reset">Reset passsword here</a>';

    $message = '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="BGtable" style="border-collapse: collapse; margin: 0px; padding: 0px; table-layout: fixed; width: 100% !important; height: 100% !important; "><tr><td align="center" valign="top" class="BGtable-inner">
         <!-- start header_1s -->
        <table align="center" border="0" cellpadding="0" cellspacing="0" class="wrap header" style="border-collapse: collapse;width: 100%;margin: 0 auto;" width="640"><tr><td align="center"  class="wrap radius-top header a" style="width: 100%; margin: 0px auto; background-color: #ECECEC; background-size: cover;" valign="top" width="100%"><div><table align="center" border="0" cellpadding="0" cellspacing="0" class="row" style="border-collapse: collapse;" width="100%"><tr><td align="center">
          <!--logo & menu-->
                 <table border="0" cellpadding="0" cellspacing="0" class="row" style="border-collapse: collapse;" width="100%"><tr><td align="center" class="block">
           <!--logo-->
                   <table align="left" border="0" cellpadding="0" cellspacing="0" class="logo fn" style="width:100%;  border-collapse: collapse;border: none;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><tr><td style="padding: 25px 0;"><a href="http://pickadove.com" style="color: #00d5c3;display: block; outline: medium none; text-align: center; text-decoration: none; width: 100%;"><img alt="" class="logo-img" editable="true" height="auto" style="vertical-align: middle; width: 200px;" label="logo" src="http://pickadove.com/uploads/logo-png.png" /></a></td></tr></table></td><td class="hide" width="88"></td><td align="center" class="block"></td></tr></table></td></tr></table></div></td></tr></table></td></tr></table>
      <!-- middle part-->
          <p><strong><span style="color: #000000;">Dear '.$firstname.',</span></strong></p><p><span style="color: #000000;">'.$body.'</span></p><p><span style="color: #000000;">&nbsp;</span></p><p><span style="font-family: Verdana; font-size: 10pt; color: #000000;">Thank You,<br /> <strong>Dating site</strong></span></p><p><span style="color: #000000;">&nbsp;</span></p>
      <!-- footer start-->
          <table align="center"  border="0" cellpadding="0" cellspacing="0" class="wrap bottom" style="border-collapse: collapse; width: 100%; margin: 0px auto; background-image: none; background-color: #484848;" width="100%"><tr class="social-icons"><td style="padding: 15px 0; text-align: center; display: block;"><a href="https://www.facebook.com/#" style="display: inline-block;  text-align: center; text-decoration: none; margin: 0 5px" >Facebook</a><a href="https://twitter.com/#" style="display: inline-block;  text-align: center; text-decoration: none; margin: 0 5px">Twitter</a></td></tr><tr><td align="center"><table align="center" border="0" cellpadding="0" cellspacing="0" class="row" style="border-collapse: collapse;" width="100%"><tr><td align="center" class="h6 t"  style="font-family: Raleway,Tahoma;color: #fff;font-weight: 400;font-size: 16px; line-height: 25px; padding: 0 0 25px;">©2018 Dating site. All Rights Reserved.</td></tr></table></td></tr></table>';
    $headers = 'From: '.strip_tags($from)."\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    $status = mail($to, $subject, $message, $headers);

    return $status;
}

function signupsuccess($email, $firstname)
{
    $to = $email;
    $subject = 'Registration';
    $from = 'no-reply@dating.com';

    $body = 'Your profile has been created successfully, please verify your account by using verification code sent to your registered number and email id.';

    $message = '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="BGtable" style="border-collapse: collapse; margin: 0px; padding: 0px; table-layout: fixed; width: 100% !important; height: auto !important; "><tr><td align="center" valign="top" class="BGtable-inner">
         <!-- start header_1s -->
        <table align="center" border="0" cellpadding="0" cellspacing="0" class="wrap header" style="border-collapse: collapse;width: 100%;margin: 0 auto;" width="640"><tr><td align="center"  class="wrap radius-top header a" style="width: 100%; margin: 0px auto; background-color: #ECECEC; background-size: cover;" valign="top" width="100%"><div><table align="center" border="0" cellpadding="0" cellspacing="0" class="row" style="border-collapse: collapse;" width="100%"><tr><td align="center">
          <!--logo & menu-->
                 <table border="0" cellpadding="0" cellspacing="0" class="row" style="border-collapse: collapse;" width="100%"><tr><td align="center" class="block">
           <!--logo-->
                   <table align="left" border="0" cellpadding="0" cellspacing="0" class="logo fn" style="width:100%;  border-collapse: collapse;border: none;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><tr><td style="padding: 25px 0;"><a href="http://pickadove.com" style="color: #00d5c3;display: block; outline: medium none; text-align: center; text-decoration: none; width: 100%;"><img alt="" class="logo-img" editable="true" height="auto" style="vertical-align: middle; width: 200px;" label="logo" src="http://pickadove.com/uploads/logo-png.png" /></a></td>
                   </table>
                   </td><td class="hide" width="88"></td><td align="center" class="block"></td>
                 </table>
                 </td>
        </table></div></td></table></td></table>
      <!-- middle part-->
          <p><strong><span style="color: #000000;">Dear '.$firstname.',</span></strong></p><p><span style="color: #000000;">'.$body.'</span></p><p><span style="color: #000000;">&nbsp;</span></p><p><span style="font-family: Verdana; font-size: 10pt; color: #000000;">Thank You,<br /> <strong>Dating site</strong></span></p><p><span style="color: #000000;">&nbsp;</span></p>
      <!-- footer start-->
          <table align="center"  border="0" cellpadding="0" cellspacing="0" class="wrap bottom" style="border-collapse: collapse; width: 100%; margin: 0px auto; background-image: none; background-color: #484848;" width="100%"><tr class="social-icons"><td style="padding: 15px 0; text-align: center; display: block;"><a href="https://www.facebook.com/#" style="display: inline-block;  text-align: center; text-decoration: none; margin: 0 5px" >Facebook</a><a href="https://twitter.com/#" style="display: inline-block;  text-align: center; text-decoration: none; margin: 0 5px">Twitter</a></td></tr><tr><td align="center"><table align="center" border="0" cellpadding="0" cellspacing="0" class="row" style="border-collapse: collapse;" width="100%"><tr><td align="center" class="h6 t"  style="font-family: Raleway,Tahoma;color: #fff;font-weight: 400;font-size: 16px; line-height: 25px; padding: 0 0 25px;">©2018 Dating site. All Rights Reserved.</td></tr></table></td></tr></table>';
    $headers = 'From: '.strip_tags($from)."\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    $status = mail($to, $subject, $message, $headers);

    return $status;
}

function sendverifybyemail($email, $firstname, $vcode)
{
    $to = $email;
    $subject = 'Verification code';
    $from = 'no-reply@dating.com';

    $body = 'Your verification code is: '.$vcode;

    $message = '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="BGtable" style="border-collapse: collapse; margin: 0px; padding: 0px; table-layout: fixed; width: 100% !important; height: auto !important; "><tr><td align="center" valign="top" class="BGtable-inner">
         <!-- start header_1s -->
        <table align="center" border="0" cellpadding="0" cellspacing="0" class="wrap header" style="border-collapse: collapse;width: 100%;margin: 0 auto;" width="640"><tr><td align="center"  class="wrap radius-top header a" style="width: 100%; margin: 0px auto; background-color: #ECECEC; background-size: cover;" valign="top" width="100%"><div><table align="center" border="0" cellpadding="0" cellspacing="0" class="row" style="border-collapse: collapse;" width="100%"><tr><td align="center">
          <!--logo & menu-->
                 <table border="0" cellpadding="0" cellspacing="0" class="row" style="border-collapse: collapse;" width="100%"><tr><td align="center" class="block">
           <!--logo-->
                   <table align="left" border="0" cellpadding="0" cellspacing="0" class="logo fn" style="width:100%;  border-collapse: collapse;border: none;mso-table-lspace: 0pt;mso-table-rspace: 0pt;"><tr><td style="padding: 25px 0;"><a href="http://pickadove.com" style="color: #00d5c3;display: block; outline: medium none; text-align: center; text-decoration: none; width: 100%;"><img alt="" class="logo-img" editable="true" height="auto" style="vertical-align: middle; width: 200px;" label="logo" src="http://pickadove.com/uploads/logo-png.png" /></a></td>
                   </table>
                   </td><td class="hide" width="88"></td><td align="center" class="block"></td>
                 </table>
                 </td>
        </table></div></td></table></td></table>
      <!-- middle part-->
          <p><strong><span style="color: #000000;">Dear '.$firstname.',</span></strong></p><p><span style="color: #000000;">'.$body.'</span></p><p><span style="color: #000000;">&nbsp;</span></p><p><span style="font-family: Verdana; font-size: 10pt; color: #000000;">Thank You,<br /> <strong>Dating site</strong></span></p><p><span style="color: #000000;">&nbsp;</span></p>
      <!-- footer start-->
          <table align="center"  border="0" cellpadding="0" cellspacing="0" class="wrap bottom" style="border-collapse: collapse; width: 100%; margin: 0px auto; background-image: none; background-color: #484848;" width="100%"><tr class="social-icons"><td style="padding: 15px 0; text-align: center; display: block;"><a href="https://www.facebook.com/#" style="display: inline-block;  text-align: center; text-decoration: none; margin: 0 5px" >Facebook</a><a href="https://twitter.com/#" style="display: inline-block;  text-align: center; text-decoration: none; margin: 0 5px">Twitter</a></td></tr><tr><td align="center"><table align="center" border="0" cellpadding="0" cellspacing="0" class="row" style="border-collapse: collapse;" width="100%"><tr><td align="center" class="h6 t"  style="font-family: Raleway,Tahoma;color: #fff;font-weight: 400;font-size: 16px; line-height: 25px; padding: 0 0 25px;">©2018 Dating site. All Rights Reserved.</td></tr></table></td></tr></table>';
    $headers = 'From: '.strip_tags($from)."\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    $status = mail($to, $subject, $message, $headers);

    return $status;
}

/**
 * Verifying required params posted or not.
 */
function verifyRequiredParams($required_fields)
{
    $error = false;
    $error_fields = '';
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ('PUT' == $_SERVER['REQUEST_METHOD']) {
        $app = \Slim\Slim::getInstance();
        parse_str($app->request()->getBody(), $request_params);
    }
    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field.', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        $response['error'] = true;
        $response['message'] = 'Required field(s) '.substr($error_fields, 0, -2).' is missing or empty';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Validating email address.
 */
function validateEmail($email)
{
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['error'] = true;
        $response['message'] = 'Email address is not valid';
        echoRespnse(400, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client.
 *
 * @param string $status_code Http response code
 * @param int    $response    Json response
 */
function echoRespnse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}
$app->run();
