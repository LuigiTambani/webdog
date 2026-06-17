<?php
include("../includes/config.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: ../paginas/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../paginas/listar_pets.php");
    exit;
}

$usuarioId = (int) $_SESSION['usuario'];
$petId = (int) ($_POST["pet_id"] ?? 0);

$stmt = $conn->prepare("SELECT id, usuario_id FROM pets WHERE id = ?");
$stmt->bind_param("i", $petId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../paginas/listar_pets.php");
    exit;
}

$pet = $result->fetch_assoc();
$doadorId = (int) $pet["usuario_id"];

if ($doadorId === $usuarioId || $doadorId <= 0) {
    header("Location: ../paginas/detalhes_pet.php?id=" . $petId);
    exit;
}

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM solicitacoes_adocao WHERE pet_id = ? AND status = 'aprovada'");
$stmt->bind_param("i", $petId);
$stmt->execute();
$aprovada = $stmt->get_result()->fetch_assoc();

if ((int) $aprovada["total"] > 0) {
    header("Location: ../paginas/detalhes_pet.php?id=" . $petId);
    exit;
}

$stmt = $conn->prepare("SELECT id, status FROM solicitacoes_adocao WHERE pet_id = ? AND solicitante_id = ? LIMIT 1");
$stmt->bind_param("ii", $petId, $usuarioId);
$stmt->execute();
$existente = $stmt->get_result();

if ($existente->num_rows > 0) {
    $solicitacao = $existente->fetch_assoc();
    if (in_array($solicitacao["status"], ["recusada", "cancelada"], true)) {
        $stmt = $conn->prepare("UPDATE solicitacoes_adocao SET status = 'pendente' WHERE id = ?");
        $stmt->bind_param("i", $solicitacao["id"]);
        $stmt->execute();
    }

    header("Location: ../paginas/chat_adocao.php?id=" . (int) $solicitacao["id"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO solicitacoes_adocao (pet_id, solicitante_id, doador_id, status) VALUES (?, ?, ?, 'pendente')");
$stmt->bind_param("iii", $petId, $usuarioId, $doadorId);
$stmt->execute();
$solicitacaoId = $conn->insert_id;

header("Location: ../paginas/chat_adocao.php?id=" . (int) $solicitacaoId);
exit;


