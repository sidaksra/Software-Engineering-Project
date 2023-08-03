<?php
session_start();  //Starting the Session 

//If the user session is not declared, it means the user is not logined, therefore, it will redirect him to the Login Page
if(empty($_SESSION['ActiveuserID'])){
    header("Location: login.php");
}

//Setting the variable for the session of active userID in activer user varriable
$activeuser = $_SESSION['ActiveuserID'];
$first_name = trim(filter_var($_POST['first_name'] ?? NULL,FILTER_SANITIZE_STRING));

include 'includes/library.php'; // Including the library
$pdo = connectDB();             //connnecting it to the database


//In order to display the Name of the active user : We are fetching the first name from the database for the activeuser 
$GetFirstNameQuery = "SELECT * FROM user_name WHERE user_code='$activeuser'"; 
$FQuery = $pdo->prepare($GetFirstNameQuery);
$FQuery->execute();
$AnsUser=$FQuery->fetch();
$Fuser = $AnsUser['first_name'];   //Stores the value in variable username (It will store the Username)
$Luser = $AnsUser['last_name']; 
$emailUser = $AnsUser['email']; 
$userCode = $AnsUser['user_code']; 

//In order to display the position of the active user
$GetRoleQuery = "SELECT * FROM user_name INNER JOIN role ON user_name.role_id=role.role_id WHERE user_code='$activeuser'"; 
$RQuery = $pdo->prepare($GetRoleQuery);
$RQuery->execute();
$AnsRole=$RQuery->fetch();
$role = $AnsRole['position_name'];  
?>
<!DOCTYPE html>
<html>
<head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta
            name="description"
            content="Main Landing Page of the Dashboard of Longworth Dental Boutique" />
        <link rel="stylesheet" href="styles/main.css" />
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap"
            rel="stylesheet" />
        <script
            src="https://kit.fontawesome.com/170f096220.js"
            crossorigin="anonymous"></script>
        <script defer src="script/main.js" type="text/javascript"></script>
        <link
            href="http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css"
            rel="stylesheet" />
        <title>Account | Story</title>
    </head>
    <body>
        <header>
            <!-- Top Bar Navigation  -->
            <div class="dashboardPage" id="main">
                <div id="navHead" class="navHead">
                    <nav class="logoBook">
                        <a href="dashboard.php">
                            <i class="fa-solid fa-book-open-reader"></i>
                        </a>
                    </nav>
                    <!-- User Profile Dropdown -->
                    <div id="userProfileDropdown" class="userProfileDropdown">
                        <?php include "includes/navAccount.php"?>
                    </div>
                </div>
            </div>
        </header>
        <main class="accountInfo">
            <div class="Acc-container">
                <h1>ACCOUNT DETAILS</h1>
                <!-- Start of the Form  -->
                <form>
                    <!-- First Name is displayed  -->
                    <div class="form-group">
                        <label for="Fname">First Name: <?php echo $Fuser?></label>
                      
                    </div>
                    <!-- Last name is displayed  -->
                    <div class="form-group">
                        <label for="Lname">Last Name: <?php echo $Luser?></label>
         
                    </div>
                    <!-- Email is displayed  -->
                    <div class="form-group">
                        <label for="email">Email: <?php echo $emailUser?></label>
                       
                    </div>
                    <!-- Potition of the logged in user is displayed  -->
                    <div class="form-group">
                        <label for="position-name">Position: <?php echo $role?></label>
                       
                    </div>
                    <!-- User Code is displayed   -->
                    <div class="form-group">
                        <label for="position-name">User Code: <?php echo $userCode?></label>
                       
                    </div>
                    <!-- To change the password, a link for it will be displayed that will take the user to the change password page -->
                    <div class="loginProblem">
                        <span class="underlineHoverEffect"
                            ><a href="password.php">Change Password?</a>
                        </span>
                    </div>
                </form>
            </div>
        </main>
    </body>
</html>
