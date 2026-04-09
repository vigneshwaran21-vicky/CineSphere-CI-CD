<?php
// TMDB API Configuration
// Replace this placeholder with your actual TMDB API Key!
// Get a free key at https://www.themoviedb.org/documentation/api
define('TMDB_API_KEY', 'YOUR_TMDB_API_KEY_HERE');
define('TMDB_BASE_URL', 'https://api.themoviedb.org/3');

function fetchFromTMDB($endpoint) {
    $url = TMDB_BASE_URL . $endpoint;
    // Add API key correctly
    if (strpos($url, '?') !== false) {
        $url .= '&api_key=' . TMDB_API_KEY;
    } else {
        $url .= '?api_key=' . TMDB_API_KEY;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        return json_decode($response, true);
    }
    return null;
}
?>
