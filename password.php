<?php

$errors = array();              //Declaring an array to store the errors into it
$email= trim(filter_var($_POST['email'] ?? null,FILTER_SANITIZE_STRING)); //Declaring the variable email

if (isset($_POST['submit'])) {  //only do this code if the form has been submitted

    //Validating the email ID
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors['email1'] = true;
        
    }
    //If there are no errors on the page
    if(count($errors)===0){
            
            //Including the library and connecting it to the database
            include 'includes/library.php';
            $pdo = connectDB();

            //Making an query for the email in user_name 
            $query = "SELECT * FROM user_name WHERE email=?";
            $stmtCheck = $pdo->prepare($query);
            $stmtCheck->execute([$email]);
            $result=$stmtCheck->fetch();

            //If the email isn't registered
            if(!$result){
                $errors['emailErr'] = true; //login error is true
            }
            
            //if the email is registered
            else {      
                //Starting the session for the user              
                    session_start();
                    $_SESSION['email'] = $email;    //Recording the session for the email in email variable
                    
                    //Using the random to generate the random pin for the user for the OTP. This OTp
                    //will be sent to the user on the email provided by him.
                    $randompin = rand(1000,9999);
                    
                    //Updating the user_name for the entered email id, we are storing this because, it will match for the OTp entered by the user
                    //On the next page.
                    $query2 = "UPDATE user_name SET verification = ? WHERE email=?";
                    $stmt2 = $pdo->prepare($query2);
                    $stmt2->execute([$randompin,$email]);
                    
                    //Declaring the variable and storing the value rec from the query fro the email and username
                    $recieveremail = $result['email'];
                    
                    
                    //To send the Mail to the specific email defined by the user
                    //Reference: Lecture Slides

                    require_once "Mail.php";                                        //this includes the pear SMTP mail library
                    $from = " Story Password Reset <noreply@blackboard.com>";
                    $to = "$recieverusername <$recieveremail>";                     //putting the user's email here
                    $subject = "Password Reset";
                    //Message sent to the user on his email
                    $body = "Hi,
                     
We got the request to reset your Story password. 

Your verification code is $randompin. 

Please Don't share this code to anyone. If You didn't send the request for password change, please contact us through our email.
                    
Thank You!
From Team Story";
                    $host = "smtp.trentu.ca";
                    $headers = array ('From' => $from,
                    'To' => $to,
                    'Subject' => $subject);
                    $smtp = Mail::factory('smtp',
                    array ('host' => $host));
                    
                    $mail = $smtp->send($to, $headers, $body);
                    if (PEAR::isError($mail)) {
                     echo("<p>" . $mail->getMessage() . "</p>");
                    } else {
                        echo("<p>Message successfully sent!</p>");
                    }
                    //Redirecting to the Password Verification php to match the password entered by the user to the database password
                    //So, that user can change his pswd by entering the OTP
                    header("Location:passwordVerification.php");
                    exit();
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
                    <a href="dashboard.php"><i class="fa-solid fa-book-open-reader"></i></a>
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
                    <h1>Forgot Password</h1>
                    <p>
                        Enter your account's email and we'll send you an <br />
                        email to reset the password.
                    </p>
                    <span class="error <?=!isset($errors['emailErr']) ? 'hidden' : "";?>">Error: No account exists with this Email</span>
                    <!-- Display the error here using php If the email enter by the user doesn't match  -->
                    <div class="enterForm">
                        <div class="detailsPassword">
                            <!-- Taking Input of the email ID -->
                            <label for="email">EMAIL ADDRESS</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                size="40"
                                placeholder="name@example.com"
                                required />
                            <div>
                                <!-- Submit button   -->
                                <button
                                    type="submit"
                                    name="submit"
                                    value="submit"
                                    class="submit">
                                    SEND RESET OTP
                                </button>
                            </div>
                        </div>
                    </div>
                    <!--Forgot Email -->
                    
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
        <!-- Including the footer -->
        <?php include "includes/footer.php" ?>
    </body>
</html>
<!-- End -->
