<?php

require_once('config.inc.php');

function run_git($command, $repo, $bare=false)
{
	$repo = str_replace('/', '', $repo);
	$repo = str_replace('\\', '', $repo);
	if (!$bare)
	  $repo.='/.git';	
		
	$output = array();
	$ret = 0;
	$c = GIT_EXEC.' --git-dir='.ROOT_DIR.$repo.' '.$command;
	exec($c, $output, $ret);
	if ($ret != 0) { 
		//debug
		echo($c);
		die('git failed, is git path correct?'); 
	}
	return $output;
}

function git_get_log($repo, $limit = 10, $bare=false)
{
	$format = array('%H', '%at', '%an', '%s');
	$params = implode(DELIMETER, $format);
	$data = run_git('log --pretty=format:"'.$params.'" -'.$limit, $repo, $bare);	
	$result = array();
	foreach($data as $line)
	{
		$columns = explode(DELIMETER, $line);
		$row = array(
			'commit' => $columns[0],
			'timestamp' => $columns[1],
			'author' => $columns[2],
			'message' => $columns[3],
		);
		$result[] = $row;
	}
	return $result;
}
?>
