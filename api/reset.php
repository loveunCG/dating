<?php
	require_once 'include/DbHandler.php';
	$response = array();
	$db = new DbHandler();
	if(isset($_GET['action']))
{
    if($_GET['action']=="reset")
    {
        $encrypt = ($_GET['encrypt']);
        $userid = ($_GET['userid']);
        $result = $db->verifypassword($encrypt);
        if($result)
        {
			echo'<form action="http://54.218.127.55/projects/Dating/api/reset.php" method="post">
				<h1>Reset your password here</h1>
				<p><input id="password" name="password" type="text" placeholder="password" onkeyup="check();"></p>
				<p><input id="re-password" name="re-password" type="password" placeholder="Re-password" onkeyup="check();">
				<span id="message"></span></p>
				<input name="action" type="hidden" value="forgot" /></p>
				<input name="userid" type="hidden" value="'.$userid.'" /></p>
				<p><input id="reset" type="submit" value="Reset" /></p>
			</form>';
        }
        else
        {
            echo 'Invalid key please try again. <a href="http://54.218.127.55/projects/Dating">Forget Password?</a>';
        }
    }
}
else
{
    if(isset($_POST['action']) && $_POST['action'] =="forgot")
	{
		$password = $_POST['password'];
		$userid = $_POST['userid'];
		$checkstatus=$db->updatepassword($userid,$password);

		if($checkstatus)
		{
			$message = "Your password changed sucessfully <a href=\" http://54.218.127.55/projects/Dating\">click here to login</a>.";
		}else
		{
			$message = 'Failed to update password.Please try again';
		}
		echo $message;
	}
}

?>
<script>
	var check = function() {
  if (document.getElementById('password').value ==
    document.getElementById('re-password').value) {
    document.getElementById('message').style.color = 'green';
    document.getElementById('message').innerHTML = 'matching';
  } else {
    document.getElementById('message').style.color = 'red';
    document.getElementById('message').innerHTML = 'not matching';
  }
}
</script>
