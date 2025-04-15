<?php
include('../includes/connect.php');
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: ../auth/login.php");
    exit();
}

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

// Handle logout
if (isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}

// Handle date filtering
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : date('Y-m-d');

// Fetch expenses data
$expenses_query = "SELECT SUM(amount) as total_expenses, 
                         COUNT(*) as expense_count,
                         category,
                         DATE(created_at) as date
                  FROM expenses 
                  WHERE user_id = '$user_id' 
                  AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
                  GROUP BY category, DATE(created_at)
                  ORDER BY created_at DESC";
$expenses_result = mysqli_query($conn, $expenses_query);


$income_query = "SELECT SUM(amount) as total_income, 
                         COUNT(*) as income_count,
                         category,
                         DATE(created_at) as date
                  FROM income 
                  WHERE user_id = '$user_id' 
                  AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
                  GROUP BY category, DATE(created_at)
                  ORDER BY created_at DESC";
$income_result = mysqli_query($conn, $income_query);

// Fetch tasks data
$tasks_query = "SELECT COUNT(*) as total_tasks,
                       DATE(created_at) as date
                FROM tasks 
                WHERE user_id = '$user_id' 
                AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'
                GROUP BY DATE(created_at)
                ORDER BY created_at DESC";
$tasks_result = mysqli_query($conn, $tasks_query);

// Calculate totals
$total_query = "SELECT 
    (SELECT COUNT(*) FROM tasks WHERE user_id = '$user_id' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date') as total_tasks,
    (SELECT SUM(amount) FROM expenses WHERE user_id = '$user_id' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date') as total_expenses,
    (SELECT SUM(amount) FROM income WHERE user_id = '$user_id' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date') as total_income";
$total_result = mysqli_query($conn, $total_query);
$totals = mysqli_fetch_assoc($total_result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - EffortEase</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="./report.css">
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
                <li>
                    <form method="POST" action="" style="display: flex; border-radius: 6px; width: 100%; height: 100%; align-items: center;">
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
                <h1>Reports & Analytics</h1>
            </div>
            <div class="project-subtitle">
                Track Your Progress, Analyze Your Patterns, and Make Data-Driven Decisions
            </div>
            <div class="header-actions">
                <div class="date-filter">
                    <form method="POST" class="filter-form">
                        <div class="date-inputs">
                            <div class="date-field">
                                <label>Start Date</label>
                                <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="date-field">
                                <label>End Date</label>
                                <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                        </div>
                        <button type="submit" class="generate-button">Generate Report</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="separator"></div>

        <div class="summary-cards">
            <div class="summary-card">
                <div class="card-content">
                    <h3>Total Tasks</h3>
                    <div class="card-value"><?php echo number_format($totals['total_tasks']); ?></div>
                    <div class="card-period"><?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d', strtotime($end_date)); ?></div>
                </div>
            </div>
            <div class="summary-card expenses">
                <div class="card-content">
                    <h3>Total Expenses</h3>
                    <div class="card-value">$<?php echo number_format($totals['total_expenses'], 2); ?></div>
                    <div class="card-period"><?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d', strtotime($end_date)); ?></div>
                </div>
            </div>
            <div class="summary-card expenses">
                <div class="card-content">
                    <h3>Total Income</h3>
                    <div class="card-value">$<?php echo number_format($totals['total_income'], 2); ?></div>
                    <div class="card-period"><?php echo date('M d', strtotime($start_date)); ?> - <?php echo date('M d', strtotime($end_date)); ?></div>
                </div>
            </div>
        </div>
        <div class="board-section">
    <h3>Income & Expenses Report</h3>
    <table class="board-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Type</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $current_date = strtotime($end_date);
            $end = strtotime($start_date);
            
            while ($current_date >= $end) {
                $date = date('Y-m-d', $current_date);
                
                // Get expenses for this date
                $daily_expenses_query = "SELECT SUM(amount) as total, category 
                                      FROM expenses 
                                      WHERE user_id = '$user_id' 
                                      AND DATE(created_at) = '$date' 
                                      GROUP BY category";
                $daily_expenses_result = mysqli_query($conn, $daily_expenses_query);
                
                // Get income for this date
                $daily_income_query = "SELECT SUM(amount) as total, category 
                                    FROM income 
                                    WHERE user_id = '$user_id' 
                                    AND DATE(created_at) = '$date' 
                                    GROUP BY category";
                $daily_income_result = mysqli_query($conn, $daily_income_query);
                
                // Display expenses
                if ($daily_expenses_result && mysqli_num_rows($daily_expenses_result) > 0) {
                    while ($expense = mysqli_fetch_assoc($daily_expenses_result)) {
                        echo "<tr>";
                        echo "<td>" . date('M d, Y', $current_date) . "</td>";
                        echo "<td><span class='status-badge expense " . htmlspecialchars($expense['category']) . "'>" . htmlspecialchars($expense['category']) . "</span></td>";
                        echo "<td><span class='type-badge expense'>Expense</span></td>";
                        echo "<td>-$" . number_format($expense['total'], 2) . "</td>";
                        echo "</tr>";
                    }
                }
                
                // Display income
                if ($daily_income_result && mysqli_num_rows($daily_income_result) > 0) {
                    while ($income = mysqli_fetch_assoc($daily_income_result)) {
                        echo "<tr>";
                        echo "<td>" . date('M d, Y', $current_date) . "</td>";
                        echo "<td><span class='status-badge income " . htmlspecialchars($income['category']) . "'>" . htmlspecialchars($income['category']) . "</span></td>";
                        echo "<td><span class='type-badge income'>Income</span></td>";
                        echo "<td>+$" . number_format($income['total'], 2) . "</td>";
                        echo "</tr>";
                    }
                }
                
                $current_date = strtotime('-1 day', $current_date);
            }
            ?>
        </tbody>
    </table>
</div>

<div class="board-section">
    <h3>Tasks Report</h3>
    <table class="board-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Task Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $current_date = strtotime($end_date);
            $end = strtotime($start_date);
            
            while ($current_date >= $end) {
                $date = date('Y-m-d', $current_date);
                
                // Get tasks for this date
                $daily_tasks_query = "SELECT task, created_at 
                                    FROM tasks 
                                    WHERE user_id = '$user_id' 
                                    AND DATE(created_at) = '$date'";
                $daily_tasks_result = mysqli_query($conn, $daily_tasks_query);
                
                // Display tasks
                if ($daily_tasks_result && mysqli_num_rows($daily_tasks_result) > 0) {
                    while ($task = mysqli_fetch_assoc($daily_tasks_result)) {
                        echo "<tr>";
                        echo "<td>" . date('M d, Y H:i', strtotime($task['created_at'])) . "</td>";
                        echo "<td>" . htmlspecialchars($task['task']) . "</td>";
                        echo "</tr>";
                    }
                }
                
                $current_date = strtotime('-1 day', $current_date);
            }
            ?>
        </tbody>
    </table>
</div>

</div>


    </div>


    
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
    <script src="./report.js"></script>
    </body>
    </html>