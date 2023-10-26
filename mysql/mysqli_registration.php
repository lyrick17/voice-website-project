<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require("mysqli_connect.php");
require("mysqli_logs.php");

// file if user/admin is not logged in


// Registration Form Request; isset function makes sure the form submitted is for registration

// variables used on server-side error handling 
$param_error = array("usernameTaken" => false,
                            "emailTaken" => false,
                            "emailValid" => true);
$display_errors = array();


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['formType'])) {
    $error = 0;

    // 1 get all the values input first and protection from sql injection, then
    // 2 get username and email from database, to avoid same data
    // 3 before validating it if its empty or user/email already taken or password matched

    // 1
    $username = mysqli_real_escape_string($dbcon, trim($_POST['username']));
    $email = mysqli_real_escape_string($dbcon, trim($_POST['email']));
    $password = mysqli_real_escape_string($dbcon, trim($_POST['pword']));
    $usertype = "user";
    $hashedPass = md5($password);


    // 2
    $usernameCheck = "SELECT * FROM `users` WHERE username = '" . $username . "' ";
    $usernameResult = mysqli_query($dbcon, $usernameCheck);
    $emailCheck = "SELECT * FROM `users` WHERE email = '" . $email . "' ";
    $emailResult = mysqli_query($dbcon, $emailCheck);

    // 3
    if (empty($_POST['username'])) {
        $display_errors["user"] = " *please enter your username"; $error++;
    } else if (mysqli_num_rows($usernameResult) >= 1) {
        $display_errors['user'] = " *username already taken"; $error++;
        $param_error['usernameTaken'] = true;
    }

    if (empty($_POST['email'])) {
        $display_errors["email"] = " *please enter your email"; $error++;
    } else if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $display_errors["email"] = " *email not valid"; $error++;
        $param_error['emailValid'] = false;
    } else if (mysqli_num_rows($emailResult) >= 1) {
        $display_errors['email'] = " *email already taken"; $error++;
        $param_error['emailTaken'] = true;
    }

    if (empty($_POST['pword'])) {
        $display_errors["pass"] = " *please enter your password"; $error++;
    } else if ($_POST['pword'] != $_POST['pword2']) {
        $display_errors["pass"] = " *passwords do not match"; $error++;
    }

    if ($error == 0) {
        $query = mysqli_prepare($dbcon, "INSERT INTO users(username, email, pword, type, registration_date) 
                                        VALUES (?, ?, ?, ?, NOW())");
        mysqli_stmt_bind_param($query, "ssss", $username, $email, $hashedPass, $usertype);
        $result = mysqli_stmt_execute($query);

        if ($result) {
            // register is successful
            
            // get the new user info from db, with the user_id
            $get_user_query = "SELECT * FROM users WHERE (`username` = '". $username ."' AND `pword` = '". $hashedPass . "')";

            $userresult = @mysqli_query($dbcon, $get_user_query);

            $_SESSION = mysqli_fetch_array($userresult, MYSQLI_ASSOC);

            //$user_id = mysqli_query($dbcon, "SELECT user_id FROM users WHERE username = '$username'
            //    and email = '$email'");

            //$_SESSION['user_id'] = mysqli_fetch_assoc($user_id);
            //$_SESSION['username'] = $username;
            //$_SESSION['email'] = $email;
            //$_SESSION['usertype'] = $usertype;

            // record/log it
            logs("register", $username, $dbcon);

            header("location: dashboard1.php");
            mysqli_free_result($result);
            mysqli_close($dbcon);
            exit();

        } else {
            echo " q<script>alert('System Error. \\nPlease try again or contact the System Administrator. We apologize for the inconvenience');</script>";
            echo " q<script>alert('Error: ". mysqli_error($dbcon) ."');</script>";
        }
        mysqli_close($dbcon);
        

    } else {

        // let javascript do the error handling, on form-validation.js
    }

    mysqli_close($dbcon);
}


// Login Form Request; isset function makes sure the form submitted is for login

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['formType2'])) {

    $username = (!empty($_POST['user'])) ? mysqli_real_escape_string($dbcon, trim($_POST['user'])) : FALSE;
    $password = (!empty($_POST['pass'])) ? mysqli_real_escape_string($dbcon, trim($_POST['pass'])) : FALSE;
    $hashedPass = md5($password);
    if ($username && $password) {

        $q1 = "SELECT * FROM users WHERE (`username` = '". $username ."' OR `email` = '". $username ."') AND `pword` = '". $hashedPass . "'";
        $result1 = @mysqli_query($dbcon, $q1);
        
        // check if username and pass OR email and pass
        // -note: papaikliin pa
        if (mysqli_num_rows($result1) > 0) {
            $_SESSION = mysqli_fetch_array($result1, MYSQLI_ASSOC);
            logs("login", $_SESSION['username'], $dbcon);  
            
            mysqli_free_result($result1);
            mysqli_close($dbcon);
            
            header("location: dashboard1.php");
            exit();
        } else {
            // display error
            
            $display_errors['login']  = "invalid username/email or password";
        }
    } else {
        $display_errors['login']  = "invalid username/email or password";
    }
}


?>
