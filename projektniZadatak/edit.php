<?php
include 'db.php';
define('ENCRYPTION_KEY', 'your-secret-key');


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

$user_id = $_SESSION["user_id"];
$password_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$password_id) {
    die("Password ID is required");
}


$stmt = $conn->prepare("SELECT purpose, password FROM passwords WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $password_id, $user_id);
$stmt->execute();
$stmt->bind_result($purpose, $password);
$stmt->fetch();


$stmt->free_result();


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_purpose = $_POST["purpose"];
    $new_password = encrypt($_POST["password"]);


    $update_stmt = $conn->prepare("UPDATE passwords SET purpose = ?, password = ? WHERE id = ?");
    $update_stmt->bind_param("ssi", $new_purpose, $new_password, $password_id);

    if ($update_stmt->execute()) {
        echo "<div class='alert alert-success text-center'>Password updated successfully!</div>";
        header("refresh:2; url=dashboard.php");
    } else {
        echo "<div class='alert alert-danger text-center'>Error updating password.</div>";
    }

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
                        <div class="progress mt-2">
                            <div id="strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small id="password-strength-text" class="form-text text-muted"></small>
                        <small id="time-to-crack" class="form-text text-muted"></small>

                    </div>

                    
                    
                    <button type="submit" class="btn btn-primary w-100">Update Password</button>
                </form>

                <div class="mt-3 text-center">
                    <a href="dashboard.php" class="btn btn-secondary w-100">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.js"></script>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        togglePassword.addEventListener('click', function (e) {
  
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;


            if (type === 'password') {
                toggleIcon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                toggleIcon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById("password").addEventListener("input", function () {
    const password = this.value;
    const strengthBar = document.getElementById("strength-bar");
    const strengthText = document.getElementById("password-strength-text");
    const crackTimeText = document.getElementById("time-to-crack");


    const { score, crackTime } = evaluatePassword(password);


    const strengthColors = ["#dc3545", "#ffc107", "#28a745"];
    const strengthLabels = ["Weak", "Medium", "Strong"];
    
    let strengthLevel = 0; 
    if (score > 50) strengthLevel = 1; 
    if (score > 80) strengthLevel = 2; 

    strengthBar.style.width = `${score}%`;
    strengthBar.style.backgroundColor = strengthColors[strengthLevel];
    strengthText.textContent = `Strength: ${strengthLabels[strengthLevel]}`;
    strengthText.style.color = strengthColors[strengthLevel];


    crackTimeText.textContent = `Estimated time to crack: ${crackTime}`;
});

function evaluatePassword(password) {
    const lengthScore = Math.min(password.length * 10, 50); 
    const varietyScore = calculateVarietyScore(password); 
    const score = Math.min(lengthScore + varietyScore, 100); 


    const entropy = calculateEntropy(password);
    const timeToCrack = estimateCrackTime(entropy);

    return { score, crackTime: timeToCrack };
}

function calculateVarietyScore(password) {
    let variety = 0;
    if (/[a-z]/.test(password)) variety += 10; 
    if (/[A-Z]/.test(password)) variety += 10; 
    if (/[0-9]/.test(password)) variety += 10;
    if (/[^a-zA-Z0-9]/.test(password)) variety += 20; 
    return variety;
}


function calculateEntropy(password) {
    let charSetSize = 0;
    if (/[a-z]/.test(password)) charSetSize += 26;
    if (/[A-Z]/.test(password)) charSetSize += 26;
    if (/[0-9]/.test(password)) charSetSize += 10;
    if (/[^a-zA-Z0-9]/.test(password)) charSetSize += 32;

    return Math.log2(charSetSize) * password.length;
}


function estimateCrackTime(entropy) {
    const guessesPerSecond = 1e9;
    const seconds = Math.pow(2, entropy) / guessesPerSecond;

    if (seconds < 60) return `${Math.round(seconds)} seconds`;
    if (seconds < 3600) return `${Math.round(seconds / 60)} minutes`;
    if (seconds < 86400) return `${Math.round(seconds / 3600)} hours`;
    if (seconds < 31536000) return `${Math.round(seconds / 86400)} days`;
    return `${Math.round(seconds / 31536000)} years`;
}

    </script>
</body>
</html>
