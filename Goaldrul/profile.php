<?php
ini_set('display_errors', 1); 
ini_set('display_startup_errors', 1);  
error_reporting(E_ALL);  

$dsn = 'pgsql:host=localhost;port=5432;dbname=soccer';
$user = 'postgres';
$password = '123';

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
 
session_start();

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: dashboard.php"); 
    exit();
}
if ( !isset($_SESSION["user_id"])) {
    echo "<script>alert('You are not logged in or the session user_id was not found..');</script>";
    exit;
}

$user_id = $_SESSION["user_id"];
// $username = $_SESSION['username'];

$name = $bio = $birthday = $country = $phone = $twitter = $facebook = $google_plus = $linkedin = $instagram = '';

$stmt = $pdo->prepare("SELECT name,bio, birthday, country, phone, twitter, facebook, google_plus, linkedin, instagram, profile_photo FROM profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if ($row) {
    $bio = $row['bio'];
    $birthday = $row['birthday'];
    $country = $row['country'];
    $phone = $row['phone'];
    $twitter = $row['twitter'];
    $facebook = $row['facebook'];
    $google_plus = $row['google_plus'];
    $linkedin = $row['linkedin'];
    $instagram = $row['instagram'];
    $profile_photo = $row['profile_photo'];
    $name = $row['name'];
}

if (isset($_POST['update_name'])) {
    $name = $_POST['name']; 
    // $user_id = $_SESSION['user_id'];
    

    $stmt = $pdo->prepare("SELECT user_id FROM profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);

    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("UPDATE profiles SET name = ? WHERE user_id = ?");
        $success = $stmt->execute([$name, $user_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO profiles (user_id, name) VALUES (?, ?)");
        $success = $stmt->execute([$user_id, $name]);
    }

    if ($success) {
        echo "<script>alert('Name changed successfully.');</script>";
    } else {
        echo "<script>alert('An error occurred while changing the name.');</script>";
    }


    // $stmt->close();
}

if (isset($_POST['update_info'])) {
    $bio = $_POST['bio'];
    $birthday = $_POST['birthday'];
    $country = $_POST['country'];
    $phone = $_POST['phone'];

    
    $stmt = $pdo->prepare("UPDATE profiles SET bio = ?, birthday = ?, country = ?, phone = ? WHERE user_id = ?");
    $stmt->execute([$bio, $birthday, $country, $phone, $user_id]);


    $stmt = $pdo->prepare("SELECT user_id FROM profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);

    if ($stmt->rowCount() > 0) {
        // Jika user_id sudah ada, update data
        $stmt = $pdo->prepare("UPDATE profiles SET bio = ?, birthday = ?, country = ?, phone = ?, profile_photo = ? WHERE user_id = ?");
        $stmt->execute([$bio, $birthday, $country, $phone, $profile_photo, $user_id]);
    } else {
        // Jika user_id belum ada, lakukan insert data baru
        $stmt = $pdo->prepare("INSERT INTO profiles (user_id, bio, birthday, country, phone, profile_photo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $bio, $birthday, $country, $phone, $default_photo]);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Info updated successfully!');</script>";
    } else {
        // echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
    // $stmt->close();
}

if (isset($_POST['update_social_links'])) {
    $twitter = $_POST['twitter'];
    $facebook = $_POST['facebook'];
    $google_plus = $_POST['google_plus'];
    $linkedin = $_POST['linkedin'];
    $instagram = $_POST['instagram'];

    $stmt = $pdo->prepare("UPDATE profiles SET twitter = ?, facebook = ?, google_plus = ?, linkedin = ?, instagram = ? WHERE user_id = ?");
    $stmt->execute([$twitter, $facebook, $google_plus, $linkedin, $instagram, $user_id]);

    if ($stmt->execute()) {
        echo "<script>alert('Social links updated successfully!');</script>";
    } else {
        // echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
    // $stmt->close();
}
if (isset($_POST['update_profile'])) {
    if (isset($_FILES["profile_photo"]) && $_FILES["profile_photo"]["error"] == 0) {
        $target_dir = "uploads/";
        $imageFileType = strtolower(pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION));
        
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true); // Membuat folder jika belum ada
        }

        if ($_FILES["profile_photo"]["size"] > 500000) {
            die("<script>alert('File is too large.');</script>");
        }

        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (!in_array($imageFileType, $allowed_types)) {
            die("<script>alert('Only JPG, JPEG, PNG, and GIF files are allowed.');</script>");
        }

        $unique_filename = uniqid('profile_', true) . '.' . $imageFileType;
        $target_file = $target_dir . $unique_filename;

        if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
            $stmt = $pdo->prepare("UPDATE profiles SET profile_photo = ? WHERE user_id = ?");
            if ($stmt->execute([$target_file, $user_id])) {
                echo "<script>alert('Profile photo updated successfully.');</script>";
            } else {
                echo "<script>alert('Failed to update database.');</script>";
            }
        } else {
            echo "<script>alert('Failed to upload file.');</script>";
        }
    } else {
        echo "<script>alert('No file uploaded or an error occurred.');</script>";
    }
}

if (isset($_POST['update_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    $user_id = $_SESSION["user_id"];

    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password)) {
        echo "<script>alert('All fields must be filled!');</script>";
    } elseif ($password_baru !== $konfirmasi_password) {
        echo "<script>alert('New password and confirm password do not match!');</script>";
    } else {
        $sql = "SELECT password_hash FROM users WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if (password_verify($password_lama, $row['password_hash'])) {
                $hashed_password_baru = password_hash($password_baru, PASSWORD_BCRYPT);
                $sql_update = "UPDATE users SET password_hash = ? WHERE id = ?";
                $stmt_update = $pdo->prepare($sql_update);

                if ($stmt_update->execute([$hashed_password_baru, $user_id])) {
                    echo "<script>alert('Password changed successfully!');</script>";
                } else {
                    echo "<script>alert('Failed to change password. Please try again.');</script>";
                }
            } else {
                echo "<script>alert('Old password is wrong.');</script>";
            }
        } else {
            echo "<script>alert('User not found.');</script>";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GD Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/profile.css?v=<?php echo filemtime('css/profile.css'); ?>">
    <link rel="stylesheet" href="css/teaminfo.css?v=<?php echo filemtime('css/teaminfo.css'); ?>">
    <link rel="stylesheet" href="background.css?v=<?php echo filemtime('background.css'); ?>">
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboardlogin.php">
                <img src="assets/gd.png" class="img-fluid" alt="Logo Goaldrul"> 
                GOALDRUL 
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user"></i> Profile</a>
                        <ul class="dropdown-menu">
                            <li>
                                <form action="" method="POST" class="d-inline">
                                    <button type="submit" name="logout" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="favoriteteam.php"><i class="fas fa-star"></i> Favorite Team</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="upcoming.php"><i class="fas fa-calendar-alt"></i> Upcoming Matches</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="league.php" id="league"><i class="fas fa-calendar-alt"></i> League</a>
                    </li> 
                </ul>
            </div>
        </div>
    </nav>

    <div class="container light-style flex-grow-1 container-p-y">
        <h4 class="font-weight-bold py-3 mb-4">
            Account Settings
        </h4>
        <div class="card overflow-hidden">
            <div class="row no-gutters row-bordered row-border-light">
                <div class="col-md-3 pt-0">
                    <div class="list-group list-group-flush account-settings-links">
                        <a class="list-group-item list-group-item-action active" data-toggle="list"
                            href="#account-general">General</a>
                        <a class="list-group-item list-group-item-action" data-toggle="list"
                            href="#account-change-password">Change password</a>
                        <a class="list-group-item list-group-item-action" data-toggle="list"
                            href="#account-info">Info</a>
                        <a class="list-group-item list-group-item-action" data-toggle="list"
                            href="#account-social-links">Social links</a>
                        
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content">
                        <div class="tab-pane fade active show" id="account-general">
                            <div class="card-body media align-items-center">
                            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Picture" class="profile-photo" >                            
                            </div>

                            <hr class="border-light m-0">
                            <div class="card-body">
                                <form action="#" method="POST" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="profile_photo">Upload Profile Picture:</label>
                                    <input type="file" name="profile_photo" id="profile_photo" accept="image/*" required>
                                    <input type="submit" name="update_profile">
                                </div>
                                </form>
                                <form>
                                    <!-- <div class="mt-3">
                                        <label for="username">Username:</label>
                                        <input type="text" id="username" class="form-control mb-1" value="Profile" readonly>
                                    </div> -->
                                </form>
                                <form action="" method="POST">
                                    <div class="form-group">
                                        <label class="form-label">Name</label>
                                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($name); ?>">
                                    </div>
                                    <div class="text-right mt-3">
                                        <input type="submit" class="btn btn-primary" name="update_name" value="Save Change">
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="account-change-password">
                            <div class="card-body pb-2">
                                <form action="" method="POST">
                                    <div class="form-group">
                                        <label class="form-label">Current password</label>
                                        <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">New password</label>
                                        <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Repeat new password</label>
                                        <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                                    </div>
                                    <div class="text-right mt-3">
                                        <input type="submit" class="btn btn-primary" name="update_password" value="Change Password">
                                    </div>
                                </form>

                            </div>
                        </div>
                            <div class="tab-pane fade" id="account-info">
                                <div class="card-body pb-2">
                                    <form action="" method="POST">

<div class="form-group">
    <label class="form-label">Biografi</label>
    <textarea type="text" class="form-control" name="bio" required><?php echo !empty($bio) ? htmlspecialchars($bio) : ''; ?></textarea>
</div>

<div class="form-group">
    <label class="form-label">Birthday</label>
    <input type="date" class="form-control" name="birthday" value="<?php echo !empty($birthday) ? $birthday : ''; ?>" required>
</div>

<div class="form-group">
    <label class="form-label">Country</label>
    <input placeholder="Indonesia" type="text" class="form-control" name="country" value="<?php echo !empty($country) ? htmlspecialchars($country) : ''; ?>" required>
</div>

<div class="form-group">
    <label class="form-label">Contacts</label>
    <input placeholder="+62" type="text" class="form-control" name="phone" value="<?php echo !empty($phone) ? htmlspecialchars($phone) : ''; ?>" required>
</div>

<div class="text-right mt-3">
    <input type="submit" class="btn btn-primary" name="update_info" value="Save Changes">
</div>


                                    </form>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="account-social-links">
                                <div class="card-body pb-2">
                                    <form action="" method="POST">
                                        <div class="form-group">
    <label class="form-label">Twitter</label>
    <input type="url" class="form-control" name="twitter" value="<?php echo !empty($twitter) ? htmlspecialchars($twitter) : ''; ?>">
</div>

<div class="form-group">
    <label class="form-label">Facebook</label>
    <input type="url" class="form-control" name="facebook" value="<?php echo !empty($facebook) ? htmlspecialchars($facebook) : ''; ?>">
</div>

<div class="form-group">
    <label class="form-label">Google+</label>
    <input type="url" class="form-control" name="google_plus" value="<?php echo !empty($google_plus) ? htmlspecialchars($google_plus) : ''; ?>">
</div>

<div class="form-group">
    <label class="form-label">LinkedIn</label>
    <input type="url" class="form-control" name="linkedin" value="<?php echo !empty($linkedin) ? htmlspecialchars($linkedin) : ''; ?>">
</div>

<div class="form-group">
    <label class="form-label">Instagram</label>
    <input type="url" class="form-control" name="instagram" value="<?php echo !empty($instagram) ? htmlspecialchars($instagram) : ''; ?>">
</div>

                                        <div class="text-right mt-3">
                                            <input type="submit" class="btn btn-primary" name="update_social_links" value="Save Changes">
                                        </div>
                                    </form>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
 
    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


</body>
</html>
