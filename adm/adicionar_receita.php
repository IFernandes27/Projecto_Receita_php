<?php
session_start();
require '../conexao.php';

// Bloqueia acesso de não administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] != 1) {
    header("Location: ../login.php?erro=acesso");
    exit;
}

/* =========================
   Carregar países (idpais, Pais)
   ========================= */
$paises = [];
$resPaises = $conn->query("SELECT idpais, Pais FROM pais ORDER BY Pais ASC");
if ($resPaises && $resPaises->num_rows > 0) {
    while ($p = $resPaises->fetch_assoc()) {
        $paises[] = $p;
    }
}

/* =========================
   Carregar categorias (idcategoria, nome_categoria)
   ========================= */
$categorias = [];
$resCats = $conn->query("SELECT idcategoria, nome_categoria FROM categoria ORDER BY nome_categoria ASC");
if ($resCats && $resCats->num_rows > 0) {
    while ($c = $resCats->fetch_assoc()) {
        $categorias[] = $c; // ['idcategoria' => ..., 'nome_categoria' => ...]
    }
}

$erro = null;

// Submissão do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome         = trim($_POST['nome'] ?? '');
    $descricao    = trim($_POST['descricao'] ?? '');
    $ingredientes = trim($_POST['ingredientes'] ?? '');
    $preparacao   = trim($_POST['preparacao'] ?? '');
    $precoStr     = trim($_POST['preco'] ?? '0');
    $pais_id      = (int)($_POST['pais_idpais'] ?? 0);
    $cat_id       = (int)($_POST['categoria_idcategoria'] ?? 0);

    // Converte preço no formato PT (1.234,56 -> 1234.56)
    $precoLimpo = str_replace('.', '', $precoStr);
    $preco      = (float)str_replace(',', '.', $precoLimpo);

    // Validações básicas
    if ($nome === '' || $pais_id <= 0 || $cat_id <= 0) {
        $erro = "Por favor, preencha o Nome e selecione País e Categoria.";
    } else {
        // Upload da imagem (opcional)
        $nomeArquivoFinal = ''; // se vazio, a listagem mostrará imagem padrão

        if (!empty($_FILES['imagem']['name'])) {
            $uploadDir = dirname(__DIR__) . '/assets/images'; // ../assets/images
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0775, true);
            }

            $tmpName = $_FILES['imagem']['tmp_name'];
            $orig    = $_FILES['imagem']['name'];
            $size    = (int)$_FILES['imagem']['size'];
            $err     = (int)$_FILES['imagem']['error'];

            if ($err === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
                $permitidas = ['jpg','jpeg','png','gif','webp'];

                if (!in_array($ext, $permitidas, true)) {
                    $erro = "Formato de imagem inválido. Use JPG, PNG, WEBP ou GIF.";
                } elseif ($size > 5 * 1024 * 1024) {
                    $erro = "Imagem muito grande (máx. 5 MB).";
                } else {
                    $nomeUnico = 'rec_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $destino   = $uploadDir . '/' . $nomeUnico;

                    if (move_uploaded_file($tmpName, $destino)) {
                        $nomeArquivoFinal = $nomeUnico;
                    } else {
                        $erro = "Falha ao mover a imagem enviada.";
                    }
                }
            } elseif ($err !== UPLOAD_ERR_NO_FILE) {
                $erro = "Erro no upload da imagem (código $err).";
            }
        }

        // Inserir se estiver tudo certo
        if (!$erro) {
            $sql = "INSERT INTO receita 
                        (nome, descricao, ingredientes, preparacao, preco, imagens, pais_idpais, categoria_idcategoria)
                    VALUES (?,    ?,         ?,            ?,          ?,     ?,       ?,            ?)";
            $stmt = $conn->prepare($sql);
            // tipos: s s s s d s i i
            $stmt->bind_param(
                "ssssdsii",
                $nome,
                $descricao,
                $ingredientes,
                $preparacao,
                $preco,
                $nomeArquivoFinal,
                $pais_id,
                $cat_id
            );

            if ($stmt->execute()) {
                header("Location: ver_receitas.php?msg=adicionado_sucesso");
                exit;
            } else {
                $erro = "Erro ao gravar a receita na base de dados.";
            }
        }
    }
}
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <title>Adicionar Receita</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="./css/adminlte.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
  <?php include 'slide_bar.php'; ?>

  <!-- Main -->
  <main class="app-main">
    <div class="container my-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-plus-circle"></i> Adicionar Receita</h2>
        <div>
          <a href="ver_receitas.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Voltar</a>
        </div>
      </div>

      <?php if ($erro): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
      <?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Nome da Receita *</label>
                <input type="text" name="nome" class="form-control" required>
              </div>

              <div class="col-md-3">
                <label class="form-label">País *</label>
                <select name="pais_idpais" class="form-select" required>
                  <option value="">-- selecione --</option>
                  <?php foreach ($paises as $p): ?>
                    <option value="<?= $p['idpais'] ?>"><?= htmlspecialchars($p['Pais']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label">Categoria *</label>
                <select name="categoria_idcategoria" class="form-select" required>
                  <option value="">-- selecione --</option>
                  <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['idcategoria'] ?>"><?= htmlspecialchars($c['nome_categoria']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label">Preço (€)</label>
                <input type="text" name="preco" class="form-control" placeholder="ex: 12,50">
              </div>

              <div class="col-12">
                <label class="form-label">Descrição</label>
                <textarea name="descricao" rows="3" class="form-control" placeholder="Breve descrição da receita..."></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">Ingredientes</label>
                <textarea name="ingredientes" rows="5" class="form-control" placeholder="Liste os ingredientes, um por linha..."></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">Preparação</label>
                <textarea name="preparacao" rows="6" class="form-control" placeholder="Explique o modo de preparo passo a passo..."></textarea>
              </div>

              <div class="col-md-6">
                <label class="form-label">Imagem (JPG/PNG/WEBP/GIF, máx. 5MB)</label>
                <input type="file" name="imagem" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif,image/*">
                <small class="text-muted">Se não enviar, será usada imagem padrão na listagem.</small>
              </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
              <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Guardar</button>
            </div>
          </form>
        </div>
      </div>

    </div>
  </main>

  <footer class="app-footer">
    <div class="float-end d-none d-sm-inline">Área Administrativa</div>
  </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
