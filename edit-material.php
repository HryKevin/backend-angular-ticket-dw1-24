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

$idMaterial = $_GET["id"];

// Prend les données brutes de la requête
$json = file_get_contents('php://input');

// Le convertit en objet PHP
$material = json_decode($json);


//On recupère l'utilisateur dans la bdd
$requete = $connexion->prepare("SELECT * FROM materiel WHERE id = ?");
$requete->execute([$idMaterial]);
$materialDb = $requete->fetch();

//si il n'y a pas d'utilisateur on retourne une erreur 404
if (!$materialDb) {
    http_response_code(404);
    echo '{"message" : "Ce matériel n\'existe pas"}';
    exit();
}




$requete = $connexion->prepare("UPDATE materiel SET 
                                    nom = :nom,
                                    date_achat = :date_achat, 
                                    numero_de_serie = :numero_de_serie
                                WHERE id = :id");

$requete->execute([
    "nom" => $material->nom,
    "date_achat" => $material->date_achat,
    "numero_de_serie" => $material->numero_de_serie,
    "id" => $idMaterial,
]);

echo '{"message" : "Le matériel a bien été modifié"}';
