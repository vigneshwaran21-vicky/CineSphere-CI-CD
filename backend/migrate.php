<?php
require_once 'db.php';
try {
    $conn->exec("ALTER TABLE reviews ADD COLUMN rating INT DEFAULT 0 AFTER movie_id");
    echo "SQL SUCCESS: Added rating column to reviews table.";
} catch (PDOException $e) {
    if (stripos($e->getMessage(), 'duplicate column') !== false) {
        echo "SQL INFO: Column already exists.";
    } else {
        echo "SQL ERROR: " . $e->getMessage();
    }
}
?>
