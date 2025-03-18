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
  <style>
    .tabcontent {
      display: none;
    }

    .tablinks.active {
      background-color: #ccc;
    }
  </style>
</head>

<body>
  <div class="tab">
    <button class="tablinks" onclick="openTab(event, 'Tab1')">Tab 1</button>
    <button class="tablinks" onclick="openTab(event, 'Tab2')">Tab 2</button>
    <button class="tablinks" onclick="openTab(event, 'Tab3')">Tab 3</button>
  </div>

  <!-- tab1------------------------------------------------------------------------------- -->
  <div id="Tab1" class="tabcontent">
  <h1><center>Top 10 des applications par grand client (en €)</center></h1>
    <?php  
    $sqlClients = "SELECT GrandClientID, nomGrandClient FROM grandclients";
    $reqClients = $pdo->prepare($sqlClients);
    $reqClients->execute();
    $clients = $reqClients->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <form id="filterFormTab1">
      <div class="form-group">
        <label for="grandClientTab1">Sélectionnez un grand client :</label>
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
    <br>
    <div id="resultTable"></div>
  </div>
<!-- tab2---------------------------------------------------------------------------------------------- -->

  <div id="Tab2" class="tabcontent">
  <h1><center>Évolution des montants pour les 3 plus grands clients</center></h1>
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

      let ctxx = document.getElementById('montantChart').getContext('2d');
      new Chart(ctxx, {
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

<!-- TAB 3 début ------------------------------------------------------------------------------------ -->

<div id="Tab3" class="tabcontent">
    <h1><center>Évolution des volumes des produits 1_1 et 1_4</center></h1>
    <?php
    // Requête SQL pour récupérer les volumes par période pour les produits 13 et 20
    $query = "
        SELECT
            DATE_FORMAT(lf.mois, '%Y-%m') AS periode,
            p.NOM_PRODUIT,
            SUM(lf.volume) AS total_volume
        FROM ligne_facturation lf
        JOIN produit p ON lf.produitID = p.produitID
        WHERE p.produitID IN (13, 20)
          AND lf.mois >= '2021-01-01'
          AND lf.mois <= '2022-04-30'
        GROUP BY DATE_FORMAT(lf.mois, '%Y-%m'), p.NOM_PRODUIT
        ORDER BY periode, p.NOM_PRODUIT
    ";

    $result = $pdo->query($query);
    $data = [];

    // Traitement des résultats
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
      $data[] = [
        'periode' => $row['periode'],
        'produit' => $row['NOM_PRODUIT'],
        'volume'  => (int)$row['total_volume']
      ];
    }

 

    // Organisation des données pour Chart.js
    $periodes = [];         // liste des périodes distinctes
    $volumesParProduit = []; // volumes regroupés par produit

    foreach ($data as $row) {
      $periode = $row['periode'];
      $produit = $row['produit'];
      $volume  = $row['volume'];

      if (!in_array($periode, $periodes)) {
        $periodes[] = $periode;
      }
      $volumesParProduit[$produit][$periode] = $volume;
    }
    sort($periodes); // tri chronologique

    // Préparation des datasets pour Chart.js
    $datasets = [];
    $couleurs = [
      "rgba(255, 99, 132, 0.7)",
      "rgba(54, 162, 235, 0.7)",
      "rgba(255, 205, 86, 0.7)"
    ];

    $i = 0;
    foreach ($volumesParProduit as $produitName => $listeVolumes) {
      $dataPoints = [];
      foreach ($periodes as $periode) {
        $dataPoints[] = isset($listeVolumes[$periode]) ? $listeVolumes[$periode] : 0;
      }
      $datasets[] = [
        'label' => $produitName,
        'data'  => $dataPoints,
        'backgroundColor' => $couleurs[$i % count($couleurs)]
      ];
      $i++;
    }
    ?>

    <!-- Conteneur pour le graphique -->
    <div class="chart-container">
      <canvas id="myChart"></canvas>
    </div>

    <script>
      // Récupération des données PHP encodées en JSON
      var periodesJS = <?php echo json_encode($periodes); ?>;
      var datasetsJS = <?php echo json_encode($datasets); ?>;

      // Création du graphique avec Chart.js
      var ctx = document.getElementById('myChart').getContext('2d');
      var myChart = new Chart(ctx, {
        type: 'bar', // Vous pouvez changer en 'line', 'scatter', etc.
        data: {
          labels: periodesJS,
          datasets: datasetsJS
        },
        options: {
          responsive: true,
          maintainAspectRatio: false, // Respecte les dimensions du conteneur
          scales: {
            x: {
              title: {
                display: true,
                text: 'Période (AAAA-MM)'
              }
            },
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Volume'
              }
            }
          }
        }
      });
      
    
    function openTab(evt, tabName) {
      var i, tabcontent, tablinks;
      tabcontent = document.getElementsByClassName("tabcontent");
      for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
      }
      tablinks = document.getElementsByClassName("tablinks");
      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
      }
      document.getElementById(tabName).style.display = "block";
      evt.currentTarget.className += " active";
    }

    // Open the first tab by default
    document.getElementsByClassName("tablinks")[0].click();
  </script>
  <style>
      /* Style du conteneur pour le graphique */
      .chart-container {
        width: 1100px;
        /* Largeur fixe */
        height: 800px;
        /* Hauteur fixe */
        margin: 0 auto;
        border: 1px solid #ccc;
        padding: 10px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        background: #fff;
      }
 
      .chart-container canvas {
        width: 100% !important;
        height: auto !important;
      }
    </style>
  
  </div>

  <!-- TAB 3 FIN ------------------------------------------------------------------------------------ -->

  

<!-- TAB 3 FIN ------------------------------------------------------------------------------------ -->
</body>

</html>
