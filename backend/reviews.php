<?php
require_once 'db.php';
header("Content-Type: application/json; charset=UTF-8");

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'movie') {
        $movie_id = isset($_GET['movie_id']) ? $_GET['movie_id'] : null;
        if($movie_id){
            try {
                $query = "SELECT r.id, r.comment, r.rating, r.created_at, u.name as user_name 
                          FROM reviews r 
                          JOIN users u ON r.user_id = u.id 
                          WHERE r.movie_id = :movie_id
                          ORDER BY r.created_at DESC";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(":movie_id", $movie_id);
                $stmt->execute();
    
                $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($reviews);
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(["message" => "Server error."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Movie ID required."]);
        }
    } elseif ($action === 'user') {
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        if($user_id){
            try {
                $query = "SELECT id, movie_id, comment, rating, created_at 
                          FROM reviews 
                          WHERE user_id = :user_id
                          ORDER BY created_at DESC";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(":user_id", $user_id);
                $stmt->execute();
    
                $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($reviews);
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
    
    if ($action === 'add' || $action === 'update') {
        if (!empty($data->user_id) && !empty($data->movie_id) && !empty($data->comment)) {
            try {
                if ($action === 'add') {
                    // 1. Ensure movie exists in movies table (cache it from TMDB if not)
                    $movie_check = $conn->prepare("SELECT id FROM movies WHERE id = :id");
                    $movie_check->execute([':id' => $data->movie_id]);
                    
                    if ($movie_check->rowCount() === 0) {
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

                    $query = "INSERT INTO reviews SET user_id=:user_id, movie_id=:movie_id, comment=:comment, rating=:rating";
                } else {
                    $query = "UPDATE reviews SET comment=:comment, rating=:rating 
                              WHERE user_id=:user_id AND movie_id=:movie_id";
                }

                $stmt = $conn->prepare($query);
                
                $comment = htmlspecialchars(strip_tags($data->comment));
                $rating = isset($data->rating) ? (int)$data->rating : 0;

                $stmt->bindParam(":user_id", $data->user_id);
                $stmt->bindParam(":movie_id", $data->movie_id);
                $stmt->bindParam(":comment", $comment);
                $stmt->bindParam(":rating", $rating);

                if ($stmt->execute()) {
                    $msg = ($action === 'add') ? "Review added." : "Review updated.";
                    http_response_code(($action === 'add') ? 201 : 200);
                    echo json_encode(["message" => $msg]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "Unable to perform action."]);
                }
            } catch(Exception $e) {
                http_response_code(500);
                echo json_encode(["message" => "Server error: " . $e->getMessage()]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete data."]);
        }
    }
} else {
    http_response_code(404);
    echo json_encode(["message" => "Action not found."]);
}
?>
