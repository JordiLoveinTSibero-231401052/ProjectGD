<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: dashboard.php");
    exit();
}

include "service/apikeyorg.php";

$fixtures_url = 'https://api.football-data.org/v4/matches';

$league_ids = [
    2016, // English Championship
    2003, // Eredivisie
    2001, // UCL
    2013, // Campeonato Brasileiro Série A
    2021, // Premier League
    2014, // La Liga
    2015, // Ligue 1
    2002, // Bundesliga
    2019, // Serie A
    2017  // Primeira Liga
];

$all_matches = [];

foreach ($league_ids as $league_id) {
    $params = [
        'competitions' => $league_id,
        'dateFrom' => date('Y-m-d'),
        'dateTo' => date('Y-m-d', strtotime('+7 days'))
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $fixtures_url . '?' . http_build_query($params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-Auth-Token: ' . $api_key]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);

    if ($response === false) {
        echo 'Error: ' . curl_error($ch);
        continue;
    }

    $data = json_decode($response, true);
    if (!empty($data['matches'])) {
        $all_matches = array_merge($all_matches, $data['matches']);
    }

    curl_close($ch);
}

usort($all_matches, function ($a, $b) {
    return strtotime($a['utcDate']) - strtotime($b['utcDate']);
});

$upcoming_matches = array_slice($all_matches, 0, 50);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Matches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime('css/style.css'); ?>">
    <link rel="stylesheet" href="css/teaminfo.css?v=<?php echo filemtime('css/teaminfo.css'); ?>">
    <link rel="stylesheet" href="background.css?v=<?php echo filemtime('background.css'); ?>">
</head>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboardlogin.php">
                <img src="assets/gd.png" class="img-fluid" alt="Logo Goaldrul"> GOALDRUL 
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user"></i> profile</a>
                        <ul class="dropdown-menu">
                            <li>
                            <a href="profile.php" class="dropdown-item">Profile</a> 
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

    <nav class="navbar bg-body-tertiary">
    <div class="bottom_nav">
        <ul>
            <a href="matches.php?league_id=2021">
                <img src="assets/premierleague.png" alt="Premier League" class="img">
            </a>
            <a href="matches.php?league_id=2014">
                <img src="assets/laliga24.png" alt="La Liga" class="img">
            </a>
            <a href="matches.php?league_id=2015">
                <img src="assets/ligue1.png" alt="Ligue 1" class="img">
            </a>
            <a href="matches.php?league_id=2002">
                <img src="assets/bundesliga.png" alt="Bundesliga" class="img">
            </a>
            <a href="matches.php?league_id=2019">
                <img src="assets/serie_a.png" alt="Serie A" class="img">
            </a>
            <a href="matches.php?league_id=2001">
                <img src="assets/ucl.png" alt="UCL" class="img">
            </a>
            <a href="matches.php?league_id=2016">
                <img src="assets/championship_england.png" alt="Championship" class="img">
            </a>
            <a href="matches.php?league_id=2017">
                <img src="assets/primeira_liga.png" alt="Primeira Liga" class="img">
            </a>
            <a href="matches.php?league_id=2013">
                <img src="assets/serie_a_brazil.png" alt="Serie A Brazil" class="img">
            </a>
            <a href="matches.php?league_id=2003">
                <img src="assets/eredivisie.png" alt="Eredivisie" class="img">
            </a>
        </ul>
    </div>
</nav>

<body>
<div class="container mt-3">
    <h2 class="text-center mt-3">MATCHES</h2>

    <?php 
if (!empty($upcoming_matches)) {
    $current_day = '';

    echo "<ul class='list-group'>";

    foreach ($upcoming_matches as $match) {
        $home_team_id = $match['homeTeam']['id'];
        $home_team = $match['homeTeam']['name'];
        $home_team_logo = $match['homeTeam']['crest'] ?? 'default_logo.png'; 
        $away_team_id = $match['awayTeam']['id'];
        $away_team = $match['awayTeam']['name'];
        $away_team_logo = $match['awayTeam']['crest'] ?? 'default_logo.png';
        $match_competition = $match['competition']['name'];
        $match_date = $match['utcDate'];
        $match_status = $match['status'];
        $home_score = $match['score']['fullTime']['home'] ?? '-';
        $away_score = $match['score']['fullTime']['away'] ?? '-';

        date_default_timezone_set('Asia/Bangkok');
        $formatted_date = date('H:i', strtotime($match_date));
        $match_day = date('l, d M Y', strtotime($match_date));

        if ($current_day !== $match_day) {
            $current_day = $match_day;
            echo "<h4 class='text-center mt-3 mb-2'><strong>$current_day</strong></h4>";
        }

        echo "<li class='list-group-item match-item'>";
        echo "    <div class='d-flex align-items-center match-container'>";
        echo "        <div class='d-flex align-items-center team-info' style='flex: 1;'>";
        echo "            <img src='$home_team_logo' alt='$home_team Logo' class='img-fluid me-2' style='width: 50px; height: 50px;'>";
        echo "            <a href='team_info.php?team_id=$home_team_id' class='text-truncate home-team-name' style='flex-grow: 1;'>$home_team</a>";
        echo "        </div>";

        echo "        <div class='score-container text-center d-flex align-items-center justify-content-center' style='flex: 0 0 80px;'>";
        if ($match_status === 'FINISHED') {
            echo "            <div class='text-center'>"; 
            echo "                <small class='text-muted'>FT</small><br>";
            echo "                <strong>$home_score - $away_score</strong>";
            echo "            </div>";
        } else {
            echo "            <small><strong>$formatted_date</strong></small>";
        }
        echo "        </div>";

        echo "        <div class='d-flex align-items-center team-info justify-content-end' style='flex: 1;'>";
        echo "            <a href='team_info.php?team_id=$away_team_id' class='text-truncate away-team-name me-2' style='text-align: right; flex-grow: 1;'>$away_team</a>";
        echo "            <img src='$away_team_logo' alt='$away_team Logo' class='img-fluid' style='width: 50px; height: 50px;'>";
        echo "        </div>";
        echo "    </div>";

        echo "    <div class='match-details mt-2 text-center'>";
        echo "        <small class='text-muted'>$match_competition</small>";
        echo "    </div>";
        echo "</li>";
    }

    echo "</ul>";
} else {
    echo "<p class='mt-5 text-center'>There are no matches scheduled.</p>";
}
?>
</div>


    <footer class="text-center text-lg-start mt-5 pt-4">
        <div class="text-center p-3">
            <p>&copy; 2024 Goaldrul. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
