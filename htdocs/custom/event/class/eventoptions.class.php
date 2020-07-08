<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) 2013  Jean-François FERRY 		<jfefe@aternatik.fr>
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * 	\file		prodoptions/class/prodoptions.class.php
 * 	\ingroup	prodoptions
 * 	\brief		Class to manage product options
 */

require_once "day.class.php";

require_once DOL_DOCUMENT_ROOT."/product/class/product.class.php";

/**
 * Class to manage product options
 */
class Eventoptions  extends Day
{

	//private $db; //!< To store db handler
	public $error; //!< To return error code (or message)
	public $errors = array(); //!< To return several error codes (or messages)
	//public $element='eventoptions';	//!< Id that identify managed objects
	//public $table_element='event_day_options';	//!< Name of table without prefix where object is stored
	public $id;


	/**
	 * Constructor
	 *
	 * 	@param	DoliDb		$db		Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
		return 1;
	}

	/**
	 *  Lie un produit optionnel à la journée
	 *
	 *  @param      int	$fk_eventday    	Id de la journée
	 *  @param      int	$fk_option    		Id du produit a lier
	 *  @return     int        				< 0 if KO, > 0 if OK
	 */
	function add_event_option($fk_eventday, $fk_option)
	{
		$sql = 'DELETE from '.MAIN_DB_PREFIX.'event_day_options';
		$sql .= ' WHERE fk_eventday  = "'.$fk_eventday.'" AND fk_product_options = "'.$fk_option.'"';
		if (! $this->db->query($sql))
		{
			dol_print_error($this->db);
			return -1;
		}
		else
		{
			$sql = 'SELECT fk_eventday from '.MAIN_DB_PREFIX.'event_day_options';
			$sql .= ' WHERE fk_eventday  = "'.$fk_eventday.'" AND fk_product_options = "'.$fk_option.'"';
			if (! $this->db->query($sql))
			{
				dol_print_error($this->db);
				return -1;
			}
			else
			{
				$result = $this->db->query($sql);
				if ($result)
				{
					$num = $this->db->num_rows($result);
					if($num > 0)
					{
						$this->error="isFatherOfThis";
						return -1;
					}
					else
					{
						$sql = 'SELECT * FROM '.MAIN_DB_PREFIX.'product AS p WHERE p.rowid = '.$fk_option;
						$resql = $this->db->query($sql);
						$res = $resql->fetch_assoc();
						$res['stock'] != NULL ? $stock = $res['stock'] : $stock = 0;
						$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'event_day_options (fk_eventday,fk_product_options,qty,price,notes,datec)';
						$sql .= ' VALUES ("'.$fk_eventday.'","'.$fk_option.'","'.$stock.'","'.$res['price'].'","'.$res['note'].'","'.$res['datec'].'")';
						if (! $this->db->query($sql))
						{
							dol_print_error($this->db);
							return -1;
						}
						else
						{
							return 1;
						}
					}
				}
			}
		}
	}

	/**
	 *  Retire le lien entre un produit optionnel et un produit/service
	 *
	 *  @param      int	$fk_parent		Id du produit auquel ne sera plus lie le produit lie
	 *  @param      int	$fk_child		Id du produit a ne plus lie
	 *  @return     int			    	< 0 si erreur, > 0 si ok
	 */
	function del_event_option($fk_parent, $fk_option)
	{
		$sql = "DELETE FROM ".MAIN_DB_PREFIX."event_day_options";
		$sql.= " WHERE fk_eventday  = '".$fk_parent."'";
		$sql.= " AND fk_product_options = '".$fk_option."'";

		if (! $this->db->query($sql))
		{
			dol_print_error($this->db);
			return -1;
		}

		return 1;
	}

	/**
	 *  Verifie si c'est un produit optionnel
	 *
	 *  @param      int	$fk_parent		Id du produit auquel le produit est lie
	 *  @param      int	$fk_child		Id du produit lie
	 *  @return     int			    	< 0 si erreur, > 0 si ok
	 */
	function is_prodoptions($fk_parent, $fk_option)
	{
		$sql = "SELECT fk_eventday, qty";
		$sql.= " FROM ".MAIN_DB_PREFIX."event_day_options";
		$sql.= " WHERE fk_eventday  = '".$fk_parent."'";
		$sql.= " AND fk_product_options = '".$fk_option."'";

		$result = $this->db->query($sql);
		if ($result)
		{
			$num = $this->db->num_rows($result);

			if($num > 0)
			{
				$obj = $this->db->fetch_object($result);
				$this->is_prodoptions_qty = $obj->qty;
				$this->is_prodoptions_price_increased = $obj->price_increase;

				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

	/**
	 *  reconstruit l'arborescence des options sous la forme d'un tableau
	 *
	 *	@param		int		$multiply		Because each sublevel must be multiplicated by parent nb
	 *  @return 	array 					$this->res
	 */
	function get_arbo_each_eventoptions($multiply=1)
	{
		$this->res = array();
		if (isset($this->prodoptions) && is_array($this->prodoptions))
		{
			foreach($this->prodoptions as $nom_pere => $desc_pere)
			{
				if (is_array($desc_pere)) $this->fetch_prodoptions_arbo($desc_pere,"",$multiply);
			}
		}
		return $this->res;
	}

	/**
	 *  Fonction recursive uniquement utilisee par get_arbo_each_prodoptions, recompose l'arborescence des sousproduits
	 * 	Define value of this->res
	 *
	 *	@param		array	$prod			Products array
	 *	@param		string	$compl_path		Directory path
	 *	@param		int		$multiply		Because each sublevel must be multiplicated by parent nb
	 *	@param		int		$level			Init level
	 *  @return 	void
	 */
	function fetch_prodoptions_arbo($prod, $compl_path="", $multiply=1, $level=1)
	{
		global $conf,$langs;

		$product = new Product($this->db);
		foreach($prod as $nom_pere => $desc_pere)
		{
			if (is_array($desc_pere))	// If this parent desc is an array, this is an array of childs
			{
				$id=(! empty($desc_pere[0]) ? $desc_pere[0] :'');
				$nb=(! empty($desc_pere[1]) ? $desc_pere[1] :'');
				$price_increase=(! empty($desc_pere[2]) ? $desc_pere[2] :'');
				$ref=(! empty($desc_pere[3]) ? $desc_pere[3] :'');

				if ($multiply)
				{
					$img="";
					$this->fetch($id);
					$this->load_stock();
					if ($this->stock_warehouse[1]->real < $this->seuil_stock_alerte)
					{
						$img=img_warning($langs->trans("StockTooLow"));
					}
					$this->res[]= array(
					'id'=>$id,					// Id product
					'nb'=>$nb,					// Nb of units that compose parent product
					'nb_total'=>$nb*$multiply,	// Nb of units for all nb of product
					'stock'=>$this->stock_warehouse[1]->real,		// Stock
					'stock_alert'=>$this->seuil_stock_alerte,	// Stock alert
					'ref'=>$ref,		// Ref
					'fullpath' => $compl_path.$nom_pere,	// Label
					'price_increase' => $price_increase,	// Price increase for option
					'type'=>$type					// Nb of units that compose parent product
					);
				}
				else
				{
					$this->fetch($desc_pere[0]);
					$this->load_stock();
					$this->res[]= array(
					'id'=>$id,					// Id product
					'nb'=>$nb,					// Nb of units that compose parent product
					'nb_total'=>$nb,				// Nb of units for all nb of product
					'stock'=>$this->stock_warehouse[1]->real,		// Stock
					'stock_alert'=>$this->seuil_stock_alerte,	// Stock alert
					'price_increase' => $price_increase,	// Price increase for option
					'ref'=>$ref,		// Ref
					'fullpath' => $compl_path.$nom_pere,	// Label
					'type'=>$type					// Nb of units that compose parent product
					);
				}
			}
			else if($nom_pere != "0" && $nom_pere != "1")
			{
				$this->res[]= array($compl_path.$nom_pere,$desc_pere);
			}

			// Recursive call if child is an array
			if (is_array($desc_pere[0]))
			{
				$this ->fetch_prod_arbo($desc_pere[0], $nom_pere." -> ", $desc_pere[1]*$multiply, $level+1);
			}
		}
	}

	/**
	 *  Return childs of product with if fk_parent
	 *
	 * 	@param		int		$fk_parent	Id of product to search childs of
	 *  @return     array       		Prod
	 */
	function getOptionnalsArbo($fk_parent)
	{
		$sql = "SELECT p.rowid, p.ref, p.label as label, po.qty as qty, po.fk_product_options as id, p.fk_product_type";
		$sql.= " FROM ".MAIN_DB_PREFIX."product as p";
		$sql.= ", ".MAIN_DB_PREFIX."event_day_options as po";
		$sql.= " WHERE p.rowid = po.fk_product_options";
		$sql.= " AND po.fk_eventday = ".$fk_parent;

		$res  = $this->db->query($sql);
		if ($res)
		{
			$prods = array();
			while ($rec = $this->db->fetch_array($res))
			{
				//$prods[$this->db->escape($rec['label'])]= array(0=>$rec['id'],1=>$rec['qty'],2=>$rec['fk_product_type']);
				$prods[$this->db->escape($rec['label'])]= array(0=>$rec['id'],1=>$rec['qty'],2=>$rec['price_increase'],3=>$rec['ref']);
				$listofchilds=$this->getOptionnalsArbo($rec['id']);
				foreach($listofchilds as $keyChild => $valueChild)
				{
					$prods[$this->db->escape($rec['label'])][$keyChild] = $valueChild;
				}
			}

			return $prods;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

	/**
	 * 	Return tree of all options for event. Tree contains id, name and quantity.
	 * 	Set this->prodoptions
	 *
	 *  @return    	void
	 */
	function get_eventoptions_arbo()
	{
		$parent = $this->getOptionParent();
		foreach($parent as $key => $value)
		{
			foreach($this->getOptionnalsArbo($value[0]) as $keyChild => $valueChild)
			{
				$parent[$key][$keyChild] = $valueChild;
			}
		}
		foreach($parent as $key => $value)
		{
			$this->prodoptions[$key] = $value;
		}
	}

	/**
	 *  Return all parent products fo current product
	 *
	 *  @return 	array prod
	 */
	function getOptionParent()
	{

		$sql = "SELECT p.label as label,p.rowid,po.fk_eventday as id,p.fk_product_type";
		$sql.= " FROM ".MAIN_DB_PREFIX."event_day_options as po,";
		$sql.= " ".MAIN_DB_PREFIX."product as p";
		$sql.= " WHERE p.rowid = po.fk_product_options";
		$sql.= " AND po.fk_eventday = ".$this->id;
		$res = $this->db->query($sql);
		if ($res)
		{
			$prods = array ();
			while ($record = $this->db->fetch_array($res))
			{
				$prods[$this->db->escape($record['label'])] = array(0=>$record['id']);
			}
			return $prods;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

	/**
	 *  Return all Father products fo current product
	 *
	 *  @return 	array prod
	 */
	function getProdOptionsForDay()
	{

		$product = new Product($this->db);

		$sql = "SELECT p.label as label,p.ref, p.rowid, po.fk_product_options as id,p.fk_product_type, po.price";
		$sql.= " FROM ".MAIN_DB_PREFIX."event_day_options as po,";
		$sql.= " ".MAIN_DB_PREFIX."product as p";
		$sql.= " WHERE p.rowid = po.fk_product_options";
		$sql.= " AND po.fk_eventday=".$this->id;

		$res = $this->db->query($sql);
		if ($res)
		{
			$prods = array ();
			while ($record = $this->db->fetch_array($res))
			{
				$product->fetch($record['id']);
				$prods[$record['id']]['id'] =  $record['rowid'];
				$prods[$record['id']]['ref'] =  $product->ref;
				$prods[$record['id']]['label'] =  $this->db->escape($product->label);
				$prods[$record['id']]['fk_product_type'] =  $product->type;
				$prods[$record['id']]['price'] =  $product->price;
			}
			return $prods;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}


	/**
	 *	Return clicable link of object (with eventually picto)
	 *
	 *	@param		int		$withpicto		Add picto into link
	 *	@param		string	$option			Where point the link
	 *	@param		int		$maxlength		Maxlength of ref
	 *	@return		string					String with URL
	 */
	function getNomUrl($withpicto=0,$option='',$maxlength=0)
	{
		global $langs;

		$result='';

		if ($option == 'prodoptions')
		{
			$lien = '<a href="'.dol_buildpath('/prodoptions/prodoptions.php',1).'?id='.$this->id.'">';
			$lienfin='</a>';
		}
		else
		{
			$lien = '<a href="'.DOL_URL_ROOT.'/product/fiche.php?id='.$this->id.'">';
			$lienfin='</a>';
		}
		$newref=$this->ref;
		if ($maxlength) $newref=dol_trunc($newref,$maxlength,'middle');

		if ($withpicto) {
			if ($this->type == 0) $result.=($lien.img_object($langs->trans("ShowProduct").' '.$this->ref,'product').$lienfin.' ');
			if ($this->type == 1) $result.=($lien.img_object($langs->trans("ShowService").' '.$this->ref,'service').$lienfin.' ');
		}
		$result.=$lien.$newref.$lienfin;
		return $result;
	}

}
