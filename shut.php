<?php

if (!defined("WHMCS"))
        die("This file cannot be accessed directly");

function curl_call($myvars)
{
   $ch = curl_init();
   curl_setopt($ch, CURLOPT_URL, $myvars);
   curl_setopt($ch, CURLOPT_HEADER, 0);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
   $response = curl_exec($ch);
   curl_close($ch);
   $output = json_decode($response,true);
   return $output;
}

function curl_post($url,$fields)
{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        $fp = fopen('/var/www/html/includes/hooks/errorlog.txt', 'a');
        fwrite($fp,"request: ".date("F j, Y, g:i a")."-> ".http_build_query($fields)."\n"."response: ".$response);
        fclose($fp);
        return ($response);
}

function endsWith($string, $endString)
{
    $len = strlen($endString);
    if ($len == 0) {
        return false;
    }
    return (substr($string, -$len) === $endString);
}

function shutPort($vars)
{
    $serverAddress = "http://172.17.7.211:8453";
    $apiKey = '4bdfccdf6777c85ca2a9594958c90abc';
    $swList = array("172.30.3.132"=>"1804","172.30.3.133"=>"1806","172.30.3.134"=>"1808",
                        "172.30.3.131"=>"1801","172.30.2.121"=>"1501","172.30.2.122"=>"1504",
                        "172.25.0.20"=>"4","172.25.0.26"=>"1","172.25.0.27"=>"19","172.25.0.29"=>"301","172.30.3.135"=>"1810","172.25.0.24"=>"901","172.25.0.30"=>"903","172.25.0.28"=>"1201","172.30.3.136"=>"2101" );
    
    $IP="79.175.174.53";
    $SERVERNAME="AF-SHD-WHM01";
    $KEY="whmcscloud";
    $PSKFILE="/etc/ssl/certs/zabbix/zabbix_agentd.psk";
    $swMsg="Error in getting MAAS switch port list ";
    $portMsg="Error in disabling port ";
    if ($vars['params']['moduletype'] == "dedicated") {
        foreach ($swList as $name=>$value)
        {
                $serverName = $vars['params']['domain'];
                $url = $serverAddress.'/api/json/device/getInterfaces?';
                $myvars = $url . '&name=' . $name . '&apiKey=' . $apiKey;
                $output = curl_call($myvars);
		if (array_key_exists('error',$output)) {
		        shell_exec("zabbix_sender -z $IP -s '$SERVERNAME' -k $KEY -o '$swMsg$name' --tls-connect psk --tls-psk-identity '$SERVERNAME' --tls-psk-file $PSKFILE");
			break;
		}
                $portNumber = array();
                foreach ($output['interfaces'] as $interfaceName) {
                   if (endsWith($interfaceName['displayName'],$serverName) == true) {
                      $fp = fopen('/var/www/html/includes/hooks/ifacename.txt', 'a');
                      fwrite($fp,"before replace: ".date("F j, Y, g:i a")."-> serverName: ".$serverName."/interfaceName: ".$interfaceName['displayName']."\n");
                      array_push($portNumber,preg_replace("/-$serverName(.*)$/","",$interfaceName['displayName']));
                      //array_push($portNumber,(str_replace("-".$serverName,"",$interfaceName['displayName'])));
                      fwrite($fp,"after replace: ".date("F j, Y, g:i a")."-> ".var_export($portNumber, true)."\n\n");
                   }
                }
                fclose($fp);
		#logModuleCall('npshut', "",$portNumber, "", "", array());
                foreach ($portNumber as $port) {
	                $url = $serverAddress . '/api/json/ncmsettings/execConfiglet';
                	$apiKey = '4bdfccdf6777c85ca2a9594958c90abc';
                	$VAR_NAME = '["INTERFACE"]';
        	        $selectedDevices = '["'.$value.'"]';
	                $TEMPLATE_ID = "601";
                        $VARIABLES = '{"INTERFACE":"'.$port.'"}';
			$fields = array("apiKey" => $apiKey,"VAR_NAME" => $VAR_NAME,"SELECTEDDEVICES" => $selectedDevices
					,"TEMPLATE_ID" => $TEMPLATE_ID,"VARIABLES" => $VARIABLES, "BACKUP_ENABLED" => "false"
					, "ComponentSelection" => "SpecificDevice");
			$output = curl_post($url,$fields);
                        
			#logModuleCall('nocpsshut', "",$output, "", "", array());
		        if (array_key_exists('error',$output) or intval($output['isSuccess']) != "true") {
                        	shell_exec("zabbix_sender -z $IP -s '$SERVERNAME' -k $KEY -o '$portMsg$serverName$port' --tls-connect psk --tls-psk-identity '$SERVERNAME' --tls-psk-file $PSKFILE");
         	         }
                        sleep(15);
                }
        }
   }
}
function noshutPort($vars)
{
    $serverAddress = "http://172.17.7.211:8453";
    $apiKey = '4bdfccdf6777c85ca2a9594958c90abc';
    $swList = array("172.30.3.132"=>"1804","172.30.3.133"=>"1806","172.30.3.134"=>"1808",
                        "172.30.3.131"=>"1801","172.30.2.121"=>"1501","172.30.2.122"=>"1504",
                        "172.25.0.20"=>"4","172.25.0.26"=>"1","172.25.0.27"=>"19","172.25.0.29"=>"301","172.30.3.135"=>"1810","172.25.0.24"=>"901","172.25.0.30"=>"903","172.25.0.28"=>"1201","172.30.3.136"=>"2101");
    $IP="79.175.174.53";
    $SERVERNAME="AF-SHD-WHM01";
    $KEY="whmcscloud";
    $PSKFILE="/etc/ssl/certs/zabbix/zabbix_agentd.psk";
    $swMsg="Error in getting MAAS switch port list ";
    $portMsg="Error in disabling port ";
    if ($vars['params']['moduletype'] == "dedicated") {
        foreach ($swList as $name=>$value)
        {
                $serverName = $vars['params']['domain'];
                $url = $serverAddress.'/api/json/device/getInterfaces?';
                $myvars = $url . '&name=' . $name . '&apiKey=' . $apiKey;
                $output = curl_call($myvars);
                if (array_key_exists('error',$output)) {
                        shell_exec("zabbix_sender -z $IP -s '$SERVERNAME' -k $KEY -o '$swMsg$name' --tls-connect psk --tls-psk-identity '$SERVERNAME' --tls-psk-file $PSKFILE");
                        break;
                }
                $portNumber = array();
                foreach ($output['interfaces'] as $interfaceName) {
                   if (endsWith($interfaceName['displayName'],$serverName) == true) {
                      array_push($portNumber,preg_replace("/-$serverName(.*)$/","",$interfaceName['displayName']));
                      //array_push($portNumber,(str_replace("-".$serverName,"",$interfaceName['displayName'])));
                   }
                }
                foreach ($portNumber as $port) {
                        $url = $serverAddress . '/api/json/ncmsettings/execConfiglet';
                        $apiKey = '';
                        $VAR_NAME = '["INTERFACE"]';
                        $selectedDevices = '["'.$value.'"]';
                        $TEMPLATE_ID = "602";
                        $VARIABLES = '{"INTERFACE":"'.$port.'"}';
                        $fields = array("apiKey" => $apiKey,"VAR_NAME" => $VAR_NAME,"SELECTEDDEVICES" => $selectedDevices
                                        ,"TEMPLATE_ID" => $TEMPLATE_ID,"VARIABLES" => $VARIABLES, "BACKUP_ENABLED" => "false"
                                        , "ComponentSelection" => "SpecificDevice");
                        $output = curl_post($url,$fields);
                        #logModuleCall('nocpsshut', "",$output, "", "", array());
                        if (array_key_exists('error',$output) or intval($output['isSuccess']) != "true") {
                                shell_exec("zabbix_sender -z $IP -s '$SERVERNAME' -k $KEY -o '$portMsg$serverName$port' --tls-connect psk --tls-psk-identity '$SERVERNAME' --tls-psk-file $PSKFILE");
                         }
                        sleep(15);
                }
        }
   }
}
add_hook('PreModuleSuspend', 1, 'shutPort');
add_hook('PreModuleUnsuspend', 1, 'noshutPort');
//add_hook('AfterModuleCreate', 1, 'noshutPort');
