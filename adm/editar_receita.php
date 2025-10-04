<?php
session_start();
require '../conexao.php';

// Apenas administradores
if (!isset($_SESSION['id_utilizador']) || $_SESSION['id_tipo'] != 1) {
    header("Location: ../login.php?erro=acesso");
    exit;
}

// -- ID da receita --
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ver_receitas.php?erro=id_invalido");
    exit;
}
$idreceita = (int)$_GET['id'];

$erro = null;
$sucesso = null;

// Carregar países
$paises = [];
$resPaises = $conn->query("SELECT idpais, Pais FROM pais ORDER BY Pais ASC");
if ($resPaises && $resPaises->num_rows > 0) {
    while ($p = $resPaises->fetch_assoc()) {
        $paises[] = $p;
    }
}

// Carregar categorias (idcategoria, nome_categoria)
$categorias = [];
$resCats = $conn->query("SELECT idcategoria, nome_categoria FROM categoria ORDER BY nome_categoria ASC");
if ($resCats && $resCats->num_rows > 0) {
    while ($c = $resCats->fetch_assoc()) {
        $categorias[] = $c;
    }
}

// Buscar receita
$stmt = $conn->prepare("
    SELECT idreceita, nome, descricao, ingredientes, preparacao, preco, imagens, pais_idpais, categoria_idcategoria
    FROM receita
    WHERE idreceita = ?
");
$stmt->bind_param("i", $idreceita);
$stmt->execute();
$receita = $stmt->get_result()->fetch_assoc();

if (!$receita) {
    header("Location: ver_receitas.php?erro=nao_encontrada");
    exit;
}

// Submissão de edição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome         = trim($_POST['nome'] ?? '');
    $descricao    = trim($_POST['descricao'] ?? '');
    $ingredientes = trim($_POST['ingredientes'] ?? '');
    $preparacao   = trim($_POST['preparacao'] ?? '');
    $precoStr     = trim($_POST['preco'] ?? '0');
    $pais_id      = (int)($_POST['pais_idpais'] ?? 0);
    $cat_id       = (int)($_POST['categoria_idcategoria'] ?? 0);

    // preço: PT -> US
    $precoLimpo = str_replace('.', '', $precoStr);
    $preco      = (float)str_replace(',', '.', $precoLimpo);

    if ($nome === '' || $pais_id <= 0 || $cat_id <= 0) {
        $erro = "Por favor, preencha o Nome e selecione País e Categoria.";
    } else {
        $nomeArquivoFinal = $receita['imagens']; // default mantém a atual

        // Se veio nova imagem
        if (!empty($_FILES['imagem']['name'])) {
            $uploadDir = dirname(__DIR__) . '/assets/images';
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
                    $novoNome = 'rec_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $destino  = $uploadDir . '/' . $novoNome;
                    if (move_uploaded_file($tmpName, $destino)) {
                        // apaga a antiga se existir
                        if (!empty($receita['imagens'])) {
                            $antigo = $uploadDir . '/' . $receita['imagens'];
                            if (is_file($antigo)) @unlink($antigo);
                        }
                        $nomeArquivoFinal = $novoNome;
                    } else {
                        $erro = "Falha ao mover a imagem enviada.";
                    }
                }
            } elseif ($err !== UPLOAD_ERR_NO_FILE) {
                $erro = "Erro no upload da imagem (código $err).";
            }
        }

        if (!$erro) {
            $sql = "
                UPDATE receita
                SET nome = ?, descricao = ?, ingredientes = ?, preparacao = ?, preco = ?, imagens = ?, pais_idpais = ?, categoria_idcategoria = ?
                WHERE idreceita = ?
            ";
            $stmtUp = $conn->prepare($sql);
            // tipos: s s s s d s i i i
            $stmtUp->bind_param(
                "ssssdsiii",
                $nome,
                $descricao,
                $ingredientes,
                $preparacao,
                $preco,
                $nomeArquivoFinal,
                $pais_id,
                $cat_id,
                $idreceita
            );

            if ($stmtUp->execute()) {
                header("Location: ver_receitas.php?msg=editado_sucesso");
                exit;
            } else {
                $erro = "Erro ao atualizar a receita.";
            }
        }
    }

    // Em caso de erro, manter dados no formulário
    $receita = array_merge($receita, [
        'nome' => $nome,
        'descricao' => $descricao,
        'ingredientes' => $ingredientes,
        'preparacao' => $preparacao,
        'preco' => $preco,
        'pais_idpais' => $pais_id,
        'categoria_idcategoria' => $cat_id,
        'imagens' => $nomeArquivoFinal ?? $receita['imagens']
    ]);
}
?>
<!doctype html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <title>Editar Receita</title>
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
        <h2><i class="bi bi-pencil-square"></i> Editar Receita #<?= htmlspecialchars($idreceita) ?></h2>
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
                <input type="text" name="nome" class="form-control" required
                       value="<?= htmlspecialchars($receita['nome']) ?>">
              </div>

              <div class="col-md-3">
                <label class="form-label">País *</label>
                <select name="pais_idpais" class="form-select" required>
                  <option value="">-- selecione --</option>
                  <?php foreach ($paises as $p): ?>
                    <option value="<?= $p['idpais'] ?>"
                      <?= ($receita['pais_idpais'] == $p['idpais']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($p['Pais']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label">Categoria *</label>
                <select name="categoria_idcategoria" class="form-select" required>
                  <option value="">-- selecione --</option>
                  <?php foreach ($categorias as $c): ?>
                    <option value="<?= $c['idcategoria'] ?>"
                      <?= ($receita['categoria_idcategoria'] == $c['idcategoria']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($c['nome_categoria']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="col-md-3">
                <label class="form-label">Preço (€)</label>
                <input type="text" name="preco" class="form-control"
                       value="<?= htmlspecialchars(number_format((float)$receita['preco'], 2, ',', '.')) ?>">
              </div>

              <div class="col-12">
                <label class="form-label">Descrição</label>
                <textarea name="descricao" rows="3" class="form-control"
                          placeholder="Breve descrição..."><?= htmlspecialchars($receita['descricao']) ?></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">Ingredientes</label>
                <textarea name="ingredientes" rows="5" class="form-control"
                          placeholder="Liste os ingredientes, um por linha..."><?= htmlspecialchars($receita['ingredientes']) ?></textarea>
              </div>

              <div class="col-12">
                <label class="form-label">Preparação</label>
                <textarea name="preparacao" rows="6" class="form-control"
                          placeholder="Explique o modo de preparo..."><?= htmlspecialchars($receita['preparacao']) ?></textarea>
              </div>

              <div class="col-md-6">
                <label class="form-label d-block">Imagem atual</label>
                <?php if (!empty($receita['imagens'])): ?>
                  <img src="../assets/images/<?= htmlspecialchars($receita['imagens']) ?>" alt="Imagem" style="width:140px; height:140px; object-fit:cover; border:1px solid #ddd;">
                <?php else: ?>
                  <img src="../assets/images/no-image.png" alt="Sem imagem" style="width:140px; height:140px; object-fit:cover; border:1px solid #ddd;">
                <?php endif; ?>
              </div>

              <div class="col-md-6">
                <label class="form-label">Trocar imagem (JPG/PNG/WEBP/GIF, máx. 5MB)</label>
                <input type="file" name="imagem" class="form-control" accept=".jpg,.jpeg,.png,.webp,.gif,image/*">
                <small class="text-muted">Se não enviar, mantém a imagem atual.</small>
              </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
              <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Guardar alterações</button>
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
