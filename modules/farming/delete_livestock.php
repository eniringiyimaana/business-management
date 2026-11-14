<?php
include 'db_config.php'; 

if (isset($_GET['id'])) {
    $livestock_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Check if record exists
    $checkQuery = "SELECT * FROM livestock WHERE livestock_id = '$livestock_id'";
    $checkResult = mysqli_query($conn, $checkQuery);

    if (mysqli_num_rows($checkResult) > 0) {
        // Delete record
        $deleteQuery = "DELETE FROM livestock WHERE livestock_id = '$livestock_id'";
        if (mysqli_query($conn, $deleteQuery)) {
            echo "<script>alert('Livestock deleted successfully!'); window.location.href='livestock.php';</script>";
        } else {
            echo "Error deleting record: " . mysqli_error($conn);
        }
    } else {
        echo "<script>alert('Error: Livestock record not found!'); window.location.href='livestock.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request!'); window.location.href='livestock.php';</script>";
}
?>
