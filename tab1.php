<?php
require_once 'connect.php';

if (isset($_GET['grandClient'])) {
    $grandClientID = $_GET['grandClient'];

    $sql = "SELECT nomAppli, SUM(prix) AS totalPrix, nomGrandClient FROM application 
        INNER JOIN ligne_facturation ON application.IRT = ligne_facturation.IRT 
        INNER JOIN centresactivite ON ligne_facturation.centreActiviteID = centresactivite.centreActiviteID 
        INNER JOIN clients ON clients.centreActiviteID = centresactivite.centreActiviteID 
        INNER JOIN grandclients ON grandclients.GrandClientID = clients.GrandClientID
        WHERE grandclients.GrandClientID = :grandClientID
        GROUP BY nomAppli, nomGrandClient
        ORDER BY totalPrix DESC
        LIMIT 10";
    $req = $pdo->prepare($sql);
    $req->bindParam(':grandClientID', $grandClientID, PDO::PARAM_INT);
    $req->execute();
    $resultat = $req->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultat);
}
?>