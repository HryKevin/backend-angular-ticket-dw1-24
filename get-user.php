<?php

include 'header-init.php';
include 'jwt-helper.php';

$user = extractJwtBody();

if ($user->role != "Administrateur") {
    http_response_code(403);
    echo '{"message" : "Vous n\avez pas les droits nécessaires"}';
    exit();
}

if (!isset($_GET['id'])) {
    echo '{"message" : "il n\'y a pas d\'identiant dans l\'URL"}';
    http_response_code(400);
    exit;
}

$idUser = $_GET["id"];

$requete = $connexion->prepare("SELECT u.email, u.id, r.name AS role, u.firstname, u.lastname
                                FROM user AS u 
                                JOIN role AS r ON u.id_role = r.id
                                WHERE u.id = ?");
$requete->execute([$idUser]);

$user = $requete->fetch();

if (!$user) {
    echo json_encode(["message" => "utilisateur inexistant"]);
    http_response_code(404);
    exit;
}

echo json_encode($user);
