<?php

session_start();  //Starting the Session 

//If the user session is not declared, it means the user is not logined, therefore, it will redirect him to the Login Page
if(empty($_SESSION['ActiveuserID'])){
    header("Location: login.php");
}


//Setting the variables 
//session of active userID in activer user varriable
$activeuser = $_SESSION['ActiveuserID'];
// filtering and trimming all inputs
$first_name = trim(filter_var($_POST['first_name']??NULL, FILTER_SANITIZE_STRING));
$line_user_code = trim(filter_var($_GET['lineUser']??NULL, FILTER_SANITIZE_STRING));
$month_bar = trim(filter_var($_GET['month_dropdown'] ?? idate('m'), FILTER_SANITIZE_STRING));
$year_bar = trim(filter_var($_GET['year_dropdown'] ?? date('Y'), FILTER_SANITIZE_STRING));
$year_bar2 = trim(filter_var($_GET['year_dropdown_bar2'] ?? date('Y'), FILTER_SANITIZE_STRING));
$provider_bar2 = trim(filter_var($_GET['provider_name_dropdown'] ?? NULL, FILTER_SANITIZE_STRING));

include 'includes/library.php'; // Including the library
$pdo = connectDB();             //connnecting it to the database

//get all provider name and code
$pr_stmt=$pdo->query("SELECT first_name, user_code FROM user_name WHERE role_id=1 OR role_id=3");
$stmt=$pdo->query("SELECT first_name, user_code FROM user_name WHERE role_id=1 OR role_id=3");
$stmt_copy=$pdo->query("SELECT first_name, user_code FROM user_name WHERE role_id=1 OR role_id=3");
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

// get current month's goal
$month_goal_curr = "SELECT base_value AS goal FROM month_goal WHERE month = MONTH(now()) AND year = YEAR(now())";

// is a user is selected for the line graph
if(isset($_POST['lineUserNone'])){
    $line_user_code = NULL;
    header("Location: analysis.php");
    exit();
}

//Line graph query
$dataQuery = "SELECT DAY(transaction_date) day, SUM(total) total".(in_array($current_user_role,[2,5])?"":", SUM(CASE WHEN user_code=? THEN total ELSE 0 END) AS total")." FROM ledger WHERE MONTH(transaction_date)=MONTH(now()) AND YEAR(transaction_date)=YEAR(now())".(in_array($current_user_role,[2,5])?"":" AND user_code = ?")." GROUP BY day";
$data1Query = "SELECT DAY(transaction_date) day, SUM(total) total".(in_array($current_user_role,[2,5])?"":", SUM(CASE WHEN user_code=? THEN total ELSE 0 END) AS total")." FROM ledger WHERE MONTH(transaction_date)=MONTH(DATE_SUB(now(), INTERVAL 1 MONTH)) AND YEAR(transaction_date)=YEAR(now())".(in_array($current_user_role,[2,5])?"":" AND user_code = ?")." GROUP BY day";
$data2Query = "SELECT DAY(transaction_date) day, SUM(total) total".(in_array($current_user_role,[2,5])?"":", SUM(CASE WHEN user_code=? THEN total ELSE 0 END) AS total")." FROM ledger WHERE MONTH(transaction_date)=MONTH(now()) AND YEAR(transaction_date)=YEAR(DATE_SUB(now(), INTERVAL 1 YEAR))".(in_array($current_user_role,[2,5])?"":" AND user_code = ?")." GROUP BY day";

// creating copy of line graph query for when button is pressed in manager view since it does not take any conditions like above statements
$dataQuery_cp = "SELECT DAY(transaction_date) day, SUM(total) total FROM ledger WHERE MONTH(transaction_date)=MONTH(now()) AND YEAR(transaction_date)=YEAR(now()) AND user_code = ? GROUP BY day";
$data1Query_cp = "SELECT DAY(transaction_date) day, SUM(total) total FROM ledger WHERE MONTH(transaction_date)=MONTH(DATE_SUB(now(), INTERVAL 1 MONTH)) AND YEAR(transaction_date)=YEAR(now()) AND user_code = ? GROUP BY day";
$data2Query_cp = "SELECT DAY(transaction_date) day, SUM(total) total FROM ledger WHERE MONTH(transaction_date)=MONTH(now()) AND YEAR(transaction_date)=YEAR(DATE_SUB(now(), INTERVAL 1 YEAR)) AND user_code = ? GROUP BY day";

// BAR GRAPH QUERY
$dataQuerybar = "SELECT SUM(total) total, user_code FROM ledger WHERE MONTH(transaction_date) = ? AND YEAR(transaction_date) = ?  GROUP BY user_code"; 

// if submit button is pressed to refresh with dropdown selected values
if(isset($_POST['barSubmit'])){
    $month_bar = $_GET['month_dropdown'];
    $year_bar = $_GET['year_dropdown'];
    header("Location: analysis.php");
    exit();
}


// Second bar graph QUERY
$params_bar2 = array();
$dataQuerybar2 = "SELECT MONTH(transaction_date) months, SUM(total) total FROM ledger WHERE YEAR(transaction_date) = ?"; 
$params_bar2[] = $year_bar2;
if (!empty($provider_bar2)) {
    // Add an additional WHERE clause to filter by selected provider
    $dataQuerybar2 .= " AND user_code = ?";
    $params_bar2[] = $provider_bar2;
}
$dataQuerybar2 .= " GROUP BY MONTH(transaction_date)";

// if second submit button is pressed to refresh with dropdown selected values
if(isset($_POST['bar2Submit'])){
    $year_bar2 = $_GET['year_dropdown_bar2'];
    $provider_bar2 = $_GET['provider_name_dropdown'];
    header("Location: analysis.php");
    exit();
}

// preparing all queries
$goal0 = $pdo->prepare($month_goal_curr);
$FQuery0 = $pdo->prepare($dataQuery);
$FQuery1 = $pdo->prepare($data1Query);
$FQuery2 = $pdo->prepare($data2Query);
$FQuerybar = $pdo->prepare($dataQuerybar);
$FQuerybar2 = $pdo->prepare($dataQuerybar2);

//executing all prepared queries

// get current month goal
$goal0->execute();

// if manager or general manager is not logged in then send logged in user's id as conditional else no WHERE clause
if($current_user_role != 2 && $current_user_role != 5){
    $FQuery0->execute([$activeuser,$activeuser]);
    $FQuery1->execute([$activeuser,$activeuser]);
    $FQuery2->execute([$activeuser,$activeuser]);
}
else {
        $FQuery0->execute();
        $FQuery1->execute();
        $FQuery2->execute();
}

// if a user is selected then run the second set of line graph queries to pull that particular provider's data
if(!empty($line_user_code)){
    $FQuery0 = $pdo->prepare($dataQuery_cp);
    $FQuery1 = $pdo->prepare($data1Query_cp);
    $FQuery2 = $pdo->prepare($data2Query_cp);
    $FQuery0->execute([$line_user_code]);
    $FQuery1->execute([$line_user_code]);
    $FQuery2->execute([$line_user_code]);
}

// execute bar graph queries
$FQuerybar->execute([$month_bar,$year_bar]);
$FQuerybar2->execute($params_bar2);

// fetch int value of monthly goal
$get_curr_goal=$goal0->fetch();
// if a  goal exists then set in a variable to be used later else set it to 0
if ($get_curr_goal !== false) {
    $goal_curr = $get_curr_goal['goal'];
} else {
    // handle the case where $get_curr_goal is false, e.g. set a default value for $goal_curr
    $goal_curr = 0;
}
// add 10000 to finalize the maximum value of the y-axis range
$goal_max = $goal_curr + 10000;

// variable to add up all revenues upto date to have an increasing line graph
$monthsum = 0;

// array is created to store data for the graph
// we loop through the pulled data and substitute the required data to the array
// the code is repeated for EACH QUERY
$data = array();
foreach ($FQuery0 as $row) {
    $monthsum += $row['total'];
    $data[] = array('day' => $row['day'], 'total' => $monthsum);
}
$monthsum1 = 0;
$data1 = array();
foreach ($FQuery1 as $row) {
    $monthsum1 += $row['total'];
    $data1[] = array('day' => $row['day'], 'total' => $monthsum1);
}
$monthsum2 = 0;
$data2 = array();
foreach ($FQuery2 as $row) {
    $monthsum2 += $row['total'];
    $data2[] = array('day' => $row['day'], 'total' => $monthsum2);
}

//loop through the returned data 
$bar_data = array();
foreach ($FQuerybar as $row) {
  $bar_data[] = $row;
}
$bar2_data = array();
foreach ($FQuerybar2 as $row){
    $bar2_data[] = $row;
}

//now print the data
$line1 = [];
foreach ($data as $d) {
    $line1[] = ['x' => $d['day'], 'y' => round($d['total'], 2)];
}

$line2 = [];
foreach ($data1 as $d) {
    $line2[] = ['x' => $d['day'], 'y' => round($d['total'], 2)];
}

$line3 = [];
foreach ($data2 as $d) {
    $line3[] = ['x' => $d['day'], 'y' => round($d['total'], 2)];
}

// get the maximum y-value for each line graph
if(!empty(array_column($line1, 'y'))) $max_y1 = max(array_column($line1, 'y')); else $max_y1=0;
if(!empty(array_column($line2, 'y'))) $max_y2 = max(array_column($line2, 'y')); else $max_y2=0;
if(!empty(array_column($line3, 'y'))) $max_y3 = max(array_column($line3, 'y')); else $max_y3=0;

// get the maximum revenue value of the 3 line graphs (if they exist else put it to 0)
$max_y= max(((!empty(array_column($line1, 'y')))?max(array_column($line1, 'y')):0),((!empty(array_column($line2, 'y')))?max(array_column($line2, 'y')):0),((!empty(array_column($line3, 'y')))?max(array_column($line3, 'y')):0));

// if the maximum revenue value is greater than the current month goal then set the y-axis maximum value to that value - convert so the scale is a difference of 5000
$y_max_line = max($goal_max,$max_y);
$y_max_line = (ceil($y_max_line)%5000 === 0) ? ceil($y_max_line) : round(($y_max_line+5000/2)/5000)*5000;

// enter in an array which will store the data to be displayed on graph
$x_bar=array();
$y_bar=array();
foreach($bar_data as $d){
    array_push($y_bar,$d['total']);
    array_push($x_bar,$d['user_code']);
}

$x_bar2=array();
$y_bar2=array();
foreach($bar2_data as $d){
    array_push($y_bar2,$d['total']);
    array_push($x_bar2,$d['months']);
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
        <link rel="stylesheet" href="styles/main.css" defer/>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap"
            rel="stylesheet" />
        <script
            src="https://kit.fontawesome.com/170f096220.js"
            crossorigin="anonymous"></script>
        <script  defer src="script/main.js" type="text/javascript"></script>
        <script  src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script  src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
        <script  src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
        <link
            href="http://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css"
            rel="stylesheet" />
        <title>Data Analysis | Story</title>
    </head>
    <body>
        <header>
            <!-- Side Bar Navigation  -->
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
        <!-- Main Page Contents -->
        <main id="mainContent">     
            <form id="graphSubmit" action = "<?=htmlentities($_SERVER['PHP_SELF']); ?>" method="GET">    
            <div id="mainGraphContainer">
                <!-- Container that displays the Line Graph  -->
                    <div class="Graph3">
                            <h1>Monthly Production</h1>
                            <div class="lineGraphContainer">
                                <!-- is the user logged in is not a manager, then the names of the providers will not be displayed as a list of buttons -->
                                <div class="nameList"
                                <?php if($current_user_role != 2 && $current_user_role != 5){ ?> 
                                        style="display:none;"
                                    <?php } ?>
                                >
                                <!-- display list of all users -->
                                <input type="submit" name="lineUserNone" id="lineUserNone" class="lineUserNone"  value="All Users"/>
                                    <?php  foreach ($stmt_copy as $row): ?>
                                        <a id="lineUser" href="analysis.php?lineUser=<?= $row['user_code']; ?>" ><span class="lineUser"><?= $row['first_name'] ?></span></a>
                                    <?php endforeach; ?>
                                </div>
                                <!-- displaying the graph -->
                                <div class="Graph">
                                    <canvas id="myLineChart" class="graphPrint"></canvas>
                                    <script>
                                        const data = {
                                            labels: Array.from(Array(31).keys()).map((x) => x + 1), // days of the month
                                            datasets: [
                                                {
                                                label: 'This month',
                                                data: <?php echo json_encode($line1); ?>,
                                                borderColor: 'rgba(255, 99, 132, 1)',
                                                borderWidth: 1,
                                                fill: false,
                                                },
                                                {
                                                label: 'Last month',
                                                data: <?php echo json_encode($line2); ?>,
                                                borderColor: 'rgba(54, 162, 235, 1)',
                                                borderWidth: 1,
                                                fill: false,
                                                },
                                                {
                                                label: 'Last year',
                                                data: <?php echo json_encode($line3); ?>,
                                                borderColor: 'rgba(255, 206, 86, 1)',
                                                borderWidth: 1,
                                                fill: false,
                                                },
                                                {
                                                label: 'Month Goal',
                                                data: Array.from(Array(31).keys()).map((x) => x + 1).map((day) => ({ x: day, y: <?php echo $goal_curr; ?> })),
                                                borderColor: 'rgba(255, 0, 0, 1)',
                                                borderWidth: 1,
                                                fill: false,
                                                pointRadius: 0,
                                                },
                                            ],
                                            };

                                            //define max value of y-axis
                                            const y_max_line = <?php echo $y_max_line; ?>;
                                            const config = {
                                            type: 'line',
                                            data,
                                            options: {
                                                responsive: true,
                                                tooltops: {
                                                    mode: 'label',
                                                },
                                                title: {
                                                    display: true,
                                                    text: "Revenue Monthly Production",
                                                },
                                                scales: {
                                                    xAxes: [{
                                                        display: true,
                                                        scaleLabel:{
                                                            display: true,
                                                            labelString: 'Day of Month'
                                                        },
                                                    }],
                                                    yAxes: [{
                                                        display: true,
                                                        scaleLabel: {
                                                            display: true,
                                                            labelString: 'Total Revenue',
                                                        },
                                                        ticks: {
                                                        callback: function (value, index, values) {
                                                            return '$' + value;
                                                        },
                                                        min:0,
                                                        max: y_max_line,
                                                        stepSize: 5000
                                                        },
                                                    }],
                                                },
                                            },
                                            };

                                            var myChart = new Chart(document.getElementById('myLineChart'), config);
                                    </script>
                                </div>
                                <!-- Print button that will print the line graph -->
                                <div class="printbtn">
                                    <button id="printbtn" class="printbtn" onclick="printGraphContainer()"> Print </button>
                                </div>
                            </div>
                        </div>   
                <!-- the container that displays 2 bar graphs is not displayed if user logged is not a manager or general manager  -->
                <div class="GraphContainer"
                    <?php if($current_user_role != 2 && $current_user_role != 5){ ?> 
                        style="display:none;"
                    <?php } ?>
                >
                    <div class="GraphContainerChild">
                        <h1>Provider's Revenue Analysis</h1>
                        <div class="chartDropdown">
                            <!-- dropdown to select a month and year -->
                            <select id="month_dropdown" name="month_dropdown">
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
                            <select id="year_dropdown" class="year_dropdown" name="year_dropdown">
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
                            <input type="submit" name="barSubmit" id="barSubmit" class="barSubmit"  value="Refresh"/>
                        </div>
                        <!-- display the month and year whose graph is  -->
                        <div class="chartDropdown">
                            <p><b>Month:</b> <?php echo $month_bar ?> </p>
                            <p><b>Year:</b> <?php echo $year_bar ?> </p>
                        </div>
                        <!-- display graph -->
                        <div class="Graph">
                            <canvas id="barGraph"></canvas>
                            <script>
                                var yValues =<?php echo json_encode($y_bar, JSON_NUMERIC_CHECK);?>;
                                var xValues =<?php echo json_encode($x_bar, JSON_NUMERIC_CHECK);?>;
                                var colors = ["rgb(20, 52, 164)","rgb(100, 149, 237)","rgb(135, 206, 235)"];
                                var barColors = []
                                for (let i = 0; i < xValues.length; i++) {
                                    barColors[i] = colors[i%colors.length];
                                }
                                new Chart("barGraph", {
                                type: "bar",
                                data: {
                                    labels: xValues,
                                    datasets: [{
                                    backgroundColor: barColors,
                                    data: yValues
                                    }]
                                },
                                options: {
                                    legend: {display: false},
                                    title: {
                                        display: true,
                                        text: "Provider Revenues"
                                    },
                                }
                                });
                            </script>
                        </div>
                    </div>
                    <!-- second bar graph -->
                    <div class="GraphContainerChild">
                        <h1>Clinic's Revenue Analysis</h1>
                        <!-- dropdown to let the user select a year and provider name -->
                        <div class="chartDropdown">
                            <select id="year_dropdown_bar2" class="year_dropdown_bar2" name="year_dropdown_bar2">
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
                            <select id="provider_name_dropdown" name="provider_name_dropdown">
                                <option value="" selected disabled hidden>Select a Provider..</option>
                                <option value="">All Users</option>
                                <?php  foreach ($stmt as $row): ?>
                                   <option value="<?php echo $row['user_code'];?>"><?php echo $row['first_name']?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="submit" id="bar2Submit" name="bar2Submit" value="Refresh"/>
                        </div>
                        <!-- display the value of the year and provider whose graph is being displayed -->
                        <div class="chartDropdown">
                            <p><b>Year: </b> <?php echo $year_bar2 ?></p>
                            <p><b>Provider: </b> <?php echo $provider_bar2 ?></p>
                        </div>
                        <!--  bar graph 2-->
                        <div class="Graph">
                            <canvas id="barGraph2"></canvas>
                            <script>
                                var yValues =<?php echo json_encode($y_bar2, JSON_NUMERIC_CHECK);?>;
                                var xValues =<?php echo json_encode($x_bar2, JSON_NUMERIC_CHECK);?>;
                                var colors = ["rgb(250, 141, 0)","rgb(255, 171, 42)","rgb(255, 200, 69)"];
                                var barColors = []
                                for (let i = 0; i < xValues.length; i++) {
                                    barColors[i] = colors[i%colors.length];
                                }
                                new Chart("barGraph2", {
                                type: "bar",
                                data: {
                                    labels: xValues,
                                    datasets: [{
                                    backgroundColor: barColors,
                                    data: yValues
                                    }]
                                },
                                options: {
                                    legend: {display: false},
                                    title: {
                                    display: true,
                                    text: "Provider Revenues"
                                    }
                                }
                                });
                            </script>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </main>   
    </body>
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