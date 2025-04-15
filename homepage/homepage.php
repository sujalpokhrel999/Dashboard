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
$query = mysqli_query($conn, "SELECT username, id, profile_image FROM users WHERE email='$email'");

if ($query && mysqli_num_rows($query) > 0) {
    $row = mysqli_fetch_assoc($query);
    $username = $row['username'];
    $user_id = $row['id'];
    $profile_image =$row['profile_image'];
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

// Calculate total tasks
$total_query = "SELECT COUNT(*) as total FROM tasks WHERE user_id = '$user_id'";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_tasks = $total_row['total'] ?? 0;

// Get latest task date if any tasks exist
$latest_date_query = "SELECT created_at 
                      FROM tasks 
                      WHERE user_id = '$user_id' 
                      ORDER BY created_at DESC 
                      LIMIT 1";
$latest_date_result = mysqli_query($conn, $latest_date_query);
$latest_date = mysqli_fetch_assoc($latest_date_result);
$display_date = $latest_date ? date('j F', strtotime($latest_date['created_at'])) : date('j F');




// Calculate total expenses
$total_query = "SELECT SUM(amount) as total FROM expenses WHERE user_id = '$user_id'";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_expenses = $total_row['total'] ?? 0;

// Get expenses for the chart (last 6 entries)
$chart_query = "SELECT amount, created_at 
                FROM expenses 
                WHERE user_id = '$user_id' 
                ORDER BY created_at DESC 
                LIMIT 6";
$chart_result = mysqli_query($conn, $chart_query);

// Process data for Chart.js
$expenses_data = array();
$dates = array();
while ($row = mysqli_fetch_assoc($chart_result)) {
    $expenses_data[] = floatval($row['amount']);
    $dates[] = date('M d', strtotime($row['created_at']));
}
$expenses_data = array_reverse($expenses_data); // Show oldest to newest
$dates = array_reverse($dates);



// Fetch income data for the chart (last 6 entries)
$income_query = "SELECT amount, created_at 
                 FROM income 
                 WHERE user_id = '$user_id' 
                 ORDER BY created_at DESC 
                 LIMIT 6";
$income_result = mysqli_query($conn, $income_query);

// Process data for Chart.js
$income_data = array();
while ($row = mysqli_fetch_assoc($income_result)) {
    $income_data[] = floatval($row['amount']);
}
$income_data = array_reverse($income_data); // Show oldest to newest

// Calculate total expenses
$total_query = "SELECT SUM(amount) as total FROM expenses WHERE user_id = '$user_id'";
$total_result = mysqli_query($conn, $total_query);
$total_row = mysqli_fetch_assoc($total_result);
$total_expenses = $total_row['total'] ?? 0;

// Get expenses for the chart (last 6 entries)
$chart_query = "SELECT amount, created_at 
                FROM expenses 
                WHERE user_id = '$user_id' 
                ORDER BY created_at DESC 
                LIMIT 6";
$chart_result = mysqli_query($conn, $chart_query);

// Process data for SVG path
$expenses_data = array();
$dates = array();
while ($row = mysqli_fetch_assoc($chart_result)) {
    $expenses_data[] = floatval($row['amount']);
    $dates[] = date('M d', strtotime($row['created_at']));
}
$expenses_data = array_reverse($expenses_data); // Show oldest to newest
$dates = array_reverse($dates);

// Calculate SVG dimensions and scales
$svg_width = 200;
$svg_height = 60;
$padding = 20; // Padding for scales
$chart_width = $svg_width - $padding;
$chart_height = $svg_height - $padding;

// Only create SVG if there are expenses
$has_expenses = !empty($expenses_data);
if ($has_expenses) {
    $data_count = count($expenses_data);
    if ($data_count > 1) {
        $max_amount = max($expenses_data);
        $min_amount = min($expenses_data);
        $range = $max_amount - $min_amount;

        // Create scale markers (3 points)
        $scale_points = array(
            $max_amount,
            $max_amount / 2,
            0
        );

        // Calculate SVG path points
        $svg_points = "";
        $x_interval = $chart_width / ($data_count - 1);

        foreach ($expenses_data as $index => $amount) {
            $x = $padding + ($index * $x_interval);
            $y = $chart_height - (($amount / $max_amount) * $chart_height) + $padding / 2;
            if ($index === 0) {
                $svg_points .= "M$x,$y ";
            } else {
                $prev_x = $padding + (($index - 1) * $x_interval);
                $control_x = ($prev_x + $x) / 2;
                $svg_points .= "S$control_x,$y $x,$y ";
            }
        }
    } else {
        // Handle the case with only one expense
        $single_amount = $expenses_data[0];
        $x = $padding;
        $y = $chart_height - (($single_amount / $single_amount) * $chart_height) + $padding / 2;
        $svg_points = "M$x,$y L" . ($chart_width + $padding) . ",$y";
    }
}




// Fetch income data for the logged-in user
$income_query = "SELECT SUM(amount) as total_income FROM income WHERE user_id = '$user_id'";
$income_result = mysqli_query($conn, $income_query);
$total_income = 0;
if ($income_result && mysqli_num_rows($income_result) > 0) {
    $row = mysqli_fetch_assoc($income_result);
    $total_income = $row['total_income'];
}

// Fetch previous income to calculate the percentage change (if applicable)
$previous_income_query = "SELECT SUM(amount) as previous_income FROM income WHERE user_id = '$user_id' AND created_at < NOW() - INTERVAL 1 MONTH";
$previous_income_result = mysqli_query($conn, $previous_income_query);
$previous_income = 0;
if ($previous_income_result && mysqli_num_rows($previous_income_result) > 0) {
    $row = mysqli_fetch_assoc($previous_income_result);
    $previous_income = $row['previous_income'];
}

// Calculate the percentage change
$percentage_change = 0;
if ($previous_income > 0) {
    $percentage_change = (($total_income - $previous_income) / $previous_income) * 100;
} else {
    $percentage_change = 100; // If no previous income, show 100% change.
}

// Calculate the breakdown of percentages (for demo purposes, we’ll assume they are static for now)
$percentage_1 = 15; // Example: 15%
$percentage_2 = 21; // Example: 21%
$percentage_3 = 32; // Example: 32%

// Get income for the chart (last 6 entries)
$income_query = "SELECT amount, created_at 
                 FROM income 
                 WHERE user_id = '$user_id' 
                 ORDER BY created_at DESC 
                 LIMIT 6";
$income_result = mysqli_query($conn, $income_query);

// Process data for Chart.js
$income_data = array();
$income_dates = array();
while ($row = mysqli_fetch_assoc($income_result)) {
    $income_data[] = floatval($row['amount']);
    $income_dates[] = date('M d', strtotime($row['created_at']));
}
$income_data = array_reverse($income_data); // Show oldest to newest
$income_dates = array_reverse($income_dates);

// Check if income data is available
if (empty($income_data)) {
} else {
    // Generate SVG path for income data
    $svg_points_income = "";
    $max_income = max($income_data);
    $min_income = min($income_data);
    $income_range = $max_income - $min_income;

    $svg_width = 170;
    $svg_height = 70;
    $padding = 20;
    $chart_width = $svg_width - $padding;
    $chart_height = $svg_height - $padding;

    $data_count_income = count($income_data);
    if ($data_count_income > 1) {
        $x_interval = $chart_width / ($data_count_income - 1);

        foreach ($income_data as $index => $amount) {
            $x = $padding + ($index * $x_interval);
            $y = $chart_height - (($amount / $max_income) * $chart_height) + $padding / 2;
            if ($index === 0) {
                $svg_points_income .= "M$x,$y ";
            } else {
                $prev_x = $padding + (($index - 1) * $x_interval);
                $control_x = ($prev_x + $x) / 2;
                $svg_points_income .= "S$control_x,$y $x,$y ";
            }
        }
    } else {
        // Handle the case with only one income entry
        $single_income = $income_data[0];
        $x = $padding;
        $y = $chart_height - (($single_income / $single_income) * $chart_height) + $padding / 2;
        $svg_points_income = "M$x,$y L" . ($chart_width + $padding) . ",$y";
    }

    // You can now use $svg_points_income to render your SVG chart if data is available
}



// Get the current date
$currentDate = new DateTime();
$todaysDate = $currentDate->format('j F'); // '3 March'


// Subtract one month to get the previous month
$currentDate->modify('-1 month');

// Format the previous month as abbreviated month (e.g., 'Jan')
$previousMonth = $currentDate->format('M'); // 'Jan'


// Get the current date (today)
$today = new DateTime();
$todayFormatted = $today->format('M'); // 'Feb'
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

    <!-- Include Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            </div>
            <div class="flex items-center gap-4">
                <!-- <button class=" w-10 h-10 text-white p-2 rounded-full notification">
                    <img width="20" height="20" src="https://img.icons8.com/puffy/20/appointment-reminders.png" alt="appointment-reminders" style="filter: brightness(0) invert(1);"/>
                </button> -->
                <a href="../profile/profile.php"> 
                    <div class="w-10 h-10 bg-gray-200 rounded-full"> 
                        <img class='profileImg' src="<?php echo $profile_image ?>" alt=""> 
                    </div>
                </a>
            </div>
        </header>

        <div class="grid grid-cols-4 gap-6 mb-8">


            <!-- Total Tasks Card -->
            <div class="card bg-white rounded-lg shadow-sm p-4">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="font-bold mb-2">Total Tasks</h3>
                        <div class="text-sm text-gray-500"><?php echo $todaysDate; ?></div>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <div class="text-3xl font-bold"><?php echo number_format($total_tasks); ?></div>
                    <?php if ($total_tasks > 0): ?>
                    <?php else: ?>
                        <div class="text-sm text-gray-500">No tasks yet</div>
                    <?php endif; ?>
                </div>
            </div>

        <!-- Expenses Card -->
<div class="card">
    <div class="flex justify-between items-start mb-4">
        <h3 class="font-bold">Expenses</h3>
        <div class="text-red-400">
            <div class="text-l font-bold">$<?php echo number_format($total_expenses, 2); ?></div>
        </div>
    </div>

    <!-- SVG Expenses Chart or No Data Message -->
    <div class="svg-container">
        <?php if (empty($expenses_data)): ?>
            <!-- If no expenses data, display the message -->
            <div class="no-data-message text-center text-gray-500">
                No expenses data available
            </div>
        <?php else: ?>
            <!-- SVG for expenses data -->
            <svg viewBox="0 0 200 60" class="mb-0">
                <!-- X-axis (Time Scale from Previous Month to Today) -->
                <line x1="0" y1="60" x2="220" y2="60" stroke="#FFF" stroke-width="1" />

                <!-- Time labels (Previous Month to Today) -->
                <text x="10" y="60" text-anchor="middle" class="text-xs"><?php echo $previousMonth; ?></text>
                <text x="185" y="60" text-anchor="middle" class="text-xs"><?php echo $todayFormatted; ?></text>

                <!-- Expenses Chart Line -->
                <path d="<?php echo $svg_points; ?>" class="chart-line"/>
            </svg>
        <?php endif; ?>
    </div>
</div>


<!-- Income card -->
<!-- Income Card -->
<div class="card">
    <div class="flex justify-between items-start mb-4">
        <h3 class="font-bold">Income</h3>
        <div class="text-green-600">
            <div class="text-l font-bold">
                $<?php echo number_format($total_income, 2); ?>
            </div>
        </div>
    </div>

    <!-- SVG Income Chart or No Data Message -->
    <div class="svg-container">
        <?php if (empty($income_data)): ?>
            <!-- If no income data, display the message -->
            <div class="no-data-message text-center text-gray-500">
                No income data available
            </div>
        <?php else: ?>
            <!-- SVG for income data -->
            <svg viewBox="0 0 200 60" class="mb-0">
                <!-- X-axis (Time Scale from Previous Month to Today) -->
                <line x1="0" y1="60" x2="220" y2="60" stroke="#FFF" stroke-width="1" />

                <!-- Time labels (Previous Month to Today) -->
                <text x="10" y="60" text-anchor="middle" class="text-xs"><?php echo $previousMonth; ?></text>
                <text x="185" y="60" text-anchor="middle" class="text-xs"><?php echo $todayFormatted; ?></text>

                <!-- Income Chart Line -->
                <path d="<?php echo $svg_points_income; ?>" class="chart-line"/>
            </svg>
        <?php endif; ?>
    </div>
</div>



  <!-- Random Quote Card -->
<div class="card">
    <div class="flex justify-between items-start">
        <div class="text-l font-bold">Quote</div>
    </div>
    <div class="">
        <p class="text-base italic text-gray-700">"The only way to do great work is to love what you do."</p>
        <div class="mt-2 text-sm text-gray-500">- Steve Jobs</div>
    </div>
</div>



</div>
 







        <div class="grid grid-cols-4 gap-6 mb-8">
                    <!-- Chart.js Line Graph -->
                    <div class="col-span-2">
                <canvas id="expensesChart" class="mb-8"></canvas>
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

    <script src="./homepagescript.js"></script>
    <script>
        const body = document.querySelector('body'),
            sidebar = body.querySelector('nav'),
            searchBtn = body.querySelector(".search-box"),
            modeSwitch = body.querySelector(".toggle-switch"),
            modeText = body.querySelector(".mode-text");

        modeSwitch.addEventListener("click", () => {
            body.classList.toggle("dark");
            
            if (body.classList.contains("dark")) {
                modeText.innerText = "Light mode";
            } else {
                modeText.innerText = "Dark mode";
            }
        });
        const ctx = document.getElementById('expensesChart').getContext('2d');
const expensesChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($dates); ?>,
        datasets: [{
            label: 'Expenses Over Time',
            data: <?php echo json_encode($expenses_data); ?>,
            fill: false,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }, {
            label: 'Income Over Time',
            data: <?php echo json_encode($income_data); ?>,
            fill: false,
            borderColor: 'rgb(54, 162, 235)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(tooltipItem) {
                        return '$' + tooltipItem.raw.toFixed(2);
                    }
                }
            }
        }
    }
});

    </script>
</body>

</html>
