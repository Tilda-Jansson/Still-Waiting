<?php
$dbHost = ''; 
$dbName = ''; 
$dbUsername = '';
$dbPassword = ''; 

// Create database connection
$conn = new mysqli($dbHost, $dbUsername, $dbPassword, $dbName);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect data from POST request
$playerInitials = isset($_POST['initials']) ? $conn->real_escape_string($_POST['initials']) : '';
$score = isset($_POST['score']) ? (int)$_POST['score'] : 0;
$keyPresses = isset($_POST['keyPresses']) ? (int)$_POST['keyPresses'] : 0;

// First, fetch the best score and corresponding key presses for this player
$sql = "SELECT score, key_presses FROM high_scores WHERE player_initials = ? ORDER BY score DESC, key_presses ASC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $playerInitials);
$stmt->execute();
$result = $stmt->get_result();
$existingRecord = $result->fetch_assoc();

// Decide whether to update or insert based on the new score
if ($existingRecord) {
    // If the new score is better than the existing best score
    if ($score > $existingRecord['score'] || ($score == $existingRecord['score'] && $keyPresses < $existingRecord['key_presses'])) {
        // Delete the existing scores for this player
        $deleteSql = "DELETE FROM high_scores WHERE player_initials = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("s", $playerInitials);
        $deleteStmt->execute();
        $deleteStmt->close();

        // Insert the new best score
        $insertSql = "INSERT INTO high_scores (player_initials, score, key_presses) VALUES (?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("sii", $playerInitials, $score, $keyPresses);
        $insertStmt->execute();
        $insertStmt->close();

        echo "New personal best score recorded.";
    } else {
        echo "The submitted score is not a new personal best.";
    }
} else {
    // If the player does not have an existing score, simply add the new score
    $insertSql = "INSERT INTO high_scores (player_initials, score, key_presses) VALUES (?, ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("sii", $playerInitials, $score, $keyPresses);
    $insertStmt->execute();
    $insertStmt->close();

    echo "Score recorded for a new player.";
}

// Close database connection
$conn->close();
?>
