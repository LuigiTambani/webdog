<?php
include("../includes/config.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$usuarioId = (int) $_SESSION['usuario'];
$busca = trim($_GET["busca"] ?? "");
$tipo = trim($_GET["tipo"] ?? "");
$raca = trim($_GET["raca"] ?? "");
$idade = trim($_GET["idade"] ?? "");
$ordem = $_GET["ordem"] ?? "recentes";

$racas = [];
$racasResult = $conn->query("SELECT DISTINCT raca FROM pets WHERE raca <> '' ORDER BY raca");
if ($racasResult) {
    while ($row = $racasResult->fetch_assoc()) {
        $racas[] = $row["raca"];
    }
}

$where = [];
$types = "i";
$params = [$usuarioId];

if ($busca !== "") {
    $where[] = "(p.nome LIKE ? OR p.tipo LIKE ? OR p.raca LIKE ? OR p.idade LIKE ? OR p.doador LIKE ? OR p.descricao LIKE ?)";
    $termo = "%" . $busca . "%";
    for ($i = 0; $i < 6; $i++) {
        $types .= "s";
        $params[] = $termo;
    }
}

if ($tipo !== "") {
    $where[] = "p.tipo = ?";
    $types .= "s";
    $params[] = $tipo;
}

if ($raca !== "") {
    $where[] = "p.raca = ?";
    $types .= "s";
    $params[] = $raca;
}

if ($idade !== "") {
    $where[] = "p.idade LIKE ?";
    $types .= "s";
    $params[] = "%" . $idade . "%";
}

$orderBy = $ordem === "nome" ? "p.nome ASC" : "p.id DESC";
$sql = "SELECT p.*,
        (SELECT s.status FROM solicitacoes_adocao s WHERE s.pet_id = p.id AND s.solicitante_id = ? ORDER BY s.id DESC LIMIT 1) AS minha_solicitacao,
        (SELECT COUNT(*) FROM solicitacoes_adocao s WHERE s.pet_id = p.id AND s.status = 'aprovada') AS adocao_aprovada
    FROM pets p";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY " . $orderBy;

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$totalPets = $result->num_rows;

function resumo($texto, $limite = 116) {
    if (function_exists("mb_strimwidth")) {
        return mb_strimwidth($texto, 0, $limite, "...", "UTF-8");
    }

    return strlen($texto) > $limite ? substr($texto, 0, $limite - 3) . "..." : $texto;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed de pets - WebDog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/listar.css">
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

<main class="feed-shell">
    <section class="feed-hero">
        <div>
            <span class="eyebrow">Adoção responsável</span>
            <h1>Pets disponíveis para encontrar um novo lar</h1>
            <p>Pesquise, compare e abra os detalhes antes de solicitar uma adoção.</p>
        </div>
        <div class="feed-summary">
            <strong><?= (int) $totalPets ?></strong>
            <span><?= $totalPets === 1 ? "pet encontrado" : "pets encontrados" ?></span>
        </div>
    </section>

    <form class="filter-panel" method="GET">
        <div class="field search-field">
            <label for="busca">Pesquisar</label>
            <input id="busca" type="search" name="busca" placeholder="Nome, tipo, raça, doador ou descrição" value="<?= e($busca) ?>">
        </div>

        <div class="field">
            <label for="tipo">Tipo</label>
            <select id="tipo" name="tipo">
                <option value="">Todos</option>
                <?php foreach (["Cachorro", "Gato", "Outro"] as $tipoOpcao): ?>
                    <option value="<?= e($tipoOpcao) ?>" <?= $tipo === $tipoOpcao ? "selected" : "" ?>><?= e($tipoOpcao) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field">
            <label for="raca">Raça</label>
            <select id="raca" name="raca">
                <option value="">Todas</option>
                <?php foreach ($racas as $opcao): ?>
                    <option value="<?= e($opcao) ?>" <?= $raca === $opcao ? "selected" : "" ?>><?= e($opcao) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="field compact-field">
            <label for="idade">Idade</label>
            <input id="idade" type="text" name="idade" placeholder="2 anos" value="<?= e($idade) ?>">
        </div>

        <div class="field compact-field">
            <label for="ordem">Ordenar</label>
            <select id="ordem" name="ordem">
                <option value="recentes" <?= $ordem === "recentes" ? "selected" : "" ?>>Recentes</option>
                <option value="nome" <?= $ordem === "nome" ? "selected" : "" ?>>Nome</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit">Aplicar</button>
            <a href="listar_pets.php" class="btn secondary">Limpar</a>
        </div>
    </form>

    <?php if ($totalPets === 0): ?>
        <div class="empty-state">Nenhum pet encontrado com esses filtros.</div>
    <?php else: ?>
        <section class="pet-grid" aria-label="Lista de pets disponíveis">
            <?php while ($pet = $result->fetch_assoc()): ?>
                <?php
                    $isOwner = (int) $pet['usuario_id'] === $usuarioId;
                    $adocaoAprovada = (int) $pet['adocao_aprovada'] > 0;
                    $minhaSolicitacao = $pet['minha_solicitacao'] ?? null;
                ?>
                <article class="pet-card">
                    <a class="pet-photo-link" href="detalhes_pet.php?id=<?= (int) $pet['id'] ?>">
                        <img src="<?= e(app_url($pet['imagem'])) ?>" class="pet-img" alt="Foto de <?= e($pet['nome']) ?>">
                    </a>
                    <div class="pet-content">
                        <div class="pet-topline">
                            <span class="tag"><?= e($pet['tipo'] ?? 'Pet') ?></span>
                            <span class="age"><?= e($pet['idade']) ?></span>
                        </div>
                        <h2><?= e($pet['nome']) ?></h2>
                        <p class="pet-meta"><?= e($pet['raca']) ?> · Doador: <?= e($pet['doador']) ?></p>
                        <?php if ($isOwner): ?>
                            <span class="status-pill owner">Seu cadastro</span>
                        <?php elseif ($adocaoAprovada): ?>
                            <span class="status-pill approved">Adoção aprovada</span>
                        <?php elseif ($minhaSolicitacao): ?>
                            <span class="status-pill <?= e($minhaSolicitacao) ?>">Solicitação <?= e($minhaSolicitacao) ?></span>
                        <?php endif; ?>
                        <p class="pet-description"><?= e(resumo($pet['descricao'])) ?></p>
                    </div>
                    <div class="pet-actions">
                        <a href="detalhes_pet.php?id=<?= (int) $pet['id'] ?>" class="btn">Ver detalhes</a>
                        <?php if ($isOwner): ?>
                            <a href="editar_pet.php?id=<?= (int) $pet['id'] ?>" class="icon-action" title="Editar">Editar</a>
                        <?php elseif (!$adocaoAprovada && !$minhaSolicitacao): ?>
                            <form action="../acoes/solicitar_adocao.php" method="POST" class="inline-form">
                                <input type="hidden" name="pet_id" value="<?= (int) $pet['id'] ?>">
                                <button type="submit" class="btn accent">Solicitar adoção</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
        </section>
    <?php endif; ?>
</main>

</body>
</html>








