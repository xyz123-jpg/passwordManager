<?php
include 'db.php';
define('ENCRYPTION_KEY', 'your-secret-key'); // Make sure to store this key securely

// Encryption function
function encrypt($data) {
    return openssl_encrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, '1234567890123456');
}

// Decryption function (for displaying the password)
function decrypt($data) {
    return openssl_decrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, '1234567890123456');
}

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$password_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$password_id) {
    die("Password ID is required");
}

// Fetch the password details from the database
$stmt = $conn->prepare("SELECT purpose, password FROM passwords WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $password_id, $user_id);
$stmt->execute();
$stmt->bind_result($purpose, $password);
$stmt->fetch();

// Ensure that we free the result set before running another query
$stmt->free_result();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_purpose = $_POST["purpose"];
    $new_password = encrypt($_POST["password"]);

    // Update the password in the database
    $update_stmt = $conn->prepare("UPDATE passwords SET purpose = ?, password = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $new_purpose, $new_password, $password_id);

    if ($update_stmt->execute()) {
        echo "<div class='alert alert-success text-center'>Password updated successfully!</div>";
        header("refresh:2; url=dashboard.php");
    } else {
        echo "<div class='alert alert-danger text-center'>Error updating password.</div>";
    }

    // Free the result after the update
    $update_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Password - Password Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .password-toggle-icon {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Edit Your Password</h2>
                
                <form action="edit.php?id=<?php echo $password_id; ?>" method="post">
                    <div class="mb-3">
                        <label for="purpose" class="form-label">Purpose</label>
                        <input type="text" class="form-control" id="purpose" name="purpose" value="<?php echo htmlspecialchars($purpose); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="password" name="password" required>
                            <button type="button" class="btn btn-outline-secondary password-toggle-icon" id="togglePassword">
                                <i class="bi bi-eye-slash" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Update Password</button>
                </form>

                <div class="mt-3 text-center">
                    <a href="dashboard.php" class="btn btn-secondary w-100">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Icons CDN for the eye icon -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        togglePassword.addEventListener('click', function (e) {
            // Toggle the type of password input between 'password' and 'text'
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;

            // Toggle the eye icon (show/hide password)
            if (type === 'password') {
                toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
