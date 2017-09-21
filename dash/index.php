<?php
session_start();
require_once 'includes/auth_validate.php';
require_once 'includes/database.php';

   $url1=$_SERVER['PHP_SELF'];
    header("Refresh: 5; URL=$url1");



include_once('includes/header.php');


$servername = "localhost";
$username = "root";
$password = "root";

try {
    $conn = new PDO("mysql:host=$servername;dbname=snortdb", $username, $password);
    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    

    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }

// *****

$event = $conn->query("SELECT * FROM event order by cid desc");
$acid_event = $conn->query("SELECT cid FROM acid_event order by cid desc limit 1")->fetch(PDO::FETCH_ASSOC);

while ($row = $event->fetch(PDO::FETCH_ASSOC))
{

if ($row["cid"] == $acid_event["cid"])
{
     break;
}

else {
    
    $sig = $conn->query("SELECT sig_name FROM signature where sig_id='".$row['signature']."'")->fetch(PDO::FETCH_ASSOC);
    $other = $conn->query("SELECT * FROM iphdr where cid='".$row['signature']."'")->fetch(PDO::FETCH_ASSOC);

    $add = $conn->exec("insert into acid_event VALUES ('".$row["sid"]."','".$row["cid"]."','".$row["signature"]."','".$sig["sig_name"]."','0','0','".$row["timestamp"]."','"
                        .$other["ip_src"]."','".$other["ip_dst"]."','".$other["ip_proto"]."','0','0')");

}


}

?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Dashboard</h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <div class="row">
        <div class="col-lg-12">
    <table class="table">
          <thead>
    <tr>
      <th>#ID</th>
      <th>SIG Name</th>
      <th>IP source</th>
      <th>IP dest</th>
      <th>Date</th>
      <th>Protocol</th>

    </tr>
  </thead>
        
<?php
$pro = Array("1" => "ICMP", "17" => "UDP", "6" => "TCP");

$result = $conn->query("SELECT * FROM acid_event order by cid desc limit 20");
while ($row = $result->fetch(PDO::FETCH_ASSOC))
{
    echo "<tr>";
echo "<th>".$row["cid"]."</th>";
echo "<th>".$row["sig_name"]."</th>";
    $ip_src = $conn->query("SELECT ipc_fqdn FROM acid_ip_cache where ipc_ip='".$row['ip_src']."'")->fetch(PDO::FETCH_ASSOC);
echo "<th>".$ip_src["ipc_fqdn"]."</th>";
    $ip_dst = $conn->query("SELECT ipc_fqdn FROM acid_ip_cache where ipc_ip='".$row['ip_dst']."'")->fetch(PDO::FETCH_ASSOC);
echo "<th>".$ip_dst["ipc_fqdn"]."</th>";


echo "<th>".$row["timestamp"]."</th>";
echo "<th>".$pro[$row["ip_proto"]]."</th>";

echo "</tr>";
}
     
?>       
    </table>            
                
                
                
                
                
                
        </div>
    </div>
    <!-- /.row -->
    <div class="row">
        <div class="col-lg-8">


            <!-- /.panel -->
        </div>
        <!-- /.col-lg-8 -->
        <div class="col-lg-4">

            <!-- /.panel .chat-panel -->
        </div>
        <!-- /.col-lg-4 -->
    </div>
    <!-- /.row -->
</div>
<!-- /#page-wrapper -->

<?php include_once('includes/footer.php');


?>
