<?php
// Include database connection file
include('../includes/connect.php');

// Start the session at the beginning of the script
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Handle task addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $user_id = $_SESSION['user_id']; // Assuming the user ID is stored in the session

    // Using prepared statements to securely insert data
    $stmt = $conn->prepare("INSERT INTO tasks (user_id, task) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $task);
    if ($stmt->execute()) {
        echo "Task added successfully!";
    } else {
        echo "Error adding task: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch user data
$email = $_SESSION['email'];
$email = mysqli_real_escape_string($conn, $email);
$query = mysqli_query($conn, "SELECT username, id FROM users WHERE email='$email'");

if ($query && mysqli_num_rows($query) > 0) {
    $row = mysqli_fetch_assoc($query);
    $username = $row['username'];
    $user_id = $row['id'];
} else {
    echo "No user found";
    exit();
}

if (isset($_POST['logout'])) {
    // Unset all session variables
    session_unset();
    // Destroy the session
    session_destroy();
    // Redirect to the login page
    header("Location: ../auth/login.php");
    exit();
}


// Fetch total expenses for the logged-in user
$total_expenses_query = "SELECT SUM(amount) as total_expenses FROM expenses WHERE user_id = '$user_id'";
$total_expenses_result = mysqli_query($conn, $total_expenses_query);
$total_expenses = 0;
if ($total_expenses_result && mysqli_num_rows($total_expenses_result) > 0) {
    $row = mysqli_fetch_assoc($total_expenses_result);
    $total_expenses = $row['total_expenses'];
}

// Fetch total tasks for the logged-in user
$total_tasks_query = "SELECT COUNT(*) as total_tasks FROM tasks WHERE user_id = '$user_id'";
$total_tasks_result = mysqli_query($conn, $total_tasks_query);
$total_tasks = 0;
if ($total_tasks_result && mysqli_num_rows($total_tasks_result) > 0) {
    $row = mysqli_fetch_assoc($total_tasks_result);
    $total_tasks = $row['total_tasks'];
}

// Fetch tasks for the logged-in user
$tasks_query = "SELECT * FROM tasks WHERE user_id = '$user_id' ORDER BY created_at DESC";
$tasks_result = mysqli_query($conn, $tasks_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap"
        rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="./homepagecss.css">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>

<body>
    <div class="sidebar">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="../assets/images/logo.png" alt="">
                </span>
                <div class="text logo-text">
                    <span class="name">EffortEase</span>
                </div>
            </div>
        </header>
        <div class="menu-bar">
            <div class="menu">
                <li class="search-box">
                    <i class='bx bx-search icon'></i>
                    <input type="text" placeholder="Search...">
                </li>
                <ul class="menu-links">
                    <li class="nav-link">
                        <a href="./homepage.php">
                            <i class='bx bx-home-alt icon'></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../task/task.php">
                            <i class='bx bx-bar-chart-alt-2 icon'></i>
                            <span class="text nav-text">Tasks</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../expenses/expenses.php">
                            <i class='bx bx-bell icon'></i>
                            <span class="text nav-text">Expenses</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="bottom-content">
                <li class="">
                    <form method="POST" action="" style="
                        display: flex;
                        border-radius: 6px;
                        width: 100%;
                        height: 100%;
                        align-items: center;">
                            <button type="submit" class="logoutBtn" name="logout">
                                    <i class='bx bx-log-out icon'></i>
                                    <span class="text nav-text">Logout</span>
                            </button>
                    </form>
                </li>
                <li class="mode">
                    <div class="sun-moon">
                        <i class='bx bx-moon icon moon'></i>
                        <i class='bx bx-sun icon sun'></i>
                    </div>
                    <span class="mode-text text">Dark mode</span>
                    <div class="toggle-switch">
                        <span class="switch"></span>
                    </div>
                </li>

            </div>
        </div>
    </div>

    <!-- Main Content -->
     <div class="main-content">
     <header class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-8">
            <h1 class="text-2xl font-bold">Analytics</h1>
            <!-- <div class="flex gap-4">
                <button class="bg-black w-50 h-8 text-m text-white px-4 py-2 rounded-full">Full Statistics</button>
                <button class="bg-gray text-gray-500 px-4 py-2 rounded-full">Results Summary</button>
            </div> -->
        </div>
        <div class="flex items-center gap-4">
            <button class=" w-10 h-10 text-white p-2 rounded-full notification">
            <img width="20" height="20" src="https://img.icons8.com/puffy/20/appointment-reminders.png" alt="appointment-reminders" style="filter: brightness(0) invert(1);"/>
            </button>
            <div class="w-10 h-10 bg-gray-200 rounded-full profileImg" ></div>
        </div>
    </header>

    <div class="grid grid-cols-4 gap-6 mb-8">
        <div class="card">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h3 class="font-bold mb-2 ">Total Tasks</h3>
                    <div class="text-sm text-gray-500">14 November</div>
                </div>
                <div class="text-gray-400">🔔</div>
            </div>
            <div class="flex justify-between items-center">
                <div class="text-xl font-bold"><?php echo number_format($total_tasks); ?></div>
                <div class="text-red-500 text-sm">From last week</div>
                <!-- <button class="bg-black w-10 h-10 text-white p-2 rounded-full"></button> -->
            </div>
        </div>

        <div class="card">
            <div class="flex justify-between items-start mb-4">
                <h3 class="font-bold">Expenses</h3>
                <div class="text-purple-400">  <div class="text-l font-bold">$<?php echo number_format($total_expenses, 2); ?></div></div>
            </div>
            <svg viewBox="0 0 200 60" class="mb-0">
                <path d="M0,30 C20,40 40,20 60,30 S100,40 140,30 S180,20 200,30" class="chart-line"/>
            </svg>

        </div>

        <div class="card">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold">Income statistics</h3>
                <div class="text-green-500"><div class="text-l font-bold">+8%</div></div>
            </div>
            <div class="flex items-end h-7 gap-4">
                <div class="h-1/3 w-12 bg-blue-100 rounded"></div>
                <div class="h-1/2 w-12 bg-blue-200 rounded"></div>
                <div class="h-full w-12 bg-blue-400 rounded"></div>
            </div>
            <div class="flex justify-between text-sm text-gray-500 mt-2">
                <div>15%</div>
                <div>21%</div>
                <div>32%</div>
            </div>
        </div>

        <div class="plan-card">
            <div class="text-l font-bold mb-8">Choose Best Plan!</div>
            <div class="flex gap-4">
                <button class="bg-white text-gray-800 px-4 py-2 rounded-lg text-sm">Details</button>
                <button class="bg-black text-white px-4 py-2 rounded-lg text-sm">Upgrade</button>
            </div>
        </div>
    </div>

    <section class="mb-8">
    
    </section>

    

   </div>
    <script src="./homepagescript.js"></script>
    <script>
        // Toggle the new task form
        document.getElementById('newTaskBtn').addEventListener('click', function () {
            const form = document.querySelector('.new-task-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });
    </script>

    <script>
        const body = document.querySelector('body'),
      sidebar = body.querySelector('nav'),
      searchBtn = body.querySelector(".search-box"),
      modeSwitch = body.querySelector(".toggle-switch"),
      modeText = body.querySelector(".mode-text");

modeSwitch.addEventListener("click" , () =>{
    body.classList.toggle("dark");
    
    if(body.classList.contains("dark")){
        modeText.innerText = "Light mode";
    }else{
        modeText.innerText = "Dark mode";
        
    }
});
    </script>
    
</body>

</html>