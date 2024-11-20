<?php
session_start();
include 'db.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $admin_username = trim($_POST['admin_username']);
    $admin_password = $_POST['admin_password'];


    if (empty($admin_username) || empty($admin_password)) {
        $error_message = "Please fill in both fields.";
    } else {
        try {
 
            $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
            $stmt->bind_param("s", $admin_username);
            $stmt->execute();
            $stmt->bind_result($admin_id, $hashed_password);

 
            if ($stmt->fetch() && password_verify($admin_password, $hashed_password)) {

                $_SESSION['admin_id'] = $admin_id;
                header("Location: admin_dashboard.php");
                exit();
            } else {

                $error_message = "Invalid admin username or password.";
            }
            $stmt->close();
        } catch (Exception $e) {
            error_log("Error during admin login: " . $e->getMessage());
            $error_message = "An error occurred. Please try again later.";
        }
    }
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Password Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center">Admin Login</h2>
                <form action="admin_login.php" method="post" class="mt-4">
                    <div class="mb-3">
                        <label for="admin-username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="admin-username" name="admin_username" required>
                    </div>
                    <div class="mb-3">
                        <label for="admin-password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="admin-password" name="admin_password" required>
                    </div>
                    <button type="submit" class="btn btn-danger btn-lg w-100">Login</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="index.php">Back to Homepage</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
