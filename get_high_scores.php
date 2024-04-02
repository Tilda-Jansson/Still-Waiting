<?php
$dbHost = '';
$dbName = '';
$dbUsername = '';
$dbPassword = '';

// Create database connection
$conn = mysqli_connect($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Number of high scores to fetch
$limit = 10;

// SQL query to fetch top scores
$sql = "SELECT player_initials, score, key_presses FROM high_scores ORDER BY score DESC, key_presses ASC, timestamp ASC LIMIT ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $limit);

// Execute query
$stmt->execute();

// Bind result variables
$stmt->bind_result($playerInitials, $score, $keyPresses);

// Fetch values
$scores = [];
while ($stmt->fetch()) {
    $scores[] = ['initials' => $playerInitials, 'score' => $score, 'key_presses' => $keyPresses];
}

// Close statement and connection
$stmt->close();
$conn->close();

// Return results as JSON
header('Content-Type: application/json');
echo json_encode($scores);
?>
