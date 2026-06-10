<?php
include("conexao.php");

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit;
}
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<div class="container">

    <h2>Bem-vindo <?= $_SESSION['nome'] ?></h2>

    <p style="text-align:center; margin-bottom:20px;">
        Perfil: <?= $_SESSION['perfil'] ?>
    </p>

    <div class="nav">
        <a href="usuarios.php">Usuários</a>
        <a href="produtos.php">Produtos</a>
        <a href="logout.php" class="danger">Sair</a>
    </div>

    <section class="audit-section">
        <h3>Auditoria de movimentações</h3>
        <?php
        $audit = $conn->query(
            "SELECT m.data_hora, u.nome AS usuario, p.nome AS produto, m.tipo, m.quantidade
            FROM movimentacoes m
            LEFT JOIN usuarios u ON m.usuario_id = u.id
            LEFT JOIN produtos p ON m.produto_id = p.id
            ORDER BY m.data_hora DESC
            LIMIT 10"
        );
        if($audit && $audit->num_rows > 0):
            while($row = $audit->fetch_assoc()):
                $date = date('d/m/Y', strtotime($row['data_hora']));
                $time = date('H:i', strtotime($row['data_hora']));
                $action = $row['tipo'] === 'saida' ? 'Saída de' : 'Entrada de';
                $label = $action . ' ' . intval($row['quantidade']) . ' ' . htmlspecialchars($row['produto']);
        ?>
        <div class="audit-card">
            <div class="audit-date"><?= $date ?></div>
            <div class="audit-time"><?= $time ?></div>
            <div class="audit-user"><?= htmlspecialchars($row['usuario']) ?></div>
            <div class="audit-description"><?= $label ?></div>
        </div>
        <?php
            endwhile;
        else:
        ?>
        <div class="audit-empty">Nenhuma movimentação registrada ainda.</div>
        <?php endif; ?>
    </section>

</div>