<form method="POST" style="margin-bottom:0;" action="?menu={$module_name}">
<table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
<tr>
  <td>
    <table width="90%" cellpadding="4" cellspacing="0" border="0">
      <tr class="letra12">
        <td width="8%" align="right">{$txt_fecha_init.LABEL}: <span  class="required">*</span></td>
        <td width="8%" align="left" nowrap>{$txt_fecha_init.INPUT}</td>
        <td width="8%" align="right">{$txt_fecha_end.LABEL}: <span  class="required">*</span></td>
        <td width="8%" align="left" nowrap>{$txt_fecha_end.INPUT}</td>
        <td width="5%" align="right">{$status_choosed.LABEL}:</td>
        <td width="8%" align="left" >{$status_choosed.INPUT} </td>
        <td width="5%" align="center">
            <input class="button" type="submit" name="submit_fecha" value="{$btn_consultar}" >
        </td>
      </tr>
   </table>
  </td>
</tr>
</table>
</form>
