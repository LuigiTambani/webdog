<?php
include("../includes/config.php");

header("Content-Type: application/json; charset=utf-8");

if (!isset($_SESSION["usuario"])) {
    echo json_encode(["erro" => "login"]);
    exit;
}

$usuarioId = (int) $_SESSION["usuario"];
$solicitacaoId = (int) ($_GET["id"] ?? 0);

$stmt = $conn->prepare("SELECT id FROM solicitacoes_adocao WHERE id = ? AND (solicitante_id = ? OR doador_id = ?)");
$stmt->bind_param("iii", $solicitacaoId, $usuarioId, $usuarioId);
$stmt->execute();
$solicitacao = $stmt->get_result();

if ($solicitacao->num_rows === 0) {
    echo json_encode(["erro" => "negado"]);
    exit;
}

$stmt = $conn->prepare("SELECT m.id, m.remetente_id, m.mensagem, m.criado_em, u.nome AS remetente_nome
    FROM mensagens_adocao m
    INNER JOIN usuarios u ON u.id = m.remetente_id
    WHERE m.solicitacao_id = ?
    ORDER BY m.criado_em ASC, m.id ASC");
$stmt->bind_param("i", $solicitacaoId);
$stmt->execute();
$resultado = $stmt->get_result();

$mensagens = [];
while ($mensagem = $resultado->fetch_assoc()) {
    $mensagens[] = [
        "id" => (int) $mensagem["id"],
        "minha" => (int) $mensagem["remetente_id"] === $usuarioId,
        "remetente" => (int) $mensagem["remetente_id"] === $usuarioId ? "Você" : $mensagem["remetente_nome"],
        "texto" => $mensagem["mensagem"],
        "hora" => date("H:i", strtotime($mensagem["criado_em"])),
    ];
}

echo json_encode(["mensagens" => $mensagens], JSON_UNESCAPED_UNICODE);
exit;
