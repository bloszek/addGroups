<?php




	class helper
	{
		public $cfg;

		function __construct($file)
		{
			$this->cfg = include_once '/include/'. $file;
		}

		public function success(array $object)
		{
			return $object[__FUNCTION__];
		}

        public function printConsole($text)
        {
            if(strpos('[ERROR]', $text) !== FALSE)
            {
                exit('[ '. date('D-M-Y H:i') .' ][Error] '.$text);
            }
            else
            {
                echo '[ '. date('D-M-Y H:i') .' ] '.$text;
            }
        }

		public function unEscapeText($text)
    	{
        	$escapedChars   = array("\t", "\v", "\r", "\n", "\f", "\s", "\p", "\/");
        	$unEscapedChars = array('', '', '', '', '', ' ', '|', '/');
        	$text           = str_replace($escapedChars, $unEscapedChars, $text);
        	return $text;
    	}

    	public function inGroups($usergroups, $group)
    	{
        	if(in_array($group, explode(',', $usergroups)))
        	{
                return true;
        	}
        	else
       	    {
            	return false;
        	}
   		}

   		public function randomName() 
   		{
        		$names = array(
            		'Martyna',
           		    'Dorota',
           		    'Zuza',
            		'Karolina',
            		'Julka',
            		'Dominika',
            		'Sandra',
            		'Eliza',
            		'Adrianna',
            		'Paulina',
            		'Magda',
            		'Kaja',
            		'Alicja',
            		'Aniela',
            		'Alicja',
            		'Weronika',
            		'Nikola',
                    'Maja',
                    'Oliwia',
                    'Kornelia',
                    'Kasia'
        		);
        		return $names[array_rand($names, 1)];
    		}

    	public function sendCommand($command)
   		{
			global $tsAdminSocket;
			$splittedCommand = str_split($command, 1024);
			$splittedCommand[(count($splittedCommand) - 1)] .= "\n";
        	foreach($splittedCommand as $commandPart) 
        	{
				fputs($tsAdminSocket, $commandPart);
			}
			return fgets($tsAdminSocket, 4096);
		}

		public function getData()
    	{
	
			global $tsAdminSocket;
			$data = fgets($tsAdminSocket, 4096);
			if(!empty($data))
			{
		    	$datasets = explode(' ', $data);
		    	$output = array();
            	foreach($datasets as $dataset)
            	{
			    	$dataset = explode('=', $dataset);
                	if(count($dataset) > 2) 
                	{
                    	for($i = 2; $i < count($dataset); $i++) 
                    	{
						   $dataset[1] .= '='.$dataset[$i];
			        	}
			        	$output[self::unEscapeText($dataset[0])] = self::unEscapeText($dataset[1]);
                	}
                	else
                	{	
                    	if(count($dataset) == 1) 
                    	{
					   	    $output[self::unEscapeText($dataset[0])] = '';
                    	}
                    	else
                    	{
					   		$output[self::unEscapeText($dataset[0])] = self::unEscapeText($dataset[1]);
				    	}
					}
		    	}
		    	return $output;
			}
		}

        public static function replaceMessage($msg, $info)
        {
            return str_replace([
                '[user_nickname]',
                '[assistant_name]'], [$info['client_nickname'], self::randomName()], $msg);
        }

	}