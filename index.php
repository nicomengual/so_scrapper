<?php

?>

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<?php
	$con = new mysqli('localhost','dbuser','dbpassword','stackoverflow');
    
	if ($con->connect_error)
    {
        $resp = "Failed to connect to MySQL: " . mysqli_connect_error();
    }

	$sql = "SELECT * FROM ques_ans WHERE 1";
	$result = $con->query($sql);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			echo "<ul>";
			foreach($row as $key => $val) {
				echo "<li>".$key.": ";
				if($key == 'content') { 
					echo "<code>".nl2br(htmlspecialchars($val))."</code>";
				} elseif($key == 'tags' && !empty($val)) {
					$val = trim($val,'"');
					$tags = json_decode($val, TRUE);
					foreach($tags as $t) {
						echo '<span class="badge badge-secondary mr-2">'.$t.'</span>';
					}
				}else echo htmlspecialchars($val);
				echo "</li>";
			}
			echo "</ul>";
			echo "<hr>";
    	}
	} else {
		echo "0 results";
	}
	
	$con->close();

