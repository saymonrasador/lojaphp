<?php
require "verifica.php";
require "fachada.php";

$estoqueDao  = $factory->getEstoqueDao();
$produtoDao  = $factory->getProdutoDao();
$mensagem = "";
$erro = "";
$estoqueEditar = null;
$erros_campo = [];
$form_data = [];

// --- POST: Inserir ou Alterar ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $produto_id  = isset($_POST['produto_id']) && $_POST['produto_id'] !== '' ? (int)$_POST['produto_id'] : null;
    $quantidade  = trim($_POST['quantidade'] ?? '');
    $preco       = trim($_POST['preco'] ?? '');
    $operacao    = $_POST['operacao'] ?? 'definir'; // 'definir', 'entrada', 'saida'

    $erros_validacao = [];
    if (!$produto_id) $erros_validacao['produto_id'] = "Selecione um Produto!";
    if ($quantidade === '') $erros_validacao['quantidade'] = "O campo Quantidade é obrigatório!";
    elseif (!is_numeric($quantidade) || (int)$quantidade < 0) $erros_validacao['quantidade'] = "Quantidade deve ser um número inteiro positivo!";
    if ($preco === '') $erros_validacao['preco'] = "O campo Preço é obrigatório!";
    elseif (!is_numeric($preco) || (float)$preco < 0) $erros_validacao['preco'] = "Preço deve ser um valor numérico positivo!";

    if (!empty($erros_validacao)) {
        $_SESSION['erros_campo_estoque'] = $erros_validacao;
        $_SESSION['form_data_estoque'] = ['id' => $id, 'produto_id' => $produto_id, 'quantidade' => $quantidade, 'preco' => $preco];
        if ($id !== null) {
            header("Location: estoque.php?acao=editar&id=$id");
        } else {
            header("Location: estoque.php");
        }
        exit;
    }

    $quantidade = (int)$quantidade;
    $preco = (float)$preco;

    if ($id === null) {
        // Verifica se já existe estoque para esse produto
        $existente = $estoqueDao->buscaPorProduto($produto_id);
        if ($existente) {
            $_SESSION['erros_campo_estoque'] = ['produto_id' => 'Já existe um registro de estoque para este produto! Use Editar para alterá-lo.'];
            $_SESSION['form_data_estoque'] = ['id' => null, 'produto_id' => $produto_id, 'quantidade' => $quantidade, 'preco' => $preco];
            header("Location: estoque.php");
            exit;
        }
        $estoque = new Estoque(null, $produto_id, $quantidade, $preco);
        if ($estoqueDao->insere($estoque)) {
            $_SESSION['mensagem_estoque'] = "Estoque cadastrado com sucesso!";
        } else {
            $_SESSION['erro_estoque'] = "Erro ao cadastrar estoque.";
        }
    } else {
        $estoque = $estoqueDao->buscaPorId($id);
        if ($estoque) {
            if ($operacao === 'entrada') {
                $novaQtd = $estoque->getQuantidade() + $quantidade;
                $estoque->setQuantidade($novaQtd);
            } elseif ($operacao === 'saida') {
                if ($quantidade > $estoque->getQuantidade()) {
                    $_SESSION['erro_estoque'] = "Quantidade de saída ($quantidade) maior que o estoque atual ({$estoque->getQuantidade()})!";
                    header("Location: estoque.php");
                    exit;
                }
                $novaQtd = $estoque->getQuantidade() - $quantidade;
                $estoque->setQuantidade($novaQtd);
            } else {
                $estoque->setQuantidade($quantidade);
            }
            $estoque->setProdutoId($produto_id);
            $estoque->setPreco($preco);
            if ($estoqueDao->altera($estoque)) {
                $_SESSION['mensagem_estoque'] = "Estoque atualizado com sucesso!";
            } else {
                $_SESSION['erro_estoque'] = "Erro ao atualizar estoque.";
            }
        } else {
            $_SESSION['erro_estoque'] = "Registro de estoque não encontrado.";
        }
    }
    header("Location: estoque.php");
    exit;
}

// --- GET: Deletar ---
if (isset($_GET['acao']) && $_GET['acao'] === 'deletar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($estoqueDao->removePorId($id)) {
        $_SESSION['mensagem_estoque'] = "Registro de estoque excluído com sucesso!";
    } else {
        $_SESSION['erro_estoque'] = "Erro ao excluir registro de estoque.";
    }
    header("Location: estoque.php");
    exit;
}

// --- GET: Editar ---
if (isset($_GET['acao']) && $_GET['acao'] === 'editar' && isset($_GET['id'])) {
    $estoqueEditar = $estoqueDao->buscaPorId((int)$_GET['id']);
}

// --- Busca ou listagem ---
$lista = [];
$buscaAtiva = false;
if (isset($_GET['acao']) && $_GET['acao'] === 'buscar') {
    $buscaAtiva = true;
    $tipo  = $_GET['tipo'] ?? 'nome';
    $valor = trim($_GET['valor'] ?? '');
    if ($tipo === 'id') {
        $found = $estoqueDao->buscaPorId((int)$valor);
        if ($found) {
            $produto = $produtoDao->buscaPorId($found->getProdutoId());
            if ($produto) $found->setProdutoNome($produto->getNome());
            $lista = [$found];
        }
    } else {
        $lista = $estoqueDao->buscaPorNomeProduto($valor);
    }
} else {
    $lista = $estoqueDao->buscaTodosComProduto();
}

// Mensagens
if (isset($_SESSION['mensagem_estoque'])) { $mensagem = $_SESSION['mensagem_estoque']; unset($_SESSION['mensagem_estoque']); }
if (isset($_SESSION['erro_estoque']))     { $erro = $_SESSION['erro_estoque'];         unset($_SESSION['erro_estoque']); }
if (isset($_SESSION['erros_campo_estoque'])) { $erros_campo = $_SESSION['erros_campo_estoque']; unset($_SESSION['erros_campo_estoque']); }
if (isset($_SESSION['form_data_estoque']))   { $form_data   = $_SESSION['form_data_estoque'];   unset($_SESSION['form_data_estoque']); }

// Todos os produtos para o select
$todosProdutos = $produtoDao->buscaTodos();

// Helper de valores do form
function valE($campo, $estoqueEditar, $form_data) {
    if (!empty($form_data) && isset($form_data[$campo])) return $form_data[$campo] ?? '';
    if ($estoqueEditar) {
        switch ($campo) {
            case 'produto_id': return $estoqueEditar->getProdutoId();
            case 'quantidade': return $estoqueEditar->getQuantidade();
            case 'preco':      return $estoqueEditar->getPreco();
        }
    }
    return '';
}

$isEdit = $estoqueEditar || (!empty($form_data) && isset($form_data['id']) && $form_data['id']);

// Resumo
$totalItens  = count($lista);
$totalZerado = count(array_filter($lista, fn($e) => $e->getQuantidade() == 0));
$totalBaixo  = count(array_filter($lista, fn($e) => $e->getQuantidade() > 0 && $e->getQuantidade() <= 5));
?>
<?php $page_css = ['libs/css/crud.css', 'libs/css/estoque.css']; include_once "layout_header.php"; ?>

<main>
<h2>Gestão de Estoque</h2>
<p><a href="index.php">« Voltar ao Início</a></p>

<?php if ($mensagem): ?>
    <p class="msg-sucesso"><?php echo htmlspecialchars($mensagem); ?></p>
<?php endif; ?>
<?php if ($erro): ?>
    <p class="msg-erro"><?php echo htmlspecialchars($erro); ?></p>
<?php endif; ?>

<?php if (!$buscaAtiva && !$estoqueEditar && empty($form_data)): ?>
<div class="estoque-resumo">
    <div class="estoque-card">
        <div class="valor"><?php echo $totalItens; ?></div>
        <div class="label">Produtos no estoque</div>
    </div>
    <div class="estoque-card alerta">
        <div class="valor"><?php echo $totalZerado; ?></div>
        <div class="label">Zerados</div>
    </div>
    <div class="estoque-card">
        <div class="valor"><?php echo $totalBaixo; ?></div>
        <div class="label">Estoque baixo (≤ 5)</div>
    </div>
</div>
<?php endif; ?>

<hr>
<h3><?php echo $isEdit ? 'Atualizar Estoque' : 'Adicionar ao Estoque'; ?></h3>
<form method="POST" action="estoque.php">
    <?php if ($estoqueEditar): ?>
        <input type="hidden" name="id" value="<?php echo $estoqueEditar->getId(); ?>">
    <?php elseif (!empty($form_data) && $form_data['id']): ?>
        <input type="hidden" name="id" value="<?php echo (int)$form_data['id']; ?>">
    <?php endif; ?>

    <div class="form-group <?php echo isset($erros_campo['produto_id']) ? 'campo-erro' : ''; ?>">
        <label for="e_produto">Produto:</label>
        <select id="e_produto" name="produto_id" <?php echo $isEdit ? 'disabled' : ''; ?>>
            <option value="">-- Selecione --</option>
            <?php foreach ($todosProdutos as $p): ?>
                <option value="<?php echo $p->getId(); ?>"
                    <?php echo (valE('produto_id', $estoqueEditar, $form_data) == $p->getId()) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($p->getNome()); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php if ($isEdit): ?>
            <input type="hidden" name="produto_id" value="<?php echo valE('produto_id', $estoqueEditar, $form_data); ?>">
        <?php endif; ?>
        <?php if (isset($erros_campo['produto_id'])): ?><span class="erro-campo"><?php echo htmlspecialchars($erros_campo['produto_id']); ?></span><?php endif; ?>
    </div>

    <?php if ($isEdit): ?>
    <div class="form-group">
        <label for="e_operacao">Operação:</label>
        <select id="e_operacao" name="operacao">
            <option value="definir">Definir quantidade exata</option>
            <option value="entrada">Entrada (somar)</option>
            <option value="saida">Saída (subtrair)</option>
        </select>
    </div>
    <?php else: ?>
        <input type="hidden" name="operacao" value="definir">
    <?php endif; ?>

    <div class="form-group <?php echo isset($erros_campo['quantidade']) ? 'campo-erro' : ''; ?>">
        <label for="e_quantidade">Quantidade:</label>
        <input type="number" id="e_quantidade" name="quantidade" min="0" value="<?php echo htmlspecialchars((string)valE('quantidade', $estoqueEditar, $form_data)); ?>">
        <?php if (isset($erros_campo['quantidade'])): ?><span class="erro-campo"><?php echo htmlspecialchars($erros_campo['quantidade']); ?></span><?php endif; ?>
    </div>

    <div class="form-group <?php echo isset($erros_campo['preco']) ? 'campo-erro' : ''; ?>">
        <label for="e_preco">Preço unitário (R$):</label>
        <input type="number" id="e_preco" name="preco" min="0" step="0.01" value="<?php echo htmlspecialchars((string)valE('preco', $estoqueEditar, $form_data)); ?>">
        <?php if (isset($erros_campo['preco'])): ?><span class="erro-campo"><?php echo htmlspecialchars($erros_campo['preco']); ?></span><?php endif; ?>
    </div>

    <button type="submit"><?php echo $isEdit ? 'Salvar Alterações' : 'Cadastrar'; ?></button>
    <?php if ($estoqueEditar): ?>
        <a href="estoque.php"><button type="button">Cancelar</button></a>
    <?php endif; ?>
</form>

<hr>
<h3>Consultar Estoque</h3>
<form method="GET" action="estoque.php">
    <input type="hidden" name="acao" value="buscar">
    <label>Buscar por:
        <select name="tipo">
            <option value="nome">Nome do Produto</option>
            <option value="id">Código do Registro</option>
        </select>
    </label>
    <input type="text" name="valor" placeholder="Digite o valor" required>
    <button type="submit">Buscar</button>
    <a href="estoque.php"><button type="button">Listar Todos</button></a>
</form>

<hr>
<h3>Lista de Estoque <?php echo $buscaAtiva ? '(resultado da busca)' : ''; ?></h3>
<?php if (empty($lista)): ?>
    <p>Nenhum item no estoque.</p>
<?php else: ?>
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Status</th>
                <th>Preço Unit.</th>
                <th>Valor Total</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($lista as $e): ?>
            <?php
                $qtd = $e->getQuantidade();
                if ($qtd == 0)      $badge = '<span class="badge-zerado">Zerado</span>';
                elseif ($qtd <= 5)  $badge = '<span class="badge-baixo">Baixo</span>';
                else                $badge = '<span class="badge-ok">OK</span>';
            ?>
            <tr>
                <td><?php echo $e->getId(); ?></td>
                <td><?php echo htmlspecialchars($e->getProdutoNome() ?? ''); ?></td>
                <td><?php echo $qtd; ?></td>
                <td><?php echo $badge; ?></td>
                <td>R$ <?php echo number_format($e->getPreco(), 2, ',', '.'); ?></td>
                <td>R$ <?php echo number_format($e->getPreco() * $qtd, 2, ',', '.'); ?></td>
                <td>
                    <a href="estoque.php?acao=editar&id=<?php echo $e->getId(); ?>">Editar</a>
                    |
                    <a href="estoque.php?acao=deletar&id=<?php echo $e->getId(); ?>"
                       onclick="return confirm('Confirma a exclusão do estoque do produto <?php echo addslashes($e->getProdutoNome() ?? ''); ?>?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</main>
