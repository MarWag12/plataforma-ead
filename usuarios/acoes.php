<?php
// Configurações para API
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

// Previne que a página seja carregada diretamente pelo navegador
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'erro', 'mensagem' => 'Método não permitido']));
}

include '../config/conexao.php';

$acao = $_POST['acao'] ?? '';
$response = [];

function validarSenha($senha) {
    if (strlen($senha) < 8) return false;
    if (!preg_match('/[A-Z]/', $senha)) return false;
    if (!preg_match('/[a-z]/', $senha)) return false;
    if (!preg_match('/[0-9]/', $senha)) return false;
    if (!preg_match('/[^A-Za-z0-9]/', $senha)) return false;
    return true;
}

function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function emailExiste($pdo, $email, $id = null) {
    $sql = "SELECT id FROM usuarios WHERE email = ?";
    $params = [$email];
    
    if ($id) {
        $sql .= " AND id != ?";
        $params[] = $id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() !== false;
}

$tiposPermitidos = ['aluno', 'instrutor', 'admin'];

switch ($acao) {

    case 'listar':
        $usuarios = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC")->fetchAll();
        $response = ['status' => 'ok', 'dados' => $usuarios];
        break;

    case 'salvar':
        $id = $_POST['id'] ?? '';
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);
        $senha = $_POST['senha'] ?? '';
        $tipo = $_POST['tipo'] ?? 'aluno';

        // Validações
        if (strlen($nome) < 3 || strlen($nome) > 100) {
            $response = ['status' => 'erro', 'mensagem' => 'O nome deve ter entre 3 e 100 caracteres.'];
            break;
        }

        if (!validarEmail($email)) {
            $response = ['status' => 'erro', 'mensagem' => 'Email inválido.'];
            break;
        }

        if (!in_array($tipo, $tiposPermitidos)) {
            $response = ['status' => 'erro', 'mensagem' => 'Tipo de usuário inválido.'];
            break;
        }

        if (emailExiste($pdo, $email, $id)) {
            $response = ['status' => 'erro', 'mensagem' => 'Este email já está cadastrado.'];
            break;
        }

        if (empty($id)) {
            // Inserir novo
            if (empty($senha)) {
                $response = ['status' => 'erro', 'mensagem' => 'A senha é obrigatória para novos usuários.'];
                break;
            }

            if (!validarSenha($senha)) {
                $response = ['status' => 'erro', 'mensagem' => 'A senha deve ter no mínimo 8 caracteres, incluindo maiúsculas, minúsculas, números e caracteres especiais.'];
                break;
            }

            $hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $hash, $tipo]);
            $idNovo = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO auditoria_usuarios (usuario_id, acao, detalhes) VALUES (?, ?, ?)")
                ->execute([$idNovo, 'inserção', 'Usuário criado via AJAX']);
            $response = ['status' => 'ok', 'mensagem' => 'Usuário cadastrado com sucesso!'];
        } else {
            // Atualizar existente
            if (!empty($senha)) {
                $hash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, email=?, senha=?, tipo=? WHERE id=?");
                $stmt->execute([$nome, $email, $hash, $tipo, $id]);
            } else {
                $stmt = $pdo->prepare("UPDATE usuarios SET nome=?, email=?, tipo=? WHERE id=?");
                $stmt->execute([$nome, $email, $tipo, $id]);
            }
            $pdo->prepare("INSERT INTO auditoria_usuarios (usuario_id, acao, detalhes) VALUES (?, ?, ?)")
                ->execute([$id, 'edição', 'Usuário atualizado via AJAX']);
            $response = ['status' => 'ok', 'mensagem' => 'Usuário atualizado com sucesso!'];
        }
        break;

    case 'excluir':
        try {
            if (empty($_POST['id'])) {
                throw new Exception('ID do usuário não fornecido');
            }
            
            $id = $_POST['id'];
            
            // Verifica se o usuário existe
            $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            if (!$stmt->fetch()) {
                throw new Exception('Usuário não encontrado');
            }
            
            // Inicia a transação
            $pdo->beginTransaction();
            
            // Primeiro busca os dados do usuário
            $stmt = $pdo->prepare("SELECT nome, email FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                throw new Exception('Usuário não encontrado');
            }
            
            // Registra na auditoria com os dados do usuário
            $stmt = $pdo->prepare("INSERT INTO auditoria_usuarios (usuario_id, acao, detalhes, usuario_nome, usuario_email) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, 'exclusão', 'Usuário removido via AJAX', $usuario['nome'], $usuario['email']]);
            
            // Depois deleta o usuário
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            
            // Confirma a transação
            $pdo->commit();
            
            $response = ['status' => 'ok', 'mensagem' => 'Usuário excluído com sucesso!'];
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $response = ['status' => 'erro', 'mensagem' => 'Erro ao excluir usuário: ' . $e->getMessage()];
        }
        break;

    default:
        $response = ['status' => 'erro', 'mensagem' => 'Ação inválida'];
}

echo json_encode($response);
?>
