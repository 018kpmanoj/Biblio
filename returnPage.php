<?php
session_start();
date_default_timezone_set("Asia/Calcutta");

	// Check if the user is logged in, if not then redirect him to login page
	if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
		header("location: login_reader.php");
		exit;
	}
	require 'connect_db.php';
	
	$return_err = "";
	if($_SERVER["REQUEST_METHOD"]=="POST") {
		//check if field is empty
		if(empty(trim($_POST["return_bookID"]))){
			$return_err = "Please enter Book ID";
		} else {
			$return_bookID = $_POST["return_bookID"];
		}
		
		if(empty($return_err)) {
			$userID = $_SESSION["id"];
			$bookID = $return_bookID;
			$action = "Return";
			$date1 =  date("Y-m-d");
			$time1 = date("h:i:s");
			$prev_status = "";
			//query to check availability
			$status_Que = "SELECT availability FROM book WHERE book_ID='".$bookID."'";
			$result = $link->query($status_Que);
			$row = mysqli_num_rows($result); 
			if($row==1) {
				while($data = $result->fetch_assoc()) {
					$prev_status = $data['availability'];
				}
			} else {
				echo "Invalid Book ID";
				header("Location: returnPage.php");
				exit;
			}
			//query to delete row from BORROWS
			$delete_que = "DELETE FROM borrowed WHERE book_ID='".$bookID."'";
			
			//query to update BOOK table. To change availability as per the requirement
			if($prev_status == 'Borrowed') {
				$update_que = "UPDATE book SET availability = 'Available' WHERE book_ID='".$bookID."' AND availability = 'Borrowed'";
			} else if($prev_status == 'Reserved') {
				$update_que = "UPDATE book SET availability = 'Available_Reserved' WHERE book_ID='".$bookID."' AND availability = 'Reserved'";
			} else {
				echo "<script>alert('This book is not issued yet'); window.location='returnPage.php'</script>";
			}
			
			//query to insert row into REPORT
			$insert_reportQue = "INSERT INTO report(reader_ID, book_ID, action, date, time) VALUES('".$userID."','".$bookID."','".$action."','".$date1."','".$time1."')";
			
			//queries execution
			//inserting into REPORT table
			if ($link->query($insert_reportQue) === TRUE) {
				//echo "New record created successfully";
				//we do nothing here
			} else {
				echo "Error: " . $insert_reportQue . "<br>" . $link->error;
			}
			
			//updating the BOOK table
			if ($link->query($update_que) === TRUE) {
				//echo "Update successfull";
				//we do nothing here
			} else {
				echo "Error: " . $update_que . "<br>" . $link->error;
			}
			
			//deleting from BORROWED table
			if ($link->query($delete_que) === TRUE) {
				//echo "Deletion successfull";
				//we do nothing here
			} else {
				echo "Error: " . $delete_que . "<br>" . $link->error;
			}
			
			echo "<script>alert('Book returned successfully');document.location='welcome_reader.php'</script>";
		}
	}
?>
<html>
<head>
<title> Return books </title>
</head>
<body>
	<p>
	<?php
		if(!empty($return_err)){
            echo $login_err;
        }
	?>
	</p>
	<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
	<label>Enter the Book ID : 
	<input type="text" name="return_bookID" required/>
	</label>
	<br/><br/>
	<input type="submit" name="return_button" value="Return"/>
	<p>
		<a href="welcome_reader.php">Back</a>
	</p>
</body>
</html>