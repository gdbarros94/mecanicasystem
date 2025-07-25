<?php 
if(isset($_GET['id'])){
    $qry = $conn->query("SELECT * FROM `transaction_list` where id = '{$_GET['id']}' ");
    if($qry->num_rows > 0){
        $res = $qry->fetch_array();
        foreach($res as $k => $v){
            if(!is_numeric($k)){
                $$k = $v;
            }
        }
        if(isset($mechanic_id) && is_numeric($mechanic_id)){
            $mechanic = $conn->query("SELECT concat(firstname,' ', coalesce(concat(middlename,' '),''), lastname) as `name` FROM `mechanic_list` where id = '{$mechanic_id}' ");
            if($mechanic->num_rows > 0){
                $mechanic_name = $mechanic->fetch_array()['name'];
            }
        }
        if(isset($user_id) && is_numeric($user_id)){
            $user = $conn->query("SELECT concat(firstname,' ', lastname) as `name` FROM `users` where id = '{$user_id}' ");
            if($user->num_rows > 0){
                $user_name = $user->fetch_array()['name'];
            }
        }
    }else{
        echo '<script> alert("Unknown Transaction\'s ID."); location.replace("./?page=transactions"); </script>';
    }
}else{
    echo '<script> alert("Transaction\'s ID is required to access the page."); location.replace("./?page=transactions"); </script>';
}
?>
<div class="content py-3">
    <div class="card card-outline card-navy rounded-0 shadow">
        <div class="card-header">
            <h4 class="card-title">Detalhes da Transação: <b><?= isset($code) ? $code : "" ?></b></h4>
            <div class="card-tools">
                <a href="./?page=transactions" class="btn btn-default border btn-sm"><i class="fa fa-angle-left"></i> Voltar a Lista</a>
            </div>
        </div>
        <div class="card-body">
            <div class="container-fluid" id="printout">
                <div class="row mb-0">
                    <div class="col-3 py-1 px-2 border border-navy bg-gradient-navy mb-0"><b>Código da Transação</b></div>
                    <div class="col-9 py-1 px-2 border mb-0"><?= isset($code) ? $code : '' ?></div>
                    <div class="col-3 py-1 px-2 border border-navy bg-gradient-navy mb-0"><b>Nome do Cliente</b></div>
                    <div class="col-9 py-1 px-2 border mb-0"><?= isset($client_name) ? $client_name : '' ?></div>
                    <div class="col-3 py-1 px-2 border border-navy bg-gradient-navy mb-0"><b>Telefone / WhatsApp</b></div>
                    <div class="col-9 py-1 px-2 border mb-0"><?= isset($contact) ? $contact : '' ?></div>
                    <div class="col-3 py-1 px-2 border border-navy bg-gradient-navy mb-0"><b>E-mail</b></div>
                    <div class="col-9 py-1 px-2 border mb-0"><?= isset($email) ? $email : '' ?></div>
                    <div class="col-3 py-1 px-2 border border-navy bg-gradient-navy mb-0"><b>Endereço</b></div>
                    <div class="col-9 py-1 px-2 border mb-0"><?= isset($address) ? $address : '' ?></div>
                    <div class="col-3 py-1 px-2 border border-navy bg-gradient-navy mb-0"><b>Status</b></div>
                    <div class="col-9 py-1 px-2 border mb-0">
                        <?php 
                        $status = isset($status) ? $status : '';
                        switch($status){
                            case 0:
                                echo '<span class="badge badge-default border px-3 rounded-pill">Pendente</span>';
                                break;
                            case 1:
                                echo '<span class="badge badge-primary px-3 rounded-pill">Em Progresso</span>';
                                break;
                            case 2:
                                echo '<span class="badge badge-success px-3 rounded-pill">Pronto</span>';
                                break;
                            case 3:
                                echo '<span class="badge badge-teal bg-gradient-teal px-3 rounded-pill">Pago</span>';
                                break;
                            case 4:
                                echo '<span class="badge badge-danger px-3 rounded-pill">Cancelado</span>';
                                break;
                        }
                        ?>
                    </div>
                    <div class="col-3 py-1 px-2 border border-navy bg-gradient-navy mb-0"><b>Mecânico Designado</b></div>
                    <div class="col-9 py-1 px-2 border mb-0"><?= isset($mechanic_name) ? $mechanic_name : '' ?></div>
                    <div class="col-3 py-1 px-2 border border-navy bg-gradient-navy mb-0"><b>Criado pelo usuário</b></div>
                    <div class="col-9 py-1 px-2 border mb-0"><?= isset($user_name) ? $user_name : '' ?></div>
                </div>
                <div class="clear-fix mb-2"></div>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <fieldset>
                        <legend>Serviços</legend>
                        <div class="clear-fix mb-2"></div>
                        <table class="table table-striped table-bordered" id="service-list">
                            <colgroup>
                                <col width="70%">
                                <col width="30%">
                            </colgroup>
                            <thead>
                                <tr class="bg-gradient-navy">
                                    <th class="text-center">Serviço</th>
                                    <th class="text-center">Preço</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $service_amount = 0;
                                $ts_qry = $conn->query("SELECT ts.*, s.name as `service` FROM `transaction_services` ts inner join `service_list` s on ts.service_id = s.id where ts.`transaction_id` = '{$id}' ");
                                while($row = $ts_qry->fetch_assoc()):
                                    $service_amount += $row['price'];
                                ?>
                                <tr>
                                    <td><?= $row['service'] ?></td>
                                    <td class="text-right service_price"><?= format_num($row['price']) ?></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-gradient-secondary">
                                    <th class="text-center">Total</th>
                                    <th class="text-right" id="service_total"><?= isset($service_amount) ? format_num($service_amount): 0 ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </fieldset>
                </div>
                <hr>
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <fieldset>
                        <legend>Produtos</legend>
                        <div class="clear-fix mb-2"></div>
                        <table class="table table-striped table-bordered" id="product-list">
                            <colgroup>
                                <col width="45%">
                                <col width="15%">
                                <col width="20%">
                                <col width="20%">
                            </colgroup>
                            <thead>
                                <tr class="bg-gradient-navy">
                                    <th class="text-center">Nome do Item</th>
                                    <th class="text-center">Quantidade</th>
                                    <th class="text-center">Preço</th>
                                    <th class="text-center">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php 
                                $product_total = 0;
                                $tp_qry = $conn->query("SELECT tp.*, p.name as `product` FROM `transaction_products` tp inner join `product_list` p on tp.product_id = p.id where tp.`transaction_id` = '{$id}' ");
                                while($row = $tp_qry->fetch_assoc()):
                                    $product_total += ($row['price'] * $row['qty']);
                            ?>
                                <tr>
                                    <td><?= $row['product'] ?></td>
                                    <td class="text-right"><?= $row['qty'] ?></td>
                                    <td class="text-right product_price"><?= $row['price'] ?></td>
                                    <td class="text-right product_total"><?= format_num($row['price'] * $row['qty']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                            </tbody>
                            <tfoot>
                                <tr class="bg-gradient-secondary">
                                    <th colspan="3" class="text-center">Total</th>
                                    <th class="text-right" id="product_total"><?= isset($product_total) ? format_num($product_total): 0 ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </fieldset>
                </div>
                <hr>
                <div class="clear-fix mb-3"></div>
                <h2 class="text-navy text-right">Valor total a pagar: <b id="amount"><?= isset($amount) ? number_format($amount, 2, ',', '.') : "0,00" ?></b></h2>
            </div>
            <hr>
            <div class="row justify-content-center">
                <button class="btn btn-primary bg-gradient-navy border col-lg-3 col-md-4 col-sm-12 col-xs-12 rounded-pill" id="update_status" type="button">Atualizar status</button>
                <a class="btn btn-primary bg-gradient-primary border col-lg-3 col-md-4 col-sm-12 col-xs-12 rounded-pill" href="./?page=transactions/manage_transaction&id=<?= isset($id) ? $id : '' ?>"><i class="fa fa-edit"></i> Editar</a>
                <button class="btn btn-light bg-gradient-light border col-lg-3 col-md-4 col-sm-12 col-xs-12 rounded-pill" id="print"><i class="fa fa-print"></i> Imprimir</button>
                <button class="btn btn-danger bg-gradient-danger border col-lg-3 col-md-4 col-sm-12 col-xs-12 rounded-pill" id="delete_transaction" type="button"><i class="fa fa-trash"></i> Deletar Transação</button>
            </div>
        </div>
    </div>
</div>
<noscript id="print-header">
    <div class="d-flex w-100">
        <div class="col-2 text-center">
            <img style="height:.8in;width:.8in!important;object-fit:cover;object-position:center center" src="<?= validate_image($_settings->info('logo')) ?>" alt="" class="w-100 img-thumbnail rounded-circle">
        </div>
        <div class="col-8 text-center">
            <div style="line-height:1em">
                <h4 class="text-center"><?= $_settings->info('name') ?></h4>
                <h3 class="text-center"><b>Detalhes da Transação</b></h3>
            </div>
        </div>
    </div>
    <hr>
</noscript>
<script>
$(function(){
    $('#print').click(function(){
        var head = $('head').clone()
        var p = $('#printout').clone()
        var phead = $($('noscript#print-header').html()).clone()
        var el = $('<div>')
        el.append(head)
        el.find('title').text("Detalhes da transação - Visualização de impressão")
        el.append(phead)
        el.append(p)
        el.find('.bg-gradient-navy').css({'background':'#001f3f linear-gradient(180deg, #26415c, #001f3f) repeat-x !important','color':'#fff'})
        el.find('.bg-gradient-secondary').css({'background':'#6c757d linear-gradient(180deg, #828a91, #6c757d) repeat-x !important','color':'#fff'})
        el.find('tr.bg-gradient-navy').attr('style',"color:#000")
        el.find('tr.bg-gradient-secondary').attr('style',"color:#000")
        start_loader();
        var nw = window.open("", "_blank", "width="+($(window).width() * .8)+", height="+($(window).height() * .8)+", left="+($(window).width() * .1)+", top="+($(window).height() * .1))
                 nw.document.write(el.html())
                 nw.document.close()
                 setTimeout(()=>{
                     nw.print()
                     setTimeout(()=>{
                        nw.close()
                        end_loader()
                     },300)
                 },500)
    })
    $('#update_status').click(function(){
        uni_modal("Atualizar status da transação", "transactions/update_status.php?id=<?= isset($id) ? $id : '' ?>")
    })
    $('#delete_transaction').click(function(){
        _conf("Tem certeza de que deseja excluir esta transação permanentemente?","delete_transaction",[])
    })
})
function delete_transaction($id){
		start_loader();
		$.ajax({
			url:_base_url_+"classes/Master.php?f=delete_transaction",
			method:"POST",
			data:{id: '<?= isset($id) ? $id : "" ?>'},
			dataType:"json",
			error:err=>{
				console.log(err)
				alert_toast("Ocorreu um Erro.",'error');
				end_loader();
			},
			success:function(resp){
				if(typeof resp== 'object' && resp.status == 'success'){
					location.replace('./?page=transactions');
				}else{
					alert_toast("Ocorreu um Erro.",'error');
					end_loader();
				}
			}
		})
	}
</script>