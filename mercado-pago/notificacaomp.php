<?php
require('constantes.php');
require ('./vendor/autoload.php');
include('conexao.php');

 require __DIR__ . '/vendor/autoload.php';
  
  unset($_SESSION['np']);
  
    MercadoPago\SDK::setAccessToken(PROD_TOKEN);

    $payment = MercadoPago\Payment::find_by_id($_GET["id"]);

	$payment->{'status'};
	
	$id = $payment->{'description'};
	$retorno = 'sim';
	$state = 'pay';

$url = 'http://zapcupons.com.br/notificacaomp.php';

$fp=fopen('log.txt', 'a');
$html=$payment->{'status'}.' | '.$payment->{'status_detail'}.' | '.$payment->{'description'}.' | '.$payment->{'transaction_amount'}.' | <proximo> | ';
$write=fwrite($fp,$html);

$px = $payment->{'id'};
$situacao = $payment->{'status'};
$status_detail = $payment->{'status_detail'};
$dados_banco=$payment->{'description'};
$arr = explode(' ',trim($dados_banco));
$pid = $arr[0];


//aprovado($dados_banco!='')&&(
if(($dados_banco!='')&&($situacao=='approved')){

$sql = "UPDATE `order` SET `retorno_automatico` = '$retorno',  `pagmp` = '$situacao',  `status_detail` = '$status_detail', `pedidoid` = '$px', `state` = '$state', `datapag` = NOW() WHERE `order`.`id` = '$pid'";
		if(mysqli_query($con, $sql)){
		    
		    geracup($pid);
	    
	    
	    
	    $sql = "SELECT * FROM `order` WHERE `order`.`id` = '$pid'";
    				$result = mysqli_query($con, $sql);
    				if(mysqli_num_rows($result) > 0){
    				while($row = mysqli_fetch_array($result))  
                     {  
                         $order_id = $row['id'];
    				     $userid = $row['user_id'];    
                     }    
    				$sql2 = "SELECT * FROM `user` WHERE `user`.`id` = '$userid'";
    				$result2 = mysqli_query($con, $sql2);
    				$row2 = mysqli_fetch_array($result2);
                     {
                         $number = "55".$row2['mobile'];
                         $nome = $row2['realname'];
                     }
                     $sql3 = "SELECT * FROM `coupon` WHERE `coupon`.`order_id` = '$pid'";
    				$result3 = mysqli_query($con, $sql3);
    				$row3 = mysqli_fetch_array($result3);
                     {
                         $coupon_id = $row3['id'];
                         $datecreate = $row3['expire_time'];
                     }
                        $sql = "UPDATE `order` SET `send` = '1' WHERE `order`.`id` = '$pid'";
    				if(mysqli_query($con, $sql)){
    				}
    				
            $number = preg_replace("/[^0-9]/", "", $number);
            $data_final = date('d/m/Y', $datecreate);
    
        
  $url = chave;
  $ch = curl_init($url);
  $numero = $number;
  $text = str_replace('\\n', PHP_EOL, 'Olá Sr(a) '.$nome.'! \n ----------- \n O *pedido '.$order_id.'* foi pago com sucesso. ✅ \n ----------- \n Nº do Cupom: *'.$coupon_id.'* \n Válido até: *'.$data_final.'* \n ----------- \n *Visualize os seus Cupons:* 👇🔖 \n https://zapcupons.com.br/cupons \n ----------- ');
  $data = array(
       'sessionName' => "session1",
      'number' => "$numero",
      'text' => "$text"
  );
  
  $body = json_encode($data);
  
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_VERBOSE, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_AUTOREFERER, false);
  curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
  curl_setopt($ch, CURLOPT_HEADER, 0);        
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);        
  curl_setopt($ch, CURLOPT_POST,true);        
  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json; charset=utf-8')); 
  curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

    $retorno = curl_exec($ch);
    curl_close($ch); 
    
	    // send text
	    $agora = date('d/m/Y H:i');
	    $fp1=fopen('send.txt', 'a');
	    $zp = "Retorno: '.$retorno.' --- Número: '.$number.' -- Nome: '.$nome.' -- Ordem: '.$order_id.' -- em: '.$agora.' <proximo> \n";
	    $write1=fwrite($fp1,$zp);
	    fclose($fp1);
    				}else{
    				    echo "Erro na requisição";
    				    exit();
    				} 
             
		}
}

    
//cancelado
if(($dados_banco!='')&&($situacao=='cancelled')){
$state = 'unpay';
$retorno = 'sim';

$sql = "UPDATE `order` SET `retorno_automatico` = '$retorno', `pagmp` = '$situacao', `status_detail` = '$status_detail', `pedidoid` = '$px', `state` = '$state' WHERE `order`.`id` = '$pid'";
				if(mysqli_query($con, $sql)){
				}
}

if(($dados_banco!='')&&($situacao!='cancelled')&&($situacao!='approved')){
    $state = 'unpay';
    $retorno = 'sim';
  $sql = "UPDATE `order` SET `retorno_automatico` = '$retorno', `pagmp` = '$situacao', `status_detail` = '$status_detail', `pedidoid` = '$px', `state` = '$state' WHERE `order`.`id` = '$pid'";
				if(mysqli_query($con, $sql)){
				}  
}

fclose($fp);

    
function geracup($pid){
//GERAÇÃO DE CUPONS
	   require_once('./app.php');
	   //need_manager();
	   
	   //need_auth('order');
	   
	$order = Table::Fetch('order', $pid);
	$user = Table::Fetch('user', $order['user_id']);

// buscando dados da oferta - verificando se ela está ativa para enviar cupons para todos os clientes que compraram
	$team = Table::Fetch('team', $order['team_id']);

	 if($team['now_number'] >= $team['min_number']){  //<!--  A oferta esta ativa  --> 
	  	    
	  	  
		//criando os cupons
		ZCoupon::CheckOrder($order);

		$sql = "select a.id,a.secret,a.order_id,a.nome,b.username,b.email,c.title,c.homepage,c.location,c.address,c.chavesms  from coupon a,user b, partner c where a.partner_id = c.id and a.user_id = b.id and a.order_id = ".$order['id']." and a.team_id = ".$order['team_id'];
		$rs = mysqli_query(DB::$mConnection,$sql);
	
		$achou = false;
		$cont=0;
		while($row = mysqli_fetch_object($rs)){
			$cont++;
			$achou = true;
			$numcupom 	= $row->id;
			$senha 		= $row->secret; 
			$pedido  	= $row->order_id; 
			$email 		= $row->email; 
			$nome 		= $row->nome; 
			$username  	= $row->username; 
			$parceiro  	= $row->title ; 
			$homepage   = $row->homepage  ; 
			$location   = $row->address  ; 
			$complemento = $row->chavesms; 
			$location =  $location. " ".$complemento;

			
			Util::log($_LANG['ordernumber'].": ".$pedido. " - ".$_LANG['couponnumber'].": $numcupom - ".$_LANG['password'].": $senha - ".$_LANG['couponuser'].": $nome");
			$msg.="<br>".$_LANG['ordernumber'].": ".$pedido. " - ".$_LANG['couponnumber'].": $numcupom - ".$_LANG['password'].": $senha - ".$_LANG['couponuser'].": $nome";
			$url = $INI['system']['wwwprefix']; 
			$url.= $url."/pedidos";
		  
		    $parametros = array('oferta' => $team['title'], 'username' => $username, 'numcupom' => $numcupom, 'senha' => $senha,  'utilizador' => $nome ,'parceiro' => $parceiro, 'location' => $location, 'homepage' => $homepage  );
	 
			$request_params = array(
				'http' => array(
					'method'  => 'POST',
					'header'  => implode("\r\n", array(
						'Content-Type: application/x-www-form-urlencoded',
						'Content-Length: ' . strlen(http_build_query($parametros)),
					)),
					'content' => http_build_query($parametros),
				)
			);

			$request  = stream_context_create($request_params);
			$body = file_get_contents($INI["system"]["wwwprefix"]."/templates/envio_cupom.php", false, $request);
	
			if(Util::postemailCliente($body,$INI['system']['sitename']. " - Cupom",$email)){
				//Util::log($pedido. " - Email para o cliente com dados do cupom enviado com sucesso(".$email.")...");
				$msg.="<br>".$_LANG['ajax_sendemailcupom']."(".$email.").";
				
				$sql2 = "update coupon set envioucupom =1 where id = '".$numcupom."'";
				$rs2 = mysqli_query(DB::$mConnection,$sql2);
				if($rs2){
					//Util::log($pedido. " - campo envioucupom atualizado.");
				 }
				 else{
					Util::log($pedido. " - campo envioucupom nao atualizado. $sql2");
				 }
		
			}
			else{
				//Util::log($pedido. " - Erro no envio do email... (".$email.") .");
			    $msg.= "<br>".$_LANG['ajax_sendemailerror']." (".$email.")";
			}
			
		}
}
}

?>
