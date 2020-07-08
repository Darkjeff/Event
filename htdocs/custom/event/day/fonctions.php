<?php
	
function jour_suivant($db,$fk_event,$date_event)
{ 	 
	$sql = "SELECT date_event,rowid FROM llx_event_day WHERE fk_event='".$fk_event."' and date_event>'".$date_event."' order by date_event";
	$resql = $db->query($sql);
	if ($resql)		
	  if($res = $resql->fetch_assoc())
			return $res["rowid"];
	return '';
	
}

function jour_precedent($db,$fk_event,$date_event)
{
	$sql = "SELECT date_event,rowid FROM llx_event_day WHERE fk_event='".$fk_event."' and date_event<'".$date_event."' order by date_event desc";
	$resql = $db->query($sql);
	if ($resql)		
	  if($res = $resql->fetch_assoc())
			return $res["rowid"];
	return '';	
}
	
?>