<?php
// PHPA1JDavis.php - Google Books

// Load variables from .env
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Fetch API key from environment variables
$apiKey = isset($_ENV['Google_Books_API_Key']) ? $_ENV['Google_Books_API_Key'] : '';

// Check if API key exists
if (empty($apiKey)) {
    echo json_encode(['error' => 'API key not configured']);
    exit;
}

// Header for JSON response
header('Content-Type: application/json');

// Google Books API base URL
$apiUrl = 'https://www.googleapis.com/books/v1/volumes';

// Get search parameters from request
$title = isset($_GET['title']) ? $_GET['title'] : '';
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$author = isset($_GET['author']) ? $_GET['author'] : '';
$pageMin = isset($_GET['pageMin']) ? $_GET['pageMin'] : '';
$pageMax = isset($_GET['pageMax']) ? $_GET['pageMax'] : '';

//search query
$queryParts = array();

// Add title
if (!empty($title)) {
    $queryParts[] = urlencode($title);
}

// Add genre
if (!empty($genre)) {
    $queryParts[] = 'subject:' . urlencode($genre);
}

// Add author
if (!empty($author)) {
    $queryParts[] = 'inauthor:' . urlencode($author);
}

// Check if min one search parameter is provided
if (empty($queryParts)) {
    echo json_encode(['error' => 'At least one search criteria is required']);
    exit;
}

// Combine query parts
$query = implode('+', $queryParts);

// Build entire API URL
$fullUrl = $apiUrl . '?q=' . $query . '&maxResults=20&key=' . $apiKey;

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $fullUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

// Execute API request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Confirm request was successful
if ($httpCode !== 200 || $response === false) {
    echo json_encode(['error' => 'Failed to fetch data from Google Books API']);
    exit;
}

// Decode JSON response
$data = json_decode($response, true);

// Filter by page count if specified
if (!empty($pageMin) || !empty($pageMax)) {
    if (isset($data['items'])) {
        $filteredItems = array();

        foreach ($data['items'] as $item) {
            if (isset($item['volumeInfo']['pageCount'])) {
                $pageCount = $item['volumeInfo']['pageCount'];

                // Check page min
                if (!empty($pageMin) && $pageCount < intval($pageMin)) {
                    continue;
                }

                // Check page max
                if (!empty($pageMax) && $pageCount > intval($pageMax)) {
                    continue;
                }

                $filteredItems[] = $item;
            }
        }

        $data['items'] = $filteredItems;
    }
}

// Return JSON response
echo json_encode($data);
?>