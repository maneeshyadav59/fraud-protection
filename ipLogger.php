<?php
use WHMCS\Database\Capsule;
function ipLogger_config()
{
    $configarray = [
        "name" => "IP Logger",
        "description" => "This is a IP Logger",
        "version" => "1.1.0",
        "author" => "Kuroit",
        "fields" => [],
    ];
    return $configarray;
}

function ipLogger_activate()
{
    // Create custom tables and schema required by your module
    try {
        Capsule::schema()->create("ipLogger", function ($table) {
            /** @var \Illuminate\Database\Schema\Blueprint $table */
            $table->increments("id");
            $table->text("client_id");
            $table->text("ip_address");
            $table->text("action_type");
            $table->timestamp("insert_date");
        });
        return [
            // Supported values here include: success, error or info
            "status" => "success",
            "description" =>
                "This is a demo module only. " .
                "In a real module you might report a success or instruct a " .
                "user how to get started with it here.",
        ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            "status" => "error",
            "description" => "Unable to create ipLogger: " . $e->getMessage(),
        ];
    }
}

function ipLogger_deactivate()
{
    // Undo any database and schema modifications made by your module here
    try {
        Capsule::schema()->dropIfExists("ipLogger");
        return [
            // Supported values here include: success, error or info
            "status" => "success",
            "description" =>
                "This is a demo module only. " .
                "In a real module you might report a success here.",
        ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            "status" => "error",
            "description" => "Unable to drop ipLogger: {$e->getMessage()}",
        ];
    }
}

function ipLogger_output()
{
    echo '
   <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.5/dist/umd/popper.min.js" integrity="sha384-Xe+8cL9oJa6tN/veChSP7q+mnSPaj5Bcu9mPX5F5xIGE0DVittaqT5lorf0EI7Vk" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.min.js" integrity="sha384-ODmDIVzN+pFdexxHEHFBQH3/9/vQ9uori45z4JjnFsRydbmQbmL5t1tQ0culUzyK" crossorigin="anonymous"></script>

    <button type="button" class="btn btn-primary"><a href="https://dev.kuroit.co.uk/kuroitadmin/addonmodules.php?module=ipLogger">IP-Logs</a></button>

<button type="button" class="btn btn-primary">Duplicate</button>

<button type="button" class="btn btn-primary"><a href="https://dev.kuroit.co.uk/kuroitadmin/addonmodules.php?module=ipLogger&action=clear">Clear</a></button>

<div class="dropdown">
  <button class="btn btn-secondary dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
    Clear-Logs(in days)
  </button>

  <ul class="dropdown-menu">
    <li><a class="dropdown-item" href="#">30 days</a></li>
    <li><a class="dropdown-item" href="#">90 days</a></li>
    <li><a class="dropdown-item" href="#">180 days</a></li>
    <li><a class="dropdown-item" href="#">360 days</a></li>
  </ul>
</div>

<form method="post">
<label>Search</label>
<input type="text" name="search">
<input type="submit" name="submit">
</form>

';

    if (!isset($_GET["action"])) {

        $pdo = Capsule::connection()->getPdo();

        $limit = 3;

        $page = 1;

        $stmt2 = $pdo->query("SELECT count(*) FROM ipLogger");
        $user2 = $stmt2->fetchColumn();
        $value = $user2 / $limit;
        $total_pages = ceil($value);

        if (isset($_GET["page"]) && $_GET["page"] != "") {
            $page = $_GET["page"];
        }

        $starting_limit = ($page - 1) * $limit;

        $r = $pdo->prepare("SELECT * FROM ipLogger LIMIT ?,?");
        $r->execute([$starting_limit, $limit]);
        $show_user = $r->fetchAll(PDO::FETCH_ASSOC);

        if (isset($_POST["submit"])) {
            $pdo = Capsule::connection()->getPdo();
            $str = $_POST["search"];
            $r = $pdo->prepare("SELECT * FROM ipLogger WHERE ip_address = '$str'");
        $r->execute();
        $sth = $r->fetchAll(PDO::FETCH_ASSOC);
         echo'<table class="table">
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Client-ID</th>
      <th scope="col">IP-Address</th>
      <th scope="col">Action-Type</th>
      <th scope="col">Date</th>
    </tr>
  </thead>
  <tbody>';

        foreach ($sth as $sth) {
    
    
     echo ' <tr>
     <td>' .$sth["id"] .'</td>
     <td>' .$sth["client_id"] .'</td>
     <td>' .$sth["ip_address"] .'</td>
     <td>' .$sth["action_type"] .'</td>
     <td>' .$sth["insert_date"] .'</td>
    </tr>';
        }
        echo '</tbody>
</table>';
}
 echo '
   <table class="table">
  <thead>
    <tr>
      <th scope="col">ID</th>
      <th scope="col">Client-ID</th>
      <th scope="col">IP-Address</th>
      <th scope="col">Action-Type</th>
      <th scope="col">Date</th>
    </tr>
  </thead>
  <tbody>';
        foreach ($show_user as $show_user) {
            echo ' <tr>
     <td>' .$show_user["id"] .'</td>
     <td>' .$show_user["client_id"] .'</td>
     <td>' .$show_user["ip_address"] .'</td>
     <td>' .$show_user["action_type"] .'</td>
     <td>' .$show_user["insert_date"] .'</td>
    </tr>';
        }
        echo '</tbody>
</table>';
        ?>
<?php for ($page = 1; $page <= $total_pages; $page++): ?>

<a href='<?php echo "https://dev.kuroit.co.uk/kuroitadmin/addonmodules.php?module=ipLogger&page=" .
    $page; ?>' class="links">
  <?php echo $page; ?>
 </a>

<?php endfor;
    }
}
if (isset($_GET["action"]) && $_GET["action"] == "clear") {
    // echo"hyyy";
}
?>
        