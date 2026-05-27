<?php
include("../includes/config.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"] ?? "");
    $tipo = trim($_POST["tipo"] ?? "Cachorro");
    $idade = trim($_POST["idade"] ?? "");
    $raca = trim($_POST["raca"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $doador = trim($_POST["doador"] ?? "");
    $usuarioId = (int) $_SESSION["usuario"];

    if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES["imagem"];
        $extensao = strtolower(pathinfo($arquivo["name"], PATHINFO_EXTENSION));
        $novoNome = uniqid("pet_", true) . "." . $extensao;
        $caminho = "uploads/" . $novoNome;
        $destino = __DIR__ . "/../" . $caminho;

        if (move_uploaded_file($arquivo["tmp_name"], $destino)) {
            $stmt = $conn->prepare("INSERT INTO pets (nome, tipo, idade, raca, descricao, doador, imagem, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssi", $nome, $tipo, $idade, $raca, $descricao, $doador, $caminho, $usuarioId);

            if ($stmt->execute()) {
                header("Location: listar_pets.php");
                exit;
            }

            $erro = "Erro ao salvar no banco.";
        } else {
            $erro = "Erro no upload da imagem.";
        }
    } else {
        $erro = "Selecione uma imagem.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar pet - WebDog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/cadastrar.css">
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

<main class="form-container">
    <div class="form-box wide-form">
        <h2>Cadastrar pet</h2>

        <?php if ($erro !== ""): ?>
            <div class="alerta"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="field">
                    <label for="nome">Nome</label>
                    <input id="nome" type="text" name="nome" required>
                </div>

                <div class="field">
                    <label for="idade">Idade</label>
                    <input id="idade" type="text" name="idade" placeholder="Ex.: 2 anos" required>
                </div>

                <div class="field">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" required>
                        <option value="Cachorro">Cachorro</option>
                        <option value="Gato">Gato</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>

                <div class="field">
                    <label for="raca">Raça</label>
                    <input id="raca" type="text" name="raca" required>
                </div>

                <div class="field">
                    <label for="doador">Nome do doador</label>
                    <input id="doador" type="text" name="doador" required>
                </div>
            </div>

            <div class="field">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" placeholder="Conte um pouco sobre o pet, temperamento, cuidados e rotina." required></textarea>
            </div>

            <div class="field">
                <label for="imagem">Foto do pet</label>
                <input id="imagem" type="file" name="imagem" accept="image/*" required>
            </div>

            <button type="submit" class="btn">Cadastrar pet</button>
        </form>

        <a class="form-link" href="menu.php">Voltar</a>
    </div>
</main>

</body>
</html>












