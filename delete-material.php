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

$idMateriel = $_GET['id'];

//On recupère l'utilisdateur dans la bdd
$requete = $connexion->prepare("SELECT * FROM materiel WHERE id = ?");
$requete->execute([$idMateriel]);
$materiel = $requete->fetch();

//si il n'y a pas d'utilisateur on retourne une erreur 404
if (!$materiel) {
    http_response_code(404);
    echo '{"message" : "Ce materiel n\'existe pas"}';
    exit();
}

$requete = $connexion->prepare("DELETE FROM materiel WHERE id = ?");

$requete->execute([$idMateriel]);

echo '{"message" : "Le materiel a bien été supprimé"}';
