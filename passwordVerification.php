<?php
//This page is used to verify the Password(OTP) by checking for the verification message, 
//if the verification message matches the code sent on the email, it will change the password

session_start();            //Start the session
$email=$_SESSION['email'];  //Intilizing the session of email
$errors = array();          //Array to store the error messages

//Verify the OTP
if (isset($_POST['verify'])) { //only do this code if the form has been submitted
    if(count($errors)===0){
        //Including the library and connecting it with the database
            include 'includes/library.php';
            $pdo = connectDB();

            //Query to fetch the result for the email entered by the user
            $query = "SELECT * FROM user_name WHERE email=?";
            $stmtCheck = $pdo->prepare($query);
            $stmtCheck->execute([$email]);
            $result=$stmtCheck->fetch();

            //If the username entered by the user doesn't exist
            if(!$result){
                $errors['verificationErr'] = true; //Verification false
            }
            //If the username exist in the database -> It will verify the password(OTp)
            else {
                $enteredverifcaiton = $_POST['validation'];
                //If verification code entered by the user matches the verification code for the result
                if ($enteredverifcaiton == $result['verification']) {
                    

                    //Redirecting the user to the reset password page
                    header("Location: resetPassword.php");
                    exit();
                } 
                //If the verification code fails
                else {
                    $errors['verificationErr'] = true;
                }
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
                    <h1>Change Password</h1>
                    <span class="error <?=!isset($errors['verificationErr']) ? 'hidden' : "";?>">Error: Verification Code is Invalid</span>
                   
                    <!-- Display the error here using php If the email enter by the user doesn't match  -->
                    <div class="enterForm">
                        <div class="detailsPassword">
                            <!-- Taking Input of the email ID -->
                            <label for="validation">VERIFY YOUR EMAIL:</label>
                            <input
                                type="text"
                                id="validation"
                                name="validation"
                               
                                placeholder="Validation Number"
                                required />
                            <div>
                                <button
                                    type="submit"
                                    name="verify"
                                    value="submit"
                                    class="submit">
                                    VERIFY
                                </button>
                            </div>
                        </div>
                    </div>
                    <!--Forgot Email -->
                    <div class="loginProblem">
                    <p>Don't wanna change your password?  <span class="underlineHoverEffect"
                            > <a href="login.php">Cancel</a></span
                        ></p>
                    </div>
                   
                </form>
                <!-- Form End -->
            </div>
        </main>
        <!-- Include the footer here -->
    </body>
</html>
<!-- End -->
