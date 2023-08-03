<?php
//This is the Reset Password Page

if (isset($_POST['reset'])){ //only do this code if the form has been submitted
    
    //Starting the session for the particular user.
    session_start();
    $email = $_SESSION['email'];

    //Declaring the array for print and erros
    $print =array();
    $errors = array();

    // Including library and making a database connection
    include 'includes/library.php';
    $pdo = connectDB();
    
    //intilizing the password and confirm password variable 
    $password = trim(filter_var($_POST['password'] ?? NULL,FILTER_SANITIZE_STRING));
    $confirmpassword = trim(filter_var($_POST['confirmpassword'] ?? NULL,FILTER_SANITIZE_STRING));

    //CHecking for the strong password from the user
    if( !empty($password) && !empty($confirmpassword)  ){
        //IF password is less than 8, it will print the error
        if (strlen($_POST["password"]) <= '8') {
            $errors['tosmall'] = true; 
        }
        //IF THE PASSWORD IS NOT HAVING NUMBER, SHOW ERROR
        elseif(!preg_match("#[0-9]+#",$password)) {
            $errors['passNum'] = true; 
        }
        //IF THE PASSWORD IS NOT HAVING A CAPITAL LETTER, PRINT ERROR
        elseif(!preg_match("#[A-Z]+#",$password)) {
            $errors['passCaps'] = true; 
        }
        //IF THE PASSWORD IS NOT HAVING ANY SYMBOL, IT WILL SHOW ERROR
        elseif(!preg_match("#[\W]+#",$password)) {
            $errors['passSymbol'] = true; 
        }
        //IF THE ENTERED PASSWORD, DOES'NT MATCH WITH RE-ENTERED PASSWORD, IT WILL SHOW ERRORA
        if($password != $confirmpassword){
            $errors['passwordmatch'] = true;
        }
    }

    //IF THE ERROR IS NULL
    if(count($errors)===0){

        //password hashed
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);
        
        /********************************************
         * UPDATING user PASSWORD to the database
         ********************************************/
        $query3 = "UPDATE user_name SET password = ? WHERE email = ?";
        $stmt3 = $pdo->prepare($query3);
        $result2 = $stmt3->execute([$hashPassword, $email]);

        //if insert is done!
        if ($result2) {
            header("refresh:0.5; url=login.php");
            echo '<script type="text/javascript">
            alert("Your Password has been sucessfully updated! Please Login again to your account");
            </script>';
        } 

        //if insert is unsuccessful!
        else {
            $print['unsuccess'] = true;
        }
        
    }
}
?>
<!-- HTML Start -->
<!DOCTYPE html>
<html lang="en">
    <!-- Header -->
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Reset Password | Story</title>
        <link rel="stylesheet" href="styles/main.css" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <script
            src="https://kit.fontawesome.com/170f096220.js"
            crossorigin="anonymous"></script>
    </head>
    <!-- Body elements -->
    <body>
        <main>
            <div class="loginNav">
                <div>
                    <a href="#"><i class="fa-solid fa-book-open-reader"></i></a>
                </div>
                <div>
                    <span class="underlineHoverEffect"
                        ><a href="login.php" class="myAccount">LOG IN</a></span
                    >
                </div>
            </div>
            <div class="changePassword">
                <!-- Creating a Self-processing Form  -->
                <form id="resetPassPage" name="resetPassword" method="post">
                    <h1>NEW PASSWORD</h1>
                    <p>Use at least 8 or more characters with a mix of letters, numbers & symbols</p>
                    <span class="error <?=!isset($errors['tosmall']) ? 'hidden' : "";?>">Your Password Must Contain At Least 8 Characters!</span>
                        <span class="error <?=!isset($errors['passNum']) ? 'hidden' : "";?>">Your Password Must Contain At Least 1 Number!</span>
                        <span class="error <?=!isset($errors['passCaps']) ? 'hidden' : "";?>">Your Password Must Contain At Least 1 Capital Letter!</span>
                        <span class="error <?=!isset($errors['passSymbol']) ? 'hidden' : "";?>">Your Password Must Contain symbols!</span>
                        <span class="error <?=!isset($errors['passwordmatch']) ? 'hidden' : "";?>">Your Password Doesn't Match!</span>
                        
                        <span class="error <?=!isset($errors['unsuccess']) ? 'hidden' : "";?>">Error: Updating Password failed</span>
                        <!-- Display the error here using php If the email enter by the user doesn't match  -->
                    <div class="enterForm">
                        <div class="detailsPassword">
                            <!-- Taking Input of the email ID -->
                            <label for="validation">ENTER YOUR NEW PASSWORD</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                               
                                
                                required />
                                <label for="validation">RE-ENTER YOUR PASSWORD</label>
                            <input
                                type="password"
                                id="password"
                                name="confirmpassword"
                               
                                
                                required />
                                
                            <div>
                                <button
                                    type="submit"
                                    name="reset"
                                    value="submit"
                                    class="submit">
                                    SUBMIT
                                </button>
                            </div>
                        </div>
                    </div>
                   
                    <div class="accountPolicy">
                        <p>
                            Secure Login with reCAPTCHA subject to Google<br />
                            <a href="https://policies.google.com/terms?hl=en"
                                >Terms</a
                            >
                            &
                            <a href="https://policies.google.com/privacy?hl=en"
                                >Privacy</a
                            >
                        </p>
                    </div>
                </form>
                <!-- Form End -->
            </div>
        </main>
        <!-- Include the footer here -->
    </body>
</html>
<!-- End -->
