<?php include 'config/conexao.php'; session_start(); ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Cursos Disponíveis</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="p-4">
<div class="container">
  <div class="d-flex align-items-center mb-4">
    <h2 class="me-auto">Cursos Disponíveis</h2>
    <?php if (!empty($_SESSION['user'])): ?>
      <div>Olá, <?php echo htmlspecialchars($_SESSION['user']['nome']); ?> — <a href="helpers/login.php?action=logout" class="btn btn-sm btn-outline-secondary">Sair</a></div>
    <?php else: ?>
      <a href="login.php" class="btn btn-primary me-2">Entrar</a>
      <a href="register.php" class="btn btn-outline-primary">Cadastrar</a>
    <?php endif; ?>
  </div>

  <div class="row mb-3">
    <div class="col-md-6">
      <input id="busca" class="form-control" placeholder="Buscar por título, instrutor ou categoria">
    </div>
    <div class="col-md-6 text-end">
      <select id="filtroCategoria" class="form-select w-auto d-inline-block">
        <option value="">Todas as categorias</option>
      </select>
    </div>
  </div>

  <div id="listaCursos" class="row g-4">
    <!-- cursos preenchidos via JS -->
  </div>

  <div class="mt-4">
    <nav aria-label="Paginação">
      <ul class="pagination justify-content-center" id="paginacao"></ul>
    </nav>
  </div>

  <style>
  .curso-card {
    height: 100%;
    transition: transform 0.2s;
  }
  .curso-card:hover {
    transform: translateY(-5px);
  }
  .curso-card .card-img-wrapper {
    height: 200px;
    background: #f8f9fa;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .curso-card .card-img-top {
    object-fit: cover;
    width: 100%;
    height: 100%;
  }
  .curso-card .card-img-placeholder {
    color: #dee2e6;
    font-size: 3rem;
  }
  .curso-card .card-body {
    display: flex;
    flex-direction: column;
  }
  .curso-card .card-text {
    flex: 1;
  }
  .curso-stats {
    font-size: 0.875rem;
    color: #6c757d;
  }
  .curso-stats i {
    margin-right: 0.25rem;
  }
  .curso-preco {
    font-size: 1.25rem;
    font-weight: 600;
    color: #198754;
  }
  </style>
</div>

<script>
function carregarCategorias(){
  $.post('/courses/cursos/acoes.php', {acao:'listar_categorias'}, function(ret){
    if(ret.status==='ok'){
      let opts = '<option value="">Todas as categorias</option>';
      ret.dados.forEach(c => {
        if (c.categoria) { // só adiciona se tiver categoria
          opts += `<option value="${c.categoria}">${c.categoria}</option>`;
        }
      });
      $('#filtroCategoria').html(opts);
    } else {
      console.error('Erro ao carregar categorias:', ret.mensagem);
      $('#filtroCategoria').html('<option value="">Erro ao carregar categorias</option>');
    }
  },'json').fail(function(jqXHR, textStatus, errorThrown) {
    console.error('Falha na requisição de categorias:', textStatus, errorThrown);
    $('#filtroCategoria').html('<option value="">Erro ao carregar categorias</option>');
  });
}

let paginaAtual = 1;

function listarCursos(q, categoria, pagina = 1){
  paginaAtual = pagina;
  $('#listaCursos').html('<div class="col-12 text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Carregando...</span></div></div>');
  
  $.post('cursos/acoes.php', {
    acao: 'listar_publico', 
    q: q, 
    categoria: categoria,
    page: pagina
  }, function(ret){
    console.log('Resposta do servidor:', ret); // Debug
    if(ret.status==='ok'){
      let html = '';
      ret.dados.forEach(c=>{
        html += `
          <div class="col-md-6 col-lg-4">
            <div class="card curso-card">
              <div class="card-img-wrapper">
                ${c.imagem_capa 
                  ? `<img src="${c.imagem_capa}" class="card-img-top" alt="${c.titulo}">`
                  : `<i class="bi bi-image card-img-placeholder"></i>`
                }
              </div>
              <div class="card-body">
                <h5 class="card-title">${c.titulo}</h5>
                <p class="card-text text-muted">${c.descricao_curta || 'Sem descrição'}</p>
                <div class="curso-stats mb-2">
                  <span><i class="bi bi-person"></i>${c.instrutor_nome}</span>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                  <span class="curso-preco">
                    ${parseFloat(c.preco||0) > 0 
                      ? `R$ ${parseFloat(c.preco).toFixed(2)}` 
                      : 'Gratuito'
                    }
                  </span>
                  <div>
                    <a href="course-single.php?id=${c.id}" class="btn btn-sm btn-outline-primary me-2">
                      <i class="bi bi-eye"></i> Ver
                    </a>
                    <a href="matriculas/index.php?curso_id=${c.id}" class="btn btn-sm btn-primary">
                      <i class="bi bi-plus-lg"></i> Matricular
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;
      });

      // Atualiza listagem
      $('#listaCursos').html(html || '<div class="col-12 text-center text-muted">Nenhum curso encontrado</div>');
      
      // Atualiza paginação
      if(ret.paginas > 1) {
        let paginacao = '';
        // Anterior
        paginacao += `
          <li class="page-item ${pagina <= 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${pagina-1}">Anterior</a>
          </li>
        `;
        
        // Páginas
        for(let i = 1; i <= ret.paginas; i++) {
          if (
            i <= 2 || // primeiras 2 páginas
            i >= ret.paginas - 1 || // últimas 2 páginas
            Math.abs(i - pagina) <= 1 // páginas próximas à atual
          ) {
            paginacao += `
              <li class="page-item ${i === pagina ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
              </li>
            `;
          } else if (Math.abs(i - pagina) === 2) {
            paginacao += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
          }
        }
        
        // Próxima
        paginacao += `
          <li class="page-item ${pagina >= ret.paginas ? 'disabled' : ''}">
            <a class="page-link" href="#" data-page="${pagina+1}">Próxima</a>
          </li>
        `;
        
        $('#paginacao').html(paginacao);
      } else {
        $('#paginacao').empty();
      }
    } else {
      $('#listaCursos').html('<div class="col-12 text-danger">Erro ao carregar cursos</div>');
    }
  },'json').fail(function(){ 
    $('#listaCursos').html('<div class="col-12 text-danger">Erro na requisição</div>'); 
  });
}

$(function(){
  carregarCategorias();
  listarCursos();

  // Handlers de busca/filtro
  let timeoutBusca;
  $('#busca').on('input', function(){
    clearTimeout(timeoutBusca);
    timeoutBusca = setTimeout(() => {
      listarCursos($(this).val(), $('#filtroCategoria').val(), 1);
    }, 300);
  });
  
  $('#filtroCategoria').change(function(){
    listarCursos($('#busca').val(), $(this).val(), 1);
  });

  // Handler de paginação
  $(document).on('click', '.page-link', function(e){
    e.preventDefault();
    const pagina = $(this).data('page');
    if(pagina) {
      listarCursos($('#busca').val(), $('#filtroCategoria').val(), pagina);
      $('html, body').animate({ scrollTop: 0 }, 'fast');
    }
  });
});
</script>

</body>
</html>