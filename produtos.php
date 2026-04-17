<?php
require "verifica.php";
require "fachada.php";

$dao          = $factory->getProdutoDao();
$fornecedorDao = $factory->getFornecedorDao();
$mensagem = "";
$erro = "";
$produtoEditar = null;

// --- POST: Inserir ou Alterar ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id           = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $nome         = trim($_POST['nome'] ?? '');
    $descricao    = trim($_POST['descricao'] ?? '');
    $fornecedor_id = isset($_POST['fornecedor_id']) && $_POST['fornecedor_id'] !== '' ? (int)$_POST['fornecedor_id'] : null;

    if (!$nome || !$fornecedor_id) {
        $_SESSION['erro_produto'] = "Nome e Fornecedor são obrigatórios!";
    } else {
        $fornecedor = $fornecedorDao->buscaPorId($fornecedor_id);
        if (!$fornecedor) {
            $_SESSION['erro_produto'] = "Fornecedor não encontrado.";
        } else {
            if ($id === null) {
                $produto = new Produto(null, $nome, $descricao, null, $fornecedor);
                if ($dao->insere($produto)) {
                    $_SESSION['mensagem_produto'] = "Produto cadastrado com sucesso!";
                } else {
                    $_SESSION['erro_produto'] = "Erro ao cadastrar produto.";
                }
            } else {
                $produto = $dao->buscaPorId($id);
                if ($produto) {
                    $produto->setNome($nome);
                    $produto->setDescricao($descricao);
                    $produto->setFornecedor($fornecedor);
                    if ($dao->altera($produto)) {
                        $_SESSION['mensagem_produto'] = "Produto alterado com sucesso!";
                    } else {
                        $_SESSION['erro_produto'] = "Erro ao alterar produto.";
                    }
                } else {
                    $_SESSION['erro_produto'] = "Produto não encontrado.";
                }
            }
        }
    }
    header("Location: produtos.php");
    exit;
}

// --- GET: Deletar ---
if (isset($_GET['acao']) && $_GET['acao'] === 'deletar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($dao->removePorId($id)) {
        $_SESSION['mensagem_produto'] = "Produto excluído com sucesso!";
    } else {
        $_SESSION['erro_produto'] = "Erro ao excluir produto.";
    }
    header("Location: produtos.php");
    exit;
}

// --- GET: Editar ---
if (isset($_GET['acao']) && $_GET['acao'] === 'editar' && isset($_GET['id'])) {
    $produtoEditar = $dao->buscaPorId((int)$_GET['id']);
}

// --- Busca ou listagem ---
$lista = [];
$buscaAtiva = false;
if (isset($_GET['acao']) && $_GET['acao'] === 'buscar') {
    $buscaAtiva = true;
    $tipo  = $_GET['tipo'] ?? 'nome';
    $valor = trim($_GET['valor'] ?? '');
    if ($tipo === 'id') {
        $found = $dao->buscaPorId((int)$valor);
        $lista = $found ? [$found] : [];
    } else {
        $lista = $dao->buscaPorNome($valor);
    }
} else {
    $lista = $dao->buscaTodos();
}

// Mensagens da sessão
if (isset($_SESSION['mensagem_produto'])) {
    $mensagem = $_SESSION['mensagem_produto'];
    unset($_SESSION['mensagem_produto']);
}
if (isset($_SESSION['erro_produto'])) {
    $erro = $_SESSION['erro_produto'];
    unset($_SESSION['erro_produto']);
}

// Lista de fornecedores para o select
$todosFornecedores = $fornecedorDao->buscaTodos();
?>
<?php $page_css = ['libs/css/crud.css']; include_once "layout_header.php"; ?>

<main>
<h2>Cadastro de Produtos</h2>

<p><a href="index.php">« Voltar ao Início</a></p>

<?php if ($mensagem): ?>
    <p><strong><?php echo htmlspecialchars($mensagem); ?></strong></p>
<?php endif; ?>
<?php if ($erro): ?>
    <p><strong>Erro: <?php echo htmlspecialchars($erro); ?></strong></p>
<?php endif; ?>

<hr>
<h3><?php echo $produtoEditar ? 'Alterar Produto' : 'Novo Produto'; ?></h3>
<form method="POST" action="produtos.php">
    <?php if ($produtoEditar): ?>
        <input type="hidden" name="id" value="<?php echo $produtoEditar->getId(); ?>">
    <?php endif; ?>

    <label>Nome: <input type="text" name="nome" value="<?php echo htmlspecialchars($produtoEditar ? $produtoEditar->getNome() : ''); ?>" required></label><br><br>
    <label>Descrição: <input type="text" name="descricao" value="<?php echo htmlspecialchars($produtoEditar ? $produtoEditar->getDescricao() : ''); ?>"></label><br><br>
    <label>Fornecedor:
        <select name="fornecedor_id" required>
            <option value="">-- Selecione --</option>
            <?php foreach ($todosFornecedores as $f): ?>
                <option value="<?php echo $f->getId(); ?>"
                    <?php echo ($produtoEditar && $produtoEditar->getFornecedor() && $produtoEditar->getFornecedor()->getId() == $f->getId()) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($f->getNome()); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <button type="submit"><?php echo $produtoEditar ? 'Salvar Alterações' : 'Cadastrar'; ?></button>
    <?php if ($produtoEditar): ?>
        <a href="produtos.php"><button type="button">Cancelar</button></a>
    <?php endif; ?>
</form>

<hr>
<h3>Consultar Produtos</h3>
<div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:8px;">
    <label>Buscar por:
        <select id="busca-tipo">
            <option value="nome">Nome</option>
            <option value="id">Código</option>
        </select>
    </label>
    <input type="text" id="busca-valor" placeholder="Digite para filtrar..." style="min-width:200px;">
    <button type="button" id="btn-limpar">Listar Todos</button>
    <span id="busca-status" style="color:#888; font-size:0.9em;"></span>
</div>

<hr>
<h3>Lista de Produtos</h3>
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Descrição</th>
            <th>Fornecedor</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody id="tabela-produtos-body">
    <?php foreach ($lista as $p): ?>
        <tr>
            <td><?php echo $p->getId(); ?></td>
            <td><?php echo htmlspecialchars($p->getNome()); ?></td>
            <td><?php echo htmlspecialchars($p->getDescricao()); ?></td>
            <td><?php echo $p->getFornecedor() ? htmlspecialchars($p->getFornecedor()->getNome()) : '-'; ?></td>
            <td>
                <a href="produtos.php?acao=editar&id=<?php echo $p->getId(); ?>">Editar</a>
                |
                <a href="produtos.php?acao=deletar&id=<?php echo $p->getId(); ?>"
                   onclick="return confirm('Confirma a exclusão do produto <?php echo addslashes($p->getNome()); ?>?')">Excluir</a>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($lista)): ?>
        <tr><td colspan="5">Nenhum produto encontrado.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<script>
(function () {
    var inputValor  = document.getElementById('busca-valor');
    var selectTipo  = document.getElementById('busca-tipo');
    var tbody       = document.getElementById('tabela-produtos-body');
    var btnLimpar   = document.getElementById('btn-limpar');
    var statusEl    = document.getElementById('busca-status');
    var debounceTimer = null;

    function esc(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function renderTabela(dados) {
        if (dados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5">Nenhum produto encontrado.</td></tr>';
            return;
        }
        tbody.innerHTML = dados.map(function (p) {
            var nomeSafe = esc(p.nome).replace(/'/g, "\\'");
            return '<tr>' +
                '<td>' + esc(p.id) + '</td>' +
                '<td>' + esc(p.nome) + '</td>' +
                '<td>' + esc(p.descricao) + '</td>' +
                '<td>' + esc(p.fornecedor) + '</td>' +
                '<td>' +
                    '<a href="produtos.php?acao=editar&id=' + esc(p.id) + '">Editar</a>' +
                    ' | ' +
                    '<a href="produtos.php?acao=deletar&id=' + esc(p.id) + '" ' +
                        'onclick="return confirm(\'Confirma a exclusão do produto ' + nomeSafe + '?\')">Excluir</a>' +
                '</td>' +
            '</tr>';
        }).join('');
    }

    function buscar() {
        var tipo  = selectTipo.value;
        var valor = inputValor.value.trim();
        statusEl.textContent = 'Buscando...';

        fetch('ajax_busca_produtos.php?tipo=' + encodeURIComponent(tipo) + '&valor=' + encodeURIComponent(valor))
            .then(function (res) { return res.json(); })
            .then(function (dados) {
                statusEl.textContent = '';
                renderTabela(dados);
            })
            .catch(function () {
                statusEl.textContent = 'Erro ao buscar.';
            });
    }

    // Dispara automaticamente ao digitar (debounce de 300ms)
    inputValor.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(buscar, 300);
    });

    // Refaz a busca ao trocar o tipo
    selectTipo.addEventListener('change', function () {
        if (inputValor.value.trim() !== '') {
            buscar();
        }
    });

    // Limpa e lista todos
    btnLimpar.addEventListener('click', function () {
        inputValor.value = '';
        buscar();
    });
})();
</script>
</main>
