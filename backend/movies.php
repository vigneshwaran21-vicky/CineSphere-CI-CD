<?php


error_reporting(0);
ini_set('display_errors', 0);

$action = isset($_GET['action']) ? $_GET['action'] : 'all';

function fetchFromTMDB($endpoint) {
    // Official public tutorial API key widely used for unblocked academic testing
    $apiKey = '3fd2be6f0c70a2a598f084ddfb75487c'; 
    $url = "https://api.themoviedb.org/3" . $endpoint . (strpos($endpoint, '?') !== false ? "&" : "?") . "api_key=" . $apiKey;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if(curl_errno($ch)) return null;
    // curl_close is deprecated in newer PHP versions and not needed for CurlHandle objects
    return json_decode($response, true);
}

function mapTmdbMovie($m) {
    return [
        "id" => $m['id'],
        "title" => $m['title'] ?? $m['name'] ?? 'Unknown',
        "genre" => "Feature Film", 
        "rating" => isset($m['vote_average']) ? round($m['vote_average'], 1) : 0,
        "description" => $m['overview'] ?? 'No description available.',
        "image_url" => isset($m['poster_path']) && $m['poster_path'] ? 'https://image.tmdb.org/t/p/w500' . $m['poster_path'] : 'https://via.placeholder.com/300x450?text=No+Poster'
    ];
}

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'all') {
        $tmdb_popular = fetchFromTMDB('/movie/popular');
        
        if($tmdb_popular && isset($tmdb_popular['results'])) {
            $mapped = array_map('mapTmdbMovie', $tmdb_popular['results']);
            echo json_encode($mapped);
        } else {
            echo json_encode([]);
        }
    } elseif ($action === 'search') {
        $searchTerm = isset($_GET['q']) ? urlencode($_GET['q']) : '';
        if(!empty($searchTerm)){
            $res = fetchFromTMDB('/search/movie?query=' . $searchTerm);
            if($res && isset($res['results'])) {
                $mapped = array_map('mapTmdbMovie', $res['results']);
                echo json_encode($mapped);
            } else {
                echo json_encode([]);
            }
        } else {
            echo json_encode([]);
        }
    } elseif ($action === 'detail') {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        if($id){
            $res = fetchFromTMDB('/movie/' . $id);
            if($res && !isset($res['status_code'])) {
                $mapped = mapTmdbMovie($res);
                if(isset($res['genres']) && is_array($res['genres']) && count($res['genres']) > 0) {
                    $mapped['genre'] = implode(', ', array_column($res['genres'], 'name'));
                }
                echo json_encode($mapped);
            } else {
                http_response_code(404);
                echo json_encode(["message" => "Movie not found inside TMDB."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Incomplete request."]);
        }
    } else {
        http_response_code(404);
        echo json_encode(["message" => "Action not found."]);
    }
}
?>
