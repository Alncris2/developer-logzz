<?php

require_once (dirname(__FILE__) . '/includes/config.php');

$get_all_users = $conn->prepare('SELECT * FROM users');
$get_all_users->execute();

while($users_info = $get_all_users->fetch()){
    
    $user__id = $users_info['user__id'];

    # Verifica se as infos já estão na nova tabela
    $verify_transfered_infos = $conn->prepare('SELECT * FROM bank_account_list WHERE account_user_id = :user__id');
    $verify_transfered_infos->execute(array('user__id' => $user__id));

    if ($verify_transfered_infos->rowCount() > 0) {

        echo "As infos bacnárias do Usuário <b>" . $user__id . "</b> já foram transferidas ateriormente!<br>";

    } else {

        # Se não estiverem, transfere
        $verify_added_accs = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = "added_accounts"');
        $verify_added_accs->execute(array('user__id' => $user__id));
    
        if ($verify_added_accs->rowCount() == 1){
            
            $added_accs = $verify_added_accs->fetch();
            $added_accs = $added_accs['meta_value'];

            $this_acc = 1;
            
            while ($this_acc <= $added_accs){
                                        
                $meta_key_bank = "ACC_U" . $user__id . "-A" . $this_acc . "_BANK";
                $meta_key_agency = "ACC_U" . $user__id . "-A" . $this_acc . "_AGENCY";
                $meta_key_account = "ACC_U" . $user__id . "-A" . $this_acc . "_ACCOUNT";
                $meta_key_type = "ACC_U" . $user__id . "-A" . $this_acc . "_TYPE";
                $meta_key_pix_type = "ACC_U" . $user__id . "-A" . $this_acc . "_PIX_TYPE";
                $meta_key_pix_key = "ACC_U" . $user__id . "-A" . $this_acc . "_PIX_KEY";
                
                $get_bank = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = :meta_key');
                $get_bank->execute(array('meta_key' => $meta_key_bank, 'user__id' => $user__id));
                $get_bank = $get_bank->fetch();
                $bank = $get_bank[0];

                $get_agency = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = :meta_key');
                $get_agency->execute(array('meta_key' => $meta_key_agency, 'user__id' => $user__id));
                $get_agency = $get_agency->fetch();
                $agency = $get_agency[0];

                $get_account = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = :meta_key');
                $get_account->execute(array('meta_key' => $meta_key_account, 'user__id' => $user__id));
                $get_account = $get_account->fetch();
                $account = $get_account[0];

                $get_type = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = :meta_key');
                $get_type->execute(array('meta_key' => $meta_key_type, 'user__id' => $user__id));
                $get_type = $get_type->fetch();
                $type = $get_type[0];
                
                $get_pix_type = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = :meta_key');
                $get_pix_type->execute(array('meta_key' => $meta_key_pix_type, 'user__id' => $user__id));
                $get_pix_type = $get_pix_type->fetch();
                @$pix_type = $get_pix_type[0];

                $get_pix_key = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = :meta_key');
                $get_pix_key->execute(array('meta_key' => $meta_key_pix_key, 'user__id' => $user__id));
                $get_pix_key = $get_pix_key->fetch();
                @$pix_key = $get_pix_key[0];

                $bank_acc_migrate = $conn->prepare('INSERT INTO bank_account_list (account_id, account_user_id, account_bank, account_agency, account_number, account_type, account_pix_type, account_pix_key) VALUES (:account_id, :account_user_id, :account_bank, :account_agency, :account_number, :account_type, :account_pix_type, :account_pix_key)');
                $bank_acc_migrate->execute(array('account_id' => 0, 'account_user_id' => $user__id, 'account_bank' => $bank, 'account_agency' => $agency, 'account_number' => $account, 'account_type' => $type, 'account_pix_type' => $pix_type, 'account_pix_key' => $pix_key));

                $this_acc = $this_acc + 1;
            }

            echo "Migrada(s) " . $this_acc . " conta(s) do USER " . $user__id ."<br>";

        }
    }

}


?>