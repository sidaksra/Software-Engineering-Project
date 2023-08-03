<?php

//Declaring Arrays fro error and print
$errors = array();
$print = array();

//Intilizing the variables for the things which are in the database
$firstName = trim(filter_var($_POST['firstName']??NULL, FILTER_SANITIZE_STRING));
$lastName =trim(filter_var($_POST['lastName'] ?? null,FILTER_SANITIZE_STRING));
$role_id = trim(filter_var($_POST['position_name'] ?? null,FILTER_SANITIZE_STRING));
$email= trim(filter_var($_POST['email'] ?? null,FILTER_SANITIZE_STRING));
$password = trim(filter_var($_POST['password'] ?? null,FILTER_SANITIZE_STRING));
$confirmpass = $_POST['confirmpass'] ?? null;
$user_code = trim(filter_var($_POST['user_code'] ?? null,FILTER_SANITIZE_STRING));

 // Include library, make database connection
 include 'includes/library.php';
 $pdo = connectDB();

$stmt=$pdo->query("SELECT * FROM role");
//dealing with the possibilties of not getting any results
if(!$stmt){
    die("Database pull did not return data");
}

if (isset($_POST['submit'])){ //only do this code if the form has been submitted


    //Checking wheather the email already registered onto the database or not!
    $queryCheckEmail = "SELECT * FROM user_name WHERE email=?";
    $stmtCheck = $pdo->prepare($queryCheckEmail);
    $stmtCheck->execute([$email]);
    $rows=$stmtCheck->fetchColumn();

    //Checking wheather the User Code already registered onto the database or not!
    $queryCheck_UCode = "SELECT * FROM user_name WHERE user_code=?";
    $stmtCheck_UCode = $pdo->prepare($queryCheck_UCode);
    $stmtCheck_UCode->execute([$user_code]);
    $UCodeExist=$stmtCheck_UCode->fetchColumn();

    //if User Code already registered
    if($UCodeExist)
    {
        $errors['user_code_exist'] = true; 
    }

        
    //if email already registered
    if($rows)
    {
        $errors['emailexist'] = true; 
    }

    //For Strong Password
    if(!empty($password)){
        if (strlen($_POST["password"]) <= '8') {
            $errors['passErrLen'] = true; 
        }
        elseif(!preg_match("#[0-9]+#",$password)) {
            $errors['passErrNum'] = true; 
        }
        elseif(!preg_match("#[A-Z]+#",$password)) {
            $errors['passErrLet'] = true; 
        }
        elseif(!preg_match("#[\W]+#",$password)) {
            $errors['passErrChar'] = true; 
        }
    }
    //If the Password entered by the user, doesn't match with the re-entered password
    if ($password != $confirmpass) {
        //Error password does not match
        $errors['passwordmatch'] = true;
        
    }
    // Checking if first name and last name contain only alphabetic characters
    if (!ctype_alpha($firstName)) {
        $errors['firstname'] = true;
    }
    if (!ctype_alpha($lastName)) {
        $errors['lastname'] = true;
    }


    //If there are no erros
    if(count($errors)===0){

        //password hashed
        $hashPassword = password_hash($password, PASSWORD_DEFAULT);

        //Declaring verifcation as zero
        $verificationcode=0000;
        

        /********************************************
         * Inserting user credentials to the database
         ********************************************/
        
        //Making query statement to insert users data into user_name table
        $queryInsert = "INSERT INTO user_name VALUES (?,?,?,?,?,?,?, NOW())";
        $stmtInsert = $pdo->prepare($queryInsert);
        $result = $stmtInsert->execute([$user_code, $email, $hashPassword,$firstName,$lastName,$verificationcode,$role_id]);


        //if Account is created succesfully, insertion is done!
        if ($result) {
            //Moving To the Login Page!
            header("refresh:0.5; url=login.php");
            //It will show an alert box to user. Just to make them sure that their account is created!
            echo '<script type="text/javascript">
            alert("Your Account has been sucessfully created! Please Login into your account.");
            </script>';
            exit();
        } 

        //if insert of user credentials in the database is unsuccessful!
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
        <title>Sign Up | Story</title>
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
                        <a href="login.php" class="myAccount"
                            >LOGIN</a
                        >
                    </span>
                </div> 
            </div>

            <!-- Including the Navigation php for the un-signed user here -->
            <div class="loginForm">
                <!-- Creating a Self-processing Form  -->
                <form id="loginPage" name="login" method="post">
                    <h1>Sign Up to Story</h1>
                    <!-- Error for email -->
                    <span class="error <?=!isset($errors['email']) ? 'hidden' : "";?>">Please enter a correct email<br /></span>
                    <span class="error <?=!isset($errors['firstname']) ? 'hidden' : "";?>">Error: No Numbers. Only Words are accepted in First Name Column. Please Try Again<br /></span>
                    <span class="error <?=!isset($errors['lastname']) ? 'hidden' : "";?>">Error: No Numbers. Only Words are accepted in Last Name Column. Please Try Again<br /></span>
                    <span class="error <?=!isset($errors['emailexist']) ? 'hidden' : "";?>">The email address is already registered!<br /></span>
                    <span class="error <?=!isset($errors['user_code_exist']) ? 'hidden' : "";?>">The User Code is already registered!. Please Try Again<br /></span>
                    <span class="printout <?=!isset($print['unsuccess']) ? 'hidden' : "";?>"><?php echo "Your Signup was unsuccessful!" ?></span>
                    <!-- Error for the Password -->
                    <span class="error <?=!isset($errors['passErrLen']) ? 'hidden' : "";?>">Password Error: Use at least 8 or more characters. Please Try Again<br /></span>
                    <span class="error <?=!isset($errors['passErrLet']) ? 'hidden' : "";?>">Password Error: Use Capital Letters, at least a mix of letters. Please Try Again<br /></span>
                    <span class="error <?=!isset($errors['passErrNum']) ? 'hidden' : "";?>">Password Error: Use at least 1 number. Please Try Again<br /></span>
                    <span class="error <?=!isset($errors['passErrChar']) ? 'hidden' : "";?>">Password Error: Use at least 1 special character. Please Try Again<br /></span>
                    <span class="error <?=!isset($errors['passwordmatch']) ? 'hidden' : "";?>">Your Password Doesn't Match!<br /></span>
                    <!-- Display the error here using php If the email or password detailsEnter by the user doesn't match  -->
                    <div class="formFill">
                        <div class="detailsEnter">
                            <!--Enter the First Name    -->
                            <label for="firstName">FIRST NAME</label>
                            <input
                                type="text"
                                id="firstName"
                                name="firstName"
                                size="40"
                               
                               
                                required />
                            <!--  Enter the Last Name Field --> 
                            <label for="lastName">LAST NAME</label>
                            <input
                                type="text"
                                id="lastName"
                                name="lastName"
                                size="40"
                                
                              
                                required />
                        
                            <!-- Taking Input of the email ID -->
                            <label for="email">EMAIL ADDRESS</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                size="40"
                                
                                placeholder="name@example.com"
                                required />
                           
                            <!-- Taking Input of the Password -->
                            <label for="password">PASSWORD</label>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                size="40"
            
                                
                                required />
                                <p>For Password: Use at least 8 or more characters with a mix of letters, numbers & symbols</p>
                            <!-- Taking Input of the Password -->
                            <label for="password">CONFIRM PASSWORD</label>
                            <input type="password" class="confirmpass" name="confirmpass"  size="40" required />
                            <label for="user_code">User Code</label>
                            <input
                                type="text"
                                id="user_code"
                                name="user_code"
                                size="5"
                                autocomplete="off"
                                required />
                            <!-- Taking input of the position of the user (role in the company)  -->
                            <label for="roleID">POSITION: </label>
                            <select id="position_name" name="position_name">
                                <?php foreach ($stmt as $row): ?>
                                   <option value="<?php echo $row['role_id'];?>"><?php echo $row['position_name']?></option>
                                <?php endforeach; ?>
                            </select><br /><br />
                            
                            <!-- The form Login button -->
                            <div>
                                <button
                                    type="submit"
                                    name="submit"
                                    value="submit"
                                    class="submit">
                                    Sign Up
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
                            ><a href="login.php">Already have an Account?</a>
                        </span>
                    </div>
                    <!-- Policy  -->
                    <div class="accountPolicy">
                        <p>
                            By creating an account to use a Story
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