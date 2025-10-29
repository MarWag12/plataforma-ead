<?php
// Helper para verificar se usuário está logado
function require_login() {
    session_start();
    if (empty($_SESSION['user'])) {
        // Se for requisição AJAX, retorna erro em JSON
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'erro', 'mensagem' => 'Autenticação necessária']);
            exit;
        }
        // Se não, redireciona para login com ?next= para voltar
        $next = urlencode($_SERVER['REQUEST_URI']);
        header("Location: /login.php?next=$next");
        exit;
    }
    return $_SESSION['user'];
}