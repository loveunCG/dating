<?php

	require_once 'include/DbHandler.php';
	/**
	 *Verify email address
	 * method GET
	*/

	$response = array();
	$db = new DbHandler();
	if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['activation']) && !empty($_GET['activation'])){
		// Verify data
		$email = $_GET['email']; // Set email variable
		$uactivation = $_GET['activation']; // Set hash variable
		// fetching all user tasks
		$result = $db->verifyEmail($email,$uactivation);
		if($result){
			$response='Your account has been activated, you can now login';
			$subject='Welcome to GreatColoradoHomes.com!';
			$name=$_GET['name'];
			$msg='<p><img style="display: block; margin-left: auto; margin-right: auto;" src="http://www.greatcoloradohomes.com/uploads/agent-1/Great-Colorado-Homes-Email-Header-2.jpg" alt="Great Colorado Homes Email Header" width="700" height="126" /></p>';
			$msg.="Thanks for registering on our website,<a href='http://54.202.119.58/greatcoloradohomes'>GreatColoradoHomes.com!</a> If you have any questions or need assistance, please feel free to contact me directly. I'm here to help if you need me.";
				$msg.='<table align="center"  border="0" cellpadding="0" cellspacing="0" class="wrap bottom" style="border-collapse: collapse; width: 100%; margin: 0px auto; background-image: none; background-color: #484848;" width="100%"><tr class="social-icons"><td style="padding: 15px 0; text-align: center; display: block;"><a href="https://www.facebook.com/greatcoloradohomesforsale" style="display: inline-block;  text-align: center; text-decoration: none; margin: 0 5px" >Facebook</a><a href="https://twitter.com/greatcolohomes" style="display: inline-block;  text-align: center; text-decoration: none; margin: 0 5px">Twitter</a></td></tr><tr><td align="center"><table align="center" border="0" cellpadding="0" cellspacing="0" class="row" style="border-collapse: collapse;" width="100%"><tr><td align="center" class="h6 t"  style="font-family: Raleway,Tahoma;color: #fff;font-weight: 400;font-size: 16px; line-height: 25px; padding: 0 0 25px;">©2017 GREATCOLORADOHOMES, Inc. All Rights Reserved.</td></tr></table></td></tr></table>';

			$to =$_GET['email'] ;
			$from='no-reply@greatcoloradohomes.com';
			$replyto='no-reply@greatcoloradohomes.com';
			$headers = "From: " . strip_tags($from) . "\r\n";
			$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
			$checkstatus=mail($to, $subject, $msg, $headers);
		}
		else
		{
			$response='Activation is failed or you already have activated your account.';
		}
	}else{
		$response='The url is either invalid';
	}
	echo $response;
?>
