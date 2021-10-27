<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Untitled Document</title>
</head>

<body>
<?php require 'nav.php';?>
<?php require 'db_config.php';?>
<?php
if ( !isset( $_POST[ "btnSearch" ] ) ) {
  ?>
<!-- reate a form to search for patrol car based on id -->
<form name="form1" method="post" action="<?php echo
htmlentities($_SERVER['PHP_SELF']); ?> ">
  <table width="80%" border="0" align="center" cellpadding="4"
cellspacing="4">
    <tr>
      <td width="25%" class="td_label">Patrol Car ID :</td>
      <td width="25%" class="td_Data"><input type="text"
name="patrolCarId" id="patrolCarId"></td>
      <!-- must validate for no empty entry at the Client side, HOW??? -->
      <td class="td_Data"><input type="submit" name="btnSearch"
id="btnSearch" value="Search"></td>
    </tr>
  </table>
</form>
<?php
} else { // post back here after clicking the btnSearch button
  // create database connection
  $mysqli = mysqli_connect( DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE );
  // Check connection
  if ( $mysqli->connect_errno ) {
    die( "Failed to connect to MySQL: " . $mysqli->connect_errno );
  }
  // retrieve patrol car detail
  $sql = "SELECT * FROM patrolcar WHERE patrolCarId = ?";
  if ( !( $stmt = $mysqli->prepare( $sql ) ) ) {
    die( "Prepare failed: " . $mysqli->errno );
  }
  if ( !$stmt->bind_param( 's', $_POST[ 'patrolCarId' ] ) ) {
    die( "Binding parameters failed: " . $stmt->errno );
  }
  if ( !$stmt->execute() ) {
    die( "Execute failed failed: " . $stmt->errno );
  }
  if ( !( $resultset = $stmt->get_result() ) ) {
    die( "Getting result set failed: " . $stmt->errno );
  }
  // if the patrol car does not exist, redirect back to update.php
  if ( $resultset->num_rows == 0 ) {
    ?>
<script type="text/javascript">window.location="./update.php";</script>
<?php
}
// else if the patrol car found
$patrolCarId;
$patrolCarStatusId;
while ( $row = $resultset->fetch_assoc() ) {
  $patrolCarId = $row[ 'patrolcarId' ];
  $patrolCarStatusId = $row[ 'patrolcarStatusId' ];
}
// retrieve from patrolcar_status table for populating the combo box
$sql = "SELECT * FROM patrolcar_status";
if ( !( $stmt = $mysqli->prepare( $sql ) ) ) {
  die( "Prepare failed: " . $mysqli->errno );
}
if ( !$stmt->execute() ) {
  die( "Execute failed: " . $stmt->errno );
}
if ( !( $resultset = $stmt->get_result() ) ) {
  die( "Getting result set failed: " . $stmt->errno );
}
$patrolCarStatusArray;; // an array variable
while ( $row = $resultset->fetch_assoc() ) {
  $patrolCarStatusArray[ $row[ 'statusId' ] ] = $row[ 'statusDesc' ];
}
$stmt->close();
$resultset->close();
$mysqli->close();
?>
<!-- display a form for operator to update status of patrol car -->
<form name="form2" method="post"
action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?> ">
  <table class="ContentStyle">
    <tr></tr>
    <tr>
      <td>ID :</td>
      <td><?php echo $patrolCarId ?>
        <input type="hidden" name="patrolCarId" id="patrolCarId"
value="<?php echo $patrolCarId ?>"></td>
    </tr>
    <tr>
      <td>Status :</td>
      <td><select name="patrolCarStatus" id="patrolCarStatus">
          <?php foreach( $patrolCarStatusArray as $key => $value){ ?>
          <option value="<?php echo $key ?>"
<?php if ($key==$patrolCarStatusId) {?>
 selected="selected"
<?php }?>
> <?php echo $value ?> </option>
          <?php } ?>
        </select></td>
    </tr>
    <tr>
      <td><input type="reset"
name="btnCancel" id="btnCancel" value="Reset"></td>
      <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input
type="submit" name="btnUpdate" id="btnUpdate"
 value="Update"></td>
    </tr>
  </table>
</form>
<?php } ?>
<?php
if ( isset( $_POST[ "btnUpdate" ] ) ) {
  // create database connection
  $mysqli = mysqli_connect( DB_SERVER, DB_USER, DB_PASSWORD,
    DB_DATABASE );
  // Check connection
  if ( $mysqli->connect_errno ) {
    die( "Failed to connect to MySQL: " . $mysqli->connect_errno );
  }
  // update patrol car status
  $sql = "UPDATE patrolcar SET patrolcarStatusId = ? WHERE patrolcarId = ? ";
  if ( !( $stmt = $mysqli->prepare( $sql ) ) ) {
    die( "Prepare failed: " . $mysqli->errno );
  }
  if ( !$stmt->bind_param( 'ss', $_POST[ 'patrolCarStatus' ], $_POST[ 'patrolCarId' ] ) ) {
    die( "Binding parameters failed: " . $stmt->errno );
  }
  if ( !$stmt->execute() ) {
    die( "Update patrolcar table failed: " . $stmt->errno );
  }
  // if patrol car status is Arrived (4) then capture the time of arrival
  if ( $_POST[ "patrolCarStatus" ] == '4' ) {
    $sql = "UPDATE dispatch SET timeArrived = NOW()
WHERE timeArrived is NULL AND patrolcarId = ?";
    if ( !( $stmt = $mysqli->prepare( $sql ) ) ) {
      die( "Prepare failed: " . $mysqli->errno );
    }
    if ( !$stmt->bind_param( 's', $_POST[ 'patrolCarId' ] ) ) {
      die( "Binding parameters failed: " . $stmt->errno );
    }
    if ( !$stmt->execute() ) {
      die( "Update dispatch table failed: " . $stmt->errno );
    }
  } else if ( $_POST[ "patrolCarStatus" ] == '3' ) { // else if patrol car status is FREE (3)then capture the time of completion
    // First, retrieve the incident ID from dispatch table handled by that patrol car
    $sql = "SELECT incidentId FROM dispatch WHERE timeCompleted IS
 NULL AND patrolcarId = ?";
    if ( !( $stmt = $mysqli->prepare( $sql ) ) ) {
      die( "Prepare failed: " . $mysqli->errno );
    }
    if ( !$stmt->bind_param( 's', $_POST[ 'patrolCarId' ] ) ) {
      die( "Binding parameters failed: " . $stmt->errno );
    }
    if ( !$stmt->execute() ) {
      die( "Execute failed failed: " . $stmt->errno );
    }
    if ( !( $resultset = $stmt->get_result() ) ) {
      die( "Getting result set failed: " . $stmt->errno );
    }
    $incidentId;
    while ( $row = $resultset->fetch_assoc() ) {
      $incidentId = $row[ 'incidentId' ];
    }
    // next update dispatch table
    $sql = "UPDATE dispatch SET timeCompleted = NOW()
WHERE timeCompleted is NULL AND patrolcarId = ?";
    if ( !( $stmt = $mysqli->prepare( $sql ) ) ) {
      die( "Prepare failed: " . $mysqli->errno );
    }
    if ( !$stmt->bind_param( 's', $_POST[ 'patrolCarId' ] ) ) {
      die( "Binding parameters failed: " . $stmt->errno );
    }
    if ( !$stmt->execute() ) {
      die( "Update dispatch table failed: " . $stmt->errno );
    }
    // last but not least, update incident table to completed (3) all patrol car attended to it are FREE now
    $sql = "UPDATE incident SET incidentStatusId = '3' WHERE incidentId =
 '$incidentId' AND NOT EXISTS (SELECT * FROM dispatch
 WHERE timeCompleted IS NULL AND incidentId = '$incidentId')";
    if ( !( $stmt = $mysqli->prepare( $sql ) ) ) {
      die( "Prepare failed 11: " . $mysqli->errno );
    }
    if ( !$stmt->execute() ) {
      die( "Update dispatch table failed: " . $stmt->errno );
    }
    $resultset->close();
  }
  $stmt->close();
  $mysqli->close();
  ?>
<script type="text/javascript">window.location="./logcall.php";</script>
<?php } ?>
</body>
</html>