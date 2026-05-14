<?php
$koneksi = mysqli_connect ('localhost','root','','lib');
if (!$koneksi) {
    die("Connection failed: " . mysqli_connect_error());
}
?>