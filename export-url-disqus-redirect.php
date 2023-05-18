<?php
require __DIR__ . '/wp-load.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prompt for the post IDs
echo "Enter the post IDs (comma-separated): ";
$postIDsInput = trim(fgets(STDIN));
$postIDs = explode(',', $postIDsInput);

// Prompt for the new slug option
echo "Enter the new slug option: ";
$newSlugOption = trim(fgets(STDIN));

// Construct the base URL by wp-config.php
$baseURL = get_option('siteurl') . '/manga/';

// Construct the SQL query
$query = "
    SELECT
        manga.post_name AS manga_slug,
        chapter.chapter_slug AS chapter_slug
    FROM
        wp_posts AS manga
    INNER JOIN
        wp_manga_chapters AS chapter ON chapter.post_id = manga.ID
    WHERE
        manga.post_type = 'wp-manga'
        AND chapter.chapter_status = 1";

// Check if post IDs are provided
if (!empty($postIDs)) {
    $postIDs = implode(',', $postIDs); // Convert the array of post IDs to a comma-separated string
    $query .= " AND manga.ID IN ({$postIDs})";
}

// Execute the SQL query
$results = $wpdb->get_results($query, ARRAY_A);

// Define the CSV file path
$csvFilePath = "export.csv";

// Open the CSV file for writing
$csvFile = fopen($csvFilePath, 'w');

// Write the headers to the CSV file
// fputcsv($csvFile, ["Manga Slug", "New Manga Slug"]);

// Initialize variables to store previous values
$previousMangaSlug = '';

// Loop through the result set
foreach ($results as $result) {
    // Extract the manga slug and chapter slug
    $mangaSlug = $baseURL . $result['manga_slug'];
    $chapterSlug = $baseURL . $result['manga_slug'] . '/' . $result['chapter_slug'];

    // Construct the new manga URL and new chapter URL
    $newMangaSlug = $mangaSlug . $newSlugOption;
    $newChapterSlug = $baseURL . $result['manga_slug'] . $newSlugOption . '/' . $result['chapter_slug'];

    // Check if the current manga slug is the same as the previous one
    if ($mangaSlug !== $previousMangaSlug) {
        // Write the data to the CSV file
        fputcsv($csvFile, [$mangaSlug, $newMangaSlug]);
    }

    // Write the data to the CSV file
    fputcsv($csvFile, [$chapterSlug, $newChapterSlug]);

    // Update the previous values
    $previousMangaSlug = $mangaSlug;
}

// Close the CSV file
fclose($csvFile);

echo "Export completed. The results have been saved to {$csvFilePath}\n";
