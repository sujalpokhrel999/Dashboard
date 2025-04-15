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

//Handle date filteration
$selected_date = isset($_POST['selected_date']) ? $_POST['selected_date'] : date('Y-m-d');

// Handle task addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_task'])) {
    $task = $_POST['task'];
    $user_id = $_SESSION['user_id']; // Assuming the user ID is stored in the session

    // Using prepared statements to securely insert data
    $stmt = $conn->prepare("INSERT INTO tasks (user_id, task) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $task);
    if ($stmt->execute()) {
        // Redirect to the same page to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Handle error (optional)
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



// Modify your query to filter tasks by the selected date
$tasks_query = "SELECT * FROM tasks WHERE user_id = '$user_id' AND DATE(created_at) = '$selected_date'";
$tasks_result = mysqli_query($conn, $tasks_query);





// Handle task deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_task'])) {
    $task_id = $_POST['task_id'];
    $user_id = $_POST['user_id'];

    // Prepare the SQL statement to delete the task
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to the same page to avoid form resubmission
        header("Location: task.php");
        exit();
    } else {
        echo "Error deleting task: " . $conn->error;
    }

    $stmt->close();
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
// Handle status update via AJAX
if (isset($_POST['update_status'])) {
    $task_id = $_POST['task_id'];
    $new_status = $_POST['status'];

    // Update the task status in the database
    $stmt = $conn->prepare("UPDATE tasks SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $task_id);

    if ($stmt->execute()) {
        echo "Status updated successfully";  // You can send a response back
    } else {
        echo "Error updating status: " . $stmt->error;
    }
    $stmt->close();
    exit();
}



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tasks - EffortEase</title>
    <link rel="stylesheet" href="./task.css">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
</head>

<body class="dark">

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
                        <a href="../homepage/homepage.php">
                            <i class='bx bx-home-alt icon' ></i>
                            <span class="text nav-text">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="./task.php">
                            <i class='bx bx-bar-chart-alt-2 icon' ></i>
                            <span class="text nav-text">Tasks</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../expenses/expenses.php">
                            <i class='bx bx-wallet icon'></i>
                            <span class="text nav-text">Expenses</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../newIncome/newIncome.php">
                            <i class='bx bx-money icon'></i>
                            <span class="text nav-text">Income</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../Income/income.php">
                            <i class='bx bx-dollar icon'></i>
                            <span class="text nav-text">Finanace</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="../report/report.php">
                            <i class='bx bx-file icon'></i>
                            <span class="text nav-text">Reports</span>
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

    <div class="main-content">
        <div class="header">
            <div class="project-title">
                <h1>Task Manager
                </h1>
            </div>

            <div class="project-subtitle">Organize, Prioritize, and Shine a Light on Your Path to Success with Intelligence and Integrity.</div>

            <div class="header-actions">
                <div class="button-types">
                    <button onclick="openModal()" class="add-button">+ Add to task</button>
                    <!-- Trigger button to open modal -->

                    <button class="more-button"><img
                            src="https://img.icons8.com/?size=100&id=61873&format=png&color=000000" class="more" alt=""
                            srcset=""></button>
                </div>
                <div class="search-filter">
                    <input type="text" class="search-bar" placeholder="Search...">
                    <button class="more-button"><img
                            src="https://img.icons8.com/?size=100&id=3004&format=png&color=000000" class="more" alt=""
                            srcset=""></button>
                    <button class="more-button"><img
                            src="https://img.icons8.com/?size=100&id=61873&format=png&color=000000" class="more" alt=""
                            srcset=""></button>
                </div>
            </div>
        </div>

        <div class="separator"></div>

        <div class="date-filter">
    <form method="POST" class="filter-form">
        <div class="date-field">
            <label>Select Date</label>
            <input type="date" name="selected_date" value="<?php echo isset($_POST['selected_date']) ? $_POST['selected_date'] : date('Y-m-d'); ?>">
        </div>
        <button type="submit" class="generate-button">Search</button>
    </form>
</div>


            <?php if (mysqli_num_rows($tasks_result) > 0): ?>
    <table class="board-table">
        <thead>
            <tr>
                <th>Task</th>
                <th>Status</th>
                <th>Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php while ($task = mysqli_fetch_assoc($tasks_result)): ?>
            <tr>
                <td>
                    <div class="folder">
                        <?php echo htmlspecialchars($task['task']); ?>
                    </div>
                </td>
                <td>
                   <!-- Status is shown as a clickable span -->
        <span class="status-badge status-<?php echo strtolower($task['status']); ?>" 
              id="status-<?php echo $task['id']; ?>" 
              data-task-id="<?php echo $task['id']; ?>"
              data-status="<?php echo $task['status']; ?>">
            <?php echo $task['status']; ?>
        </span>
                </td>
                <td>
                    <?php echo $task['created_at']; ?>
                </td>
                <td>
                    <div class="delete">
                        <div class="deleteIcon">
                            <form method="POST" action="task.php">
                                <input type="hidden" name="task_id" value="<?php echo $task['id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                <button type="submit" name="delete_task" class="deleteBtn">
                                <img src="../assets/images/delete.png" alt="Delete Expenses" height="20px" width="20px" alt="Delete Task">
                                </button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="noTask">
        <img src="./notask.png" alt="No tasks available">
    </p>
<?php endif; ?>

        </div>
    </div>
    <!-- Modal overlay and content -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <form method="post" action="">
                <button class="close-button" onclick="closeModal()">×</button>
                <h1>Add New Task</h1>
                <p class="subtitle">Fill in the details below to create your task.</p>
                <div class="separator"></div>
                <label class="input-label">Task Description *</label>
                <input type="text" id="taskbtn" name="task" class="email-input"
                    placeholder="E.g., Buy groceries, Complete project report..."required>
                <button type="submit" id="addBtn" name="add_task" class="reset-button">Add</button>
            </form>
        </div>
    </div>

    <script src="./task.js"></script>


    <script>
        function openModal() {
            document.getElementById('modalOverlay').classList.add('show');
            document.querySelector('.modal').classList.add('show');
        }

        function closeModal() {
            document.getElementById('modalOverlay').classList.remove('show');
            document.querySelector('.modal').classList.remove('show');
        }
        

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
    <script>
       const taskInput = document.getElementById("taskBtn");
const addBtn = document.getElementById("addBtn");

// Correct way to disable/enable a button
if (taskInput.value === "") {
    addBtn.disabled = true;
} else {
    addBtn.disabled = false;
}

document.querySelector('form').addEventListener('submit', function(event) {
    if (taskInput.value.trim() === "") {
        event.preventDefault(); // Prevent form submission if input is empty
        addBtn.disabled = true;
    }
});
    </script>


</body>

</html>