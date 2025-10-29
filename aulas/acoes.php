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

function uploadArquivo($arquivo, $tipo) {
    if (!isset($arquivo['error']) || $arquivo['error'] !== 0) {
        return null;
    }

    $tipos_permitidos = [
        'video' => ['video/mp4'],
        'pdf' => ['application/pdf']
    ];

    if (!isset($tipos_permitidos[$tipo]) || !in_array($arquivo['type'], $tipos_permitidos[$tipo])) {
        throw new Exception('Tipo de arquivo não permitido. Envie apenas MP4 para vídeos ou PDF.');
    }

    $tamanho_maximo = 100 * 1024 * 1024; // 100MB
    if ($arquivo['size'] > $tamanho_maximo) {
        throw new Exception('O arquivo é muito grande. Tamanho máximo permitido: 100MB');
    }

    $diretorio = "../uploads/{$tipo}s/";
    if (!file_exists($diretorio)) {
        mkdir($diretorio, 0777, true);
    }

    $nome_arquivo = uniqid() . '_' . basename($arquivo['name']);
    $caminho_completo = $diretorio . $nome_arquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
        throw new Exception('Falha ao fazer upload do arquivo');
    }

    return "uploads/{$tipo}s/" . $nome_arquivo;
}

try {
    switch ($acao) {
        case 'listar':
            if (empty($_POST['curso_id'])) {
                throw new Exception('ID do curso não fornecido');
            }

            $stmt = $pdo->prepare("SELECT * FROM aulas WHERE curso_id = ? ORDER BY posicao, id");
            $stmt->execute([$_POST['curso_id']]);
            $aulas = $stmt->fetchAll();
            
            $response = ['status' => 'ok', 'dados' => $aulas];
            break;

        case 'salvar':
            if (empty($_POST['curso_id'])) {
                throw new Exception('ID do curso não fornecido');
            }

            $id = $_POST['id'] ?? '';
            $dados = [
                'curso_id' => $_POST['curso_id'],
                'titulo' => trim($_POST['titulo']),
                'tipo_arquivo' => $_POST['tipo_arquivo'],
                'posicao' => 0 // Será atualizado depois
            ];

            // Validações
            if (strlen($dados['titulo']) < 3 || strlen($dados['titulo']) > 255) {
                throw new Exception('O título deve ter entre 3 e 255 caracteres.');
            }

            // Verifica se o curso existe
            $stmt = $pdo->prepare("SELECT id FROM cursos WHERE id = ?");
            $stmt->execute([$dados['curso_id']]);
            if (!$stmt->fetch()) {
                throw new Exception('Curso não encontrado');
            }

            $pdo->beginTransaction();

            if (empty($id)) {
                // Busca a última posição
                $stmt = $pdo->prepare("SELECT MAX(posicao) FROM aulas WHERE curso_id = ?");
                $stmt->execute([$dados['curso_id']]);
                $dados['posicao'] = ($stmt->fetchColumn() ?? 0) + 1;

                // Inserir nova aula
                if ($_POST['tipo_arquivo'] === 'texto') {
                    $dados['conteudo'] = trim($_POST['conteudo'] ?? '');
                } else if (isset($_FILES['arquivo'])) {
                    $dados['caminho_arquivo'] = uploadArquivo($_FILES['arquivo'], $_POST['tipo_arquivo']);
                }

                $campos = array_keys($dados);
                $valores = array_values($dados);
                $sql = "INSERT INTO aulas (" . implode(',', $campos) . ") VALUES (" . str_repeat('?,', count($campos)-1) . "?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($valores);

                $response = ['status' => 'ok', 'mensagem' => 'Aula cadastrada com sucesso!'];
            } else {
                // Atualizar aula existente
                $aula_atual = $pdo->prepare("SELECT * FROM aulas WHERE id = ? AND curso_id = ?");
                $aula_atual->execute([$id, $dados['curso_id']]);
                $aula = $aula_atual->fetch();

                if (!$aula) {
                    throw new Exception('Aula não encontrada');
                }

                if ($_POST['tipo_arquivo'] === 'texto') {
                    $dados['conteudo'] = trim($_POST['conteudo'] ?? '');
                    $dados['caminho_arquivo'] = null;
                } else if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === 0) {
                    // Remove arquivo antigo se existir
                    if ($aula['caminho_arquivo'] && file_exists("../{$aula['caminho_arquivo']}")) {
                        unlink("../{$aula['caminho_arquivo']}");
                    }
                    $dados['caminho_arquivo'] = uploadArquivo($_FILES['arquivo'], $_POST['tipo_arquivo']);
                    $dados['conteudo'] = null;
                }

                $sets = [];
                $valores = [];
                foreach ($dados as $campo => $valor) {
                    $sets[] = "$campo = ?";
                    $valores[] = $valor;
                }
                $valores[] = $id;
                $valores[] = $dados['curso_id'];

                $sql = "UPDATE aulas SET " . implode(',', $sets) . " WHERE id = ? AND curso_id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($valores);

                $response = ['status' => 'ok', 'mensagem' => 'Aula atualizada com sucesso!'];
            }

            $pdo->commit();
            break;

        case 'excluir':
            if (empty($_POST['id']) || empty($_POST['curso_id'])) {
                throw new Exception('ID da aula ou do curso não fornecido');
            }
            
            $id = $_POST['id'];
            $curso_id = $_POST['curso_id'];
            
            // Verifica se a aula existe e pertence ao curso
            $stmt = $pdo->prepare("SELECT * FROM aulas WHERE id = ? AND curso_id = ?");
            $stmt->execute([$id, $curso_id]);
            $aula = $stmt->fetch();
            
            if (!$aula) {
                throw new Exception('Aula não encontrada');
            }
            
            $pdo->beginTransaction();
            
            // Remove o arquivo se existir
            if ($aula['caminho_arquivo'] && file_exists("../{$aula['caminho_arquivo']}")) {
                unlink("../{$aula['caminho_arquivo']}");
            }
            
            // Deleta a aula
            $stmt = $pdo->prepare("DELETE FROM aulas WHERE id = ? AND curso_id = ?");
            $stmt->execute([$id, $curso_id]);
            
            // Reordena as posições
            $stmt = $pdo->prepare("
                SET @pos = 0;
                UPDATE aulas 
                SET posicao = (@pos := @pos + 1) 
                WHERE curso_id = ? 
                ORDER BY posicao;
            ");
            $stmt->execute([$curso_id]);
            
            $pdo->commit();
            
            $response = ['status' => 'ok', 'mensagem' => 'Aula excluída com sucesso!'];
            break;

        case 'reordenar':
            if (empty($_POST['curso_id']) || empty($_POST['ordem'])) {
                throw new Exception('Dados de ordenação incompletos');
            }

            $curso_id = $_POST['curso_id'];
            $ordem = json_decode($_POST['ordem'], true);

            if (!is_array($ordem)) {
                throw new Exception('Formato de ordenação inválido');
            }

            $pdo->beginTransaction();

            foreach ($ordem as $posicao => $id) {
                $stmt = $pdo->prepare("UPDATE aulas SET posicao = ? WHERE id = ? AND curso_id = ?");
                $stmt->execute([$posicao + 1, $id, $curso_id]);
            }

            $pdo->commit();
            $response = ['status' => 'ok', 'mensagem' => 'Ordem atualizada com sucesso!'];
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