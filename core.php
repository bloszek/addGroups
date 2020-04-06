<?php

    

	date_default_timezone_set('Europe/Warsaw');
	ini_set('default_charset', 'UTF-8');
	setlocale(LC_ALL, 'UTF-8');
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	define('INTERVAL', 1);
	define('VER', '1.0');

	require_once '/include/classes/helper.class.php';
	require_once '/include/classes/ts3admin.class.php';

	$cache = [];
	$helper = new helper('config.php');

	if(property_exists($helper, 'cfg'))
	{
		$ts = new ts3admin($helper->cfg['teamspeak']['host'], $helper->cfg['teamspeak']['queryPort']);
		if($helper->success($ts->connect()))
		{
			$helper->printConsole('Poprawnie połączono z serwerem!');
			if($helper->success($ts->login($helper->cfg['teamspeak']['queryLogin'], $helper->cfg['teamspeak']['queryPass'])))
			{
				$helper->printConsole('Poprawnie zalogowano do serwera!');
				if($helper->success($ts->selectServer($helper->cfg['teamspeak']['serverPort'], 'port', false, $helper->cfg['teamspeak']['botName'])))
				{
					$helper->printConsole('Poprawnie wybrano serwer oraz zmienono nazwe!');
                    $tsAdminSocket = $ts->runtime['socket'];
					$helper->sendCommand("servernotifyregister event=textprivate");
					$helper->sendCommand("servernotifyregister event=server");
					if($helper->success($ts->clientMove($ts->getQueryClid(), $helper->cfg['teamspeak']['channel'])))
					{
						$helper->printConsole('Poprawnie zmieniono kanał');
					}
					else
					{
						$helper->printConsole('[ERROR] Nie udało zmienić się kanału');
					}
				}
				else
				{
					$helper->printConsole('[ERROR] Nie udało się wybrać serwera lub zmienić nazwy');
				}
			}
			else
			{
				$helper->printConsole('[ERROR] Nie udało się zalogować do serwera');
			}
		}
		else
		{
			$helper->printConsole('[ERROR] Nie udało się połączyć z serwerem');
		}
		while(1)
		{
        		$socketData = $helper->getData();
        		if(!$socketData)
        		{
        			continue;
        		}
        		if(isset($socketData["notifycliententerview"]) && !empty($socketData["client_database_id"]))
        		{
        			foreach($helper->cfg['commands']['motd'] as $msg)
        			{
        				$ts->sendMessage(1, $socketData['clid'], $helper->replaceMessage($msg, $socketData));
        			}
        		}
        		if(isset($socketData["notifytextmessage"]) && !empty($socketData["invokerid"]))
        		{
           		    $cmd = explode(" ", $socketData['msg']);
                    if(isset($cmd[0]))
                    {
                        $command = $cmd[0];
                    }
           		    $id = $socketData['invokerid'];
            		$clientInfo = $ts->getElement('data', $ts->clientInfo($id));
            		foreach($ts->serverGroupList(1)['data'] as $groups)
            		{
            			if(in_array($groups['sgid'], $helper->cfg['groups']['all']))
            			{
            				$cache['groups'][$groups['sgid']] = $groups['name'];
            			}
            		}
            		$cache['userInGroups'] = [];
            		foreach($helper->cfg['groups']['all'] as $group)
            		{
            			   if($helper->inGroups($clientInfo['client_servergroups'], $group))
                            {
                                $cache['userInGroups'][$id][] = $group;
                            }
            		}
            		switch ($command) {
            			case '!add':
            				if(empty($cmd[1]))
            				{
            					$ts->sendMessage(1, $id, 'Parametr "id" jest pusty!');
            				}
            				else
            				{
            					if(!isset($cache['groups'][$cmd[1]]))
            					{
            						$ts->sendMessage(1, $id, 'Takowa grupa nie istnieje lub nie jest dozwolona do nadania!');
            					}
            					else
            					{
            						if(!$helper->inGroups($clientInfo['client_servergroups'], $cmd[1]) && count($cache['userInGroups'][$id]) >= $helper->cfg['groups']['limit']);
            						{
            							$ts->serverGroupAddClient($cmd[1], $clientInfo['client_database_id']);
            							$ts->sendMessage(1, $id, 'Poprawnie nadano grupe: '. $cache['groups'][$cmd[1]]);
            						}
            						else
            						{
            							$ts->sendMessage(1, $id, 'Posiadasz daną rangę');
            						}
            					}
            				}
            				break;
            			case '!dell':
            				if(empty($cmd[1]))
            				{
            					$ts->sendMessage(1, $id, 'Parametr "id" jest pusty!');
            				}
            				else
            				{
            					if(!isset($cache['groups'][$cmd[1]]))
            					{
            						$ts->sendMessage(1, $id, 'Takowa grupa nie istnieje lub nie jest dozwolona do zabrania!');
            					}
            					else
            					{
            						$ts->serverGroupDeleteClient($cmd[1], $clientInfo['client_database_id']);
            						$ts->sendMessage(1, $id, 'Grupa została zabrana!');
            					}
            				}
            				break;	
            			case '!list':
            				$ts->sendMessage(1, $id, 'Grupy dostępne do nadania: ');
            				$ts->sendMessage(1, $id, 'Id grupy - Nazwa');
            				foreach($cache['groups'] as $sgid => $name)
            				{
            					$ts->sendMessage(1, $id, '[b]'. $sgid.' - '. $name);
            				}
            				break;
            			case '!help':
        					foreach($helper->cfg['commands']['motd'] as $msg)
        					{
        						$ts->sendMessage(1, $socketData['clid'], $helper->replaceMessage($msg, $socketData));
        					}
            				break;
            			default:
            				$ts->sendMessage(1, $id, 'Takowa komenda nie istneje!');
            				usleep(100);
            				break;
            		}
        		}
			usleep(INTERVAL * 1000000);
		}
	}
