<?php
include 'connect.php';
session_start();
$errors = []; // Initialize an array to hold error messages

/**
 * Function to handle user registration.
 */
function handleRegistration($dbConnection) {
    global $errors; // Use the global errors array
    // Collect and sanitize user input
    $fullName = trim($_POST['name']);
    $userEmail = trim($_POST['email']);
    $userPassword = trim($_POST['password']);

    // Validate input fields
    if (empty($fullName)) {
        $errors['name'] = "Name is required.";
    }
    if (empty($userEmail)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($userEmail, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email format.";
    }
    if (empty($userPassword)) {
        $errors['password'] = "Password is required.";
    }

    // If there are errors, return early
    if (!empty($errors)) {
        return;
    }

    // Prepare SQL statement to check if the email already exists
    $checkEmailQuery = "SELECT * FROM users WHERE email = '$userEmail'";
    $checkEmailResult = $dbConnection->query($checkEmailQuery);
    
    // Check if email already exists
    if ($checkEmailResult->num_rows > 0) {
        $errors['email'] = "Email already exists!!!";
    } else {
        // Insert new user
        $insertUserQuery = "INSERT INTO users (name, email, password) VALUES ('$fullName', '$userEmail', '$userPassword')";

        if ($dbConnection->query($insertUserQuery)) {
            // New: Get the inserted user ID
            $userId = $dbConnection->insert_id;
            $userDbName = "user_db_" . $fullName;

            // New: Create a new database for the user
            $createDbQuery = "CREATE DATABASE `$userDbName`";
            if ($dbConnection->query($createDbQuery) === FALSE) {
                die("Error creating database: " . $dbConnection->error);
          
            }
        
            // New: Connect to the new user database and create a sample table
            $userDbConnection = new mysqli('localhost', 'root', '', $userDbName);
            if ($userDbConnection->connect_error) {
                die("Connection failed: " . $userDbConnection->connect_error);
            }
            $createTableQuery = "CREATE TABLE user_data (
                id INT AUTO_INCREMENT PRIMARY KEY,
                data_field1 VARCHAR(255),
                data_field2 TEXT
            )";
            if ($userDbConnection->query($createTableQuery) === FALSE) {
                die("Error creating table: " . $userDbConnection->error);
            }
            
            // Close the connection to the user database
            $userDbConnection->close();

            header("Location: .\auth\index.php"); // Redirect to login page
            exit();
        } else {
            $errors['db'] = "Error: " . $dbConnection->error;
        }
    }
}

/**
 * Function to handle user login.
 */
function handleLogin($dbConnection) {
    global $errors; // Use the global errors array
    // Collect and sanitize user input
    $userEmail = trim($_POST['email']);
    $userPassword = trim($_POST['password']);
    
    // Validate input fields
    if (empty($userEmail)) {
        $errors['email'] = "Email is required.";
    }
    if (empty($userPassword)) {
        $errors['password'] = "Password is required.";
    }

    // If there are errors, return early
    if (!empty($errors)) {
        return;
    }

    $loginQuery = "SELECT * FROM users WHERE email = '$userEmail'";
    $loginResult = $dbConnection->query($loginQuery);
    if ($loginResult->num_rows > 0) {
        $userRecord = $loginResult->fetch_assoc();

        if ($userPassword == $userRecord['password']) {
            // Set session variables
            $_SESSION['email'] = $userRecord['email'];
            $_SESSION['name'] = $userRecord['name']; // Store the name in the session

            // Create the user-specific database name
            $userDbName = "user_db_" . $userRecord['name'];

            // Connect to the user's specific database
            $userDbConnection = new mysqli('localhost', 'root', '', $userDbName);
            if ($userDbConnection->connect_error) {
                die("Connection failed: " . $userDbConnection->connect_error);
            }

            // Example: Get some data from the user's database (can be changed based on your need)
            $checkUserDataQuery = "SELECT * FROM user_data LIMIT 1";
            $userDataResult = $userDbConnection->query($checkUserDataQuery);
            if ($userDataResult->num_rows > 0) {
                // User's database is ready, redirect them to their personalized homepage
                header("location: .\homepage\homepage.php");
                exit();
            } else {
                $errors['db'] = "User-specific database not set up properly!";
            }

            // Close the connection to the user's database
            $userDbConnection->close();
        } else {
            $errors['password'] = "Incorrect Password!";
        }
    } else {
        $errors['email'] = "User not found!";
    }
}

/**
 * Function to handle user logout.
 */
function handleLogout() {
    header("Location: index.php"); // Redirect to login page
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['signUp'])) {
        handleRegistration($conn);
    } elseif (isset($_POST['signIn'])) {
        handleLogin($conn);
    } elseif (isset($_POST['logout'])) {
        handleLogout();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <title>Login Page</title>
    <style>
        .error-message {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container" id="container">
        <!-- Display error messages -->
        <?php if (!empty($errors['db'])) : ?>
            <div class="error-message"><?php echo $errors['db']; ?></div>
        <?php endif; ?>

        <div class="form-container sign-up">
            <form method="post" action="">
                <h1>Create Account</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
                <span>or use your email for registration</span>
                <input type="text" placeholder="Name" name="name" required>
                <?php if (!empty($errors['name'])) : ?>
                    <div class="error-message"><?php echo $errors['name']; ?></div>
                <?php endif; ?>
                <input type="email" placeholder="Email" name="email" required>
                <?php if (!empty($errors['email'])) : ?>
                    <div class="error-message"><?php echo $errors['email']; ?></div>
                <?php endif; ?>
                <input type="password" placeholder="Password" name="password" required>
                <?php if (!empty($errors['password'])) : ?>
                    <div class="error-message"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
                <button type="submit" name="signUp">Sign Up</button>
            </form>
        </div>

        <div class="form-container sign-in">
            <form method="post" action="">
                <h1>Sign In</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-github"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-linkedin-in"></i></a>
                </div>
                <span>or use your email password</span>
                <input type="text" name="email" placeholder="Email" required>
                <?php if (!empty($errors['email'])) : ?>
                    <div class="error-message"><?php echo $errors['email']; ?></div>
                <?php endif; ?>
                <input type="password" name="password" placeholder="Password" >
                <?php if (!empty($errors['password'])) : ?>
                    <div class="error-message"><?php echo $errors['password']; ?></div>
                <?php endif; ?>
                <a href="#">Forget Your Password?</a>
                <button type="submit" name="signIn">Sign In</button>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Hello, Friend!</h1>
                    <p>Enter your personal details to use all of site features</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Welcome Back!</h1>
                    <p>Register with your personal details to use all of site features</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
