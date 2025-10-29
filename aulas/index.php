<?php 
include '../config/conexao.php';

// Obter o ID do curso da URL
$curso_id = isset($_GET['curso_id']) ? intval($_GET['curso_id']) : 0;

// Buscar informações do curso
$stmt = $pdo->prepare("SELECT c.*, u.nome as instrutor_nome 
                       FROM cursos c 
                       LEFT JOIN usuarios u ON c.instrutor_id = u.id 
                       WHERE c.id = ?");
$stmt->execute([$curso_id]);
$curso = $stmt->fetch();

if (!$curso) {
    die('Curso não encontrado');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Aulas - <?php echo htmlspecialchars($curso['titulo']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="p-4">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../cursos/">Cursos</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($curso['titulo']); ?></li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Aulas do Curso</h2>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAula">+ Nova Aula</button>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <strong>Curso:</strong> <?php echo htmlspecialchars($curso['titulo']); ?><br>
                <strong>Instrutor:</strong> <?php echo htmlspecialchars($curso['instrutor_nome']); ?>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="listaAulas">
                    <!-- As aulas serão carregadas aqui via JavaScript -->
                </ul>
            </div>
        </div>
    </div>

    <!-- Modal de Cadastro/Edição -->
    <div class="modal fade" id="modalAula" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formAula">
                    <div class="modal-header">
                        <h5 class="modal-title">Nova Aula</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        <input type="hidden" name="curso_id" id="curso_id" value="<?php echo $curso_id; ?>">
                        
                        <div class="mb-3">
                            <label>Título da Aula</label>
                            <input type="text" name="titulo" id="titulo" class="form-control" required minlength="3" maxlength="255">
                            <div class="invalid-feedback">Título é obrigatório e deve ter entre 3 e 255 caracteres.</div>
                        </div>

                        <div class="mb-3">
                            <label>Tipo de Conteúdo</label>
                            <select name="tipo_arquivo" id="tipo_arquivo" class="form-control" required>
                                <option value="video">Vídeo</option>
                                <option value="pdf">PDF</option>
                                <option value="texto">Texto</option>
                                <option value="link">Link (externo)</option>
                            </select>
                        </div>

                        <!-- Campo para upload de arquivo (vídeo/PDF) -->
                        <div class="mb-3" id="campoArquivo">
                            <label>Arquivo</label>
                            <input type="file" name="arquivo" id="arquivo" class="form-control">
                            <small class="form-text text-muted">
                                Formatos permitidos: MP4, PDF (máx. 100MB)
                            </small>
                            <div id="previewArquivo" class="mt-2"></div>
                        </div>

                        <!-- Campo para link externo -->
                        <div class="mb-3" id="campoLink" style="display:none;">
                            <label>Link (URL)</label>
                            <input type="url" name="link" id="link" class="form-control" placeholder="https://example.com/external-resource">
                            <div id="previewLink" class="mt-2"></div>
                        </div>

                        <!-- Campo para conteúdo em texto -->
                        <div class="mb-3" id="campoConteudo" style="display:none;">
                            <label>Conteúdo da Aula</label>
                            <textarea name="conteudo" id="conteudo" class="form-control" rows="10"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" id="btnSalvar" class="btn btn-success">
                            <span class="spinner-border spinner-border-sm d-none" id="spinnerSalvar" role="status" aria-hidden="true"></span>
                            <span id="textoSalvar">Salvar</span>
                        </button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Passa o ID do curso para o JavaScript
        const CURSO_ID = <?php echo $curso_id; ?>;
    </script>
    <script src="script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>