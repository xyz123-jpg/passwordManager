<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Password Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5 text-center">
        <h2>Welcome to the Password Manager</h2>
        <p>Please select an option:</p>
        <form action="login_signup.php" method="get">
            <button type="submit" name="action" value="login" class="btn btn-primary btn-lg m-2">Login</button>
            <button type="submit" name="action" value="signup" class="btn btn-success btn-lg m-2">Sign Up</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
