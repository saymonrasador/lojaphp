<?php
require "verifica.php";
require "fachada.php";

$dao = $factory->getUsuarioDao();
$mensagem = "";
$erro = "";
$usuarioEditar = null;
$erros_campo = [];
$form_data = [];

// --- POST: Inserir ou Alterar ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id    = isset($_POST['id']) && $_POST['id'] !== '' ? (int)$_POST['id'] : null;
    $nome  = trim($_POST['nome'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    $erros_validacao = [];
    if (!$nome) $erros_validacao['nome'] = "O campo Nome é obrigatório!";
    if (!$login) $erros_validacao['login'] = "O campo Login é obrigatório!";
    if ($id === null && !$senha) $erros_validacao['senha'] = "O campo Senha é obrigatório para novo usuário!";
    if ($senha && strlen($senha) < 6) $erros_validacao['senha'] = "A senha deve ter no mínimo 6 caracteres!";

    if (!empty($erros_validacao)) {
        $_SESSION['erros_campo_usuario'] = $erros_validacao;
        $_SESSION['form_data_usuario'] = ['id' => $id, 'nome' => $nome, 'login' => $login];
        if ($id !== null) {
            header("Location: usuarios.php?acao=editar&id=$id");
        } else {
            header("Location: usuarios.php");
        }
        exit;
    }

    if ($id === null) {
        $loginExistente = $dao->buscaPorLogin($login);
        if ($loginExistente) {
            $_SESSION['erros_campo_usuario'] = ['login' => 'Este login já está em uso!'];
            $_SESSION['form_data_usuario'] = ['id' => null, 'nome' => $nome, 'login' => $login];
            header("Location: usuarios.php");
            exit;
        }
        $senhaHash = md5($senha);
        $usuario = new Usuario(null, $nome, $login, $senhaHash);
        if ($dao->insere($usuario)) {
            $_SESSION['mensagem_usuario'] = "Usuário cadastrado com sucesso!";
        } else {
            $_SESSION['erro_usuario'] = "Erro ao cadastrar usuário.";
        }
    } else {
        $usuario = $dao->buscaPorId($id);
        if ($usuario) {
            $usuario->setNome($nome);
            $usuario->setLogin($login);
            if ($senha) {
                $usuario->setSenha(md5($senha));
            }
            if ($dao->altera($usuario)) {
                $_SESSION['mensagem_usuario'] = "Usuário alterado com sucesso!";
            } else {
                $_SESSION['erro_usuario'] = "Erro ao alterar usuário.";
            }
        } else {
            $_SESSION['erro_usuario'] = "Usuário não encontrado.";
        }
    }
    header("Location: usuarios.php");
    exit;
}

// --- GET: Deletar ---
if (isset($_GET['acao']) && $_GET['acao'] === 'deletar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    if ($id === (int)$_SESSION['id_usuario']) {
        $_SESSION['erro_usuario'] = "Você não pode excluir o seu próprio usuário!";
    } elseif ($dao->removePorId($id)) {
        $_SESSION['mensagem_usuario'] = "Usuário excluído com sucesso!";
    } else {
        $_SESSION['erro_usuario'] = "Erro ao excluir usuário.";
    }
    header("Location: usuarios.php");
    exit;
}

// --- GET: Editar (carregar dados) ---
if (isset($_GET['acao']) && $_GET['acao'] === 'editar' && isset($_GET['id'])) {
    $usuarioEditar = $dao->buscaPorId((int)$_GET['id']);
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
if (isset($_SESSION['mensagem_usuario'])) {
    $mensagem = $_SESSION['mensagem_usuario'];
    unset($_SESSION['mensagem_usuario']);
}
if (isset($_SESSION['erro_usuario'])) {
    $erro = $_SESSION['erro_usuario'];
    unset($_SESSION['erro_usuario']);
}
if (isset($_SESSION['erros_campo_usuario'])) {
    $erros_campo = $_SESSION['erros_campo_usuario'];
    unset($_SESSION['erros_campo_usuario']);
}
if (isset($_SESSION['form_data_usuario'])) {
    $form_data = $_SESSION['form_data_usuario'];
    unset($_SESSION['form_data_usuario']);
}

// Helper: retorna valor do form_data (validação) > objeto editar > vazio
function valU($campo, $usuarioEditar, $form_data) {
    if (!empty($form_data) && isset($form_data[$campo])) return $form_data[$campo] ?? '';
    if ($usuarioEditar) {
        switch ($campo) {
            case 'nome': return $usuarioEditar->getNome();
            case 'login': return $usuarioEditar->getLogin();
        }
    }
    return '';
}
$isEdit = $usuarioEditar || (!empty($form_data) && isset($form_data['id']) && $form_data['id']);
$abrirModal = $isEdit || !empty($erros_campo);
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
    <h2>Usuários</h2>
    <div class="crud-filters">
        <select id="busca-tipo">
            <option value="nome">Nome</option>
            <option value="id">Código</option>
        </select>
        <input type="text" id="busca-valor" placeholder="Filtrar usuários...">
        <button type="button" onclick="buscarUsuarios()">Buscar</button>
        <button type="button" class="btn-limpar-filtro" onclick="document.getElementById('busca-valor').value=''; buscarUsuarios();">Listar Todos</button>
    </div>
    <button type="button" class="btn-cadastrar" onclick="abrirModalUsuario()">+ Cadastrar Usuário</button>
</div>

<table class="crud-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Login</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody id="tabela-body">
    <?php if (empty($lista)): ?>
        <tr><td colspan="4" class="sem-dados">Nenhum usuário encontrado.</td></tr>
    <?php else: ?>
        <?php foreach ($lista as $u): ?>
        <tr>
            <td><?php echo $u->getId(); ?></td>
            <td><?php echo htmlspecialchars($u->getNome()); ?></td>
            <td><?php echo htmlspecialchars($u->getLogin()); ?></td>
            <td>
                <a href="usuarios.php?acao=editar&id=<?php echo $u->getId(); ?>" class="btn-acao btn-editar">Editar</a>
                <a href="usuarios.php?acao=deletar&id=<?php echo $u->getId(); ?>" class="btn-acao btn-excluir"
                   onclick="return confirm('Confirma a exclusão do usuário <?php echo addslashes($u->getNome()); ?>?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<!-- MODAL -->
<div class="modal-overlay" id="modalUsuario">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitulo"><?php echo $isEdit ? 'Alterar Usuário' : 'Novo Usuário'; ?></h3>
            <button type="button" class="modal-close" onclick="fecharModal()">&times;</button>
        </div>
        <form method="POST" action="usuarios.php">
            <div class="modal-body">
                <?php if ($usuarioEditar): ?>
                    <input type="hidden" name="id" value="<?php echo $usuarioEditar->getId(); ?>">
                <?php elseif (!empty($form_data) && $form_data['id']): ?>
                    <input type="hidden" name="id" value="<?php echo (int)$form_data['id']; ?>">
                <?php endif; ?>

                <div class="form-group <?php echo isset($erros_campo['nome']) ? 'campo-erro' : ''; ?>">
                    <label for="nome">Nome:</label>
                    <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars(valU('nome', $usuarioEditar, $form_data)); ?>">
                    <?php if (isset($erros_campo['nome'])): ?>
                        <span class="erro-campo"><?php echo htmlspecialchars($erros_campo['nome']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group <?php echo isset($erros_campo['login']) ? 'campo-erro' : ''; ?>">
                    <label for="login">Login:</label>
                    <input type="text" id="login" name="login" value="<?php echo htmlspecialchars(valU('login', $usuarioEditar, $form_data)); ?>">
                    <?php if (isset($erros_campo['login'])): ?>
                        <span class="erro-campo"><?php echo htmlspecialchars($erros_campo['login']); ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group <?php echo isset($erros_campo['senha']) ? 'campo-erro' : ''; ?>">
                    <label for="senha">Senha:</label>
                    <input type="password" id="senha" name="senha">
                    <?php if ($isEdit): ?><small>Deixe em branco para manter a senha atual</small><?php endif; ?>
                    <?php if (isset($erros_campo['senha'])): ?>
                        <span class="erro-campo"><?php echo htmlspecialchars($erros_campo['senha']); ?></span>
                    <?php endif; ?>
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
function abrirModalUsuario() {
    document.getElementById('modalUsuario').classList.add('ativo');
}
function fecharModal() {
    document.getElementById('modalUsuario').classList.remove('ativo');
    // Se estava editando, limpa a URL
    if (window.location.search.includes('editar')) {
        window.history.replaceState({}, '', 'usuarios.php');
    }
}
// Fechar ao clicar fora
document.getElementById('modalUsuario').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});

// Abrir modal automaticamente se editando ou com erros
<?php if ($abrirModal): ?>
    abrirModalUsuario();
<?php endif; ?>

// Busca na tabela
function buscarUsuarios() {
    var tipo = document.getElementById('busca-tipo').value;
    var valor = document.getElementById('busca-valor').value.trim();
    window.location.href = valor ? 'usuarios.php?acao=buscar&tipo=' + tipo + '&valor=' + encodeURIComponent(valor) : 'usuarios.php';
}
document.getElementById('busca-valor').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') buscarUsuarios();
});
</script>
</main>
