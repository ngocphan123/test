<!-- BEGIN: main -->
<div id="module_show_list">
	{BLOCK_CAT_LIST}
</div>
<br />
<a id="edit"></a>
<!-- BEGIN: error -->
<div class="alert alert-warning">{ERROR}</div>
<!-- END: error -->
<form class="form-inline" action="{NV_BASE_ADMINURL}index.php" method="post">
	<input type="hidden" name ="{NV_NAME_VARIABLE}"value="{MODULE_NAME}" />
	<input type="hidden" name ="{NV_OP_VARIABLE}"value="{OP}" />
	<input type="hidden" name ="bid" value="{DATA.bid}" />
	<input name="savecat" type="hidden" value="1" />
	<div class="table-responsive">
		<table class="table table-striped table-bordered table-hover">
			<caption>{LANG.add_block_cat}</caption>
			<col width="150"/>
			<tbody>
				<tr>
					<th class="text-right">{LANG.block_name}: </th>
					<td><input class="form-control" style="width: 650px" name="title" type="text" value="{DATA.title}" maxlength="255" /></td>
				</tr>
				<!-- BEGIN: alias -->
				<tr>
					<th class="text-right" width="100px">{LANG.alias}: </th>
					<td><input class="form-control" style="width: 650px" name="alias" type="text" value="{DATA.alias}" maxlength="255" /></td>
				</tr>
				<!-- END: alias -->
				<tr>
					<th class="text-right">{LANG.keywords}: </th>
					<td><input class="form-control" style="width: 650px" name="keywords" type="text" value="{DATA.keywords}" maxlength="255" /></td>
				</tr>
				<tr>
					<th class="text-right">
					<br>
					{LANG.description}</th>
					<td><textarea class="form-control" style="width: 650px" name="description" cols="100" rows="5">{DATA.description}</textarea></td>
				</tr>
				<tr>
					<th class="text-right">{LANG.content_homeimg}</th>
					<td>
						<input class="form-control" style="width: 500px" type="text" name="image" id="image" value="{DATA.image}"/>
						<a class="btn btn-info" name="selectimg"><em class="fa fa-folder-open-o">&nbsp;</em>{LANG.file_selectfile}</a>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
	<br />
	<div class="text-center">
		<input class="btn btn-primary" name="submit1" type="submit" value="{LANG.save}" />
	</div>
</form>
<script>
$("a[name=selectimg]").click(function() {
	var area = "image";
	var path = "{UPLOAD_CURRENT}";
	var currentpath = "{UPLOAD_CURRENT}";
	var type = "image";
	nv_open_browse(script_name + "?" + nv_name_variable + "=upload&popup=1&area=" + area + "&path=" + path + "&type=" + type + "&currentpath=" + currentpath, "NVImg", 850, 420, "resizable=no,scrollbars=no,toolbar=no,location=no,status=no");
	return false;
});
</script>
<!-- END: main -->