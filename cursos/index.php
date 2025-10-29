<?php include '../config/conexao.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="p-4">
    <div class="container">
        <h2 class="mb-4">Gerenciamento de Cursos</h2>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalCurso">+ Novo Curso</button>

        <table class="table table-bordered" id="tabelaCursos">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Instrutor</th>
                    <th>Categoria</th>
                    <th>Preço</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <!-- Modal de Cadastro/Edição -->
    <div class="modal fade" id="modalCurso" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formCurso">
                    <div class="modal-header">
                        <h5 class="modal-title">Novo Curso</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        
                        <div class="row mb-3">
                            <div class="col-md-8">
                                <label>Título</label>
                                <input type="text" name="titulo" id="titulo" class="form-control" required minlength="3" maxlength="255">
                                <div class="invalid-feedback">Título é obrigatório e deve ter entre 3 e 255 caracteres.</div>
                            </div>
                            <div class="col-md-4">
                                <label>Categoria</label>
                                <input type="text" name="categoria" id="categoria" class="form-control" maxlength="100">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Instrutor</label>
                                <select name="instrutor_id" id="instrutor_id" class="form-control" required>
                                    <option value="">Selecione...</option>
                                </select>
                                <div class="invalid-feedback">Selecione um instrutor.</div>
                            </div>
                            <div class="col-md-3">
                                <label>Preço</label>
                                <input type="number" name="preco" id="preco" class="form-control" step="0.01" min="0">
                            </div>
                            <div class="col-md-3">
                                <label>Status</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="rascunho">Rascunho</option>
                                    <option value="publicado">Publicado</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Descrição Curta</label>
                            <input type="text" name="descricao_curta" id="descricao_curta" class="form-control" maxlength="255">
                            <small class="form-text text-muted">Breve descrição para listagem do curso (máx. 255 caracteres)</small>
                        </div>

                        <div class="mb-3">
                            <label>Descrição Completa</label>
                            <textarea name="descricao_longa" id="descricao_longa" class="form-control" rows="4"></textarea>
                        </div>

                        <div class="mb-3">
                            <label>Imagem de Capa</label>
                            <input type="file" name="imagem" id="imagem" class="form-control" accept="image/*">
                            <div id="previewImagem" class="mt-2"></div>
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

    <script src="script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>