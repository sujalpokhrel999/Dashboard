<?php
include '../includes/connect.php';

$message = "";
$toastClass = "";
$email="";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and execute
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $db_password);
        $stmt->fetch();

        if (password_verify($password, $db_password)) {
            $message = "Login successful";
            $toastClass = "bg-success";
            session_start();
            $_SESSION['user_id'] = $user_id;
            $_SESSION['email'] = $email;
            header("Location: ../homepage/homepage.php");
            exit();
        } else {
            $message = "Incorrect password";
            $toastClass = "bg-danger";
        }
    } else {
        $message = "Email not found";
        $toastClass = "bg-danger";
        $email="";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/login.css">
    <title>Login Page</title>
    <style>
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

        .custom-toast.bg-success { background-color: #28a745; }
        .custom-toast.bg-danger { background-color: #dc3545; }
        .custom-toast.bg-warning { background-color: #ffc107; }

        .close-btn {
            background: none;
            border: none;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            margin-left:5px;
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

<section class="login-container">
    <div class="login-inputs">
        <h1>Gratafy</h1>
        <form action="" method="post">
        <input name="email" id="email" placeholder="Example@gmail.com" type="email" required minlength="5" 
  maxlength="50" 
  pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" value="<?php echo htmlspecialchars($email); ?>" />
          <div class="password"> <input name="password" id="password" placeholder="Password" type="password" id="password" required />
          <img src="../assets/images/view.png" alt="view-btn" class="view" id="view">
          </div>
            <button type="submit">Log in </button>
        </form>
        <div class="SignUp_Forgot">
        <a class="forget" href="./register.php">Sign Up</a>
        <span style="color: #62627d;">/</span>
        <a class="forget forgetPassword" href="./forgot.php">Forgot Your Password? </a>
        </div>
    </div>
</section>

<script>
    // Function to close the toast
    function closeToast() {
        const toast = document.getElementById('toast');
        if (toast) {
            toast.style.opacity = '0';
            setTimeout(() => { toast.style.display = 'none'; }, 3000);
        }
    }

    // Automatically close toast after 3 seconds
    window.onload = () => {
        setTimeout(closeToast, 3000);
    };


    const view = document.getElementById('view'); 
    const password = document.getElementById('password');
    password.addEventListener('input', () => {
    // Check if the password input is not empty
    if (password.value.trim() !== "") {
        view.style.display = "block"; // Show the icon
    } else {
        view.style.display = "none"; // Hide the icon
    }
});

view.addEventListener('click',()=>{
    if(password.type === "password"){
        password.type ="text";
        console.log('pass');
        view.src = "../assets/images/hide.png";

    }else{
        password.type ="password";
        console.log('word');
        view.src = "../assets/images/view.png";
    }
});
</script>
</body>

</html>
