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
//array of all memo values that were updated
$memo_values = $_POST['memo'] ?? [];
//schedule id array
$schedule_id_values = $_POST['s_id'] ?? [];
//array of list ids for the manager only
$listID = trim(filter_var($_GET['listID'] ?? NULL,FILTER_SANITIZE_STRING));


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


//SQL Query to select schedule data from database table - provider_schedule
$fetchedScheduleData = "SELECT schedule_id, user_code, schedule_date, start_time, end_time, Memo FROM provider_schedule ORDER BY schedule_date DESC";
$scheduleColumns = $pdo->prepare($fetchedScheduleData);
$scheduleColumns->execute();

//if MEMO input is clicked on to be edited and the current user is manager or general manager, then the memo field is editable, otherwise only readonly
//only the logged in user's data is displayed
if($current_user_role != 2 && $current_user_role != 5){ 
    $readonly = "readonly";
    $fetchedScheduleData = "SELECT schedule_id, user_code, schedule_date, start_time, end_time, Memo FROM provider_schedule WHERE user_code = ? ORDER BY schedule_date DESC";
    $scheduleColumns = $pdo->prepare($fetchedScheduleData);
    $scheduleColumns->execute([$activeuser]);
}
else{
    $readonly = "";
}


//loop through each provider name
foreach ($pr_stmt as $provider):
    if($provider['user_code'] == $listID){
        $fetchedScheduleData = "SELECT schedule_id, user_code, schedule_date, start_time, end_time, Memo FROM provider_schedule WHERE user_code = ? ORDER BY schedule_date DESC";
        $scheduleColumns = $pdo->prepare($fetchedScheduleData);
        $scheduleColumns->execute([$listID]);
    }
endforeach;

//if the import button is pressed then refresh the page
if(isset($_POST['file-import'])){
    header("Location: dashboardNumbers.php");
    exit;
}

//if database import button is pressed then push the MEMO column value to database and delete checked rows
if(isset($_POST['dbimport'])){
    for($i = 0; $i< count($memo_values); $i++){
        $UpdateMemoQuery = "UPDATE provider_schedule SET Memo = ? WHERE schedule_id = ? ";
        $UpdateMemo = $pdo->prepare($UpdateMemoQuery);
        $UpdateMemo->execute([$memo_values[$i], $schedule_id_values[$i]]); 
    }
    foreach ($_POST['checked'] as $id){
        $UpdateCheckedQuery = "DELETE FROM provider_schedule WHERE schedule_id = ? ";
        $UpdateChecked = $pdo->prepare($UpdateCheckedQuery);
        $UpdateChecked->execute([$id]); 
    }
    header("Location: dashboardNumbers.php");
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
        <title>Numbers | Story</title>
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
                    <a href="analysis.php">
                        <i class="fa-solid fa-chart-pie"></i>
                        <span class="linksName">Analytics</span>
                    </a>
                </li>
                <!-- If user is not a manager, then the list of provider names will nto be visible -->
                <li 
                    <?php if($current_user_role != 2 && $current_user_role != 5){ ?> 
                        style="display:none;"
                    <?php } ?>
                > 
                    <a
                        href="dashboardNumbers.php?listID=''"
                        class="userNamesDrop">
                        <i class="fa fa-caret-down"></i
                        ><span class="linksName">Users Report</span>
                    </a>
                    <div class="dropNumbers" id="dropNumbers">
                    <!-- change this for a particular user table when clicked -->
                        <?php foreach ($stmt as $row): ?>
                            <a id="provider_name_report" href="dashboardNumbers.php?listID=<?= $row['user_code']; ?>" ><i class="fa-solid fa-circle-small"></i><span class="linksName"><?= $row['first_name']  ?></span></a>
                        <?php endforeach; ?>
                    </div>  
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
                
            <!-- Main Page Contents -->
            <!-- <input type="submit" name = "add-row" class="button" value = "add-row"/> -->
            <section  id="mainContent" class="homeContent">     
                <form id="buttons" action = "<?=htmlentities($_SERVER['PHP_SELF']); ?>" method="post">       
                <div class="dataActions">
                    <div class="action-box">
                        <input type="submit" id="dbimport" name="dbimport" value="Submit Changes"/>     
                    </div>
                    <div class="action-box">
                        <input type="submit" id="file-import" name="file-import" value="Refresh Page Imports"/>
                    </div>
                </div>

                <div class="numbersTable">
              <!--  <form action="" method="post">
                      Table to display the values -->
                     <table class="dataSheet">
                        <tr>
                            <th>
                                <input type="checkbox" onclick="checkAllRows(this)" id="selectAll"  />
                            </th>
                            <th>DR</th>
                            <th>DATE<i class="fa fa-caret-down"></i></th>
                            <th>AVAILABLE UNITS</th>
                            <th>DOWNTIME UNITS</th>
                            <th>GROSS PRODUCTION</th>
                            <th>MEMO</th>
                        </tr>
                        <!-- php code to fetch data from rows of database -->
                        <?php  
                        
                        while($rows=$scheduleColumns->fetch()){
                           $schedule_id = $rows['schedule_id'];
                        ?>
                        <tr class="Row">
                            <?php if (!$rows){ ?>
                                <td colspan="7">No data available for this user</td>
                            <?php } else { ?>
                            <!-- fetching data from each row of every column -->
                            <td><input type="checkbox" name="checked[]" class="rowCheckbox" value="<?php echo $rows["schedule_id"]; ?>"  /></td>
                            <td><?php echo $rows["user_code"]; ?></td> <!-- provider -->
                            <td><?php echo $rows["schedule_date"]; ?></td> <!-- date of schedule -->
                            <?php 
                                //calculate available units 
                                //available units = (end time - start time) * 6
                                
                                //convert end time into double
                                $explodeEndTime = explode(":", $rows["end_time"]);
                                $scheduleEndTime = $explodeEndTime[0] + ($explodeEndTime[1]/60) + ($explodeEndTime[2]/3600);
                                
                                //convert start time into double
                                $explodeStartTime = explode(":", $rows["start_time"]);
                                $scheduleStartTime = $explodeStartTime[0] + ($explodeStartTime[1]/60) + ($explodeStartTime[2]/3600);
                                
                                $availableHours = $scheduleEndTime - $scheduleStartTime;
                                $availableUnits =  $availableHours * 6;
                            ?>
                            <td><?php echo $availableUnits; ?></td> <!-- available units -->
                            <?php
                                //calculate donwtime units
                                //donwtime units = available units - productive units
                                //productive units = sum of (end time - start time) of appointments for that day
                                //fetching appointment times
                                $fetchedAppointmentData = "SELECT start_time, end_time FROM appointment 
                                WHERE user_code = ? AND start_time >= ?
                                AND end_time <= ?";
                                $apptColumns = $pdo->prepare($fetchedAppointmentData);
                                $apptColumns->execute([$rows['user_code'], "$rows[schedule_date] 00:00:00", "$rows[schedule_date] 23:59:59"]);
                                
                                //initialize productive hours variable as 0
                                $productiveHours = 0.00;
                                $diffTime = 0.00;

                                //loop through all fetched rows and sum the difference of all start time and end time
                                while($apptRows=$apptColumns->fetch()){
                                    
                                    //explode time to convert end time to double
                                    $explodedEndTime2 = date("h:i:s", strtotime($apptRows["end_time"]));
                                    $explodedEndTime2 = explode(":", $explodedEndTime2); 
                                    $apptEndTime = $explodedEndTime2[0] + ($explodedEndTime2[1]/60) + ($explodedEndTime2[2]/3600);

                                    //explode time to convert end time to double
                                    $explodedStartTime2 = date("h:i:s", strtotime($apptRows["start_time"]));
                                    $explodedStartTime2 = explode(":", $explodedStartTime2);
                                    $apptStartTime = $explodedStartTime2[0] + ($explodedStartTime2[1]/60) + ($explodedStartTime2[2]/3600);
                                    
                                    //calculate productive hours - productive hours are the numbers of hours of appointments
                                    $diffTime = $apptEndTime - $apptStartTime;
                                    //add up all productive hours for THAT DAY AND PROVIDER
                                    $productiveHours = $productiveHours + $diffTime;
                                }
                                //donwtime units
                                $productiveUnits = $productiveHours * 6;
                                $downtimeUnits =  $availableUnits - $productiveUnits;
                            ?>
                            <td><?php echo $downtimeUnits; ?></td> <!-- downtime units -->
                            <?php
                                //get gross production
                                //SQL Query to select schedule data from database table - provider_schedule
                                $fetchedLedgerData = "SELECT SUM(total) AS TOTAL FROM ledger 
                                WHERE user_code = ? AND transaction_date = ? ";
                                $ledgerColumns = $pdo->prepare($fetchedLedgerData);
                                $ledgerColumns->execute([$rows['user_code'], $rows['schedule_date']]);
                                $ledgerData = $ledgerColumns->fetch();
                                $ledgerData = $ledgerData['TOTAL'];
                            ?>
                            <td><?php echo $ledgerData; ?></td> <!-- gross production -->
                            <td style="display:none;"><input
                                    type="hidden"
                                    id="s_id"
                                    class="s_id"
                                    name="s_id[]"
                                    size="5"
                                    value= <?php echo $rows["schedule_id"]?>
                                /></td>
                            <td>
                                <input
                                    type="text"
                                    id="memo"
                                    class="memo"
                                    name="memo[]"
                                    size="50"
                                    value="<?php echo $rows["Memo"] ?>"
                                    <?php echo $readonly ?>
                                />
                            </td>
                            <?php }  ?>
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