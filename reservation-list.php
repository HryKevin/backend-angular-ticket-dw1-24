<?php

include 'header-init.php';
include 'jwt-helper.php';

$user = extractJwtBody();

if ($user->role != "Administrateur") {
    http_response_code(403);
    echo '{"message" : "Vous n\avez pas les droits nécessaires"}';
    exit();
}

$request = $connexion->query("  SELECT   u.email, u.firstname, u.lastname, date_debut, date_fin, accepte, m.nom, m.numero_de_serie
                                FROM reservation AS r
                                LEFT JOIN user AS u  ON r.id_loueur = u.id
                                LEFT JOIN materiel AS m ON r.id_materiel = m.id");

$reservationList = $request->fetchAll();

echo json_encode($reservationList);
