<?php
$doctorName = $_POST['doctorName'] ?? null;
$date =$_POST['date'] ?? null;
$startTime =$_POST['startTime'] ?? null;
$endTime=$_POST['endTime'] ?? null;

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

$stmt=$pdo->query("SELECT * FROM provider_schedule INNER JOIN user_name ON user_name.user_code=provider_schedule.user_code ORDER BY schedule_date DESC");
//dealing with the possibilties of not getting any results
if(!$stmt){
    die("Database pull did not return data");
}

//In order to display the Name of the active user : We are fetching the first name from the database for the activeuser 
$GetFirstNameQuery = "SELECT * FROM user_name WHERE user_code='$activeuser'"; 
$FQuery = $pdo->prepare($GetFirstNameQuery);
$FQuery->execute();
$AnsUser=$FQuery->fetch();
$user = $AnsUser['first_name'];   //Stores the value in variable username (It will store the Username)

//In order to display the position of the active user
$GetRoleQuery = "SELECT * FROM user_name INNER JOIN role ON user_name.role_id=role.role_id WHERE user_code='$activeuser'"; 
$RQuery = $pdo->prepare($GetRoleQuery);
$RQuery->execute();
$AnsRole=$RQuery->fetch();
$role = $AnsRole['position_name'];

if($role != "Receptionist"){
    header("Location: login.php");
    exit();
}

if(isset($_POST["delSchedule"])){
    

    $deleteitemid = $_POST["delSchedule"];//takes the value of the button of the trash button which saves the itemID
    $DeletespecificItem = "DELETE FROM provider_schedule WHERE schedule_id=?";  //selects the user filtered from the itemID
    $stmtDeleteitem = $pdo->prepare($DeletespecificItem);
    $stmtDeleteitem->execute([$deleteitemid]);
    $itemdeleted=$stmtDeleteitem->fetch();
    header('Location: '.$_SERVER['REQUEST_URI']);//Redirects to the same page ---- referenced from stackoverflow

}


?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
        <?php include "includes/receptionistNav.php"?>
        <section class="homeContent">
            <div>
                <h1>Schedule Report</h1>
            </div>
            <div class="submitTimesheet">
                <form method="post">
                <div class="numbersTable">
                    <table class="dataSheet">
                    
                        <tr>
                            <th>User ID</th>
                            <th>Dentist Name</th>
                            <th>Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th></th>
                           
                        </tr>
                        <?php foreach ($stmt as $row): ?>
                        <tr class ="Row">
                            <td><?php echo $row['user_code']  ?></td>
                            <td><?php echo $row['first_name']  ?></td>
                            <td><?php echo $row['schedule_date']  ?></td>
                            <td><?php echo $row['start_time']  ?></td>
                            <td><?php echo $row['end_time']  ?></td>
                           
                            <td><button type="submit" onclick="return  confirm('Are you sure you want to delete this schedule?')" class="deleteSchedule" name="delSchedule" value=<?php echo "$row[schedule_id]"?>><i class="fa-solid fa-delete-left"></i></button></td>
                           
                        </tr>
            			<?php endforeach; ?>
                    </table>
                </div>
                </form>
            </div>

        </section>
    </body>
</html>
