<?php
// Encryption and decryption function
include 'db.php';
define('ENCRYPTION_KEY', 'your-secret-key'); // Make sure to store this key securely

function encrypt($data) {
    return openssl_encrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, '1234567890123456');
}

function decrypt($data) {
    return openssl_decrypt($data, 'aes-256-cbc', ENCRYPTION_KEY, 0, '1234567890123456');
}

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: index.php");
    exit();
}

// Handle logout
if (isset($_POST['logout'])) {
    // Destroy the session and redirect to index.php
    session_destroy();
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Check if the form is submitted (POST method)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["purpose"]) && isset($_POST["password"])) {
    // Get the form data
    $purpose = $_POST["purpose"];
    $password = encrypt($_POST["password"]); // Hash the password

    // Insert password into the database
    $stmt = $conn->prepare("INSERT INTO passwords (user_id, purpose, password) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $purpose, $password);
    $stmt->execute();
    
    // Redirect to the same page to avoid form resubmission on page refresh
    header("Location: dashboard.php");
    exit();
}


// Fetch stored encrypted passwords
$result = $conn->query("SELECT id, purpose, password FROM passwords WHERE user_id = $user_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Password Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Custom CSS for logout button */
        .logout-btn {
            position: fixed;
            top: 10px;
            right: 10px;
            width: 70px;
            height: 70px;
            border-radius: 10px;
            background-color: #f8d7da;
            color: #721c24;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            font-weight: bold;
            border: none;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #721c24;
            color: #fff;
        }

        .logout-btn:focus {
            outline: none;
        }
        .password-toggle-icon {
            cursor: pointer;
        }
        .password-cell {
            position: relative;
        }
        .password-cell .password {
            display: inline-block;
        }
        .password-cell .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Your Saved Passwords</h2>
        <!-- Logout Button -->
        <form action="dashboard.php" method="post">
            <button type="submit" name="logout" class="logout-btn">Log Out</button>
        </form>
        <form action="dashboard.php" method="post" class="mb-4">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4"> <!-- Adjust width with col-md-6 and col-lg-4 -->
            <div class="mb-3">
                <label for="purpose" class="form-label">Purpose</label>
                <input type="text" class="form-control" id="purpose" name="purpose" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Add Password</button>
        </div>
    </div>
</form>


        <h3>Your Passwords:</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Purpose</th>
                    <th>Password</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row["purpose"]); ?></td>
                        <td class="password-cell">
                            <!-- Display password as dots -->
                            <span class="password" id="password-<?php echo $row['id']; ?>">
                                <?php echo str_repeat('•', strlen($row['password'])); ?>
                            </span>
                            <!-- Decrypt and show the actual password -->
                            <span class="password" id="password-visible-<?php echo $row['id']; ?>" style="display:none">
                                <?php echo htmlspecialchars(decrypt($row['password'])); ?>
                            </span>
                            <!-- Eye icon to toggle password visibility -->
                            <i class="bi bi-eye-slash password-toggle-icon" id="toggle-<?php echo $row['id']; ?>"></i>
                        </td>
                        <td>
                            <a href="edit.php?id=<?php echo $row["id"]; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.password-toggle-icon').forEach(function (toggleIcon) {
            toggleIcon.addEventListener('click', function () {
                // Get the row ID from the element's ID attribute
                const passwordId = this.id.split('-')[1];

                const passwordField = document.getElementById('password-' + passwordId);
                const passwordVisibleField = document.getElementById('password-visible-' + passwordId);
                const toggleIcon = document.getElementById('toggle-' + passwordId);

                // Toggle visibility of the password
                if (passwordField.style.display === 'none') {
                    passwordField.style.display = 'inline-block';
                    passwordVisibleField.style.display = 'none';
                    toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
                } else {
                    passwordField.style.display = 'none';
                    passwordVisibleField.style.display = 'inline-block';
                    toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
                }
            });
        });
    </script>
</body>
</html>
