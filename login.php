<?php
include("conexao.php");

if(isset($_POST['login'])){

    $usuario = $conn->real_escape_string(trim($_POST['usuario']));
    $senha = md5($_POST['senha']);

    $sql = $conn->query("
        SELECT * FROM usuarios
        WHERE usuario='$usuario'
        AND senha='$senha'
    ");

    if($sql && $sql->num_rows > 0){

        $dados = $sql->fetch_assoc();

        $_SESSION['id'] = $dados['id'];
        $_SESSION['nome'] = $dados['nome'];
        $_SESSION['perfil'] = $dados['perfil'];

        header("Location: painel.php");
        exit;
    }else{
        echo "Login inválido";
    }
}
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<form method="POST">
    Usuário:
    <input type="text" name="usuario">

    Senha:
    <input type="password" name="senha">

    <button name="login">Entrar</button>
</form>
<a href="javascript:history.back()">
    <button type="button">Voltar</button>
</a>