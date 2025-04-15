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

// Handle expense addition
// Handle expense addition
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    $description = $_POST['description'];
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $user_id = $_SESSION['user_id']; // Assuming the user ID is stored in the session

    // Using prepared statements to securely insert data
    $stmt = $conn->prepare("INSERT INTO expenses (user_id, description, category, amount) VALUES (?, ?, ?, ?)");

    // Check if $amount should be 'i' or 'd' depending on its data type (integer or float)
    $stmt->bind_param("issd", $user_id, $description, $category, $amount);

    if ($stmt->execute()) {
        // Redirect to the same page to avoid form resubmission
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Handle error (optional)
        echo "Error adding expense: " . $stmt->error;
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

// Handle expenses deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_expenses'])) {
    $expenses_id = $_POST['expenses_id'];
    $user_id = $_POST['user_id'];

    // Prepare the SQL statement to delete the task
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $expenses_id, $user_id);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to the same page to avoid form resubmission
        header("Location: expenses.php");
        exit();
    } else {
        echo "Error deleting task: " . $conn->error;
    }

    $stmt->close();
}

// Fetch expenses for the logged-in user
$expense_query = "SELECT * FROM expenses WHERE user_id = '$user_id' ORDER BY created_at DESC";
$expense_result = mysqli_query($conn, $expense_query);


// Fetch category totals for the graph
$category_totals_query = "SELECT category, SUM(amount) as total 
                         FROM expenses 
                         WHERE user_id = '$user_id' 
                         GROUP BY category";
$category_totals_result = mysqli_query($conn, $category_totals_query);

// Fetch category totals and create JSON - keep this part
$category_data = array();
while ($row = mysqli_fetch_assoc($category_totals_result)) {
    $category_data[] = array(
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



?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses - EffortEase</title>
    <link rel="stylesheet" href="./expenses.css">

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

    <div class="main-content">
        <div class="header">
            <div class="left">
            <div class="project-title">
                <h1>Expense Tracker
                </h1>
            </div>

            <div class="project-subtitle">Illuminate Your Financial Journey with Intelligence, Integrity, and Originality for a Brighter Tomorrow.</div>

            <div class="header-actions">
                <div class="button-types">
                    <button onclick="openModal()" class="add-button">+ Add expenses</button>
                    <!-- Trigger button to open modal -->

                    <button class="more-button"><img
                            src="https://img.icons8.com/?size=100&id=61873&format=png&color=000000" class="more" alt=""
                            srcset=""></button>
                </div>
                
            </div>
            </div>
            <div class="right">
                    <div class="bar-chart" id="expenseChart"></div>
            </div>
        </div>

        <div class="separator"></div>

        <div class="board-section">
            <!-- <div class="section-header">
                <h2>Wireframe</h2>
                <button class="add-column">+ Add column</button>
            </div> -->

            <table class="board-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (mysqli_num_rows($expense_result) > 0): ?>
                    <?php while ($expense = mysqli_fetch_assoc($expense_result)): ?>
                    <tr>
                    <tr>
                        <td>
                            <div class="folder">
                            <?php echo htmlspecialchars($expense['description']); ?>
                            </div>
                        </td>
                        <td><span class="status-badge <?php echo htmlspecialchars($expense['category']); ?> "><?php echo htmlspecialchars($expense['category']); ?></span></td>
                        <td>
                        <?php echo htmlspecialchars($expense['created_at']); ?>
                        </td>
                        <td>
                        <?php echo htmlspecialchars($expense['amount']); ?>
                        </td>
                        <td>
                        <div class="delete">
                        <div class="deleteIcon">
                            <form method="POST" action="expenses.php">
                                <input type="hidden" name="expenses_id" value="<?php echo $expense['id']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
                                <button type="submit" name="delete_expenses" class="deleteBtn">
                                    <img src="../assets/images/delete.png" alt="Delete Expenses" height="20px" width="20px" alt="Delete Expenses">
                                </button>
                            </form>
                        </div>
                    </div>
                    </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php else: ?>
                    <!-- <p>No expenses yet. Great Job!</p> -->
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Modal overlay and content -->
    <div class="modal-overlay" id="modalOverlay">
        <div class="modal">
            <form method="post" action="">
                <button class="close-button" onclick="closeModal()">×</button>
                <h1>Add New Expense</h1>
                <p class="subtitle">Fill in the details below to create your expense.</p>
                <div class="separator"></div>
                <label class="input-label">Expense Description *</label>
                <input type="text" name="description" class="input"
                    placeholder="E.g., Shankhamul-Sinamangal" required>
                    <label class="input-label">Category *</label>
                    <select id="category" name="category"  required>
        <option value="Food">Food and Drinks</option>
        <option value="Transportation">Transportation</option>
        <option value="Entertainment">Entertainment</option>
        <option value="Utilities">Utilities</option>
        <!-- Add more categories as needed -->
    </select>
                    <label class="input-label">Amount *</label>
                <input type="text" name="amount" class="input"
                    placeholder="E.g., $20" required>
                <button type="submit" name="add_expense" class="reset-button">Add</button>
            </form>
        </div>
    </div>

    <script>
    // Make category data available to JavaScript
    const categoryData = <?php echo json_encode($category_data); ?>;
</script>
<script src="./expenses.js"></script> 
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