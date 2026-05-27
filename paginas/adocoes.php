<?php
include("../includes/config.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$usuarioId = (int) $_SESSION['usuario'];

$stmt = $conn->prepare("SELECT s.*, p.nome AS pet_nome, p.imagem, p.tipo, p.raca, u.nome AS solicitante_nome, u.email AS solicitante_email
    FROM solicitacoes_adocao s
    INNER JOIN pets p ON p.id = s.pet_id
    INNER JOIN usuarios u ON u.id = s.solicitante_id
    WHERE s.doador_id = ?
    ORDER BY FIELD(s.status, 'pendente', 'aprovada', 'recusada', 'cancelada'), s.criado_em DESC");
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$recebidas = $stmt->get_result();

$stmt = $conn->prepare("SELECT s.*, p.nome AS pet_nome, p.imagem, p.tipo, p.raca, u.nome AS doador_nome, u.email AS doador_email
    FROM solicitacoes_adocao s
    INNER JOIN pets p ON p.id = s.pet_id
    INNER JOIN usuarios u ON u.id = s.doador_id
    WHERE s.solicitante_id = ?
    ORDER BY s.criado_em DESC");
$stmt->bind_param("i", $usuarioId);
$stmt->execute();
$minhas = $stmt->get_result();

function statusTexto($status) {
    $mapa = [
        "pendente" => "Pendente",
        "aprovada" => "Aprovada",
        "recusada" => "Recusada",
        "cancelada" => "Cancelada",
    ];

    return $mapa[$status] ?? ucfirst($status);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adoções - WebDog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/adocoes.css">
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

<main class="adoption-shell">
    <section class="adoption-hero">
        <span class="eyebrow">Central de adoções</span>
        <h1>Acompanhe pedidos recebidos e solicitações enviadas.</h1>
        <p>Quando alguém solicitar adoção de um pet seu, o pedido fica pendente até você aceitar ou recusar.</p>
    </section>

    <section class="adoption-section">
        <div class="section-heading">
            <h2>Pedidos recebidos</h2>
            <p>Solicitações feitas para pets cadastrados por você.</p>
        </div>

        <?php if ($recebidas->num_rows === 0): ?>
            <div class="empty-state">Você ainda não recebeu solicitações de adoção.</div>
        <?php else: ?>
            <div class="request-list">
                <?php while ($item = $recebidas->fetch_assoc()): ?>
                    <article class="request-card">
                        <img src="<?= e(app_url($item['imagem'])) ?>" alt="Foto de <?= e($item['pet_nome']) ?>">
                        <div class="request-body">
                            <span class="status-pill <?= e($item['status']) ?>"><?= e(statusTexto($item['status'])) ?></span>
                            <h3><?= e($item['pet_nome']) ?></h3>
                            <p><?= e($item['tipo']) ?> · <?= e($item['raca']) ?></p>
                            <p><strong>Solicitante:</strong> <?= e($item['solicitante_nome']) ?> · <?= e($item['solicitante_email']) ?></p>
                        </div>
                        <?php if ($item['status'] === 'pendente'): ?>
                            <div class="request-actions">
                                <form action="../acoes/responder_adocao.php" method="POST">
                                    <input type="hidden" name="solicitacao_id" value="<?= (int) $item['id'] ?>">
                                    <input type="hidden" name="acao" value="aceitar">
                                    <button class="btn" type="submit">Aceitar</button>
                                </form>
                                <form action="../acoes/responder_adocao.php" method="POST">
                                    <input type="hidden" name="solicitacao_id" value="<?= (int) $item['id'] ?>">
                                    <input type="hidden" name="acao" value="recusar">
                                    <button class="btn excluir" type="submit">Recusar</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="adoption-section">
        <div class="section-heading">
            <h2>Minhas solicitações</h2>
            <p>Pets que você solicitou para adoção.</p>
        </div>

        <?php if ($minhas->num_rows === 0): ?>
            <div class="empty-state">Você ainda não solicitou adoção de nenhum pet.</div>
        <?php else: ?>
            <div class="request-list">
                <?php while ($item = $minhas->fetch_assoc()): ?>
                    <article class="request-card">
                        <img src="<?= e(app_url($item['imagem'])) ?>" alt="Foto de <?= e($item['pet_nome']) ?>">
                        <div class="request-body">
                            <span class="status-pill <?= e($item['status']) ?>"><?= e(statusTexto($item['status'])) ?></span>
                            <h3><?= e($item['pet_nome']) ?></h3>
                            <p><?= e($item['tipo']) ?> · <?= e($item['raca']) ?></p>
                            <p><strong>Doador:</strong> <?= e($item['doador_nome']) ?> · <?= e($item['doador_email']) ?></p>
                        </div>
                        <a href="detalhes_pet.php?id=<?= (int) $item['pet_id'] ?>" class="btn secondary">Ver pet</a>
                    </article>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

</body>
</html>








