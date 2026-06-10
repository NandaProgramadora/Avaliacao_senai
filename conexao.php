<?php
$conn = new mysqli("localhost","root","","estoque", 3307);

if($conn->connect_error){
    die("Erro na conexão");
}

session_start();
?>