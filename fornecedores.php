<?php
require "verifica.php";
require "fachada.php";

$dao        = $factory->getFornecedorDao();
$enderecoDao = $factory->getEnderecoDao();
$mensagem = "";
$erro = "";
$fornecedorEditar = null;
$erros_campo = [];
$form_data = [];

// --- POST: Inserir ou Alterar ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $nome      = trim($_POST['nome'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $telefone  = trim($_POST['telefone'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    // Endereço
    $rua          = trim($_POST['rua'] ?? '');
    $numero       = trim($_POST['numero'] ?? '');
    $complemento  = trim($_POST['complemento'] ?? '');
    $bairro       = trim($_POST['bairro'] ?? '');
    $cep          = trim($_POST['cep'] ?? '');
    $cidade       = trim($_POST['cidade'] ?? '');
    $estado       = trim($_POST['estado'] ?? '');
    $endereco_id  = isset($_POST['endereco_id']) && $_POST['endereco_id'] !== '' ? (int)$_POST['endereco_id'] : null;

    $erros_validacao = [];
    if (!$nome) $erros_validacao['nome'] = "O campo Nome é obrigatório!";
    if (!$telefone) $erros_validacao['telefone'] = "O campo Telefone é obrigatório!";
    if (!$email) $erros_validacao['email'] = "O campo E-mail é obrigatório!";
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $erros_validacao['email'] = "E-mail inválido!";
    if (!$rua) $erros_validacao['rua'] = "O campo Rua é obrigatório!";
    if (!$numero) $erros_validacao['numero'] = "O campo Número é obrigatório!";
    if (!$bairro) $erros_validacao['bairro'] = "O campo Bairro é obrigatório!";
    if (!$cep) $erros_validacao['cep'] = "O campo CEP é obrigatório!";
    if (!$cidade) $erros_validacao['cidade'] = "O campo Cidade é obrigatório!";
    if (!$estado) $erros_validacao['estado'] = "O campo Estado é obrigatório!";

    if (!empty($erros_validacao)) {
        $_SESSION['erros_campo_fornecedor'] = $erros_validacao;
        $_SESSION['form_data_fornecedor'] = [
            'id' => $id, 'nome' => $nome, 'descricao' => $descricao, 'telefone' => $telefone, 'email' => $email,
            'rua' => $rua, 'numero' => $numero, 'complemento' => $complemento, 'bairro' => $bairro,
            'cep' => $cep, 'cidade' => $cidade, 'estado' => $estado, 'endereco_id' => $endereco_id
        ];
        if ($id !== null) {
            header("Location: fornecedores.php?acao=editar&id=$id");
        } else {
            header("Location: fornecedores.php");
        }
        exit;
    }

    $endereco = new Endereco(null, $rua, $numero, $complemento, $bairro, $cep, $cidade, $estado);

    if ($id === null) {
        $novoEnderecoId = $enderecoDao->insereRetornaId($endereco);
        if (!$novoEnderecoId) {
            $_SESSION['erro_fornecedor'] = "Erro ao salvar endereço.";
            header("Location: fornecedores.php");
            exit;
        }
        $endereco->setId($novoEnderecoId);
        $fornecedor = new Fornecedor(null, $nome, $descricao, $telefone, $email, $endereco);
        if ($dao->insere($fornecedor)) {
            $_SESSION['mensagem_fornecedor'] = "Fornecedor cadastrado com sucesso!";
        } else {
            $_SESSION['erro_fornecedor'] = "Erro ao cadastrar fornecedor.";
        }
    } else {
        $fornecedor = $dao->buscaPorId($id);
        if ($fornecedor) {
            if ($endereco_id) {
                $endereco->setId($endereco_id);
                $enderecoDao->altera($endereco);
                $fornecedor->setEndereco($endereco);
            }
            $fornecedor->setNome($nome);
            $fornecedor->setDescricao($descricao);
            $fornecedor->setTelefone($telefone);
            $fornecedor->setEmail($email);
            if ($dao->altera($fornecedor)) {
                $_SESSION['mensagem_fornecedor'] = "Fornecedor alterado com sucesso!";
            } else {
                $_SESSION['erro_fornecedor'] = "Erro ao alterar fornecedor.";
            }
        } else {
            $_SESSION['erro_fornecedor'] = "Fornecedor não encontrado.";
        }
    }
    header("Location: fornecedores.php");
    exit;
}

// --- GET: Deletar ---
if (isset($_GET['acao']) && $_GET['acao'] === 'deletar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($dao->removePorId($id)) {
        $_SESSION['mensagem_fornecedor'] = "Fornecedor excluído com sucesso!";
    } else {
        $_SESSION['erro_fornecedor'] = "Erro ao excluir fornecedor. Verifique se não há produtos vinculados.";
    }
    header("Location: fornecedores.php");
    exit;
}

// --- GET: Editar ---
if (isset($_GET['acao']) && $_GET['acao'] === 'editar' && isset($_GET['id'])) {
    $fornecedorEditar = $dao->buscaPorId((int)$_GET['id']);
    // Carrega endereço
    if ($fornecedorEditar && $fornecedorEditar->getEndereco() === null) {
        // tenta carregar pelo id armazenado
    }
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
if (isset($_SESSION['mensagem_fornecedor'])) {
    $mensagem = $_SESSION['mensagem_fornecedor'];
    unset($_SESSION['mensagem_fornecedor']);
}
if (isset($_SESSION['erro_fornecedor'])) {
    $erro = $_SESSION['erro_fornecedor'];
    unset($_SESSION['erro_fornecedor']);
}
if (isset($_SESSION['erros_campo_fornecedor'])) {
    $erros_campo = $_SESSION['erros_campo_fornecedor'];
    unset($_SESSION['erros_campo_fornecedor']);
}
if (isset($_SESSION['form_data_fornecedor'])) {
    $form_data = $_SESSION['form_data_fornecedor'];
    unset($_SESSION['form_data_fornecedor']);
}

// Endereço para edição
$endEditar = null;
if ($fornecedorEditar && $fornecedorEditar->getEndereco()) {
    $endEditar = $fornecedorEditar->getEndereco();
}

// Helper para valores do formulário
function valF($campo, $fornecedorEditar, $endEditar, $form_data) {
    if (!empty($form_data) && isset($form_data[$campo])) return $form_data[$campo] ?? '';
    if ($fornecedorEditar) {
        switch ($campo) {
            case 'nome': return $fornecedorEditar->getNome();
            case 'descricao': return $fornecedorEditar->getDescricao();
            case 'telefone': return $fornecedorEditar->getTelefone();
            case 'email': return $fornecedorEditar->getEmail();
        }
    }
    if ($endEditar) {
        switch ($campo) {
            case 'rua': return $endEditar->getRua();
            case 'numero': return $endEditar->getNumero();
            case 'complemento': return $endEditar->getComplemento();
            case 'bairro': return $endEditar->getBairro();
            case 'cep': return $endEditar->getCep();
            case 'cidade': return $endEditar->getCidade();
            case 'estado': return $endEditar->getEstado();
        }
    }
    return '';
}
$isEdit = $fornecedorEditar || (!empty($form_data) && isset($form_data['id']) && $form_data['id']);
?>
<?php $page_css = ['libs/css/crud.css']; include_once "layout_header.php"; ?>

<main>
<h2>Cadastro de Fornecedores</h2>

<p><a href="index.php">« Voltar ao Início</a></p>

<?php if ($mensagem): ?>
    <p class="msg-sucesso"><?php echo htmlspecialchars($mensagem); ?></p>
<?php endif; ?>
<?php if ($erro): ?>
    <p class="msg-erro"><?php echo htmlspecialchars($erro); ?></p>
<?php endif; ?>

<hr>
<h3><?php echo $isEdit ? 'Alterar Fornecedor' : 'Novo Fornecedor'; ?></h3>
<form method="POST" action="fornecedores.php">
    <?php if ($fornecedorEditar): ?>
        <input type="hidden" name="id" value="<?php echo $fornecedorEditar->getId(); ?>">
        <?php if ($endEditar): ?>
            <input type="hidden" name="endereco_id" value="<?php echo $endEditar->getId(); ?>">
        <?php endif; ?>
    <?php elseif (!empty($form_data) && $form_data['id']): ?>
        <input type="hidden" name="id" value="<?php echo (int)$form_data['id']; ?>">
        <?php if (isset($form_data['endereco_id']) && $form_data['endereco_id']): ?>
            <input type="hidden" name="endereco_id" value="<?php echo (int)$form_data['endereco_id']; ?>">
        <?php endif; ?>
    <?php endif; ?>

    <fieldset>
        <legend>Dados do Fornecedor</legend>
        <div class="form-group <?php echo isset($erros_campo['nome']) ? 'campo-erro' : ''; ?>">
            <label for="f_nome">Nome:</label>
            <input type="text" id="f_nome" name="nome" value="<?php echo htmlspecialchars(valF('nome', $fornecedorEditar, $endEditar, $form_data)); ?>">
            <?php if (isset($erros_campo['nome'])): ?><span class="erro-campo"><?php echo htmlspecialchars($erros_campo['nome']); ?></span><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="f_descricao">Descrição:</label>
            <input type="text" id="f_descricao" name="descricao" value="<?php echo htmlspecialchars(valF('descricao', $fornecedorEditar, $endEditar, $form_data)); ?>">
        </div>
        <div class="form-group <?php echo isset($erros_campo['telefone']) ? 'campo-erro' : ''; ?>">
            <label for="f_telefone">Telefone:</label>
            <input type="text" id="f_telefone" name="telefone" value="<?php echo htmlspecialchars(valF('telefone', $fornecedorEditar, $endEditar, $form_data)); ?>">
            <?php if (isset($erros_campo['telefone'])): ?><span class="erro-campo"><?php echo htmlspecialchars($erros_campo['telefone']); ?></span><?php endif; ?>
        </div>
        <div class="form-group <?php echo isset($erros_campo['email']) ? 'campo-erro' : ''; ?>">
            <label for="f_email">E-mail:</label>
            <input type="email" id="f_email" name="email" value="<?php echo htmlspecialchars(valF('email', $fornecedorEditar, $endEditar, $form_data)); ?>">
            <?php if (isset($erros_campo['email'])): ?><span class="erro-campo"><?php echo htmlspecialchars($erros_campo['email']); ?></span><?php endif; ?>
        </div>
    </fieldset>

    <br>
    <fieldset>
        <legend>Endereço</legend>
        <div class="form-group <?php echo isset($erros_campo['rua']) ? 'campo-erro' : ''; ?>">
            <label for="f_rua">Rua:</label>
            <input type="text" id="f_rua" name="rua" value="<?php echo htmlspecialchars(valF('rua', $fornecedorEditar, $endEditar, $form_data)); ?>">
            <?php if (isset($erros_campo['rua'])): ?><span class="erro-campo"><?php echo htmlspecialchars($erros_campo['rua']); ?></span><?php endif; ?>
        </div>
        <div class="form-group <?php echo isset($erros_campo['numero']) ? 'campo-erro' : ''; ?>">
            <label for="f_numero">Número:</label>
            <input type="text" id="f_numero" name="numero" value="<?php echo htmlspecialchars(valF('numero', $fornecedorEditar, $endEditar, $form_data)); ?>">
            <?php if (isset($erros_campo['numero'])): ?><span class="erro-campo"><?php echo htmlspecialchars($erros_campo['numero']); ?></span><?php endif; ?>
        </div>
        <div class="form-group">
            <label for="f_complemento">Complemento:</label>
            <input type="text" id="f_complemento" name="complemento" value="<?php echo htmlspecialchars(valF('complemento', $fornecedorEditar, $endEditar, $form_data)); ?>">
        </div>
        <div class="form-group <?php echo isset($erros_campo['bairro']) ? 'campo-erro' : ''; ?>">
            <label for="f_bairro">Bairro:</label>
            <input type="text" id="f_bairro" name="bairro" value="<?php echo htmlspecialchars(valF('bairro', $fornecedorEditar, $endEditar, $form_data)); ?>">
            <?php if (isset($erros_campo['bairro'])): ?><span class="erro-campo"><?php echo htmlspecialchars($erros_campo['bairro']); ?></span><?php endif; ?>
        </div>
        <div class="form-group <?php echo isset($erros_campo['cep']) ? 'campo-erro' : ''; ?>">
            <label for="f_cep">CEP:</label>
            <input type="text" id="f_cep" name="cep" value="<?php echo htmlspecialchars(valF('cep', $fornecedorEditar, $endEditar, $form_data)); ?>">
            <?php if (isset($erros_campo['cep'])): ?><span class="erro-campo"><?php echo htmlspecialchars($erros_campo['cep']); ?></span><?php endif; ?>
        </div>
        <div class="form-group <?php echo isset($erros_campo['cidade']) ? 'campo-erro' : ''; ?>">
            <label for="f_cidade">Cidade:</label>
            <input type="text" id="f_cidade" name="cidade" value="<?php echo htmlspecialchars(valF('cidade', $fornecedorEditar, $endEditar, $form_data)); ?>">
            <?php if (isset($erros_campo['cidade'])): ?><span class="erro-campo"><?php echo htmlspecialchars($erros_campo['cidade']); ?></span><?php endif; ?>
        </div>
        <div class="form-group <?php echo isset($erros_campo['estado']) ? 'campo-erro' : ''; ?>">
            <label for="f_estado">Estado:</label>
            <input type="text" id="f_estado" name="estado" maxlength="2" value="<?php echo htmlspecialchars(valF('estado', $fornecedorEditar, $endEditar, $form_data)); ?>">
            <?php if (isset($erros_campo['estado'])): ?><span class="erro-campo"><?php echo htmlspecialchars($erros_campo['estado']); ?></span><?php endif; ?>
        </div>
    </fieldset>

    <br>
    <button type="submit"><?php echo $isEdit ? 'Salvar Alterações' : 'Cadastrar'; ?></button>
    <?php if ($fornecedorEditar): ?>
        <a href="fornecedores.php"><button type="button">Cancelar</button></a>
    <?php endif; ?>
</form>

<hr>
<h3>Consultar Fornecedores</h3>
<form method="GET" action="fornecedores.php">
    <input type="hidden" name="acao" value="buscar">
    <label>Buscar por:
        <select name="tipo">
            <option value="nome">Nome</option>
            <option value="id">Código</option>
        </select>
    </label>
    <input type="text" name="valor" placeholder="Digite o valor" required>
    <button type="submit">Buscar</button>
    <a href="fornecedores.php"><button type="button">Listar Todos</button></a>
</form>

<hr>
<h3>Lista de Fornecedores <?php echo $buscaAtiva ? '(resultado da busca)' : ''; ?></h3>
<?php if (empty($lista)): ?>
    <p>Nenhum fornecedor encontrado.</p>
<?php else: ?>
    <table border="1" cellpadding="5">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Descrição</th>
                <th>Telefone</th>
                <th>E-mail</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($lista as $f): ?>
            <tr>
                <td><?php echo $f->getId(); ?></td>
                <td><?php echo htmlspecialchars($f->getNome()); ?></td>
                <td><?php echo htmlspecialchars($f->getDescricao()); ?></td>
                <td><?php echo htmlspecialchars($f->getTelefone()); ?></td>
                <td><?php echo htmlspecialchars($f->getEmail()); ?></td>
                <td>
                    <a href="fornecedores.php?acao=editar&id=<?php echo $f->getId(); ?>">Editar</a>
                    |
                    <a href="fornecedores.php?acao=deletar&id=<?php echo $f->getId(); ?>"
                       onclick="return confirm('Confirma a exclusão do fornecedor <?php echo addslashes($f->getNome()); ?>?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</main>
