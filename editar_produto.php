<?php
include("conexao.php");

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
if($id <= 0){
    die("ID inválido");
}

if(isset($_POST['editar'])){
    $nome = $conn->real_escape_string(trim($_POST['nome']));

    if($nome === ""){
        die("Nome inválido");
    }

    $conn->query("UPDATE produtos SET nome='$nome' WHERE id=$id");

    header("Location: produtos.php");
    exit;
}

$produtoQuery = $conn->query("SELECT * FROM produtos WHERE id=$id");
if(!$produtoQuery || $produtoQuery->num_rows === 0){
    die("Produto não encontrado");
}

$produto = $produtoQuery->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Produto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Editar produto</h2>
        <form method="POST">
            <label>Nome:</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($produto['nome']) ?>" required>
            <button type="submit" name="editar">Salvar</button>
        </form>
        <a href="produtos.php" class="btn-voltar">Voltar</a>
    </div>
</body>
</html>