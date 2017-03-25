<?php
include "../db.php";

class Users {
	var $error  = false;
	var $errmsg = "";
	
	// Add (Register) a new user
	function NewUser( $username, $email, $password, $confirm ) {
		// Check format of username
		if ( !( $username = $this->ValidName( $username ) ) && $username != "" ) {
			return !( $this->error = true );
		}
		
		// Check format of password
		if ( !( $password = $this->ValidPassword( $password, $confirm ) ) ) {
			return !( $this->error = true );
		}
		
		// Check format of email address
		if ( !( $email = $this->ValidEmail( $email ) ) ) {
			return !( $this->error = true );
		}

		$hpassword = md5( $password );
		
		$q = "INSERT INTO " . TBL_QUESTIONS . " (username,email,password,created,lastlogin) VALUES " .
			 "($username,$email,$hpassword,SYSDATE(),SYSDATE() )";
		
		$result = mysqli_query( $this->connection, $q );
		if ( $this->debug == true ) {
			echo "Q $q". PHP_EOL;
			echo mysqli_error( $this->connection ) . PHP_EOL;
		}
		
		return $hpassword;
	}
	
	// Check if name is valid syntax
	function ValidName( $username ) {
		$username = trim( $username );
		$username = strip_tags( $username );
		$username = htmlspecialchars( $username );
		
		// Check if name has been registered already
		if ( $username != "" ) {
			if ( $this->IsRegisteredName( $username ) ) {
				$this->errmsg = "Name already registered.";
				return !( $this->error = true );
			}
		}
		
		return $username;
	}
	
	// Check if password is valid syntax
	function ValidPassword( $password, $confirm ) {
		$password = trim( $password );
		$password = strip_tags( $password );
		$password = htmlspecialchars( $password );
		
		// Password Required
		if ( $password == "" ) {
			$this->errmsg = "No password specified.";
			return false;
		}
		
		$confirm = trim( $confirm );
		$confirm = strip_tags( $confirm );
		$confirm = htmlspecialchars( $confirm );
		
		// Confirm Password Required
		if ( $confirm == "" ) {
			$this->errmsg = "No confirm password specified.";
			return false;
		}
		
		// Check that passwords match
		if ( $password != $confirm ) {
			$this->errmsg = "Password and confirm password mismatch.";
			return false;
		}
		
		return $password;
	}
	
	// Check if email is valid syntax
	function ValidEmail( $email ) {
		$email = trim( $email );
		$email = strip_tags( $email );
		$email = htmlspecialchars( $email );
		
		// Email Required
		if ( $email == "" ) {
			$this->errmsg = "No password specified.";
			return false;
		}
		
		// Check if valid format
		if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$this->errmsg = "Not a valid email address.";
			return false;
		}
		
		// Check if email is already registered
		if ( $this->IsRegisteredEmail( $email ) ) {
			$this->errmsg = "Email already registered.";
			return !( $this->error = true );
		}
		
		return $email;
	}
	
	function IsRegisteredName( $name ) {
		return false;
	}
	
	function IsRegisteredEmail( $name ) {
		return false;
	}
	
	function Login( $username, $password ) {
		
		$hpassword = md5( $password );
		return $hpassword;
	}
	
	function ResetPassword() {
		
	}
}

$users = new Users();
if ( isset( $_GET['new'] ) ) {
	$username = $_GET['username'];
	$email    = $_GET['email'];
    $password = $_GET[ 'password' ];
    $confirm  = $_GET[ 'confirm' ];

	$rc = $users->NewUser( $username, $email, $password, $confirm );
	echo $rc . $users->errmsg;
}
?>