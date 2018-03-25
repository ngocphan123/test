<!-- BEGIN: main -->
<!-- BEGIN: error -->
<div class="alert alert-danger">
	{error}
</div>
<!-- END: error -->
<!-- BEGIN: data -->
<table class="table table-striped table-bordered table-hover">
	<thead>
		<tr>
			<th width="10px" class="text-center">&nbsp;</th>
			<th>{LANG.money_name}</th>
			<th>{LANG.currency}</th>
			<th>{LANG.weight_sign}</th>
			<th>{LANG.exchange}</th>
			<th>{LANG.round}</th>
			<th width="120px" class="text-center">{LANG.function}</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="6"><i class="fa fa-check-square-o">&nbsp;</i><a href="#" id="checkall">{LANG.prounit_select}</a>&nbsp;&nbsp;<i class="fa fa-square-o">&nbsp;</i><a href="#" id="uncheckall">{LANG.prounit_unselect}</a>&nbsp;&nbsp;<i class="fa fa-trash-o">&nbsp;</i><a href="#" id="delall">{LANG.prounit_del_select}</a></td>
		</tr>
	</tfoot>
	<tbody>
	<!-- BEGIN: row -->
		<tr>
			<td><input type="checkbox" class="ck" value="{ROW.id}" /></td>
			<td>{ROW.code}</td>
			<td>{ROW.currency}</td>
			<td>{ROW.symbol}</td>
			<td>1 {ROW.code} = {ROW.exchange} {MONEY_UNIT}</td>
			<td>{ROW.round}</td>
			<td class="text-center"><i class="fa fa-edit">&nbsp;</i><a href="{ROW.link_edit}" title="">{LANG.edit}</a>&nbsp; <i class="fa fa-trash-o">&nbsp;</i><a href="{ROW.link_del}" class="delete" title="">{LANG.del}</a></td>
		</tr>
	<!-- END: row -->
	</tbody>
</table>
<script type='text/javascript'>
	$(function() {
		$('#checkall').click(function() {
			$('input:checkbox').each(function() {
				$(this).attr('checked', 'checked');
			});
		});
		$('#uncheckall').click(function() {
			$('input:checkbox').each(function() {
				$(this).removeAttr('checked');
			});
		});
		$('#delall').click(function() {
			if (confirm("{LANG.prounit_del_confirm}")) {
				var listall = [];
				$('input.ck:checked').each(function() {
					listall.push($(this).val());
				});
				if (listall.length < 1) {
					alert("{LANG.prounit_del_no_items}");
					return false;
				}
				$.ajax({
					type : 'POST',
					url : '{URL_DEL}',
					data : 'listall=' + listall,
					success : function(data) {
						window.location = '{URL_DEL_BACK}';
					}
				});
			}
		});
		$('a.delete').click(function(event) {
			event.preventDefault();
			if (confirm("{LANG.prounit_del_confirm}")) {
				var href = $(this).attr('href');
				$.ajax({
					type : 'POST',
					url : href,
					data : '',
					success : function(data) {
						window.location = '{URL_DEL_BACK}';
					}
				});
			}
		});
	});
</script>
<!-- END: data -->

<form action="" method="post"><input name="savecat" type="hidden" value="1" />
	<input type="hidden" value="{DATA.id}" name="id" />
	<table class="table table-striped table-bordered table-hover">
		<caption>{DATA.caption}</caption>
		<tbody>
			<tr>
				<td align="right" width="230px"><strong>{LANG.money_name}: </strong></td>
				<td>
				<select class="form-control w200" name="code">
					<!-- BEGIN: money -->
					<option value="{DATAMONEY.value}"{DATAMONEY.selected}>{DATAMONEY.title}</option>
					<!-- END: money -->
				</select></td>
			</tr>
			<tr>
				<td valign="top" align="right"><strong>{LANG.currency}: </strong></td>
				<td><input class="form-control w400" name="currency" type="text" value="{DATA.currency}" maxlength="255" /></td>
			</tr>
			<tr>
				<td valign="top" align="right"><strong>{LANG.weight_sign}: </strong></td>
				<td><input class="form-control w400" name="symbol" type="text" value="{DATA.symbol}" maxlength="255" /></td>
			</tr>
			<tr>
				<td valign="top" align="right"><strong>{LANG.exchange}: </strong></td>
				<td><input class="form-control w400" name="exchange" type="text" value="{DATA.exchange}" maxlength="255" /></td>
			</tr>
			<tr>
				<td valign="top" align="right"><strong>{LANG.round}: </strong></td>
				<td>
					<select class="form-control w400" name="round">
						<!-- BEGIN: round -->
						<option value="{ROUND.round1}"{ROUND.selected}>{ROUND.round2}</option>
						<!-- END: round -->
					</select>
				</td>
			</tr>
			<tr>
				<td valign="top" align="right"><strong>{LANG.money_number_format}: </strong></td>
				<td class="form-inline text-middle">
					<span>
						{LANG.money_number_format_dec_point}
						<input class="form-control" style="width: 50px" name="dec_point" type="text" value="{DATA.dec_point}" maxlength="1" />
						{LANG.money_number_format_thousands_sep}
						<input class="form-control" style="width: 50px" name="thousands_sep" type="text" value="{DATA.thousands_sep}" maxlength="1" />
					</span>
				</td>
			</tr>
		</tbody>
	</table>
	<br>
	<div class="text-center">
		<input class="btn btn-primary" name="submit" type="submit" value="{LANG.prounit_save}" />
	</div>
</form>
<!-- END: main -->