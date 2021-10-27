<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Police Emergency Service System</title>
</head>

<body>
<?php require 'nav.php';?>
<?php 
	if (isset($_POST["btnDispatch"]))
	{
		require_once 'db_config.php';
		
		$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
		if ($mysqli->connect_errno)
		{
			die("Failed to connct to My SQL: ".$mysqli->connect_errno);
		}
		$patrolcarDispatched = $_POST["chkPatrolcar"];
		$numOfPatrolcarDispatched = count($patrolcarDispatched);
$incidentStatus;
if ($numOfPatrolcarDispatched > 0 ) {
    $incidentStatus='2';
} else {
    $incidentStatus='1';
}

$sql = "INSERT INTO incident (callerName, phoneNumber, incidentTypeId, incidentLocation, incidentDesc, incidentStatusId) VALUES (?, ? ,? ,? ,? ,?)";

if (!($stmt = $mysqli->prepare($sql)))
{
    die("Prepare failed: ".$mysqli->errno);
}

if (!$stmt->bind_param('ssssss', $_POST['callerName'],
                    $_POST['contactNo'],
                    $_POST['incidentType'],
                    $_POST['location'],
                    $_POST['incidentDesc'],
                    $incidentStatus))
{
	die("Binding parameters failed: ".$stmt0>errno);
}
		if(!$stmt->execute())
		{
			die("Insert incident table failed ".$stmt->errno);
}
		$incidentId=mysqli_insert_id($mysqli);
		
		for($i=0; $i < $numOfPatrolcarDispatched; $i++)
		{
			$sql = "UPDATE patrolcar SET patrolcarStatusId ='1' WHERE patrolcarId = ?";
			
			if (!($stmt = $mysqli->prepare($sql))) {
				die("Prepare failed: ".$mysqli->errno);
			}
if (!$stmt->bind_param('s', $patrolcarDispatched[$i])){
    die("Binding paramters failed ".$stmt->errno);
}

if (!$stmt->execute()) {
    die("Update patrolcar_status table failed: ".$stmt->errno);
}

$sql = "INSERT INTO dispatch (incidentId, patrolcarId, timeDispatched) VALUES (?, ?, NOW())";

if (!($stmt = $mysqli->prepare($sql))) {
    die("Prepare failed: ".$mysqli->errno);
}

if (!$stmt->bind_param('ss', $incidentId, $patrolcarDispatched[$i])){
    die("Binding parameter failed: ".$stmt->errno);
}

if (!$stmt->execute()) {
    die("Insert dispatch: ".$stmt->errno);
}	
		}
$stmt->close();
$mysqli->close();
	}?>
<form name="form1" method="post" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
  <table>
    <tr>
      <td colspan="2">Incident Detail</td>
    </tr>
    <tr>
      <td>callerName :</td>
      <td><?php echo $_POST['callerName'] ?>
        <input type="hidden" name="callerName" id="callerName" value="<?php echo $_POST['callerName'] ?>" /></td>
    </tr>
    <tr>
      <td>contactNo :</td>
      <td><?php echo $_POST['contactNo'] ?>
        <input type="hidden" name="contactNo" id="contactNo" value="<?php echo $_POST['contactNo'] ?>" /></td>
    </tr>
    <tr>
      <td>Location :</td>
      <td><?php echo $_POST['location'] ?>
        <input type="hidden" name="location" id="location" value="<?php echo $_POST['location'] ?>" /></td>
    </tr>
    <tr>
      <td>incidentType :</td>
      <td><?php echo $_POST['incidentType'] ?>
        <input type="hidden" name="incidentType" id="incidentType" value="<?php echo $_POST['incidentType'] ?>" /></td>
    </tr>
    <tr>
      <td>descripton :</td>
      <td><textarea name="incidentDesc" cols="45"
    rows="5" readonly id="incidentDesc"><?php echo $_POST['incidentDesc'] ?></textarea>
        <input name="incidentDesc" type="hidden"
    id="incidentDesc" value="<?php echo $_POST['incidentDesc'] ?>"></td>
      </td>
    </tr>
  </table>
  <?php
	require_once 'db_config.php';
		  
	$mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
	if ($mysqli->connect_errno) {
		die("Failed to connect to MySQL: ".$mysqli->connect_errno);
	}
		  $sql = "SELECT patrolcarId, statusDesc FROM patrolcar JOIN patrolcar_status ON patrolcar.patrolcarStatusId=patrolcar_status.StatusId
		  WHERE patrolcar.patrolcarStatusId='2' OR patrolcar.patrolcarStatusId='3'";
		  if (!($stmt = $mysqli->prepare($sql))) {
    die("Prepare failed: ".$mysqli->errno);
}
if (!$stmt->execute()) {
    die("Execute failed: ".$stmt->errno);
}
if (!($resultset = $stmt->get_result())) {
    die("Getting result set failed: ".$stmt->errno);
}

$patrolcarArray;

while ($row = $resultset->fetch_assoc()) {
    $patrolcarArray[$row['patrolcarId']] = $row['statusDesc'];
}


$stmt->close();
$resultset->close();
$mysqli->close();
?>
  <br>
  <br>
  <table border="1" align="center">
    <tr>
      <td colspan="3">Dispatch Patrolcar Panel</td>
    </tr>
    <?php
	foreach($patrolcarArray as $key=>$value){
		?>
    <tr>
      <td><input type="checkbox" name="chkPatrolcar[]"
		value="<?php echo $key?>"></td>
      <td><?php echo $key ?></td>
      <td><?php echo $value ?></td>
    </tr>
    <?php } ?>
    <tr>
      <td><input type="reset" name="btnCancel" id="btnCancel" value="Reset"></td>
      <td colspan="2">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="submit" name="btnDispatch" id="btnDispatch" value="Dispatch"></td>
    </tr>
  </table>
  </table>
</form>
</body>
</html>
