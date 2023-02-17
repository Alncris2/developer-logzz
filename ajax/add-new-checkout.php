<?php 

    require dirname(__FILE__) . "/../includes/config.php";
    session_name(SESSION_NAME);
    session_start();


    $user__id = $_SESSION['UserID'];

    if($_POST['action'] == 'create-checkout') {
        
        $lbls = json_decode($_POST['lbls']);

        $errors = false;
        $checkout_name = addslashes($_POST['nameCheckout']);
        $whatsapp = isset($_POST['whatsapp']) ? addslashes($_POST['whatsapp']) : "";
        $email = isset($_POST['email']) ? addslashes($_POST['email']) : ""; 
        $suportActive = $whatsapp !== "" || $email !== "" ? 1 : 0 ;
        $emailClient = isset($_POST['emailClient']) ? addslashes($_POST['emailClient']) : 0 ;
        $checkout_exclusive = isset($_POST['checkout-exclusive']) ? addslashes($_POST['checkout-exclusive']) : null;

        # NOTIFICAÇÕES 
        $thisMoment = $lbls->lbl1->value == "true" ? 1 : 0;
        $thisWeek = $lbls->lbl2->value == "true" ? 1 : 0;
        $last30M = $lbls->lbl3->value == "true" ? 1 : 0;
        $today = $lbls->lbl4->value == "true" ? 1 : 0;
        $lashHour = $lbls->lbl5->value == "true" ? 1 : 0; 

        $minQuantityLbl1 = $lbls->lbl1->minQtd;
        $minQuantityLbl2 = $lbls->lbl2->minQtd;
        $minQuantityLbl3 = $lbls->lbl3->minQtd;
        $minQuantityLbl4 = $lbls->lbl4->minQtd;
        $minQuantityLbl5 = $lbls->lbl5->minQtd;

        # Cores do contador
        $bgCounter = isset($_POST['bgCounter']) ? $_POST['bgCounter'] : '#c66751';
        $textColor = isset($_POST['textColor']) ? $_POST['textColor']: '#ffffff';
        $textCounter = isset($_POST['textCounter']) ? $_POST['textCounter'] : 'A condição especial terminará em:'; 
        $timer = isset($_POST['timer']) ? $_POST['timer'] : '05:00';
        $haveCounter = isset($_POST['haveCounter']) ? 1 : 0;

        # Cores do Card onde fica a imagem do produto
        $bgBoxColor = isset($_POST['bgBoxColor']) ? $_POST['bgBoxColor'] : '#c8ffe6';
        $bgBoxTextColor = isset($_POST['bgBoxTextColor']) ? $_POST['bgBoxTextColor'] : '#3d4465';

        $arrayImages = [
            'imageTopDesktop' => '',
            'imageSideDesktop' => '',
            'imageTopMobile' => '',
            'imagebottonMobile' => ''
        ];
        
        if(isset($_POST['haveImage'])){

            $arrayOrder = explode(',', $_POST['arrayOrder']);
            $arrayOrderValues = [];
            foreach($arrayOrder as $key => $order){ 
                $arrayOrderValues[$order] = $key;
            }

            // VERIFICAÇÃO CASO TENHA UPDATE EM ALGUMA DAS DUAS IMAGENS
            if(isset($arrayOrderValues['imageTopDesktop'])){
                if(!isset($arrayOrderValues['imageSideDesktop'])){
                    echo json_encode(['status' => 0, 'msg' => "Não é possivel inserir apenas uma imagem desktop.", 'type' => "warning"]);
                    return;
                }
            }
            
            if(isset($arrayOrderValues['imageSideDesktop'])){
                if(!isset($arrayOrderValues['imageTopDesktop'])){
                    echo json_encode(['status' => 0, 'msg' => "Não é possivel inserir apenas uma imagem desktop.", 'type' => "warning"]);
                    return;
                }
            }

            if(isset($arrayOrderValues['imageTopMobile'])){
                if(!isset($arrayOrderValues['imageBottomMobile'])){
                    echo json_encode(['status' => 0, 'msg' => "Não é possivel inserir apenas uma imagem mobile.", 'type' => "warning"]);
                    return;
                }
            }

            if(isset($arrayOrderValues['imageBottomMobile'])){
                if(!isset($arrayOrderValues['imageTopMobile'])){
                    echo json_encode(['status' => 0, 'msg' => "Não é possivel inserir apenas uma imagem mobile.", 'type' => "warning"]);
                    return;
                }
            }
    

            $countfiles = count($_FILES['files']['name']);
            $filetypes = array('jfif', 'png','PNG', 'jpeg', 'jpg');
            
        
            $errorUpload = false;
            for($i=0; $i < $countfiles; $i++){
                if(in_array($_FILES['files']['type'][$i], $filetypes)){
                    echo json_encode(['status' => 0, 'msg' => "As imagems do checkout precisa estar em formato JPEG, PNG ou JFIF.", 'type' => "warning"]);
                    $errorUpload = true;
                }
            }

            $errorImageTopDesktop = false;
            $imageSideDesktop = false;

            for($i=0; $i < $countfiles; $i++){

                $width = getimagesize($_FILES['files']['tmp_name'][$i])[0];
                $height = getimagesize($_FILES['files']['tmp_name'][$i])[1];

                if($arrayOrder[$i] == 'imageTopDesktop'){
                    if($width > 975 || $height > 365){   
                        $errorImageTopDesktop = true;
                    }
                }

                if($arrayOrder[$i] == 'imageSideDesktop'){
                    if($width > 450 || $height > 1350){   
                        $imageSideDesktop = true;
                    }
                }
        
            }
        
            if($errorImageTopDesktop){
                echo json_encode(['status' => 0, 'msg' => "A Imagem de capa do desktop deve ser menor que 975x365px", 'type' => "warning"]);
                return;
            }

            if($imageSideDesktop){
                echo json_encode(['status' => 0, 'msg' => "A Imagem lateral deve ser menor que 450x1350px", 'type' => "warning"]);
                return;
            }

            if($errorUpload) return;

            $arrayFiles = [];
            for($i=0; $i < $countfiles; $i++){
                try {
                    $new_name = uniqid('_') . $_FILES['files']['name'][$i];
                    $dir = '../uploads/imagens/checkout/';
                    $arrayFiles[$i] = $new_name;

                    move_uploaded_file($_FILES['files']['tmp_name'][$i], $dir.$new_name);
                    
                    $arrayImages[$arrayOrder[$i]] = $new_name;
                
                } catch (\Throwable $th) {
                    echo json_encode(['status' => 0, 'msg' => "Não deu! Erro ao fazer upload da imagem do checkout!", 'type' => "warning"]);
                    return;
                }   
            }    
        }


        $verify_name = $conn->prepare("SELECT name_checkout FROM custom_checkout AS cc WHERE cc.name_checkout = :name_checkout AND cc.user__id = :user__id");
        $verify_name->execute(['name_checkout' => $checkout_name, 'user__id' => $user__id]);

        
        if($verify_name->rowCount() > 0){
            echo json_encode(['status' => 0, 'msg' => "Você já possui um checkout com esse nome, insira outro nome para prosseguir.", 'type' => "warning"]);
            return;
        }

        $insert_custom_checkout = $conn->prepare("INSERT INTO custom_checkout (name_checkout, user__id, checkout_id_product, counter_active, support_active, support_whatsapp, support_email, request_email_client, checkout_bg_counter, checkout_text_counter, checkout_box_color, checkout_box_text_color, checkout_counter_lblText, checkout_not1, checkout_not2, checkout_not3, checkout_not4, checkout_not5, checkout_min_quantity1, checkout_min_quantity2, checkout_min_quantity3, checkout_min_quantity4, checkout_min_quantity5, time_counter, imageTopDesktop, imageSideDesktop, imageTopMobile, imagebottonMobile, isCustom, isActive) VALUES (:name_checkout, :user__id, :checkout_id_product, :counter_active, :support_active, :support_whatsapp, :support_email, :request_email_client, :checkout_bg_counter, :checkout_text_counter, :checkout_box_color, :checkout_box_text_color, :checkout_counter_lblText, :checkout_not1, :checkout_not2, :checkout_not3, :checkout_not4, :checkout_not5, :checkout_min_quantity1, :checkout_min_quantity2, :checkout_min_quantity3, :checkout_min_quantity4, :checkout_min_quantity5, :time_counter,:imageTopDesktop, :imageSideDesktop, :imageTopMobile, :imagebottonMobile, :isCustom, :isActive)");

        try {
            //code...
            $insert_custom_checkout->execute(['name_checkout' => $checkout_name,'user__id' => $user__id, 'checkout_id_product' => $checkout_exclusive,'counter_active' => $haveCounter,'support_active' => $suportActive,'support_whatsapp' => $whatsapp,'support_email' => $email,'request_email_client' => $emailClient,'checkout_bg_counter' => $bgCounter,'checkout_text_counter' => $textColor, 'checkout_box_color' => $bgBoxColor, 'checkout_box_text_color' => $bgBoxTextColor, 'checkout_counter_lblText' => $textCounter, 'checkout_not1' => $thisMoment,'checkout_not2' => $thisWeek,'checkout_not3' => $last30M,'checkout_not4' => $today,'checkout_not5' => $lashHour, 'checkout_min_quantity1' => $lbls->lbl1->minQtd, 'checkout_min_quantity2' => $lbls->lbl2->minQtd,'checkout_min_quantity3' => $lbls->lbl3->minQtd, 'checkout_min_quantity4' => $lbls->lbl4->minQtd, 'checkout_min_quantity5' => $lbls->lbl5->minQtd, 'isCustom' => 1,'isActive' => 1,'time_counter' => $timer, 'imageTopDesktop' => $arrayImages['imageTopDesktop'], 'imageSideDesktop' => $arrayImages['imageSideDesktop'], 'imageTopMobile' => $arrayImages['imageTopMobile'], 'imagebottonMobile' => $arrayImages['imagebottonMobile']]);
            echo json_encode(['status' => 200, 'msg' => "Novo checkout cadastrado com sucesso!", 'type' => "success"]);
            return;

        } catch(PDOException $e) {
            $error= 'ERROR: ' . $e->getMessage();
            $feedback = array('status' => 0, 'msg' => $error);
            echo json_encode($feedback);
        }

    } else if($_POST['action'] == 'edition-checkout') {

        
        $arrayOrder = explode(',', @$_POST['arrayOrder']);
    
        $user__id = $_SESSION['UserID'];
        $lbls = json_decode($_POST['lbls']);
        $RemoveImage = json_decode($_POST['RemoveImage']);
        $errors = false;
        $checkout_id = addslashes($_POST['idCheckout']);
        $checkout_exclusive = addslashes($_POST['checkout-exclusive']);

        $checkout_name = addslashes($_POST['nameCheckout']);
        $whatsapp = isset($_POST['support_whatsapp']) ? addslashes($_POST['support_whatsapp']) : "";
        $email = isset($_POST['support_email']) ? addslashes($_POST['support_email']) : ""; 
        $suportActive = $whatsapp !== "" || $email !== "" ? 1 : 0;
        $emailClient =  addslashes($_POST['emailClient']);
        
        # Cores do Card onde fica a imagem do produto
        $bgBox = isset($_POST['bgBox']) ? $_POST['bgBox'] : '#c8ffe6';
        $bgTextBox = isset($_POST['bgTextBox']) ? $_POST['bgTextBox'] : '#3d4465';


        # NOTIFICAÇÕES 
        $thisMoment = $lbls->lbl1->value == "true" ? 1 : 0;
        $thisWeek = $lbls->lbl2->value == "true" ? 1 : 0;
        $last30M = $lbls->lbl3->value == "true" ? 1 : 0;
        $today = $lbls->lbl4->value == "true" ? 1 : 0;
        $lashHour = $lbls->lbl5->value == "true" ? 1 : 0; 

        # QUANTIDADE MINIMA
        $minQuantityLbl1 = $lbls->lbl1->minQtd;
        $minQuantityLbl2 = $lbls->lbl2->minQtd;
        $minQuantityLbl3 = $lbls->lbl3->minQtd;
        $minQuantityLbl4 = $lbls->lbl4->minQtd;
        $minQuantityLbl5 = $lbls->lbl5->minQtd;

        # Cores do contador
        $bgCounter = isset($_POST['bgCounter']) ? $_POST['bgCounter'] : '#c66751';
        $textColor = isset($_POST['textColor']) ? $_POST['textColor']: '#ffffff';
        $textCounter = isset($_POST['textCounter']) ? $_POST['textCounter'] : 'A condição especial terminará em:'; 
        $timer = isset($_POST['timer']) ? $_POST['timer'] : '05:00';
        $haveCounter = isset($_POST['haveCounter']) ? 1 : 0;

        // - Pegar imagens já cadastradas no banco de dados
        $get_images = $conn->prepare("SELECT imageTopDesktop, imageSideDesktop, imageTopMobile, imagebottonMobile FROM custom_checkout WHERE checkout_id = :checkout_id");
        $get_images->execute([
            'checkout_id' => $checkout_id
        ]);
        
        $images = $get_images->fetchAll(\PDO::FETCH_ASSOC)[0];


        $arrayImages = [
            'imageTopDesktop' => '',
            'imageSideDesktop' => '',
            'imageTopMobile' => '',
            'imagebottonMobile' => ''
        ];
        
        $arrayRemoveImage = [];


        if($RemoveImage->RemoveTopDesktop == true){
            $arrayRemoveImage['imageTopDesktop'] = true;
        }
        if($RemoveImage->RemoveSideDesktop == true){
            $arrayRemoveImage['imageSideDesktop'] = true;
        }
        if($RemoveImage->RemoveTopMobile == true){
            $arrayRemoveImage['imageTopMobile'] = true;
        }
        if($RemoveImage->RemovebottonMobile == true){
            $arrayRemoveImage['imagebottonMobile'] = true;
        }


        $arrayOrder = explode(',', @$_POST['arrayOrder']);
        $arrayOrderValues = [];
        foreach($arrayOrder as $key => $order){ 
            $arrayOrderValues[$order] = $key;
        }


        if(isset($_POST['haveImage'])){

            // VERIFICAÇÃO CASO TENHA UPDATE EM ALGUMA DAS DUAS IMAGENS
            if(isset($arrayOrderValues['imageTopDesktop'])){
                if(!isset($arrayOrderValues['imageSideDesktop']) && isset($arrayRemoveImage['imageSideDesktop'])){
                    echo json_encode(['status' => 0, 'msg' => "Não é possivel inserir apenas uma imagem desktop.", 'type' => "warning"]);
                    return;
                }
            }
            
            if(isset($arrayOrderValues['imageSideDesktop'])){
                if(!isset($arrayOrderValues['imageTopDesktop']) &&isset($arrayRemoveImage['imageTopDesktop'])){
                    echo json_encode(['status' => 0, 'msg' => "Não é possivel inserir apenas uma imagem desktop.", 'type' => "warning"]);
                    return;
                }
            }

            if(isset($arrayOrderValues['imageTopMobile'])){
                if(!isset($arrayOrderValues['imageBottomMobile']) && isset($arrayRemoveImage['imageBottomMobile'])){
                    echo json_encode(['status' => 0, 'msg' => "Não é possivel inserir apenas uma imagem mobile.", 'type' => "warning"]);
                    return;
                }
            }

            if(isset($arrayOrderValues['imageBottomMobile'])){
                if(!isset($arrayOrderValues['imageTopMobile']) && isset($arrayRemoveImage['imageTopMobile'])){
                    echo json_encode(['status' => 0, 'msg' => "Não é possivel inserir apenas uma imagem mobile.", 'type' => "warning"]);
                    return;
                }
            }

            $countfiles = count($_FILES['files']['name']);
            $filetypes = array('jfif', 'png','PNG', 'jpeg', 'jpg');
            
        
            $errorUpload = false;
            for($i=0; $i < $countfiles; $i++){
                if(in_array($_FILES['files']['type'][$i], $filetypes)){
                    echo json_encode(['status' => 0, 'msg' => "As imagems do checkout precisa estar em formato JPEG, PNG ou JFIF.", 'type' => "warning"]);
                    $errorUpload = true;
                }
            }

            $errorImageTopDesktop = false;
            $imageSideDesktop = false;

            for($i=0; $i < $countfiles; $i++){
                $width = getimagesize($_FILES['files']['tmp_name'][$i])[0];
                $height = getimagesize($_FILES['files']['tmp_name'][$i])[1];

                if($arrayOrder[$i] == 'imageTopDesktop'){
                    if($width > 975 || $height > 365){   
                        echo json_encode(['status' => 0, 'msg' => "A Imagem de capa do desktop deve ser menor que 975x365px", 'type' => "warning"]);
                        return;
                    }
                }

                if($arrayOrder[$i] == 'imageSideDesktop'){
                    if($width > 450 || $height > 1350){   
                        echo json_encode(['status' => 0, 'msg' => "A Imagem lateral deve ser menor que 450x1350px", 'type' => "warning"]);
                        return;
                    }
                }

                if($arrayOrder[$i] == 'imageTopMobile'){
                    if($width > 975 || $height > 365){   
                        echo json_encode(['status' => 0, 'msg' => "A Imagem de capa do mobile deve ser menor que 975x365px", 'type' => "warning"]);
                        return;
                    }
                }

                if($arrayOrder[$i] == 'imageBottomMobile'){
                    if($width > 975 || $height > 365){   
                        echo json_encode(['status' => 0, 'msg' => "A Imagem footer do mobile deve ser menor que 975x365px", 'type' => "warning"]);
                        return;
                    }
                }
            }

            // VERIFICAÇÕES E UPDATE DAS IMAGENS UMA POR UMA 
            for ($i=0; $i < $countfiles; $i++) { 
                
                if($arrayOrder[$i] == 'imageTopDesktop'){
                    if(isset($_FILES['files']['tmp_name'][$i])){ // TEVE UPDATE

                        try {
                            $new_name = uniqid('_');
                            $dir = '../uploads/imagens/checkout/';
                            $arrayFiles[$i] = $new_name;
        
                            move_uploaded_file($_FILES['files']['tmp_name'][$i], $dir.$new_name);
                            
                            $update_image_top = $conn->prepare('UPDATE custom_checkout SET imageTopDesktop = :imageTopDesktop WHERE checkout_id = :checkout_id');
                            $update_image_top->execute(['imageTopDesktop' => $new_name, 'checkout_id' => $checkout_id]);
                            
                        } catch (\Throwable $th) {
                            echo json_encode(['status' => 0, 'msg' => "Não deu! Erro ao fazer upload da imagem do checkout!", 'type' => "warning"]);
                            return;
                        }   


                    }
                }

                if($arrayOrder[$i] == 'imageSideDesktop'){
                    if(isset($_FILES['files']['tmp_name'][$i])){ // TEVE UPDATE
                        
                        try {
                            $new_name = uniqid('_');
                            $dir = '../uploads/imagens/checkout/';
                            $arrayFiles[$i] = $new_name;
        
                            move_uploaded_file($_FILES['files']['tmp_name'][$i], $dir.$new_name);
                            
                            $update_image_side = $conn->prepare('UPDATE custom_checkout SET imageSideDesktop = :imageSideDesktop WHERE checkout_id = :checkout_id');
                            $update_image_side->execute(['imageSideDesktop' => $new_name, 'checkout_id' => $checkout_id]);
                        
                        } catch (\Throwable $th) {
                            echo json_encode(['status' => 0, 'msg' => "Não deu! Erro ao fazer upload da imagem do checkout!", 'type' => "warning"]);
                            return;
                        }   

                    }   
                }
                
                if($arrayOrder[$i] == 'imageTopMobile'){
                    if(isset($_FILES['files']['tmp_name'][$i])){ // TEVE UPDATE

                        try {
                            $new_name = uniqid('_');
                            $dir = '../uploads/imagens/checkout/';
                            $arrayFiles[$i] = $new_name;
        
                            move_uploaded_file($_FILES['files']['tmp_name'][$i], $dir.$new_name);
                            
                            $update_image_top_mobile = $conn->prepare('UPDATE custom_checkout SET imageTopMobile = :imageTopMobile WHERE checkout_id = :checkout_id');
                            $update_image_top_mobile->execute(['imageTopMobile' => $new_name, 'checkout_id' => $checkout_id]);
                            
                        } catch (\Throwable $th) {
                            echo json_encode(['status' => 0, 'msg' => "Não deu! Erro ao fazer upload da imagem do checkout!", 'type' => "warning"]);
                            return;
                        }   


                    }   
                }

                if($arrayOrder[$i] == 'imagebottonMobile'){
                    if(isset($_FILES['files']['tmp_name'][$i])){ // TEVE UPDATE

                        try {
                            $new_name = uniqid('_');
                            $dir = '../uploads/imagens/checkout/';
                            $arrayFiles[$i] = $new_name;
        
                            move_uploaded_file($_FILES['files']['tmp_name'][$i], $dir.$new_name);
                            
                            $update_image_bottom_mobile = $conn->prepare('UPDATE custom_checkout SET imagebottonMobile = :imagebottonMobile WHERE checkout_id = :checkout_id');
                            $update_image_bottom_mobile->execute(['imagebottonMobile' => $new_name, 'checkout_id' => $checkout_id]);
                        
                        } catch (\Throwable $th) {
                            echo json_encode(['status' => 0, 'msg' => "Não deu! Erro ao fazer upload da imagem do checkout!", 'type' => "warning"]);
                            return;
                        }   


                    }   
                }
            }

        }

        // VERIFICAÇÃO CASO NÃO TENHA UPDATE NAS IMAGENS 

        if(isset($arrayRemoveImage['imageTopDesktop']) && !isset($arrayRemoveImage['imageSideDesktop'])){
            echo json_encode(['status' => 0, 'msg' => "As duas imagens desktop devem ser informadas ou removidas.", 'type' => "warning"]);
            return;
        }

        if(isset($arrayRemoveImage['imageSideDesktop']) && !isset($arrayRemoveImage['imageTopDesktop'])){
            echo json_encode(['status' => 0, 'msg' => "As duas imagens desktop devem ser informadas ou removidas.", 'type' => "warning"]);
            return;
        }

        if(isset($arrayRemoveImage['imageTopMobile']) && !isset($arrayRemoveImage['imagebottonMobile'])){
            echo json_encode(['status' => 0, 'msg' => "As duas imagens mobile devem ser informadas ou removidas.", 'type' => "warning"]);
            return;
        }

        if(isset($arrayRemoveImage['imagebottonMobile']) && !isset($arrayRemoveImage['imageTopMobile'])){
            echo json_encode(['status' => 0, 'msg' => "As duas imagens mobile devem ser informadas ou removidas.", 'type' => "warning"]);
            return;
        }

        // VERIFICAR SE HOUVE IMAGENS REMOVIDAS 
        if(isset($arrayRemoveImage['imageTopDesktop']) && $images['imageTopDesktop'] !== ''){ // REMOVER FOTO DO TOPO DO DESKTOP

            $update_image_top = $conn->prepare('UPDATE custom_checkout SET imageTopDesktop = :imageTopDesktop WHERE checkout_id = :checkout_id');
            $update_image_top->execute(['imageTopDesktop' => '', 'checkout_id' => $checkout_id]);

        } 

        if(isset($arrayRemoveImage['imageSideDesktop']) && $images['imageSideDesktop'] !== ''){ // REMOVER FOTO DA LATERAL DO DESKTOP 

            $update_image_top = $conn->prepare('UPDATE custom_checkout SET imageSideDesktop = :imageSideDesktop WHERE checkout_id = :checkout_id');
            $update_image_top->execute(['imageSideDesktop' => '', 'checkout_id' => $checkout_id]);

        } 
    
        if(isset($arrayRemoveImage['imageTopMobile']) && $images['imageTopMobile'] !== ''){ // REMOVER FOTO DO TOPO MOBILE 
            
            $update_image_top = $conn->prepare('UPDATE custom_checkout SET imageTopMobile = :imageTopMobile WHERE checkout_id = :checkout_id');
            $update_image_top->execute(['imageTopMobile' => '', 'checkout_id' => $checkout_id]);

        } 
    
        if(isset($arrayRemoveImage['imagebottonMobile']) && $images['imagebottonMobile'] !== ''){ // REMOVER FOTO BOTTOM DO MOBILE

            $update_image_top = $conn->prepare('UPDATE custom_checkout SET imagebottonMobile = :imagebottonMobile WHERE checkout_id = :checkout_id');
            $update_image_top->execute(['imagebottonMobile' => '', 'checkout_id' => $checkout_id]);

        } 

        $insert_custom_checkout = $conn->prepare("UPDATE custom_checkout SET name_checkout = :name_checkout, user__id = :user__id, checkout_id_product = :checkout_id_product, counter_active = :counter_active, support_active = :support_active, support_whatsapp = :support_whatsapp, support_email = :support_email, request_email_client = :request_email_client, checkout_bg_counter = :checkout_bg_counter, checkout_text_counter = :checkout_text_counter, checkout_box_color = :checkout_box_color, checkout_box_text_color = :checkout_box_text_color, checkout_counter_lblText = :checkout_counter_lblText, checkout_not1 = :checkout_not1, checkout_not2 = :checkout_not2, checkout_not3 = :checkout_not3, checkout_not4 = :checkout_not4, checkout_not5 = :checkout_not5, checkout_min_quantity1 = :checkout_min_quantity1, checkout_min_quantity2 = :checkout_min_quantity2, checkout_min_quantity3 = :checkout_min_quantity3, checkout_min_quantity4 = :checkout_min_quantity4, checkout_min_quantity5 = :checkout_min_quantity5, time_counter = :time_counter, isCustom = :isCustom, isActive = :isActive WHERE checkout_id = :checkout_id");
            
        // ATUALIZAR OFERTAS QUE USAM O NOME DESSA OFERTA
        $updade_sale = $conn->prepare("UPDATE sales AS s SET s.type_checkout = :new_name WHERE s.product_id = :product_id AND s.type_checkout = :name_after");
        
        try {
            //code...
            $insert_custom_checkout->execute(['name_checkout' => $checkout_name,'user__id' => $user__id, 'checkout_id_product' => $checkout_exclusive,'counter_active' => $haveCounter,'support_active' => $suportActive,'support_whatsapp' => $whatsapp,'support_email' => $email,'request_email_client' => $emailClient,'checkout_bg_counter' => $bgCounter,'checkout_text_counter' => $textColor, 'checkout_box_color' => $bgBox, 'checkout_box_text_color' => $bgTextBox,  'checkout_counter_lblText' => $textCounter, 'checkout_not1' => $thisMoment,'checkout_not2' => $thisWeek,'checkout_not3' => $last30M,'checkout_not4' => $today,'checkout_not5' => $lashHour, 'checkout_min_quantity1' => $minQuantityLbl1, 'checkout_min_quantity2' => $minQuantityLbl2, 'checkout_min_quantity3' => $minQuantityLbl3, 'checkout_min_quantity4' => $minQuantityLbl4, 'checkout_min_quantity5' => $minQuantityLbl5, 'isCustom' => 1,'isActive' => 1,'time_counter' => $timer, 'checkout_id' => $checkout_id]);
            $updade_sale->execute(['new_name' => $checkout_name,'product_id' => $_POST['product-id'],'name_after' => $_POST['checkout-name']]);

            echo json_encode(['status' => 200, 'msg' => "Checkout editado com sucesso!", 'type' => "success"]);
            return;

        } catch (\Exception $th) {
            echo json_encode(['status' => 500, 'msg' => "Erro ao editar checkout, tente novamente mais tarde.", 'type' => $th->getMessage()]);
            return;
        }
    } else if($_POST['action'] == 'delete-checkout'){

        $checkout_id = addslashes($_POST['idCheckout']);

        $check_custom_checkout = $conn->prepare('SELECT user__id FROM custom_checkout WHERE checkout_id = :checkout_id');
        $check_custom_checkout->execute(array('checkout_id' => $checkout_id));
        $custom_checkout = $check_custom_checkout->fetch();

        if($custom_checkout['user__id'] != $user__id){    
            if($_SESSION['UserID'] != 5){
                echo json_encode(['status' => 500, 'msg' => "Você não tem permissão para excluir este checkout!", 'type' => "error"]);
                return;
            }
        }

        try {
            $delete_custom_checkout = $conn->prepare('DELETE FROM custom_checkout WHERE checkout_id = :checkout_id');
            $delete_custom_checkout->execute(array('checkout_id' => $checkout_id));

            $feedback = array('title' => 'Feito!', 'msg' => "Checkout deletado com sucesso", 'type' => 'success');
            echo json_encode($feedback);
            exit; 

        } catch (\Throwable $th) {
            echo json_encode(['status' => 500, 'msg' => "Erro ao deletar o checkout, tente novamente mais tarde.", 'type' => "error"]);
            return;
        }  

    } else {
        echo json_encode(['status' => 500, 'msg' => "ERRO! Acesso inválido.", 'type' => "warning"]);
        return;
    }
