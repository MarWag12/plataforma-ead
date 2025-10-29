<?php
// Configurações para API
ini_set('display_errors', 0);
error_reporting(E_ALL);
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'erro', 'mensagem' => 'Método não permitido']));
}

include '../config/conexao.php';

$acao = $_POST['acao'] ?? '';
$response = [];

// Função helper para exportar CSV
function exportarCSV($data, $filename = 'export.csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM para UTF-8
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

try {
    switch ($acao) {
        case 'listar':
            // opcional filtro por curso
            $curso_id = $_POST['curso_id'] ?? '';
            if (!empty($curso_id)) {
                $stmt = $pdo->prepare("SELECT m.*, u.nome as usuario_nome, c.titulo as curso_titulo, m.curso_id FROM matriculas m JOIN usuarios u ON m.usuario_id = u.id JOIN cursos c ON m.curso_id = c.id WHERE m.curso_id = ? ORDER BY m.data_matricula DESC");
                $stmt->execute([$curso_id]);
            } else {
                $stmt = $pdo->query("SELECT m.*, u.nome as usuario_nome, c.titulo as curso_titulo, m.curso_id FROM matriculas m JOIN usuarios u ON m.usuario_id = u.id JOIN cursos c ON m.curso_id = c.id ORDER BY m.data_matricula DESC");
            }
            $dados = $stmt->fetchAll();
            $response = ['status' => 'ok', 'dados' => $dados];
            break;

        case 'listar_usuarios':
            $stmt = $pdo->query("SELECT id, nome, email FROM usuarios ORDER BY nome");
            $response = ['status' => 'ok', 'dados' => $stmt->fetchAll()];
            break;

        case 'listar_cursos':
            $stmt = $pdo->query("SELECT id, titulo FROM cursos ORDER BY titulo");
            $response = ['status' => 'ok', 'dados' => $stmt->fetchAll()];
            break;

        case 'matricular':
            $usuario_id = $_POST['usuario_id'] ?? '';
            $curso_id = $_POST['curso_id'] ?? '';
            $status = $_POST['status'] ?? 'ativa';

            if (empty($usuario_id) || empty($curso_id)) {
                throw new Exception('Usuário e curso são obrigatórios');
            }

            // Verifica duplicidade
            $stmt = $pdo->prepare("SELECT id FROM matriculas WHERE usuario_id = ? AND curso_id = ?");
            $stmt->execute([$usuario_id, $curso_id]);
            if ($stmt->fetch()) {
                throw new Exception('Usuário já matriculado neste curso');
            }

            $stmt = $pdo->prepare("INSERT INTO matriculas (usuario_id, curso_id, status) VALUES (?, ?, ?)");
            $stmt->execute([$usuario_id, $curso_id, $status]);

            $response = ['status' => 'ok', 'mensagem' => 'Matricula realizada com sucesso'];
            break;

        case 'atualizar_progresso':
            $id = $_POST['id'] ?? '';
            $progresso = $_POST['progresso'] ?? '';
            if ($id === '' || $progresso === '') throw new Exception('Dados incompletos');
            $progresso = floatval($progresso);
            if ($progresso < 0) $progresso = 0; if ($progresso > 100) $progresso = 100;

            $stmt = $pdo->prepare("UPDATE matriculas SET progresso = ? WHERE id = ?");
            $stmt->execute([$progresso, $id]);
            $response = ['status' => 'ok', 'mensagem' => 'Progresso atualizado'];
            break;

        case 'mudar_status':
            $id = $_POST['id'] ?? '';
            $status = $_POST['status'] ?? '';
            if ($id === '' || $status === '') throw new Exception('Dados incompletos');
            $allowed = ['ativa','concluida','cancelada'];
            if (!in_array($status, $allowed)) throw new Exception('Status inválido');
            $stmt = $pdo->prepare("UPDATE matriculas SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            $response = ['status' => 'ok', 'mensagem' => 'Status atualizado'];
            break;

        case 'excluir':
            $id = $_POST['id'] ?? '';
            if (empty($id)) throw new Exception('ID não fornecido');
            $stmt = $pdo->prepare("DELETE FROM matriculas WHERE id = ?");
            $stmt->execute([$id]);
            $response = ['status' => 'ok', 'mensagem' => 'Matrícula excluída'];
            break;

        case 'progresso_agregado':
            $curso_id = $_POST['curso_id'] ?? '';
            $where = !empty($curso_id) ? "WHERE m.curso_id = :curso_id" : "";
            
            $query = "SELECT 
                        c.id as curso_id,
                        c.titulo as curso,
                        COUNT(m.id) as total_matriculas,
                        ROUND(AVG(m.progresso), 1) as media_progresso,
                        COUNT(CASE WHEN m.status = 'concluida' THEN 1 END) as concluidos,
                        COUNT(CASE WHEN m.status = 'ativa' THEN 1 END) as ativos,
                        COUNT(CASE WHEN m.status = 'cancelada' THEN 1 END) as cancelados
                     FROM cursos c 
                     LEFT JOIN matriculas m ON c.id = m.curso_id
                     $where
                     GROUP BY c.id, c.titulo
                     ORDER BY c.titulo";
            
            if (!empty($curso_id)) {
                $stmt = $pdo->prepare($query);
                $stmt->execute(['curso_id' => $curso_id]);
            } else {
                $stmt = $pdo->query($query);
            }
            
            $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response = ['status' => 'ok', 'dados' => $dados];
            break;

        case 'exportar_csv':
            $curso_id = $_POST['curso_id'] ?? '';
            $query = "SELECT 
                        m.id, u.nome as usuario, u.email, c.titulo as curso,
                        m.data_matricula, m.progresso, m.status
                     FROM matriculas m 
                     JOIN usuarios u ON m.usuario_id = u.id 
                     JOIN cursos c ON m.curso_id = c.id";
            
            if (!empty($curso_id)) {
                $query .= " WHERE m.curso_id = :curso_id";
                $stmt = $pdo->prepare($query);
                $stmt->execute(['curso_id' => $curso_id]);
            } else {
                $stmt = $pdo->query($query);
            }
            
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($data)) throw new Exception('Nenhum dado para exportar');
            
            // Prepare CSV data
            $csvData = [];
            $csvData[] = ['ID', 'Usuário', 'Email', 'Curso', 'Data Matrícula', 'Progresso', 'Status'];
            foreach ($data as $row) {
                $csvData[] = [
                    $row['id'],
                    $row['usuario'],
                    $row['email'],
                    $row['curso'],
                    $row['data_matricula'],
                    $row['progresso'] . '%',
                    $row['status']
                ];
            }
            
            exportarCSV($csvData, 'matriculas_' . date('Y-m-d') . '.csv');
            break;

        default:
            throw new Exception('Ação inválida');
    }
} catch (Exception $e) {
    $response = ['status' => 'erro', 'mensagem' => $e->getMessage()];
}

echo json_encode($response);
