<?php
// Include your database connection file
include 'connect.php';

// Start the session at the beginning of the script
session_start();

/**
 * Function to handle user logout.
 */
function handleLogout() {
    // Unset all session variables and destroy the session
    $_SESSION = array();
    session_destroy();
    header("Location: index.php"); // Redirect to login page
    exit();
}

// Handle logout when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['logout'])) {
    handleLogout();
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap"
        rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="./task.css">
</head>

<body>
    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="div" style="    flex-basis: 80%;
            display: flex;
            flex-direction: column;
            gap: 35px;">

                <div class="profile">
                    <img src="pp.jpg" alt="Profile Picture">
                    <h2><?php 
if (isset($_SESSION['email'])) {
    $email = $_SESSION['email'];
    $query = mysqli_query($conn, "SELECT users.* FROM `users` WHERE users.email='$email'");
    
    // Check if the query executed successfully
    if ($query) {
        while ($row = mysqli_fetch_array($query)) {
            echo $row['name'];
        }
    } else {
        echo "Error in query execution: " . mysqli_error($conn);
    }
}
?>
                    </h2>
                </div>
                <div class="menus">
                    <ul class="menu">
                        <li>
                            <a href="./homepage.php">
                                <!--?xml version="1.0" encoding="UTF-8"?-->
                                <svg id="Activity" width="18px" height="18px" viewBox="0 0 24 24" version="1.1"
                                    xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                                    <title>Iconly/Light/Activity</title>
                                    <g id="Iconly/Light/Activity" stroke="none" stroke-width="1.5" fill="none"
                                        fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round">
                                        <g id="Activity" transform="translate(2.000000, 1.500000)" stroke="#000000"
                                            stroke-width="1.5">
                                            <polyline id="Path_33966"
                                                points="5.24485128 13.2814646 8.23798631 9.39130439 11.652174 12.0732266 14.5812358 8.29290622">
                                            </polyline>
                                            <circle id="Ellipse_741" cx="17.9954234" cy="2.70022885" r="1.92219681">
                                            </circle>
                                            <path
                                                d="M12.9244852,1.62013731 L5.6567506,1.62013731 C2.64530894,1.62013731 0.778032041,3.75286043 0.778032041,6.76430209 L0.778032041,14.846682 C0.778032041,17.8581237 2.60869567,19.9816935 5.6567506,19.9816935 L14.2608696,19.9816935 C17.2723113,19.9816935 19.1395882,17.8581237 19.1395882,14.846682 L19.1395882,7.80778036"
                                                id="Path"></path>
                                        </g>
                                    </g>
                                </svg>Dashboard</a></li>
                        <li><a href="task.php"><img width="20" height="20"
                                    src="https://img.icons8.com/material-outlined/18/student-center.png"
                                    alt="student-center" />Courses</a></li>
                        <li><a href="expenses.html"><img width="20" height="20" src="https://img.icons8.com/ios-glyphs/20/task.png"
                                    alt="task" />Expenses</a></li>
                        <li><a href="#"><img width="20" height="20"
                                    src="https://img.icons8.com/material-outlined/18/compass.png"
                                    alt="compass" />Schedules</a></li>
                        <li><a href="#"><img width="20" height="20"
                                    src="https://img.icons8.com/fluency-systems-regular/20/user-group-woman-woman.png"
                                    alt="user-group-woman-woman" />Classmates</a></li>
                        <li><a href="#"><img width="20" height="20" src="https://img.icons8.com/ios/20/settings.png"
                                    alt="settings" />Settings</a></li>
                    </ul>
                </div>
            </div>


            <form method="post" action="">
                <button type="submit" name="logout" class="logout"><img width="20" height="20"
                        src="https://img.icons8.com/windows/20/exit.png" alt="exit" / style="margin-right: 10px;">Log
                    Out</button>
            </form>
        </nav>
        <!-- Main Content -->
        <div class="main-content">
            <div class="middle">
                <header>
                    <h1>Hi, Alysia</h1>
                </header>
                <!-- Assignments Section -->
                <section class="assignments" id="assignments">
                    <h2>Today's Task</h2>
                    <div class="assignment-card" id="assignment-card" >
                        <div class="taskHead">
                        <p id="asd">Typography Test</p>
                        <p id="timeStamp">1:00-5:00</p>
                        </div>
                        <span class="status" style="background-color: #10B981;">Completed</span>
                        <img width="20" height="20" src="https://img.icons8.com/fluency-systems-regular/20/filled-trash.png" alt="filled-trash"/ class='delete' aria-label="Delete">
                    </div>
                    <div class="assignment-card"  id="assignment-card">
                    <div class="taskHead">
                        <p>Typography Test</p>
                        <p id="timeStamp">1:00-5:00</p>
                        </div>
                        <span class="status" style="background-color: #10B981;">Completed</span>
                        <img width="20" height="20" src="https://img.icons8.com/fluency-systems-regular/20/filled-trash.png" alt="filled-trash"/ class='delete'>
                    </div>
                    <div class="assignment-card"  id="assignment-card">
                    <div class="taskHead">
                        <p>Typography Test</p>
                        <p id="timeStamp">1:00-5:00</p>
                        </div>
                        <span class="status">Incomplete</span>
                        <img width="20" height="20" src="https://img.icons8.com/fluency-systems-regular/20/filled-trash.png" alt="filled-trash"/ class='delete'>
                    </div>
                </section>
            </div>

            <!----------Add task panel------>
            <div class="side-panel">
                <div class="upper">
                    <div class="header">
                        <span class="badge">PRO</span>
                        <button class="icon-btn">&#x21bb;</button>
                    </div>

                    <div class="taskDetails">
                        <h1>Mobile Design</h1>
                        <p class="description">This course will teach you how to do just that — design great mobile user
                            interfaces.</p>
                        <div class="skills">
                            <span style="color: #777;
                    font-size: 12px;
                    ">Priority's Available</span>
                            <div class="priorityAvailable">
                                <span class="skill ">High</span>
                                <span class="skill ">Medium</span>
                                <span class="skill">Low </span>
                            </div>
                        </div>
                    </div>
                    <div class="PandD" style="margin-top: -15px;">
                        <div class="participants">
                            <span style="color: #777;
                    font-size: 12px;
                    ">Add new task</span>
                            <div class="avatars">
                                <input type="text" placeholder="" id="inputField">
                            </div>

                        </div>
                        <div class="details">
                            <div class="detail">
                                <span class="icon"><img width="18" height="18"
                                        src="https://img.icons8.com/fluency-systems-regular/18/bullish.png"
                                        alt="bullish" /></span>
                                <div class="detailtype">
                                    <div class="detailsdisc">
                                    <span>Priority</span>
                                    <p id="level">Level</p>
                                    </div>
                                    <select id="priorityDropdown" onchange="updatePriority()">
                                    <option  value="Level">None</option>
                                    <option  value="High" >High</option>
                                    <option  value="Medium" >Medium</option>
                                    <option  value="Low">Low</option>
                                    </select>
                                </div>
                            </div>
                            <div class="detail">
                                <span class="icon"><img width="18" height="18"
                                        src="https://img.icons8.com/fluency-systems-regular/24/calendar--v1.png"
                                        alt="calendar--v1" /></span>
                                <div class="detailType">
                                    <span>2 weeks</span>
                                    <p>Duration</p>
                                </div>
                            </div>
                            <div class="detail">
                                <span class="icon"><img width="18" height="18"
                                        src="https://img.icons8.com/ios/24/clock.png" alt="clock" /></span>
                                <div class="detailType">
                                    <span>12:00–13:00</span>
                                    <p>Time</p>
                                </div>
                            </div>
                            <div class="detail">
                                <span class="icon"><img width="18" height="18"
                                        src="https://img.icons8.com/ios/22/wallet--v1.png" alt="wallet--v1" /></span>
                                <div class="detailType">
                                    <span>$360</span>
                                    <p>Price</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="actions">
                    <button class="join-btn" id="addBtn">Add task</button>
                    <button class="reschedule-btn" id="clearBtn">Let me rethink</button>
                </div>
            </div>


        </div>
    </div>

    <script src="./task.js"></script>
</body>

</html>