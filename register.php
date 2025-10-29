<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Cadastro</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="p-4">
<div class="container">
  <h2 class="mb-4">Criar Conta</h2>
  <form id="formRegister" class="w-50">
    <div class="mb-3">
      <label>Nome</label>
      <input type="text" name="nome" id="nome" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" id="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Senha</label>
      <input type="password" name="senha" id="senha" class="form-control" required minlength="8">
    </div>
    <button class="btn btn-primary" id="btnCadastrar">Criar conta</button>
    <a href="login.php" class="btn btn-link">Já tenho conta</a>
  </form>
</div>

<script>
$('#formRegister').submit(function(e){
  e.preventDefault();
  const dados = $(this).serialize() + '&action=register';
  $('#btnCadastrar').prop('disabled', true).text('Processando...');
  $.post('helpers/login.php', dados, function(ret){
    $('#btnCadastrar').prop('disabled', false).text('Criar conta');
    if(ret.status==='ok'){
      Swal.fire('Sucesso', ret.mensagem, 'success').then(()=>{
        window.location.href = ret.next || 'cursos.php';
      });
    } else {
      Swal.fire('Erro', ret.mensagem, 'error');
    }
  }, 'json').fail(function(){
    $('#btnCadastrar').prop('disabled', false).text('Criar conta');
    Swal.fire('Erro', 'Falha na requisição', 'error');
  });
});
</script>
</body>
</html>