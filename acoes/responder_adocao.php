<?php
include("../includes/config.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: ../paginas/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../paginas/adocoes.php");
    exit;
}

$usuarioId = (int) $_SESSION['usuario'];
$solicitacaoId = (int) ($_POST["solicitacao_id"] ?? 0);
$acao = $_POST["acao"] ?? "";

$stmt = $conn->prepare("SELECT * FROM solicitacoes_adocao WHERE id = ? AND doador_id = ? AND status = 'pendente'");
$stmt->bind_param("ii", $solicitacaoId, $usuarioId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: ../paginas/adocoes.php");
    exit;
}

$solicitacao = $result->fetch_assoc();

if ($acao === "aceitar") {
    $conn->begin_transaction();

    $stmt = $conn->prepare("UPDATE solicitacoes_adocao SET status = 'aprovada' WHERE id = ? AND doador_id = ?");
    $stmt->bind_param("ii", $solicitacaoId, $usuarioId);
    $stmt->execute();

    $stmt = $conn->prepare("UPDATE solicitacoes_adocao SET status = 'recusada' WHERE pet_id = ? AND id <> ? AND status = 'pendente'");
    $stmt->bind_param("ii", $solicitacao["pet_id"], $solicitacaoId);
    $stmt->execute();

    $conn->commit();
} elseif ($acao === "recusar") {
    $stmt = $conn->prepare("UPDATE solicitacoes_adocao SET status = 'recusada' WHERE id = ? AND doador_id = ?");
    $stmt->bind_param("ii", $solicitacaoId, $usuarioId);
    $stmt->execute();
}

header("Location: ../paginas/adocoes.php");
exit;



