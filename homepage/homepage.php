<?php
// Include database connection file
include('../auth/connect.php');

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

// Fetch tasks for the logged-in user
$tasks_query = "SELECT * FROM tasks WHERE user_id = '$user_id' ORDER BY created_at DESC";
$tasks_result = mysqli_query($conn, $tasks_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="./homepagecss.css">
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <nav class="sidebar">
            <div class="profile-section" style="flex-basis: 80%; display: flex; flex-direction: column; gap: 35px;">
                <div class="profile">
                    <img src="pp.jpg" alt="Profile Picture">
                    <h2><?php echo htmlspecialchars($username); ?></h2>
                </div>

                <div class="menus">
                    <ul class="menu">
                        <li><a href="./homepage.php"><img src="https://img.icons8.com/material-outlined/18/home.png" alt="dashboard"/> Dashboard</a></li>
                        <li><a href="../task/task.php"><img src="https://img.icons8.com/material-outlined/18/student-center.png" alt="courses"/> Courses</a></li>
                        <li><a href="../expenses/expenses.html"><img src="https://img.icons8.com/ios-glyphs/20/task.png" alt="expenses"/> Expenses</a></li>
                        <li><a href="#"><img src="https://img.icons8.com/material-outlined/18/compass.png" alt="schedules"/> Schedules</a></li>
                        <li><a href="#"><img src="https://img.icons8.com/fluency-systems-regular/20/user-group-woman-woman.png" alt="classmates"/> Classmates</a></li>
                        <li><a href="#"><img src="https://img.icons8.com/ios/20/settings.png" alt="settings"/> Settings</a></li>
                    </ul>
                </div>

                <!-- Logout Form -->
                <form method="post" action="logout.php">
                    <button type="submit" name="logout" class="logout"><img src="https://img.icons8.com/windows/20/exit.png" alt="logout"/> Log Out</button>
                </form>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Hi, <?php echo htmlspecialchars($username); ?></h1>
                <button class="new-course-btn" id="newTaskBtn">Add New Task</button>
            </header>

            <!-- Stats Section -->
            <section class="stats">
                <div class="stat-card">
                    <h3 id="TotalTaskCounter"><?php echo mysqli_num_rows($tasks_result); ?></h3>
                    <p>Total Tasks</p>
                </div>
                <div class="stat-card">
                    <h3 id="CompletedTaskCounter">196/200</h3>
                    <p>Completed Tasks</p>
                </div>
                <div class="stat-card">
                    <h3 id="TotalExpensesCounter">12</h3>
                    <p>Total Expenses</p>
                </div>
            </section>

            <!-- Add Task Form -->
            <section class="new-task-form" style="display: none;">
                <h2>Add New Task</h2>
                <form method="post" action="">
                    <input type="text" name="task" required placeholder="Enter Task">
                    <button type="submit" name="add_task">Add Task</button>
                </form>
            </section>

            <!-- Tasks Section -->
            <section class="assignments">
                <h2>Today's Tasks</h2>
                <?php if (mysqli_num_rows($tasks_result) > 0): ?>
                    <?php while ($task = mysqli_fetch_assoc($tasks_result)): ?>
                        <div class="assignment-card">
                            <p><?php echo htmlspecialchars($task['task']); ?></p>
                            <span class="status"><?php echo $task['created_at']; ?></span>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No tasks yet. Add a task!</p>
                <?php endif; ?>
            </section>
        </div>
    </div>

    <script src="./homepagescript.js"></script>
    <script>
        // Toggle the new task form
        document.getElementById('newTaskBtn').addEventListener('click', function() {
            const form = document.querySelector('.new-task-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });
    </script>
</body>
</html>
