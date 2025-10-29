<?php
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../config/conexao.php';

// Verificar conexão
try {
    $pdo->query("SELECT 1");
    $status['conexao'] = "OK";
} catch (PDOException $e) {
    $status['conexao'] = "ERRO: " . $e->getMessage();
}

// Verificar tabelas
try {
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $status['tabelas'] = $tables;
} catch (PDOException $e) {
    $status['tabelas'] = "ERRO: " . $e->getMessage();
}

// Verificar dados das tabelas principais
try {
    $counts = [];
    $tables_to_check = ['usuarios', 'cursos', 'matriculas'];
    foreach ($tables_to_check as $table) {
        if (in_array($table, $tables)) {
            $count = $pdo->query("SELECT COUNT(*) FROM $table")->fetchColumn();
            $counts[$table] = $count;
        } else {
            $counts[$table] = "Tabela não existe";
        }
    }
    $status['contagens'] = $counts;
} catch (PDOException $e) {
    $status['contagens'] = "ERRO: " . $e->getMessage();
}

echo json_encode($status, JSON_PRETTY_PRINT);