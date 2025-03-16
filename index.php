<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
  <div class="tab">
    <button class="tablinks" onclick="openTab(event, 'Tab1')">Tab 1</button>
    <button class="tablinks" onclick="openTab(event, 'Tab2')">Tab 2</button>
    <button class="tablinks" onclick="openTab(event, 'Tab3')">Tab 3</button>
  </div>

  <div id="Tab1" class="tabcontent">
  
    <?php
    require_once 'connect.php';

    // Fetch grand clients for the dropdown
    $sqlClients = "SELECT GrandClientID, nomGrandClient FROM grandclients";
    $reqClients = $pdo->prepare($sqlClients);
    $reqClients->execute();
    $clients = $reqClients->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <form method="GET" action="">
      <div class="form-group">
        <label for="grandClient">Sélectionnez un grand client:</label>
        <select class="form-control" name="grandClient" id="grandClient">
        <?php foreach ($clients as $client) : ?>
          <option value="<?php echo htmlspecialchars($client['GrandClientID']); ?>">
          <?php echo htmlspecialchars($client['nomGrandClient']); ?>
          </option>
        <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Filtrer</button>
    </form>

    <?php
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

      echo '<table class="table table-striped">';
      echo "<thead><tr><th>Nom de l'application</th>
      <th>Prix total</th>
      <th>Nom du grand client</th></tr></thead>";
      echo "<tbody>";
      foreach ($resultat as $ligne) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($ligne["nomAppli"]) . "</td>";
        echo "<td>" . htmlspecialchars($ligne["totalPrix"]) . "€</td>";
        echo "<td>" . htmlspecialchars($ligne["nomGrandClient"]) . "</td>";
        echo "</tr>";
      }
      echo "</tbody>";
      echo "</table>";
    }
    ?>
  </div>

  <div id="Tab2" class="tabcontent">
    <h3>Tab 2</h3>
    <p>Contenu pour Tab 2.</p>
  </div>

  <div id="Tab3" class="tabcontent">
    <h3>Tab 3</h3>
    <p>Contenu pour Tab 3.</p>
  </div>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script src="script.js"></script>
</body>

</html>