<?php
require_once 'db.php';
header("Content-Type: application/json; charset=UTF-8");

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'all') {
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        if($user_id){
            try {
                $query = "SELECT w.id as watchlist_id, m.id, m.title, m.genre, m.rating, m.image_url 
                          FROM watchlist w 
                          JOIN movies m ON w.movie_id = m.id 
                          WHERE w.user_id = :user_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->execute();
    
                $watchlist = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($watchlist);
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(["message" => "Server error."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "User ID required."]);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    
    if ($action === 'add') {
        if (!empty($data->user_id) && !empty($data->movie_id)) {
            try {
                // 1. Ensure movie exists in movies table (cache it from TMDB if not)
                $movie_check = $conn->prepare("SELECT id FROM movies WHERE id = :id");
                $movie_check->execute([':id' => $data->movie_id]);
                
                if ($movie_check->rowCount() === 0) {
                    // Fetch from TMDB (simplified internal fetch)
                    $apiKey = '3fd2be6f0c70a2a598f084ddfb75487c'; 
                    $url = "https://api.themoviedb.org/3/movie/" . $data->movie_id . "?api_key=" . $apiKey;
                    $ch = curl_init($url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    $res = json_decode(curl_exec($ch), true);
                    curl_close($ch);

                    if ($res && !isset($res['status_code'])) {
                        $title = $res['title'] ?? 'Unknown';
                        $genre = isset($res['genres'][0]) ? $res['genres'][0]['name'] : 'Feature Film';
                        $rating = $res['vote_average'] ?? 0;
                        $img = 'https://image.tmdb.org/t/p/w500' . ($res['poster_path'] ?? '');
                        $desc = $res['overview'] ?? '';

                        $ins_movie = $conn->prepare("INSERT INTO movies (id, title, genre, rating, image_url, description) VALUES (:id, :t, :g, :r, :i, :d)");
                        $ins_movie->execute([':id' => $data->movie_id, ':t' => $title, ':g' => $genre, ':r' => $rating, ':i' => $img, ':d' => $desc]);
                    }
                }

                // 2. Check if already in watchlist
                $check_query = "SELECT id FROM watchlist WHERE user_id = :user_id AND movie_id = :movie_id";
                $check_stmt = $conn->prepare($check_query);
                $check_stmt->bindParam(":user_id", $data->user_id);
                $check_stmt->bindParam(":movie_id", $data->movie_id);
                $check_stmt->execute();
                
                if($check_stmt->rowCount() > 0){
                    http_response_code(400);
                    echo json_encode(["message" => "Movie already in watchlist."]);
                    exit;
                }

                $query = "INSERT INTO watchlist SET user_id=:user_id, movie_id=:movie_id";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(":user_id", $data->user_id);
                $stmt->bindParam(":movie_id", $data->movie_id);

                if ($stmt->execute()) {
                    http_response_code(201);
                    echo json_encode(["message" => "Movie added to watchlist."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "Unable to add to watchlist."]);
                }
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(["message" => "Server error."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
    } elseif ($action === 'remove') {
        if (!empty($data->user_id) && !empty($data->movie_id)) {
            try {
                $query = "DELETE FROM watchlist WHERE user_id=:user_id AND movie_id=:movie_id";
                $stmt = $conn->prepare($query);
                $stmt->bindValue(":user_id", $data->user_id, PDO::PARAM_INT);
                $stmt->bindValue(":movie_id", $data->movie_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    http_response_code(200);
                    echo json_encode(["message" => "Movie removed from watchlist."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "Unable to remove."]);
                }
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(["message" => "Server error."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
    $movie_id = isset($_GET['movie_id']) ? $_GET['movie_id'] : null;

    if (!empty($user_id) && !empty($movie_id)) {
        try {
            $query = "DELETE FROM watchlist WHERE user_id=:user_id AND movie_id=:movie_id";
            $stmt = $conn->prepare($query);
            $stmt->bindValue(":user_id", $user_id, PDO::PARAM_INT);
            $stmt->bindValue(":movie_id", $movie_id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                http_response_code(200);
                echo json_encode(["message" => "Movie removed from watchlist."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to remove."]);
            }
        } catch(Exception $e) {
            http_response_code(500);
            echo json_encode(["message" => "Server error."]);
        }
    } else {
        http_response_code(400);
        echo json_encode(["message" => "Incomplete data."]);
    }
} else {
    http_response_code(404);
    echo json_encode(["message" => "Action not found."]);
}
?>
