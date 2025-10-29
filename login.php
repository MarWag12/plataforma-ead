<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="p-4">
<div class="container">
  <h2 class="mb-4">Entrar</h2>
  <form id="formLogin" class="w-50">
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" id="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Senha</label>
      <input type="password" name="senha" id="senha" class="form-control" required>
    </div>
    <input type="hidden" id="next" name="next" value="<?php echo isset($_GET['next'])?htmlspecialchars($_GET['next']):'cursos.php'; ?>">
    <button class="btn btn-primary" id="btnEntrar">Entrar</button>
    <a href="register.php" class="btn btn-link">Criar conta</a>
  </form>
</div>

<script>
$('#formLogin').submit(function(e){
  e.preventDefault();
  const dados = $(this).serialize() + '&action=login';
  $('#btnEntrar').prop('disabled', true).text('Entrando...');
  $.post('helpers/login.php', dados, function(ret){
    $('#btnEntrar').prop('disabled', false).text('Entrar');
    if(ret.status==='ok'){
      window.location.href = ret.next || $('#next').val() || 'cursos.php';
    } else {
      Swal.fire('Erro', ret.mensagem, 'error');
    }
  }, 'json').fail(function(){
    $('#btnEntrar').prop('disabled', false).text('Entrar');
    Swal.fire('Erro', 'Falha na requisição', 'error');
  });
});
</script>
</body>
</html>