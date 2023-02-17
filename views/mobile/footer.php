</div>
        <!--**********************************
            Content body end
        ***********************************-->

        <?php if(!isset($mobile))   { ?> 

            <!--**********************************
                Footer start
            ***********************************-->
                        
            <?php require_once (dirname(__FILE__) . '/../../elements/footer.php'); ?>
		
            <!--**********************************
                Footer end
            ***********************************-->
        <?php } ?>

    </div>
    <!--**********************************
        Main wrapper end
    ***********************************-->

    <?php if(isset($mobile) && $mobile)   { ?> 

        <div class="position-sticky d-block w-100 text-center" style="bottom: 0px !important; z-index: 999; background-color:#2fde91; height: 80px">
            <div class="p-1 my-2" style="border-radius: 50px; ">   
                <div class="d-flex justify-content-center mt-3">   
                    <div class="mr-5">     
                        <a type="button" class="" href="/views/mobile/finances.php">
                            <i class="fa fa-2x fa-dollar-sign" style="color: white;"></i>                        
                        </a>
                        <p style="color: white;">Saque</p>
                    </div>
                    <div class="mr-5"> 
                        <a type="button" class="" href="<?php echo SERVER_URI; ?>/index.php">
                            <i class="fa fa-2x fa-home" style="color: white;"></i>
                        </a>
                        <p style="color: white;">Início</p>
                    </div>
                    <div class=""> 
                        <a type="button" id="logout" class="">
                            <i class="fa fa-2x fa-sign-out-alt" style="color: white;"></i>
                        </a>  
                        <p style="color: white;">Sair</p>
                    </div>
                </div> 
            </div>
        </div>

        <?php } else { ?>
             
            <a href="https://api.whatsapp.com/send?phone=5562981374687" target="_blank" class="btn btn-success" id="chat-bottom-fixed-btn" title="Chat">
                <i class="fab fa-whatsapp fa-2x"></i>
            </a>

    <?php } ?>

    <!--**********************************
        Scripts
    ***********************************-->
	<?php require_once (dirname(__FILE__) . '/../../includes/layout/default/default-footer-scripts.php'); ?>
</body>
</html>

<script>
    $("#logout").on("click", function() {
        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: 'btn btn-primary',  
            cancelButton: 'btn btn-danger mr-2'
        },
        buttonsStyling: false
        })

        swalWithBootstrapButtons.fire({ 
            title: 'Você deseja sair ?',
            icon: 'question', 
            showCancelButton: true,
            confirmButtonText: 'Sair',
            denyButtonText: `Cancelar`,            
            reverseButtons: true,
            focusConfirm: false 
            }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                window.location.href = u + "/sair";
            } 
        })
    })
</script>