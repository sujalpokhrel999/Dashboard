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

// Fetch tasks for the logged-in user
$tasks_query = "SELECT * FROM tasks WHERE user_id = '$user_id' ORDER BY created_at DESC";
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



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management UI</title>
    <link rel="stylesheet" href="./task.css">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
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

    <div class="main-content">
        <div class="header">
            <div class="project-title">
                <h1>Task Manager
                </h1>
            </div>

            <div class="project-subtitle">The logo embodies a source of light that expands outward, symbolizing
                intelligence, integrity and originality.</div>

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

        <div class="board-section">
            <!-- <div class="section-header">
                <h2>Wireframe</h2>
                <button class="add-column">+ Add column</button>
            </div> -->

            <?php if (mysqli_num_rows($tasks_result) > 0): ?>
    <table class="board-table">
        <thead>
            <tr>
                <th>Task</th>
                <th>People</th>
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
                    <div class="avatar-group">
                        <div class="avatar"></div>
                        <div class="avatar"></div>
                        <div class="avatar"></div>
                    </div>
                </td>
                <td>
                    <span class="status-badge status-ongoing" id="status-badge">Ongoing</span>
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
                                    <img src="https://img.icons8.com/puffy/20/trash.png" alt="Delete Task">
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
                <input type="text" name="task" class="email-input"
                    placeholder="E.g., Buy groceries, Complete project report...">
                <button type="submit" name="add_task" class="reset-button">Add</button>
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
</body>

</html>