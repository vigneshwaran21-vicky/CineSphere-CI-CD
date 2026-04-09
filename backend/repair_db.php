<?php
require_once 'db.php';

echo "<pre>";
try {
    // 1. Create movies table if missing
    echo "Checking 'movies' table...\n";
    $conn->exec("CREATE TABLE IF NOT EXISTS movies (
        id BIGINT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        genre VARCHAR(100),
        rating DECIMAL(3,1),
        image_url TEXT,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Movies table ready.\n";

    // 2. Add rating column to reviews if missing
    echo "Checking 'reviews' column 'rating'...\n";
    $stmt = $conn->query("DESCRIBE reviews");
    $fields = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('rating', $fields)) {
        $conn->exec("ALTER TABLE reviews ADD COLUMN rating INT DEFAULT 0 AFTER movie_id");
        echo "Added 'rating' column to 'reviews'.\n";
    } else {
        echo "'rating' column already exists.\n";
    }

    // 3. Create watchlist table if missing
    echo "Checking 'watchlist' table...\n";
    $conn->exec("CREATE TABLE IF NOT EXISTS watchlist (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        movie_id BIGINT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE,
        UNIQUE(user_id, movie_id)
    )");
    echo "Watchlist table ready.\n";

    echo "\nDATABASE REPAIR COMPLETED SUCCESSFULLY.";

} catch (PDOException $e) {
    echo "\nDATABASE ERROR: " . $e->getMessage();
}
echo "</pre>";
?>
