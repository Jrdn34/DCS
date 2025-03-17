<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="style.css" />
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="script.js"></script>
  <script src="scripttab1.js"></script>
  <?php require_once 'connect.php'; ?>

</head>

<body>
  <div class="tab">
    <button class="tablinks" onclick="openTab(event, 'Tab1')">Tab 1</button>
    <button class="tablinks" onclick="openTab(event, 'Tab2')">Tab 2</button>
    <button class="tablinks" onclick="openTab(event, 'Tab3')">Tab 3</button>
  </div>

  <div id="Tab1" class="tabcontent">
    <?php  
    $sqlClients = "SELECT GrandClientID, nomGrandClient FROM grandclients";
    $reqClients = $pdo->prepare($sqlClients);
    $reqClients->execute();
    $clients = $reqClients->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <form id="filterFormTab1">
      <div class="form-group">
        <label for="grandClientTab1">Sélectionnez un grand client:</label>
        <select class="form-control" name="grandClient" id="grandClientTab1">
          <?php foreach ($clients as $client) : ?>
            <option value="<?php echo htmlspecialchars($client['GrandClientID']); ?>">
              <?php echo htmlspecialchars($client['nomGrandClient']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Filtrer</button>
    </form>
    <div id="resultTable"></div>
  </div>

  <div id="Tab2" class="tabcontent">
    <?php
    
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

    $result = $pdo->query($sql);
    $data = [];

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
      $data[$row['NomGrandClient']][] = [
        'mois' => $row['mois'],
        'total_montant' => $row['total_montant']
      ];
    } 
    ?>

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
        data: {
          labels,
          datasets
        },
        options: {
          responsive: true
        }
      });
    </script>
  </div>

  <div id="Tab3" class="tabcontent">
    <h3>Tab 3</h3>
    <p>Contenu pour Tab 3.</p>
  </div>


</body>

</html>