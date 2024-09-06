<?php

$dbHost = ''; 
$dbName = ''; 
$dbUsername = '';
$dbPassword = ''; 

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check for a connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve and sanitize the player's initials from the GET request
$playerInitials = isset($_GET['initials']) ? $conn->real_escape_string($_GET['initials']) : '';

$personalBestScore = null;
$leastKeyPressesForBestScore = null;
$overallHighScore = null;
$overallLeastKeyPresses = null;

// Fetch the highest score overall
$sqlOverallHighScore = "SELECT score, key_presses FROM high_scores ORDER BY score DESC, key_presses ASC LIMIT 1;";
$resultOverallHighScore = $conn->query($sqlOverallHighScore);

if ($resultOverallHighScore) {
    if ($resultRow = $resultOverallHighScore->fetch_assoc()) {
        $overallHighScore = $resultRow['score'];
        $overallLeastKeyPresses = $resultRow['key_presses'];
    }
} else {
    die("Error fetching overall high score: " . $conn->error);
}

if ($playerInitials !== '') {
    // SQL query to fetch the highest score and the least key presses for that score for the given player
    $sql = "SELECT score, key_presses 
            FROM high_scores 
            WHERE player_initials = ? 
            AND score = (SELECT MAX(score) FROM high_scores WHERE player_initials = ?) 
            ORDER BY key_presses ASC 
            LIMIT 1";

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }

    $stmt->bind_param("ss", $playerInitials, $playerInitials);
    $stmt->execute();
    $result = $stmt->get_result(); 
    
    if ($result) {
        $row = $result->fetch_assoc();
        if ($row) {
            $personalBestScore = $row['score'];
            $leastKeyPressesForBestScore = $row['key_presses'];
        }
    } else {
        die("Error executing query: " . $stmt->error);
    }

    $stmt->close();
}

$conn->close();

// Set content type and output JSON response
header('Content-Type: application/json');
$response = [
    'personalBestScore' => $personalBestScore,
    'leastKeyPressesForBestScore' => $leastKeyPressesForBestScore,
    'overallHighScore' => $overallHighScore,
    'overallLeastKeyPresses' => $overallLeastKeyPresses
];
echo json_encode($response, JSON_NUMERIC_CHECK); 
