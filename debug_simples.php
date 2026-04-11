<?php
// Debug muito simples - sem nada que possa quebrar
header('Content-Type: text/html; charset=utf-8');

echo "<pre>";
echo "=== DEBUG SIMPLES ===\n";
echo "Data: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "\n";

// Ativa todos os erros
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Tenta carregar fachada
echo "Tentando require 'fachada.php'...\n";
ob_start();

try {
    require_once('fachada.php');
    $output = ob_get_clean();
    echo "OK - Fachada carregada\n";
    if ($output) {
        echo "Output da fachada:\n";
        echo $output . "\n";
    }
} catch (Throwable $e) {
    ob_end_clean();
    echo "ERRO CAPTURADO:\n";
    echo "Tipo: " . get_class($e) . "\n";
    echo "Mensagem: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
    echo "\n";
    echo "Trace:\n";
    echo $e->getTraceAsString() . "\n";
}

// Tenta verificar a factory
if (isset($factory)) {
    echo "\n✓ Factory foi criada com sucesso\n";
} else {
    echo "\n✗ Factory NÃO foi criada\n";
}

echo "</pre>";
?>
