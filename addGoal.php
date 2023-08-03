<?php

session_start();  //Starting the Session 

//If the user session is not declared, it means the user is not logined, therefore, it will redirect him to the Login Page
if(empty($_SESSION['ActiveuserID'])){
    header("Location: login.php");
}

//array to store errors
$errors = array();

//Setting the variables 
//session of active userID in activer user varriable
$activeuser = $_SESSION['ActiveuserID'];
//first name of active user 
$first_name = $_POST['first_name'] ?? NULL;
$month_goal = filter_var($_POST['month_dropdown'] ?? idate('m'), FILTER_SANITIZE_STRING);
$year_goal = filter_var($_POST['year_dropdown'] ?? date('Y'), FILTER_SANITIZE_STRING);
$base_value = filter_var($_POST['base_value'] ?? null, FILTER_SANITIZE_STRING);
$hiddenValue = 'hidden';
$hiddenGoal = 'hidden';

include 'includes/library.php'; // Including the library
$pdo = connectDB();             //connnecting it to the database


//get all provider name and code
$pr_stmt=$pdo->query("SELECT first_name, user_code FROM user_name WHERE role_id=1 OR role_id=3");
$stmt=$pdo->query("SELECT first_name, user_code FROM user_name WHERE role_id=1 OR role_id=3");
//dealing with the possibilties of not getting any results
if(!$pr_stmt){
    die("Database pull did not return data");
}
if(!$stmt){
    die("Database pull did not return data");
}


//In order to display the Name of the active user : We are fetching the first name from the database for the activeuser 
$GetFirstNameQuery = "SELECT * FROM user_name WHERE user_code=?"; 
$FQuery = $pdo->prepare($GetFirstNameQuery);
$FQuery->execute([$activeuser]);
$AnsUser=$FQuery->fetch();
$user = $AnsUser['first_name'];   //Stores the value in variable username (It will store the Username)
$current_user_role = $AnsUser['role_id']; //stores the value of role id in the variable


//In order to display the position of the active user
$GetRoleQuery = "SELECT * FROM user_name INNER JOIN role ON user_name.role_id=role.role_id WHERE user_code='$activeuser'"; 
$RQuery = $pdo->prepare($GetRoleQuery);
$RQuery->execute();
$AnsRole=$RQuery->fetch();
$role = $AnsRole['position_name'];   



// when the add goal button is pressed then the query is executed to add the data to the databse table
if(isset($_POST['dbAddGoal'])){
    // Check if comma is present in the input, replace with empty string if true
    if(strpos($base_value, ',') !== false){
        $base_value = str_replace(',', '', $base_value);
    }
    // add errors for invalid input - 0 or less than 0
    if($base_value <= 0){
        $errors['negzval'] = true;
        $hiddenValue = "";
    }

    // query that selects all monthly goals from database table to ensure an entry does not already exist
    $monthlyGoalQuery = "SELECT * FROM month_goal WHERE month = ? AND year = ?";
    $monthlyGoal = $pdo->prepare($monthlyGoalQuery);
    $monthlyGoal->execute([$month_goal, $year_goal]);

    // check if any goal already exists for the selected month and year
    if($monthlyGoal->rowCount() > 0){
        $errors['goalExists'] = true;
        $hiddenGoal = "";
    }

    if(count($errors)===0){
        $UpdateGoalQuery = "INSERT INTO month_goal VALUES (DEFAULT,?,?,?)";
        $UpdateGoal = $pdo->prepare($UpdateGoalQuery);
        $UpdateGoal->execute([$month_goal, $year_goal, $base_value]); 

        header("Location: deleteGoal.php");
        exit;    
    }
}


?>
<!DOCTYPE html>
<html lang="en">
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

        

        <link
            href="http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css"
            rel="stylesheet" />
        <title>Add Goals | Story</title>
    </head>
    <body>
        
    <div class="dashSideBar" id="mySidebar">
            
            <ul class="sideNavList">
                
                <li class="navLink">
                    <a href="dashboard.php">
                    <i class="fa-solid fa-table-columns"></i>
                        <span class="linksName">Dashboard</span>
                    </a>
                </li>
                <li class="navLink">
                        <a href="dashboardNumbers.php">
                        <i class="fa-sharp fa-solid fa-hashtag"></i>
                            <span class="linksName">Numbers</span>
                        </a>
                </li>
                <li class="navLink">
                        <a href="analysis.php">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span class="linksName">Analytics</span>
                        </a>
                </li>
                <li  class="navLink"> 
                    <a href="deleteGoal.php"> 
                        <i class="fa-sharp fa-solid fa-bullseye" style="color:white;"></i>
                        <span class="linksName">Monthly Goals</span>
                    </a>
                </li>
                <li  class="navLink"> 
                    <a href="addGoal.php"> 
                        <i class="fa-solid fa-plus"></i>
                        <span class="linksName">Add Goals</span>
                    </a>
                </li>
                <li class="profile">
                    <div class="profileInfo">
                        <div class="name_position">
                            <div class="name"><?php echo $user; ?></div>
                            <div class="position">Position: <?php echo $role; ?></div>
                        </div>
                       
                    </div>
                </li>
            </ul>
        </div>
        <div class ="dashboardPage" id="main">
                <div id="navHead" class="navHead">
                    <nav class="accountDrop">
                        <button
                            class="openBtn"
                            onclick="closeNav()"
                            id="openCloseButton">
                            <i class="fa-solid fa-bars"></i>
                        </button>
                        <span>
                            <i class="fa-solid fa-book-open-reader"></i>
                        </span>
                        
                    </nav>
                    <!-- User Profile Dropdown -->
                    <div id="userProfileDropdown" class="userProfileDropdown">
                       <?php include "includes/navAccount.php"?>
                    </div>
                </div>
            <!-- Main Page Contents -->
            <section  id="mainContent" class="homeContent">     
                <form id="buttons" action = "<?=htmlentities($_SERVER['PHP_SELF']); ?>" method="post">       
                <div class="dataActions">
                    <div class="action-box">
                        <input type="submit" id="dbimport" name="dbAddGoal" value="Add New Goal"/>     
                    </div>
                </div>
                <div class="formGoal">
                    <!-- dropdown to allow user to select a month -->
                    <select id="month_dropdown" name="month_dropdown" required>
                        <option value="" selected disabled hidden>Select Month..</option>
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    <!-- dropdown to allow user to select a year -->
                    <select id="year_dropdown" class="year_dropdown" name="year_dropdown" required>
                        <option value="" selected disabled hidden>Select Year..</option>
                        <?php  
                            $year_start = 2021;
                            $year_end = date('Y'); //current Year
                            for ($i_year = $year_start; $i_year <= $year_end; $i_year++) {
                                $selected = $year_end == $i_year ? ' selected' : '';
                                echo '<option value="'.$i_year.'"'.$selected.'>'.$i_year.'</option>'."\n";
                            }
                        ?>
                    </select>
                    <!-- enter the amount that will be set as the monthly goal -->
                    <label for="base_value">Goal Value</label>
                            <input
                                type="text"
                                id="base_value"
                                name="base_value"
                                size="40"   
                            required />
                            <span class="error" <?php echo $hiddenValue; ?>>Please enter a value greater than 0<br /></span>
                            <span class="error" <?php echo $hiddenGoal; ?>>Monthly Goal already exists for this Month. Please delete existing goal if you wish to re-enter new value.<br /></span>
                </div>
                </form>
            </section>
        <script defer src="script/main.js" type="text/javascript"></script>
    </body>
</html>