<?php
    require_once 'connect.php';

    // Fetch grand clients for the dropdown
    $sqlClients = "SELECT GrandClientID, nomGrandClient FROM grandclients";
    $reqClients = $pdo->prepare($sqlClients);
    $reqClients->execute();
    $clients = $reqClients->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <form id="filterForm">
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

    <div id="resultTable"></div>