<?php
require_once 'db_config.php'; // Include the database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $crop_name = $_POST['crop_name'];
    $planting_date = $_POST['planting_date'];
    $harvest_date = $_POST['harvest_date'];
    $yield_quantity = $_POST['yield_quantity'];

    // Insert crop data into the database
    $query = "INSERT INTO crops (crop_name, planting_date, harvest_date, yield_quantity) 
              VALUES ('$crop_name', '$planting_date', '$harvest_date', '$yield_quantity')";

    if (mysqli_query($conn, $query)) {
        header('Location: crops.php'); // Redirect to the main page after successful insertion
    } else {
        echo 'Error adding crop: ' . mysqli_error($conn);
    }
}

// Close the database connection
mysqli_close($conn);
?>
