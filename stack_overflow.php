<?php
require_once('scrape.class.php');

$ins = 0;
$red = 0;
$rem = 0;
$exi = 0;

//change starting point as wished (in case it's interrupted/blocked)
for($j=999;$j<2000;$j++) {

	echo "\nstarting iteration $j\n";

	for($i=$j*1000;$i<($j+1)*1000;$i++) {

		if(ques_ans_exist($i)) {
			echo "\nques_ans exist: $i";		
			$exi++;
			continue;	
		}

		$Question = new Scrape('https://stackoverflow.com/questions/'.$i);

		$not_found = (isset($Question->xPathObj->query('/html/head/title')->item(0)->nodeValue) ? $Question->xPathObj->query('/html/head/title')->item(0)->nodeValue : '');
		if($not_found === 'Page not found - Stack Overflow') {
			echo "\nquestion removed: $i";
			$rem++;
    	    continue;
		}

		if($Question->source === false) {
			echo "\nfound redirect: $i";
			$red++;
			continue;
		}

		$Ques = new stdClass();
		$Ques->id = $Question->xPathObj->query('//*[@id="question"]/@data-questionid')->item(0)->nodeValue; 
		if(!isset($Ques->id) || empty($Ques->id)) {
			echo "\nquestion no ID: $i";
			$rem++;
			continue;
		}

		$Ques->title = $Question->xPathObj->query('//div[@id="question-header"]/h1/a')->item(0)->nodeValue;
		$Ques->answers_to = NULL;
		$Ques->content = $Question->xPathObj->query('//div[@id="question"]/div[@class="post-layout"]/div[@class="postcell post-layout--right"]/div[@class="post-text"]')->item(0)->nodeValue;
		$Ques->votes = $Question->xPathObj->query('//*[@id="question"]/div[2]/div[1]/div/div')->item(0)->nodeValue;
		$tags = $Question->xPathObj->query('//div[@id="question"]/div[@class="post-layout"]/div[@class="postcell post-layout--right"]/div[@class="post-taglist grid gs4 gsy fd-column"]/div/a');
		$Ques->tags = array();
		foreach($tags as $tag) {
			$q_tags[] = $tag->nodeValue;
		}
		$Ques->tags = json_encode($q_tags);

		if(ques_ans_exist($Ques->id)) {
    	    $exi++;
        	echo "\nques_ans exist: $id";
	        continue;
    	}

		if(insert_to_db($Ques)) {
			$ins++;
			echo "\nquestion inserted: $i";
		}

		$Ans = array();
		$answers = $Question->xPathObj->query('//*[@id="answers"]/div[contains(@class, "answer")]');	

		foreach($answers as $k => $ans) {
			$id = $ans->getAttribute('data-answerid');
			if(empty($id)) continue;
			if(ques_ans_exist($id)) {
				$exi++;
    	    	echo "\nques_ans exist: $id";
    		    continue;
		    }
			$Answer = new stdClass();
			$Answer->id = $id;
			$Answer->title = NULL;
			$Answer->content = NULL;
			$Answer->answers_to = $Ques->id;
			$content = $Question->xPathObj->query('//div[@id="answer-'.$id.'"]/div/div[2]/div[1]');
			foreach($content as $cont) {
				$Answer->content .= $cont->nodeValue;
			}
			$Answer->votes = $Question->xPathObj->query('//div[@id="answer-'.$id.'"]/div/div[1]/div/div[1]')->item(0)->nodeValue;		
			$Answer->tags = NULL;
			$Ans[$k] = $Answer;

			if(insert_to_db($Answer)) {
				$ins++;
        		echo "\nanswer for Q $i inserted: $id";
	    	}
		}

		echo "\nsleeping a bit...";
		sleep(rand(9,15));

	}

	echo "\n----\nDONE\n---\nalready exist: $exi\nremoved: $rem\ninserted: $ins\nredirected: $red\n";
	echo "\n\nfinished iteration $j, now sleeping a lot...\n";
	sleep(rand(600,900));

}

function insert_to_db($Ques_Ans) {

	$con=mysqli_connect("localhost","root",'$hinobi83',"stackoverflow");

	$Ques_Ans =  (array)$Ques_Ans;

	$insert = array(
				'id' => (int) $Ques_Ans['id'],
				'title' => (!empty($Ques_Ans['title']) ? mysqli_real_escape_string($con, $Ques_Ans['title']) : ''),
				'answers_to' => (!empty($Ques_Ans['answers_to']) ? $Ques_Ans['answers_to'] : 0),
				'content' => mysqli_real_escape_string($con, $Ques_Ans['content']),
				'votes' => $Ques_Ans['votes'],
				'tags' => (!empty($Ques_Ans['tags']) ? json_encode($Ques_Ans['tags']) : '')
			);

	if (mysqli_connect_errno())
	{
		return false;
	}

	$query = "INSERT INTO ques_ans (id,title,answers_to,content,votes,tags) 
			  VALUES (".$insert['id'].",'".$insert['title']."',".$insert['answers_to'].",
			  '".$insert['content']."','".$insert['votes']."','".$insert['tags']."')";

	if (!mysqli_query($con, $query)) {
		$resp = false;
	} else $resp = true;

	if(mysqli_affected_rows($con) > 0) $resp = true;
	else $resp = false;

	mysqli_close($con);

	return $resp;

}

function ques_ans_exist($id) {

	$con=mysqli_connect("localhost","root",'$hinobi83',"stackoverflow");
	if (mysqli_connect_errno())
    {
        $resp = "Failed to connect to MySQL: " . mysqli_connect_error();
    }
	$query = "SELECT id FROM ques_ans WHERE id = ".$id;
	if ($result=mysqli_query($con,$query))
	{
  		$rowcount=mysqli_num_rows($result);
 		mysqli_free_result($result);
  	}

	mysqli_close($con);

	if($rowcount > 0) {
		return true;
	} else return false;

}

?>

