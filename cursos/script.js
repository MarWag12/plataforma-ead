$(document).ready(function () {
    listarCursos();
    carregarInstrutores();

    // Preview de imagem
    $('#imagem').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImagem').html(`
                    <img src="${e.target.result}" class="img-thumbnail" style="max-height: 200px">
                `);
            }
            reader.readAsDataURL(file);
        } else {
            $('#previewImagem').empty();
        }
    });

    // Limpar formulário ao abrir modal para novo curso
    $('.btn-primary[data-bs-toggle="modal"]').click(function() {
        $('#formCurso')[0].reset();
        $('#id').val('');
        $('.modal-title').text('Novo Curso');
        $('#previewImagem').empty();
    });

    // Listar cursos
    function listarCursos() {
        $.post('acoes.php', { acao: 'listar' }, function (ret) {
            if (ret.status === 'ok') {
                let linhas = '';
                ret.dados.forEach(c => {
                    linhas += `
                        <tr>
                            <td>${c.id}</td>
                            <td>${c.titulo}</td>
                            <td>${c.instrutor_nome}</td>
                            <td>${c.categoria || '-'}</td>
                            <td>R$ ${parseFloat(c.preco).toFixed(2)}</td>
                            <td>
                                <span class="badge bg-${c.status === 'publicado' ? 'success' : 'warning'}">
                                    ${c.status}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-info detalhes" data-id="${c.id}">
                                        <i class="bi bi-info-circle"></i> Detalhes
                                    </button>
                                    <button class="btn btn-sm btn-warning editar" data-id="${c.id}">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-danger excluir" data-id="${c.id}">
                                        <i class="bi bi-trash"></i> Excluir
                                    </button>
                                </div>
                            </td>
                        </tr>`;
                });
                $('#tabelaCursos tbody').html(linhas);
            }
        }, 'json')
        .fail(function(jqXHR, textStatus, errorThrown) {
            Swal.fire('Erro!', 'Erro ao carregar cursos: ' + errorThrown, 'error');
        });
    }

    // Carregar lista de instrutores
    function carregarInstrutores() {
        $.post('acoes.php', { acao: 'listar_instrutores' }, function (ret) {
            if (ret.status === 'ok') {
                let opcoes = '<option value="">Selecione...</option>';
                ret.dados.forEach(i => {
                    opcoes += `<option value="${i.id}">${i.nome}</option>`;
                });
                $('#instrutor_id').html(opcoes);
            }
        }, 'json')
        .fail(function(jqXHR, textStatus, errorThrown) {
            Swal.fire('Erro!', 'Erro ao carregar instrutores: ' + errorThrown, 'error');
        });
    }

    // Salvar curso
    $('#formCurso').submit(function (e) {
        e.preventDefault();

        const btn = $('#btnSalvar');
        const spinner = $('#spinnerSalvar');
        const texto = $('#textoSalvar');

        btn.prop('disabled', true);
        spinner.removeClass('d-none');
        texto.text('Salvando...');

        // Usar FormData para permitir upload de arquivo
        const formData = new FormData(this);
        formData.append('acao', 'salvar');

        $.ajax({
            url: 'acoes.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(ret) {
                if (ret.status === 'ok') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sucesso!',
                        text: ret.mensagem,
                        timer: 2500,
                        showConfirmButton: false
                    });

                    $('#modalCurso').modal('hide');
                    $('#formCurso')[0].reset();
                    $('#previewImagem').empty();
                    listarCursos();
                } else {
                    Swal.fire('Erro!', ret.mensagem, 'error');
                }
            },
            error: function() {
                Swal.fire('Erro!', 'Ocorreu um erro na requisição.', 'error');
            },
            complete: function() {
                btn.prop('disabled', false);
                spinner.addClass('d-none');
                texto.text('Salvar');
            }
        });
    });

    // Editar curso
    $(document).on('click', '.editar', function () {
        const id = $(this).data('id');
        $.post('acoes.php', { acao: 'listar' }, function (ret) {
            const c = ret.dados.find(x => x.id == id);
            if (c) {
                $('#id').val(c.id);
                $('#titulo').val(c.titulo);
                $('#descricao_curta').val(c.descricao_curta);
                $('#descricao_longa').val(c.descricao_longa);
                $('#categoria').val(c.categoria);
                $('#preco').val(c.preco);
                $('#instrutor_id').val(c.instrutor_id);
                $('#status').val(c.status);
                
                if (c.imagem_capa) {
                    $('#previewImagem').html(`
                        <img src="../${c.imagem_capa}" class="img-thumbnail" style="max-height: 200px">
                    `);
                } else {
                    $('#previewImagem').empty();
                }

                $('.modal-title').text('Editar Curso');
                $('#modalCurso').modal('show');
            }
        }, 'json')
        .fail(function(jqXHR, textStatus, errorThrown) {
            Swal.fire('Erro!', 'Erro ao carregar dados do curso: ' + errorThrown, 'error');
        });
    });

    // Visualizar detalhes do curso
    $(document).on('click', '.detalhes', function () {
        const id = $(this).data('id');
        $.post('acoes.php', { acao: 'listar' }, function (ret) {
            const c = ret.dados.find(x => x.id == id);
            if (c) {
                // Criar o conteúdo do modal de detalhes
                let imagemHtml = c.imagem_capa 
                    ? `<img src="../${c.imagem_capa}" class="img-fluid mb-3" style="max-height: 300px;">`
                    : '<div class="alert alert-info">Sem imagem de capa</div>';

                Swal.fire({
                    title: c.titulo,
                    html: `
                        <div class="text-start">
                            ${imagemHtml}
                            <div class="mb-3">
                                <strong>Instrutor:</strong> ${c.instrutor_nome}
                            </div>
                            <div class="mb-3">
                                <strong>Categoria:</strong> ${c.categoria || '-'}
                            </div>
                            <div class="mb-3">
                                <strong>Preço:</strong> R$ ${parseFloat(c.preco).toFixed(2)}
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong> 
                                <span class="badge bg-${c.status === 'publicado' ? 'success' : 'warning'}">
                                    ${c.status}
                                </span>
                            </div>
                            <div class="mb-3">
                                <strong>Descrição Curta:</strong><br>
                                ${c.descricao_curta || '-'}
                            </div>
                            <div class="mb-3">
                                <strong>Descrição Completa:</strong><br>
                                ${c.descricao_longa || '-'}
                            </div>
                            <hr>
                            <div class="text-center">
                                <a href="../aulas/index.php?curso_id=${c.id}" class="btn btn-primary">
                                    <i class="bi bi-collection-play"></i> Gerenciar Aulas
                                </a>
                            </div>
                        </div>
                    `,
                    width: '800px',
                    showCloseButton: true,
                    showConfirmButton: false
                });
            }
        }, 'json')
        .fail(function(jqXHR, textStatus, errorThrown) {
            Swal.fire('Erro!', 'Erro ao carregar detalhes do curso: ' + errorThrown, 'error');
        });
    });

    // Excluir curso
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
                        listarCursos();
                    } else {
                        Swal.fire('Erro!', ret.mensagem, 'error');
                    }
                }, 'json')
                .fail(function(jqXHR, textStatus, errorThrown) {
                    Swal.fire('Erro!', 'Erro ao excluir curso: ' + errorThrown, 'error');
                });
            }
        });
    });

});