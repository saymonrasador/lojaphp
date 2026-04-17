<?php
require "verifica.php";
require "fachada.php";

header('Content-Type: application/json; charset=utf-8');

$dao   = $factory->getProdutoDao();
$tipo  = $_GET['tipo']  ?? 'nome';
$valor = trim($_GET['valor'] ?? '');

$lista = [];

if ($valor === '') {
    $lista = $dao->buscaTodos();
} elseif ($tipo === 'id') {
    $found = $dao->buscaPorId((int)$valor);
    $lista = $found ? [$found] : [];
} else {
    $lista = $dao->buscaPorNome($valor);
}

$resultado = [];
foreach ($lista as $p) {
    $resultado[] = [
        'id'         => $p->getId(),
        'nome'       => $p->getNome(),
        'descricao'  => $p->getDescricao(),
        'fornecedor' => $p->getFornecedor() ? $p->getFornecedor()->getNome() : '-',
    ];
}

echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
