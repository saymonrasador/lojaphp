<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<pre>";
echo "=== TESTANDO CADA ARQUIVO DE FACHADA ===\n\n";

// Testa cada include de fachada.php separadamente
$files = [
    'model/Cliente.php',
    'model/Endereco.php',
    'model/Estoque.php',
    'model/Fornecedor.php',
    'model/ItemPedido.php',
    'model/Pedido.php',
    'model/Produto.php',
    'model/Usuario.php',
    'dao/DaoFactory.php',
    'dao/postgres/PostgresDaoFactory.php'
];

foreach ($files as $file) {
    echo "Testando: $file\n";
    
    if (!file_exists($file)) {
        echo "  ✗ ARQUIVO NÃO EXISTE\n\n";
        continue;
    }
    
    try {
        ob_start();
        require_once($file);
        $output = ob_get_clean();
        echo "  ✓ OK\n";
        if ($output) {
            echo "  Output: " . trim($output) . "\n";
        }
    } catch (Throwable $e) {
        ob_end_clean();
        echo "  ✗ ERRO: " . $e->getMessage() . "\n";
        echo "  Arquivo: " . $e->getFile() . "\n";
        echo "  Linha: " . $e->getLine() . "\n";
    }
    echo "\n";
}

echo "=== FIM DOS TESTES ===\n";
echo "</pre>";
?>
