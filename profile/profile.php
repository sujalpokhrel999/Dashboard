<?php 

include('../includes/connect.php');


// Start the session at the beginning of the script
session_start();

// Redirect to login if the user is not logged in
if (!isset($_SESSION['email'])) {
    header("Location: ../auth/login.php");
    exit();
}


//UsersDEtails
$message = "";
$toastClass = ""; 
$email = $_SESSION['email'];
$username =  "";
$number="";
$address = "";
$id = $_SESSION['user_id'];


$sql = "SELECT username,Number,Address,profile_image FROM users WHERE id = $id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $username = $row['username'];
    $phone = $row['Number'];
    $address = $row['Address'];
    $profile_image = $row['profile_image'];
} else {
    echo "User not found in the database.";
    echo $email;
    echo $id;
}



// <!-- Logout  -->
if (isset($_POST['logout'])) {
    // Unset all session variables
    session_unset();
    // Destroy the session
    session_destroy();
    // Redirect to the login page
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Replace this with dynamic ID logic, e.g., session or hidden input
    $username = isset($_POST['username']) ? $_POST['username'] : null;
    $phone = isset($_POST['phone']) ? $_POST['phone'] : null;
    $address = isset($_POST['address']) ? $_POST['address'] : null;

    if (isset($_POST['save_changes'])) {
        // Fetch current values from the database
        $result = $conn->query("SELECT * FROM users WHERE id = '$id'");
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Use current values for fields not provided in the form
            $username = $username ?: $user['username'];
            $phone = $phone ?: $user['Number'];
            $address = $address ?: $user['Address'];

            // Update query
            $sql = "UPDATE users SET username = '$username', Number = '$phone', Address = '$address' WHERE id = '$id'";

            if ($conn->query($sql) === TRUE) {
                $message = "Changes saved successfully";
                $toastClass = "success"; 
            } else {
                $message = "Error Saving Changes";
                $toastClass = "danger"; 
            }
        } 
    } elseif (isset($_POST['delete_account'])) {
        // Delete account logic
        $sql = "DELETE FROM users WHERE id = '$id'";

        if ($conn->query($sql) === TRUE) {
            header("Location: ../auth/login.php");
            exit();
        } else {
            $message = "Error deleting account";
                $toastClass = "danger"; 
        }
    } else {
        $message = "Invalid Action";
        $toastClass = "primary"; 
    }
}







// Check if form is submitted
if (isset($_POST['saveUpload'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === 0) {
        $image_name = $_FILES['profile_image']['name'];
        $image_tmp_name = $_FILES['profile_image']['tmp_name'];
        $image_size = $_FILES['profile_image']['size'];
        $image_type = $_FILES['profile_image']['type'];

        // Allowed file types
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($image_type, $allowed_types)) {
            die("Only JPG, PNG, and GIF files are allowed.");
        }

        $target_dir = "../assets/images/";
        $target_file = $target_dir . basename($image_name);

        // Ensure the user ID is set
        if (isset($id)) {
            // Move the uploaded file
            if (move_uploaded_file($image_tmp_name, $target_file)) {
                // Fetch the current profile image
                $sql = "SELECT profile_image FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->bind_result($current_image);
                $stmt->fetch();
                $stmt->close();



                // Update the profile_image column in the database
                $update_sql = "UPDATE users SET profile_image = ? WHERE id = ?";
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("si", $target_file, $id);
                if ($stmt->execute()) {
                    $message = "Profile image updated successfully!";
                    $toastClass = "success";
                    header("Refresh:0");
                } else {
                    $message = "Database update failed.";
                    $toastClass = "error";
                }
                $stmt->close();
            } else {
                $message = "Error moving the uploaded file.";
                $toastClass = "error";
            }
        } else {
            $message = "User ID is not set.";
            $toastClass = "error";
        }
    } else {
        $message = "No file uploaded or there was an error.";
        $toastClass = "error";
    }
}
























?>













<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="profile.css">
    <title>Profile - EffortEase</title>
</head>

<body class="dark">
<?php if ($message): ?>
    <div id="toast" class="custom-toast <?php echo $toastClass; ?>">
        <div class="custom-toast-content">
            <span><?php echo $message; ?></span>
            <button class="close-btn" onclick="closeToast()">x</button>
        </div>
    </div>
    
<?php endif; ?>


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

    <div class="profileSection">
    <div class="container">
        <!-- Profile Header -->
        <header class="profile-header">
            <h1>Profile</h1>
        </header>

        <div class="profile-content">
            <!-- Left Column - Profile Image and Buttons -->
            <div class="profile-left">
    <div class="profile-image-container">
        <form action="" method="POST" enctype="multipart/form-data">
            <div class="camera-icon" onclick="document.getElementById('fileInput').click();">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <input type="file" id="fileInput" name="profile_image" style="display: none;" accept="image/*"
                onchange="handleImageSelection(event)" />
                <img src="<?php echo $profile_image; ?>" alt="Profile Image" id="profilePreview" class="profileImg" />


            <div class="save-model" id="save-model" style="display:none; gap:15px;">
                <button type="submit" name="saveUpload">SAVE</button>
                <button type="button" onclick="cancelImageSelection()">CANCEL</button>
            </div>
        </form>
    </div>
</div>
            <!-- Right Column - Profile Information -->
            <div class="profile-right">
                <div class="profile-field">
                    <label>Name:</label>
                    <p class="username"><?php echo $username; ?></p>
                </div>
                
                <div class="profile-field">
                    <label>Email:</label>
                    <p><?php echo $email; ?></p>
                </div>
                
                <div class="profile-field">
                    <label>Phone Number:</label>
                    <p><?php echo $phone?></p>
                </div>
                
                <div class="profile-field">
                    <label>Address:</label>
                    <p><?php echo $address?></p>
                </div>

                <button class="btn-edit" onclick="openModal()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                    </svg>
                    EDIT PROFILE
                </button>
            </div>
        </div>
    </div>
    <!-- Modal Overlay -->
  <div class="modal-overlay" id="modalOverlay">
    <div class="modal">
      <div class="modal-header">
        <h2 class="modal-title">Edit Profile</h2>
        <button class="close-button" onclick="closeModal()">&times;</button>
      </div>
      
      <form action="" method="post">
        <div class="form-group">
          <label for="username">Full Name</label>
          <input type="text" id="username" name="username" value="<?php echo ($username); ?>">
        </div>
        
        <div class="form-group">
          <label for="phone">Phone Number</label>
          <input type="tel" id="phone" name="phone" type="tel" pattern="[0-9]+" value="<?php echo ($phone); ?>">
        </div>
        
        <div class="form-group">
          <label for="address">Address</label>
          <input type="text" id="address" name="address" value="<?php echo ($address); ?>">
        </div>
        
        <div class="buttons" style="display:flex;justify-content:space-around">
        <button type="submit" name="save_changes" class="save-button">Save Changes</button>
        <button type="button" onclick="openDeleteModal()" class="save-button delete">Delete Account</button>
        </div>

      </form>
    </div>
  </div>

    </div>

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModalOverlay">
    <div class="modal">
        <div class="modal-header">
            <h2 class="modal-title">Confirm Deletion</h2>
            <button class="close-button" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="modal-body">
            <p>Are you sure you want to delete your account?</p>
        </div>
        <div class="buttons AccountDeleteBtn" style="display: flex; justify-content: space-around">
            <!-- Make sure the form action is POST -->
            <form method="POST">
                <button type="submit" name="delete_account" class="save-button delete">Yes</button>
            </form>
            <button class="save-button" onclick="closeDeleteModal()">No</button>
        </div>
    </div>
</div>




<script>
const fileInput = document.getElementById('fileInput');
const saveModel = document.getElementById('save-model');
const profileImg = document.querySelector('.profileImg');

fileInput.addEventListener('change', function () {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
            profileImg.src = e.target.result; // Update the image source
        };
        reader.readAsDataURL(file); // Convert the file to a data URL
        saveModel.style.display = "flex";
    }
});


function cancelImageSelection() {
        const fileInput = document.getElementById('fileInput');
        const saveModel = document.getElementById('save-model');
        const profilePreview = document.getElementById('profilePreview');

        fileInput.value = ""; // Clear the file input
        saveModel.style.display = "none"; // Hide the Save and Cancel buttons
        profilePreview.src = "<?php echo $profile_image; ?>"; // Reset the preview to the original image
    }

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


<script>
     function openModal() {
      document.getElementById('modalOverlay').classList.add('show');
    }

    function closeModal() {
      document.getElementById('modalOverlay').classList.remove('show');
    }

    // Close modal when clicking outside
    document.getElementById('modalOverlay').addEventListener('click', function(event) {
      if (event.target === this) {
        closeModal();
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
            }, 500); // Wait for the fade-out transition to complete
        }
    }

    // Show the toast and set it to auto-close
    window.onload = () => {
        setTimeout(closeToast, 2000); // Automatically close after 3 seconds
    };
</script>
<script>
function openDeleteModal() {
    document.getElementById("deleteModalOverlay").style.display = "flex";
}

function closeDeleteModal() {
    document.getElementById("deleteModalOverlay").style.display = "none";
}

</script>
</body>

</html>