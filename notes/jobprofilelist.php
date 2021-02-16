

<div id="jobrole" class="modal fade" role="dialog">
	<div class="modal-dialog modal-lg" id="welcomecontainer">
	<!-- Modal content-->
		<div class="modal-content" style="box-shadow: none;border: none;"> 
			<div class="modal-body"  style="padding:0px;">
				<button type="button" class="close" data-dismiss="modal" style="color: #000;opacity: 1;font-size: 30px;">&times;</button>			 
				<div style="text-align:center">
						<div class="" id="JP"></div>
				</div>
			</div>	
		</div>
	</div>
</div>
						
<div class="container">
  <h2>Job Profile</h2>
   
		<table id="JobPRO" class="table table-striped table-bordered"	>
				<thead>
					<tr>
						<th>S.No.</th>
						<th>Job Name</th>
						<th>Sector</th>
						<th>Action</th>
					   
					</tr>
				</thead>
				
				<tbody>
				<?php $i=1;
				foreach ($jobprofiledata as $row){
					?>
					<tr>
						<td><?php echo $i; ?></td>
						<td><?php echo $row['occupation']; ?></td>
						<td><?php echo $row['subsector']; ?></td>
						<td><input type="button" value="View more" class="viewmore" id="<?php echo $row['occupationid']; ?>" name="viewmore">
						</td>	
					</tr>
				<?php
				$i++; }
				?>	 
			<?php
				$row++;
			?>
				</tbody>
			</table>
	 
          
  
</div>

<style>
.modal-dialog {
  width: 100%;
  height: 100%;
  margin: 0;
  padding: 0;
}

.modal-content {
  height: auto;
  min-height: 100%;
  border-radius: 0;
}
.modal:before 
{ 
    display: contents !important;
}
.viewmore{
	border: 1px solid #f92c8b;
    padding: 7px 13px;
    color: #fff;
    border-radius: 5px;
    background: #f92c8b;
    font-family: 'Roboto';
    text-align: center;
}

#JobPRO_length select{
	height: auto;
    font-size: inherit;
    width: auto;
    margin-bottom: 10px;
    -webkit-appearance: none;
    background: #fff;
    border: 1px solid #d9d9d9;
    border-top: 1px solid #c0c0c0;
    padding: 0 8px;
}

</style>





<link href="<?php echo base_url(); ?>assets/css/datatbl/jquery.dataTables.min.css" rel="stylesheet" type="text/css"> 
<script src="<?php echo base_url(); ?>assets/js/datatbl/jquery.dataTables.min.js" type="text/javascript"></script>
<script>
	
	$(document).ready(function() {
    $('#JobPRO').DataTable();
} );

</script><script>
$(document).ready(function () {

    $("#JobPRO").on("click", ".viewmore", function()
	{  
		var occid=$(this).attr('id');
		$.ajax({
			type: "POST",
			url: "<?php echo base_url()."index.php/home/jobprofilelist_popup"; ?>",
			data: {occupationid:occid},
			success: function(result)
			{
				$("#JP").html(result);
				$('#jobrole').modal({backdrop: 'static', keyboard: false}) ;
			}
		});
      
    });
});

</script>
	