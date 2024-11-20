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


if (isset($_POST['logout'])) {

    session_destroy();
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION["user_id"];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["purpose"]) && isset($_POST["password"])) {

    $purpose = $_POST["purpose"];
    $password = encrypt($_POST["password"]);


    $stmt = $conn->prepare("INSERT INTO passwords (user_id, purpose, password) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $purpose, $password);
    $stmt->execute();
    

    header("Location: dashboard.php");
    exit();
}



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

        <form action="dashboard.php" method="post">
            <button type="submit" name="logout" class="logout-btn">Log Out</button>
        </form>
        <form action="dashboard.php" method="post" class="mb-4">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4"> 
            <div class="mb-3">
                <label for="purpose" class="form-label">Purpose</label>
                <input type="text" class="form-control" id="purpose" name="purpose" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
                <div class="progress mt-2">
                    <div id="strength-bar" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small id="password-strength-text" class="form-text text-muted"></small>
                <small id="time-to-crack" class="form-text text-muted"></small>
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

                            <span class="password" id="password-<?php echo $row['id']; ?>">
                                <?php echo str_repeat('•', strlen($row['password'])); ?>
                            </span>

                            <span class="password" id="password-visible-<?php echo $row['id']; ?>" style="display:none">
                                <?php echo htmlspecialchars(decrypt($row['password'])); ?>
                            </span>

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

                const passwordId = this.id.split('-')[1];

                const passwordField = document.getElementById('password-' + passwordId);
                const passwordVisibleField = document.getElementById('password-visible-' + passwordId);
                const toggleIcon = document.getElementById('toggle-' + passwordId);


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
