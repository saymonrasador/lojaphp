<?php
require "fachada.php";
include_once "comum.php";

if (is_session_started() === FALSE) {
    session_start();
}

$produtoDao = $factory->getProdutoDao();
$estoqueDao = $factory->getEstoqueDao();

$produtos = $produtoDao->buscaTodos();

// Montar array de estoque indexado por produto_id
$estoquePorProduto = [];
$todosEstoques = $estoqueDao->buscaTodos();
if ($todosEstoques) {
    foreach ($todosEstoques as $est) {
        $estoquePorProduto[$est->getProdutoId()] = $est;
    }
}

// Adicionar ao carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_carrinho'])) {
    $prodId = (int)$_POST['produto_id'];
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }
    // Verificar estoque disponível
    $estoqueItem = $estoquePorProduto[$prodId] ?? null;
    if ($estoqueItem && $estoqueItem->getQuantidade() > 0) {
        if (isset($_SESSION['carrinho'][$prodId])) {
            $_SESSION['carrinho'][$prodId]['quantidade']++;
        } else {
            $prod = $produtoDao->buscaPorId($prodId);
            $_SESSION['carrinho'][$prodId] = [
                'produto_id' => $prodId,
                'nome' => $prod->getNome(),
                'preco' => $estoqueItem->getPreco(),
                'quantidade' => 1
            ];
        }
        $_SESSION['msg_carrinho'] = "Produto adicionado ao carrinho!";
    } else {
        $_SESSION['msg_carrinho_erro'] = "Produto sem estoque disponível!";
    }
    header("Location: loja.php");
    exit;
}

$msgSucesso = $_SESSION['msg_carrinho'] ?? '';
$msgErro = $_SESSION['msg_carrinho_erro'] ?? '';
unset($_SESSION['msg_carrinho'], $_SESSION['msg_carrinho_erro']);

$page_css = ['libs/css/loja.css'];
include_once "layout_header.php";
?>

<main class="loja-container">
    <section class="loja-header">
        <h2>Bem-vindo à nossa Loja Virtual!</h2>
        <p>Confira nossos produtos disponíveis</p>
        <a href="carrinho.php" class="btn-carrinho-link">🛒 Ver Carrinho
            <?php if (!empty($_SESSION['carrinho'])): ?>
                <span class="badge"><?php echo array_sum(array_column($_SESSION['carrinho'], 'quantidade')); ?></span>
            <?php endif; ?>
        </a>
    </section>

    <?php if ($msgSucesso): ?>
        <div class="alerta sucesso"><?php echo htmlspecialchars($msgSucesso); ?></div>
    <?php endif; ?>
    <?php if ($msgErro): ?>
        <div class="alerta erro"><?php echo htmlspecialchars($msgErro); ?></div>
    <?php endif; ?>

    <?php if (empty($produtos)): ?>
        <div class="sem-produtos">
            <h3>Nenhum produto cadastrado</h3>
            <p>No momento não há produtos disponíveis. Volte em breve!</p>
        </div>
    <?php else: ?>
        <div class="grade-produtos">
            <?php foreach ($produtos as $produto):
                $estoque = $estoquePorProduto[$produto->getId()] ?? null;
                $preco = $estoque ? $estoque->getPreco() : null;
                $quantidade = $estoque ? $estoque->getQuantidade() : 0;
                $disponivel = $quantidade > 0;
            ?>
                <div class="card-produto <?php echo !$disponivel ? 'indisponivel' : ''; ?>">
                    <div class="card-imagem">
                        <?php if ($produto->getFoto()): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($produto->getFoto()); ?>" alt="<?php echo htmlspecialchars($produto->getNome()); ?>">
                        <?php else: ?>
                            <div class="sem-imagem">📦</div>
                        <?php endif; ?>
                    </div>
                    <div class="card-info">
                        <h3><?php echo htmlspecialchars($produto->getNome()); ?></h3>
                        <?php if ($produto->getDescricao()): ?>
                            <p class="descricao"><?php echo htmlspecialchars($produto->getDescricao()); ?></p>
                        <?php endif; ?>
                        <div class="estoque-info">
                            <?php if ($estoque): ?>
                                <span class="preco">R$ <?php echo number_format($preco, 2, ',', '.'); ?></span>
                                <span class="status <?php echo $disponivel ? 'em-estoque' : 'sem-estoque'; ?>">
                                    <?php echo $disponivel ? "Em estoque ({$quantidade})" : "Esgotado"; ?>
                                </span>
                            <?php else: ?>
                                <span class="status sem-estoque">Sem informação de estoque</span>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="loja.php">
                            <input type="hidden" name="produto_id" value="<?php echo $produto->getId(); ?>">
                            <button type="submit" name="adicionar_carrinho" class="btn-comprar" <?php echo !$disponivel ? 'disabled' : ''; ?>>
                                <?php echo $disponivel ? 'Comprar' : 'Indisponível'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>
