<?php
require 'E:/xampp/htdocs/dashboard/Dashboard/includes/src/PHPMailer.php';
require 'E:/xampp/htdocs/dashboard/Dashboard/includes/src/SMTP.php';
require 'E:/xampp/htdocs/dashboard/Dashboard/includes/src/Exception.php';



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
$message='';
$toastClass='';
include '../includes/connect.php';
// forgot_password.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // Database connection

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(16));

        // Save the token to the database
        $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();

        // Prepare the reset link
        $resetLink = "http://localhost/dashboard/Dashboard/auth/ResetPassword.php?token=" . $token;


        // Create a PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Use your mail server, for example, Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'sujal3pokhrel@gmail.com';  // Your email address
            $mail->Password = 'shoz flno oasp yvrr';  // Your email password or app-specific password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;  // SMTP port

            // Recipients
            $mail->setFrom('sujal3pokhrel@gmail.com', 'Gratafy');  // From email and name
            $mail->addAddress($email);  // Recipient's email address

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "Click the link to reset your password: <a href='$resetLink'>$resetLink</a>";

            // Send the email
            $mail->send();
            $message= "An email has been sent to your address with a reset link.";
            $toastClass = "success";

        } catch (Exception $e) {
            echo "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $message= "Email address not found.";
        $toastClass="danger";
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/register.css">
    <title>Registration</title>
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
        .custom-toast.primary { background-color: #007bff; }
        .custom-toast.success { background-color: #28a745; }
        .custom-toast.danger { background-color: #dc3545; }

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

<section class="register-container">
    <div class="register-inputs">
        <h1>Gratafy</h1>
        <form id="forgotPasswordForm" method="POST" action="">
            <input name="email" id="email" placeholder="Example@gmail.com" type="email" required />
          
            <button type="submit">Search</button>
        </form>
        <a class="forget" href="./login.php">Already have an account? Sign in</a>
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
