<?php
include("../includes/config.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$usuarioId = (int) $_SESSION['usuario'];
$id = (int) ($_GET["id"] ?? 0);

$stmt = $conn->prepare("SELECT * FROM pets WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $id, $usuarioId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: listar_pets.php");
    exit;
}

$pet = $result->fetch_assoc();
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"] ?? "");
    $tipo = trim($_POST["tipo"] ?? "Cachorro");
    $idade = trim($_POST["idade"] ?? "");
    $raca = trim($_POST["raca"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $doador = trim($_POST["doador"] ?? "");
    $imagem = $pet["imagem"];

    if (isset($_FILES["imagem"]) && $_FILES["imagem"]["error"] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES["imagem"];
        $extensao = strtolower(pathinfo($arquivo["name"], PATHINFO_EXTENSION));
        $novoNome = uniqid("pet_", true) . "." . $extensao;
        $caminho = "uploads/" . $novoNome;
        $destino = __DIR__ . "/../" . $caminho;

        if (move_uploaded_file($arquivo["tmp_name"], $destino)) {
            $imagem = $caminho;
        }
    }

    $stmt = $conn->prepare("UPDATE pets SET nome = ?, tipo = ?, idade = ?, raca = ?, descricao = ?, doador = ?, imagem = ? WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("sssssssii", $nome, $tipo, $idade, $raca, $descricao, $doador, $imagem, $id, $usuarioId);

    if ($stmt->execute()) {
        header("Location: detalhes_pet.php?id=" . $id);
        exit;
    }

    $erro = "Erro ao atualizar.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar pet - WebDog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/editar.css">
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
    <div class="form-box">
        <h2>Editar pet</h2>

        <?php if ($erro): ?>
            <div class="alerta"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="field"><label for="nome">Nome</label><input id="nome" type="text" name="nome" value="<?= e($pet['nome']) ?>" required></div>
                <div class="field"><label for="idade">Idade</label><input id="idade" type="text" name="idade" value="<?= e($pet['idade']) ?>" required></div>
                <div class="field">
                    <label for="tipo">Tipo</label>
                    <select id="tipo" name="tipo" required>
                        <?php foreach (["Cachorro", "Gato", "Outro"] as $tipoOpcao): ?>
                            <option value="<?= e($tipoOpcao) ?>" <?= ($pet["tipo"] ?? "Cachorro") === $tipoOpcao ? "selected" : "" ?>><?= e($tipoOpcao) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field"><label for="raca">Raça</label><input id="raca" type="text" name="raca" value="<?= e($pet['raca']) ?>" required></div>
                <div class="field"><label for="doador">Doador</label><input id="doador" type="text" name="doador" value="<?= e($pet['doador']) ?>" required></div>
            </div>

            <div class="field"><label for="descricao">Descrição</label><textarea id="descricao" name="descricao" required><?= e($pet['descricao']) ?></textarea></div>
            <div class="field"><label>Imagem atual</label><img src="<?= e(app_url($pet['imagem'])) ?>" class="preview" alt="Foto atual de <?= e($pet['nome']) ?>"></div>
            <div class="field"><label for="imagem">Trocar imagem</label><input id="imagem" type="file" name="imagem" accept="image/*"></div>
            <button type="submit" class="btn">Atualizar pet</button>
        </form>
    </div>
</main>

</body>
</html>










