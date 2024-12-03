<?php
include "service/database.php";
session_start();

if (isset($_SESSION["is_login"])) {
    header("location: dashboardlogin.php");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_input = $_POST['login_input'];
    $password = $_POST['password'];

    // Cari user berdasarkan email atau username
    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE email = :input OR username = :input");
    $stmt->bindParam(':input', $login_input);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verifikasi password
    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: dashboardlogin.php");
        echo "p";
        // exit;
    } else {
        echo "Invalid username/email or password.";
    }
}
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GD LOGIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="css/login.css?v=<?php echo filemtime('css/login.css'); ?>">
    <style>
        body{
            background-image: url(assets/bbb.jpg);
        }
    </style>
</head>
<body>
    <section>
        <div class="container mt-5 pt-5">
            <div class="row">
                <div class="col-12 col-sm-8 col-md-6 m-auto">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="mb-3 pt-4">GOALDRUL</h3>
                            <form  method="POST">
                                
                                <input type="text" class="form-control my-4 py-2" placeholder="Username or Email"                                 name="login_input" placeholder="Username or Email" required>
                                <input type="password" class="form-control my-4 py-2" placeholder="Password" name="password" required />
                                <div class="text-center">
                                    <button class="btn btn-primary" type="submit" >Login</button>
                                </div>
                                <div class="text-center mt-3">
                                    <a href="register.php" class="nav-link">Don't have an account?</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="footer">
        <p>&copy; 2024 Goaldrul Football Website | All Rights Reserved</p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
