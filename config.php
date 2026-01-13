<?php
// config.php
$host     = "localhost";       
$user     = "root";            
$password = "";                
$database = "perpustakaanku";  
$port     = 3307;              // <-- WAJIB ditambahkan

$koneksi = mysqli_connect("localhost", "root", "", "perpustakaanku", 3307);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
