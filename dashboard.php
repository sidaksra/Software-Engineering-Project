<?php

session_start();  //Starting the Session 

//If the user session is not declared, it means the user is not logined, therefore, it will redirect him to the Login Page
if(empty($_SESSION['ActiveuserID'])){
    header("Location: login.php");
}

//Setting the variable for the session of active userID in activer user varriable
$activeuser = $_SESSION['ActiveuserID'];
$first_name = trim(filter_var($_POST['first_name']??NULL, FILTER_SANITIZE_STRING));

include 'includes/library.php'; // Including the library
$pdo = connectDB();             //connnecting it to the database


//In order to display the Name of the active user : We are fetching the first name from the database for the activeuser 
//$GetFirstNameQuery = "SELECT * FROM user_name WHERE user_code='$activeuser'"; 
$GetFirstNameQuery = "SELECT * FROM user_name WHERE user_code=?"; 

$FQuery = $pdo->prepare($GetFirstNameQuery);
$FQuery->execute([$activeuser]);
$AnsUser=$FQuery->fetch();
$user = $AnsUser['first_name'];   //Stores the value in variable username (It will store the Username)
$current_user_role = $AnsUser['role_id']; //stores the value of role id in the variable

//In order to display the position of the active user
$GetRoleQuery = "SELECT * FROM user_name INNER JOIN role ON user_name.role_id=role.role_id WHERE user_code=?"; 
$RQuery = $pdo->prepare($GetRoleQuery);
$RQuery->execute([$activeuser]);
$AnsRole=$RQuery->fetch();
$role = $AnsRole['position_name'];  



//Code to print the current month revenue, last month revenue and last month of last year revenue

//This Month Rev
$thisMonthRev = "SELECT SUM(total) AS this_revenue " . ($role == 'Manager' || 'General Manager (HR)' ? "" : ", SUM(CASE WHEN user_code = '$activeuser' THEN total ELSE 0 END) AS this_user_revenue") . " FROM ledger WHERE MONTH(transaction_date) = MONTH(CURRENT_DATE()) AND YEAR(transaction_date) = YEAR(CURRENT_DATE())" . ($role == 'Manager' || 'General Manager (HR)' ? "" : " AND user_code = '$activeuser'");

//Previous Month revenue
$prevMonthRev = "SELECT SUM(total) AS prev_revenue " . ($role == 'Manager' || 'General Manager (HR)' ? "" : ", SUM(CASE WHEN user_code = '$activeuser' THEN total ELSE 0 END) AS prev_user_revenue") . " FROM ledger WHERE MONTH(transaction_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(transaction_date) = YEAR(CURRENT_DATE())" . ($role == 'Manager' || 'General Manager (HR)' ? "" : " AND user_code = '$activeuser'");

//Last year revenue of this month 
$lastYearRev = "SELECT SUM(total) AS last_revenue " . ($role == 'Manager' || 'General Manager (HR)' ? "" : ", SUM(CASE WHEN user_code = '$activeuser' THEN total ELSE 0 END) AS last_user_revenue") . " FROM ledger WHERE MONTH(transaction_date) = MONTH(CURRENT_DATE()) AND YEAR(transaction_date) = YEAR(CURRENT_DATE() - INTERVAL 1 YEAR)" . ($role == 'Manager' || 'General Manager (HR)' ? "" : " AND user_code = '$activeuser'");

//Query for each revenue results

$thisMonthQuery = $pdo->prepare($thisMonthRev);
$thisMonthQuery->execute();
$thisMonthResult=$thisMonthQuery->fetch();
$rev_this = $thisMonthResult['this_revenue'] ?? 'NONE';

$prevMonthQuery = $pdo->prepare($prevMonthRev);
$prevMonthQuery->execute();
$prevMonthResult=$prevMonthQuery->fetch();
$rev_prev = $prevMonthResult['prev_revenue'] ?? 'NONE';

$lastYearQuery = $pdo->prepare($lastYearRev);
$lastYearQuery->execute();
$lastYearResult=$lastYearQuery->fetch();
$rev_lastYear = $lastYearResult['last_revenue'] ?? 'NONE';

//Base Revenue - Min. Goal to compatre the data of doctors with this and print the accurate statements on the dashboard 
$base_revenue = 20000;

$MessageString = '';

if ($rev_this != 'NONE') {
    if ($rev_this >= $base_revenue) {
        $MessageString = "Looks like you are having a good month!";
    } elseif (date('j') <= ceil(date('t')/2)){
        $MessageString = "The month has just started, let's keep going!";
    }
    elseif ($rev_this < $base_revenue){
        $MessageString = "Looks like there is some room for improvement! Keep working hard!";
    }
} else {
    $MessageString = "No revenue data available for this month";
}

//End of calculating revenue

/* Read data from csv files and import data into database */
if(isset($_POST['submit'])){
    // Allowed mime types
    $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 
    'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 
    'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 
    'application/vnd.msexcel', 'text/plain');
    
    //LEDGER CSV FILE
    //Validate whether the selected file is a CSV file
    if(!empty($_FILES['csv-file1']['name']) && in_array($_FILES['csv-file1']['type'], $csvMimes)){
        //if file is uploaded
        if(is_uploaded_file($_FILES['csv-file1']['tmp_name'])){
           
            // Open uploaded CSV file with read-only mode
            $csvFile1 = fopen($_FILES['csv-file1']['tmp_name'], 'r');

            fgetcsv($csvFile1, 1000, ",");
            while(($line1 = fgetcsv($csvFile1, 1000, ",")) !== FALSE) {
                
                if (is_array($line1)) {
                  $ledgerDate = date("Y-m-d H:i:s",strtotime($line1[0]));
                  //$ledgerDate = $line1[0];
                  $ledgerPrCode = $line1[1];
                  $ledgerTotal = $line1[2];
                  $queryInsert = "INSERT INTO ledger VALUES(NULL,?,?,?)";
                  $stmtInsert = $pdo->prepare($queryInsert);
                  $result = $stmtInsert->execute([$ledgerDate, $ledgerTotal, $ledgerPrCode]);
                }

              }
              
        //close opened csv file
        fclose($csvFile1);
        }
    }
    //Appointment CSV FILE
    //Validate whether the selected file is a CSV file
    if(!empty($_FILES['csv-file2']['name']) && in_array($_FILES['csv-file2']['type'], $csvMimes)){
        //if file is uploaded
        if(is_uploaded_file($_FILES['csv-file2']['tmp_name'])){
            
            // Open uploaded CSV file with read-only mode
            $csvFile2 = fopen($_FILES['csv-file2']['tmp_name'], 'r');

            //get row data
            fgetcsv($csvFile2, 1000, ",");
            while(($line2=fgetcsv($csvFile2, 1000, ","))!==FALSE){
                
                if (is_array($line2)) {

                    //appt type id
                    $appTypeId = $line2[0];
                    //start time
                    $appStart = $line2[1];
                    //end time
                    $appEnd = $line2[2];
                    //ops id
                    $opsId = $line2[3];
                    //patient last name
                    $patLName = $line2[4];
                    //patient first name
                    $patFName = $line2[5];
                    //pr code
                    $prCode = $line2[6];
                    
                    //update patient data into database
                    $queryPatient = "INSERT INTO patient VALUES(NULL,?,?)";
                    $PatientInsert = $pdo->prepare($queryPatient);
                    $result2 = $PatientInsert->execute([$patFName, $patLName]);

                    //We have used $escaped_name because there was a patient in the database that her last name is "O'handley"
                    //So PHP misused single quote (') character, which is causing the string to end and making rest of the query syntax.
                    //In order to resolve this issue, we used addslashes function
                    $escaped_Lname = addslashes($patLName);
                    $GetpatientId = "SELECT patient_id FROM patient WHERE first_name = '$patFName' AND last_name ='$escaped_Lname'"; 
                    $PQuery = $pdo->prepare($GetpatientId);
                    $PQuery->execute();
                    $patientId=$PQuery->fetch();
                    
                    //update appointment data in the database
                    $queryappointment = "INSERT INTO appointment VALUES(NULL, ?,?,?,?,?,?)";
                    $AppoInsert = $pdo->prepare($queryappointment);

                    //If the variable is not an array, then it will be converted to an array, or it will be assigned to a single element array.
                    $appTypeIdArray = is_array($appTypeId) ? $appTypeId : [$appTypeId];
                    $patientIdArray = is_array($patientId) ? $patientId : [$patientId];
                    $prCodeArray = is_array($prCode) ? $prCode : [$prCode];
                    $opsIdArray = is_array($opsId) ? $opsId : [$opsId];
                    $appStart = str_replace('/', '-', $appStart);
                    $appStart = date('Y-m-d H:i:s', strtotime($appStart));  
                    $appEnd = str_replace('/', '-', $appEnd);
                    $appEnd = date('Y-m-d H:i:s', strtotime($appEnd));  
                    $appStartArray = is_array($appStart) ? $appStart : [$appStart];
                    $appEndArray = is_array($appEnd) ? $appEnd : [$appEnd];

                    //converting the array into a string format before inserting it using implode
                    $result3 = $AppoInsert->execute([implode(",", $appTypeIdArray), implode(",", $patientIdArray), implode(",", $prCodeArray), implode(",", $opsIdArray), implode(",", $appStartArray), implode(",", $appEndArray) ]);

                    
                }
            }
        //close opened csv file
        fclose($csvFile2);
        header("Location: dashboard.php");
        }
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
        <script defer src="script/main.js" type="text/javascript"></script>
        <link
            href="http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css"
            rel="stylesheet" />
        <title>Dashboard | Story</title>
    </head>
    <body>
        <header>
        <!-- Side Bar Navigation Links -->
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
                <li  class="navLink"
                    <?php if($current_user_role != 2 && $current_user_role != 5){ ?> 
                        style="display:none;"
                    <?php } ?>
                > 
                    <a href="deleteGoal.php"> 
                        <i class="fa-sharp fa-solid fa-bullseye" style="color:white;"></i>
                        <span class="linksName">Monthly Goals</span>
                    </a>
                </li>
                <li  class="navLink"
                    <?php if($current_user_role != 2 && $current_user_role != 5){ ?> 
                        style="display:none;"
                    <?php } ?>
                > 
                    <a href="addGoal.php"> 
                        <i class="fa-solid fa-plus"></i>
                        <span class="linksName">Add Goals</span>
                    </a>
                </li>
                <!-- Upload CSV link is only displayed to the Manager  -->
                <li class="navLink"
                <?php if($current_user_role != 2 && $current_user_role != 5){ ?> 
                        style="display:none;"
                    <?php } ?>
                >
                    <a href="#" id="upload-csv-btn" onclick="openPopup()">
                    <i class="fa-solid fa-upload"></i>
                        <span class="linksName">Upload CSV</span>
                    </a>
                    
                    <div id="popup-container" style="display: none;">
                        <div id="popup-content">
                            <span id="close-popup" onclick="closePopup()">&times;</span>
                            <form id="CSVForm" action="#" method="post" enctype="multipart/form-data">
                                <div>
                                    <!-- Upload the Ledger CSV File  -->
                                    <label for="UploadCSV">Upload Ledger CSV:</label>
                                    <input type="file" name="csv-file1" id="csv-file1" accept=".csv" required/>
                                </div>
                                <div>
                                    <!--  Upload the Appointment CSV file -->
                                    <label for="UploadCSV">Upload Appointment CSV:</label>
                                    <input type="file" name="csv-file2" id="csv-file2" accept=".csv" required/>
                                </div>
                                <button name="submit" class="btnUploadCSV" value="submit" type="submit">Upload</button>
                            </form>
                        </div>
                    </div>
                </li>
                <!-- Display the user name and role of the logged in user  -->
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
        <!-- End of Side Bar Nav  -->
        <!-- Top Bar Navigation  -->
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
        </div>
        <!-- End of Top bar Nav -->
        </header>
        <main>
            <!-- Main Page Contents -->
            <div id="mainContent">
                <!-- Displaying the Greeting Message for a Logged in User with Current (Today's) Day  -->
                <div id="greetingBox" class="greetingBox">
                    <p></p>
                    <h1>Happy <?php echo date('l');?>, <?php echo $user; ?>!<?php if($role == 'Manager'): ?> Here's your clinic weekly update</h1>
                    <?php else: ?>
                    Here's your weekly update</h1>
                    <?php endif; ?>
                    <p></p>
                </div>
                <!-- Revenues are displayed in the boxes -->
                <div id="avgFigure" class="avgFigure">
                    <!-- Revenue for the current month of the current year  -->
                    <div id="thisMonth" class="thisMonth">
                        <h2><?php $currentMonth = date('F Y'); echo $currentMonth;?></h2>
                        <p>Total Revenue<p>
                        <span id="thisMonthRevenue" class="thisMonthRevenue">
                        <?php  if($rev_this!='NONE') echo round($rev_this,2); else echo $rev_this; ?>
                    </span>
                    </div>
                    <!-- Revenue for the Previous month of the current year  -->
                    <div id="prevMonth" class="prevMonth">
                        <h2><?php $previousMonth = date('F Y', strtotime('-1 month')); echo $previousMonth;?></h2>
                        <p>Total Revenue<p>
                        <span id="prevMonthRevenue" class="prevMonthRevenue">
                        <?php  if($rev_prev!='NONE') echo round($rev_prev,2); else echo $rev_prev; ?>
                        </span>
                    </div>
                    <!--  Revenue for the current month of the last year  -->
                    <div id="lastYear" class="lastYear">
                        <h2><?php $currentMonthLastYear = date('F Y', strtotime('-1 year')); echo $currentMonthLastYear;?></h2>
                        <p>Total Revenue<p>
                        <span id="lastYearRevenue" class="lastYearRevenue">
                        <?php  if($rev_lastYear!='NONE') echo round($rev_lastYear,2); else echo $rev_lastYear; ?>
                        </span>
                    </div>
                </div>
                <!-- A Message is displayed according to the Revenue of the current month  (Comparing - PHP code above) -->
                <div id="exclaimBox" class="exclaimBox">
                    <p>
                        <!--  This thing is implemented in the above php -->
                    <?php echo $MessageString ?>
                    </p>
                </div>
            </div>
        </main>
    </body>
    <!-- To open the Upload CSV Pop up window (Java Script)  -->
    <script>
        function openPopup() {
            event.preventDefault();
            document.querySelector("#popup-container").style.display = "block";
            
        }
        function closePopup() {
            document.querySelector("#popup-container").style.display = "none";
           
        }
    </script>
</html>