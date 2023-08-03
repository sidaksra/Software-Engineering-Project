<?php
$schedule_id = (int)(trim(filter_var($_POST['schedule_id'] ?? null,FILTER_SANITIZE_STRING)));
$doctorName = trim(filter_var($_POST['doctorName'] ?? null,FILTER_SANITIZE_STRING));
$date = $_POST['date'] ?? null;
$startTime = $_POST['startTime'] ?? null;
$endTime= $_POST['endTime'] ?? null;
$MEMO= trim(filter_var($_POST['MEMO'] ?? null,FILTER_SANITIZE_STRING));

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

$stmt=$pdo->query("SELECT * FROM user_name WHERE role_id=1 OR role_id=3");
//dealing with the possibilties of not getting any results
if(!$stmt){
    die("Database pull did not return data");
}

//In order to display the Name of the active user : We are fetching the first name from the database for the activeuser 
$GetFirstNameQuery = "SELECT * FROM user_name WHERE user_code='$activeuser'"; 
$FQuery = $pdo->prepare($GetFirstNameQuery);
$FQuery->execute();
$AnsUser=$FQuery->fetch();
$user = $AnsUser['first_name'];   //Stores the value in variable First Name (It will store the First Name)

//In order to display the position of the active user
$GetRoleQuery = "SELECT * FROM user_name INNER JOIN role ON user_name.role_id=role.role_id WHERE user_code='$activeuser'"; 
$RQuery = $pdo->prepare($GetRoleQuery);
$RQuery->execute();
$AnsRole=$RQuery->fetch();
$role = $AnsRole['position_name'];

//If the Role is not receptionist then it will re-direct the client to the login page. 
//This page is only for Receptionist
if($role != "Receptionist"){
    header("Location: login.php");
    exit();
}
//When submit button is clicked.
if (isset($_POST['submit'])){
    //Fetching the user ID for a particular doctor 
    $GetUserIDQuery = "SELECT * FROM user_name WHERE first_name='$doctorName'"; 
    $UserIDQuery = $pdo->prepare($GetUserIDQuery);
    $UserIDQuery->execute();
    $AnsUserID=$UserIDQuery->fetch();
    $userID = $AnsUserID['user_code'];  

    //Inserting the Providers Schedule data into the database
    $queryInsert = "INSERT INTO provider_schedule VALUES (?,?,?,?,?,?)";
    $stmtInsert = $pdo->prepare($queryInsert);
    $result = $stmtInsert->execute([$schedule_id,$userID, $date,$startTime,$endTime,$MEMO]);

    //After adding the data in Timehseet page, it will re-direct the user to the Schedule Reporyt page to see the new update of data
    header("Location: scheduleReport.php");
    exit();

}
//End of PHP
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <!-- main CSS file  --> 
        <link rel="stylesheet" href="styles/main.css" />
        <link
            href="https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css"
            rel="stylesheet" />
        <script
            src="https://kit.fontawesome.com/170f096220.js"
            crossorigin="anonymous"></script>
        <link
            href="http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css"
            rel="stylesheet" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    </head>
    <body>
        <!-- Including the Receptionist Nav PHP File  -->
        <?php include "includes/receptionistNav.php"?>
        <section class="homeContent">
            <div>
                <h1>Timesheet</h1>
            </div>
            <div class="submitTimesheet">
                <!--Start of the form -->
                <form id="TimeSheetPage" name="submitTimesheet" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
                    <div class="enterTimeInfo">
                    <!--Enter the Date and Time Field -->    
                    <label for="datetime">Date</label>
                        <input
                            type="date"
                            id="date"
                            name="date"
                            required />
                    <!--  Select the Doctor  -->
                    <label for="userName">Dr. Name</label>
                        <select id="userName" name="doctorName">
                            
                            <?php foreach ($stmt as $row): ?>
                                <option><?php echo $row['first_name']?></option>
            			<?php endforeach; ?>
                        </select>
                    <!--Enter the Start Time   -->
                    <label for="time">Start Time</label>
                        <input
                            type="time"
                            id="startTime"
                            name="startTime"
                            required />
                    <!--Enter the End time -->
                    <label for="time">End Time</label>
                        <input
                            type="time"
                            id="endTime"
                            name="endTime"
                            required />

                        <!-- The form Login button -->
                        <div>
                            <button
                                type="submit"
                                name="submit"
                                value="submit"
                                class="submit">
                                Submit Time
                            </button>
                        </div>
                    </div>
                </form>
                <!-- End of form  -->
            </div>
        </section>
    </body>
</html>
