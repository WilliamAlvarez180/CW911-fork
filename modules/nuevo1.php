<?php
public function register(){		
	$message = '';
	if(!empty($_POST["register"]) && $_POST["email"] !='') {
		$sqlQuery = "SELECT * FROM ".$this->userTable." 
			WHERE email='".$_POST["email"]."'";
		$result = mysqli_query($this->dbConnect, $sqlQuery);
		$isUserExist = mysqli_num_rows($result);
		if($isUserExist) {
			$message = "User already exist with this email address.";
		} else {			
			$insertQuery = "INSERT INTO ".$this->userTable."(first_name, last_name, email, password, authtoken) 
			VALUES ('".$_POST["firstname"]."', '".$_POST["lastname"]."', '".$_POST["email"]."', '".md5($_POST["passwd"])."', '".$authtoken."')";
			$userSaved = mysqli_query($this->dbConnect, $insertQuery);
			if($userSaved) {				
				$link = "<a href='http://example.com/user-management-system/verify.php?authtoken=".$authtoken."'>Verify Email</a>";			
				$toEmail = $_POST["email"];
				$subject = "Verify email to complete registration";
				$msg = "Hi there, click on this ".$link." to verify email to complete registration.";
				$msg = wordwrap($msg,70);
				$headers = "From: info@webdamn.com";
				if(mail($toEmail, $subject, $msg, $headers)) {
					$message = "Verification email send to your email address. Please check email and verify to complete registration.";
				}
			} else {
				$message = "User register request failed.";
			}
		}
	}
	return $message;
}

public function savePassword(){
	$message = '';
	if($_POST['password'] != $_POST['cpassword']) {
		$message = "Password does not match the confirm password.";
	} else if($_POST['authtoken']) {
		$sqlQuery = "
			SELECT email, authtoken 
			FROM ".$this->userTable." 
			WHERE authtoken='".$_POST['authtoken']."'";			
		$result = mysqli_query($this->dbConnect, $sqlQuery);
		$numRows = mysqli_num_rows($result);
		if($numRows) {				
			$userDetails = mysqli_fetch_assoc($result);
			$authtoken = $this->getAuthtoken($userDetails['email']);
			if($authtoken == $_POST['authtoken']) {
				$sqlUpdate = "
					UPDATE ".$this->userTable." 
					SET password='".md5($_POST['password'])."'
					WHERE email='".$userDetails['email']."' AND authtoken='".$authtoken."'";	
				$isUpdated = mysqli_query($this->dbConnect, $sqlUpdate);	
				if($isUpdated) {
					$message = "Password saved successfully. Please <a href='login.php'>Login</a> to access account.";
				}
			} else {
				$message = "Invalid password change request.";
			}
		} else {
			$message = "Invalid password change request.";
		}	
	}
	return $message;
}
?>