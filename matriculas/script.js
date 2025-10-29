$(document).ready(function () {
    // Loading helpers
    const loading = {
        show: () => $('.loading-overlay').css('display', 'flex'),
        hide: () => $('.loading-overlay').hide()
    };

    carregarFiltros();
    listarMatriculas();
    carregarProgressoAgregado();

    function carregarProgressoAgregado(curso_id) {
        $.post('acoes.php', { acao: 'progresso_agregado', curso_id: curso_id }, function(ret) {
            if (ret.status === 'ok') {
                let cards = '';
                ret.dados.forEach(curso => {
                    cards += `
                        <div class="col-md-6 col-lg-4">
                            <div class="card estatistica-card h-100">
                                <div class="card-body">
                                    <h6 class="card-title">${curso.curso}</h6>
                                    <div class="progress mb-2">
                                        <div class="progress-bar" role="progressbar" style="width: ${curso.media_progresso}%" 
                                             aria-valuenow="${curso.media_progresso}" aria-valuemin="0" aria-valuemax="100">
                                            ${curso.media_progresso}%
                                        </div>
                                    </div>
                                    <div class="curso-stats">
                                        <span><i class="bi bi-people-fill text-primary"></i>${curso.total_matriculas} alunos</span>
                                        <span><i class="bi bi-check-circle-fill text-success"></i>${curso.concluidos} concluídos</span>
                                        <span><i class="bi bi-x-circle-fill text-danger"></i>${curso.cancelados} cancelados</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('#estatisticasCursos').html(cards || '<div class="col-12 text-center text-muted">Nenhum dado disponível</div>');
            }
        }, 'json');
    }
    
    // Exportar CSV
    $('#exportarCSV').click(function() {
        const curso_id = $('#filtroCurso').val();
        const form = $('<form>')
            .attr('method', 'POST')
            .attr('action', 'acoes.php')
            .append($('<input>').attr('type', 'hidden').attr('name', 'acao').val('exportar_csv'))
            .append($('<input>').attr('type', 'hidden').attr('name', 'curso_id').val(curso_id));
        $('body').append(form);
        form.submit();
        form.remove();
    });

    // Carregar usuários e cursos para modal
    function carregarFiltros() {
        $.post('acoes.php', { acao: 'listar_cursos' }, function(ret) {
            if (ret.status === 'ok') {
                let opts = '<option value="">Filtrar por curso (todos)</option>';
                ret.dados.forEach(c => opts += `<option value="${c.id}">${c.titulo}</option>`);
                $('#filtroCurso').html(opts);

                // também popular select do modal
                let opts2 = '<option value="">Selecione...</option>';
                ret.dados.forEach(c => opts2 += `<option value="${c.id}">${c.titulo}</option>`);
                $('#curso_id').html(opts2);
            }
        }, 'json');

        $.post('acoes.php', { acao: 'listar_usuarios' }, function(ret) {
            if (ret.status === 'ok') {
                let opts = '<option value="">Selecione...</option>';
                ret.dados.forEach(u => opts += `<option value="${u.id}">${u.nome} (${u.email})</option>`);
                $('#usuario_id').html(opts);
            }
        }, 'json');
    }

    $('#filtroCurso').change(function(){
        const curso_id = $(this).val();
        listarMatriculas(curso_id);
        carregarProgressoAgregado(curso_id);
    });

    // Abrir curso
    $(document).on('click', '.ver-curso', function() {
        const curso_id = $(this).data('curso');
        window.location.href = `/courses/cursos/?id=${curso_id}`;
    });

    function listarMatriculas(curso_id) {
        loading.show();
        $('#tabelaMatriculas tbody').html('<tr><td colspan="7" class="text-center">Carregando...</td></tr>');
        $.post('acoes.php', { acao: 'listar', curso_id: curso_id }, function(ret) {
            if (ret.status === 'ok') {
                let rows = '';
                ret.dados.forEach(m => {
                    rows += `
                        <tr>
                            <td>${m.id}</td>
                            <td>${m.usuario_nome}</td>
                            <td>${m.curso_titulo}</td>
                            <td>${m.data_matricula}</td>
                            <td style="width:220px;">
                                <div class="d-flex align-items-center">
                                    <input type="range" min="0" max="100" value="${m.progresso}" class="form-range me-2 progresso-range" data-id="${m.id}" style="flex:1">
                                    <span class="badge bg-primary progresso-label" data-id="${m.id}">${(m.progresso||0)}%</span>
                                </div>
                            </td>
                            <td>${m.status}</td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-info ver-curso" data-curso="${m.curso_id}" title="Ver Curso"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-sm btn-success salvar-progresso" data-id="${m.id}" title="Salvar Progresso"><i class="bi bi-check2"></i></button>
                                    <button class="btn btn-sm btn-warning mudar-status" data-id="${m.id}" title="Mudar Status"><i class="bi bi-arrow-repeat"></i></button>
                                    <button class="btn btn-sm btn-danger excluir" data-id="${m.id}" title="Excluir"><i class="bi bi-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                $('#tabelaMatriculas tbody').html(rows || '<tr><td colspan="7" class="text-center">Nenhuma matrícula</td></tr>');
            } else {
                Swal.fire('Erro', ret.mensagem, 'error');
            }
        }, 'json').fail(function(){
            Swal.fire('Erro', 'Falha ao carregar matrículas', 'error');
            $('#tabelaMatriculas tbody').html('<tr><td colspan="7" class="text-center text-danger">Erro ao carregar dados</td></tr>');
        }).always(function(){
            loading.hide();
        });
    }

    // Matricular
    $('#formMatricula').submit(function(e){
        e.preventDefault();
        const $form = $(this);
        const $submitBtn = $form.find('button[type="submit"]');
        const originalText = $submitBtn.html();
        
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Processando...');
        const dados = $form.serialize() + '&acao=matricular';
        
        $.post('acoes.php', dados, function(ret){
            if (ret.status === 'ok') {
                Swal.fire('Sucesso', ret.mensagem, 'success');
                $('#modalMatricula').modal('hide');
                listarMatriculas($('#filtroCurso').val());
                $('#formMatricula')[0].reset();
            } else {
                Swal.fire('Erro', ret.mensagem, 'error');
            }
        }, 'json').fail(function(){
            Swal.fire('Erro', 'Falha ao matricular', 'error');
        }).always(function(){
            $submitBtn.prop('disabled', false).html(originalText);
        });
    });

    // Atualizar label quando move range
    $(document).on('input', '.progresso-range', function(){
        const id = $(this).data('id');
        $('.progresso-label[data-id="'+id+'"]').text($(this).val() + '%');
    });

    // Salvar progresso
    $(document).on('click', '.salvar-progresso', function(){
        const $btn = $(this);
        const id = $btn.data('id');
        const val = $('.progresso-range[data-id="'+id+'"]').val();
        
        $btn.addClass('saving');
        $.post('acoes.php', { acao: 'atualizar_progresso', id: id, progresso: val }, function(ret){
            if (ret.status === 'ok') {
                // Show inline confirmation
                $btn.removeClass('saving').addClass('btn-outline-success').html('<i class="bi bi-check2"></i>');
                setTimeout(() => {
                    $btn.removeClass('btn-outline-success').addClass('btn-success').html('<i class="bi bi-check2"></i>');
                }, 2000);
                listarMatriculas($('#filtroCurso').val());
            } else {
                Swal.fire('Erro', ret.mensagem, 'error');
            }
        }, 'json').fail(function(){
            Swal.fire('Erro', 'Falha ao atualizar progresso', 'error');
        });
    });

    // Mudar status
    $(document).on('click', '.mudar-status', function(){
        const id = $(this).data('id');
        Swal.fire({
            title: 'Mudar status',
            input: 'select',
            inputOptions: { 'ativa':'Ativa','concluida':'Concluída','cancelada':'Cancelada' },
            inputPlaceholder: 'Selecione o status',
            showCancelButton: true
        }).then((res)=>{
            if (res.isConfirmed) {
                $.post('acoes.php', { acao: 'mudar_status', id: id, status: res.value }, function(ret){
                    if (ret.status === 'ok') {
                        Swal.fire('Sucesso', ret.mensagem, 'success');
                        listarMatriculas($('#filtroCurso').val());
                    } else {
                        Swal.fire('Erro', ret.mensagem, 'error');
                    }
                }, 'json');
            }
        });
    });

    // Excluir
    $(document).on('click', '.excluir', function(){
        const id = $(this).data('id');
        Swal.fire({
            title: 'Confirma exclusão?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sim, excluir'
        }).then((result)=>{
            if (result.isConfirmed) {
                $.post('acoes.php', { acao: 'excluir', id: id }, function(ret){
                    if (ret.status === 'ok') {
                        Swal.fire('Sucesso', ret.mensagem, 'success');
                        listarMatriculas($('#filtroCurso').val());
                    } else {
                        Swal.fire('Erro', ret.mensagem, 'error');
                    }
                }, 'json').fail(function(){
                    Swal.fire('Erro', 'Falha ao excluir matrícula', 'error');
                });
            }
        });
    });

});