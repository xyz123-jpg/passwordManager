<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);


    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<div class='alert alert-warning'>Username already exists. Please choose another.</div>";
    } else {

        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $password);

        if ($stmt->execute()) {
            echo "<div class='alert alert-success'>Signup successful! You can now <a href='login.php'>login</a></div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $stmt->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Password Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Create a New Account</h2>
                <form action="signup.php" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
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

                    <button type="submit" class="btn btn-success w-100">Sign Up</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="index.php">Back to Homepage</a>
                </div>
            </div>
        </div>
    </div>

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
