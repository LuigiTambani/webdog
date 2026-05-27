<?php
include("../includes/config.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - WebDog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/menu.css">
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

<main class="dashboard-shell">
    <section class="dashboard-hero">
        <div class="dashboard-copy">
<h1>Olá, <?= e($usuarioLogadoNome) ?>. O que vamos fazer hoje?</h1>
            <p>Acesse rapidamente o feed, cadastre um novo pet ou acompanhe as solicitações de adoção.</p>
        </div>
</section>

    <section class="menu-container" aria-label="Ações principais">
        <a class="menu-card feed-card" href="listar_pets.php">
            <span class="card-icon">01</span>
            <h2>Explorar feed</h2>
            <p>Pesquise por tipo, raça, idade e veja os detalhes completos de cada pet.</p>
        </a>

        <a class="menu-card donate-card" href="cadastrar_pet.php">
            <span class="card-icon">02</span>
            <h2>Cadastrar pet</h2>
            <p>Publique um pet com foto, descrição, doador e informações essenciais.</p>
        </a>

        <a class="menu-card adoption-card" href="adocoes.php">
            <span class="card-icon">03</span>
            <h2>Minhas adoções</h2>
            <p>Veja solicitações recebidas, aceite ou recuse pedidos pendentes.</p>
        </a>
    </section>
</main>

</body>
</html>




