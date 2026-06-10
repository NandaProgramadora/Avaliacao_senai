<?php
include("conexao.php");

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit;
}

if($_SESSION['perfil'] != 'admin'){
    die("Acesso negado");
}

if(isset($_POST['salvar'])){

    $nome = $conn->real_escape_string(trim($_POST['nome']));
    $usuario = $conn->real_escape_string(trim($_POST['usuario']));
    $senha = md5($_POST['senha']);
    $perfil = $conn->real_escape_string($_POST['perfil']);

    if($nome !== "" && $usuario !== ""){
        $conn->query("
        INSERT INTO usuarios(nome,usuario,senha,perfil)
        VALUES('$nome','$usuario','$senha','$perfil')
    ");
    }
}
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<form method="POST">

Nome:
<input name="nome">

Usuário:
<input name="usuario">

Senha:
<input type="password" name="senha">

Perfil:
<select name="perfil">
    <option value="admin">Admin</option>
    <option value="operador">Operador</option>
</select>

<button name="salvar">
Cadastrar
</button>

</form>

<hr>
<a href="javascript:history.back()" class="btn-voltar">Voltar</a>

<?php

$res = $conn->query("SELECT * FROM usuarios");

while($u = $res->fetch_assoc()){

    echo $u['nome']." - ".$u['perfil'];

    echo "
    <a href='excluir_usuario.php?id=".$u['id']."'>
    Excluir
    </a><br>";
}
?>