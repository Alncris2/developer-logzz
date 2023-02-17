
<?php
require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

if (!(isset($_SESSION['UserID'])) || $_SESSION['UserPlan'] < 5) {
    header('Location: ' . SERVER_URI . '/login');
    exit;
}

$user__id = $_SESSION['UserID'];

$page_title = "Editar Usuário | DropExpress";
$subscriber_page = true;
$profile_page = true;
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');
?>

<style>
    .drop-zone {
        max-width: 500px;
        height: 250px;
        padding: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        font-family: "Quicksand", sans-serif;
        font-weight: 500;
        font-size: 20px;
        cursor: pointer;
        color: #cccccc;
        border: 4px dashed #009578;
        border-radius: 10px;
        }

    .drop-zone--over {
        border-style: solid;
    }

    .drop-zone__input {
        display: none;
    }

    .drop-zone__thumb {
        width: 100%;
        height: 100%;
        border-radius: 10px;
        overflow: hidden;
        background-color: #cccccc;
        background-size: cover;
        position: relative;
    }

    .drop-zone__thumb::after {
        content: attr(data-label);
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 5px 0;
        color: #ffffff;
        background: rgba(0, 0, 0, 0.75);
        font-size: 14px;
        text-align: center;
    }
</style>

    <div class="container-fluid">
        <!-- row -->
        <div class="row">
            <div class="col-xl-6 col-xxl-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Dados do Usuário</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label class="text-label" >Imagem da capa</label>      
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" name="select-banner" id="select-banner" <?php if(@$sale['url_upsell'] != null) echo 'checked'?>>
                                <label class="custom-control-label" id="select-banner-lbl" for="select-banner">&nbsp; <?php if(@$sale['url_upsell'] != null){ echo 'Sim'; }else{ echo "Não"; }?></label>
                            </div>

                            <div id="components-banner" class="d-none">
                                <div class="selects-container d-flex flex-column justify-content-start">
                                <ul class="nav nav-tabs d-flex justify-content-start" style="width:100%;">
                                    <li id="desktop" style="width:25%;" class="mr-1 actives text-align-left">
                                        <a data-toggle="tab" href="#menu1">
                                            <svg style="width:16px;margin-right:3px;" fill="#494a51" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M528 0h-480C21.5 0 0 21.5 0 48v320C0 394.5 21.5 416 48 416h192L224 464H152C138.8 464 128 474.8 128 488S138.8 512 152 512h272c13.25 0 24-10.75 24-24s-10.75-24-24-24H352L336 416h192c26.5 0 48-21.5 48-48v-320C576 21.5 554.5 0 528 0zM512 288H64V64h448V288z"/></svg>
                                            Layout desktop 
                                        </a>
                                    </li> 
                                    <li class="ml-4 d-flex align-items-center justify-content-start" id="mobile">
                                        <a data-toggle="tab" href="#menu2">
                                            <svg style="width:10px;margin-right:3px;" fill="#494a51" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M320 0H64C37.5 0 16 21.5 16 48v416C16 490.5 37.5 512 64 512h256c26.5 0 48-21.5 48-48v-416C368 21.5 346.5 0 320 0zM240 447.1C240 456.8 232.8 464 224 464H159.1C151.2 464 144 456.8 144 448S151.2 432 160 432h64C232.8 432 240 439.2 240 447.1zM304 384h-224V64h224V384z"/></svg> 
                                            Layout mobile 
                                            <svg  class="ml-1" fill="#898b96" style="width:15px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M256 0C114.6 0 0 114.6 0 256s114.6 256 256 256s256-114.6 256-256S397.4 0 256 0zM256 400c-18 0-32-14-32-32s13.1-32 32-32c17.1 0 32 14 32 32S273.1 400 256 400zM325.1 258L280 286V288c0 13-11 24-24 24S232 301 232 288V272c0-8 4-16 12-21l57-34C308 213 312 206 312 198C312 186 301.1 176 289.1 176h-51.1C225.1 176 216 186 216 198c0 13-11 24-24 24s-24-11-24-24C168 159 199 128 237.1 128h51.1C329 128 360 159 360 198C360 222 347 245 325.1 258z"/></svg>
                                        </a>
                                    </li> 
                                </ul> 
                                        
                                <div class="tab-content mt-3">
                                    <div id="menu1" class="tab-pane fade active show">
                                        <p>Imagem de capa</p>
                                        <div class="labels d-flex">                                    
                                            <div class="col-lg-8 box-banner d-flex flex-column" style="width:500;">
                                                <div class="superior-banner d-flex justify-content-center align-items-center" style="background:white;height:150px;border:1px dashed #ccc;">
                                                    <p style="color:#ccc;font-size:25px;">Arraste e solte os arquivos aqui</p>  
                                                </div>

                                                <div class="options-btn d-flex mt-2" style="width:100%;">
                                                    <button type="button" style="width:50%;border:1px solid #adbaca;" disabled class="btn btn-outline-secondary">Imagem superior</button>
                                                    <button type="button" style="border:1px solid #adbaca;" class="btn btn-outline-secondary" disabled>Remover</button>
                                                    <label id="btn-file" for="inputTag">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="#fff" style="width:20px;margin-right:5px;" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M572.6 270.3l-96 192C471.2 473.2 460.1 480 447.1 480H64c-35.35 0-64-28.66-64-64V96c0-35.34 28.65-64 64-64h117.5c16.97 0 33.25 6.742 45.26 18.75L275.9 96H416c35.35 0 64 28.66 64 64v32h-48V160c0-8.824-7.178-16-16-16H256L192.8 84.69C189.8 81.66 185.8 80 181.5 80H64C55.18 80 48 87.18 48 96v288l71.16-142.3C124.6 230.8 135.7 224 147.8 224h396.2C567.7 224 583.2 249 572.6 270.3z"/></svg>
                                                        Procurar
                                                        <input id="fileBtn" type="file"/>
                                                    </label>
                                                </div>

                                                <div class="options-btn d-flex mt-2" style="width:100%;">
                                                    <button type="button" style="width:50%;border:1px solid #adbaca;" disabled class="btn btn-outline-secondary">Imagem Lateral</button>
                                                    <button type="button" style="border:1px solid #adbaca;" class="btn btn-outline-secondary" disabled>Remover</button> 
                                                    <label id="btn-file" for="inputTag">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="#fff" style="width:20px;margin-right:5px;" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M572.6 270.3l-96 192C471.2 473.2 460.1 480 447.1 480H64c-35.35 0-64-28.66-64-64V96c0-35.34 28.65-64 64-64h117.5c16.97 0 33.25 6.742 45.26 18.75L275.9 96H416c35.35 0 64 28.66 64 64v32h-48V160c0-8.824-7.178-16-16-16H256L192.8 84.69C189.8 81.66 185.8 80 181.5 80H64C55.18 80 48 87.18 48 96v288l71.16-142.3C124.6 230.8 135.7 224 147.8 224h396.2C567.7 224 583.2 249 572.6 270.3z"/></svg> 
                                                        Procurar
                                                        <input id="fileBtn" type="file"/>
                                                    </label>
                                                </div>
                                                <p style="font-size:15px;">Tamanho de imagem máximo permitido 975x365px</p>                                                     
                                            </div>
                                            
                                            <div class="col-lg-3 box-banner ml-2 d-flex flex-column justify-content-center align-items-center" style="background:white;height:500px;border:1px dashed #ccc;">
                                                <p style="color:#ccc;font-size:25px;">Arraste e solte os arquivos aqui</p> 
                                            </div>                                
                                        </div>
                                    </div>
                                    <div id="menu2" class="tab-pane fade">
                                        <p>Imagem de capa</p>
                                        <div class="labels d-flex">                                    
                                            <div class="col-lg-8 box-banner d-flex flex-column" style="width:500;">
                                                <div class="superior-banner d-flex justify-content-center align-items-center" style="background:white;height:150px;border:1px dashed #ccc;">
                                                    <p style="color:#ccc;font-size:25px;">Arraste e solte os arquivos aqui</p>  
                                                </div>

                                                <div class="options-btn d-flex mt-2" style="width:100%;">
                                                    <button type="button" style="width:50%;border:1px solid #adbaca;" disabled class="btn btn-outline-secondary">Imagem superior</button>
                                                    <button type="button" style="border:1px solid #adbaca;" class="btn btn-outline-secondary" disabled>Remover</button>
                                                    <label id="btn-file" for="inputTag">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="#fff" style="width:20px;margin-right:5px;" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M572.6 270.3l-96 192C471.2 473.2 460.1 480 447.1 480H64c-35.35 0-64-28.66-64-64V96c0-35.34 28.65-64 64-64h117.5c16.97 0 33.25 6.742 45.26 18.75L275.9 96H416c35.35 0 64 28.66 64 64v32h-48V160c0-8.824-7.178-16-16-16H256L192.8 84.69C189.8 81.66 185.8 80 181.5 80H64C55.18 80 48 87.18 48 96v288l71.16-142.3C124.6 230.8 135.7 224 147.8 224h396.2C567.7 224 583.2 249 572.6 270.3z"/></svg>
                                                        Procurar
                                                        <input id="fileBtn" type="file"/>
                                                    </label>
                                                </div>

                                                <div class="options-btn d-flex mt-2" style="width:100%;">
                                                    <button type="button" style="width:50%;border:1px solid #adbaca;" disabled class="btn btn-outline-secondary">Imagem Lateral</button>
                                                    <button type="button" style="border:1px solid #adbaca;" class="btn btn-outline-secondary" disabled>Remover</button> 
                                                    <label id="btn-file" for="inputTag">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="#fff" style="width:20px;margin-right:5px;" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.1.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M572.6 270.3l-96 192C471.2 473.2 460.1 480 447.1 480H64c-35.35 0-64-28.66-64-64V96c0-35.34 28.65-64 64-64h117.5c16.97 0 33.25 6.742 45.26 18.75L275.9 96H416c35.35 0 64 28.66 64 64v32h-48V160c0-8.824-7.178-16-16-16H256L192.8 84.69C189.8 81.66 185.8 80 181.5 80H64C55.18 80 48 87.18 48 96v288l71.16-142.3C124.6 230.8 135.7 224 147.8 224h396.2C567.7 224 583.2 249 572.6 270.3z"/></svg> 
                                                        Procurar
                                                        <input id="fileBtn" type="file"/>
                                                    </label>
                                                </div>
                                                <p style="font-size:15px;">Tamanho de imagem máximo permitido 975x365px</p>                                                 
                                        </div>
                                        <div class="col-lg-3 box-banner ml-2 d-flex flex-column justify-content-center align-items-center" style="background:white;height:500px;border:1px dashed #ccc;">
                                            <p style="color:#ccc;font-size:25px;">Arraste e solte os arquivos aqui</p> 
                                        </div>                                
                                    </div>
                                </div>                   
                            </div>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>

<script>
    document.querySelectorAll(".drop-zone__input").forEach((inputElement) => {
        const dropZoneElement = inputElement.closest(".drop-zone");

        dropZoneElement.addEventListener("click", (e) => {
            inputElement.click();
        });

        inputElement.addEventListener("change", (e) => {
            if (inputElement.files.length) {
            updateThumbnail(dropZoneElement, inputElement.files[0]);
            }
        });

        dropZoneElement.addEventListener("dragover", (e) => {
            e.preventDefault();
            dropZoneElement.classList.add("drop-zone--over");
        });

        ["dragleave", "dragend"].forEach((type) => {
            dropZoneElement.addEventListener(type, (e) => {
            dropZoneElement.classList.remove("drop-zone--over");
            });
        });

        dropZoneElement.addEventListener("drop", (e) => {
            e.preventDefault();

            if (e.dataTransfer.files.length) {
            inputElement.files = e.dataTransfer.files;
            updateThumbnail(dropZoneElement, e.dataTransfer.files[0]);
            }

            dropZoneElement.classList.remove("drop-zone--over");
        });
    });

    function updateThumbnail(dropZoneElement, file) {
        let thumbnailElement = dropZoneElement.querySelector(".drop-zone__thumb");

        // First time - remove the prompt
        if (dropZoneElement.querySelector(".drop-zone__prompt")) {
            dropZoneElement.querySelector(".drop-zone__prompt").remove();
        }

        // First time - there is no thumbnail element, so lets create it
        if (!thumbnailElement) {
            thumbnailElement = document.createElement("div");
            thumbnailElement.classList.add("drop-zone__thumb");
            dropZoneElement.appendChild(thumbnailElement);
        }

        thumbnailElement.dataset.label = file.name;

        // Show thumbnail for image files
        if (file.type.startsWith("image/")) {
            const reader = new FileReader();

            reader.readAsDataURL(file);
            reader.onload = () => {
            thumbnailElement.style.backgroundImage = `url('${reader.result}')`;
            };
        } else {
            thumbnailElement.style.backgroundImage = null;
        }
    }
</script>
