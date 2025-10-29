<?php
// Endpoint simples para login / registro / logout
header('Content-Type: application/json; charset=utf-8');
session_start();
include __DIR__ . '/../config/conexao.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
	if ($action === 'register') {
		$nome = trim($_POST['nome'] ?? '');
		$email = trim($_POST['email'] ?? '');
		$senha = $_POST['senha'] ?? '';

		if (!$nome || !$email || !$senha) throw new Exception('Todos os campos são obrigatórios');
		if (strlen($senha) < 8) throw new Exception('Senha deve ter ao menos 8 caracteres');

		// Verifica duplicidade
		$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
		$stmt->execute([$email]);
		if ($stmt->fetch()) throw new Exception('Email já cadastrado');

		$hash = password_hash($senha, PASSWORD_DEFAULT);
		$stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
		$stmt->execute([$nome, $email, $hash, 'aluno']);

		// Logar automaticamente
		$id = $pdo->lastInsertId();
		$_SESSION['user'] = ['id' => $id, 'nome' => $nome, 'email' => $email, 'tipo' => 'aluno'];

		echo json_encode(['status' => 'ok', 'mensagem' => 'Cadastro realizado', 'next' => $_POST['next'] ?? 'cursos.php']);
		exit;
	}

	if ($action === 'login') {
		$email = trim($_POST['email'] ?? '');
		$senha = $_POST['senha'] ?? '';
		if (!$email || !$senha) throw new Exception('Email e senha são obrigatórios');

		$stmt = $pdo->prepare("SELECT id, nome, email, senha, tipo FROM usuarios WHERE email = ? LIMIT 1");
		$stmt->execute([$email]);
		$u = $stmt->fetch(PDO::FETCH_ASSOC);
		if (!$u) throw new Exception('Usuário não encontrado');
		if (!password_verify($senha, $u['senha'])) throw new Exception('Senha incorreta');

		// Set session
		$_SESSION['user'] = ['id' => $u['id'], 'nome' => $u['nome'], 'email' => $u['email'], 'tipo' => $u['tipo']];
		echo json_encode(['status' => 'ok', 'mensagem' => 'Autenticado', 'next' => $_POST['next'] ?? 'cursos.php']);
		exit;
	}

	if ($action === 'logout' || ($_GET['action'] ?? '') === 'logout') {
		session_unset();
		session_destroy();
		// se chamada via link (GET), redireciona para página principal
		if(isset($_GET['action'])){
			header('Location: ../cursos.php');
			exit;
		}
		echo json_encode(['status' => 'ok', 'mensagem' => 'Logout realizado']);
		exit;
	}

	throw new Exception('Ação inválida');

} catch (Exception $e) {
	echo json_encode(['status' => 'erro', 'mensagem' => $e->getMessage()]);
	exit;
}

