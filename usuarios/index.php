<?php include '../config/conexao.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Gestão de Usuários</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/password-strength-meter/dist/password-strength-meter.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/password-strength-meter"></script>
</head>
<body class="p-4">
<div class="container">
  <h2 class="mb-4">Gerenciamento de Usuários</h2>
  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalUsuario">+ Novo Usuário</button>

  <table class="table table-bordered" id="tabelaUsuarios">
    <thead>
      <tr>
        <th>ID</th><th>Nome</th><th>Email</th><th>Tipo</th><th>Ações</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- Modal de Cadastro/Edição -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="formUsuario">
        <div class="modal-header">
          <h5 class="modal-title">Novo Usuário</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id" id="id">
          <div class="mb-3">
            <label>Nome</label>
            <input type="text" name="nome" id="nome" class="form-control" required minlength="3" maxlength="100">
            <div class="invalid-feedback">Nome é obrigatório e deve ter entre 3 e 100 caracteres.</div>
          </div>
          <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" id="email" class="form-control" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
            <div class="invalid-feedback">Por favor, insira um email válido.</div>
          </div>
          <div class="mb-3">
            <label>Senha</label>
            <input type="password" name="senha" id="senha" class="form-control" minlength="8">
            <div id="senhaForca" class="mt-2"></div>
            <small class="form-text text-muted">Mínimo 8 caracteres, deve conter letra maiúscula, minúscula, número e caractere especial.</small>
            <div class="invalid-feedback">A senha deve ter no mínimo 8 caracteres.</div>
          </div>
          <div class="mb-3">
            <label>Tipo</label>
            <select name="tipo" id="tipo" class="form-control" required>
              <option value="aluno">Aluno</option>
              <option value="instrutor">Instrutor</option>
              <option value="admin">Admin</option>
            </select>
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
