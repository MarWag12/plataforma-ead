<?php
require 'config/conexao.php';
session_start();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
}

// Busca curso com instrutor
$stmt = $pdo->prepare("SELECT c.*, u.nome as instrutor_nome, u.id as instrutor_id FROM cursos c LEFT JOIN usuarios u ON c.instrutor_id = u.id WHERE c.id = ? LIMIT 1");
$stmt->execute([$id]);
$curso = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $curso ? htmlspecialchars($curso['titulo']) . ' — Curso' : 'Curso não encontrado'; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>
<body class="bg-light">
<div class="container py-4">
  <a href="cursos.php" class="btn btn-link mb-3">&larr; Voltar</a>

  <?php if (!$curso): ?>
    <div class="alert alert-warning">Curso não encontrado.</div>
  <?php else: ?>

    <style>
      /* Estilos inspirados no layout da Rocketseat (simples, limpo e com destaque ao hero) */
      .curso-hero{
        border-radius: .5rem;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 2rem;
        color: #fff;
        background: linear-gradient(135deg, #7b61ff 0%, #3ab0ff 100%);
      }
      .curso-hero .info{max-width:66%;}
      .curso-hero h1{font-size:1.75rem;margin-bottom:.25rem}
      .curso-hero .meta{opacity:.9}
      .curso-aside{position:sticky;top:20px}
      .aula-item{display:flex;align-items:center;justify-content:space-between;padding:.75rem 1rem}
      .aula-item .left{display:flex;gap:.75rem;align-items:center}
      .tag{background:rgba(255,255,255,0.12);padding:.25rem .5rem;border-radius:.375rem;font-size:.85rem}
    </style>

    <!-- Hero -->
    <div class="curso-hero mb-4">
      <div class="info">
        <h1><?php echo htmlspecialchars($curso['titulo']); ?></h1>
        <div class="meta mb-2">Por <?php echo htmlspecialchars($curso['instrutor_nome'] ?? '-'); ?> • <span class="tag"><?php echo htmlspecialchars($curso['categoria'] ?? 'Sem categoria'); ?></span></div>
        <p class="mb-0 text-white-50"><?php echo nl2br(htmlspecialchars($curso['descricao_curta'] ?? '')); ?></p>
      </div>
      <?php if (!empty($curso['imagem_capa'])): ?>
        <div class="img-wrap" style="width:220px;flex-shrink:0">
          <img src="<?php echo htmlspecialchars($curso['imagem_capa']); ?>" alt="<?php echo htmlspecialchars($curso['titulo']); ?>" class="img-fluid rounded">
        </div>
      <?php endif; ?>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="card mb-3">
          <div class="card-body">
            <h5>Sobre este curso</h5>
            <div class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($curso['descricao_longa'] ?? $curso['descricao_curta'] ?? 'Sem descrição.')); ?></div>

            <h5 class="mt-4">Currículo</h5>
            <?php
            $stmt = $pdo->prepare("SELECT * FROM aulas WHERE curso_id = ? ORDER BY posicao, id");
            $stmt->execute([$id]);
            $aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (!$aulas):
            ?>
              <div class="alert alert-secondary">Ainda não há aulas para este curso.</div>
            <?php else: ?>
              <div class="list-group">
                <?php foreach ($aulas as $a): ?>
                  <div class="list-group-item aula-item">
                    <div class="left">
                      <span class="badge bg-primary me-2"><?php echo $a['posicao'] ? intval($a['posicao']) : '-'; ?></span>
                      <div>
                        <div><strong><?php echo htmlspecialchars($a['titulo']); ?></strong></div>
                        <div class="text-muted small"><?php echo htmlspecialchars($a['tipo_arquivo']); ?></div>
                      </div>
                    </div>
                    <div>
                      <?php if ($a['tipo_arquivo'] === 'video' && !empty($a['caminho_arquivo'])): ?>
                        <a class="btn btn-sm btn-outline-primary" href="<?php echo htmlspecialchars($a['caminho_arquivo']); ?>" target="_blank"><i class="bi bi-play-fill"></i></a>
                      <?php elseif ($a['tipo_arquivo'] === 'pdf' && !empty($a['caminho_arquivo'])): ?>
                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo htmlspecialchars($a['caminho_arquivo']); ?>" target="_blank"><i class="bi bi-file-earmark-pdf"></i></a>
                      <?php elseif ($a['tipo_arquivo'] === 'link' && !empty($a['link'])): ?>
                        <a class="btn btn-sm btn-outline-info" href="<?php echo htmlspecialchars($a['link']); ?>" target="_blank"><i class="bi bi-link-45deg"></i></a>
                      <?php else: ?>
                        <span class="text-muted small">Sem arquivo</span>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="curso-aside">
          <div class="card mb-3">
            <div class="card-body text-center">
              <div class="mb-3">
                <div class="h4 mb-0"><?php echo (floatval($curso['preco']) > 0) ? 'R$ ' . number_format($curso['preco'], 2, ',', '.') : 'Gratuito'; ?></div>
                <div class="text-muted small">Valor do curso</div>
              </div>

              <?php
              $usuarioMatriculado = false;
              if (!empty($_SESSION['user']['id'])) {
                  $stmt = $pdo->prepare("SELECT COUNT(*) FROM matriculas WHERE usuario_id = ? AND curso_id = ?");
                  $stmt->execute([$_SESSION['user']['id'], $id]);
                  $usuarioMatriculado = $stmt->fetchColumn() > 0;
              }
              ?>

              <?php if ($usuarioMatriculado): ?>
                <a href="matriculas/index.php?curso_id=<?php echo $id; ?>" class="btn btn-success btn-lg w-100">Acessar curso</a>
              <?php else: ?>
                <a href="matriculas/index.php?curso_id=<?php echo $id; ?>" class="btn btn-primary btn-lg w-100">Matricular-se</a>
              <?php endif; ?>

              <div class="mt-3 small text-muted">Instrutor: <?php echo htmlspecialchars($curso['instrutor_nome'] ?? '-'); ?></div>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <h6 class="mb-2">Detalhes</h6>
              <ul class="list-unstyled small mb-0">
                <li>Categoria: <?php echo htmlspecialchars($curso['categoria'] ?? '-'); ?></li>
                <li>Status: <span class="badge bg-<?php echo ($curso['status'] === 'publicado') ? 'success' : 'secondary'; ?>"><?php echo htmlspecialchars($curso['status']); ?></span></li>
                <li>Instrutor: <?php echo htmlspecialchars($curso['instrutor_nome'] ?? '-'); ?></li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

  <?php endif; ?>

</div>
</body>
</html>
