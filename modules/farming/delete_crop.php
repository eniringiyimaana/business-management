<?php
require_once 'db_config.php'; // Include the database connection

if (isset($_GET['id'])) {
    $crop_id = $_GET['id'];

    // Delete the crop from the database
    $query = "DELETE FROM crops WHERE crop_id = $crop_id";

    if (mysqli_query($conn, $query)) {
        header('Location: crops.php'); // Redirect to the main page after deletion
    } else {
        echo 'Error deleting crop: ' . mysqli_error($conn);
    }
}

// Close the database connection
mysqli_close($conn);
?>
