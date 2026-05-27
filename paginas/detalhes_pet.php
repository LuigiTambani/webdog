<?php
include("../includes/config.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$usuarioId = (int) $_SESSION['usuario'];
$id = (int) ($_GET["id"] ?? 0);

$stmt = $conn->prepare("SELECT p.*,
        (SELECT s.status FROM solicitacoes_adocao s WHERE s.pet_id = p.id AND s.solicitante_id = ? ORDER BY s.id DESC LIMIT 1) AS minha_solicitacao,
        (SELECT COUNT(*) FROM solicitacoes_adocao s WHERE s.pet_id = p.id AND s.status = 'aprovada') AS adocao_aprovada
    FROM pets p WHERE p.id = ?");
$stmt->bind_param("ii", $usuarioId, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: listar_pets.php");
    exit;
}

$pet = $result->fetch_assoc();
$isOwner = (int) $pet["usuario_id"] === $usuarioId;
$adocaoAprovada = (int) $pet["adocao_aprovada"] > 0;
$minhaSolicitacao = $pet["minha_solicitacao"] ?? null;
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pet["nome"]) ?> - WebDog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/detalhes.css">
</head>
<body>

<header class="navbar">
    <div class="logo"><img src="../assets/img/logo.jpg" class="logo-img" alt="Logo WebDog"><span>WebDog</span></div>
    <nav>
        <span class="user-name">Olá, <?= e($usuarioLogadoNome) ?></span>
        <a href="listar_pets.php">Feed</a>
        <a href="cadastrar_pet.php">Cadastrar pet</a>
        <a href="adocoes.php">Minhas adoções</a>
        <a href="../acoes/logout.php">Sair</a>
    </nav>
</header>

<main class="details-shell">
    <a href="listar_pets.php" class="back-link">Voltar ao feed</a>

    <section class="details-panel details-grid">
        <div class="details-media">
            <img class="details-image" src="<?= e(app_url($pet["imagem"])) ?>" alt="Foto de <?= e($pet["nome"]) ?>">
        </div>

        <div class="details-content">
            <div class="details-kicker">
                <span class="tag"><?= e($pet["tipo"] ?? "Pet") ?></span>
                <span class="age"><?= e($pet["idade"]) ?></span>
                <?php if ($isOwner): ?>
                    <span class="status-pill owner">Seu cadastro</span>
                <?php elseif ($adocaoAprovada): ?>
                    <span class="status-pill approved">Adoção aprovada</span>
                <?php elseif ($minhaSolicitacao): ?>
                    <span class="status-pill <?= e($minhaSolicitacao) ?>">Solicitação <?= e($minhaSolicitacao) ?></span>
                <?php endif; ?>
            </div>
            <h1><?= e($pet["nome"]) ?></h1>
            <p class="pet-meta"><?= e($pet["raca"]) ?></p>

            <div class="details-list">
                <div>
                    <strong>Doador</strong>
                    <span><?= e($pet["doador"]) ?></span>
                </div>
                <div>
                    <strong>Cadastrado em</strong>
                    <span><?= isset($pet["criado_em"]) ? e(date("d/m/Y", strtotime($pet["criado_em"]))) : "Não informado" ?></span>
                </div>
            </div>

            <div class="description-box">
                <h2>Sobre este pet</h2>
                <p><?= nl2br(e($pet["descricao"])) ?></p>
            </div>

            <div class="details-actions">
                <?php if ($isOwner): ?>
                    <a href="editar_pet.php?id=<?= (int) $pet["id"] ?>" class="btn">Editar cadastro</a>
                    <a href="../acoes/excluir_pet.php?id=<?= (int) $pet["id"] ?>" class="btn excluir" onclick="return confirm('Tem certeza que deseja excluir este pet?')">Excluir pet</a>
                <?php elseif ($adocaoAprovada): ?>
                    <span class="status-message">Este pet já teve uma adoção aprovada.</span>
                <?php elseif ($minhaSolicitacao === "pendente"): ?>
                    <span class="status-message">Sua solicitação está pendente. Aguarde o doador responder.</span>
                <?php elseif ($minhaSolicitacao === "aprovada"): ?>
                    <span class="status-message">Sua adoção foi aprovada pelo doador.</span>
                <?php else: ?>
                    <form action="../acoes/solicitar_adocao.php" method="POST" class="inline-form">
                        <input type="hidden" name="pet_id" value="<?= (int) $pet["id"] ?>">
                        <button type="submit" class="btn accent">Solicitar adoção</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

</body>
</html>








