<?php
session_start();
require_once 'includes/auth_validate.php';
require_once 'includes/database.php';



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
            <h1 class="page-header">Statistics</h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <div class="row">
        <div class="col-lg-12">

<form class="form-inline" method="POST" action="statistics.php">
    <div>Choose date range :</div>
  <label class="sr-only" for="startdate">Start date</label>
  <input type="text" class="form-control mb-2 mr-sm-2 mb-sm-0 datepicker" name="startdate" id="startdate">

  <label class="sr-only" for="enddate">End date</label>
    <input type="text" class="form-control datepicker" name="enddate" id="enddate">
</br>
</br>
</br>

  <button type="submit" class="btn btn-primary">Submit</button>
</form>

<?php
if (isset($_POST["startdate"]) && isset($_POST["enddate"]) )
{
    $startdate= date_create_from_format('Y-m-j', $_POST["startdate"]);
    $enddate= date_create_from_format('Y-m-j', $_POST["enddate"]);
    $sd = date_format($startdate,'Y-m-d');
    $ed = date_format($enddate,'Y-m-d');
     

$sql = 0;
$blindsql=0;
$xss=0;
$localpath=0;
$blocked=0;
$nonblocked= 0;

$all = $conn->query("SELECT * FROM acid_event where timestamp between '".$sd."' and '".$ed."' ");
while ($row = $all->fetch(PDO::FETCH_ASSOC))
{
if ( strrpos($row["sig_name"], "SQL INJECTION")  )
{
    $sql++;
    $blocked++;
}
else if ( strrpos($row["sig_name"], "XSS INJECTION")  )
{
    $xss;
    $blocked++;

}
else if ( strrpos($row["sig_name"], "BLIND SQL INJECTION")  )
{
    $blindsql++;
    $blocked++;

}
else if ( strrpos($row["sig_name"], "LOCAL PATH")  )
{
    $localpath++;
    $blocked++;

}
else {
    $nonblocked++;
}



}
}

?>


            
        <div class="col-lg-12">

<div id="container" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>

        </div>
    </div>
    <!-- /.row -->
    <div class="row">
        <div class="col-lg-12">
<div id="container2" style="min-width: 310px; height: 400px; max-width: 600px; margin: 0 auto"></div>


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

<script>





<?php

echo "Highcharts.chart('container2', {
    chart: {
        type: 'column'
    },
    title: {
        text: 'Traffic Chart'
    },
    xAxis: {
        categories: ['NON BLOCKED', 'BLOCKED']
    },
    yAxis: {
        min: 0,
        title: {
            text: 'Blocked / Non blocked traffic'
        },
        stackLabels: {
            enabled: true,
            style: {
                fontWeight: 'bold',
                color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
            }
        }
    },
    legend: {
        align: 'right',
        x: -30,
        verticalAlign: 'top',
        y: 25,
        floating: true,
        backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || 'white',
        borderColor: '#CCC',
        borderWidth: 1,
        shadow: false
    },
    tooltip: {
        headerFormat: '<b>{point.x}</b><br/>',
        pointFormat: '{series.name}: {point.y}<br/>Total: {point.stackTotal}'
    },
    plotOptions: {
        column: {
            stacking: 'normal',
            dataLabels: {
                enabled: true,
                color: (Highcharts.theme && Highcharts.theme.dataLabelsColor) || 'white'
            }
        }
    },
    series: [{
        name: 'NON BLOCKED',
        data: [ ".$nonblocked." , 0   ]
    },{
        name: 'SQLI',
        data: [0,".$sql."  ]
    }, {
        name: 'BSQLI', 
        data: [0, ".$blindsql."]
    }, {
        name: 'XSS',
        data: [0, ".$xss."]
    }, {
        name: 'LPI',
        data: [0, ".$localpath."]
    }]
}); \n";





     
    echo "Highcharts.chart('container', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: 'Blocked / Non blocked traffic'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                style: {
                    color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                }
            }
        }
    },
    series: [{
        name: 'Percent',
        colorByPoint: true,
        data: [{
            name: 'BLOCKED',
            y: ".$blocked."
        }, {
            name: 'NON BLOCKED',
            y: ".$nonblocked.",
            sliced: true,
            selected: true,
        }, ]
    }]
});";

?> 
</script>
<script>
    $(document).ready(function(){
      var date_input=$('.datepicker'); //our date input has the name "date"
      var container=$('.bootstrap-iso form').length>0 ? $('.bootstrap-iso form').parent() : "body";
      var options={
        format: 'yyyy-mm-dd',
        container: container,
        todayHighlight: true,
        autoclose: true,
      };
      date_input.datepicker(options);
    });
</script>


<?php include_once('includes/footer.php');


?>
