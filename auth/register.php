<?php
include '../includes/connect.php';

$message = "";
$toastClass = "";
$username="";
$number="";
$address="";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $number =$_POST['phone'];
    $address =$_POST['address'];
    $profile_image = '../assets/images/default-profile.png';

      // Hash the password
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if email already exists
    $checkEmailStmt = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmailStmt->bind_param("s", $email);
    $checkEmailStmt->execute();
    $checkEmailStmt->store_result();

    if ($checkEmailStmt->num_rows > 0) {
        $message = "Email ID already exists";
        $toastClass = "primary"; // Primary color
    } else {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO users (username, email, password,Number,Address,profile_image) VALUES (?, ?, ?,?,?,?)");
        $stmt->bind_param("sssiss", $username, $email, $hashedPassword,$number,$address,$profile_image);

        if ($stmt->execute()) {
            $message = "Account created successfully";
            $toastClass = "success"; // Success color
            $username="";
            $number="";
            $address="";
            header("Location: ./login.php");
        } else {
            $message = "Error: " . $stmt->error;
            $toastClass = "danger"; // Danger color
        }

        $stmt->close();
    }
  
    $checkEmailStmt->close();
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


        /* /Tool tippp */
        .tooltip {
      position: absolute;
      top: 100%;
      left: 0;
      margin-top: 5px;
      background-color: #fff;
      color: #333;
      border: 1px solid #ccc;
      border-radius: 5px;
      padding: 10px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      font-size: 14px;
      width: 100%;
      display: none;
    }
    .tooltip::before {
      content: "";
      position: absolute;
      top: -5px;
      left: 20px;
      border-width: 5px;
      border-style: solid;
      border-color: transparent transparent #ccc transparent;
    }
    .tooltip::after {
      content: "";
      position: absolute;
      top: -4px;
      left: 20px;
      border-width: 4px;
      border-style: solid;
      border-color: transparent transparent #fff transparent;
    }
    .tooltip.show {
      display: block;
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
            <input name="username" id="username" placeholder="Full Name" type="text" required value="<?php echo htmlspecialchars($username); ?>" />



            <input name="email" id="email" placeholder="Example@gmail.com" type="email" required minlength="5" 
  maxlength="50" 
  pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"  />
  <input 
    name="phone" 
    id="phone" 
    placeholder="Phone Number" 
    minlength="10" 
    type="tel" 
    pattern="^\d{10}$" 
    required 
    value="<?php echo htmlspecialchars($number); ?>" 
/>
            <input name="address" id="address" placeholder="Address" type="text" required value="<?php echo htmlspecialchars($address); ?>" />
          <div class="password"> 
      
                        <input 
                          oninput="checkPassword()"
                          name="password" 
                          placeholder="Password"
                          type="password" 
                          id="password" required 
                          minlength="8" 
                          maxlength="20" 
                          pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).{8,}" />
              <img src="../assets/images/view.png" alt="view-btn" class="view" id="viewPassword">
              <div class="tooltip" id="tooltip"></div>
          </div>

          <div class="confirmpassword password2">
              <input name="confirm_password"  id="confirmpassword" placeholder="Confirm Password" type="password" required minlength="8"/>
             <img src="../assets/images/view.png" alt="view-btn" class="view" id="viewConfirmPassword">
          </div>

            <button type="submit">Register</button>
            
        </form>
        <a class="forget" href="./login.php">Already have an account? Sign in</a>
    </div>
</section>
<script>
    function checkPassword() {
      const password = document.getElementById("password").value;
      const tooltip = document.getElementById("tooltip");

      const hasUpperCase = /[A-Z]/.test(password);
      const hasLowerCase = /[a-z]/.test(password);
      const hasNumber = /[0-9]/.test(password);
      const hasSymbol = /[^A-Za-z0-9]/.test(password);
      const len = password.length;

      let message = "";

      if(len<8){
          message += "• Password should be 8 characters long.<br>"
      }

      if (!hasUpperCase) {
        message += "• At least one uppercase letter.<br>";
      }
      if (!hasLowerCase) {
        message += "• At least one lowercase letter.<br>";
      }
      if (!hasNumber) {
        message += "• At least one number.<br>";
      }
      if (!hasSymbol) {
        message += "• At least one symbol (e.g., @, #, $, etc.).<br>";
      }

      if (message) {
        tooltip.innerHTML = message;
        tooltip.classList.add("show");
      } else {
        tooltip.classList.remove("show");
      }
    }
    // Hide the tooltip when the password input loses focus
const passwordInput = document.getElementById("password");
passwordInput.addEventListener("blur", () => {
  const tooltip = document.getElementById("tooltip");
  tooltip.classList.remove("show");
});
  </script>


<script>
const password = document.getElementById("password");
const confirmpassword = document.getElementById("confirmpassword");


confirmpassword.addEventListener("input", () => {
  if (confirmpassword.value.trim() !== "") {
        viewConfirmPassword.style.display = "block"; // Show the icon
    } else {
        viewConfirmPassword.style.display = "none"; // Hide the icon
    }


  if (password.value === "" && confirmpassword.value === "") {
    confirmpassword.style.border = ""; 
  } else if (confirmpassword.value === password.value) {
    confirmpassword.style.border = "1px solid green";
  } else {
    confirmpassword.style.border = "1px solid red";
  }
});



const viewPassword = document.getElementById('viewPassword');
const viewConfirmPassword = document.getElementById('viewConfirmPassword');


// Show/hide password for the first input
viewPassword.addEventListener('click', () => {
  console.log("clicked");
  if (password.type === "password") {
      password.type = "text";
      viewPassword.src = "../assets/images/hide.png";
  } else {
      password.type = "password";
      viewPassword.src = "../assets/images/view.png";
  }
});

// Show/hide password for the confirm password input
viewConfirmPassword.addEventListener('click', () => {
  if (confirmpassword.type === "password") {
      confirmpassword.type = "text";
      viewConfirmPassword.src = "../assets/images/hide.png";
  } else {
      confirmpassword.type = "password";
      viewConfirmPassword.src = "../assets/images/view.png";
  }
});


    password.addEventListener('input', () => {
    // Check if the password input is not empty
    if (password.value.trim() !== "") {
        viewPassword.style.display = "block"; // Show the icon
    } else {
        viewPassword.style.display = "none"; // Hide the icon
    }
});
</script>

<script>
    // Auto-hide the toast after 3 seconds
    function closeToast() {
        const toast = document.getElementById('toast');
        if (toast) {
            toast.style.opacity = '0'; // Fade out
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000); // Wait for the fade-out transition to complete
        }
    }

    // Show the toast and set it to auto-close
    window.onload = () => {
        setTimeout(closeToast, 3000); // Automatically close after 3 seconds
    };

    </script>

<script>

</script>


</body>

</html>
