<?php
include("conexao.php");

if(!isset($_SESSION['id'])){
    header("Location: login.php");
    exit;
}

$filtro = "";
$nome = "";
$data = "";

if(isset($_GET['nome'])){
    $nome = $conn->real_escape_string(trim($_GET['nome']));
    if($nome !== ""){
        $filtro = "WHERE nome LIKE '%$nome%'";
    }
}

if(isset($_GET['data'])){
    $data = $conn->real_escape_string(trim($_GET['data']));
    if($data !== ""){
        $filtro .= ($filtro === "" ? "WHERE " : " AND ") . "DATE(data_cadastro) = '$data'";
    }
}

if(isset($_POST['cadastrar'])){

    $nome = $conn->real_escape_string(trim($_POST['nome']));

    if($nome !== ""){
        $conn->query("
    INSERT INTO produtos(nome,quantidade)
    VALUES('$nome',0)
    ");
    }
}

if(isset($_POST['movimentar'])){

    $id = intval($_POST['id']);
    $tipo = $_POST['tipo'] === 'saida' ? 'saida' : 'entrada';
    $qtd = intval($_POST['qtd']);

    if($id <= 0 || $qtd <= 0){
        die("Dados inválidos");
    }

    $produtoQuery = $conn->query("
    SELECT * FROM produtos WHERE id=$id
    ");

    if(!$produtoQuery || $produtoQuery->num_rows === 0){
        die("Produto não encontrado");
    }

    $produto = $produtoQuery->fetch_assoc();

    if($tipo == "saida"){

        if($produto['quantidade'] < $qtd){
            die("Estoque insuficiente");
        }

        $novo = $produto['quantidade'] - $qtd;

    }else{

        $novo = $produto['quantidade'] + $qtd;
    }

    $conn->query("
    UPDATE produtos
    SET quantidade=$novo
    WHERE id=$id
    ");

    $usuario = intval($_SESSION['id']);

    $conn->query("
    INSERT INTO movimentacoes
    (produto_id,usuario_id,tipo,quantidade,data_hora)
    VALUES
    ($id,$usuario,'$tipo',$qtd,NOW())
    ");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <form method="GET" class="search-filter-form">
            <label>Pesquisar:</label>
            <input name="nome" placeholder="Buscar produto">
            <input type="date" name="data">
            <button>Buscar</button>
        </form>

        <form method="POST">
            <label>Produto:</label>
            <input name="nome" placeholder="Nome do produto">
            <button name="cadastrar">Cadastrar</button>
        </form>

        <section class="status-panel" id="global-status-panel">
            <div class="status-panel-title">
                <h2>Controle de estoque</h2>
            </div>
            <div class="status-panel-grid">
                <div class="status-item">
                    <span>Produto</span>
                    <strong id="status-produto">Selecione um produto</strong>
                </div>
                <div class="status-item">
                    <span>Estoque atual</span>
                    <strong id="status-current">0</strong>
                </div>
                <div class="status-item">
                    <span>Entrada</span>
                    <strong id="status-entrada">0</strong>
                </div>
                <div class="status-item">
                    <span>Saída</span>
                    <strong id="status-saida">0</strong>
                </div>
                <div class="status-item">
                    <span>Novo estoque</span>
                    <strong id="status-novo">0</strong>
                </div>
            </div>
            <p class="status-note">Não pode deixar estoque negativo.</p>
        </section>

        <?php
        $produtos = $conn->query("SELECT * FROM produtos $filtro");

        while($p = $produtos->fetch_assoc()){
            ?>
            <div class="produto-card" data-current="<?= intval($p['quantidade']) ?>" data-name="<?= htmlspecialchars($p['nome'], ENT_QUOTES) ?>">
                <p><strong><?= htmlspecialchars($p['nome']) ?></strong> - Estoque: <?= htmlspecialchars($p['quantidade']) ?></p>
                <?php if($_SESSION['perfil'] == 'admin'){ ?>
                    <a href="excluir_produto.php?id=<?= intval($p['id']) ?>">Excluir</a>
                <?php } ?>
                <a href="editar_produto.php?id=<?= intval($p['id']) ?>">Editar</a>

                <form method="POST" class="movimentacao-form" data-produto-id="<?= intval($p['id']) ?>">
                    <input type="hidden" name="id" value="<?= intval($p['id']) ?>">
                    <select name="tipo" class="tipo-select">
                        <option value="entrada">Entrada</option>
                        <option value="saida">Saída</option>
                    </select>
                    <input type="number" name="qtd" class="qtd-input" min="1" value="0" required>
                    <button name="movimentar">Movimentar</button>
                </form>
            </div>
        <hr>

        <?php
        }
        ?>

        <a href="painel.php" class="btn-voltar">Voltar</a>
    </div>

    <script>
        var statusPanel = document.getElementById('global-status-panel');
        var statusProduto = document.getElementById('status-produto');
        var statusCurrent = document.getElementById('status-current');
        var statusEntrada = document.getElementById('status-entrada');
        var statusSaida = document.getElementById('status-saida');
        var statusNovo = document.getElementById('status-novo');

        function updateGlobalStatus(card) {
            if(!card) return;

            var currentValue = parseInt(card.dataset.current, 10) || 0;
            var productName = card.dataset.name || 'Produto';
            var tipoSelect = card.querySelector('.tipo-select');
            var qtdInput = card.querySelector('.qtd-input');
            var tipo = tipoSelect.value;
            var qtd = parseInt(qtdInput.value, 10) || 0;
            var entrada = tipo === 'entrada' ? qtd : -qtd;
            var novo = currentValue + entrada;

            statusProduto.textContent = productName;
            statusCurrent.textContent = currentValue;
            statusEntrada.textContent = tipo === 'entrada' ? qtd : 0;
            statusSaida.textContent = tipo === 'saida' ? qtd : 0;
            statusNovo.textContent = novo;

            statusPanel.classList.toggle('status-negative', novo < 0);
        }

        var activeCard = null;
        var cards = document.querySelectorAll('.produto-card');

        cards.forEach(function(card, index) {
            var form = card.querySelector('.movimentacao-form');
            var tipoSelect = form.querySelector('.tipo-select');
            var qtdInput = form.querySelector('.qtd-input');

            function selectCard() {
                activeCard = card;
                updateGlobalStatus(card);
            }

            card.addEventListener('click', selectCard);
            form.addEventListener('focusin', selectCard);
            tipoSelect.addEventListener('change', function() {
                if(activeCard === card) updateGlobalStatus(card);
            });
            qtdInput.addEventListener('input', function() {
                if(activeCard === card) updateGlobalStatus(card);
            });

            if(index === 0) {
                activeCard = card;
                updateGlobalStatus(card);
            }
        });
    </script>
</body>
</html>