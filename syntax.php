<?php
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

// include git utils, they should be located on same directory with syntax.php
require_once(dirname(__FILE__).'/git-utils.inc.php');

class syntax_plugin_dokugitviewer extends DokuWiki_Syntax_Plugin {
 
    function getInfo(){
        return array(
            'author' => 'Tuomas Jomppanen',
            'email'  => 'www.jomppanen.com',
            'date'   => '2009-10-25',
            'name'   => 'DokuGitViewer',
            'desc'   => 'Git repository log viewer',
            'url'    => 'http://www.dokuwiki.org/wiki:plugins',
        );
    }
 
    function getType(){
        return 'substition';
    }
 
    function getSort(){
        return 999;
    }
 
    function connectTo($mode) {
      	$this->Lexer->addSpecialPattern('<dokugitviewer:.+?>',$mode,'plugin_dokugitviewer');
    }
 
 
    function handle($match, $state, $pos, &$handler)
	{
		$start = strlen('<dokugitviewer:');
		$end = -1;
		$params = substr($match, $start, $end);
		$params = preg_replace('/\s{2,}/', '', $params);
		$params = preg_replace('/\s[=]/', '=', $params);
		$params = preg_replace('/[=]\s/', '=', $params);

		$return = array();
		foreach(explode(' ', $params) as $param)
		{
			$val = explode('=', $param);
			$return[$val[0]] = $val[1];
		}
        return $return;
    }
 
    function render($mode, &$renderer, $data) {
		$elements = array('ft' => 'features',
						  'bug' => 'bugs');
        if($mode == 'xhtml'){
			if(isset($data['repository']))
			{
				if(isset($data['limit']) && is_numeric($data['limit']))
					$limit = (int)($data['limit']);
				else
					$limit = 10;
				$log = git_get_log($data['repository'], $limit);
				$renderer->doc .= '<ul class="dokugitviewer">';
				foreach($log as $row)
				{
					$renderer->doc .= '<li>';
					$message = $row['message'];
					for($index = 0; $index < strlen($message); $index++)
					{
						$char = $message[$index];
						if($char == '#')
						{
							$space_hack = true;
							$pos = strpos($message, ' ', $index);
							if($pos === FALSE && $index < strlen($message))
							{
								$pos = strlen($message);
								$space_hack = false;
							}
							$word = substr($message, $index+1, $pos-$index);
							foreach(array_keys($elements) as $element)
							{
								$el = '#'.$element;
								if(strstr($word, $element))
								{
									$value = trim(substr($word, strlen($element)));
									if(is_numeric($value))
									{
										$renderer->doc .= $renderer->internallink($data[$elements[$element]].'#'.$word, '#'.$word);			
										break;										
									}

								}
							}

							if($space_hack)
								$renderer->doc .= ' ';
							$index += $pos-$index;

						}
						else
							$renderer->doc .= $char;
					}
					//$renderer->doc .= '<strong>'.$message.'</strong>';
					$renderer->doc .= '<br />';
					$renderer->doc .= $row['author'].' on ';					
					$renderer->doc .= date(DATE_FORMAT,$row['timestamp']);
				
					$renderer->doc .= '</li>';
					
				}
				$renderer->doc .= '</ul>';				
			}
            return true;
        }
        return false;
    }
}
 
?>

