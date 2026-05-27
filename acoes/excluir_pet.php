<?php
include("../includes/config.php");

if (!isset($_SESSION['usuario'])) {
    header("Location: ../paginas/login.php");
    exit;
}

$usuarioId = (int) $_SESSION['usuario'];
$id = (int) ($_GET["id"] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM pets WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id, $usuarioId);
    $stmt->execute();
}

header("Location: ../paginas/listar_pets.php");
exit;



