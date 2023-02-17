<?php

require_once(dirname(__FILE__) . '/../../includes/config.php');
session_name(SESSION_NAME);
session_start();

$user__id =$_SESSION['UserID'];

if (!(isset($_SESSION['UserID']))) {
    header('Location: ' . SERVER_URI . '/login');
}

$page_title = "Contas Bancárias | Logzz";
$shop_page = true;
$profile_page = true;
$select_datatable_page = true;
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-header.php');

?>

    <div class="container-fluid">
        <!-- row -->
        
            <div class="row">
                <div class="col-xl-4 col-xxl-5">
                        <div class="card">

                            <div class="card-header">
                                <h4 class="card-title">Adicionar Nova Conta</h4>
                            </div>

                            <div class="card-body">
                                    <!-- FORM ADD ACC -->
                                    <div class="tab-pane fade show active" id="conta" role="tabpanel" aria-labelledby="home-tab">
                                        <div class="alert alert-warning alert-dismissible fade show mt-3">
                                            <small>O documento (CPF ou CNPJ) do titular da conta bancária <strong>precisa ser igual</strong> ao documento do responsável pela conta Logzz.</small>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label class="text-label">Documento</label>
                                        <?php
                                            $get_user_doc = $conn->prepare('SELECT company_doc FROM users WHERE user__id = :user__id');
                                            $get_user_doc->execute(array('user__id' => $user__id));
                                            $user_doc = $get_user_doc->fetch();
                                        ?>
                                            <input type="text" name="doc" class="form-control text-muted bg-light" readonly value="<?php echo $user_doc[0]; ?>">
                                        </div>

                                        <form id="AddBankAccForm" action="add-bank-account" method="POST">
                                        <input type="hidden" name="action" value="add-bank-account">

                                        <div class="form-group">
                                            <label class="text-label">Banco</label>
                                            <select class="form-control default-select" id="select-filter-status-id" data-live-search="true" style="max-height: 200px">
                                                <option selected disabled>Banco</option>
                                                <option value="001">001 - Banco Do Brasil S.A (BB)</option>
                                                <option value="237">237 - Bradesco S.A.</option>
                                                <option value="335">335 - Banco Digio S.A.</option>
                                                <option value="260">260 - Nu Pagamentos S.A (Nubank)</option>
                                                <option value="290">290 - PagSeguro Internet S.A.</option>
                                                <option value="323">323 - Mercado Pago – Conta Do Mercado Livre</option>
                                                <option value="237B">237B - Next Bank (Mesmo Código Do Bradesco)</option>
                                                <option value="637">637 - Banco Sofisa S.A (Sofisa Direto)</option>
                                                <option value="077">077 - Banco Inter S.A.</option>
                                                <option value="341">341 - Itaú Unibanco S.A.</option>
                                                <option value="104">104 - Caixa Econômica Federal (CEF)</option>
                                                <option value="033">033 - Banco Santander Brasil S.A.</option>
                                                <option value="212">212 - Banco Original S.A.</option>
                                                <option value="756">756 - Bancoob – Banco Cooperativo Do Brasil S.A.</option>
                                                <option value="655">655 - Banco Votorantim S.A.</option>
                                                <option value="655">655 - Neon Pagamentos S.A (Memso Código Do Banco Votorantim)</option>
                                                <option value="041">041 - Banrisul – Banco Do Estado Do Rio Grande Do Sul S.A.</option>
                                                <option value="389">389 - Banco Mercantil Do Brasil S.A.</option>
                                                <option value="422">422 - Banco Safra S.A.</option>
                                                <option value="070">070 - BRB – Banco De Brasília S.A.</option>
                                                <option value="136">136 - Unicred Cooperativa LTDA</option>
                                                <option value="741">741 - Banco Ribeirão Preto S.A.</option>
                                                <option value="739">739 - Banco Cetelem S.A.</option>
                                                <option value="743">743 - Banco Semear S.A.</option>
                                                <option value="100">100 - Planner Corretora De Valores S.A.</option>
                                                <option value="096">096 - Banco B3 S.A.</option>
                                                <option value="747">747 - Banco Rabobank Internacional Do Brasil S.A.</option>
                                                <option value="748">748 - Banco Cooperativa Sicredi S.A.</option>
                                                <option value="752">752 - Banco BNP Paribas Brasil S.A.</option>
                                                <option value="091B">091B - Unicred Central Rs</option>
                                                <option value="399">399 - Kirton Bank S.A. – Banco Múltiplo</option>
                                                <option value="108">108 - Portocred S.A.</option>
                                                <option value="757">757 - Banco Keb Hana Do Brasil S.A.</option>
                                                <option value="102">102 - XP Investimentos S.A.</option>
                                                <option value="348">348 - Banco XP S/A</option>
                                                <option value="340">340 - Super Pagamentos S/A (Superdital)</option>
                                                <option value="084">084 - Uniprime Norte Do Paraná</option>
                                                <option value="180">180 - Cm Capital Markets Cctvm Ltda</option>
                                                <option value="066">066 - Banco Morgan Stanley S.A.</option>
                                                <option value="015">015 - UBS Brasil Cctvm S.A.</option>
                                                <option value="143">143 - Treviso Cc S.A.</option>
                                                <option value="062">062 - Hipercard Banco Múltiplo S.A.</option>
                                                <option value="074">074 - Banco J. Safra S.A.</option>
                                                <option value="099">099 - Uniprime Central Ccc Ltda</option>
                                                <option value="025">025 - Banco Alfa S.A.</option>
                                                <option value="075">075 - Bco Abn Amro S.A.</option>
                                                <option value="040">040 - Banco Cargill S.A.</option>
                                                <option value="190">190 - Servicoop</option>
                                                <option value="063">063 - Banco Bradescard</option>
                                                <option value="191">191 - Nova Futura Ctvm Ltda</option>
                                                <option value="064">064 - Goldman Sachs Do Brasil Bm S.A.</option>
                                                <option value="097B">097B - Ccc Noroeste Brasileiro Ltda</option>
                                                <option value="016">016 - Ccm Desp Trâns Sc E Rs</option>
                                                <option value="012">012 - Banco Inbursa</option>
                                                <option value="003">003 - Banco Da Amazônia S.A.</option>
                                                <option value="060">060 - Confidence Cc S.A.</option>
                                                <option value="037">037 - Banco Do Estado Do Pará S.A.</option>
                                                <option value="159">159 - Casa do Crédito S.A.</option>
                                                <option value="172">172 - Albatross Ccv S.A.</option>
                                                <option value="085">085 - Cooperativa Central de Créditos – Ailos</option>
                                                <option value="114">114 - Central Cooperativa De Crédito no Estado do Espírito Santo</option>
                                                <option value="036">036 - Banco Bradesco BBI S.A.</option>
                                                <option value="394">394 - Banco Bradesco Financiamentos S.A.</option>
                                                <option value="004">004 - Banco Do Nordeste Do Brasil S.A.</option>
                                                <option value="320">320 - China Construction Bank – Ccb Brasil S.A.</option>
                                                <option value="189">189 - Hs Financeira</option>
                                                <option value="105">105 - Lecca Cfi S.A.</option>
                                                <option value="076">076 - Banco KDB Brasil S.A.</option>
                                                <option value="082">082 - Banco Topázio S.A.</option>
                                                <option value="286">286 - Cooperativa de Crédito Rural de De Ouro</option>
                                                <option value="093">093 - PóloCred Scmepp Ltda</option>
                                                <option value="273">273 - Ccr De São Miguel Do Oeste</option>
                                                <option value="157">157 - Icap Do Brasil Ctvm Ltda</option>
                                                <option value="183">183 - Socred S.A.</option>
                                                <option value="014">014 - Natixis Brasil S.A.</option>
                                                <option value="130">130 - Caruana Scfi</option>
                                                <option value="127">127 - Codepe Cvc S.A.</option>
                                                <option value="079">079 - Banco Original Do Agronegócio S.A.</option>
                                                <option value="081B">081B - Bbn Banco Brasileiro De Negocios S.A.</option>
                                                <option value="118">118 - Standard Chartered Bi S.A.</option>
                                                <option value="133">133 - Cresol Confederação</option>
                                                <option value="121">121 - Banco Agibank S.A.</option>
                                                <option value="083">083 - Banco Da China Brasil S.A.</option>
                                                <option value="138">138 - Get Money Cc Ltda</option>
                                                <option value="024">024 - Banco Bandepe S.A.</option>
                                                <option value="095">095 - Banco Confidence De Câmbio S.A.</option>
                                                <option value="094">094 - Banco Finaxis</option>
                                                <option value="276">276 - Senff S.A.</option>
                                                <option value="137">137 - Multimoney Cc Ltda</option>
                                                <option value="092">092 - BRK S.A.</option>
                                                <option value="047">047 - Banco do Estado de Sergipe S.A.</option>
                                                <option value="144">144 - Bexs Banco De Cambio S.A.</option>
                                                <option value="126">126 - BR Partners Banco de Investimento S.A.</option>
                                                <option value="301">301 - BPP Instituição De Pagamentos S.A.</option>
                                                <option value="173">173 - BRL Trust Dtvm Sa</option>
                                                <option value="119">119 - Banco Western Union do Brasil S.A.</option>
                                                <option value="254">254 - Paraná Banco S.A.</option>
                                                <option value="268">268 - Barigui Companhia Hipotecária</option>
                                                <option value="107">107 - Banco Bocom BBM S.A.</option>
                                                <option value="412">412 - Banco Capital S.A.</option>
                                                <option value="124">124 - Banco Woori Bank Do Brasil S.A.</option>
                                                <option value="149">149 - Facta S.A. Cfi</option>
                                                <option value="197">197 - Stone Pagamentos S.A.</option>
                                                <option value="142">142 - Broker Brasil Cc Ltda</option>
                                                <option value="389">389 - Banco Mercantil Do Brasil S.A.</option>
                                                <option value="184">184 - Banco Itaú BBA S.A.</option>
                                                <option value="634">634 - Banco Triangulo S.A (Banco Triângulo)</option>
                                                <option value="545">545 - Senso Ccvm S.A.</option>
                                                <option value="132">132 - ICBC do Brasil Bm S.A.</option>
                                                <option value="298">298 - Vip’s Cc Ltda</option>
                                                <option value="129">129 - UBS Brasil Bi S.A.</option>
                                                <option value="128">128 - Ms Bank S.A Banco De Câmbio</option>
                                                <option value="194">194 - Parmetal Dtvm Ltda</option>
                                                <option value="310">310 - VORTX Dtvm Ltda</option>
                                                <option value="163">163 - Commerzbank Brasil S.A Banco Múltiplo</option>
                                                <option value="280">280 - Avista S.A.</option>
                                                <option value="146">146 - Guitta Cc Ltda</option>
                                                <option value="279">279 - Ccr De Primavera Do Leste</option>
                                                <option value="182">182 - Dacasa Financeira S/A</option>
                                                <option value="278">278 - Genial Investimentos Cvm S.A.</option>
                                                <option value="271">271 - Ib Cctvm Ltda</option>
                                                <option value="021">021 - Banco Banestes S.A.</option>
                                                <option value="246">246 - Banco Abc Brasil S.A.</option>
                                                <option value="751">751 - Scotiabank Brasil</option>
                                                <option value="208">208 - Banco BTG Pactual S.A.</option>
                                                <option value="746">746 - Banco Modal S.A.</option>
                                                <option value="241">241 - Banco Classico S.A.</option>
                                                <option value="612">612 - Banco Guanabara S.A.</option>
                                                <option value="604">604 - Banco Industrial Do Brasil S.A.</option>
                                                <option value="505">505 - Banco Credit Suisse (Brl) S.A.</option>
                                                <option value="196">196 - Banco Fair Cc S.A.</option>
                                                <option value="300">300 - Banco La Nacion Argentina</option>
                                                <option value="477">477 - Citibank N.A.</option>
                                                <option value="266">266 - Banco Cedula S.A.</option>
                                                <option value="122">122 - Banco Bradesco BERJ S.A.</option>
                                                <option value="376">376 - Banco J.P. Morgan S.A.</option>
                                                <option value="473">473 - Banco Caixa Geral Brasil S.A.</option>
                                                <option value="745">745 - Banco Citibank S.A.</option>
                                                <option value="120">120 - Banco Rodobens S.A.</option>
                                                <option value="265">265 - Banco Fator S.A.</option>
                                                <option value="007">007 - BNDES (Banco Nacional Do Desenvolvimento Social)</option>
                                                <option value="188">188 - Ativa S.A Investimentos</option>
                                                <option value="134">134 - BGC Liquidez Dtvm Ltda</option>
                                                <option value="641">641 - Banco Alvorada S.A.</option>
                                                <option value="029">029 - Banco Itaú Consignado S.A.</option>
                                                <option value="243">243 - Banco Máxima S.A.</option>
                                                <option value="078">078 - Haitong Bi Do Brasil S.A.</option>
                                                <option value="111">111 - Banco Oliveira Trust Dtvm S.A.</option>
                                                <option value="017">017 - Bny Mellon Banco S.A.</option>
                                                <option value="174B">174B - Pernambucanas Financ S.A.</option>
                                                <option value="495">495 - La Provincia Buenos Aires Banco</option>
                                                <option value="125">125 - Brasil Plural S.A Banco</option>
                                                <option value="488">488 - Jpmorgan Chase Bank</option>
                                                <option value="065">065 - Banco Andbank S.A.</option>
                                                <option value="492">492 - Ing Bank N.V.</option>
                                                <option value="250">250 - Banco Bcv</option>
                                                <option value="145">145 - Levycam Ccv Ltda</option>
                                                <option value="494">494 - Banco Rep Oriental Uruguay</option>
                                                <option value="253">253 - Bexs Cc S.A.</option>
                                                <option value="269">269 - Hsbc Banco De Investimento</option>
                                                <option value="213">213 - Bco Arbi S.A.</option>
                                                <option value="139">139 - Intesa Sanpaolo Brasil S.A.</option>
                                                <option value="018">018 - Banco Tricury S.A.</option>
                                                <option value="630">630 - Banco Intercap S.A.</option>
                                                <option value="224">224 - Banco Fibra S.A.</option>
                                                <option value="600">600 - Banco Luso Brasileiro S.A.</option>
                                                <option value="623">623 - Banco Pan S.A.</option>
                                                <option value="204">204 - Banco Bradesco Cartoes S.A.</option>
                                                <option value="479">479 - Banco ItauBank S.A.</option>
                                                <option value="456">456 - Banco MUFG Brasil S.A.</option>
                                                <option value="464">464 - Banco Sumitomo Mitsui Brasil S.A.</option>
                                                <option value="613">613 - Omni Banco S.A.</option>
                                                <option value="652">652 - Itaú Unibanco Holding Bm S.A.</option>
                                                <option value="653">653 - Banco Indusval S.A.</option>
                                                <option value="069">069 - Banco Crefisa S.A.</option>
                                                <option value="370">370 - Banco Mizuho S.A.</option>
                                                <option value="249">249 - Banco Investcred Unibanco S.A.</option>
                                                <option value="318">318 - Banco BMG S.A.</option>
                                                <option value="626">626 - Banco Ficsa S.A.</option>
                                                <option value="270">270 - Sagitur Cc Ltda</option>
                                                <option value="366">366 - Banco Societe Generale Brasil</option>
                                                <option value="113">113 - Magliano S.A.</option>
                                                <option value="131">131 - Tullett Prebon Brasil Cvc Ltda</option>
                                                <option value="011">011 - C.Suisse Hedging-Griffo Cv S.A (Credit Suisse)</option>
                                                <option value="611">611 - Banco Paulista</option>
                                                <option value="755">755 - Bofa Merrill Lynch Bm S.A.</option>
                                                <option value="089">089 - Ccr Reg Mogiana</option>
                                                <option value="643">643 - Banco Pine S.A.</option>
                                                <option value="140">140 - Easynvest – Título Cv S.A.</option>
                                                <option value="707">707 - Banco Daycoval S.A.</option>
                                                <option value="288">288 - Carol Dtvm Ltda</option>
                                                <option value="101">101 - Renascença Dtvm Ltda</option>
                                                <option value="487">487 - Deutsche Bank S.A (Banco Alemão)</option>
                                                <option value="233">233 - Banco Cifra S.A.</option>
                                                <option value="177">177 - Guide Investimentos S.A. Corretora de Valores</option>
                                                <option value="633">633 - Banco Rendimento S.A.</option>
                                                <option value="218">218 - Banco Bs2 S.A.</option>
                                                <option value="292">292 - BS2 Distribuidora De Títulos E Investimentos</option>
                                                <option value="169">169 - Banco Olé Bonsucesso Consignado S.A.</option>
                                                <option value="293">293 - Lastro Rdv Dtvm Ltda</option>
                                                <option value="285">285 - Frente Cc Ltda</option>
                                                <option value="080">080 - B&amp;T Cc Ltda</option>
                                                <option value="753">753 - Novo Banco Continental S.A Bm</option>
                                                <option value="222">222 - Banco Crédit Agricole Br S.A.</option>
                                                <option value="754">754 - Banco Sistema S.A.</option>
                                                <option value="098">098 - Credialiança Ccr</option>
                                                <option value="610">610 - Banco VR S.A.</option>
                                                <option value="712">712 - Banco Ourinvest S.A.</option>
                                                <option value="010">010 - CREDICOAMO CRÉDITO RURAL COOPERATIVA</option>
                                                <option value="283">283 - RB Capital Investimentos Dtvm Ltda</option>
                                                <option value="217">217 - Banco John Deere S.A.</option>
                                                <option value="117">117 - Advanced Cc Ltda</option>
                                                <option value="336">336 - Banco C6 S.A – C6 Bank</option>
                                                <option value="654">654 - Banco A.J. Renner S.A.</option>
                                                <option value="272">272 - AGK Corretora de Câmbio S.A.</option>
                                                <option value="330">330 - Banco Bari de Investimentos e Financiamentos S.A.</option>
                                                <option value="362">362 - CIELO S.A.</option>
                                                <option value="322">322 - Cooperativa de Crédito Rural de Abelardo Luz – Sulcredi/Crediluz</option>
                                                <option value="350">350 - Cooperativa De Crédito Rural De Pequenos Agricultores E Da Reforma Agrária Do Ce</option>
                                                <option value="091">091 - Central De Cooperativas De Economia E Crédito Mútuo Do Estado Do Rio Grande Do S</option>
                                                <option value="379">379 - COOPERFORTE – Cooperativa De Economia E Crédito Mútuo Dos Funcionários De Instit</option>
                                                <option value="378">378 - BBC LEASING S.A. – Arrendamento Mercantil</option>
                                                <option value="360">360 - TRINUS Capital Distribuidora De Títulos E Valores Mobiliários S.A.</option>
                                                <option value="084B">084B - UNIPRIME NORTE DO PARANÁ – COOPERATIVA DE CRÉDITO LTDA</option>
                                                <option value="387">387 - Banco Toyota do Brasil S.A.</option>
                                                <option value="326">326 - PARATI – CRÉDITO, FINANCIAMENTO E INVESTIMENTO S.A.</option>
                                                <option value="315">315 - PI Distribuidora de Títulos e Valores Mobiliários S.A.</option>
                                                <option value="307">307 - Terra Investimentos Distribuidora de Títulos e Valores Mobiliários Ltda.</option>
                                                <option value="296">296 - VISION S.A. CORRETORA DE CAMBIO</option>
                                                <option value="382">382 - FIDÚCIA SOCIEDADE DE CRÉDITO AO MICROEMPREENDEDOR E À EMPRESA DE PEQUENO PORTE L</option>
                                                <option value="097">097 - Credisis – Central de Cooperativas de Crédito Ltda.</option>
                                                <option value="016B">016B - COOPERATIVA DE CRÉDITO MÚTUO DOS DESPACHANTES DE TRÂNSITO DE SANTA CATARINA E RI</option>
                                                <option value="299">299 - SOROCRED&nbsp;&nbsp; CRÉDITO, FINANCIAMENTO E INVESTIMENTO S.A.</option>
                                                <option value="359">359 - ZEMA CRÉDITO, FINANCIAMENTO E INVESTIMENTO S/A</option>
                                                <option value="391">391 - COOPERATIVA DE CRÉDITO RURAL DE IBIAM – SULCREDI/IBIAM</option>
                                                <option value="368">368 - Banco CSF S.A.</option>
                                                <option value="259">259 - MONEYCORP BANCO DE CÂMBIO S.A.</option>
                                                <option value="364">364 - GERENCIANET S.A.</option>
                                                <option value="014B">014B - STATE STREET BRASIL S.A. – BANCO COMERCIAL</option>
                                                <option value="081">081 - Banco Seguro S.A.</option>
                                                <option value="384">384 - GLOBAL FINANÇAS SOCIEDADE DE CRÉDITO AO MICROEMPREENDEDOR E À EMPRESA DE PEQUENO</option>
                                                <option value="088">088 - BANCO RANDON S.A.</option>
                                                <option value="319">319 - OM DISTRIBUIDORA DE TÍTULOS E VALORES MOBILIÁRIOS LTDA</option>
                                                <option value="274">274 - MONEY PLUS SOCIEDADE DE CRÉDITO AO MICROEMPREENDEDOR E A EMPRESA DE PEQUENO PORT</option>
                                                <option value="095">095B- Travelex Banco de Câmbio S.A.</option>
                                                <option value="332">332 - Acesso Soluções de Pagamento S.A.</option>
                                                <option value="325">325 - Órama Distribuidora de Títulos e Valores Mobiliários S.A.</option>
                                                <option value="331">331 - Fram Capital Distribuidora de Títulos e Valores Mobiliários S.A.</option>
                                                <option value="396">396 - HUB PAGAMENTOS S.A.</option>
                                                <option value="309">309 - CAMBIONET CORRETORA DE CÂMBIO LTDA.</option>
                                                <option value="313">313 - AMAZÔNIA CORRETORA DE CÂMBIO LTDA.</option>
                                                <option value="377">377 - MS SOCIEDADE DE CRÉDITO AO MICROEMPREENDEDOR E À EMPRESA DE PEQUENO PORTE LTDA</option>
                                                <option value="321">321 - CREFAZ SOCIEDADE DE CRÉDITO AO MICROEMPREENDEDOR E A EMPRESA DE PEQUENO PORTE LT</option>
                                                <option value="383">383 - BOLETOBANCÁRIO.COM TECNOLOGIA DE PAGAMENTOS LTDA.</option>
                                                <option value="324">324 - CARTOS SOCIEDADE DE CRÉDITO DIRETO S.A.</option>
                                                <option value="380">380 - PICPAY SERVICOS S.A.</option>
                                                <option value="343">343 - FFA SOCIEDADE DE CRÉDITO AO MICROEMPREENDEDOR E À EMPRESA DE PEQUENO PORTE LTDA.</option>
                                                <option value="349">349 - AL5 S.A. CRÉDITO, FINANCIAMENTO E INVESTIMENTO</option>
                                                <option value="374">374 - REALIZE CRÉDITO, FINANCIAMENTO E INVESTIMENTO S.A.</option>
                                                <option value="352">352 - TORO CORRETORA DE TÍTULOS E VALORES MOBILIÁRIOS LTDA</option>
                                                <option value="329">329 - QI Sociedade de Crédito Direto S.A.</option>
                                                <option value="342">342 - Creditas Sociedade de Crédito Direto S.A.</option>
                                                <option value="397">397 - LISTO SOCIEDADE DE CRÉDITO DIRETO S.A.</option>
                                                <option value="355">355 - ÓTIMO SOCIEDADE DE CRÉDITO DIRETO S.A.</option>
                                                <option value="367">367 - VITREO DISTRIBUIDORA DE TÍTULOS E VALORES MOBILIÁRIOS S.A.</option>
                                                <option value="373">373 - UP.P SOCIEDADE DE EMPRÉSTIMO ENTRE PESSOAS S.A.</option>
                                                <option value="408">408 - BÔNUSCRED SOCIEDADE DE CRÉDITO DIRETO S.A.</option>
                                                <option value="404">404 - SUMUP SOCIEDADE DE CRÉDITO DIRETO S.A.</option>
                                                <option value="403">403 - CORA SOCIEDADE DE CRÉDITO DIRETO S.A.</option>
                                                <option value="306">306 - PORTOPAR DISTRIBUIDORA DE TITULOS E VALORES MOBILIARIOS LTDA.</option>
                                                <option value="174">174 - PEFISA S.A. – CRÉDITO, FINANCIAMENTO E INVESTIMENTO</option>
                                                <option value="354">354 - NECTON INVESTIMENTOS S.A. CORRETORA DE VALORES MOBILIÁRIOS E COMMODITIES</option>
                                                <option value="630">630 - Banco Smartbank S.A.</option>
                                                <option value="393">393 - Banco Volkswagen S.A.</option>
                                                <option value="390">390 - BANCO GM S.A.</option>
                                                <option value="381">381 - BANCO MERCEDES-BENZ DO BRASIL S.A.</option>
                                                <option value="626">626 - BANCO C6 CONSIGNADO S.A.</option>
                                                <option value="755">755 - Bank of America Merrill Lynch Banco Múltiplo S.A.</option>
                                                <option value="089B">089B - CREDISAN COOPERATIVA DE CRÉDITO</option>
                                                <option value="363">363 - SOCOPA SOCIEDADE CORRETORA PAULISTA S.A.</option>
                                                <option value="365">365 - SOLIDUS S.A. CORRETORA DE CAMBIO E VALORES MOBILIARIOS</option>
                                                <option value="281">281 - Cooperativa de Crédito Rural Coopavel</option>
                                                <option value="654">654 - BANCO DIGIMAIS S.A.</option>
                                                <option value="371">371 - WARREN CORRETORA DE VALORES MOBILIÁRIOS E CÂMBIO LTDA.</option>
                                                <option value="289">289 - DECYSEO CORRETORA DE CAMBIO LTDA.</option>
                                            </select>
                                            <input type="hidden" id="text-filter-status-id" name="banco" value="" required>
                                        </div>

                                        <div class="form-group">
                                            <label class="text-label">Agência</label>
                                            <input type="text" name="agencia" class="form-control" required>
                                        </div>

                                        <div class="row mb-3">
                                            <div class="col-md-8">
                                                <label class="text-label">Conta</label>
                                                <input type="text" name="conta" class="form-control" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="text-label">Dígito</label>
                                                <input type="text" name="digito" class="form-control" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="text-label">Tipo de Conta</label>
                                            <select class="form-control default-select" id="select-bank-acc-type-id">
                                                <option value="1" selected>Corrente</option>
                                                <option value="2">Poupança</option>
                                            </select>
                                            <input type="hidden" id="text-bank-acc-type-id" name="tipo-conta" value="1" required>
                                        </div>
                                        
                                        <hr class="divider mt-4 mb-4">
                                        
                                        <div class="form-group">
                                            <label class="text-label">Tipo de Chave</label>
                                            <select class="form-control default-select" id="select-bank-pix-type" name="tipo-chave">
                                                <option value="1">Telefone</option>
                                                <option value="2">CPF</option>
                                                <option value="3">CNPJ</option>
                                                <option value="4">Email</option>
                                                <option value="5">Aleatória</option>
                                            </select>
                                            <!--<input type="hidden" id="text-pix-type-id" name="tipo-chave" value="1">-->
                                        </div>

                                        <div class="form-group">
                                            <label class="text-label">Chave</label>
                                            <input type="text" id="pix-chave" name="chave-pix" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <button type="submit" id="SubmitButton" class="btn btn-success">Adicionar Conta</button>
                                        </div>

                                        </form>
                                    </div>
                            </div>
                        </div>
                </div>

                <div class="col-xl-8 col-xxl-7">
                    <div class="card">
                    
                        <div class="card-body">
                        <h4 class="text-muted">Contas Bancárias</h4>
                        <?php 
                            $get_added_accs = $conn->prepare('SELECT * FROM bank_account_list WHERE account_user_id = :user__id');
                            $get_added_accs->execute(array('user__id' => $user__id));
                        
                            if ($get_added_accs->rowCount() > 0){
                                
                        ?>
                            <table class="table" id="bank-accounts">
                                <thead>
                                    <tr>
                                        <th class="col-md-3" style="text-align: center;">Banco</th>
                                        <th class="col-md-3" style="text-align: center;">Conta</th>
                                        <th class="col-md-2" style="text-align: center;">Status</th>
                                        <th class="col-md-2" style="text-align: center;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($added_accs = $get_added_accs->fetch()){

                                        $bank_name = bankName($added_accs['account_bank']);

                                        if (@$added_accs['account_status'] == 2){
                                            $acc_status = '<span tilte="Conta Aprovada" class="badge badge-sm badge-pill badge-success">Aprovada</span>';
                                        } else if (@$added_accs['account_status'] == 0){
                                            $acc_status = '<span tilte="Conta Reprovada" class="badge badge-sm badge-pill badge-danger">Reprovada</span>';
                                        } else {
                                            $acc_status = '<span tilte="Conta Pendente de Revisão" class="badge badge-sm badge-pill badge-warning">Pendente</span>';
                                        }

                                        // if (@$added_accs['account_pix_key'] != NULL && @$added_accs['account_pix_type'] != NULL){
                                        //     $has_pix = '<i tilte="Chave PIX Adicionada" class="fas fa-check-circle"></i>';
                                        // } else {
                                        //     $has_pix = '<i tilte="Sem Chave PIX Adicionada" class="fas fa-times-circle"></i></span>';
                                        // }

                                    ?>
                                    <tr>
                                        <td style="text-align: center;"><?php echo $bank_name; ?></td>
                                        <td style="text-align: center;"><?php echo $added_accs['account_number']; ?></td>
                                        <td style="text-align: center;"><?php echo $acc_status; ?></td>
                                        <td style="text-align: center;">
                                            <button type="button" class="btn btn-link btn-sm bank-account-details" data-toggle="modal" data-id="<?php echo $added_accs['account_id']; ?>" data-target="#ModalDetalhesConta">Detalhes</button>
                                        </td>
                                    </tr>
                                    <?php

                                    }
                                    ?>
                                </tbody>
                            </table>
                            <?php
                            } else {
                            ?>

                                <div class="alert alert-secondary alert-light alert-dismissible fade show">
                                    <small>Nenhuma conta ainda.</small>
                                </div>

                            <?php
                            }
                            ?>
                            <!-- <br>
                            <h4 class="text-muted mt-3">Chaves PIX</h4> -->
                            <?php 
                            $verify_added_pixkeys = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = "added_pixkeys"');
                            $verify_added_pixkeys->execute(array('user__id' => $user__id));
                        
                            if ($verify_added_pixkeys->rowCount() == 1){
                                
                                $added_pixkeys = $verify_added_pixkeys->fetch();
                                $added_pixkeys = $added_pixkeys['meta_value'];

                                $this_pix = 1;
                        ?>
                            <!-- <table class="table" id="bank-pixkeys">
                                <thead>
                                    <tr>
                                        <th class="col-md-3" style="text-align: center;">Banco</th>
                                        <th class="col-md-3" style="text-align: center;">Tipo</th>
                                        <th class="col-md-3" style="text-align: center;">Chave</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($this_pix <= $added_pixkeys){
                                        
                                        $meta_key_bank = "PIX_U" . $user__id . "-A" . $this_pix . "_BANK";
                                        $meta_key_key = "PIX_U" . $user__id . "-A" . $this_pix . "_KEY";
                                        $meta_key_type = "PIX_U" . $user__id . "-A" . $this_pix . "_TYPE";
                                        
                                        $get_bank = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = :meta_key');
                                        $get_bank->execute(array('meta_key' => $meta_key_bank, 'user__id' => $user__id));
                                        $get_bank = $get_bank->fetch();

                                        $bank_name = bankName($get_bank['meta_value']);

                                        $get_account = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = :meta_key');
                                        $get_account->execute(array('meta_key' => $meta_key_key, 'user__id' => $user__id));
                                        $get_account = $get_account->fetch();

                                        $get_type = $conn->prepare('SELECT meta_value FROM users_meta WHERE user__id = :user__id AND meta_key = :meta_key');
                                        $get_type->execute(array('meta_key' => $meta_key_type, 'user__id' => $user__id));
                                        $get_type = $get_type->fetch();

                                    ?>
                                    <tr>
                                        <td style="text-align: center;"><?php echo $bank_name; ?></td>
                                        <td style="text-align: center;"><?php echo $get_type['meta_value']; ?></td>
                                        <td style="text-align: center;"><?php echo $get_account['meta_value']; ?></td>
                                    </tr>
                                    <?php

                                    $this_acc = $this_acc+ 1;

                                    }
                                    ?>
                                </tbody>
                            </table> -->
                            <?php
                            } else {
                            ?>
<!-- 
                                <div class="alert alert-secondary alert-light alert-dismissible fade show">
                                    <small>Nenhuma chave ainda.</small>
                                </div> -->

                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>

            </div>
    </div>

    <div class="modal fade" id="ModalDetalhesConta" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header center text-center d-block">
                <h5 class="modal-title center text-center">Detalhes da Conta Bancária</h5>
                </div>
                <div class="card-body">
                    <p class="acc-details-json" id="acc-details-status">Conta Pendente de Revisão</p>
                    <div class="row mb-1">
                        <div class="col-sm-12">
                            <spam class="mt-2"><small>Banco</small></spam>
                            <h5 class="mb-0 acc-details-json" id="acc-details-bank"></h5>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-sm-12">
                            <spam class="mt-2"><small>Agência</small></spam>
                            <h5 class="mb-0 acc-details-json" id="acc-details-agency"></h5>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-sm-12">
                            <spam class="mt-2"><small>Conta</small></spam>
                            <h5 class="mb-0 acc-details-json" id="acc-details-number"></h5>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-sm-12">
                            <spam class="mt-2"><small>Tipo de Conta</small></spam>
                            <h5 class="mb-0 acc-details-json" id="acc-details-type"></h5>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-sm-12">
                            <spam class="mt-2"><small>Chave Pix (<spam class="acc-details-json" id="acc-details-pix-type"></spam>)</small></spam>
                            <h5 class="mb-0 acc-details-json" id="acc-details-pix-key"></h5>
                        </div>
                    </div>

                    <div class="alert alert-danger fade show mt-4" id="disapproval-justification-div">
                        <small><b>Motivo da Reprovação:</b></small><br>
                        <p class="mb-0 acc-details-json" id="acc-details-justification"></p>
                        <a href="#" class="badge badge-sm badge-danger mt-3" id="change-acc-details" data-id="" data-toggle="modal" data-target="#ModalAlterarConta">Alterar Dados da Conta</a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ModalAlterarConta" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header center text-center d-block">
                    <h4 class="">Alterar Dados da Conta</h4>
                </div>

                    <div class="card-body">
                        <!-- FORM ADD ACC -->
                        <div class="tab-pane fade show active" id="conta" role="tabpanel" aria-labelledby="home-tab">
                            <div class="alert alert-warning alert-dismissible fade show">
                                <small>O documento (CPF ou CNPJ) do titular da conta bancária <strong>precisa ser igual</strong> ao documento do responsável pela conta Logzz.</small>
                            </div>
                            
                            <div class="form-group">
                                <label class="text-label">Documento</label>
                            <?php
                                $get_user_doc = $conn->prepare('SELECT company_doc FROM users WHERE user__id = :user__id');
                                $get_user_doc->execute(array('user__id' => $user__id));
                                $user_doc = $get_user_doc->fetch();
                            ?>
                                <input type="text" name="doc" class="form-control text-muted bg-light" readonly value="<?php echo $user_doc[0]; ?>">
                            </div>

                            <form id="UpdateBankAccForm" action="add-bank-account" method="POST">
                            <input type="hidden" name="action" value="update-bank-account">

                            <div class="form-group">
                                <label class="text-label">Banco</label>
                                <select class="form-control default-select" id="select-filter-status-id-u" data-live-search="true" style="max-height: 200px">
                                    <option selected disabled>Banco</option>
                                    <option value="001">001 - Banco Do Brasil S.A (BB)</option>
                                    <option value="237">237 - Bradesco S.A.</option>
                                    <option value="335">335 - Banco Digio S.A.</option>
                                    <option value="260">260 - Nu Pagamentos S.A (Nubank)</option>
                                    <option value="290">290 - PagSeguro Internet S.A.</option>
                                    <option value="323">323 - Mercado Pago – Conta Do Mercado Livre</option>
                                    <option value="237B">237B - Next Bank (Mesmo Código Do Bradesco)</option>
                                    <option value="637">637 - Banco Sofisa S.A (Sofisa Direto)</option>
                                    <option value="077">077 - Banco Inter S.A.</option>
                                    <option value="341">341 - Itaú Unibanco S.A.</option>
                                    <option value="104">104 - Caixa Econômica Federal (CEF)</option>
                                    <option value="033">033 - Banco Santander Brasil S.A.</option>
                                    <option value="212">212 - Banco Original S.A.</option>
                                    <option value="756">756 - Bancoob – Banco Cooperativo Do Brasil S.A.</option>
                                    <option value="655">655 - Banco Votorantim S.A.</option>
                                    <option value="655">655 - Neon Pagamentos S.A (Memso Código Do Banco Votorantim)</option>
                                    <option value="041">041 - Banrisul – Banco Do Estado Do Rio Grande Do Sul S.A.</option>
                                    <option value="389">389 - Banco Mercantil Do Brasil S.A.</option>
                                    <option value="422">422 - Banco Safra S.A.</option>
                                    <option value="070">070 - BRB – Banco De Brasília S.A.</option>
                                    <option value="136">136 - Unicred Cooperativa LTDA</option>
                                    <option value="741">741 - Banco Ribeirão Preto S.A.</option>
                                    <option value="739">739 - Banco Cetelem S.A.</option>
                                    <option value="743">743 - Banco Semear S.A.</option>
                                    <option value="100">100 - Planner Corretora De Valores S.A.</option>
                                    <option value="096">096 - Banco B3 S.A.</option>
                                    <option value="747">747 - Banco Rabobank Internacional Do Brasil S.A.</option>
                                    <option value="748">748 - Banco Cooperativa Sicredi S.A.</option>
                                    <option value="752">752 - Banco BNP Paribas Brasil S.A.</option>
                                    <option value="091B">091B - Unicred Central Rs</option>
                                    <option value="399">399 - Kirton Bank S.A. – Banco Múltiplo</option>
                                    <option value="108">108 - Portocred S.A.</option>
                                    <option value="757">757 - Banco Keb Hana Do Brasil S.A.</option>
                                    <option value="102">102 - XP Investimentos S.A.</option>
                                    <option value="348">348 - Banco XP S/A</option>
                                    <option value="340">340 - Super Pagamentos S/A (Superdital)</option>
                                    <option value="084">084 - Uniprime Norte Do Paraná</option>
                                    <option value="180">180 - Cm Capital Markets Cctvm Ltda</option>
                                    <option value="066">066 - Banco Morgan Stanley S.A.</option>
                                    <option value="015">015 - UBS Brasil Cctvm S.A.</option>
                                    <option value="143">143 - Treviso Cc S.A.</option>
                                    <option value="062">062 - Hipercard Banco Múltiplo S.A.</option>
                                    <option value="074">074 - Banco J. Safra S.A.</option>
                                    <option value="099">099 - Uniprime Central Ccc Ltda</option>
                                    <option value="025">025 - Banco Alfa S.A.</option>
                                    <option value="075">075 - Bco Abn Amro S.A.</option>
                                    <option value="040">040 - Banco Cargill S.A.</option>
                                    <option value="190">190 - Servicoop</option>
                                    <option value="063">063 - Banco Bradescard</option>
                                    <option value="191">191 - Nova Futura Ctvm Ltda</option>
                                    <option value="064">064 - Goldman Sachs Do Brasil Bm S.A.</option>
                                    <option value="097B">097B - Ccc Noroeste Brasileiro Ltda</option>
                                    <option value="016">016 - Ccm Desp Trâns Sc E Rs</option>
                                    <option value="012">012 - Banco Inbursa</option>
                                    <option value="003">003 - Banco Da Amazônia S.A.</option>
                                    <option value="060">060 - Confidence Cc S.A.</option>
                                    <option value="037">037 - Banco Do Estado Do Pará S.A.</option>
                                    <option value="159">159 - Casa do Crédito S.A.</option>
                                    <option value="172">172 - Albatross Ccv S.A.</option>
                                    <option value="085">085 - Cooperativa Central de Créditos – Ailos</option>
                                    <option value="114">114 - Central Cooperativa De Crédito no Estado do Espírito Santo</option>
                                    <option value="036">036 - Banco Bradesco BBI S.A.</option>
                                    <option value="394">394 - Banco Bradesco Financiamentos S.A.</option>
                                    <option value="004">004 - Banco Do Nordeste Do Brasil S.A.</option>
                                    <option value="320">320 - China Construction Bank – Ccb Brasil S.A.</option>
                                    <option value="189">189 - Hs Financeira</option>
                                    <option value="105">105 - Lecca Cfi S.A.</option>
                                    <option value="076">076 - Banco KDB Brasil S.A.</option>
                                    <option value="082">082 - Banco Topázio S.A.</option>
                                    <option value="286">286 - Cooperativa de Crédito Rural de De Ouro</option>
                                    <option value="093">093 - PóloCred Scmepp Ltda</option>
                                    <option value="273">273 - Ccr De São Miguel Do Oeste</option>
                                    <option value="157">157 - Icap Do Brasil Ctvm Ltda</option>
                                    <option value="183">183 - Socred S.A.</option>
                                    <option value="014">014 - Natixis Brasil S.A.</option>
                                    <option value="130">130 - Caruana Scfi</option>
                                    <option value="127">127 - Codepe Cvc S.A.</option>
                                    <option value="079">079 - Banco Original Do Agronegócio S.A.</option>
                                    <option value="081B">081B - Bbn Banco Brasileiro De Negocios S.A.</option>
                                    <option value="118">118 - Standard Chartered Bi S.A.</option>
                                    <option value="133">133 - Cresol Confederação</option>
                                    <option value="121">121 - Banco Agibank S.A.</option>
                                    <option value="083">083 - Banco Da China Brasil S.A.</option>
                                    <option value="138">138 - Get Money Cc Ltda</option>
                                    <option value="024">024 - Banco Bandepe S.A.</option>
                                    <option value="095">095 - Banco Confidence De Câmbio S.A.</option>
                                    <option value="094">094 - Banco Finaxis</option>
                                    <option value="276">276 - Senff S.A.</option>
                                    <option value="137">137 - Multimoney Cc Ltda</option>
                                    <option value="092">092 - BRK S.A.</option>
                                    <option value="047">047 - Banco do Estado de Sergipe S.A.</option>
                                    <option value="144">144 - Bexs Banco De Cambio S.A.</option>
                                    <option value="126">126 - BR Partners Banco de Investimento S.A.</option>
                                    <option value="301">301 - BPP Instituição De Pagamentos S.A.</option>
                                    <option value="173">173 - BRL Trust Dtvm Sa</option>
                                    <option value="119">119 - Banco Western Union do Brasil S.A.</option>
                                    <option value="254">254 - Paraná Banco S.A.</option>
                                    <option value="268">268 - Barigui Companhia Hipotecária</option>
                                    <option value="107">107 - Banco Bocom BBM S.A.</option>
                                    <option value="412">412 - Banco Capital S.A.</option>
                                    <option value="124">124 - Banco Woori Bank Do Brasil S.A.</option>
                                    <option value="149">149 - Facta S.A. Cfi</option>
                                    <option value="197">197 - Stone Pagamentos S.A.</option>
                                    <option value="142">142 - Broker Brasil Cc Ltda</option>
                                    <option value="389">389 - Banco Mercantil Do Brasil S.A.</option>
                                    <option value="184">184 - Banco Itaú BBA S.A.</option>
                                    <option value="634">634 - Banco Triangulo S.A (Banco Triângulo)</option>
                                    <option value="545">545 - Senso Ccvm S.A.</option>
                                    <option value="132">132 - ICBC do Brasil Bm S.A.</option>
                                    <option value="298">298 - Vip’s Cc Ltda</option>
                                    <option value="129">129 - UBS Brasil Bi S.A.</option>
                                    <option value="128">128 - Ms Bank S.A Banco De Câmbio</option>
                                    <option value="194">194 - Parmetal Dtvm Ltda</option>
                                    <option value="310">310 - VORTX Dtvm Ltda</option>
                                    <option value="163">163 - Commerzbank Brasil S.A Banco Múltiplo</option>
                                    <option value="280">280 - Avista S.A.</option>
                                    <option value="146">146 - Guitta Cc Ltda</option>
                                    <option value="279">279 - Ccr De Primavera Do Leste</option>
                                    <option value="182">182 - Dacasa Financeira S/A</option>
                                    <option value="278">278 - Genial Investimentos Cvm S.A.</option>
                                    <option value="271">271 - Ib Cctvm Ltda</option>
                                    <option value="021">021 - Banco Banestes S.A.</option>
                                    <option value="246">246 - Banco Abc Brasil S.A.</option>
                                    <option value="751">751 - Scotiabank Brasil</option>
                                    <option value="208">208 - Banco BTG Pactual S.A.</option>
                                    <option value="746">746 - Banco Modal S.A.</option>
                                    <option value="241">241 - Banco Classico S.A.</option>
                                    <option value="612">612 - Banco Guanabara S.A.</option>
                                    <option value="604">604 - Banco Industrial Do Brasil S.A.</option>
                                    <option value="505">505 - Banco Credit Suisse (Brl) S.A.</option>
                                    <option value="196">196 - Banco Fair Cc S.A.</option>
                                    <option value="300">300 - Banco La Nacion Argentina</option>
                                    <option value="477">477 - Citibank N.A.</option>
                                    <option value="266">266 - Banco Cedula S.A.</option>
                                    <option value="122">122 - Banco Bradesco BERJ S.A.</option>
                                    <option value="376">376 - Banco J.P. Morgan S.A.</option>
                                    <option value="473">473 - Banco Caixa Geral Brasil S.A.</option>
                                    <option value="745">745 - Banco Citibank S.A.</option>
                                    <option value="120">120 - Banco Rodobens S.A.</option>
                                    <option value="265">265 - Banco Fator S.A.</option>
                                    <option value="007">007 - BNDES (Banco Nacional Do Desenvolvimento Social)</option>
                                    <option value="188">188 - Ativa S.A Investimentos</option>
                                    <option value="134">134 - BGC Liquidez Dtvm Ltda</option>
                                    <option value="641">641 - Banco Alvorada S.A.</option>
                                    <option value="029">029 - Banco Itaú Consignado S.A.</option>
                                    <option value="243">243 - Banco Máxima S.A.</option>
                                    <option value="078">078 - Haitong Bi Do Brasil S.A.</option>
                                    <option value="111">111 - Banco Oliveira Trust Dtvm S.A.</option>
                                    <option value="017">017 - Bny Mellon Banco S.A.</option>
                                    <option value="174B">174B - Pernambucanas Financ S.A.</option>
                                    <option value="495">495 - La Provincia Buenos Aires Banco</option>
                                    <option value="125">125 - Brasil Plural S.A Banco</option>
                                    <option value="488">488 - Jpmorgan Chase Bank</option>
                                    <option value="065">065 - Banco Andbank S.A.</option>
                                    <option value="492">492 - Ing Bank N.V.</option>
                                    <option value="250">250 - Banco Bcv</option>
                                    <option value="145">145 - Levycam Ccv Ltda</option>
                                    <option value="494">494 - Banco Rep Oriental Uruguay</option>
                                    <option value="253">253 - Bexs Cc S.A.</option>
                                    <option value="269">269 - Hsbc Banco De Investimento</option>
                                    <option value="213">213 - Bco Arbi S.A.</option>
                                    <option value="139">139 - Intesa Sanpaolo Brasil S.A.</option>
                                    <option value="018">018 - Banco Tricury S.A.</option>
                                    <option value="630">630 - Banco Intercap S.A.</option>
                                    <option value="224">224 - Banco Fibra S.A.</option>
                                    <option value="600">600 - Banco Luso Brasileiro S.A.</option>
                                    <option value="623">623 - Banco Pan S.A.</option>
                                    <option value="204">204 - Banco Bradesco Cartoes S.A.</option>
                                    <option value="479">479 - Banco ItauBank S.A.</option>
                                    <option value="456">456 - Banco MUFG Brasil S.A.</option>
                                    <option value="464">464 - Banco Sumitomo Mitsui Brasil S.A.</option>
                                    <option value="613">613 - Omni Banco S.A.</option>
                                    <option value="652">652 - Itaú Unibanco Holding Bm S.A.</option>
                                    <option value="653">653 - Banco Indusval S.A.</option>
                                    <option value="069">069 - Banco Crefisa S.A.</option>
                                    <option value="370">370 - Banco Mizuho S.A.</option>
                                    <option value="249">249 - Banco Investcred Unibanco S.A.</option>
                                    <option value="318">318 - Banco BMG S.A.</option>
                                    <option value="626">626 - Banco Ficsa S.A.</option>
                                    <option value="270">270 - Sagitur Cc Ltda</option>
                                    <option value="366">366 - Banco Societe Generale Brasil</option>
                                    <option value="113">113 - Magliano S.A.</option>
                                    <option value="131">131 - Tullett Prebon Brasil Cvc Ltda</option>
                                    <option value="011">011 - C.Suisse Hedging-Griffo Cv S.A (Credit Suisse)</option>
                                    <option value="611">611 - Banco Paulista</option>
                                    <option value="755">755 - Bofa Merrill Lynch Bm S.A.</option>
                                    <option value="089">089 - Ccr Reg Mogiana</option>
                                    <option value="643">643 - Banco Pine S.A.</option>
                                    <option value="140">140 - Easynvest – Título Cv S.A.</option>
                                    <option value="707">707 - Banco Daycoval S.A.</option>
                                    <option value="288">288 - Carol Dtvm Ltda</option>
                                    <option value="101">101 - Renascença Dtvm Ltda</option>
                                    <option value="487">487 - Deutsche Bank S.A (Banco Alemão)</option>
                                    <option value="233">233 - Banco Cifra S.A.</option>
                                    <option value="177">177 - Guide Investimentos S.A. Corretora de Valores</option>
                                    <option value="633">633 - Banco Rendimento S.A.</option>
                                    <option value="218">218 - Banco Bs2 S.A.</option>
                                    <option value="292">292 - BS2 Distribuidora De Títulos E Investimentos</option>
                                    <option value="169">169 - Banco Olé Bonsucesso Consignado S.A.</option>
                                    <option value="293">293 - Lastro Rdv Dtvm Ltda</option>
                                    <option value="285">285 - Frente Cc Ltda</option>
                                    <option value="080">080 - B&amp;T Cc Ltda</option>
                                    <option value="753">753 - Novo Banco Continental S.A Bm</option>
                                    <option value="222">222 - Banco Crédit Agricole Br S.A.</option>
                                    <option value="754">754 - Banco Sistema S.A.</option>
                                    <option value="098">098 - Credialiança Ccr</option>
                                    <option value="610">610 - Banco VR S.A.</option>
                                    <option value="712">712 - Banco Ourinvest S.A.</option>
                                    <option value="010">010 - CREDICOAMO CRÉDITO RURAL COOPERATIVA</option>
                                    <option value="283">283 - RB Capital Investimentos Dtvm Ltda</option>
                                    <option value="217">217 - Banco John Deere S.A.</option>
                                    <option value="117">117 - Advanced Cc Ltda</option>
                                    <option value="336">336 - Banco C6 S.A – C6 Bank</option>
                                    <option value="654">654 - Banco A.J. Renner S.A.</option>
                                    <option value="272">272 - AGK Corretora de Câmbio S.A.</option>
                                    <option value="330">330 - Banco Bari de Investimentos e Financiamentos S.A.</option>
                                    <option value="362">362 - CIELO S.A.</option>
                                    <option value="322">322 - Cooperativa de Crédito Rural de Abelardo Luz – Sulcredi/Crediluz</option>
                                    <option value="350">350 - Cooperativa De Crédito Rural De Pequenos Agricultores E Da Reforma Agrária Do Ce</option>
                                    <option value="091">091 - Central De Cooperativas De Economia E Crédito Mútuo Do Estado Do Rio Grande Do S</option>
                                    <option value="379">379 - COOPERFORTE – Cooperativa De Economia E Crédito Mútuo Dos Funcionários De Instit</option>
                                    <option value="378">378 - BBC LEASING S.A. – Arrendamento Mercantil</option>
                                    <option value="360">360 - TRINUS Capital Distribuidora De Títulos E Valores Mobiliários S.A.</option>
                                    <option value="084B">084B - UNIPRIME NORTE DO PARANÁ – COOPERATIVA DE CRÉDITO LTDA</option>
                                    <option value="387">387 - Banco Toyota do Brasil S.A.</option>
                                    <option value="326">326 - PARATI – CRÉDITO, FINANCIAMENTO E INVESTIMENTO S.A.</option>
                                    <option value="315">315 - PI Distribuidora de Títulos e Valores Mobiliários S.A.</option>
                                    <option value="307">307 - Terra Investimentos Distribuidora de Títulos e Valores Mobiliários Ltda.</option>
                                    <option value="296">296 - VISION S.A. CORRETORA DE CAMBIO</option>
                                    <option value="382">382 - FIDÚCIA SOCIEDADE DE CRÉDITO AO MICROEMPREENDEDOR E À EMPRESA DE PEQUENO PORTE L</option>
                                    <option value="097">097 - Credisis – Central de Cooperativas de Crédito Ltda.</option>
                                    <option value="016B">016B - COOPERATIVA DE CRÉDITO MÚTUO DOS DESPACHANTES DE TRÂNSITO DE SANTA CATARINA E RI</option>
                                    <option value="299">299 - SOROCRED&nbsp;&nbsp; CRÉDITO, FINANCIAMENTO E INVESTIMENTO S.A.</option>
                                    <option value="359">359 - ZEMA CRÉDITO, FINANCIAMENTO E INVESTIMENTO S/A</option>
                                    <option value="391">391 - COOPERATIVA DE CRÉDITO RURAL DE IBIAM – SULCREDI/IBIAM</option>
                                    <option value="368">368 - Banco CSF S.A.</option>
                                    <option value="259">259 - MONEYCORP BANCO DE CÂMBIO S.A.</option>
                                    <option value="364">364 - GERENCIANET S.A.</option>
                                    <option value="014B">014B - STATE STREET BRASIL S.A. – BANCO COMERCIAL</option>
                                    <option value="081">081 - Banco Seguro S.A.</option>
                                    <option value="384">384 - GLOBAL FINANÇAS SOCIEDADE DE CRÉDITO AO MICROEMPREENDEDOR E À EMPRESA DE PEQUENO</option>
                                    <option value="088">088 - BANCO RANDON S.A.</option>
                                    <option value="319">319 - OM DISTRIBUIDORA DE TÍTULOS E VALORES MOBILIÁRIOS LTDA</option>
                                    <option value="274">274 - MONEY PLUS SOCIEDADE DE CRÉDITO AO MICROEMPREENDEDOR E A EMPRESA DE PEQUENO PORT</option>
                                    <option value="095">095B- Travelex Banco de Câmbio S.A.</option>
                                    <option value="332">332 - Acesso Soluções de Pagamento S.A.</option>
                                    <option value="325">325 - Órama Distribuidora de Títulos e Valores Mobiliários S.A.</option>
                                    <option value="331">331 - Fram Capital Distribuidora de Títulos e Valores Mobiliários S.A.</option>
                                    <option value="396">396 - HUB PAGAMENTOS S.A.</option>
                                    <option value="309">309 - CAMBIONET CORRETORA DE CÂMBIO LTDA.</option>
                                    <option value="313">313 - AMAZÔNIA CORRETORA DE CÂMBIO LTDA.</option>
                                    <option value="377">377 - MS SOCIEDADE DE CRÉDITO AO MICROEMPREENDEDOR E À EMPRESA DE PEQUENO PORTE LTDA</option>
                                    <option value="321">321 - CREFAZ SOCIEDADE DE CRÉDITO AO MICROEMPREENDEDOR E A EMPRESA DE PEQUENO PORTE LT</option>
                                    <option value="383">383 - BOLETOBANCÁRIO.COM TECNOLOGIA DE PAGAMENTOS LTDA.</option>
                                    <option value="324">324 - CARTOS SOCIEDADE DE CRÉDITO DIRETO S.A.</option>
                                    <option value="380">380 - PICPAY SERVICOS S.A.</option>
                                    <option value="343">343 - FFA SOCIEDADE DE CRÉDITO AO MICROEMPREENDEDOR E À EMPRESA DE PEQUENO PORTE LTDA.</option>
                                    <option value="349">349 - AL5 S.A. CRÉDITO, FINANCIAMENTO E INVESTIMENTO</option>
                                    <option value="374">374 - REALIZE CRÉDITO, FINANCIAMENTO E INVESTIMENTO S.A.</option>
                                    <option value="352">352 - TORO CORRETORA DE TÍTULOS E VALORES MOBILIÁRIOS LTDA</option>
                                    <option value="329">329 - QI Sociedade de Crédito Direto S.A.</option>
                                    <option value="342">342 - Creditas Sociedade de Crédito Direto S.A.</option>
                                    <option value="397">397 - LISTO SOCIEDADE DE CRÉDITO DIRETO S.A.</option>
                                    <option value="355">355 - ÓTIMO SOCIEDADE DE CRÉDITO DIRETO S.A.</option>
                                    <option value="367">367 - VITREO DISTRIBUIDORA DE TÍTULOS E VALORES MOBILIÁRIOS S.A.</option>
                                    <option value="373">373 - UP.P SOCIEDADE DE EMPRÉSTIMO ENTRE PESSOAS S.A.</option>
                                    <option value="408">408 - BÔNUSCRED SOCIEDADE DE CRÉDITO DIRETO S.A.</option>
                                    <option value="404">404 - SUMUP SOCIEDADE DE CRÉDITO DIRETO S.A.</option>
                                    <option value="403">403 - CORA SOCIEDADE DE CRÉDITO DIRETO S.A.</option>
                                    <option value="306">306 - PORTOPAR DISTRIBUIDORA DE TITULOS E VALORES MOBILIARIOS LTDA.</option>
                                    <option value="174">174 - PEFISA S.A. – CRÉDITO, FINANCIAMENTO E INVESTIMENTO</option>
                                    <option value="354">354 - NECTON INVESTIMENTOS S.A. CORRETORA DE VALORES MOBILIÁRIOS E COMMODITIES</option>
                                    <option value="630">630 - Banco Smartbank S.A.</option>
                                    <option value="393">393 - Banco Volkswagen S.A.</option>
                                    <option value="390">390 - BANCO GM S.A.</option>
                                    <option value="381">381 - BANCO MERCEDES-BENZ DO BRASIL S.A.</option>
                                    <option value="626">626 - BANCO C6 CONSIGNADO S.A.</option>
                                    <option value="755">755 - Bank of America Merrill Lynch Banco Múltiplo S.A.</option>
                                    <option value="089B">089B - CREDISAN COOPERATIVA DE CRÉDITO</option>
                                    <option value="363">363 - SOCOPA SOCIEDADE CORRETORA PAULISTA S.A.</option>
                                    <option value="365">365 - SOLIDUS S.A. CORRETORA DE CAMBIO E VALORES MOBILIARIOS</option>
                                    <option value="281">281 - Cooperativa de Crédito Rural Coopavel</option>
                                    <option value="654">654 - BANCO DIGIMAIS S.A.</option>
                                    <option value="371">371 - WARREN CORRETORA DE VALORES MOBILIÁRIOS E CÂMBIO LTDA.</option>
                                    <option value="289">289 - DECYSEO CORRETORA DE CAMBIO LTDA.</option>
                                </select>
                                <input type="hidden" id="text-filter-status-id-u" name="banco" value="" required>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Agência</label>
                                <input type="text" name="agencia" class="form-control" required>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label class="text-label">Conta</label>
                                    <input type="text" name="conta" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="text-label">Dígito</label>
                                    <input type="text" name="digito" class="form-control" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="text-label">Tipo de Conta</label>
                                <select class="form-control default-select" id="select-bank-acc-type-id">
                                    <option value="1" selected>Corrente</option>
                                    <option value="2">Poupança</option>
                                </select>
                                <input type="hidden" id="text-bank-acc-type-id" name="tipo-conta" value="1" required>
                            </div>
                            
                            <hr class="divider mt-4 mb-4">
                            
                            <div class="form-group">
                                <label class="text-label">Tipo de Chave</label>
                                <select class="form-control default-select" id="select-bank-pix-type-id">
                                    <option value="1">Telefone</option>
                                    <option value="2">CPF</option>
                                    <option value="3">CNPJ</option>
                                    <option value="4">Email</option>
                                    <option value="5">Aleatória</option>
                                </select>
                                <input type="hidden" id="text-pix-type-id" name="tipo-chave" value="1">
                            </div>

                            <div class="form-group">
                                <label class="text-label">Chave</label>
                                <input type="text" id="pix-key" name="chave-pix" class="form-control">
                            </div>

                            <div class="form-group">
                                <button type="submit" id="SubmitButton" class="btn btn-success">Salvar Dados da Conta</button>
                            </div>

                            </form>
                        </div>
                    </div>
            </div>
        </div>
    </div>

<?php
require_once(dirname(__FILE__) . '/../../includes/layout/default/default-footer.php');
?>