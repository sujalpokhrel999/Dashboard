
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


// Fetch expenses for the logged-in user
$expense_query = "SELECT * FROM expenses WHERE user_id = '$user_id' ORDER BY created_at DESC";
$expense_result = mysqli_query($conn, $expense_query);


// Fetch category totals for expenses
$category_totals_query = "SELECT category, SUM(amount) as total 
                         FROM expenses 
                         WHERE user_id = '$user_id' 
                         GROUP BY category";
$category_totals_result = mysqli_query($conn, $category_totals_query);




// Fetch income for the logged-in user
$income_query = "SELECT * FROM income WHERE user_id= '$user_id' ORDER BY created_at DESC ";
$income_result = mysqli_query($conn, $income_query);


// Fetch category totals for income
$category2_totals_query = "SELECT category, SUM(amount) as total 
                         FROM income 
                         WHERE user_id = '$user_id' 
                         GROUP BY category";
$category2_totals_result = mysqli_query($conn, $category2_totals_query);





// Prepare JSON for expense categories
$category_data = array();
while ($row = mysqli_fetch_assoc($category_totals_result)) {
    $category_data[] = array(
        'category' => $row['category'],
        'total' => floatval($row['total'])
    );
}

// Prepare JSON for income categories
$category_data2 = array();
while ($row = mysqli_fetch_assoc($category2_totals_result)) {
    $category_data2[] = array(
        'category' => $row['category'],
        'total' => floatval($row['total'])
    );
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

// Query to get both total expense and total income in one go
$sum_query = "
    SELECT 
        (SELECT SUM(amount) FROM expenses WHERE user_id = '$user_id') AS total_expense,
        (SELECT SUM(amount) FROM income WHERE user_id = '$user_id') AS total_income
";
$result = mysqli_query($conn, $sum_query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    
    // Get the total expense and income
    $total_expense = $row['total_expense'];
    $total_income = $row['total_income'];

    // Format both values to 2 decimal places
    $total_expense = number_format($total_expense, 2, '.', ''); // '2' decimal places
    $total_income = number_format($total_income, 2, '.', ''); // '2' decimal places
} else {
    // Handle the case where no results are returned
    $total_expense = 0.00;
    $total_income = 0.00;
}



?>


<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="./income.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
  <title>Finance - EffortEase</title>
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
   
            <style>
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .close-btn {
            border: none;
            background: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }

        input, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .radio-group {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .radio-option input[type="radio"] {
            width: auto;
        }

        .submit-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .submit-btn:hover {
            background-color: #45a049;
        }

        /* Show modal when active class is added */
        .modal-overlay.active {
            display: flex;
        }
        </style>
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
  <div class="dashboard">
    <div class="header">
      <h1 class="title">Financial Dashboard</h1>
    </div>

   
    

    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-header">
          <div>
            <p class="stat-title">Total Income</p>
            <h2 class="stat-value"><?php echo $total_income; ?></h2>
          </div>
          <div class="stat-icon income-icon">↑</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div>
            <p class="stat-title">Total Expenses</p>
            <h2 class="stat-value"><?php  echo $total_expense;?></h2>
          </div>
          <div class="stat-icon expense-icon">↓</div>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-header">
          <div>
            <p class="stat-title">Net Balance</p>
            <h2 class="stat-value"><?php  echo ($total_income-$total_expense );?></h2>
          </div>
          <div class="stat-icon balance-icon">$</div>
        </div>
      </div>
    </div>

    <div class="charts-grid">
      <div class="chart-card">
        <h3 class="chart-title">Income Categories</h3>
        <canvas id="incomeCategories"></canvas>
      </div>

      <div class="chart-card">
        <h3 class="chart-title">Expense Categories</h3>
        <canvas id="expenseCategories"></canvas>
      </div>
    </div>

    <section class="mb-8">
            <div class="grid grid-cols-4 gap-6">
                <!-- Recent Activity Card -->
                <div class="card col-span-4">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="font-bold">Recent Activity</h3>
                        <div class="text-gray-400">Last 5 activities</div>
                    </div>
                    <div class="space-y-4">
    <?php
    // Fetch recent activities (both tasks, expenses, and income)
    $recent_query = "
        (SELECT 'task' as type, task as description, created_at, NULL as amount, NULL as category 
        FROM tasks 
        WHERE user_id = '$user_id')
        UNION ALL
        (SELECT 'expense' as type, description, created_at, amount, NULL as category 
        FROM expenses 
        WHERE user_id = '$user_id')
        UNION ALL
        (SELECT 'income' as type, category as description, created_at, amount, category 
        FROM income 
        WHERE user_id = '$user_id')
        ORDER BY created_at DESC 
        LIMIT 5
    ";
    
    $recent_result = mysqli_query($conn, $recent_query);
    while ($activity = mysqli_fetch_assoc($recent_result)) {
        $icon = $activity['type'] == 'task' ? '📋' : '💰';
        $description = $activity['description'];
        $category = $activity['category'];  // Only used for 'income'
        $time = date('M d, H:i', strtotime($activity['created_at']));
        $amount = $activity['amount'] ? '$' . number_format($activity['amount'], 2) : '';
        
        // Set the amount color based on the type (green for income, red for expenses)
        $amount_class = $activity['type'] == 'income' ? 'text-green-500' : 'text-red-500';
    ?>
        <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
            <div class="flex items-center gap-3">
                <span><?php echo $description; ?></span>
            </div>
            <div class="flex items-center gap-4">
                <?php if ($amount) { ?>
                    <span class="<?php echo $amount_class; ?> font-medium"><?php echo $amount; ?></span>
                <?php } ?>
                <span class="text-sm text-gray-500"><?php echo $time; ?></span>
            </div>
        </div>
    <?php } ?>
</div>


                </div>
            </div>
        </section>
  </div>
  <script>
    // Pass category data to JavaScript
    const incomeCategoryData = <?php echo json_encode($category_data2); ?>;
const expenseCategoryData = <?php echo json_encode($category_data); ?>;
</script>
  <script src="./income.js"></script>
    
  


   
</body>
</html>