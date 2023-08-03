<?php
    //This page will Destroy the session of the logined user, so that they can securely logout from their website
    //Starting the Session
    session_start();
    //Session destroy will destroy the activer user session for the username and email
    session_destroy();
    //Redirecting to the Login Page, once the user has clicked on the Logout Link
    header("Location: login.php");
    exit(); //Exit
?>