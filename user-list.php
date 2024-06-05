<?php

include 'header-init.php';
include 'jwt-helper.php';

$user = extractJwtBody();

if ($user->role != "Administrateur") {
    http_response_code(403);
    echo '{"message" : "Vous n\avez pas les droits nécessaires"}';
    exit();
}

$request = $connexion->query("  SELECT u.id, email, lastname, firstname, name AS role_name  
                                FROM user AS u 
                                JOIN role AS r ON u.id_role = r.id");

$userList = $request->fetchAll();

echo json_encode($userList);
