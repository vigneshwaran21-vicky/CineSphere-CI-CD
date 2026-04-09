<?php
require_once 'db.php';
try {
    echo "--- REVIEW TABLE ---\n";
    $stmt = $conn->query("DESCRIBE reviews");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "\n--- MOVIE TABLE ---\n";
    $stmt = $conn->query("DESCRIBE movies");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
