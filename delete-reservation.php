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

$idReservation = $_GET['id'];

//On recupère l'utilisdateur dans la bdd
$requete = $connexion->prepare("SELECT * FROM reservation WHERE id = ?");
$requete->execute([$idReservation]);
$reservation = $requete->fetch();

//si il n'y a pas d'utilisateur on retourne une erreur 404
if (!$reservation) {
    http_response_code(404);
    echo '{"message" : "Cet utilisateur n\'existe pas"}';
    exit();
}

$requete = $connexion->prepare("DELETE FROM reservation WHERE id = ?");

$requete->execute([$idReservation]);

echo '{"message" : "La réservation a bien été supprimée"}';
