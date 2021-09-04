<?php
/*if((empty($_SERVER['HTTP_X_REQUESTED_WITH']) or strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') or empty($_POST)){/*Detect AJAX and POST request*/
 /* exit("Unauthorized Access");
} */
require('config.php');
require('functions.php');

/* Check Login form submitted */
if(!empty($_POST) && $_POST['uname']){
    /* Define return | here result is used to return user data and error for error message */
    $Return = array('result'=>array(), 'error'=>'');

    $email = safe_input($con, $_POST['uname']);
    $password = safe_input($con, $_POST['psw']);

    /* Server side PHP input validation */
    if(filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $Return['error'] = "Please enter a valid Email address.";
    }elseif($password===''){
        $Return['error'] = "Please enter Password.";
    }
    if($Return['error']!=''){
        output($Return);
    }

    /* Check Email and Password existence in DB */
    $result = pg_query($con, "SELECT * FROM tbl_users WHERE email='$email' AND password='$password' LIMIT 1"); /* AND password='".md5($password)."' */

    if($result != null && pg_num_rows($result)===1){
        $row = pg_fetch_assoc($result);
        /* Success: Set session variables and redirect to Protected page */
        $Return['result'] = $_SESSION['UserData'] = array('user_id'=>$row['user_id']); 
        $Return['result'] = header("Location: menu.html"); 
        die();
    } else {
        /* Unsuccessful attempt: Set error message */
        $Return['error'] = 'Invalid Login Credential.';
    }
    /*Return*/
    output($Return);
}


/* Check Registration form submitted */
if(!empty($_POST) && $_POST['Name']){
    /* Define return | here result is used to return user data and error for error message */
    $Return = array('result'=>array(), 'error'=>'');

    $name = safe_input($con, $_POST['Name']);
    $email = safe_input($con, $_POST['Email']);
    $password = safe_input($con, $_POST['Password']);

    /* Server side PHP input validation */
    if($name===''){
        $Return['error'] = "Please enter Full name.";
    }elseif (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
        $Return['error'] = "Please enter a valid Email address.";
    }elseif($password===''){
        $Return['error'] = "Please enter Password.";
    }
    if($Return['error']!=''){
        output($Return);
    }

    /* Check Email existence in DB */
    $result = pg_query($con, "SELECT * FROM tbl_users WHERE email='$email' LIMIT 1");
    if($result != null && pg_num_rows($result)==1){
        /*Email already exists: Set error message */
        $Return['error'] = 'You have already registered with us, please login.';
    }else{
        /*Insert registration data to user table (user_GUID is Unique Identifier and Default status is 0 means pending)*/
        pg_query($con, "INSERT INTO tbl_users (user_GUID, email, password, entry_date), '$email', '$password' ,NOW() )"); // ".md5($password)."
        $user_id = pg_get_serial_sequence('tbl_users','values'); /* Get the auto generated id used in the last query */
        /*Insert registration data to user profile table */
        pg_query($con, "INSERT INTO `tbl_user_profile` (user_id,name) VALUES('$user_id','$name')");
        /* Success: Set session variables and redirect to Protected page */
        $Return['result'] = $_SESSION['UserData'] = array('user_id'=>$user_id);
    }
    /*Return*/
    output($Return);
}

?>