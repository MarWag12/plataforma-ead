$(document).ready(function () {
  listarUsuarios();

  // Validar força da senha
  function validarForcaSenha(senha) {
    let forca = 0;
    if (senha.length >= 8) forca++;
    if (/[A-Z]/.test(senha)) forca++;
    if (/[a-z]/.test(senha)) forca++;
    if (/[0-9]/.test(senha)) forca++;
    if (/[^A-Za-z0-9]/.test(senha)) forca++;
    return forca;
  }

  // Atualizar indicador de força da senha
  $('#senha').on('input', function() {
    const senha = $(this).val();
    const forca = validarForcaSenha(senha);
    const forcaTexto = ['Muito fraca', 'Fraca', 'Média', 'Forte', 'Muito forte'];
    const forcaCor = ['danger', 'warning', 'info', 'primary', 'success'];
    
    if (senha.length > 0) {
      $('#senhaForca').html(`
        <div class="progress">
          <div class="progress-bar bg-${forcaCor[forca-1]}" role="progressbar" 
               style="width: ${forca*20}%" aria-valuenow="${forca*20}" aria-valuemin="0" 
               aria-valuemax="100"></div>
        </div>
        <small class="text-${forcaCor[forca-1]} mt-1 d-block">${forcaTexto[forca-1]}</small>
      `);
    } else {
      $('#senhaForca').html('');
    }
  });

  // Limpar formulário ao abrir modal para novo usuário
  $('.btn-primary[data-bs-toggle="modal"]').click(function() {
    $('#formUsuario')[0].reset();
    $('#id').val('');
    $('.modal-title').text('Novo Usuário');
    $('#senha').prop('required', true);
  });

  // Listar usuários
  function listarUsuarios() {
    $.post('acoes.php', { acao: 'listar' }, function (ret) {
      if (ret.status === 'ok') {
        let linhas = '';
        ret.dados.forEach(u => {
          linhas += `
            <tr>
              <td>${u.id}</td>
              <td>${u.nome}</td>
              <td>${u.email}</td>
              <td>${u.tipo}</td>
              <td>
                <button class="btn btn-sm btn-warning editar" data-id="${u.id}">Editar</button>
                <button class="btn btn-sm btn-danger excluir" data-id="${u.id}">Excluir</button>
              </td>
            </tr>`;
        });
        $('#tabelaUsuarios tbody').html(linhas);
      }
    }, 'json')
    .fail(function(jqXHR, textStatus, errorThrown) {
      Swal.fire('Erro!', 'Erro ao carregar usuários: ' + errorThrown, 'error');
    });
  }

  // Salvar (novo ou edição)
$('#formUsuario').submit(function (e) {
  e.preventDefault();

  // Desativar o botão e mostrar spinner
  const btn = $('#btnSalvar');
  const spinner = $('#spinnerSalvar');
  const texto = $('#textoSalvar');

  btn.prop('disabled', true);
  spinner.removeClass('d-none');
  texto.text('Salvando...');

  const dados = $(this).serialize() + '&acao=salvar';

  $.post('acoes.php', dados, function (ret) {
    if (ret.status === 'ok') {
      Swal.fire({
        icon: 'success',
        title: 'Sucesso!',
        text: ret.mensagem,
        timer: 9500,
        showConfirmButton: false
      });

      $('#modalUsuario').modal('hide');
      $('#formUsuario')[0].reset();
      listarUsuarios();
    } else {
      Swal.fire('Erro!', ret.mensagem, 'error');
    }
  }, 'json')
  .fail(function () {
    Swal.fire('Erro!', 'Ocorreu um erro na requisição.', 'error');
  })
  .always(function () {
    // Reativar botão e esconder spinner
    btn.prop('disabled', false);
    spinner.addClass('d-none');
    texto.text('Salvar');
  });
});


  // Editar usuário
  $(document).on('click', '.editar', function () {
    const id = $(this).data('id');
    $.post('acoes.php', { acao: 'listar' }, function (ret) {
      const u = ret.dados.find(x => x.id == id);
      if (u) {
        $('#id').val(u.id);
        $('#nome').val(u.nome);
        $('#email').val(u.email);
        $('#tipo').val(u.tipo);
        $('#senha').val('').prop('required', false);
        $('.modal-title').text('Editar Usuário');
        $('#modalUsuario').modal('show');
      }
    }, 'json')
    .fail(function(jqXHR, textStatus, errorThrown) {
      Swal.fire('Erro!', 'Erro ao carregar dados do usuário: ' + errorThrown, 'error');
    });
  });

  // Excluir
  $(document).on('click', '.excluir', function () {
    const id = $(this).data('id');
    Swal.fire({
      title: 'Confirma a exclusão?',
      text: "Esta ação não poderá ser desfeita!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sim, excluir!',
      cancelButtonText: 'Cancelar'
    }).then((result) => {
      if (result.isConfirmed) {
        $.post('acoes.php', { acao: 'excluir', id: id }, function (ret) {
          if (ret.status === 'ok') {
            Swal.fire('Feito!', ret.mensagem, 'success');
            listarUsuarios();
          } else {
            Swal.fire('Erro!', ret.mensagem, 'error');
          }
        }, 'json')
        .fail(function(jqXHR, textStatus, errorThrown) {
          Swal.fire('Erro!', 'Erro ao excluir usuário: ' + errorThrown, 'error');
        });
      }
    });
  });

});
