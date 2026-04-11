<?php
// Ativa exibição de erros ANTES de qualquer outra coisa
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h1>Debug de Registro</h1>";
echo "<p>Script iniciado</p>";
flush();

// Tenta incluir fachada
echo "<h2>1. Incluindo fachada.php</h2>";
try {
    require "fachada.php";
    echo "<p style='color: green;'>✓ Fachada incluída</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
    exit;
}
flush();

// Inicia sessão
echo "<h2>2. Iniciando Sessão</h2>";
session_start();
echo "<p style='color: green;'>✓ Sessão iniciada</p>";
flush();

// Dados de teste
$nome = "Teste User";
$login = "testelogin123";
$senha = "senha123456";

// Teste 1: Verifica DAO Factory
echo "<h2>3. Verificando Factory</h2>";
if (isset($factory)) {
    echo "<p style='color: green;'>✓ Factory está definida</p>";
} else {
    echo "<p style='color: red;'>✗ Factory não está definida</p>";
    exit;
}
flush();

// Teste 2: Tenta obter DAO de Usuário
echo "<h2>4. Obtendo DAO de Usuário</h2>";
try {
    $dao = $factory->getUsuarioDao();
    echo "<p style='color: green;'>✓ DAO de usuário obtido</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
    exit;
}
flush();

// Teste 3: Verifica se login existe
echo "<h2>5. Buscando Login Existente</h2>";
try {
    $usuarioExistente = $dao->buscaPorLogin($login);
    if ($usuarioExistente) {
        echo "<p style='color: orange;'>Aviso: Login já existe, será descartado neste teste</p>";
    } else {
        echo "<p style='color: green;'>✓ Login disponível</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro na busca: " . $e->getMessage() . "</p>";
}
flush();

// Teste 4: Cria usuário
echo "<h2>6. Criando Objeto Usuario</h2>";
try {
    $senhaHash = md5($senha);
    $usuario = new Usuario(null, $nome, $login, $senhaHash, 'INTERNO');
    echo "<p style='color: green;'>✓ Objeto Usuario criado</p>";
    echo "<ul>";
    echo "<li>Nome: " . htmlspecialchars($usuario->getNome()) . "</li>";
    echo "<li>Login: " . htmlspecialchars($usuario->getLogin()) . "</li>";
    echo "<li>Perfil: " . htmlspecialchars($usuario->getPerfil()) . "</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
    exit;
}
flush();

// Teste 5: Insere no banco
echo "<h2>7. Inserindo no Banco</h2>";
try {
    $resultado = $dao->insere($usuario);
    if ($resultado) {
        echo "<p style='color: green;'>✓ Usuário inserido com sucesso!</p>";
    } else {
        echo "<p style='color: red;'>✗ Falha ao inserir (insere retornou false)</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro: " . $e->getMessage() . "</p>";
}
flush();

// Teste 6: Busca o usuário inserido
echo "<h2>8. Buscando Usuário Inserido</h2>";
try {
    $usuarioInserido = $dao->buscaPorLogin($login);
    if ($usuarioInserido) {
        echo "<p style='color: green;'>✓ Usuário encontrado!</p>";
        echo "<ul>";
        echo "<li>ID: " . $usuarioInserido->getId() . "</li>";
        echo "<li>Nome: " . htmlspecialchars($usuarioInserido->getNome()) . "</li>";
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>✗ Usuário não encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro na busca: " . $e->getMessage() . "</p>";
}
flush();

echo "<hr>";
echo "<p>Debug finalizado</p>";

?>

