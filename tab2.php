<?php
require_once 'connect.php';

header('Content-Type: application/json');

try {
    $grandClientID = $_GET['grandClient'];

    $sql = "
    WITH top_clients AS ( SELECT gc.GrandClientID FROM ligne_facturation lf JOIN clients c ON lf.CentreActiviteID = c.CentreActiviteID JOIN grandclients gc ON c.GrandClientID = gc.GrandClientID WHERE lf.mois BETWEEN '2021-01-01' AND '2022-04-30' GROUP BY gc.GrandClientID ORDER BY SUM(lf.prix) DESC LIMIT 5 ) SELECT DATE_FORMAT(lf.mois, '%Y-%m') AS mois, gc.NomGrandClient, SUM(lf.prix) AS total_montant FROM ligne_facturation lf JOIN clients c ON lf.CentreActiviteID = c.CentreActiviteID JOIN grandclients gc ON c.GrandClientID = gc.GrandClientID WHERE gc.GrandClientID IN (SELECT GrandClientID FROM top_clients) AND lf.mois BETWEEN '2021-01-01' AND '2022-04-30' GROUP BY mois, gc.NomGrandClient ORDER BY mois, total_montant DESC;
";
    $req = $pdo->prepare($sql);
    $req->execute(['grandClientID' => $grandClientID]);
    $result = $req->fetchAll(PDO::FETCH_ASSOC);

    $labels = [];
    $values = [];

    foreach ($result as $row) {
        $labels[] = $row['mois'];
        $values[] = $row['montant_facture'];
    }

    echo json_encode(['labels' => $labels, 'values' => $values]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>