<?php
session_start();

// Define variables and set to empty values
$adminUsername = $adminPassword = "";
$adminError = "";

// API endpoint for authentication
$apiUrl = 'https://rst.moodscope.in/api/auth.php';

// Process admin login form
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_login'])) {
    $adminUsername = sanitize_input($_POST['adminUsername']);
    $adminPassword = sanitize_input($_POST['adminPassword']);
    
    // Validate admin credentials
    if (empty($adminUsername) || empty($adminPassword)) {
        $adminError = "Please enter both username and password.";
    } else {
        // API authentication
        $response = apiAuthenticate($adminUsername, $adminPassword);
        
        if ($response['success']) {
            // Set session variables
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $adminUsername;
            $_SESSION['role'] = "admin";
            $_SESSION['token'] = $response['token'] ?? '';
            
            // Redirect to admin dashboard
            header("Location: main.php");
            exit();
        } else {
            $adminError = $response['error'] ?? "Authentication failed. Please try again.";
        }
    }
}

// Function to send authentication request to API
function apiAuthenticate($username, $password) {
    global $apiUrl;
    
    // Prepare data for API request
    $data = json_encode([
        'username' => $username,
        'password' => $password
    ]);
    
    // Initialize cURL session
    $ch = curl_init($apiUrl);
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data)
    ]);
    
    // Execute cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Check for cURL errors
    if (curl_errno($ch)) {
        curl_close($ch);
        return ['success' => false, 'error' => 'Connection error: ' . curl_error($ch)];
    }
    
    curl_close($ch);
    
    // Process response
    $responseData = json_decode($response, true);
    
    if ($httpCode == 200) {
        return ['success' => true, 'token' => $responseData['token'] ?? ''];
    } else {
        return ['success' => false, 'error' => $responseData['error'] ?? 'Authentication failed'];
    }
}

// Function to sanitize form inputs
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - MoodScope</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css?family=Montserrat:400,800');

        * {
            box-sizing: border-box;
        }

        body {
            background: #f6f5f7;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            font-family: 'Montserrat', sans-serif;
            height: 100vh;
            margin: -20px 0 50px;
        }

        h1 {
            font-weight: bold;
            margin: 0;
        }

        h2 {
            text-align: center;
        }

        p {
            font-size: 14px;
            font-weight: 100;
            line-height: 20px;
            letter-spacing: 0.5px;
            margin: 20px 0 30px;
        }

        span {
            font-size: 12px;
        }

        a {
            color: #333;
            font-size: 14px;
            text-decoration: none;
            margin: 15px 0;
        }

        button {
            border-radius: 20px;
            border: 1px solid #f64a4a;
            background-color: #f64a4a;
            color: #FFFFFF;
            font-size: 12px;
            font-weight: bold;
            padding: 12px 45px;
            letter-spacing: 1px;
            text-transform: uppercase;
            transition: transform 80ms ease-in;
            cursor: pointer;
        }

        button:active {
            transform: scale(0.95);
        }

        button:focus {
            outline: none;
        }

        form {
            background-color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 50px;
            height: 100%;
            text-align: center;
        }

        input {
            background-color: #eee;
            border: none;
            padding: 12px 15px;
            margin: 8px 0;
            width: 100%;
        }

        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 14px 28px rgba(0,0,0,0.25), 
                    0 10px 10px rgba(0,0,0,0.22);
            position: relative;
            overflow: hidden;
            width: 400px;
            max-width: 100%;
            min-height: 500px;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            width: 100%;
        }

        .admin-panel-container {
            left: 0;
            width: 100%;
            z-index: 2;
        }

        .social-container {
            margin: 20px 0;
        }

        .social-container a {
            border: 1px solid #DDDDDD;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0 5px;
            height: 40px;
            width: 40px;
        }

        .error-message {
            color: #FF416C;
            font-size: 12px;
            margin-top: 5px;
            <?php if (empty($adminError)) { echo 'display: none;'; } ?>
        }
        
        .logo-container {
            margin-bottom: 20px;
            text-align: center;
        }
        
        .logo-container img {
            max-width: 200px;
            height: auto;
        }
    </style>
</head>
<body>
    <div class="container" id="container">
        <div class="form-container admin-panel-container">
            <form id="adminLoginForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="logo-container">
                    <img src="logo.jpeg" alt="MoodScope Logo">
                </div>
                <h1>Admin Panel</h1>
                <!-- <div class="social-container">
                    <a href="#" class="social"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social"><i class="fab fa-google-plus-g"></i></a>
                    <a href="#" class="social"><i class="fab fa-linkedin-in"></i></a>
                </div> -->
                <!-- <span>or use your admin credentials</span> -->
                <input type="text" placeholder="Admin Username" id="adminUsername" name="adminUsername" value="<?php echo $adminUsername; ?>" required />
                <input type="password" placeholder="Admin Password" id="adminPassword" name="adminPassword" required />
                <div class="error-message" id="adminError" <?php if (!empty($adminError)) { echo 'style="display: block;"'; } ?>>
                    <?php echo $adminError; ?>
                </div>
                <a href="#">Forgot admin password?</a>
                <button type="submit" name="admin_login">Login as Admin</button>
            </form>
        </div>
    </div>
    <script>
    // For client-side form validation
    document.getElementById('adminLoginForm').addEventListener('submit', function(e) {
        const username = document.getElementById('adminUsername').value.trim();
        const password = document.getElementById('adminPassword').value.trim();
        const errorElement = document.getElementById('adminError');
        
        if (!username || !password) {
            e.preventDefault();
            errorElement.style.display = 'block';
            errorElement.textContent = 'Username and password are required.';
        }
    });
    </script>
</body>
</html>