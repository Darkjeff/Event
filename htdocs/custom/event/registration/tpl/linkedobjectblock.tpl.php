<?php
/* Copyright (C) 2010-2012 Regis Houssin <regis@dolibarr.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 */
?>

<!-- BEGIN PHP TEMPLATE -->

<?php

$langs = $GLOBALS['langs'];
$linkedObjectBlock = $GLOBALS['linkedObjectBlock'];


dol_include_once("/event/class/eventlevel.class.php");

echo '<br>';
print_titre($langs->trans('RelatedRegistration'));
?>
<table class="noborder">
<tr class="liste_titre">
	<td><?php echo $langs->trans("Ref"); ?></td>
	<td align="center"><?php echo $langs->trans("RegistrationDate"); ?></td>
	<td align="right"><?php echo $langs->trans("AmountHTShort"); ?></td>
	<td align="right"><?php echo $langs->trans("Status"); ?></td>
</tr>
<?php
$var=true;
foreach($linkedObjectBlock as $object)
{
	$var=!$var;


	$level=new Eventlevel($object->db);
	$result=$level->fetch($object->fk_levelday);


?>
<tr <?php echo $bc[$var]; ?>>
	<td><a href="<?php echo dol_buildpath("/event/registration/fiche.php",1).'?id='.$object->id; ?>"><?php echo img_object($langs->trans("ShowRegistration"),"event@event").' '.$object->ref.' - '.$level->label; ?></a></td>
	<td align="center"><?php echo dol_print_date($object->datec,'day'); ?></td>
	<td align="right"><?php echo price($object->total_ht); ?></td>
	<td align="right"><?php echo $object->getLibStatut(3); ?></td>
</tr>
<?php } ?>
</table>

<!-- END PHP TEMPLATE -->