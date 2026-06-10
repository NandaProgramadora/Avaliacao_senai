<?php
include("conexao.php");

if(!isset($_SESSION['id']) || $_SESSION['perfil'] != 'admin'){
    die("Acesso negado");
}

$id = intval($_GET['id'] ?? 0);
if($id <= 0){
    die("ID inválido");
}

$conn->query("DELETE FROM movimentacoes WHERE usuario_id=$id");

if(!$conn->query("DELETE FROM usuarios WHERE id=$id")){
    die("Erro ao excluir usuário");
}

header("Location: usuarios.php");
exit;