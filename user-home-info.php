<?php

include 'header-init.php';
include 'jwt-helper.php';

$user = extractJwtBody();

if (!isset($user->id)) {
    http_response_code(401);
    echo '{"message": "Utilisateur non authentifié"}';
    exit();
}

$idUser = $user->id;

$requete = $connexion->prepare("SELECT firstname, lastname, email FROM user WHERE id = ?");
$requete->execute([$idUser]);

$userInfo = $requete->fetch(PDO::FETCH_ASSOC);

if (!$userInfo) {
    echo json_encode(["message" => "Utilisateur inexistant"]);
    http_response_code(404);
    exit();
}

echo json_encode([
    "name" => $userInfo['firstname'] . ' ' . $userInfo['lastname']
]);
