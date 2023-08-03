<?php

//this declares an error array 
$errors = array();

//Declaring Variables
$email = trim(filter_var($_POST['email'] ?? null,FILTER_SANITIZE_STRING));
$password = trim(filter_var($_POST['password'] ?? null,FILTER_SANITIZE_STRING));
 
//Triggerd events from submit button

  //IF THE FORM IS SUBMITTED 
 if (isset($_POST['submit'])) { //only do this code if the form has been submitted

    include 'includes/library.php'; // Includes the library
    $pdo = connectDB();             //connnects to the database

    $query = "SELECT * FROM user_name WHERE email=?";  //selects the user filtered from the email
    $stmtCheck = $pdo->prepare($query);                     //Using Prepare
    $stmtCheck->execute([$email]);
    $result=$stmtCheck->fetch();                            //Fetching the query about the email

    //If the email entered by the user doesn't exist
    if(!$result){
        $errors['emailErr'] = true;                            //login error is true
    }
    if($result) {
        //Verifying the password (Result of Hash password stored in our database)
        if (password_verify($password, $result['password'])) {
            
            //Session elements
            session_start();                                //starting the session
            $_SESSION['email'] = $email;              //saving the email in the session
            $_SESSION['ActiveuserID'] = $result['user_code'];  //saving the userID in the database
            $activeuser = $_SESSION['ActiveuserID'];
           
            //To check wheather the receptionist is making or not. If receptionist try to login, it will redirect the user to the TimeSheet page.
            $GetRoleQuery = "SELECT * FROM user_name INNER JOIN role ON user_name.role_id=role.role_id WHERE user_code='$activeuser'"; 
            $RQuery = $pdo->prepare($GetRoleQuery);
            $RQuery->execute();
            $AnsRole=$RQuery->fetch();
            $role = $AnsRole['position_name'];

            if($role == "Receptionist"){
                header("Location: timesheet.php");
                exit();
            }
            //For all other users, they can access other pages, but not the timesheet
            else{
                //Redirecting to the ToolPage, which is the List Page
                header("Location: dashboard.php");
                exit(); //Exit
            }
        } 
        //if the verify password fails
        else {
            $errors['passErr'] = true;
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
        <title>Sign In | Story</title>
        <link rel="stylesheet" href="styles/main.css" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap"
            rel="stylesheet" />
        <script
            src="https://kit.fontawesome.com/170f096220.js"
            crossorigin="anonymous"></script>
        <!-- INCLUDE FILES FOR NAV AND FOOTER HERE -->
    </head>
    <!-- Body elements -->
    <body>
        <main>
            <div class="loginNav">
                <div>
                    <a href="login.php">
                        <i class="fa-solid fa-book-open-reader"></i>
                    </a>
                </div>
                <div>
                    <span class="underlineHoverEffect">
                        <a href="signup.php" class="myAccount"
                            >Create Account</a
                        >
                    </span>
                </div>
            </div>

            <!-- Including the Navigation php for the un-signed user here -->
            <div class="loginForm">
                <!-- Creating a Self-processing Form  -->
                <form id="loginPage" name="login" method="post">
                    <h1>Sign In to Story</h1>
                    <!-- Display the error here using php If the email or password detailsEnter by the user doesn't match  -->
                    <div class="formFill">
                        <div class="detailsEnter">
                            <!-- Taking Input of the email ID -->
                            <label for="email">EMAIL ADDRESS</label>
                            <input
                                type="text"
                                id="email"
                                name="email"
                                size="40"
                                value="<?php if(isset($_COOKIE["email"])) { echo $_COOKIE["email"]; } ?>"
                                placeholder="name@example.com"
                                required />
                            <!-- Taking Input of the Password -->
                            <label for="password">PASSWORD</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                size="40"
                                value="<?php if(isset($_COOKIE["password"])) { echo $_COOKIE["password"]; } ?>"
                                placeholder="Password"
                                required />
                            <!-- Display the error If the username or password enter by the user doesn't match  -->
                            <span class="error <?=!isset($errors['emailErr']) ? 'hidden' : "";?>">Error: Invalid Email</span>
                            <span class="error <?=!isset($errors['passErr']) ? 'hidden' : "";?>">Error: Invalid Password</span>
                            <!-- The form Login button -->
                            <div>
                                <button
                                    type="submit"
                                    name="submit"
                                    value="submit"
                                    class="submit">
                                    Sign In
                                </button>
                            </div>
                        </div>
                        <!-- Making space between social links and Login form with Line -->
                        <div class="makingSpace">
                            <div class="marginLine"></div>
                            <label class="marginOR">
                                <span>OR</span>
                            </label>
                            <div class="marginLine"></div>
                        </div>
                        <!-- Social media Login links -->
                        <div class="socialForm">
                            <div class="socialLogin">
                                <a href="#" class="Apple">
                                    <img
                                        src="images/appleicon.png"
                                        alt="Apple Icon" /><span
                                        >Continue with Apple</span
                                    >
                                </a>
                                <a href="#" class="LinkedIn">
                                    <img
                                        src="images/linkedinicon.png"
                                        alt="Linked In Icon" /><span
                                        >Continue with LinkedIn</span
                                    >
                                </a>
                                <a href="#" class="google">
                                    <img
                                        src="images/googleicon.png"
                                        alt="Google Icon" /><span
                                        >Continue with Google</span
                                    >
                                </a>
                            </div>
                        </div>
                    </div>
                    <!--Forgot password - if the user forgot about his pswd -->
                    <div class="loginProblem">
                        <span class="underlineHoverEffect"
                            ><a href="password.php">CAN'T LOG IN?</a>
                        </span>
                    </div>
                    <div class="accountPolicy">
                        <p>
                            By creating an account or continuing to use a Story
                            application, website, or software, you acknowledge
                            and agree that you have accepted the
                            <a href="https://policies.google.com/terms?hl=en">Terms of Service</a>
                            and have reviewed the
                            <a href="https://policies.google.com/privacy?hl=en-US"> Privacy Policy</a>.
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
