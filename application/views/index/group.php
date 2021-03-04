<div class="content">
	<div class="row mx-2">
	<?php if ($backUrl != null) {?>
		<div class="col-md-12">
			<h6 class="text-primary"><a href="#" onclick="history.back(-1); return false;"><i class="fa fa fa-long-arrow-left"></i> Back</a></h6>
		</div>
	<?php }?>
		<div class="col-md-6">
			<h1 class="text-primary"><strong><?=$group_name?></strong></h1>
			<h2 class="text-primary"><strong>Dashboard</strong></h2>
		</div>
		<div class="col-md-6 text-right">
			<div class="my-1"><button id="create_pdf" class="btn btn-sm btn-primary"> Generate PDF </button></div>
			<div class="my-1">
				<form action="" method="post">
					<button class="btn btn-sm btn-primary pull-right"> Apply </button>
					<input class="form-control pull-right" type="text" name="dates" placeholder="Filter by date">
				</form>
			</div>
		</div>
	</div>
	<div class="row my-4 mx-2">
		<div class="col">
			<div class="tvalues py-4">
				<h5><?=$sheets_count?> / <?=$pillows_count?></h5>
				<h6>Bed Sheets / Pillowcases</h6>
			</div>
		</div>
		<div class="col">
			<div class="tvalues py-4">
				<h5>$ <?=number_format($amount, 2)?></h5>
				<h6>Total Raised</h6>
			</div>
		</div>
        <div class="col">
            <div class="tvalues py-4">
                <h5><?=number_format(count($contact_list))?></h5>
                <h6>Total Contacts</h6>
            </div>
        </div>
	</div>
	<div class="row my-5">
		<div class="col-md-6">
			<h5>Orders</h5>
			<table class="table table-striped table_data">
				<tbody id="tbody_order">
					<?php
if (count($order_list) > 0) {
    foreach ($order_list as $key => $order) {?>
						<tr class="<?=($key > 9 ? 'hidden' : '')?>">
							<td width="5%"><span><?=$key + 1?></span></td>
							<td>
								<p><strong><?=$order["participant_name"]?></strong></p>
								<p>Participant</p>
							</td>
							<td>
								<p><strong><?=$order["customer_name"] == null ? "UNKNOWN" : $order["customer_name"]?></strong></p>
								<p>Customer</p>
							</td>
							<td>
								<p><strong>Bed Sheets:</strong> <?=$order["sheets_count"]?></p>
								<p><strong>Pillowcases:</strong> <?=$order["pillows_count"]?></p>
							</td>
							<td>
								<p><strong class="pdf_date"><?=substr($order["date"], 0, 10)?></strong></p>
								<p>Date</p>
							</td>
							<td class="text-right">$<?=number_format($order["amount"], 2)?></td>
						</tr>
					<?php }} else {?>
						<tr>
							<td class="text-center">There is no data.</td>
						</tr>
					<?php }?>
				</tbody>
			</table>
			<div class="text-center">
				<button class="btn btn-sm btn-outline-primary btn-show">SHOW ALL</button>
			</div>
		</div>
		<div class="col-md-6">
			<h5>Participants
				<a href="<?=base_url('index.php/index/group_hiberblast/' . $user['user_login'])?>" class="btn btn-sm btn-primary pull-right">Hiberblast</a>
				<a href="#" class="btn btn-sm btn-primary pull-right mr-1" data-toggle="modal" data-target="#myModal">Edit Group Detail</a>
				<a href="#" class="btn btn-sm btn-primary pull-right mr-1" data-toggle="modal" data-target="#myModal1">Show Contacts</a>
            </h5>
			<table class="table table-striped table_data">
				<tbody id="tbody_user">
					<?php
if (count($participant_list) > 0) {
    foreach ($participant_list as $key => $participant) {?>
						<tr class="<?=($key > 9 ? 'hidden' : '')?>" data-id="<?=$participant['user_login']?>">
							<td width="5%"><span><?=$key + 1?></span></td>
							<td><?=$participant["display_name"]?></td>
							<td>
								<p><strong>Contacts:</strong> <?=$participant["count_contact_list"]?></p>
							</td>
							<td>
								<p><strong>Bed Sheets:</strong> <?=$participant["sheets_count"]?></p>
								<p><strong>Pillowcases:</strong> <?=$participant["pillows_count"]?></p>
							</td>
							<td class="text-right">$<?=number_format($participant["amount"], 2)?></td>
						</tr>
					<?php }} else {?>
						<tr>
							<td class="text-center">There is no data.</td>
						</tr>
					<?php }?>
				</tbody>
			</table>
			<div class="text-center">
				<button class="btn btn-sm btn-outline-primary btn-show">SHOW ALL</button>
			</div>
		</div>
	</div>
</div>
<div class="modal" id="myModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Group</h4>
        <button type="button" class="close" data-dismiss="modal" style="background-color: transparent!important;">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
        	<label for="group_name">Group Name : </label>
        	<input id="group_name" type="text" class="form-control" name="group_name" value="<?=$group_name?>">
        </div>
        <div class="form-group">
        	<label for="group_name">Goal Amount : </label>
        	<input id="group_goal" type="number" class="form-control" name="group_goal" value="<?=$group_attrs['fundraising_goal']?>">
        </div>
        <div class="form-group">
        	<label for="group_name">End date : </label>
        	<input id="end_date" type="text" class="form-control" name="end_date" value="<?=$group_attrs['end_date']?>">
        </div>
        <div class="form-group">
        	<label for="group_name">Commission : </label>
            <select class="form-control" id="commission" name="commission">
            	<option <?php echo $group_attrs['sheet_commission'] == 12.5 ? 'selected' : '' ?>>12.5</option>
                <option <?php echo $group_attrs['sheet_commission'] == 15 ? 'selected' : '' ?>>15</option>
            </select>
        </div>
        <div class="form-group">
        	<input type="checkbox" id="hibernate_hide_leaderboard" name="hibernate_hide_leaderboard" <?php echo $group_attrs['hibernate_hide_leaderboard'] == 1 ? 'checked' : '' ?> />
        	<label for="hibernate_hide_leaderboard">Hide the Leaderboard? (Check for yes)</label>
        </div>
      </div>
      <div class="modal-footer">
        <button id="btn-save" type="button" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-default" data-dismiss="modal" style="background-color: #898989!important;">Close</button>
      </div>
    </div>
  </div>
</div>
<div class="modal" id="myModal1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Contact List</h4>
        <button type="button" class="close" data-dismiss="modal" style="background-color: transparent!important;">&times;</button>
      </div>
      <div class="modal-body">
         <table class="table table-striped table_data">
                <tbody id="tbody_group">
                    <?php
if (count($contact_list) > 0) {
    foreach ($contact_list as $key => $order) {?>
                    <tr class="<?=($key > 9 ? 'hidden' : '')?>" data-sale="<?=$key?>">
                        <td><span><?=$key + 1?></span></td>
                        <td>
                            <p><strong><?=$order["email"]?></strong></p>
                            <p>Email</p>
                        </td>
                        <td>
                            <p><strong><?=substr($order["created_date"], 0, 10)?></strong></p>
                            <p>Registered Date</p>
                        </td>
                    </tr>
                    <?php }} else {?>
                    <tr>
                        <td class="text-center">There is no data.</td>
                    </tr>
                    <?php }?>
                </tbody>
            </table>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
	var baseUrl = "<?=base_url()?>";
	var userlogin = "<?=$user['user_login']?>";

	$(document).ready(function() {
		$('input[name="dates"]').daterangepicker({
			autoUpdateInput: false,
			locale: {
			  	cancelLabel: 'Clear'
			}
		});

		$('#end_date').datepicker({
        	format:'yyyy-mm-dd',
		});

		$('input[name="dates"]').on('apply.daterangepicker', function(ev, picker) {
			$(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
		});

		$('input[name="dates"]').on('cancel.daterangepicker', function(ev, picker) {
			$(this).val('');
		});

		$("#tbody_user tr").on('click', function()
		{
			if($(this).attr('data-id') != undefined)
				document.location.href = baseUrl + "index.php/index/participant_dashboard/" + $(this).attr('data-id');
		});

		$("#btn-save").on('click', function()
		{
			$.ajax({
				url: baseUrl + "index.php/index/update_group_data/" + userlogin,
				data: {
					group_name : $("#group_name").val(),
					group_goal : $("#group_goal").val(),
					end_date : $("#end_date").val(),
					commission : $("#commission").val(),
					hibernate_hide_leaderboard : $("#hibernate_hide_leaderboard").prop('checked') == true ? 1 : 0,
				},
				success: function(result){
					result = JSON.parse(result);

					if(result.status == "success")
						document.location.href = document.location.href;

					$("#myModal").modal('hide');
				}
			});
		});
	});
</script>