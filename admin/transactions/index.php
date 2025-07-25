<?php if($_settings->chk_flashdata('success')): ?>
<script>
	alert_toast("<?php echo $_settings->flashdata('success') ?>",'success')
</script>
<?php endif;?>
<div class="card card-outline rounded-0 card-navy">
	<div class="card-header">
		<h3 class="card-title">Lista de Transações</h3>
		<div class="card-tools">
			<a href="./?page=transactions/manage_transaction" id="create_new" class="btn btn-flat btn-primary"><span class="fas fa-plus"></span>  Cadastrar Nova</a>
		</div>
	</div>
	<div class="card-body">
		<div class="container-fluid">
        <div class="container-fluid">
			<table class="table table-hover table-striped table-bordered">
				<colgroup>
					<col width="5%">
					<col width="15%">
					<col width="20%">
					<col width="25%">
					<col width="10%">
					<col width="10%">
					<col width="15%">
				</colgroup>
				<thead>
					<tr>
						<th>#</th>
						<th>Data de Atualização</th>
						<th>Código</th>
						<th>Cliente</th>
						<th>Valor</th>
						<th>Status</th>
						<th>Ação</th>
					</tr>
				</thead>
				<tbody>
					<?php 
					$i = 1;
						if($_settings->userdata('type') == 3):
						$qry = $conn->query("SELECT * FROM `transaction_list` where tech_id = '{$_settings->userdata('id')}' order by unix_timestamp(date_updated) desc ");
						else:
						$qry = $conn->query("SELECT * FROM `transaction_list` order by unix_timestamp(date_updated) desc ");
						endif;
						while($row = $qry->fetch_assoc()):
					?>
						<tr>
							<td class="text-center"><?php echo $i++; ?></td>
							<td><p class="m-0 truncate-1"><?= date("M d, Y H:i", strtotime($row['date_updated'])) ?></p></td>
							<td><p class="m-0 truncate-1"><?= $row['code'] ?></p></td>
							<td><p class="m-0 truncate-1"><?= $row['client_name'] ?></p></td>
							<td class='text-right'><?= format_num($row['amount']) ?></td>
							<td class="text-center">
								<?php 
								switch($row['status']){
									case 0:
										echo '<span class="badge badge-default border px-3 rounded-pill">Pendente</span>';
										break;
									case 1:
										echo '<span class="badge badge-primary px-3 rounded-pill">Em andamento</span>';
										break;
									case 2:
										echo '<span class="badge badge-success px-3 rounded-pill">Pronta</span>';
										break;
									case 3:
										echo '<span class="badge badge-success bg-gradient-teal px-3 rounded-pill">Paga</span>';
										break;
									case 4:
										echo '<span class="badge badge-danger px-3 rounded-pill">Cancelada</span>';
										break;
								}
								?>
                            </td>
							<td align="center">
								<a class="btn btn-default bg-gradient-light btn-flat btn-sm" href="?page=transactions/view_details&id=<?php echo $row['id'] ?>"><span class="fa fa-eye text-dark"></span> Visualizar</a>
							</td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
		</div>
		</div>
	</div>
</div>
<script>
	$(document).ready(function(){
		
		$('.table').dataTable({
			columnDefs: [
					{ orderable: false, targets: [5] }
			],
			order:[0,'asc']
		});
		$('.dataTable td,.dataTable th').addClass('py-1 px-2 align-middle')
	})
	
</script>