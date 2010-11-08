<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: oai_protocol.class.php,v 1.17 2010-06-08 19:36:41 gueluneau Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path,$base_path, $include_path;
/**
 * \mainpage Documentation du Client OAI
 * \author PMB Services
 * \author Florent TETART
 * \date 2008
 */
 
//Gestion des dates
/**
 * \brief Gestion simplifi�e des dates selon la norme iso8601
 * 
 * Conversion r�ciproque des dates format unix en dates au format iso8601 
 * @author Florent TETART
 */
class iso8601 {
	var $granularity; /*!< \brief Granularit� courante des dates en format iso8601 : YYYY-MM-DD ou YYYY-MM-DDThh:mm:ssZ */
	
	/**
	 * \brief Constructeur
	 * @param string $granularity Granularit� des dates manipul�es : YYYY-MM-DD ou YYYY-MM-DDThh:mm:ssZ
	 */
	function iso8601($granularity="YYYY-MM-DD") {
		$this->granularity=$granularity;
	}
	
	/**
	 * \brief Conversion d'une date unix (nomnbres de secondes depuis le 01/01/1970) en date au format iso8601 selon la granularit�
	 * @param integer $time date au format unix (nombres de secondes depuis le 01/01/1970)
	 * @return string date au format YYYY-MM-DD ou YYYY-MM-DDThh:mm:ssZ selon la granularit�
	 */
	function unixtime_to_iso8601($time) {
		$granularity=str_replace("T","\\T",$this->granularity);
		$granularity=str_replace("Z","\\Z",$granularity);
		$granularity=str_replace("YYYY","Y",$granularity);
		$granularity=str_replace("DD","d",$granularity);
		$granularity=str_replace("hh","H",$granularity);
		$granularity=str_replace("mm","i",$granularity);
		$granularity=str_replace("MM","m",$granularity);
		$granularity=str_replace("ss","s",$granularity);
		$date=date($granularity,$time);
		return $date;
	}
	
	/**
	 * \brief Conversion d'une date au format iso8601 en date au format unix (nomnbres de secondes depuis le 01/01/1970) selon la granularit�
	 * @param string $date date au format iso8601 YYYY-MM-DD ou YYYY-MM-DDThh:mm:ssZ selon la granularit�
	 * @return integer date au format unix (nombres de secondes depuis le 01/01/1970)
	 */
	function iso8601_to_unixtime($date) {
		$parts=explode("T",$date);
		if (count($parts)==2) {
			$day=$parts[0]; 
			$time=$parts[1];
		} else {
			$day=$parts[0];
		}
		$days=explode("-",$day);
		if ($this->granularity=="YYYY-MM-DDThh:mm:ssZ") {
			if ($time) $times=explode(":",$time);
			if ($times[2]) {
				if (substr($times[2],strlen($times[2])-1,1)=="Z") $times[2]=substr($times[2],0,strlen($times[2])-1);
			}
		}
		$unixtime=mktime($times[0]+0,$times[1]+0,$time[2]+0,$days[1]+0,$days[2]+0,$days[0]+0);
		return $unixtime;
	}
}

//Un petit parser-dom �l�gant
/**
 * \brief Petit parser dom autonome et �l�gant
 * 
 * Parse une chaine XML et permet un acc�s rapide par une interface simplifi�e DOM. 
 * Cette classe fonctionne uassi bien en PHP4 que 5.
 * \note Cette classe manipule des noeuds de type noeud (\ref noeud "voir l'attribut $tree").\n
 * \note Des chemins sont utilis�s pour acc�der aux noeuds, les syntaxes sont d�taill�es dans les m�thodes qui les utilisent :\n
 * \note -\ref path_node "syntaxe des chemins pour la m�thode get_node"\n
 * \note -\ref path_nodes "syntaxe des chemins pour la m�thode get_nodes"\n
 *   
 * @author Florent TETART
 */
class xml_dom {
	var $xml;				/*!< XML d'origine */
	var $charset;			/*!< Charset courant (iso-8859-1 ou utf-8) */
	/**
	 * \brief Arbre des noeuds du document
	 * 
	 * L'arbre est compos� de noeuds qui ont la structure suivante :
	 * \anchor noeud
	 * \verbatim
	 $noeud = array(
	 	NAME	=> Nom de l'�l�ment pour un noeud de type �l�ment (TYPE = 1)
	 	ATTRIBS	=> Tableau des attributs (nom => valeur)
	 	TYPE	=> 1 = Noeud �l�ment, 2 = Noeud texte
	 	CHILDS	=> Tableau des noeuds enfants
	 )
	 \endverbatim
	 */
	var $tree; 
	var $error=false; 		/*!< Signalement d'erreur : true : erreur lors du parse, false : pas d'erreur */
	var $error_message=""; 	/*!< Message d'erreur correspondant � l'erreur de parse */
	var $depth=0;			/*!< \protected */
	var $last_elt=array();	/*!< \protected */
	var $n_elt=array();		/*!< \protected */
	var $cur_elt=array();	/*!< \protected */
	var $last_char=false;	/*!< \protected */
	
	/**
	 * \protected
	 */
	function close_node() {
		$this->last_elt[$this->depth-1]["CHILDS"][]=$this->cur_elt;
		$this->last_char=false;
		$this->cur_elt=$this->last_elt[$this->depth-1];
		$this->depth--;
	}
	
	/**
	 * \protected
	 */
	function startElement($parser,$name,$attribs) {
		if ($this->last_char) $this->close_node();
		$this->last_elt[$this->depth]=$this->cur_elt;
		$this->cur_elt=array();
		$this->cur_elt["NAME"]=$name;
		$this->cur_elt["ATTRIBS"]=$attribs;
		$this->cur_elt["TYPE"]=1;
		$this->last_char=false;
		$this->depth++;
	}
	
	/**
	 * \protected
	 */
	function endElement($parser,$name) {
		if ($this->last_char) $this->close_node();
		$this->close_node();
	}
	
	/**
	 * \protected
	 */
	function charElement($parser,$char) {
		if ($this->last_char) $this->close_node();
		$this->last_char=true;
		$this->last_elt[$this->depth]=$this->cur_elt;
		$this->cur_elt=array();
		$this->cur_elt["DATA"].=$char;
		$this->cur_elt["TYPE"]=2;
		$this->depth++;
	}
	
	/**
	 * \brief Instanciation du parser
	 * 
	 * Le document xml est pars� selon le charset donn� et une repr�sentation sous forme d'arbre est g�n�r�e
	 * @param string $xml XML a manipuler
	 * @param string $charset Charset du document XML
	 */
	function xml_dom($xml,$charset="iso-8859-1") {
		$this->charset=$charset;
		$this->cur_elt=array("NAME"=>"document","TYPE"=>"0");
		
		//Initialisation du parser
		$xml_parser=xml_parser_create($this->charset);
		xml_set_object($xml_parser,$this);
		xml_parser_set_option( $xml_parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option( $xml_parser, XML_OPTION_SKIP_WHITE, 1 );
		xml_set_element_handler($xml_parser, "startElement", "endElement");
		xml_set_character_data_handler($xml_parser,"charElement");
		
		if (!xml_parse($xml_parser, $xml)) {
       		$this->error_message=sprintf("XML error: %s at line %d",xml_error_string(xml_get_error_code($xml_parser)),xml_get_current_line_number($xml_parser));
       		$this->error=true;
		}
		$this->tree=$this->last_elt[0];
	}
	
	/**
	 * \anchor path_node
	 * \brief R�cup�ration d'un noeud par son chemin
	 * 
	 * Recherche un noeud selon le chemin donn� en param�tre. Un noeud de d�part peut �tre pr�cis�
	 * @param string $path Chemin du noeud recherch�
	 * @param noeud [$node] Noeud de d�part de la recherche (le noeud doit �tre de type 1)
	 * @return noeud Noeud correspondant au chemin ou \b false si non trouv�
	 * \note Les chemins ont la syntaxe suivante :
	 * \verbatim
	 <a>
	 	<b>
	 		<c id="0">Texte</c>
	 		<c id="1">
	 			<d>Sous texte</d>
	 		</c>
	 		<c id="2">Texte 2</c>
	 	</b>
	 </a>
	 
	 a/b/c		Le premier noeud �l�ment c (<c id="0">Texte</c>)
	 a/b/c[2]/d	Le premier noeud �l�ment d du deuxi�me noeud c (<d>Sous texte</d>)
	 a/b/c[3]	Le troisi�me noeud �l�ment c (<c id="2">Texte 2</c>) 
	 a/b/id@c	Le premier noeud �l�ment c (<c id="0">Texte</c>). L'attribut est ignor�
	 a/b/id@c[3]	Le trois�me noeud �l�ment c (<c id="2">Texte 2</c>). L'attribut est ignor�
	 
	 Les attributs ne peuvent �tre cit�s que sur le noeud final.
	 \endverbatim
	 */
	function get_node($path,$node="") {
		if ($node=="") $node=&$this->tree;
		$paths=explode("/",$path);
		for ($i=0; $i<count($paths); $i++) {
			if ($i==count($paths)-1) {
				$pelt=explode("@",$paths[$i]);
				if (count($pelt)==1) { 
					$p=$pelt[0]; 
				} else {
					$p=$pelt[1];
					$attr=$pelt[0];
				}
			} else $p=$paths[$i];
			if (preg_match("/\[([0-9]*)\]$/",$p,$matches)) {
				$name=substr($p,0,strlen($p)-strlen($matches[0]));
				$n=$matches[1];
			} else {
				$name=$p;
				$n=0;
			}
			$nc=0;
			$found=false;
			for ($j=0; $j<count($node["CHILDS"]); $j++) {
				if (($node["CHILDS"][$j]["TYPE"]==1)&&($node["CHILDS"][$j]["NAME"]==$name)) {
					//C'est celui l� !!
					if ($nc==$n) {
						$node=&$node["CHILDS"][$j];
						$found=true;
						break;
					} else $nc++;
				}
			}
			if (!$found) return false;
		}
		return $node;
	}
	
	/**
	 * \anchor path_nodes
	 * \brief R�cup�ration d'un ensemble de noeuds par leur chemin
	 * 
	 * Recherche d'un ensemble de noeuds selon le chemin donn� en param�tre. Un noeud de d�part peut �tre pr�cis�
	 * @param string $path Chemin des noeuds recherch�s
	 * @param noeud [$node] Noeud de d�part de la recherche (le noeud doit �tre de type 1)
	 * @return array noeud Tableau des noeuds correspondants au chemin ou \b false si non trouv�
	 * \note Les chemins ont la syntaxe suivante :
	 * \verbatim
	 <a>
	 	<b>
	 		<c id="0">Texte</c>
	 		<c id="1">
	 			<d>Sous texte</d>
	 		</c>
	 		<c id="2">Texte 2</c>
	 	</b>
	 </a>
	 
	 a/b/c		Tous les �l�ments c fils de a/b 
	 a/b/c[2]/d	Tous les �l�ments d fils de a/b et du deuxi�me �l�ment c
	 a/b/id@c	Tous les noeuds �l�ments c fils de a/b. L'attribut est ignor�
	 \endverbatim
	 */
	function get_nodes($path,$node="") {
		$n=0;
		$nodes="";
		while ($nod=$this->get_node($path."[$n]",$node)) {
			$nodes[]=$nod;
			$n++;
		}
		return $nodes;
	}
	
	/**
	 * \brief R�cup�ration des donn�es s�rialis�es d'un noeud �l�ment
	 * 
	 * R�cup�re sous forme texte les donn�es d'un noeud �l�ment :\n
	 * -Si c'est un �l�ment qui n'a qu'un noeud texte comme fils, renvoie le texte\n
	 * -Si c'est un �l�ment qui a d'autres �l�ments comme fils, la version s�rialis�e des enfants est renvoy�e
	 * @param noeud $node Noeud duquel r�cup�rer les donn�es
	 * @param bool $force_entities true : les donn�es sont renvoy�es avec les entit�s xml, false : les donn�es sont renvoy�es sans entit�s
	 * @return string donn�es s�rialis�es du noeud �l�ment
	 */
	function get_datas($node,$force_entities=false) {
		$char="";
		if ($node["TYPE"]!=1) return false;
		//Recherche des fils et v�rification qu'il n'y a que du texte !
		$flag_text=true;
		for ($i=0; $i<count($node["CHILDS"]); $i++) {
			if ($node["CHILDS"][$i]["TYPE"]!=2) $flag_text=false;
		}
		if ((!$flag_text)&&(!$force_entities)) {
			$force_entities=true;
		}
		for ($i=0; $i<count($node["CHILDS"]); $i++) {
			if ($node["CHILDS"][$i]["TYPE"]==2)
				if ($force_entities) 
					$char.=htmlspecialchars($node["CHILDS"][$i]["DATA"],ENT_NOQUOTES,$this->charset);
				else $char.=$node["CHILDS"][$i]["DATA"];
			else {
				$char.="<".$node["CHILDS"][$i]["NAME"];
				if (count($node["CHILDS"][$i]["ATTRIBS"])) {
					foreach ($node["CHILDS"][$i]["ATTRIBS"] as $key=>$val) {
						$char.=" ".$key."=\"".htmlspecialchars($val,ENT_NOQUOTES,$this->charset)."\"";
					}
				}
				$char.=">";
				$char.=$this->get_datas($node["CHILDS"][$i],$force_entities);
				$char.="</".$node["CHILDS"][$i]["NAME"].">";
			}
		}
		return $char;
	}
	
	/**
	 * \brief R�cup�ration des attributs d'un noeud
	 * 
	 * Renvoie le tableau des attributs d'un noeud �l�ment (Type 1)
	 * @param noeud $node Noeud �l�ment duquel on veut les attributs
	 * @return mixed Tableau des attributs Nom => Valeur ou false si ce n'est pas un noeud de type 1
	 */
	function get_attributes($node) {
		if ($node["TYPE"]!=1) return false;
		return $node["ATTRIBUTES"];
	}
	
	/**
	 * \brief R�cup�re les donn�es ou l'attribut d'un noeud par son chemin
	 * 
	 * R�cup�re les donn�es s�rialis�es d'un noeud ou la valeur d'un attribut selon le chemin
	 * @param string $path chemin du noeud recherch�
	 * @param noeud $node Noeud de d�part de la recherche
	 * @return string Donn�e s�rialsi�e ou valeur de l'attribut, \b false si le chemin n'existe pas
	 * \note Exemples de valeurs renvoy�es selon le chemin :
	 * \verbatim
	 <a>
	 	<b>
	 		<c id="0">Texte</c>
	 		<c id="1">
	 			<d>Sous texte</d>
	 		</c>
	 		<c id="2">Texte 2</c>
	 	</b>
	 </a>
	 
	 a/b/c		Renvoie : "Texte"
	 a/b/c[2]/d	Renvoie : "Sous texte"
	 a/b/c[2]	Renvoie : "<d>Sous texte</d>"
	 a/b/c[3]	Renvoie : "Texte 2" 
	 a/b/id@c	Renvoie : "0"
	 a/b/id@c[3]	Renvoie : "2"
	 \endverbatim
	 */
	function get_value($path,$node="") {
		$elt=$this->get_node($path,$node);
		if ($elt) {
			$paths=explode("/",$path);
			$pelt=explode("@",$paths[count($paths)-1]);
			if (count($pelt)>1) {
				$a=$pelt[0];
				//Recherche de l'attribut
				if (preg_match("/\[([0-9]*)\]$/",$a,$matches)) {
					$attr=substr($a,0,strlen($a)-strlen($matches[0]));
					$n=$matches[1];
				} else {
					$attr=$a;
					$n=0;
				}
				$nc=0;
				$found=false;
				foreach($elt["ATTRIBS"] as $key=>$val) {
					if ($key==$attr) {
						//C'est celui l� !!
						if ($nc==$n) {
							$value=$val;
							$found=true;
							break;
						} else $nc++;
					}
				}
				if (!$found) $value="";
			} else {
				$value=$this->get_datas($elt);
			}
		}
		return $value;
	}
	
	/**
	 * \brief R�cup�re les donn�es ou l'attribut d'un ensemble de noeuds par leur chemin
	 * 
	 * R�cup�re les donn�es s�rialis�es ou la valeur d'un attribut d'un ensemble de noeuds selon le chemin
	 * @param string $path chemin des noeuds recherch�s
	 * @param noeud $node Noeud de d�part de la recherche
	 * @return array Tableau des donn�es s�rialis�es ou des valeur de l'attribut, \b false si le chemin n'existe pas
	 * \note Exemples de valeurs renvoy�es selon le chemin :
	 * \verbatim
	 <a>
	 	<b>
	 		<c id="0">Texte</c>
	 		<c id="1">
	 			<d>Sous texte</d>
	 		</c>
	 		<c id="2">Texte 2</c>
	 	</b>
	 </a>
	 
	 a/b/c		Renvoie : [0]=>"Texte",[1]=>"<d>Sous texte</d>",[2]=>"Texte 2"
	 a/b/c[2]/d	Renvoie : [0]=>"Sous texte"
	 a/b/id@c	Renvoie : [0]=>"0",[1]=>"1",[2]=>"2"
	 \endverbatim
	 */
	function get_values($path,$node="") {
		$n=0;
		while ($elt=$this->get_node($path."[$n]",$node)) {
			$elts[$n]=$elt;
			$n++;
		}
		if (count($elts)) {
			for ($i=0; $i<count($elts); $i++) {
				$elt=$elts[$i];
				$paths=explode("/",$path);
				$pelt=explode("@",$paths[count($paths)-1]);
				if (count($pelt)>1) {
					$a=$pelt[0];
					//Recherche de l'attribut
					if (preg_match("/\[([0-9]*)\]$/",$a,$matches)) {
						$attr=substr($a,0,strlen($a)-strlen($matches[0]));
						$n=$matches[1];
					} else {
						$attr=$a;
						$n=0;
					}
					$nc=0;
					$found=false;
					foreach($elt["ATTRIBS"] as $key=>$val) {
						if ($key==$attr) {
							//C'est celui l� !!
							if ($nc==$n) {
								$values[]=$val;
								$found=true;
								break;
							} else $nc++;
						}
					}
					if (!$found) $values[]="";
				} else {
					$values[]=$this->get_datas($elt);
				}
			}
		}
		return $values;
	}
}

//Manipualtion des enregistrements
/**
 * \brief Gestion d'un enregistrement OAI
 */
class oai_record {
	var $srecord;			//Enregistrement d'origine
	var $header;			//Ent�te
	var $metadata;			//Enregistrement pars�
	var $unimarc;			//Enregistrement converti en unimarc
	var $about;				//About
	var $handler;			//Handler pour parser les m�tadatas
	var $prefix;			//For�age du handler demand�
	var $base_path;			//Chemin de base pour les feuilles XSLT
	var $xslt_transform;	//Feuille de style pour transformer l'enregistrement en unimarc	
	var $error;
	var $error_message;
	var $charset;
	
	/**
	 * \brief Instanciation de l'enregistrement OAI
	 * 
	 * Cr�� une repr�sentation d'un enregistrement OAI et le transforme en uni_marc si possible
	 */
	function oai_record($record,$charset="iso-8859-1",$base_path="",$prefix="",$xslt_transform="",$sets_names="") {
		$this->srecord=$record;
		$this->charset=$charset;
		$this->prefix=$prefix;
		$this->base_path=$base_path;
		$this->xslt_transform=$xslt_transform;
		
		$precord=new xml_dom('<?xml version="1.0" encoding="'.$charset.'"?>'.$record,$charset);
		if ($precord->error) {
			$this->error=true;
			$this->error_message=$precord->error_message;
		} else {
			//Header
			$this->header["IDENTIFIER"]=$precord->get_value("record/header/identifier");
			$this->header["DATESTAMP"]=$precord->get_value("record/header/datestamp");
			$this->header["SETSPECS"]=$precord->get_values("record/header/setSpec");
			$this->header["STATUS"]=$precord->get_values("record/header/status");
			//Enregistrement
			$this->metadata=$precord->get_value("record/metadata");
			//About
			$this->about=$precord->get_value("record/about");
			
			$nmeta=$precord->get_node("record/metadata");
			//Conversion �ventuelle en unimarc
			if (!$this->prefix) {
				//Recherche du premier fils �l�ment
				for ($i=0; $i<count($nmeta["CHILDS"]); $i++) {
					if ($nmeta["CHILDS"][$i]["TYPE"]==1) {
						$handler=explode(":",$nmeta["CHILDS"][$i]["NAME"]);
						$this->handler=$handler[0];
						break;
					}
				}
			} else {
				$this->handler=$this->prefix;
			}
			$hd=$precord->get_node("record/header");
			//Petit truchement pour r�cup�rer le nom des sets
			if (count($this->header["SETSPECS"])) {
				for ($i=0; $i<count($this->header["SETSPECS"]);$i++) {
					$setName=array();
					$setName["NAME"]="setName";
					$setName["ATTRIBS"]=array();
					$setName["TYPE"]=1;
					$setName["CHILDS"][0]["DATA"]=($sets_names[$this->header["SETSPECS"][$i]]?$sets_names[$this->header["SETSPECS"][$i]]:$this->header["SETSPECS"][$i]);
					$setName["CHILDS"][0]["TYPE"]=2;
					$hd["CHILDS"][]=$setName;
				}
			}	
			//R�cup�ration de la feuille xslt si elle n'a pas �t� fournie
			if (!$this->xslt_transform) {
				if (file_exists($this->base_path."/".$this->handler.".xsl")) {
					$this->xslt_transform=file_get_contents($this->base_path."/".$this->handler.".xsl");
				}
			}
			//Si on peut n�c�ssaire, on transforme en unimarc
			if ($this->xslt_transform) {
				$this->unimarc=$this->to_unimarc("<record><header>".$precord->get_datas($hd)."</header><metadata>".$this->metadata."</metadata></record>");
			}
			if ($this->prefix=="pmb_xml_unimarc") $this->unimarc="<?xml version='1.0' encoding='".$this->charset."'?>\n<unimarc>\n".$this->metadata."</unimarc>";
		}
	}
	
	function to_unimarc($metatdata) {
		//$xsl=file_get_contents("/home/ftetart/public_html/php_dev/admin/connecteurs/in/oai/dc2uni.xsl");
		
		/* Allocation du processeur XSLT */
		$xh = xslt_create();
		xslt_set_encoding($xh, $this->charset);
		$notice="<?xml version='1.0' encoding='".$this->charset."'?>\n".$metatdata;

		/* Traitement du document */
		$arguments = array(
	   	  '/_xml' => $notice,
	   	  '/_xsl' => $this->xslt_transform
		);
		$result = xslt_process($xh, 'arg:/_xml', 'arg:/_xsl', NULL, $arguments); 
		return $result;
	}
}

//Environnement de parse & parser d'une ressource
class oai_parser {
	var $depth;					//Profondeur courante d'analyse
	var $cur_elt;				//Enregistrement courant
	var $last_elt;				//Tableau des derniers �l�ments pars�s pour chaque niveau
	var $verb;					//Verbe en cours (r�cup�r� de la r�ponse)
	var $tree;					//Arbre des �l�ments de niveau 1
	var $error,$error_message;	//Erreurs
	var $laction;				//Derni�re action du parser : open = "un tag vient d'�tre ouvert mais pas ferm�", close = "Un tag ouvert vient d'�tre ferm�"
	var $rtoken;				//Resumption Token : [expirationDate], [completeListSize], [cursor], [token]
	var $rec_callback;			//Fonction de callback pour un enregistrement
	var $records;				//Tableau des enregistrements r�cup�r�s
	var $charset;				//Charset de sortie
	var $oai_atoms=array(		//El�ments r�p�titifs attendus pour chaque verb
		"GetRecord"=>"record",
		"ListIdentifiers"=>"header",
		"ListMetadataFormats"=>"metadataFormat",
		"ListRecords"=>"record",
		"ListSets"=>"set"
	);
	
	//Fonctions appel�es lors du parse d'une r�ponse
	function oai_startElement($parser, $name, $attrs) {
		$this->laction="open";
		if (!$this->error) {
			switch ($name) {
				case "OAI-PMH":
					if ($this->depth!=0) {
						$this->error=true;
						$this->error_message="Unknown OAI Response";
					} else {
						$this->last_elt[$this->depth]=$name;
					}
					break;
				case "responseDate":
					if ($this->depth!=1) {
						$this->error=true;
						$this->error_message="Unknown OAI Response";
					} else {
						$this->last_elt[$this->depth]=$name;
					}
					break;
				case "request":
					if ($this->depth!=1) {
						$this->error=true;
						$this->error_message="Unknown OAI Response";
					} else {
						$this->last_elt[$this->depth]=$name;
						if ($attrs["verb"]) $this->verb=$attrs["verb"];
					}
					break;
				case "error":
					if ($this->depth!=1) {
						$this->error=true;
						$this->error_message="Unknown OAI Response";
					} else {
						$this->last_elt[$this->depth]=$name;
					}
					break;
				case $this->verb:
					if ($this->depth!=1) {
						$this->error=true;
						$this->error_message="Unknown OAI Response";
					} else {
						$this->last_elt[$this->depth]=$name;
						$this->cur_elt="";
					}
					break;
				default:
					if (($this->last_elt[1]!=$this->verb)||($this->depth==1)) {
						$this->error=true;
						$this->error_message="Unknown XML Response : tag is invalid : ".$name;
					}
					break;
			}
			if ($this->depth>=2) {
				if ($this->depth==2) {
					if (($this->verb!="Identify")&&($name!=$this->oai_atoms[$this->verb])) {
						if ($name!="resumptionToken") {
							$this->error=true;
							$this->error_message="Bad pattern response for verb : ".$this->verb;
						}
					} else {
						if ($this->verb!="Identify")
							$this->cur_elt="";
					}
				} 
				if (($name=="resumptionToken")&&($this->depth==2)) {
					$this->rtoken["expirationDate"]=$attrs["expirationDate"];
					$this->rtoken["completeListSize"]=$attrs["completeListSize"];
					$this->rtoken["cursor"]=$attrs["cursor"];
				} else {
					//$this->cur_elt.="\n";
					//for ($i = 0; $i < $this->depth; $i++) {
			   		// 	$this->cur_elt.="  ";
					//}
					$this->cur_elt.="<$name";
					foreach($attrs as $key=>$val) { 
						$this->cur_elt.=" ".$key."=\"".htmlspecialchars($val,ENT_NOQUOTES,$this->charset)."\" ";
					}
					$this->cur_elt.=">";
				}
			} else {
				$f["NAME"]=$name;
				$f["ATTRIB"]=$attrs;
				$this->tree[$this->depth][]=$f;
			}
		}
		$this->depth++;
	}
	
	function oai_charElement($parser,$char) {
		if (($this->laction=="open")&&(!$this->error)) {
			if ($this->depth<=2) {
				$this->tree[$this->depth-1][count($this->tree[$this->depth-1])-1]["CHAR"].=$char;
			} else {
				if ($this->rtoken) {
					$this->rtoken["token"].=$char;
				} else {
					$this->cur_elt.=htmlspecialchars($char,ENT_NOQUOTES,$this->charset);
				}
			}
		}
	}
	
	function oai_endElement($parser, $name) {  	  
		$this->laction="close";
		if (!$this->error) {
			if ($this->depth<=2) {
				if ($this->last_elt[$this->depth-1]!=$name) {
					$this->error=true;
					$this->error_message="Unknown OAI Response";
				} else {
					unset($this->last_elt[$this->depth]);
				}
			} else {
				if ($this->depth>2) {
					if (!$this->rtoken)
						$this->cur_elt.="</".$name.">";
				}
				if (!$this->rtoken) {
					if (($this->depth==3)&&($this->verb!="Identify")) {
						if (!$this->rec_callback)
							$this->records[]=$this->cur_elt;
						else {
							if (substr(strtolower($this->charset),0,10)=="iso-8859-1") $c=true; else $c=false;
							$rec_callback=$this->rec_callback;
							if (!is_array($rec_callback))
								$rec_callback(($c?utf8_decode($this->cur_elt):$this->cur_elt));
							else {
								$c=&$rec_callback[0];
								$f=$rec_callback[1];
								$c->$f(($c?utf8_decode($this->cur_elt):$this->cur_elt));
							}
						}
					}
				}
			}
		}
		$this->depth--;
	}
	
	function oai_parser($rcallback="",$charset="iso-8859-1") {
		$this->depth=0;
		$this->rtoken="";
		$this->rec_callback=$rcallback;
		$this->charset=$charset;
	}
}

//Gestion bas niveau du protocol
class oai_protocol {
	var $url_base;				//Url de base
	var $clean_base_url;		//Nettoyer les urls renvoy�es dans le tag request
    var $error=false;
    var $error_message="";
    var $error_oai_code="";		//Code d'erreur OAI
    var $response_date;			//Date de r�ponse
    var $request;				//Requ�te
    var $rtoken;    			//Param�tre du "Resumption Token"
    var $next_request;			//Requ�te � rappeller si Resumption Token
    var $records=array();		//Enregistrements lus
    var $charset="iso-8859-1";
    var $time_out;				//Temps maximum d'interrogation de la source
    var $xml_parser;			//Ressource parser
    var $retry_after;			//D�lais avant r��ssai
    					
    function oai_protocol($charset="iso-8859-1",$url="",$time_out="",$clean_base_url=0) {
    	$this->charset=$charset;
    	$this->time_out=$time_out;
    	$this->clean_base_url=$clean_base_url;
    	if ($url) $this->analyse_response($url);
    }
    
    function parse_xml($ch,$data) {
    	if (!$this->retry_after) {
	    	//Parse de la ressource
	    	if (!xml_parse($this->xml_parser, $data)) {
	       		$this->error_message=sprintf("XML error: %s at line %d",xml_error_string(xml_get_error_code($this->xml_parser)),xml_get_current_line_number($this->xml_parser));
	       		$this->error=true;
	       		return strlen($data);
	    	} else if ($s->error) {
	    		$this->error_message=$s->error_message;
	    		$this->error=true;
	    		return strlen($data);
	    	}
    	}
    	return strlen($data);
	}
    
    function verif_header($ch,$headers) {
    	$h=explode("\n",$headers);
    	for ($i=0; $i<count($h); $i++) {
    		$v=explode(":",$h[$i]);
    		if ($v[0]=="Retry-After") { $this->retry_after=$v[1]*1; }
    	}
    	return strlen($headers);
    }
    
    //Analyse d'une resource
    function analyse_response($url,$rcallback="") {
    	//Remise � z�ro des erreurs
    	$this->error=false;
    	$this->error_message="";
    	//remise � zero des enregistrements
    	if ($url!=$this->next_request) $this->records=array();
    	$this->next_request="";
    	$this->rtoken="";
    	
    	//Initialisation de la ressource
    	$ch = curl_init();
		// configuration des options CURL
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_WRITEFUNCTION,array(&$this,"parse_xml"));
		curl_setopt($ch, CURLOPT_HEADERFUNCTION,array(&$this,"verif_header"));	
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		if ($this->time_out) curl_setopt($ch, CURLOPT_TIMEOUT,$this->time_out);
    	//R�initialisation du "retry_after"
		$this->retry_after="";    	
    	
		configurer_proxy_curl($ch);	
		
    	//Explosion des arguments de la requ�te pour ceux qui ne respectent pas la norme !!
    	$query=substr($url,strpos($url,"?")+1);
    	$query=explode("&",$query);
    	for ($i=0; $i<count($query); $i++) {
    		if (strpos($query[$i],"verb")!==false) {
    			$verb=substr($query[$i],5);
    			break;
    		}
    	}
    	
    	//Itinitalisation de l'environnement d'�tat du parser
		$s=new oai_parser($rcallback,$this->charset);
    	
    	//Si le verb est affect�, on pr�rempli histoire d'aider un peu... :-)
    	if ($verb) $s->verb=$verb;
    	
    	//Initialisation du parser
		$this->xml_parser=xml_parser_create("utf-8");
		xml_set_object($this->xml_parser,$s);
		xml_parser_set_option( $this->xml_parser, XML_OPTION_CASE_FOLDING, 0 );
		xml_parser_set_option( $this->xml_parser, XML_OPTION_SKIP_WHITE, 1 );
		xml_set_element_handler($this->xml_parser, "oai_startElement", "oai_endElement");
		xml_set_character_data_handler($this->xml_parser,"oai_charElement");
		
		$n_try=0;
		$cexec=curl_exec($ch);
		while (($cexec)&&($this->retry_after)&&($n_try<3)) {
			$n_try++; 
			sleep((int)$this->retry_after*1);
			$this->retry_after="";
			$cexec=curl_exec($ch);
		}
		if (!$cexec) {
			$this->error=true;
			$this->error_message=curl_error($ch);
		}
		xml_parser_free($this->xml_parser);
		$this->xml_parser="";
		curl_close($ch);
		
		if ($this->error) { $this->error_message.=" - ".$url; unset($s); return; }
		
		//Affectation des �l�ments de r�ponse
		if (substr(strtolower($this->charset),0,10)=="iso-8859-1") $c=true; else $c=false;
		//Test de l'url base
		if ($this->clean_base_url) {
			$p=strpos($s->tree[1][1]["CHAR"],"?");
			if ($p!==false) $s->tree[1][1]["CHAR"]=substr($s->tree[1][1]["CHAR"],0,$p);
		}
		$this->response_date=$c?utf8_decode($s->tree[1][0]["CHAR"]):$s->tree[1][0]["CHAR"];
		$this->url_base=$c?utf8_decode($s->tree[1][1]["CHAR"]):$s->tree[1][1]["CHAR"];
		$this->request["URL_BASE"]=$c?utf8_decode($s->tree[1][1]["CHAR"]):$s->tree[1][1]["CHAR"];
		foreach ($s->tree[1][1]["ATTRIB"] as $key=>$val) {
			if ($key!="resumptionToken")
				$this->request["ATTRIBS"][$key]=$c?utf8_decode($val):$val;
		}
		$this->verb=$c?utf8_decode($s->tree[1][1]["ATTRIB"]["verb"]):$s->tree[1][1]["ATTRIB"]["verb"];
		$this->rtoken=$s->rtoken;
		
		if ($s->tree[1][2]["NAME"]=="error") {
			$this->error=true;
			$this->error_message="OAI Error, the server tell : ".$s->tree[1][2]["ATTRIB"]["code"]." : ".$s->tree[1][2]["CHAR"];
			$this->error_oai_code=$s->tree[1][2]["ATTRIB"]["code"];
		}
		
		//Si c'est la requ�te identify
		if ($this->verb=="Identify") {
			$this->records[0]=$c?utf8_decode($s->cur_elt):$s->cur_elt;
		} else {
			if (!$rcallback) {
				for ($i=0; $i<count($s->records); $i++) {
					$this->records[]=$c?utf8_decode($s->records[$i]):$s->records[$i];
				}
			}
		}
		if ($this->rtoken["token"]) {
			$this->next_request=$this->request["URL_BASE"]."?verb=".$s->verb;
			$is_first=true;
			/*foreach ($this->request["ATTRIBS"] as $key=>$val) {
				if (!$is_first) $this->next_request.="&"; else $is_first=false;
				$this->next_request.=$key."=".rawurlencode($val);
			}*/
			$this->next_request.="&resumptionToken=".rawurlencode($this->rtoken["token"]);
		}	
		//Supression de l'environnement d'�tat !
		unset($s);
    }
}

class oai20 {
	var $error;
	var $error_message;
	var $error_oai_code;
	var $no_connect=true;		//La connexion n'est as active avec l'entrepot
	var $url_base;				//Url de base du service OAI
	var $clean_base_url;		//Nettoyer les urls renvoy�es dans le tag request
	var $charset;				//Encodage d�sir� de sortie
	var $prt;					//Protocol
	var $repositoryName;		//Nom de l'entrep�t
	var $baseURL;				//Url de base retourn�e
	var $protocolVersion;		//Version du protocole
	var $earliestDatestamp;		//Date de la notice la plus ancienne
	var $deletedRecord;			//Gestion des enregistrements supprim�s
	var $granularity;			//Granularit�
	var $description;			//Description si trouv�e
	var $adminEmail;			//Email admin du service
	var $compression;			//Types de compression
	var $h_sets;				//Sets hierarchis�s
	var $sets;					//Sets bruts
	var $metadatas;				//Formats des metadatas disponibles
	var $unsupported_features;	//Fonctionalit�s non support�es (SETS)
	var $last_query;			//Derni�re requ�te effectu�
	var $time_out;				//Time out total avant erreur d'une commande
	
	function oai20($url_base,$charset="iso-8859-1",$time_out="",$clean_base_url=0) {
		//Evitons d'afficher les vilains warning qui trainent
		ini_set('display_errors', 0);
		//Initialisation du service
		$this->url_base=$url_base;
		$this->charset=$charset;
		$this->time_out=$time_out;
		$this->clean_base_url=$clean_base_url;
		//C'est parti : initialisation !
		$this->prt=new oai_protocol($this->charset,$this->url_base."?verb=Identify",$this->time_out,$this->clean_base_url);
		if ($this->prt->error) {
			$this->error=true;
			$this->error_message="Protocol error : ".$this->prt->error_message;
			return;
		} else {
			$this->no_connect=false;
			//Parse 
			$identity=new xml_dom('<?xml version="1.0" encoding="'.$this->charset.'"?>'."<Identity>".$this->prt->records[0]."</Identity>");
			$this->repositoryName=$identity->get_value("Identity/repositoryName");
			$this->baseURL=$identity->get_value("Identity/baseURL");
			$this->protocolVersion=$identity->get_value("Identity/protocolVersion");
			$this->earliestDatestamp=$identity->get_value("Identity/earliestDatestamp");
			$this->deletedRecord=$identity->get_value("Identity/deletedRecord");
			$this->granularity=$identity->get_value("Identity/granularity");
			$this->adminEmail=$identity->get_value("Identity/adminEmail");
			$this->compression=$identity->get_value("Identity/compression");
			$descriptions=$identity->get_nodes("Identity/description");
			if ($descriptions) {
				for ($i=0; $i<count($descriptions); $i++) {
					if ($this->description=$identity->get_value("oai_dc:dc/dc:description",$descriptions[$i])) break;
				}
			}
			//R�cup�ration des metadatas et sets
			$this->list_sets();
			if ($this->error) {
				$this->no_connect=true; 
			} 
			else {
				$this->list_metadata_formats();
				if ($this->error)
					$this->no_connect=true; 
			}
				
			//if ($node) print $identity->get_datas($node);
			//print $this->prt->records[0];
		}
	}
	
	function set_clean_base_url($clean_base_url) {
		$this->clean_base_url=$clean_base_url;
	}
	
	function clear_error() {
		$this->error=false;
		$this->error_message="";
		$this->error_oai_code="";
	}
	
	function send_request($url,$callback="",$callback_progress="") {
		$this->last_query=$url;
		$this->prt->analyse_response($url,$callback);
		while ((!$this->prt->error)&&($this->prt->next_request)) {
			$last_request=$this->prt->next_request;
			if ($callback_progress) {
				if (!is_array($callback_progress))
					$callback_progress($this->last_query,$this->prt->rtoken);
				else {
					$c=&$callback_progress[0];
					$f=$callback_progress[1];
					$c->$f($this->last_query,$this->prt->rtoken);
				}
			}
			$this->last_query=$this->prt->next_request;
			$this->prt->analyse_response($this->prt->next_request,$callback);
		}
		if ($this->prt->error) {
			$this->error=true;
			$this->error_message=$this->prt->error_message;
			$this->error_oai_code=$this->prt->error_oai_code;
		}
	}
	
	function has_feature($feature) {
		return (!$this->unsupported_features[$feature]);
	}
	
	function check_metadata($metadata_prefix) {
		//V�rification du metadata
		$found=false;
		for ($i=0; $i<count($this->metadatas); $i++) {
			if ($this->metadatas[$i]["PREFIX"]==$metadata_prefix) {
				$found=true;
				break;
			}
		}
		return $found;
	}
	
	function list_sets($callback="",$callback_progress="") {
		$this->clear_error();
		$this->send_request($this->url_base."?verb=ListSets",$callback,$callback_progress);
		$this->sets=array();
		$this->h_sets=array();
		if (!$this->error) {
			if (!$callback) {
				for ($i=0; $i<count($this->prt->records); $i++) {
					$record=new xml_dom('<?xml version="1.0" encoding="'.$this->charset.'"?>'.$this->prt->records[$i], $this->charset);
					if (!$record->error) {
						$set=$record->get_value("set/setSpec");
						$set_name=$record->get_value("set/setName");
						$this->sets[$set]=$set_name;
						$set=explode(":",$record->get_value("set/setSpec"));
						$path="";
						for ($j=0; $j<count($set)-1; $j++) {
							$path.="[\"".$set[$j]."\"][\"CHILDS\"]";
						}
						eval("\$this->h_sets".$path."[\"".$set[$j]."\"][\"NAME\"]=\$set_name;");
					} else $this->error_message="Can't read record : ".$record->error_message;
				} 
			}
		} else {
			if ($this->error_oai_code=="noSetHierarchy") {
				$this->error=false;
				$this->unsupported_features["SETS"]=true;
			}
		}
		return $this->sets;
	}
	
	function list_metadata_formats($identifier="",$callback="",$callback_progress="") {
		$this->clear_error();
		$url=$this->url_base."?verb=ListMetadataFormats";
		if ($identifier) $url.="&identifier=".rawurlencode($identifier);
		$this->send_request($url,$callback_progress);
		$metadatas=array();
		if (!$this->error) {
			if (!$callback) {
				for ($i=0; $i<count($this->prt->records); $i++) {
					$record=new xml_dom($this->prt->records[$i],$this->charset);
					if (!$record->error) {
						$m=array();
						$m["PREFIX"]=$record->get_value("metadataFormat/metadataPrefix");
						$m["SCHEMA"]=$record->get_value("metadataFormat/schema");
						$m["NAMESPACE"]=$record->get_value("metadataFormat/metadataNamespace");
						$metadatas[]=$m;
					}
				}
				if ($identifier=="") $this->metadatas=$metadatas;
			}
		}
		return $metadatas;
	}
	
	function list_records($from,$until,$set,$metadata_prefix,$callback="",$callback_progress="") {
		$this->clear_error();
		$records=array();
		//Conversion des from et until en fonction de la granularit�
		$iso8601=new iso8601($this->granularity);
		if ($from) $from=$iso8601->unixtime_to_iso8601($from);
		if ($until) $until=$iso8601->unixtime_to_iso8601($until);
		//V�rification du metadata
		if ($this->check_metadata($metadata_prefix)) {
			$url=$this->url_base."?verb=ListRecords&metadataPrefix=".rawurlencode($metadata_prefix);
			if ($from) $url.="&from=".$from;
			if ($until) $url.="&until=".$until;
			if ($set) $url.="&set=".rawurlencode($set);
			$this->send_request($url,$callback,$callback_progress);
			if (!$this->error) {
				if (!$callback) {
					for ($i=0; $i<count($this->prt->records); $i++) {
						$records[]=$this->prt->records[$i];
					}
				}
			}
		} else {
			$this->error=true;
			$this->error_message="Unknow metadata prefix : ".$metadata_prefix;
		}
		if (!$callback) return $records;
	}
	
	function list_identifiers($from,$until,$set,$metadata_prefix,$callback="",$callback_progress="") {
		$this->clear_error();
		$records=array();
		//Conversion des from et until en fonction de la granularit�
		$iso8601=new iso8601($this->granularity);
		if ($from) $from=$iso8601->unixtime_to_iso8601($from);
		if ($until) $until=$iso8601->unixtime_to_iso8601($until);
		//V�rification du metadata
		if ($this->check_metadata($metadata_prefix)) {
			$url=$this->url_base."?verb=ListIdentifiers&metadataPrefix=".rawurlencode($metadata_prefix);
			if ($from) $url.="&from=".$from;
			if ($until) $url.="&until=".$until;
			if ($set) $url.="&set=".rawurlencode($set);
			$this->send_request($url,$callback,$callback_progress);
			if (!$this->error) {
				if (!$callback) {
					for ($i=0; $i<count($this->prt->records); $i++) {
						$records[]=$this->prt->records[$i];
					}
				}
			}
		} else {
			$this->error=true;
			$this->error_message="Unknow metadata prefix : ".$metadata_prefix;
		}
		if (!$callback) return $records;
	}
	
	function get_record($identifier,$metadata_prefix,$callback="",$callback_progress="") {
		$this->clear_error();
		$record="";
		//V�rification du pr�fixe
		if ($this->check_metadata($metadata_prefix)) {
			$this->send_request($this->url_base."?verb=GetRecord&identifier=".rawurlencode($identifier)."&metadataPrefix=".rawurlencode($metadata_prefix),$callback,$callback_progress);
			if (!$this->error) {
				if (!$callback) {
					$record=$this->prt->records[0];
				}
			}
		} else {
			$this->error=true;
			$this->error_message="Unknow metadata prefix : ".$metadata_prefix;
		}
		return $record;
	}
}
?>
