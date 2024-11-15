<?php
include '../includes/connect.php';

$message = "";
$toastClass = "";

// Check if token is passed in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Step 2: Check if token exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Step 3: Token is valid, proceed with password update if form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $newPassword = $_POST['password'];

            // Hash the new password for security
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
         

            // Update the password in the database
            $updateStmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
            $updateStmt->bind_param("ss", $hashedPassword, $token);

            if ($updateStmt->execute()) {
                $message = "Password updated successfully!";
                $toastClass = "success"; // Success color
                echo '<script>
            setTimeout(function() {
                window.location.href = "login.php";
            }, 3000); // 3-second delay before redirecting
          </script>';
            } else {
                $message = "Error: " . $updateStmt->error;
                $toastClass = "danger"; // Danger color
            }

            $updateStmt->close();
        }
    } else {
        $message = "Invalid token.";
        $toastClass = "danger"; // Danger color
    }

    $stmt->close();
} else {
    // If no token is passed
    $message = "Invalid request.";
    $toastClass = "danger"; // Danger color
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/register.css">
    <title>Reset Password</title>
    <style>
        /* Custom Toast Styles */
        .custom-toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #333;
            color: #fff;
            padding: 10px 15px;
            border-radius: 5px;
            max-width: 300px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            opacity: 1;
            transition: opacity 0.5s ease;
            z-index: 1000;
            font-size: 14px;
            font-weight: 500;
        }

        .custom-toast.primary {
            background-color: #007bff;
        }

        .custom-toast.success {
            background-color: #28a745;
        }

        .custom-toast.danger {
            background-color: #dc3545;
        }

        .close-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            margin-left: 5px;
        }
    </style>
</head>

<body>

<?php if ($message): ?>
    <div id="toast" class="custom-toast <?php echo $toastClass; ?>">
        <div class="custom-toast-content">
            <span><?php echo $message; ?></span>
            <button class="close-btn" onclick="closeToast()">x</button>
        </div>
    </div>
<?php endif; ?>

<section class="register-container">
    <div class="register-inputs">
        <h1>Gratafy</h1>
        <form action="" method="post">
            <div class="password">
                <input name="password" id="password" placeholder="New Password" type="password" required />
                <img src="../assets/images/view.png" alt="view-btn" class="view" id="view">
            </div>
            <div class="password">
                <input name="password" id="confirmPassword" placeholder="Confirm Password" type="password" required />
                <img src="../assets/images/view.png" alt="view-btn" class="view2" id="view2">
            </div>
           <a href="./login.php"><button type="submit" >Update</button></a>
        </form>
    </div>
</section>

<script>
    // Auto-hide the toast after 3 seconds
    function closeToast() {
        const toast = document.getElementById('toast');
        if (toast) {
            toast.style.opacity = '0'; // Fade out
            setTimeout(() => {
                toast.style.display = 'none';
            }, 500); // Wait for the fade-out transition to complete
        }
    }

    // Show the toast and set it to auto-close
    window.onload = () => {
        setTimeout(closeToast, 3000); // Automatically close after 3 seconds
    };

    const view = document.getElementById('view');
    const password = document.getElementById('password');
    const view2 = document.getElementById('view2');
    const password2 = document.getElementById('password2');
    const confirmPassword = document.getElementById('confirmPassword');

    password.addEventListener('input', () => {
        // Check if the password input is not empty
        if (password.value.trim() !== "") {
            view.style.display = "block"; // Show the icon
        } else {
            view.style.display = "none"; // Hide the icon
        }
    });

    view.addEventListener('click', () => {
        if (password.type === "password") {
            password.type = "text";
            view.src = "../assets/images/hide.png";
        } else {
            password.type = "password";
            view.src = "../assets/images/view.png";
        }
    });

    confirmPassword.addEventListener('input', () => {
    if (confirmPassword.value.trim() !== "") {
            view2.style.display = "block"; // Show the icon
        } else {
            view2.style.display = "none"; // Hide the icon
        }
    });


    view2.addEventListener('click',()=>{
        if (confirmPassword.type === "password") {
            confirmPassword.type = "text";
            view2.src = "../assets/images/hide.png";
        } else {
            confirmPassword.type = "password";
            view2.src = "../assets/images/view.png";
        }
    });



    confirmPassword.addEventListener('input', () => {
        if (confirmPassword.value !== password.value) {
            confirmPassword.setCustomValidity("Passwords don't match!");
        } else {
            confirmPassword.setCustomValidity("");
        }
    });
</script>

</body>

</html>
