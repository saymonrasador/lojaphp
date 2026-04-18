<?php
require "verifica.php";
require "fachada.php";

$usuarioDao   = $factory->getUsuarioDao();
$clienteDao   = $factory->getClienteDao();
$fornecedorDao = $factory->getFornecedorDao();
$enderecoDao  = $factory->getEnderecoDao();

$usuario = $usuarioDao->buscaPorId($_SESSION["id_usuario"]);
$cliente = null;
$fornecedor = null;
$endereco = null;

$mensagem = "";
$erro = "";

// Carrega cliente
if ($usuario->getClienteId()) {
    $cliente = $clienteDao->buscaPorId($usuario->getClienteId());
    if ($cliente && $cliente->getEndereco()) {
        $endRef = $cliente->getEndereco();
        $endereco = $enderecoDao->buscaPorId(is_object($endRef) ? $endRef->getId() : $endRef);
    }
}

// Carrega fornecedor
if ($usuario->getFornecedorId()) {
    $fornecedor = $fornecedorDao->buscaPorId($usuario->getFornecedorId());
}

// --- POST: Ações ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    // === Salvar perfil (telefone, endereço, descrição) ===
    if ($acao === 'salvar_perfil') {
        $telefone    = trim($_POST['telefone'] ?? '');
        $rua         = trim($_POST['rua'] ?? '');
        $numero      = trim($_POST['numero'] ?? '');
        $complemento = trim($_POST['complemento'] ?? '');
        $bairro      = trim($_POST['bairro'] ?? '');
        $cep         = trim($_POST['cep'] ?? '');
        $cidade      = trim($_POST['cidade'] ?? '');
        $estado      = trim($_POST['estado'] ?? '');
        $descricao      = trim($_POST['descricao'] ?? '');
        $cartaoCredito  = trim($_POST['cartao_credito'] ?? '');

        // Atualiza cliente
        if ($cliente) {
            $cliente->setTelefone($telefone);
            $cliente->setCartaoCredito($cartaoCredito);

            // Endereço
            if ($endereco) {
                $endereco->setRua($rua);
                $endereco->setNumero($numero);
                $endereco->setComplemento($complemento);
                $endereco->setBairro($bairro);
                $endereco->setCep($cep);
                $endereco->setCidade($cidade);
                $endereco->setEstado($estado);
                $enderecoDao->altera($endereco);
            } else {
                // Cria endereço novo
                $novoEndereco = new Endereco(null, $rua, $numero, $complemento, $bairro, $cep, $cidade, $estado);
                $novoEnderecoId = $enderecoDao->insereRetornaId($novoEndereco);
                if ($novoEnderecoId) {
                    $novoEndereco->setId($novoEnderecoId);
                    $cliente->setEndereco($novoEndereco);
                    $endereco = $novoEndereco;
                }
            }
            $clienteDao->altera($cliente);
        }

        // Atualiza fornecedor (descrição)
        if ($fornecedor) {
            $fornecedor->setDescricao($descricao);
            
            // Sincroniza telefone e endereço no fornecedor também
            $fornecedor->setTelefone($telefone);
            if ($endereco) {
                $fornecedor->setEndereco($endereco);
            }
            $fornecedorDao->altera($fornecedor);
        }

        $_SESSION['mensagem_perfil'] = "Perfil atualizado com sucesso!";
        header("Location: perfil.php");
        exit;
    }

    // === Virar fornecedor ===
    if ($acao === 'virar_fornecedor') {
        if (!$usuario->getFornecedorId()) {
            // Cria um novo fornecedor vinculado ao usuário
            $emailFornecedor = $usuario->getLogin();
            $telefoneFornecedor = $cliente ? $cliente->getTelefone() : '';
            $enderecoFornecedor = $endereco ?? null;

            $novoFornecedor = new Fornecedor(null, $usuario->getNome(), '', $telefoneFornecedor, $emailFornecedor, $enderecoFornecedor);
            
            if ($fornecedorDao->insere($novoFornecedor)) {
                // Busca o fornecedor recém-criado
                $fornecedoresList = $fornecedorDao->buscaPorNome($usuario->getNome());
                $fornecedorCriado = null;
                foreach ($fornecedoresList as $f) {
                    if ($f->getEmail() === $emailFornecedor) {
                        $fornecedorCriado = $f;
                        break;
                    }
                }
                
                if ($fornecedorCriado) {
                    $usuario->setFornecedorId($fornecedorCriado->getId());
                    $usuarioDao->altera($usuario);
                    $_SESSION["fornecedor_id"] = $fornecedorCriado->getId();
                    $_SESSION['mensagem_perfil'] = "Parabéns! Agora você é um fornecedor! Você pode cadastrar produtos e gerenciar estoque.";
                } else {
                    $_SESSION['erro_perfil'] = "Erro ao criar fornecedor.";
                }
            } else {
                $_SESSION['erro_perfil'] = "Erro ao criar fornecedor.";
            }
        }
        header("Location: perfil.php");
        exit;
    }
}

// Recarrega dados após POST redirect
$usuario = $usuarioDao->buscaPorId($_SESSION["id_usuario"]);
$cliente = null;
$fornecedor = null;
$endereco = null;

if ($usuario->getClienteId()) {
    $cliente = $clienteDao->buscaPorId($usuario->getClienteId());
    if ($cliente && $cliente->getEndereco()) {
        $endObj = $cliente->getEndereco();
        if (is_object($endObj)) {
            $endereco = $endObj;
        } else {
            $endereco = $enderecoDao->buscaPorId($endObj);
        }
    }
}
if ($usuario->getFornecedorId()) {
    $fornecedor = $fornecedorDao->buscaPorId($usuario->getFornecedorId());
}

// Mensagens da sessão
if (isset($_SESSION['mensagem_perfil'])) {
    $mensagem = $_SESSION['mensagem_perfil'];
    unset($_SESSION['mensagem_perfil']);
}
if (isset($_SESSION['erro_perfil'])) {
    $erro = $_SESSION['erro_perfil'];
    unset($_SESSION['erro_perfil']);
}

$isFornecedor = $usuario->getFornecedorId() ? true : false;
?>
<?php $page_css = ['libs/css/perfil.css']; include_once "layout_header.php"; ?>

<main class="perfil-main">

<?php if ($mensagem): ?>
    <p class="msg-sucesso"><?php echo htmlspecialchars($mensagem); ?></p>
<?php endif; ?>
<?php if ($erro): ?>
    <p class="msg-erro"><?php echo htmlspecialchars($erro); ?></p>
<?php endif; ?>


<?php if (!$isFornecedor): ?>
<div class="perfil-card" style="text-align:center;">
    <h3 style="border:none; margin:0 0 8px 0;">Quer vender seus produtos?</h3>
    <p style="color:#666; font-size:14px;">Torne-se um fornecedor e tenha acesso ao cadastro de produtos e estoque.</p>
    <form method="POST" action="perfil.php">
        <input type="hidden" name="acao" value="virar_fornecedor">
        <button type="submit" class="btn-fornecedor">Vire um Fornecedor</button>
    </form>
</div>
<?php endif; ?>


<div class="perfil-card">
    <h2>Meu Perfil 
        <span class="perfil-badge">Cliente</span>
        <?php if ($isFornecedor): ?>
            <span class="perfil-badge fornecedor">Fornecedor</span>
        <?php endif; ?>
    </h2>

    <div class="perfil-info"><strong>Nome:</strong> <?php echo htmlspecialchars($usuario->getNome()); ?></div>
    <div class="perfil-info"><strong>Login:</strong> <?php echo htmlspecialchars($usuario->getLogin()); ?></div>

    <form method="POST" action="perfil.php">
        <input type="hidden" name="acao" value="salvar_perfil">

        <h3>Telefone</h3>
        <div class="perfil-form-group">
            <label for="telefone">Telefone</label>
            <input type="text" id="telefone" name="telefone" 
                   value="<?php echo htmlspecialchars($cliente ? $cliente->getTelefone() ?? '' : ''); ?>" 
                   placeholder="(00) 00000-0000">
        </div>

        <h3>Cartão de Crédito</h3>
        <div class="perfil-form-group">
            <label for="cartao_credito">Número do Cartão</label>
            <input type="text" id="cartao_credito" name="cartao_credito" 
                   value="<?php echo htmlspecialchars($cliente ? $cliente->getCartaoCredito() ?? '' : ''); ?>" 
                   placeholder="0000 0000 0000 0000">
        </div>

        <h3>Endereço</h3>

        <?php if ($endereco && $endereco->getRua()): ?>
        <!-- Endereço já cadastrado: exibe como texto -->
        <div id="endereco-display">
            <button type="button" class="btn-editar-endereco" onclick="document.getElementById('endereco-display').style.display='none'; document.getElementById('endereco-form').style.display='block';">Editar Endereço</button>
            <div class="perfil-info"><strong>Rua:</strong> <?php echo htmlspecialchars($endereco->getRua()); ?>, <?php echo htmlspecialchars($endereco->getNumero()); ?></div>
            <?php if ($endereco->getComplemento()): ?>
                <div class="perfil-info"><strong>Complemento:</strong> <?php echo htmlspecialchars($endereco->getComplemento()); ?></div>
            <?php endif; ?>
            <div class="perfil-info"><strong>Bairro:</strong> <?php echo htmlspecialchars($endereco->getBairro()); ?></div>
            <div class="perfil-info"><strong>CEP:</strong> <?php echo htmlspecialchars($endereco->getCep()); ?></div>
            <div class="perfil-info"><strong>Cidade:</strong> <?php echo htmlspecialchars($endereco->getCidade()); ?> - <?php echo htmlspecialchars($endereco->getEstado()); ?></div>
        </div>
        <div id="endereco-form" style="display:none;">
        <?php else: ?>
        <!-- Endereço não cadastrado: exibe inputs -->
        <div id="endereco-form">
        <?php endif; ?>

            <div class="perfil-form-row">
                <div class="perfil-form-group" style="flex:3">
                    <label for="rua">Rua</label>
                    <input type="text" id="rua" name="rua" value="<?php echo htmlspecialchars($endereco ? $endereco->getRua() ?? '' : ''); ?>">
                </div>
                <div class="perfil-form-group" style="flex:1">
                    <label for="numero">Número</label>
                    <input type="text" id="numero" name="numero" value="<?php echo htmlspecialchars($endereco ? $endereco->getNumero() ?? '' : ''); ?>">
                </div>
            </div>
            <div class="perfil-form-group">
                <label for="complemento">Complemento</label>
                <input type="text" id="complemento" name="complemento" value="<?php echo htmlspecialchars($endereco ? $endereco->getComplemento() ?? '' : ''); ?>">
            </div>
            <div class="perfil-form-row">
                <div class="perfil-form-group">
                    <label for="bairro">Bairro</label>
                    <input type="text" id="bairro" name="bairro" value="<?php echo htmlspecialchars($endereco ? $endereco->getBairro() ?? '' : ''); ?>">
                </div>
                <div class="perfil-form-group">
                    <label for="cep">CEP</label>
                    <input type="text" id="cep" name="cep" value="<?php echo htmlspecialchars($endereco ? $endereco->getCep() ?? '' : ''); ?>">
                </div>
            </div>
            <div class="perfil-form-row">
                <div class="perfil-form-group">
                    <label for="cidade">Cidade</label>
                    <input type="text" id="cidade" name="cidade" value="<?php echo htmlspecialchars($endereco ? $endereco->getCidade() ?? '' : ''); ?>">
                </div>
                <div class="perfil-form-group">
                    <label for="estado">Estado</label>
                    <input type="text" id="estado" name="estado" value="<?php echo htmlspecialchars($endereco ? $endereco->getEstado() ?? '' : ''); ?>">
                </div>
            </div>
        </div>

        <?php if ($isFornecedor): ?>
            <h3>Dados do Fornecedor</h3>
            <div class="perfil-form-group">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" placeholder="Descreva sua empresa ou serviço..."><?php echo htmlspecialchars($fornecedor ? $fornecedor->getDescricao() ?? '' : ''); ?></textarea>
            </div>
        <?php endif; ?>

        <button type="submit" class="btn-salvar">Salvar Alterações</button>
    </form>
</div>

</main>
</body>
</html>
