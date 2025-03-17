<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dcs";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "WITH top_clients AS (
            SELECT gc.GrandClientID 
            FROM ligne_facturation lf 
            JOIN clients c ON lf.CentreActiviteID = c.CentreActiviteID 
            JOIN grandclients gc ON c.GrandClientID = gc.GrandClientID 
            WHERE lf.mois BETWEEN '2021-01-01' AND '2022-04-30' 
            GROUP BY gc.GrandClientID 
            ORDER BY SUM(lf.prix) DESC 
            LIMIT 5
        )
        SELECT DATE_FORMAT(lf.mois, '%Y-%m') AS mois, gc.NomGrandClient, SUM(lf.prix) AS total_montant 
        FROM ligne_facturation lf 
        JOIN clients c ON lf.CentreActiviteID = c.CentreActiviteID 
        JOIN grandclients gc ON c.GrandClientID = gc.GrandClientID 
        WHERE gc.GrandClientID IN (SELECT GrandClientID FROM top_clients) 
        AND lf.mois BETWEEN '2021-01-01' AND '2022-04-30' 
        GROUP BY mois, gc.NomGrandClient 
        ORDER BY mois, total_montant DESC";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
    $data[$row['NomGrandClient']][] = [
        'mois' => $row['mois'],
        'total_montant' => $row['total_montant']
    ];
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Évolution des montants</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <canvas id="montantChart"></canvas>
    <script>
        const data = <?php echo json_encode($data); ?>;
        const labels = [...new Set(Object.values(data).flat().map(d => d.mois))];
        const datasets = Object.entries(data).map(([client, values], index) => ({
            label: client,
            data: labels.map(mois => {
                const record = values.find(v => v.mois === mois);
                return record ? record.total_montant : 0;
            }),
            borderColor: `hsl(${index * 60}, 70%, 50%)`,
            fill: false,
        }));
        
        const ctx = document.getElementById('montantChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: { labels, datasets },
            options: { responsive: true }
        });
    </script>
</body>
</html>