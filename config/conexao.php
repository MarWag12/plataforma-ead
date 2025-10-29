<?php
// Configuração para exibir erros apenas em ambiente de desenvolvimento
ini_set('display_errors', 0);
error_reporting(E_ALL);

// conexao.php
$host = 'localhost';
$dbname = 'plataforma_cursos1';
$usuario = 'root';
$senha = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $usuario, $senha, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die(json_encode(['status' => 'erro', 'mensagem' => 'Falha na conexão: ' . $e->getMessage()]));
}
?>
