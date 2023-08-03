<?php

session_start();  //Starting the Session 

//If the user session is not declared, it means the user is not logined, therefore, it will redirect him to the Login Page
if(empty($_SESSION['ActiveuserID'])){
    header("Location: login.php");
}

//Setting the variables 
//session of active userID in activer user varriable
$activeuser = $_SESSION['ActiveuserID'];
//first name of active user 
$first_name = trim(filter_var($_POST['first_name']??NULL, FILTER_SANITIZE_STRING));


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


// query that selects all monthly goals from database table
$monthlyGoalQuery = "SELECT * FROM month_goal ORDER BY year DESC, month DESC";
$monthlyGoal = $pdo->prepare($monthlyGoalQuery);
$monthlyGoal->execute();


// if delete button is pressed then the selected rows are deleted from the database
if(isset($_POST['dbDeleteGoal'])){
    foreach ($_POST['checked'] as $id){
        $UpdateCheckedQuery = "DELETE FROM month_goal WHERE goal_id = ? ";
        $UpdateChecked = $pdo->prepare($UpdateCheckedQuery);
        $UpdateChecked->execute([$id]); 
    }
    header("Location: deleteGoal.php");
    exit;    
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
        <title>Goals | Story</title>
    </head>
    <body>
        
    <div class="dashSideBar" id="mySidebar">
            <!-- Navigation Side Bar -->
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
                        <input type="submit" id="dbimport" name="dbDeleteGoal" value="Delete Selected Goals"/>     
                    </div>
                </div>
                <div class="numbersTable">
                     <table class="dataSheet">
                        <!-- each column - checkbox, month, year, goal -->
                        <tr>
                            <th>
                                <input type="checkbox" onclick="checkAllRows(this)" id="selectAll"  />
                            </th>
                            <th>MONTH</th>
                            <th>YEAR</th>
                            <th>GOAL</th>
                        </tr>
                        <!-- php code to fetch data from rows of database -->
                        <?php  
                        
                        while($rows=$monthlyGoal->fetch()){
                           $goal_id = $rows['goal_id'];
                        ?>
                        <tr class="Row">
                            <?php if (!$rows){ ?>
                                <td colspan="7">No data available for this user</td>
                            <?php } else { ?>
                            <!-- fetching data from each row of every column -->
                            <td><input type="checkbox" name="checked[]" class="rowCheckbox" value="<?php echo $rows["goal_id"]; ?>"  /></td>
                            <td><?php echo date("F",mktime(0,0,0,$rows['month']+1,0)) ?></td> 
                            <td><?php echo $rows['year']; ?></td> 
                            <td><?php echo $rows['base_value']; ?></td>
                            <?php } ?>
                        </tr>
                        <?php
                            } //close while loop 
                        ?>
    
                    </table>
                   
                </div>
                </form>
            </section>
        <script defer src="script/main.js" type="text/javascript"></script>
    </body>
</html>