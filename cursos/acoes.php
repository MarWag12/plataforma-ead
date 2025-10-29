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

function uploadImagem($arquivo) {
    if (!isset($arquivo['error']) || $arquivo['error'] !== 0) {
        return null;
    }

    $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($arquivo['type'], $tipos_permitidos)) {
        throw new Exception('Tipo de arquivo não permitido. Envie apenas JPG, PNG ou GIF.');
    }

    $tamanho_maximo = 5 * 1024 * 1024; // 5MB
    if ($arquivo['size'] > $tamanho_maximo) {
        throw new Exception('O arquivo é muito grande. Tamanho máximo permitido: 5MB');
    }

    $diretorio = '../uploads/img/cursos/';
    if (!file_exists($diretorio)) {
        mkdir($diretorio, 0777, true);
    }

    $nome_arquivo = uniqid() . '_' . basename($arquivo['name']);
    $caminho_completo = $diretorio . $nome_arquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
        throw new Exception('Falha ao fazer upload da imagem');
    }

    return 'uploads/img/cursos/' . $nome_arquivo;
}

try {
    switch ($acao) {
        case 'listar':
            $query = "SELECT c.*, u.nome as instrutor_nome 
                     FROM cursos c 
                     LEFT JOIN usuarios u ON c.instrutor_id = u.id 
                     ORDER BY c.id DESC";
            $cursos = $pdo->query($query)->fetchAll();
            $response = ['status' => 'ok', 'dados' => $cursos];
            break;

        case 'listar_instrutores':
            $query = "SELECT id, nome FROM usuarios WHERE tipo = 'instrutor' OR tipo = 'admin' ORDER BY nome";
            $instrutores = $pdo->query($query)->fetchAll();
            $response = ['status' => 'ok', 'dados' => $instrutores];
            break;

        case 'salvar':
            $id = $_POST['id'] ?? '';
            $dados = [
                'titulo' => trim($_POST['titulo']),
                'descricao_curta' => trim($_POST['descricao_curta'] ?? ''),
                'descricao_longa' => trim($_POST['descricao_longa'] ?? ''),
                'categoria' => trim($_POST['categoria'] ?? ''),
                'preco' => floatval($_POST['preco'] ?? 0),
                'instrutor_id' => $_POST['instrutor_id'],
                'status' => $_POST['status']
            ];

            // Validações
            if (strlen($dados['titulo']) < 3 || strlen($dados['titulo']) > 255) {
                throw new Exception('O título deve ter entre 3 e 255 caracteres.');
            }

            if (empty($dados['instrutor_id'])) {
                throw new Exception('Selecione um instrutor.');
            }

            // Verifica se o instrutor existe e tem permissão
            $stmt = $pdo->prepare("SELECT tipo FROM usuarios WHERE id = ?");
            $stmt->execute([$dados['instrutor_id']]);
            $instrutor = $stmt->fetch();
            if (!$instrutor || !in_array($instrutor['tipo'], ['instrutor', 'admin'])) {
                throw new Exception('Instrutor inválido ou sem permissão.');
            }

            $pdo->beginTransaction();

            if (empty($id)) {
                // Inserir novo
                $campos = array_keys($dados);
                $valores = array_values($dados);
                $sql = "INSERT INTO cursos (" . implode(',', $campos) . ") VALUES (" . str_repeat('?,', count($campos)-1) . "?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($valores);
                $id = $pdo->lastInsertId();

                // Upload da imagem se houver
                if (isset($_FILES['imagem'])) {
                    $caminho_imagem = uploadImagem($_FILES['imagem']);
                    if ($caminho_imagem) {
                        $stmt = $pdo->prepare("UPDATE cursos SET imagem_capa = ? WHERE id = ?");
                        $stmt->execute([$caminho_imagem, $id]);
                    }
                }

                $response = ['status' => 'ok', 'mensagem' => 'Curso cadastrado com sucesso!'];
            } else {
                // Atualizar existente
                $sets = [];
                $valores = [];
                foreach ($dados as $campo => $valor) {
                    $sets[] = "$campo = ?";
                    $valores[] = $valor;
                }
                $valores[] = $id;

                $sql = "UPDATE cursos SET " . implode(',', $sets) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($valores);

                // Upload da imagem se houver
                if (isset($_FILES['imagem'])) {
                    $caminho_imagem = uploadImagem($_FILES['imagem']);
                    if ($caminho_imagem) {
                        // Remove a imagem antiga se existir
                        $stmt = $pdo->prepare("SELECT imagem_capa FROM cursos WHERE id = ?");
                        $stmt->execute([$id]);
                        $imagem_antiga = $stmt->fetchColumn();
                        if ($imagem_antiga && file_exists("../$imagem_antiga")) {
                            unlink("../$imagem_antiga");
                        }

                        $stmt = $pdo->prepare("UPDATE cursos SET imagem_capa = ? WHERE id = ?");
                        $stmt->execute([$caminho_imagem, $id]);
                    }
                }

                $response = ['status' => 'ok', 'mensagem' => 'Curso atualizado com sucesso!'];
            }

            $pdo->commit();
            break;

        case 'excluir':
            if (empty($_POST['id'])) {
                throw new Exception('ID do curso não fornecido');
            }
            
            $id = $_POST['id'];
            
            // Verifica se o curso existe
            $stmt = $pdo->prepare("SELECT imagem_capa FROM cursos WHERE id = ?");
            $stmt->execute([$id]);
            $curso = $stmt->fetch();
            
            if (!$curso) {
                throw new Exception('Curso não encontrado');
            }
            
            $pdo->beginTransaction();
            
            // Remove a imagem se existir
            if ($curso['imagem_capa'] && file_exists("../{$curso['imagem_capa']}")) {
                unlink("../{$curso['imagem_capa']}");
            }
            
            // Deleta o curso
            $stmt = $pdo->prepare("DELETE FROM cursos WHERE id = ?");
            $stmt->execute([$id]);
            
            $pdo->commit();
            
            $response = ['status' => 'ok', 'mensagem' => 'Curso excluído com sucesso!'];
            break;

        default:
            throw new Exception('Ação inválida');
    }
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $response = ['status' => 'erro', 'mensagem' => $e->getMessage()];
}

echo json_encode($response);