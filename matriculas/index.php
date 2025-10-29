<?php 
include '../helpers/auth.php';
$user = require_login();
include '../config/conexao.php'; 
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Matrículas</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255,255,255,0.8);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}
.btn-success.saving {
    position: relative;
    pointer-events: none;
    opacity: 0.7;
}
.btn-success.saving:after {
    content: '';
    width: 1em;
    height: 1em;
    border: 2px solid #fff;
    border-radius: 50%;
    border-right-color: transparent;
    display: inline-block;
    margin-left: 0.5em;
    animation: spin 0.8s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}
.saved-check {
    color: #198754;
    display: none;
    margin-left: 0.5rem;
}
.table .btn-group .btn i {
    font-size: 0.875rem;
}
.estatistica-card {
    border-radius: 0.5rem;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
    transition: transform 0.2s;
}
.estatistica-card:hover {
    transform: translateY(-2px);
}
.estatistica-card .progress {
    height: 0.5rem;
}
.estatistica-card .small {
    font-size: 0.875rem;
    color: #6c757d;
}
.curso-stats {
    display: flex;
    gap: 1rem;
    font-size: 0.875rem;
}
.curso-stats span {
    display: inline-flex;
    align-items: center;
}
.curso-stats i {
    margin-right: 0.25rem;
}
</style>
</head>
<body class="p-4">
<div class="loading-overlay">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Carregando...</span>
    </div>
</div>
<div class="container">
  <h2 class="mb-4">Matrículas & Progresso</h2>

  <div id="progressoAgregado" class="card mb-4">
    <div class="card-body">
      <h4 class="card-title h5 mb-3">Progresso dos Cursos</h4>
      <div class="row g-3" id="estatisticasCursos">
        <!-- Preenchido via JavaScript -->
      </div>
    </div>
  </div>

  <div class="d-flex mb-3">
    <button class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#modalMatricula">+ Matricular Usuário</button>
    <div class="ms-auto">
      <select id="filtroCurso" class="form-select">
        <option value="">Filtrar por curso (todos)</option>
      </select>
    </div>
  </div>

  <table class="table table-bordered" id="tabelaMatriculas">
    <thead>
      <tr>
        <th>ID</th>
        <th>Usuário</th>
        <th>Curso</th>
        <th>Data</th>
        <th>Progresso</th>
        <th>Status</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- Modal Matricular -->
<div class="modal fade" id="modalMatricula" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formMatricula">
        <div class="modal-header">
          <h5 class="modal-title">Matricular Usuário</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label>Usuário</label>
            <select id="usuario_id" name="usuario_id" class="form-select" required>
              <option value="">Carregando...</option>
            </select>
          </div>
          <div class="mb-3">
            <label>Curso</label>
            <select id="curso_id" name="curso_id" class="form-select" required>
              <option value="">Carregando...</option>
            </select>
          </div>
          <div class="mb-3">
            <label>Status</label>
            <select name="status" id="status" class="form-select">
              <option value="ativa">Ativa</option>
              <option value="concluida">Concluída</option>
              <option value="cancelada">Cancelada</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Matricular</button>
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