<?php
require "fachada.php";
include_once "comum.php";

if (is_session_started() === FALSE) {
    session_start();
}

// Ações do carrinho
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remover'])) {
        $id = (int)$_POST['produto_id'];
        unset($_SESSION['carrinho'][$id]);
    }
    if (isset($_POST['atualizar'])) {
        $id = (int)$_POST['produto_id'];
        $qtd = max(1, (int)$_POST['quantidade']);
        if (isset($_SESSION['carrinho'][$id])) {
            $_SESSION['carrinho'][$id]['quantidade'] = $qtd;
        }
    }
    if (isset($_POST['limpar'])) {
        $_SESSION['carrinho'] = [];
    }
    header("Location: carrinho.php");
    exit;
}

$carrinho = $_SESSION['carrinho'] ?? [];
$total = 0;
foreach ($carrinho as $item) {
    $total += $item['preco'] * $item['quantidade'];
}

$page_css = ['libs/css/carrinho.css'];
include_once "layout_header.php";
?>

<main class="carrinho-container">
    <h2>🛒 Meu Carrinho</h2>

    <?php if (empty($carrinho)): ?>
        <div class="carrinho-vazio">
            <h3>Seu carrinho está vazio</h3>
            <p>Adicione produtos a partir da <a href="loja.php">loja</a>.</p>
        </div>
    <?php else: ?>
        <table class="tabela-carrinho">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Preço Unitário</th>
                    <th>Quantidade</th>
                    <th>Subtotal</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($carrinho as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['nome']); ?></td>
                        <td>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                        <td>
                            <form method="POST" action="carrinho.php" class="form-inline">
                                <input type="hidden" name="produto_id" value="<?php echo $item['produto_id']; ?>">
                                <input type="number" name="quantidade" value="<?php echo $item['quantidade']; ?>" min="1" class="input-qtd">
                                <button type="submit" name="atualizar" class="btn-atualizar">Atualizar</button>
                            </form>
                        </td>
                        <td>R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?></td>
                        <td>
                            <form method="POST" action="carrinho.php">
                                <input type="hidden" name="produto_id" value="<?php echo $item['produto_id']; ?>">
                                <button type="submit" name="remover" class="btn-remover">Remover</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="carrinho-footer">
            <div class="total">
                <strong>Total: R$ <?php echo number_format($total, 2, ',', '.'); ?></strong>
            </div>
            <div class="acoes">
                <form method="POST" action="carrinho.php" style="display:inline;">
                    <button type="submit" name="limpar" class="btn-limpar">Limpar Carrinho</button>
                </form>
                <a href="loja.php" class="btn-continuar">Continuar Comprando</a>
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <a href="finalizar_compra.php" class="btn-comprar" style="display:inline-block; padding: 10px 25px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Finalizar Pedido</a>
                <?php else: ?>
                    <a href="login.php?redirect=carrinho" class="btn-comprar" style="display:inline-block; padding: 10px 25px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;">Finalizar Pedido</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</main>
