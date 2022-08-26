<?php
use WHMCS\Database\Capsule;
function custom_module_config() {
    $configarray = array(
        "name" => "Custom Page Addon Module",
        "description" => "This is a Custom Page Addon Module",
        "version" => "1.1.0",
        "author" => "Kuroit",
        "fields" => array(),
    );
    return $configarray;
}

function custom_module_activate() {
// Create custom tables and schema required by your module
    try {
        Capsule::schema()
            ->create('custom_module',
                function ($table) {
                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                    $table->increments('id');
                    $table->text('url');
                    $table->text('title');
                    $table->text('content');    
                    $table->text('slug');
                    $table->text('author');
                    $table->date('created_date');
                    $table->text('add_to_navbar');
                }
            );
        return [
            // Supported values here include: success, error or info
            'status' => 'success',
            'description' => 'This is a demo module only. '
                . 'In a real module you might report a success or instruct a '
                    . 'user how to get started with it here.',
        ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            'status' => "error",
            'description' => 'Unable to create custom_module: ' . $e->getMessage(),
        ];
    }
}

function custom_module_deactivate() {
// Undo any database and schema modifications made by your module here
    try {
        Capsule::schema()
            ->dropIfExists('custom_module');
            return [
                // Supported values here include: success, error or info
                'status' => 'success',
                'description' => 'This is a demo module only. '
                    . 'In a real module you might report a success here.',
            ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            "status" => "error",
            "description" => "Unable to drop custom_module: {$e->getMessage()}",
        ];
    }
}

function custom_module_output() {
    $servername = 'localhost';
    $username = 'devkuroi';
    $password = '943Gz1Nrxk';
    $dbname = 'devkuroi_whmcs';
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    if(!$conn){
        die(mysqli_error($conn));
    } 
    $url_link = "http://$_SERVER[HTTP_HOST]";
    if($stmt = $conn->query("SELECT id FROM custom_module")){
        echo "<div class='text-center'><h4>".$stmt->num_rows." Records Found</h4></div>";
    }
    echo '<div>
            <form method="POST">
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
                    Add New Page
                </button><br>
                <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">New Page</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                            <div class="form-group">
                                <label for="url" class="col-form-label">Url:</label>
                                <input type="text" class="form-control" id="url" disabled value="'.$url_link.'">
                            </div>
                            <div class="form-group">
                                <label for="title" class="col-form-label">Page Title:</label>
                                <input type="text" name="title" class="form-control" id="title">
                            </div>
                            <div class="form-group">
                                <label for="content" class="col-form-label">Content:</label>
                                <input type="text" name="content" class="form-control" id="content">
                            </div>
                            <div class="form-group">
                                <label for="slug" class="col-form-label">Slug:</label>
                                <input type="text" name="slug" class="form-control" id="slug">
                            </div>
                            <div class="form-group">
                                <label for="author" class="col-form-label">Author:</label>
                                <input type="text" name="author" class="form-control" id="author">
                            </div>
                            <div class="form-group">
                                <label for="date" class="col-form-label">Created Date:</label>
                                <input type="date" name="created_date" class="form-control" id="date">
                            </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <input type="submit" class="btn btn-primary" name="submit" value="Save">
                      </div>
                    </div>
                  </div>
                </div>
            </form>
        </div>';
        if(isset($_POST['submit'])){
            $title = $_POST['title'];
            $content = $_POST['content'];
            $slug = $_POST['slug'];
            $author = $_POST['author'];
            $date = $_POST['created_date'];

            $query = "INSERT INTO `custom_module`(`url`,`title`, `content`, `slug`, `author`, `created_date`, `add_to_navbar`) VALUES ('".$url."','".$title."','".$content."','".$slug."','".$author."','".$date."',0)";
            $result = mysqli_query($conn, $query);
            if($result){
                echo '<div class="alert alert-success">
                          <strong>Success!</strong>User inserted Successfully.
                        </div>';
            }
            else{
                echo '<div class="alert alert-danger">
                          <strong>Error!</strong>Oops, Something went wrong.
                        </div>';
            }
        }
        if(isset($_POST['save'])){
            $enablePages = $_POST['checkbox'];
            $sql="SELECT id FROM `custom_module`";
            $result =  mysqli_query($conn,$sql);
            if (mysqli_num_rows($result) > 0) {
                $allPages = mysqli_fetch_all($result, MYSQLI_ASSOC);
            }

            foreach($allPages as $key => $val) {
                $pageID = $val['id'];
                if (in_array($pageID, $enablePages)) {
                    $query = "UPDATE `custom_module` SET `add_to_navbar`=1 WHERE `id`='$pageID'";
                } else {
                    $query = "UPDATE `custom_module` SET `add_to_navbar`=0 WHERE `id`='$pageID'";
                }
                $updateResult = mysqli_query($conn, $query);
            }
        }
        ?>
        <form method="POST">
            <table class="table table-striped table-hover my-5">
                <thead class="thead-dark"><br>
                    <th class="col bg-primary text-white"><input type="checkbox" id="checkAll">Add To NavBar</th>
                    <th class="col bg-primary text-white">Page Title</th>
                    <th class="col bg-primary text-white">Content</th>
                    <th class="col bg-primary text-white">Slug</th>
                    <th class="col bg-primary text-white">Author's Name</th>
                    <th class="col bg-primary text-white">Created Date</th>
                </thead>
                
                <tbody><?php
                    $query = "SELECT * FROM custom_module";     
                    $result = mysqli_query($conn, $query);     
                    while($row = mysqli_fetch_assoc($result)) { ?>
                        <tr>
                            <td><input type="checkbox" id="checkItem" name="checkbox[]" value="<?php echo $row['id']; ?>" <?php if($row['add_to_navbar']=='1'){echo "checked";}?>></td>
                            <td><a href="#"><?php echo $row['title']; ?></a></td>
                            <td><?php echo $row['content']; ?></td>
                            <td><?php echo $row['slug']; ?></td>
                            <td><?php echo $row['author']; ?></td>
                            <td><?php echo $row['created_date']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="text-center">
                <input type='submit' name='save' value='Save' class="btn btn-success"></br>
            </div>
        </form>
            <script>
                $("#checkAll").click(function () {
                $('input:checkbox').not(this).prop('checked', this.checked);
                });
            </script>
<?php
}
function custom_module_clientarea($vars) {
    return array(
        'pagetitle' => 'Custom Page',
        'breadcrumb' => array('index.php?m=custom_module'=>'Custom Page'),
        'templatefile' => 'custom',
        'vars' => array(
            'testvar' => 'demo',
            'anothervar' => 'value',
            'sample' => 'test',
            'name'=> 'maneesh',
            'page'  => $_GET['p'],
        ),
    );
}
?>