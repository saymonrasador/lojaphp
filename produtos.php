<?php
require "verifica.php";
require "fachada.php";

$dao          = $factory->getProdutoDao();
$fornecedorDao = $factory->getFornecedorDao();
$mensagem = "";
$erro = "";
$produtoEditar = null;
$erros_campo = [];
$form_data = [];

// --- POST: Inserir ou Alterar ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id           = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $nome         = trim($_POST['nome'] ?? '');
    $descricao    = trim($_POST['descricao'] ?? '');
    $fornecedor_id = isset($_POST['fornecedor_id']) && $_POST['fornecedor_id'] !== '' ? (int)$_POST['fornecedor_id'] : null;

    $erros_validacao = [];
    if (!$nome) $erros_validacao['nome'] = "O campo Nome é obrigatório!";
    if (!$descricao) $erros_validacao['descricao'] = "O campo Descrição é obrigatório!";
    if (!$fornecedor_id) $erros_validacao['fornecedor_id'] = "Selecione um Fornecedor!";

    if (!empty($erros_validacao)) {
        $_SESSION['erros_campo_produto'] = $erros_validacao;
        $_SESSION['form_data_produto'] = ['id' => $id, 'nome' => $nome, 'descricao' => $descricao, 'fornecedor_id' => $fornecedor_id];
        if ($id !== null) {
            header("Location: produtos.php?acao=editar&id=$id");
        } else {
            header("Location: produtos.php");
        }
        exit;
    }

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
if (isset($_SESSION['erros_campo_produto'])) {
    $erros_campo = $_SESSION['erros_campo_produto'];
    unset($_SESSION['erros_campo_produto']);
}
if (isset($_SESSION['form_data_produto'])) {
    $form_data = $_SESSION['form_data_produto'];
    unset($_SESSION['form_data_produto']);
}

// Lista de fornecedores para o select
$todosFornecedores = $fornecedorDao->buscaTodos();

// Helper
function valP($campo, $produtoEditar, $form_data) {
    if (!empty($form_data) && isset($form_data[$campo])) return $form_data[$campo] ?? '';
    if ($produtoEditar) {
        switch ($campo) {
            case 'nome': return $produtoEditar->getNome();
            case 'descricao': return $produtoEditar->getDescricao();
            case 'fornecedor_id': return $produtoEditar->getFornecedor() ? $produtoEditar->getFornecedor()->getId() : '';
        }
    }
    return '';
}
$isEdit = $produtoEditar || (!empty($form_data) && isset($form_data['id']) && $form_data['id']);
?>
<?php $page_css = ['libs/css/crud.css']; include_once "layout_header.php"; ?>

<main>
<h2>Cadastro de Produtos</h2>

<p><a href="index.php">« Voltar ao Início</a></p>

<?php if ($mensagem): ?>
    <p class="msg-sucesso"><?php echo htmlspecialchars($mensagem); ?></p>
<?php endif; ?>
<?php if ($erro): ?>
    <p class="msg-erro"><?php echo htmlspecialchars($erro); ?></p>
<?php endif; ?>

<hr>
<h3><?php echo $isEdit ? 'Alterar Produto' : 'Novo Produto'; ?></h3>
<form method="POST" action="produtos.php">
    <?php if ($produtoEditar): ?>
        <input type="hidden" name="id" value="<?php echo $produtoEditar->getId(); ?>">
    <?php elseif (!empty($form_data) && $form_data['id']): ?>
        <input type="hidden" name="id" value="<?php echo (int)$form_data['id']; ?>">
    <?php endif; ?>

    <div class="form-group <?php echo isset($erros_campo['nome']) ? 'campo-erro' : ''; ?>">
        <label for="p_nome">Nome:</label>
        <input type="text" id="p_nome" name="nome" value="<?php echo htmlspecialchars(valP('nome', $produtoEditar, $form_data)); ?>">
        <?php if (isset($erros_campo['nome'])): ?>
            <span class="erro-campo"><?php echo htmlspecialchars($erros_campo['nome']); ?></span>
        <?php endif; ?>
    </div>

    <div class="form-group <?php echo isset($erros_campo['descricao']) ? 'campo-erro' : ''; ?>">
        <label for="p_descricao">Descrição:</label>
        <input type="text" id="p_descricao" name="descricao" value="<?php echo htmlspecialchars(valP('descricao', $produtoEditar, $form_data)); ?>">
        <?php if (isset($erros_campo['descricao'])): ?>
            <span class="erro-campo"><?php echo htmlspecialchars($erros_campo['descricao']); ?></span>
        <?php endif; ?>
    </div>

    <div class="form-group <?php echo isset($erros_campo['fornecedor_id']) ? 'campo-erro' : ''; ?>">
        <label for="p_fornecedor">Fornecedor:</label>
        <select id="p_fornecedor" name="fornecedor_id">
            <option value="">-- Selecione --</option>
            <?php foreach ($todosFornecedores as $f): ?>
                <option value="<?php echo $f->getId(); ?>"
                    <?php echo (valP('fornecedor_id', $produtoEditar, $form_data) == $f->getId()) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($f->getNome()); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($erros_campo['fornecedor_id'])): ?>
            <span class="erro-campo"><?php echo htmlspecialchars($erros_campo['fornecedor_id']); ?></span>
        <?php endif; ?>
    </div>

    <button type="submit"><?php echo $isEdit ? 'Salvar Alterações' : 'Cadastrar'; ?></button>
    <?php if ($produtoEditar): ?>
        <a href="produtos.php"><button type="button">Cancelar</button></a>
    <?php endif; ?>
</form>

<hr>
<h3>Consultar Produtos</h3>
<form method="GET" action="produtos.php">
    <input type="hidden" name="acao" value="buscar">
    <label>Buscar por:
        <select name="tipo">
            <option value="nome">Nome</option>
            <option value="id">Código</option>
        </select>
    </label>
    <input type="text" name="valor" placeholder="Digite o valor" required>
    <button type="submit">Buscar</button>
    <a href="produtos.php"><button type="button">Listar Todos</button></a>
</form>

<hr>
<h3>Lista de Produtos <?php echo $buscaAtiva ? '(resultado da busca)' : ''; ?></h3>
<?php if (empty($lista)): ?>
    <p>Nenhum produto encontrado.</p>
<?php else: ?>
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
        <tbody>
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
        </tbody>
    </table>
<?php endif; ?>
</main>
