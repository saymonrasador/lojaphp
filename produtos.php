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
$isEdit = $produtoEditar !== null;
$abrirModal = $isEdit || !empty($erro);
?>
<?php $page_css = ['libs/css/crud.css']; include_once "layout_header.php"; ?>

<main class="crud-main">

<?php if ($mensagem): ?>
    <p class="msg-sucesso"><?php echo htmlspecialchars($mensagem); ?></p>
<?php endif; ?>
<?php if ($erro): ?>
    <p class="msg-erro"><?php echo htmlspecialchars($erro); ?></p>
<?php endif; ?>

<div class="crud-topbar">
    <h2>Produtos</h2>
    <div class="crud-filters">
        <select id="busca-tipo">
            <option value="nome">Nome</option>
            <option value="id">Código</option>
        </select>
        <input type="text" id="busca-valor" placeholder="Filtrar produtos...">
        <button type="button" id="btn-buscar">Buscar</button>
        <button type="button" class="btn-limpar-filtro" id="btn-limpar">Listar Todos</button>
    </div>
    <button type="button" class="btn-cadastrar" onclick="abrirModalProduto()">+ Cadastrar Produto</button>
</div>

<table class="crud-table">
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
    <?php if (empty($lista)): ?>
        <tr><td colspan="5" class="sem-dados">Nenhum produto encontrado.</td></tr>
    <?php else: ?>
        <?php foreach ($lista as $p): ?>
        <tr>
            <td><?php echo $p->getId(); ?></td>
            <td><?php echo htmlspecialchars($p->getNome()); ?></td>
            <td><?php echo htmlspecialchars($p->getDescricao()); ?></td>
            <td><?php echo $p->getFornecedor() ? htmlspecialchars($p->getFornecedor()->getNome()) : '-'; ?></td>
            <td>
                <a href="produtos.php?acao=editar&id=<?php echo $p->getId(); ?>" class="btn-acao btn-editar">Editar</a>
                <a href="produtos.php?acao=deletar&id=<?php echo $p->getId(); ?>" class="btn-acao btn-excluir"
                   onclick="return confirm('Confirma a exclusão do produto <?php echo addslashes($p->getNome()); ?>?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<!-- MODAL -->
<div class="modal-overlay" id="modalProduto">
    <div class="modal-content">
        <div class="modal-header">
            <h3><?php echo $isEdit ? 'Alterar Produto' : 'Novo Produto'; ?></h3>
            <button type="button" class="modal-close" onclick="fecharModal()">&times;</button>
        </div>
        <form method="POST" action="produtos.php">
            <div class="modal-body">
                <?php if ($produtoEditar): ?>
                    <input type="hidden" name="id" value="<?php echo $produtoEditar->getId(); ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="p_nome">Nome:</label>
                    <input type="text" id="p_nome" name="nome" required value="<?php echo htmlspecialchars($produtoEditar ? $produtoEditar->getNome() : ''); ?>">
                </div>

                <div class="form-group">
                    <label for="p_descricao">Descrição:</label>
                    <input type="text" id="p_descricao" name="descricao" value="<?php echo htmlspecialchars($produtoEditar ? $produtoEditar->getDescricao() : ''); ?>">
                </div>

                <div class="form-group">
                    <label for="p_fornecedor">Fornecedor:</label>
                    <select id="p_fornecedor" name="fornecedor_id" required>
                        <option value="">-- Selecione --</option>
                        <?php foreach ($todosFornecedores as $f): ?>
                            <option value="<?php echo $f->getId(); ?>"
                                <?php echo ($produtoEditar && $produtoEditar->getFornecedor() && $produtoEditar->getFornecedor()->getId() == $f->getId()) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($f->getNome()); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="fecharModal()">Cancelar</button>
                <button type="submit"><?php echo $isEdit ? 'Salvar Alterações' : 'Cadastrar'; ?></button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModalProduto() {
    document.getElementById('modalProduto').classList.add('ativo');
}
function fecharModal() {
    document.getElementById('modalProduto').classList.remove('ativo');
    if (window.location.search.includes('editar')) {
        window.history.replaceState({}, '', 'produtos.php');
    }
}
document.getElementById('modalProduto').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});

<?php if ($abrirModal): ?>
    abrirModalProduto();
<?php endif; ?>

// Busca AJAX
(function () {
    var inputValor  = document.getElementById('busca-valor');
    var selectTipo  = document.getElementById('busca-tipo');
    var tbody       = document.getElementById('tabela-produtos-body');
    var btnBuscar   = document.getElementById('btn-buscar');
    var btnLimpar   = document.getElementById('btn-limpar');
    var debounceTimer = null;

    function esc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function renderTabela(dados) {
        if (dados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="sem-dados">Nenhum produto encontrado.</td></tr>';
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
                    '<a href="produtos.php?acao=editar&id=' + esc(p.id) + '" class="btn-acao btn-editar">Editar</a>' +
                    '<a href="produtos.php?acao=deletar&id=' + esc(p.id) + '" class="btn-acao btn-excluir" ' +
                        'onclick="return confirm(\'Confirma a exclusão do produto ' + nomeSafe + '?\')">Excluir</a>' +
                '</td></tr>';
        }).join('');
    }

    function buscar() {
        var tipo  = selectTipo.value;
        var valor = inputValor.value.trim();
        fetch('ajax_busca_produtos.php?tipo=' + encodeURIComponent(tipo) + '&valor=' + encodeURIComponent(valor))
            .then(function (res) { return res.json(); })
            .then(function (dados) { renderTabela(dados); })
            .catch(function () {});
    }

    inputValor.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(buscar, 300);
    });
    btnBuscar.addEventListener('click', buscar);
    btnLimpar.addEventListener('click', function () {
        inputValor.value = '';
        buscar();
    });
})();
</script>
</main>
