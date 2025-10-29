$(document).ready(function () {
    listarAulas();

    // Inicializar Sortable para arrastar e soltar
    const lista = document.getElementById('listaAulas');
    new Sortable(lista, {
        animation: 150,
        onEnd: function() {
            const ordem = Array.from(lista.children).map(item => item.dataset.id);
            $.post('acoes.php', {
                acao: 'reordenar',
                curso_id: CURSO_ID,
                ordem: JSON.stringify(ordem)
            }, function(ret) {
                if (ret.status !== 'ok') {
                    Swal.fire('Erro!', ret.mensagem, 'error');
                    listarAulas(); // Recarrega a ordem original
                }
            }, 'json')
            .fail(function() {
                Swal.fire('Erro!', 'Erro ao atualizar a ordem das aulas.', 'error');
                listarAulas();
            });
        }
    });

    // Controlar exibição dos campos baseado no tipo de arquivo
    $('#tipo_arquivo').change(function() {
        if ($(this).val() === 'texto') {
            $('#campoArquivo').hide();
            $('#campoConteudo').show();
            $('#arquivo').prop('required', false);
            $('#conteudo').prop('required', true);
        } else {
            $('#campoArquivo').show();
            $('#campoConteudo').hide();
            $('#arquivo').prop('required', true);
            $('#conteudo').prop('required', false);
        }
    });

    // Preview de arquivo
    $('#arquivo').change(function() {
        const file = this.files[0];
        const tipo = $('#tipo_arquivo').val();
        const previewDiv = $('#previewArquivo');
        
        if (!file) {
            previewDiv.empty();
            return;
        }

        if (tipo === 'video' && file.type === 'video/mp4') {
            previewDiv.html(`
                <video class="mt-2" style="max-width: 100%; max-height: 300px;" controls>
                    <source src="${URL.createObjectURL(file)}" type="video/mp4">
                    Seu navegador não suporta o elemento de vídeo.
                </video>
            `);
        } else if (tipo === 'pdf' && file.type === 'application/pdf') {
            previewDiv.html(`
                <div class="alert alert-info mt-2">
                    <i class="bi bi-file-pdf"></i> ${file.name} (${(file.size/1024/1024).toFixed(2)}MB)
                </div>
            `);
        }
    });

    // Limpar formulário ao abrir modal para nova aula
    $('.btn-primary[data-bs-toggle="modal"]').click(function() {
        $('#formAula')[0].reset();
        $('#id').val('');
        $('.modal-title').text('Nova Aula');
        $('#previewArquivo').empty();
        $('#tipo_arquivo').trigger('change');
    });

    // Listar aulas
    function listarAulas() {
        $.post('acoes.php', { 
            acao: 'listar',
            curso_id: CURSO_ID
        }, function (ret) {
            if (ret.status === 'ok') {
                let items = '';
                ret.dados.forEach(a => {
                    const icone = a.tipo_arquivo === 'video' ? 'bi-play-circle' :
                                a.tipo_arquivo === 'pdf' ? 'bi-file-pdf' : 'bi-file-text';
                    
                    items += `
                        <li class="list-group-item" data-id="${a.id}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi ${icone} me-2"></i>
                                    <span class="fw-bold">${a.titulo}</span>
                                </div>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary visualizar" data-id="${a.id}">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-warning editar" data-id="${a.id}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger excluir" data-id="${a.id}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </li>`;
                });
                $('#listaAulas').html(items || '<li class="list-group-item text-center">Nenhuma aula cadastrada</li>');
            }
        }, 'json')
        .fail(function(jqXHR, textStatus, errorThrown) {
            Swal.fire('Erro!', 'Erro ao carregar aulas: ' + errorThrown, 'error');
        });
    }

    // Salvar aula
    $('#formAula').submit(function (e) {
        e.preventDefault();

        const btn = $('#btnSalvar');
        const spinner = $('#spinnerSalvar');
        const texto = $('#textoSalvar');

        btn.prop('disabled', true);
        spinner.removeClass('d-none');
        texto.text('Salvando...');

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

                    $('#modalAula').modal('hide');
                    $('#formAula')[0].reset();
                    $('#previewArquivo').empty();
                    listarAulas();
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

    // Visualizar aula
    $(document).on('click', '.visualizar', function () {
        const id = $(this).data('id');
        $.post('acoes.php', { 
            acao: 'listar',
            curso_id: CURSO_ID
        }, function (ret) {
            const a = ret.dados.find(x => x.id == id);
            if (a) {
                let conteudo;
                
                if (a.tipo_arquivo === 'video' && a.caminho_arquivo) {
                    conteudo = `
                        <video class="w-100" controls>
                            <source src="../${a.caminho_arquivo}" type="video/mp4">
                            Seu navegador não suporta o elemento de vídeo.
                        </video>
                    `;
                } else if (a.tipo_arquivo === 'pdf' && a.caminho_arquivo) {
                    conteudo = `
                        <div class="ratio ratio-16x9">
                            <iframe src="../${a.caminho_arquivo}"></iframe>
                        </div>
                    `;
                } else if (a.tipo_arquivo === 'texto' && a.conteudo) {
                    conteudo = `
                        <div class="bg-light p-3 rounded">
                            ${a.conteudo.replace(/\n/g, '<br>')}
                        </div>
                    `;
                } else {
                    conteudo = '<div class="alert alert-warning">Conteúdo não disponível</div>';
                }

                Swal.fire({
                    title: a.titulo,
                    html: conteudo,
                    width: '80%',
                    showCloseButton: true,
                    showConfirmButton: false
                });
            }
        }, 'json');
    });

    // Editar aula
    $(document).on('click', '.editar', function () {
        const id = $(this).data('id');
        $.post('acoes.php', { 
            acao: 'listar',
            curso_id: CURSO_ID
        }, function (ret) {
            const a = ret.dados.find(x => x.id == id);
            if (a) {
                $('#id').val(a.id);
                $('#titulo').val(a.titulo);
                $('#tipo_arquivo').val(a.tipo_arquivo).trigger('change');
                
                if (a.tipo_arquivo === 'texto') {
                    $('#conteudo').val(a.conteudo);
                } else if (a.caminho_arquivo) {
                    $('#previewArquivo').html(`
                        <div class="alert alert-info mt-2">
                            <i class="bi bi-file"></i> Arquivo atual: ${a.caminho_arquivo}
                        </div>
                    `);
                }

                $('.modal-title').text('Editar Aula');
                $('#modalAula').modal('show');
            }
        }, 'json')
        .fail(function(jqXHR, textStatus, errorThrown) {
            Swal.fire('Erro!', 'Erro ao carregar dados da aula: ' + errorThrown, 'error');
        });
    });

    // Excluir aula
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
                $.post('acoes.php', {
                    acao: 'excluir',
                    id: id,
                    curso_id: CURSO_ID
                }, function (ret) {
                    if (ret.status === 'ok') {
                        Swal.fire('Feito!', ret.mensagem, 'success');
                        listarAulas();
                    } else {
                        Swal.fire('Erro!', ret.mensagem, 'error');
                    }
                }, 'json')
                .fail(function(jqXHR, textStatus, errorThrown) {
                    Swal.fire('Erro!', 'Erro ao excluir aula: ' + errorThrown, 'error');
                });
            }
        });
    });
});