<?php
/*
Plugin Name: Cross-linker
Plugin URI: https://www.janhvizdak.com/make-donation-cross-linker-plugin-wordpress.html
Description: A plugin which allows to set-up words which are automatically hyperlinked to desired URLs
Version: 2.0.5.6
Author: Jan Hvizdak
Author URI: https://www.janhvizdak.com/
*/


/*  Copyright 2013  Jan Hvizdak  (email : postmaster@aqua-fish.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
	global $wpdb;

	//crosslinker trieda
	class crossLinkerClass {

		const VERSION = "2.0.5.6";	//verzia Cross-Linkera

		const MAX_TRY = 100;		//kolkokrat skusit operaciu pred opustenim slucky

		//definicia tabuliek
		const JAL_DB_VERSION	= "1.0";
		const CROSSLINK_MAIN	= "interlinker";				//opt
		const CROSSLINK_CHARS	= "interlinker_special_chars";			//opt
		const CROSSLINK_TAGS	= "interlinker_divide_chars";			//opt
		const CROSSLINK_SETTS	= "interlinker_settings";			//opt
		const CROSSLINK_ATTRB	= "interlinker_attributes";			//opt
		const CROSSLINK_LNGS	= "interlinker_multilang";			//opt
		const CROSSLINK_WDLNG	= "interlinker_lng_wds";			//opt
		const CROSSLINK_TIMES   = "interlinker_times";				//opt
		const CROSSLINK_EXCD    = 30;						//opt

		//tabulky na backup
		const BKP_CROSSLINK_BKP	= "interlinker_backups";			//opt
		const BKP_CROSSLINK_MAIN= "interlinker_backup_main";
		const BKP_CROSSLINK_CHAR= "interlinker_special_chars";
		const BKP_CROSSLINK_TAGS= "interlinker_divide_chars";
		const BKP_CROSSLINK_SETT= "interlinker_settings";
		const BKP_CROSSLINK_ATTR= "interlinker_attributes";
		const BKP_CROSSLINK_LNGS= "interlinker_multilang";
		const BKP_CROSSLINK_WDLN= "interlinker_lng_wds";

		//funkcia na overenie existencie tabuliek
		public function check_tbl_exists($in)
			{
				global $wpdb;

				$sql[] = "SHOW TABLES LIKE '".str_replace("_","\_",$in)."';";
				$sql[] = "SHOW TABLES LIKE '".$in."';";

				$found = 0;

				$cnt   = count($sql);
				for($i=0;$i<$cnt;$i++)
					{
						$res[$i] = $wpdb->get_var($sql[$i]);
						if($res[$i]==$in)
							{
								//table is found, let's remember this fact
								$found = 1;
							}
					}
				return $found;
			}

		//funkcia na pridanie do menu
		public function add_pages()
			{
				add_management_page('Cross-Linker Plug-In Management', 'Cross-Linker', 8, 'crosslinker', 'crossLinkerClass::cross_linker');
			}

		//funkcia na priradenie URL
		public function assign_correct_uri($uri)
			{
				global $wpdb;

				$table_name = $wpdb->prefix . "posts";
				$var        = "post:";
				if(@substr($uri,0,strlen($var))==$var)
					{
						$uri_id= @substr($uri,strlen($var));
						$uri   = $wpdb->get_var("SELECT guid FROM $table_name WHERE ID = '".$uri_id."' LIMIT 1;");
					}

				return $uri;
			}

		//maximalny pocet backupov
		public function maximum_backups()
			{
				global $wpdb;

				$ctb = $wpdb->prefix . $this::CROSSLINK_SETTS;

				$upd_id = $wpdb->get_var("SELECT value FROM ".$ctb." WHERE setting = 'remove_old_backups' LIMIT 1;");

				return $upd_id;
			}

		//maximalna dlzka bez backupu
		public function max_period_without_backup()
			{
				global $wpdb;

				$ctb = $wpdb->prefix . $this::CROSSLINK_SETTS;

				$upd_id = $wpdb->get_var("SELECT value FROM ".$ctb." WHERE setting = 'force_backup_days' LIMIT 1;");

				return $upd_id;
			}

		//overenie pre slashes
		public function check_chars($in)
			{
				$counter = 0;

				while( ($in != stripslashes($in)) && ($counter < $this::MAX_TRY) )
					{
						$in = stripslashes($in);
						$counter++;
					}

				$in = addslashes($in);
				return $in;
			}

		//odstranenie slashes
		public function uncheck_word($in)
			{
				$counter = 0;

				while( ($in != stripslashes($in)) && ($counter < $this::MAX_TRY) )
					{
						$in = stripslashes($in);
						$counter++;
					}
				return $in;
			}

		//iba horizontalna ciarka
		public function insert_hr()
			{
				return "<hr class=\"ddiv_style_clnkr\" />";
			}

		//priorita crosslinkera
		public function priority()
			{
				global $wpdb;

				$prio = $wpdb->get_var("SELECT value FROM ".$wpdb->prefix . $this::CROSSLINK_SETTS." WHERE setting = 'cl_priority' LIMIT 1;");

				if($prio=='')
					{
						$wpdb->query("INSERT INTO ".$wpdb->prefix . $this::CROSSLINK_SETTS." values ( 'NULL' , 'cl_priority' , '10' );");
						$prio = 10;
					}

				return $prio;
			}

		//meranie casu
		public function microtime_float()
			{
				list($usec, $sec) = explode(" ", microtime());
				return ((float)$usec + (float)$sec);
			}

		//nazov suboru na export db
		public function data_filename()
			{
				global $wpdb;

				$filename = $wpdb->get_var("SELECT value FROM ".$wpdb->prefix . $this::CROSSLINK_SETTS." WHERE setting = 'cl_data_filename' LIMIT 1;");

				return $filename;
			}

		//styly pre crosslinker
		public function stylesheet()
			{
				global $wpdb;

				//nalinkujeme jquery
				wp_enqueue_script("jquery");

				//vlozime css
				wp_register_style( 'cross-linker', plugins_url('mystyle.css', __FILE__) );
				wp_enqueue_style( 'cross-linker' );

				return true;

			}

		//javascript pre crosslinker
		public function javascript()
			{
				//vlozime javascript
				wp_enqueue_script(
					'cross-linker',
					plugins_url('scripts.js', __FILE__),
					array('jquery')
						);

				return true;
			}

		//ci existuje datasubor
		public function datafile_exists()
			{
				if (@file_exists(dirname(__FILE__)."/".$this->data_filename()))
					{
						$ret = 1;

						//is writable?
						$filename = dirname(__FILE__)."/".$this->data_filename();
						$handle   = @fopen($filename, "r");
						$contents = @fread($handle, filesize($filename));
						@fclose($handle);

						$fp = @fopen($filename, 'w');
						if( @fwrite($fp, $contents) !== false)
							$ret = 2;
						@fclose($fp);
						//end is writable?
					}
						else
							$ret = 0;
				return $ret;
			}

		//vytvorenie tabuliek na backupy a atributy
		public function add_mysql_tables()
			{
				global $wpdb;

				$table_name = $wpdb->prefix . $this::BKP_CROSSLINK_BKP;

				if($this->check_tbl_exists($table_name) == 0)
					{
						$sql = "CREATE TABLE IF NOT EXISTS `$table_name` ( 
`id` mediumint(7) UNSIGNED NOT NULL auto_increment, 
`timestamp` int(11) UNSIGNED NOT NULL, 
PRIMARY KEY  (`id`) 
) ENGINE=MyISAM; ";
						$wpdb->query($sql);
					}

				$table_name = $wpdb->prefix . $this::CROSSLINK_ATTRB;
				if($this->check_tbl_exists($table_name) == 0)
					{
						$sql = "CREATE TABLE IF NOT EXISTS `$table_name` ( 
 `id` mediumint(7) UNSIGNED NOT NULL, 
 `attrib` varchar(250) NOT NULL 
) ENGINE=MyISAM; ";
						$wpdb->query($sql);
					}

				$table_name = $wpdb->prefix . $this::CROSSLINK_TIMES;
				if($this->check_tbl_exists($table_name) == 0)
					{
						$sql = "CREATE TABLE IF NOT EXISTS `$table_name` (
  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `loadtime` float unsigned NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM;";
						$wpdb->query($sql);
					}
			}

		public function update_setting_clnkr($var,$db_var,$table)
			{
				global $wpdb;

				if($var=='on')
					$sql = "UPDATE ".$table." SET value = '1' WHERE setting = '".$db_var."';";
						else
								$sql = "UPDATE ".$table." SET value = '0' WHERE setting = '".$db_var."';";

				$wpdb->query($sql);
				return true;
			}

		public function drop_backup($drop_id,$table_name)
			{
				global $wpdb;

				$wpdb->query("DELETE from $table_name WHERE id = '$drop_id' limit 1;");

				$source_table = $wpdb->prefix . $this::BKP_CROSSLINK_CHAR . "_" . $drop_id;
				$wpdb->query("DROP TABLE $source_table;");

				$source_table = $wpdb->prefix . $this::BKP_CROSSLINK_TAGS . "_" . $drop_id;
				$wpdb->query("DROP TABLE $source_table;");

				$source_table = $wpdb->prefix . $this::BKP_CROSSLINK_SETT . "_" . $drop_id;
				$wpdb->query("DROP TABLE $source_table;");

				$source_table = $wpdb->prefix . $this::BKP_CROSSLINK_MAIN . "_" . $drop_id;
				$wpdb->query("DROP TABLE $source_table;");

				$source_table = $wpdb->prefix . $this::BKP_CROSSLINK_ATTR . "_" . $drop_id;
				$wpdb->query("DROP TABLE $source_table;");

				$source_table = $wpdb->prefix . $this::BKP_CROSSLINK_LNGS . "_" . $drop_id;
				$wpdb->query("DROP TABLE $source_table;");

				$source_table = $wpdb->prefix . $this::BKP_CROSSLINK_WDLN . "_" . $drop_id;
				$wpdb->query("DROP TABLE $source_table;");

				return true;
			}

		public function reset_setting_value($setting,$value)
			{
				global $wpdb;

				$table = $wpdb->prefix . $this::CROSSLINK_SETTS;
				$qry   = "SELECT id FROM $table WHERE setting = '".$setting."' LIMIT 1;";

				if($wpdb->get_var($qry,0)=='')
					{
						$sql = "INSERT INTO ".$table." values ( 'NULL' , '".$setting."' , '".$value."' );";
						$wpdb->query($sql);
					}
				return true;
			}

		public function get_data_file_db_word($sql,$i)
			{
				global $cl_file_data_words;

				global $wpdb;

				$ret = $wpdb->get_var($sql,0);

				return $ret;
			}

		public function get_data_file_db_url($sql,$i)
			{
				global $cl_file_data_links;

				global $wpdb;

				$ret = $wpdb->get_var($sql,0);

				return $ret;
			}

		public function get_data_file_db_attr($sql,$i)
			{
				global $cl_file_data_attrb;

				global $wpdb;

				$ret = $wpdb->get_var($sql,0);

				return $ret;
			}

		public function table_interlinker_install()
			{
				global $wpdb;

				//ak sme zamenili nazvy tabuliek pre divide a special, zmazeme ich a dropneme - potom sa automaticky vytvoria!
				$sql = "SELECT COUNT(*) FROM ".$wpdb->prefix.$this::CROSSLINK_CHARS.";";
				if($wpdb->get_var($sql)>1)
					$drop[] = "DROP TABLE ".$wpdb->prefix.$this::CROSSLINK_CHARS.";";
				$sql = "SHOW COLUMNS FROM ".$wpdb->prefix.$this::CROSSLINK_TAGS." WHERE lower(Field) = 'characters';";
				if($wpdb->get_var($sql)!='')
					$drop[] = "DROP TABLE ".$wpdb->prefix.$this::CROSSLINK_TAGS.";";

				$drop_count = count($drop);
				for($i=0;$i<$drop_count;$i++)
					$wpdb->query($drop[$i]); 
				unset($drop);

				//najprv osetrime engine, ak je innodb
				$sql = "SHOW TABLE STATUS FROM ".DB_NAME." WHERE Name LIKE '".$wpdb->prefix."interlinker%';";
				$zaznamy = $wpdb->get_results($sql);
				if($zaznamy)
					{
						foreach ( $zaznamy as $zaznam )
							{
								if(strtolower($zaznam->Engine)!="myisam")
									$wpdb->query("ALTER TABLE ".$zaznam->Name." ENGINE = 'MyISAM';");
							}
					}

				$table_name = $wpdb->prefix . $this::CROSSLINK_MAIN;
				if($this->check_tbl_exists($table_name) == 0)
					{
						$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
 `id` mediumint(7) unsigned NOT NULL AUTO_INCREMENT, 
  `link_word` varchar(250) CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL, 
  `link_url` text CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL, 
  `visible` enum('0','1') NOT NULL, 
  UNIQUE KEY `id` (`id`) 
) ENGINE=MyISAM ;";
						$wpdb->query($sql);

						add_option("jal_db_version", $this::JAL_DB_VERSION);
					}

				//indexy nemusia byt - hlavna tabulka
				$sql = "SHOW INDEX IN ".$table_name." WHERE Column_name = 'visible' ; ";
				if( $wpdb->get_var($sql,2) != '' )
					{
						$wpdb->query("ALTER TABLE `".$table_name."` DROP INDEX `visible` ;");
					}
				$sql = "SHOW INDEX IN ".$table_name." WHERE Column_name = 'link_word' ; ";
				if( $wpdb->get_var($sql,2) != '' )
					{
						$wpdb->query("ALTER TABLE `".$table_name."` DROP INDEX `link_word` ;");
					}
				//indexy nemusia byt - atributy
				$sql = "SHOW INDEX IN ". $wpdb->prefix . $this::CROSSLINK_ATTRB ." WHERE Column_name = 'attrib' ; ";
				if( $wpdb->get_var($sql,2) != '' )
					{
						$wpdb->query("ALTER TABLE `". $wpdb->prefix . $this::CROSSLINK_ATTRB ."` DROP INDEX `attrib` ;");
					}
				$sql = "SHOW INDEX IN ". $wpdb->prefix . $this::CROSSLINK_ATTRB ." WHERE Column_name = 'id' ; ";
				if( $wpdb->get_var($sql,2) != '' )
					{
						$wpdb->query("ALTER TABLE `". $wpdb->prefix . $this::CROSSLINK_ATTRB ."` DROP PRIMARY KEY ;");
					}

				$table_name = $wpdb->prefix . $this::CROSSLINK_TAGS;
				if($this->check_tbl_exists($table_name) == 0)
					{
						$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
id mediumint(7) NOT NULL auto_increment, 
tag_1  varchar(250) NOT NULL, 
tag_2  varchar(250) NOT NULL, 
PRIMARY KEY  (id) 
) ENGINE=MyISAM;";
						$wpdb->query($sql);

						$sql = "INSERT INTO ".$table_name." VALUES ( 'NULL' , '>' , '<' );";
						$wpdb->query($sql);
						$sql = "INSERT INTO ".$table_name." VALUES ( 'NULL' , '</h' , '<h' );";
						$wpdb->query($sql);
						$sql = "INSERT INTO ".$table_name." VALUES ( 'NULL' , '</strong' , '<strong' );";
						$wpdb->query($sql);
						$sql = "INSERT INTO ".$table_name." VALUES ( 'NULL' , '</b' , '<b' );";
						$wpdb->query($sql);
						$sql = "INSERT INTO ".$table_name." VALUES ( 'NULL' , '</a' , '<a' );";
						$wpdb->query($sql);
						$sql = "INSERT INTO ".$table_name." VALUES ( 'NULL' , '</textarea' , '<textarea' );";
						$wpdb->query($sql);

						//REMOVE duplicates if some are present
						$sql     = "SELECT tag_2, tag_1, id FROM ".$table_name." ORDER BY id ASC;";
						$zaznamy = $wpdb->get_results($sql);
						if($zaznamy)
							{
								foreach ( $zaznamy as $zaznam )
									{
										$delete = "DELETE FROM ".$table_name." WHERE (tag_2 LIKE '".$zaznam->tag_2."') AND (tag_1 LIKE '".$zaznam->tag_1."') AND (id > ".$zaznam->id.");";
										$wpdb->query($delete);
									}
							}
					}

				$table_name = $wpdb->prefix . $this::CROSSLINK_CHARS;
				if($this->check_tbl_exists($table_name) == 0)
					{
						$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
id mediumint(7) NOT NULL auto_increment,
characters text NOT NULL,
PRIMARY KEY  (id)
) ENGINE=MyISAM;";
						$wpdb->query($sql);

						$sql = "INSERT INTO ".$table_name." values ( 'NULL' , ' ; . , ) ( - : & > < ? ! * / +' );";
						$wpdb->query($sql);

						//FIX wrongly inserted rows on some mysql installations
						$sql = "DELETE FROM ".$table_name." WHERE id > 1;";
						$wpdb->query($sql);
					}

				$table_name = $wpdb->prefix . $this::CROSSLINK_SETTS;
				if($this->check_tbl_exists($table_name) == 0)
					{
						$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
id mediumint(7) NOT NULL auto_increment,
setting text NOT NULL,
value text NOT NULL,
PRIMARY KEY  (id)
) ENGINE=MyISAM;";
						$wpdb->query($sql);

						$sql = "INSERT INTO ".$table_name." values ( 'NULL' , 'link_to_thrusites' , '0' );";
						$wpdb->query($sql);
						$sql = "INSERT INTO ".$table_name." values ( 'NULL' , 'link_first_word' , '0' );";
						$wpdb->query($sql);

						//REMOVE duplicates if some are present
						$sql     = "SELECT id, setting FROM ".$table_name." ORDER BY id ASC;";
						$zaznamy = $wpdb->get_results($sql);
						if($zaznamy)
							{
								foreach ( $zaznamy as $zaznam )
									{
										$delete = "DELETE FROM ".$table_name." WHERE (setting LIKE '".$zaznam->setting."') AND (id > '".$zaznam->id."');";
										$wpdb->query($delete);
									}
							}
					}

				$table_name = $wpdb->prefix . $this::CROSSLINK_LNGS;
				if($this->check_tbl_exists($table_name) == 0)
					{
						$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
id mediumint(7) UNSIGNED NOT NULL AUTO_INCREMENT, 
lang_def varchar(200) COLLATE utf8_unicode_ci NOT NULL, 
visible enum('0','1') COLLATE utf8_unicode_ci NOT NULL, 
PRIMARY KEY (id) 
) ENGINE=MyISAM;";
						$wpdb->query($sql);
					}

				$table_name = $wpdb->prefix . $this::CROSSLINK_WDLNG;
				if($this->check_tbl_exists($table_name) == 0)
					{
						$sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
id mediumint(7) UNSIGNED NOT NULL AUTO_INCREMENT,
word_id mediumint(7) UNSIGNED NOT NULL,
lang_id smallint(3) UNSIGNED NOT NULL,
PRIMARY KEY (id)
) ENGINE=MyISAM;";
						$wpdb->query($sql);
					}
			}

		public function assign_lang($in)
			{
				global $wpdb;

				$ret = "<select name=\"modify_lang_word\" title=\"Choose language, please\">
";

				$sql = "SELECT id, lang_def from ".$wpdb->prefix . $this::CROSSLINK_LNGS." where lang_def > '' and visible = '1' order by lang_def asc;";

				$lngs      = 0;
				$found_sel = 0;

				while($wpdb->get_var($sql)!='')
					{
						$lngs++;
						$lang_def = stripslashes($wpdb->get_var($sql,1));
						$lang_id  = $wpdb->get_var($sql,0);

						if($in==$lang_id)
							{
								$found_sel = 1;
								$sel = " selected=\"selected\" ";
							}
								else
									$sel = "";
						$ret .= "<option value=\"".$lang_id."\"".$sel.">".ucfirst($lang_def)."</option>
";

						$sql = "SELECT id, lang_def from ".$wpdb->prefix . $this::CROSSLINK_LNGS." where lang_def > '".addslashes($lang_def)."' and visible = '1' order by lang_def asc;";
					}
				if($lngs==0)
					$ret .= "<option>No language is active/defined yet!</option>";
						else
							{
								if($found_sel==0)
									$sel = " selected=\"selected\" ";
										else
											$sel = "";
								$ret .= "<option value=\"-1\"".$sel.">Make available in all languages</option>";
							}

				$ret .= "</select>
";
				return $ret;
			}

		public function interlink_uninstall()
			{
				global $wpdb;

				$t[] = $wpdb->prefix . $this::CROSSLINK_MAIN;
				$t[] = $wpdb->prefix . $this::CROSSLINK_TAGS;
				$t[] = $wpdb->prefix . $this::CROSSLINK_CHARS;
				$t[] = $wpdb->prefix . $this::CROSSLINK_SETTS;
				$t[] = $wpdb->prefix . $this::CROSSLINK_ATTRB;
				$t[] = $wpdb->prefix . $this::CROSSLINK_LNGS;
				$t[] = $wpdb->prefix . $this::CROSSLINK_WDLNG;
				$t[] = $wpdb->prefix . $this::BKP_CROSSLINK_BKP;

				$j     = count($t);

				for($i=0;$i<$j;$i++)
					{
						if($this->check_tbl_exists($t[$i]) == 1)
							{
								$sql = "DROP TABLE " . $t[$i];
								$wpdb->query($sql);
							}
					}
			}

		public function get_data_file_db($sql,$i,$file_i,$lang)
			{
				global $cl_file_data_words, $cl_file_data_links, $cl_file_data_visib, $cl_file_data_langu, $cl_file_data_attrb, $min_id_words, $max_id_words;

				global $wpdb;

				$ret = "";

				if($cl_file_data_words[$i]=='')
					$ret = $wpdb->get_var($sql,0);
						else
							{
								for($z=$file_i;$z<=$max_id_words;$z++)
									if( ($cl_file_data_langu[$z]==$lang) || ($cl_file_data_langu[$z]==(-1)) )
										{
											$ret = $z;
											break;
										}
							}

				return $ret;
			}

		public function test_func()
			{
				global $wpdb, $crossLinkerObj;

				$t4 = $wpdb->prefix . $crossLinkerObj::CROSSLINK_SETTS;

				if( (is_admin()) && ($wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN.";")>$crossLinkerObj::CROSSLINK_EXCD) && mt_rand(0,100) == mt_rand(10,20) )
					{
						echo "<script type=\"text/javascript\">
alert('Warning generated by Cross-Linker: You should upgrade to PRO version since performance is greater when using PRO version! This warning is displayed in admin panel only from time to time.');
</script>";
					}

				return true;
			}

		public function interlink_w_u($content)
			{
				global $wpdb, $can_add_link, $crossLinkerObj;

				$cas['start'] = $crossLinkerObj->microtime_float();

				$crossLinkerObj->add_mysql_tables();
				//ak je vela zaznamov pre casy, premazeme tabulku
				$pocet['cas'] = $wpdb->get_var("SELECT COUNT(*) FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_TIMES.";");
				if($pocet['cas']>5000)
					$wpdb->query("TRUNCATE ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_TIMES.";");

				//speed up the process, don't load data from the database each time this function is executed
				global $linked_word, $linked_uri, $link_attribute, $processed_load, $restrict_linking, $link_to_itself, $find_sel, $no_link_s, $no_link_e, $old_i, $nn, $cut_empty_spaces, $link_document_language;

				global $cl_file_data_words, $cl_file_data_links, $cl_file_data_visib, $cl_file_data_langu, $cl_file_data_attrb, $min_id_words, $max_id_words; //stores all data loaded from file - load only once

				//REMOVE duplicate attributes if they somehow appeared in table
				$sql = "DELETE FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB." WHERE id NOT IN (SELECT id FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN.");";
				$wpdb->query($sql);

				$processed_load++;
				//end speed up

				$fix_me          = " remove_after_crosslinking ";

				$old_z           = $i;

				$content         = $fix_me.$content.$fix_me;

				//lang detection
				$link_document_language = -1;
				//$key je cislo jazyka pre dokument
				//end lang detection

				$table           = $wpdb->prefix . $crossLinkerObj::CROSSLINK_SETTS;

				if($processed_load<2)
					{
						$restrict_linking= $wpdb->get_var("SELECT value FROM $table WHERE setting = 'link_first_word' LIMIT 1;",0);
						$link_to_itself  = $wpdb->get_var("SELECT value FROM $table WHERE setting = 'link_to_itself' LIMIT 1;",0);

						if($wpdb->get_var("SELECT value FROM $table WHERE setting = 'limit_links' LIMIT 1;",0)>0)
							$find_sel  = $wpdb->get_var("SELECT value FROM $table WHERE setting = 'limit_links' LIMIT 1;",0);
								else
									$find_sel  = 0;
					}

				//load data from text file
				$min_id_words = 0;
				//end load data from text file

				$content_content = $content;
				$table           = $wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN;

				if($link_document_language==(-1))
					{
						$starting_sql = "SELECT id FROM $table WHERE visible = '1' ORDER BY id ASC LIMIT 1;";
						$gdfdb_lang   = -1;
					}
						else
							{
								$starting_sql = "SELECT ".$table.".id FROM ".$table.", ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG." WHERE ( (".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG.".lang_id = '".$link_document_language."') OR (".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG.".lang_id = '-1') ) AND (".$table.".id = ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG.".word_id) AND (".$table.".visible = '1') ORDER BY ".$table.".id ASC LIMIT 1;";
								$gdfdb_lang = $link_document_language;
							}

		//		$i               = $wpdb->get_var($starting_sql);
				$i               = $crossLinkerObj->get_data_file_db($starting_sql,$min_id_words,$min_id_words,$gdfdb_lang);
				$sql             = "SELECT * FROM $table WHERE id = '$i';";
				$j               = 0;
				$table_name_attrs= $wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB;

				if($processed_load<2)
					{
						while($crossLinkerObj->get_data_file_db($sql,$i,$i,$gdfdb_lang)!='')
		//				while($wpdb->get_var($sql,0)!='')
							{
								$j++;
								$linked_word[$j]    = $crossLinkerObj->uncheck_word(strtolower(stripslashes($crossLinkerObj->get_data_file_db_word("SELECT link_word FROM $table WHERE ( (id = '".$i."') AND (visible = '1') ) LIMIT 1;",$i))));
								$linked_uri[$j]     = $crossLinkerObj->uncheck_word(stripslashes($crossLinkerObj->get_data_file_db_url("SELECT link_url FROM $table WHERE ( (id = '".$i."') AND (visible = '1') ) LIMIT 1;",$i)));
								$link_attribute[$j] = $crossLinkerObj->uncheck_word(stripslashes($crossLinkerObj->get_data_file_db_attr("SELECT attrib FROM $table_name_attrs WHERE id = '$i' LIMIT 1;",$i)));
								$cur_j = $j;

								if(strpos($linked_word[$cur_j],"'")!==false)
									{
										$j++;
										$linked_word[$j]    = str_replace("'","&#8217;",$linked_word[$cur_j]);
										$linked_uri[$j]     = $linked_uri[$cur_j];
										$link_attribute[$j] = $link_attribute[$cur_j];
									}
								if(strpos($linked_word[$cur_j],"’")!==false)
									{
										$j++;
										$linked_word[$j]    = str_replace("’","&#8217;",$linked_word[$cur_j]);
										$linked_uri[$j]     = $linked_uri[$cur_j];
										$link_attribute[$j] = $link_attribute[$cur_j];
									}

								if($cut_empty_spaces==1)
									{
										for($pi=$cur_j;$pi<=$j;$pi++)
											{
												while(substr($linked_word[$pi],strlen($linked_word[$pi])-1,1)==' ')
													$linked_word[$pi] = substr($linked_word[$pi],0,strlen($linked_word[$pi])-1);
												while(substr($linked_word[$pi],0,1)==' ')
													$linked_word[$pi] = substr($linked_word[$pi],1,strlen($linked_word[$pi])-1);
												while(substr($linked_uri[$pi],strlen($linked_uri[$pi])-1,1)==' ')
													$linked_uri[$pi] = substr($linked_uri[$pi],0,strlen($linked_uri[$pi])-1);
												while(substr($linked_uri[$pi],0,1)==' ')
													$linked_uri[$pi] = substr($linked_uri[$pi],1,strlen($linked_uri[$pi])-1);
											}
									}

								if($link_document_language==(-1)) //original - bez visible = '1' nizsie
									$next_sql = "SELECT id FROM $table WHERE (id > '$i') AND (visible = '1') ORDER BY id asc LIMIT 1;";
										else
											$next_sql = "SELECT ".$table.".id FROM ".$table.", ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG." WHERE ( (".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG.".lang_id = '".$link_document_language."') OR (".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG.".lang_id = '-1') ) AND (".$table.".id = ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG.".word_id) AND (".$table.".visible = '1') AND (".$table.".id > '".$i."') ORDER BY ".$table.".id ASC LIMIT 1;";

		//						$i   = $wpdb->get_var($next_sql,0);
								$i   = $crossLinkerObj->get_data_file_db($next_sql,$i,($i+1),$gdfdb_lang);
								$sql = "SELECT id FROM $table WHERE id = '$i';";
							}
						$change = 1;
						$i      = $j;
						$old_i  = $j;
						while($change==1)
							{
								$change = 0;
								for($j=1;$j<$i;$j++)
									{
										if( @strlen($linked_word[$j]) < @strlen($linked_word[($j+1)]) )
											{
												$t1                  = $linked_word[($j+1)];
												$linked_word[($j+1)] = $linked_word[$j];
												$linked_word[$j]     = $t1;

												$t1                  = $linked_uri[($j+1)];
												$linked_uri[($j+1)]  = $linked_uri[$j];
												$linked_uri[$j]      = $t1;

												$t1                     = $link_attribute[($j+1)];
												$link_attribute[($j+1)] = $link_attribute[$j];
												$link_attribute[$j]     = $t1;

												$change              = 1;
											}
									}
							}
					}

				$t2  = $wpdb->prefix . $crossLinkerObj::CROSSLINK_TAGS;

				if($processed_load<2)
					{
						$i   = $wpdb->get_var("SELECT * FROM $t2 ORDER BY id ASC LIMIT 1;",0);
						$sql = "SELECT * FROM $t2 WHERE id = '$i';";
						$nn  = 0;
						while($wpdb->get_var($sql,0)!='')
							{
								$nn++;
								$no_link_s[$nn] = $wpdb->get_var($sql,1);
								$no_link_e[$nn] = $wpdb->get_var($sql,2);

								$i   = $wpdb->get_var("SELECT * FROM $t2 WHERE id > '$i' ORDER BY id asc LIMIT 1;",0);
								$sql = "SELECT * FROM $t2 WHERE id = '$i';";
							}
						$nn++;
						$no_link_s[$nn] = "<!--nocrosslink_end-->";
						$no_link_e[$nn] = "<!--nocrosslink_start-->";
						$nn++;
					}

				for($j=1;$j<=$old_i;$j++)
					{
						$r_extra      = 0;
						$starting_pos = 0;

						while(@strpos(strtolower($content_content),$linked_word[$j],$starting_pos)!==false)
							{
								$temporary_uri= $linked_uri[$j];
								$previous_pos = $starting_pos - @strlen($linked_word[$j]);
								$starting_pos = @strpos(strtolower($content_content),$linked_word[$j],$starting_pos) + @strlen($linked_word[$j]);
								$remain       = @substr($content_content,$starting_pos);
								$presiel      = 1;

								for($x=1;$x<$nn;$x++)
									{
										if(@strpos(strtolower($remain),$no_link_s[$x])!==false)
											{
												if(@strpos(strtolower($remain),$no_link_e[$x])===false)
													$presiel = 0;
														else
															{
																if(@strpos(strtolower($remain),$no_link_s[$x])<@strpos(strtolower($remain),$no_link_e[$x]))
																	$presiel = 0;
															}
											}
									}

								$remember_slash = "";
								if($temporary_uri[(@strlen($temporary_uri)-1)]=='/')
									{
										$remember_slash = "/";
										$temporary_uri  = @substr($temporary_uri,0,(@strlen($temporary_uri)-1));
									}
								if($presiel==1)
									{
										$new_slash  = 0;
										while(@strpos($temporary_uri,"/",$new_slash)!==false)
											$new_slash = @strpos($temporary_uri,"/",$new_slash) + 1;
										$point_to   = @substr($temporary_uri,$new_slash);

										$this_uri   = $_SERVER['REQUEST_URI'];

										$remember_slash1 = "";
										if($this_uri[(@strlen($this_uri)-1)]=='/')
											{
												$this_uri = @substr($this_uri,0,(@strlen($this_uri)-1));
											}

										$new_slash  = 0;
										while(@strpos($this_uri,"/",$new_slash)!==false)
											$new_slash = @strpos($this_uri,"/",$new_slash) + 1; // +1 lebo / ma dlzku 1
												$current_uri = @substr($this_uri,$new_slash);

										$var             = "post:";
										$new_point_to    = $point_to;
										$is_the_same_uri = 0;
										if(@substr($point_to,0,strlen($var))==$var)
											{
												$pageURL = 'http';
												if ($_SERVER["HTTPS"] == "on")
													{$pageURL .= "s";}
												$pageURL .= "://";
												$real_current_uri = $pageURL.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

												if($real_current_uri==$crossLinkerObj->assign_correct_uri($point_to))
													$is_the_same_uri = 1;
														else
															$is_the_same_uri = 0;
											}
												else
													$is_the_same_uri = 0;

										if( ( ($crossLinkerObj->assign_correct_uri($point_to)==$current_uri)&&($link_to_itself!=1) ) || (($link_to_itself!=1)&&($is_the_same_uri==1)) )
											{
												$presiel = 0;
											}
									}

								$z  = 1;

								$t3 = $wpdb->prefix . $crossLinkerObj::CROSSLINK_CHARS;

								$upgrade_fatal_01 = $wpdb->get_var("SELECT characters FROM $t3 WHERE id = 1 LIMIT 1;",0);
								if($upgrade_fatal_01[0]!=' ')
									$upgrade_fatal_01 = " ".$upgrade_fatal_01;
								$ending_part                        = @explode(" ", $upgrade_fatal_01);
								$ending_part_t                      = $wpdb->get_var("SELECT characters FROM $t3 WHERE id = '1' LIMIT 1;",1);
								$ending_part[@count($ending_part)]  = " ";
								$ending_part[@count($ending_part)]  = chr(10);

								$z                                = @count($ending_part);
								$found_z                          = 0;

								for($l=1;$l<$z;$l++)
									if($content_content[($starting_pos)]==$ending_part[$l])
										$found_z = 1;

								$found_z1         = 0;
								for($l=1;$l<$z;$l++)
									if($content_content[($starting_pos-@strlen($linked_word[$j])-1)]==$ending_part[$l])
										$found_z1 = 1;

								if(($found_z!=1)||($presiel!=1)||($found_z1!=1))
									$presiel = 0;

								if($presiel==1)
									{
										$r_extra++;
										$original_word      = @substr($content_content,$starting_pos-@strlen($linked_word[$j]),@strlen($linked_word[$j]));
										$supplemental       = $original_word;

										$old_part         = @substr($content_content,($previous_pos+@strlen($linked_word[$j])),($starting_pos-$previous_pos-@strlen($linked_word[$j])));
										$new_part         = @str_replace($supplemental,"<a href=\"".$crossLinkerObj->assign_correct_uri($temporary_uri.$remember_slash)."\" ".$link_attribute[$j].">".$supplemental."</a>",$old_part);
										$starting_pos     = @strlen($new_part)-@strlen($old_part)+$starting_pos;

										$found_invalid_code = 0;

										if(($find_sel!=0)&&($find_sel<$r_extra))
											$stop_linking = 1;
												else
													$stop_linking = 0;
										$can_link_to_src = 0;
										if( ( ( ($restrict_linking==1) && ($r_extra<2) ) || ($restrict_linking==0) ) && ( $stop_linking == 0 ) && ( $found_invalid_code == 0 ) )
											{
												$can_link_to_src = 1;
												$content_content = @str_replace($old_part,$new_part,$content_content);
											}
									}
							}
					}

				$content_content = @str_replace($fix_me,"",$content_content);

				if( ($can_add_link==0) && ($can_link_to_src==1) )
					{
						$content_content .= "<p style=\"opacity:0.5;padding:0;margin:0;display:inline;\"><sub><a href=\"#\" onclick=\"window.open('https://www.jan"."h"."vizdak.com/rdr.me.1'); return false;\" target=\"_blank\" style=\"cursor:help;\"><b>&#187;crosslinked&#171;</b></a></sub></p>";
						$can_add_link     = 1;
					}
		//		$content_content .= dirname(__FILE__) ;

				$cas['koniec'] = $crossLinkerObj-> microtime_float();

				//ulozime cas, ktory robil crosslinker
				$sql = "INSERT INTO " . $wpdb->prefix . $crossLinkerObj::CROSSLINK_TIMES . " VALUES ( 'NULL' , '".($cas['koniec'] - $cas['start'])."');";
				$wpdb->query($sql);

				return $content_content;
			}

		public function cross_linker()
			{
				if (!is_user_logged_in())
					{
						die("sorry, unauthorised access to cross-linker!");
					}

				global $wpdb, $cut_empty_spaces, $crossLinkerObj;

				$fix_uri = str_replace("&del_word=".$_REQUEST['del_word'],"",$_SERVER['REQUEST_URI']);
				$fix_uri = str_replace("&del_lang=".$_REQUEST['del_lang'],"",$fix_uri);

				//perform automatic modification of settings table due to backup upgrade
				if($wpdb->get_var("show columns from ".$wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_BKP." where Field = 'keep_forever';",0)=='')
					{
						$wpdb->query("ALTER TABLE ".$wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_BKP." ADD keep_forever ENUM( '0', '1' ) NOT NULL , ADD INDEX ( keep_forever );");
					}

				$paid_version_text = "<p style=\"font-weight:bold;margin:0;padding:0.5em;font-size:medium;color:red;\">Ops, this is a feature that works with <a href=\"https://www.janhvizdak.com/make-donation-cross-linker-plugin-wordpress.html#dlod\" target=\"_blank\">PRO version</a> only.</p>
";

				//reset all settings and values if not exist!!!
				$crossLinkerObj->reset_setting_value("link_to_thrusites",0);
				$crossLinkerObj->reset_setting_value("link_first_word",0);
				$crossLinkerObj->reset_setting_value("link_pages",1);
				$crossLinkerObj->reset_setting_value("link_comments",0);
				$crossLinkerObj->reset_setting_value("link_posts",1);
				$crossLinkerObj->reset_setting_value("link_to_permalinks",0);
				$crossLinkerObj->reset_setting_value("link_to_itself",0);
				$crossLinkerObj->reset_setting_value("delete_option",0);
				$crossLinkerObj->reset_setting_value("limit_links",0);

				//echo went to scripts.js!!!

				$optimisation_done = "";

				if( ($_POST['force_backup_days']!='') && ($_POST['remove_old_backups']!='') )
					{
						$optimisation_done = $paid_version_text;
					}

				if(($_POST['restore_backup']!='')&&($_POST['agree']=='on'))
					{
						$table_name =  $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_BKP;

						$time = intval($_POST['restore_backup']);
						$id   = $wpdb->get_var("SELECT id FROM $table_name WHERE timestamp = '".$time."' limit 1;");

						$source_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_MAIN . "_" . $id;
						$target_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN;
						$wpdb->query("DROP TABLE $target_table");
						$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
						$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");

						$source_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_CHAR . "_" . $id;
						$target_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_CHARS;
						$wpdb->query("DROP TABLE $target_table");
						$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
						$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");

						$source_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_TAGS . "_" . $id;
						$target_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_TAGS;
						$wpdb->query("DROP TABLE $target_table");
						$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
						$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");

						$source_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_SETT . "_" . $id;
						$target_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_SETTS;
						$wpdb->query("DROP TABLE $target_table");
						$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
						$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");

						$source_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_ATTR . "_" . $id;
						$target_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB;
						if($crossLinkerObj->check_tbl_exists($source_table) == 1)
							{
								$wpdb->query("DROP TABLE $target_table");
								$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
								$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");
							}

						$source_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_LNGS . "_" . $id;
						$target_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_LNGS;
						if($crossLinkerObj->check_tbl_exists($source_table) == 1)
							{
								$wpdb->query("DROP TABLE $target_table");
								$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
								$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");
							}

						$source_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_WDLN . "_" . $id;
						$target_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG;
						if($crossLinkerObj->check_tbl_exists($source_table) == 1)
							{
								$wpdb->query("DROP TABLE $target_table");
								$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
								$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");
							}

						echo "<script type=\"text/javascript\">
<!--
			alert (\"The backup has been restored successfully!\");
		-->
</script>
";
					}

				//PRO - START
				//optimize whole database
				if($_POST['optimise_database'] == '1' )
					{
						$optimisation_done = $paid_version_text;
					}
				//end optimize whole database
				//PRO - END

		 		//string append/prepend/replace
				if($_POST['prepend_string']!='')
					{
		 				$str_to_prepend = stripslashes($_POST['prepend_string']);

		 				$posts_modify    = $_POST['prepend_posts'];
		 				$comments_modify = $_POST['prepend_comments'];
		 				$counter_posts   = 0;
		 				$counter_comms   = 0;

		 				if(strtolower($posts_modify)=='on')
		 					{
		 						$sql = "SELECT ID, post_content FROM ".$wpdb->posts." order by ID asc limit 1;";
		 						while($wpdb->get_var($sql,0)!='')
		 							{
		 								$counter_posts++;
		 								$this_id   = $wpdb->get_var($sql,0);
		 								$this_post = $wpdb->get_var($sql,1);

		 								$update    = "update ".$wpdb->posts." set post_content = '".addslashes(stripslashes($str_to_prepend.$this_post))."' where ID = '".$this_id."' LIMIT 1;";
		 								$wpdb->query($update);

		 								$sql = "SELECT ID, post_content FROM ".$wpdb->posts." WHERE ID > '".$this_id."' order by ID asc limit 1;";
		 							}
		 					}

		 				if(strtolower($comments_modify)=='on')
		 					{
		 						$sql = "SELECT comment_ID, comment_content FROM ".$wpdb->comments." order by comment_ID asc limit 1;";
		 						while($wpdb->get_var($sql,0)!='')
		 							{
		 								$counter_comms++;
		 								$this_id   = $wpdb->get_var($sql,0);
		 								$this_post = $wpdb->get_var($sql,1);

		 								$update    = "update ".$wpdb->comments." set comment_content = '".addslashes(stripslashes($str_to_prepend.$this_post))."' where comment_ID = '".$this_id."' LIMIT 1;";
		 								$wpdb->query($update);

		 								$sql = "SELECT comment_ID, comment_content FROM ".$wpdb->comments." WHERE comment_ID > '".$this_id."' order by comment_ID asc limit 1;";
		 							}
		 					}
		 				echo "<script type=\"text/javascript\"><!--
		alert(\"Prepend performed on ".$counter_posts." posts (database records including drafts, autosaves) and ".$counter_comms." comments (database records including drafts, autosaves)!\");
		--></script>";
		 
		 			}
		 
		 		if($_POST['append_string']!='')
		 			{
		 				$str_to_append = stripslashes($_POST['append_string']);
		 
		 				$posts_modify    = $_POST['append_posts'];
		 				$comments_modify = $_POST['append_comments'];
		 				$counter_posts   = 0;
		 				$counter_comms   = 0;
		 
		 				if(strtolower($posts_modify)=='on')
		 					{
		 						$sql = "SELECT ID, post_content FROM ".$wpdb->posts." order by ID asc limit 1;";
		 						while($wpdb->get_var($sql,0)!='')
		 							{
		 								$counter_posts++;
		 								$this_id   = $wpdb->get_var($sql,0);
		 								$this_post = $wpdb->get_var($sql,1);

		 								$update    = "update ".$wpdb->posts." set post_content = '".addslashes(stripslashes($this_post.$str_to_append))."' where ID = '".$this_id."' LIMIT 1;";
		 								$wpdb->query($update);

		 								$sql = "SELECT ID, post_content FROM ".$wpdb->posts." WHERE ID > '".$this_id."' order by ID asc limit 1;";
		 							}
		 					}

		 				if(strtolower($comments_modify)=='on')
		 					{
		 						$sql = "SELECT comment_ID, comment_content FROM ".$wpdb->comments." order by comment_ID asc limit 1;";
		 						while($wpdb->get_var($sql,0)!='')
		 							{
		 								$counter_comms++;
		 								$this_id   = $wpdb->get_var($sql,0);
		 								$this_post = $wpdb->get_var($sql,1);

		 								$update    = "update ".$wpdb->comments." set comment_content = '".addslashes(stripslashes($this_post.$str_to_append))."' where comment_ID = '".$this_id."' LIMIT 1;";
		 								$wpdb->query($update);

		 								$sql = "SELECT comment_ID, comment_content FROM ".$wpdb->comments." WHERE comment_ID > '".$this_id."' order by comment_ID asc limit 1;";
		 							}
		 					}
		 				echo "<script type=\"text/javascript\"><!--
		alert(\"Append performed on ".$counter_posts." posts (database records including drafts, autosaves) and ".$counter_comms." comments (database records including drafts, autosaves)!\");
		--></script>";

					}

				if($_POST['string_replace_1']!='')
					{
		 				$string_1 = stripslashes($_POST['string_replace_1']);
		 				$string_2 = stripslashes($_POST['string_replace_2']);

		 				$posts_modify    = $_POST['replace_posts'];
		 				$comments_modify = $_POST['replace_comments'];
		 				$counter_posts   = 0;
		 				$counter_comms   = 0;

		 				if(strtolower($posts_modify)=='on')
		 					{
		 						$sql = "SELECT ID, post_content FROM ".$wpdb->posts." WHERE post_content like '%".addslashes($string_1)."%' order by ID asc limit 1;";
		 						while($wpdb->get_var($sql,0)!='')
		 							{
		 								$counter_posts++;
		 								$this_id   = $wpdb->get_var($sql,0);
		 								$this_post = $wpdb->get_var($sql,1);

		 								$this_post = str_replace($string_1,$string_2,$this_post);

		 								$update    = "update ".$wpdb->posts." set post_content = '".addslashes(stripslashes($this_post))."' where ID = '".$this_id."' LIMIT 1;";
		 								$wpdb->query($update);

		 								$sql = "SELECT ID, post_content FROM ".$wpdb->posts." WHERE ( post_content like '%".addslashes($string_1)."%' ) AND ( ID > '".$this_id."' ) order by ID asc limit 1;";
		 							}
		 					}

		 				if(strtolower($comments_modify)=='on')
		 					{
		 						$sql = "SELECT comment_ID, comment_content FROM ".$wpdb->comments." WHERE comment_content like '%".addslashes($string_1)."%' order by comment_ID asc limit 1;";
		 						while($wpdb->get_var($sql,0)!='')
		 							{
		 								$counter_comms++;
		 								$this_id   = $wpdb->get_var($sql,0);
		 								$this_post = $wpdb->get_var($sql,1);

		 								$this_post = str_replace($string_1,$string_2,$this_post);

		 								$update    = "update ".$wpdb->comments." set comment_content = '".addslashes(stripslashes($this_post))."' where comment_ID = '".$this_id."' LIMIT 1;"; echo "ok<br />";
		 								$wpdb->query($update);

		 								$sql = "SELECT comment_ID, comment_content FROM ".$wpdb->comments." WHERE ( comment_content like '%".addslashes($string_1)."%' ) AND ( comment_ID > '".$this_id."' ) order by comment_ID asc limit 1;";
		 							}
		 					}
		 				echo "<script type=\"text/javascript\"><!--
		alert(\"Replace performed on ".$counter_posts." posts (database records including drafts, autosaves) and ".$counter_comms." comments (database records including drafts, autosaves)!\");
		--></script>";

					}
				//end string append/prepend/replace

				if(($_POST['delete_backup']!='')&&($_POST['agree']=='on'))
					{
						$table_name =  $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_BKP;

						$time    = intval($_POST['delete_backup']);
						$drop_id = $wpdb->get_var("SELECT id FROM $table_name WHERE timestamp = '$time' limit 1;");

						$crossLinkerObj->drop_backup($drop_id,$table_name);
					}

				$create_force_backup = 0; //normally don't create backup

				$crossLinkerObj->add_mysql_tables();

				$t4    = $wpdb->prefix . $crossLinkerObj::CROSSLINK_SETTS;

				//update options
				if($_POST['up_set']==1)
					{

						$crossLinkerObj->update_setting_clnkr($_POST['link_to_thrusites'],"link_to_thrusites",$t4);
						$crossLinkerObj->update_setting_clnkr($_POST['link_first_word'],"link_first_word",$t4);
						$crossLinkerObj->update_setting_clnkr($_POST['link_comments'],"link_comments",$t4);
						$crossLinkerObj->update_setting_clnkr($_POST['link_pages'],"link_pages",$t4);
						$crossLinkerObj->update_setting_clnkr($_POST['link_posts'],"link_posts",$t4);
						$crossLinkerObj->update_setting_clnkr($_POST['delete_option'],"delete_option",$t4);
						$crossLinkerObj->update_setting_clnkr($_POST['link_to_permalinks'],"link_to_permalinks",$t4);
						$crossLinkerObj->update_setting_clnkr($_POST['link_to_itself'],"link_to_itself",$t4);

						$s1 = $_POST['limitlinking'];
						if($s1!='')
							$wpdb->query("UPDATE $t4 SET value = '$s1' WHERE setting = 'limit_links';");
						//unusual case

						//priority definition
						$s1 = abs(intval($_POST['priority_clnk']));
						if($s1!='')
							$wpdb->query("UPDATE $t4 SET value = '".$s1."' WHERE setting = 'cl_priority';");
					}

				//assign one attribute to all without attribute
				if($_POST['attrib_assign_to_all']!='')
					{
						$assign_to_all = $_POST['attrib_assign_to_all'];

						$table_name       = $wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN;
						$table_name_attrs = $wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB;

						$this_minimum     = $wpdb->get_var("SELECT id FROM $table_name ORDER BY id ASC LIMIT 1;");
						$sql              = "SELECT id FROM $table_name WHERE id = '$this_minimum';";

						while($wpdb->get_var($sql)!='')
							{
								$sql_1 = "SELECT id FROM $table_name_attrs WHERE id = '$this_minimum';";

								if( ($wpdb->get_var($sql_1)=='') || ( ($wpdb->get_var($sql_1)!='') && (strlen($wpdb->get_var("SELECT attrib FROM $table_name_attrs WHERE id = '$this_minimum';"))<1) ) )
									{
										if($wpdb->get_var($sql_1)=='')
											$sql_2 = "INSERT INTO ".$table_name_attrs." values ( '$this_minimum' , '$assign_to_all' );";
												else
													$sql_2 = "UPDATE ".$table_name_attrs." SET attrib = '$assign_to_all' WHERE id = '$this_minimum' LIMIT 1;";
										$wpdb->query($sql_2);
									}
								$sql          = "SELECT id FROM $table_name WHERE id > '$this_minimum' ORDER BY ID ASC;";
								$this_minimum = $wpdb->get_var($sql);
							}
					}
				//end

				$linkto_word = $_POST['linker_word'];
				$linkto_uri  = $_POST['linker_uri'];
				$linkto_attr = stripslashes($_POST['linker_attr']);

				$table_name =  $wpdb->prefix . $crossLinkerObj::CROSSLINK_SETTS;

				if($_POST['show_news_12']!='')
					{
						$cid = intval($_REQUEST['show_news_12']);
						if($wpdb->get_var("SELECT id FROM $table_name WHERE setting = 'news_1_2' limit 1;")!='')
							$wpdb->query("UPDATE $table_name SET value = '0' WHERE setting = 'news_1_2' limit 1;");
								else
									$wpdb->query("INSERT INTO $table_name values ( 'NULL' , 'news_1_2' , '0' )");
					}

				if($_POST['recommend_link_12']!='')
					{
						$cid = intval($_REQUEST['recommend_link_12']);
						if($wpdb->get_var("SELECT id FROM $table_name WHERE setting = 'recommend_link_12' limit 1;")!='')
							$wpdb->query("UPDATE $table_name SET value = '0' WHERE setting = 'recommend_link_12' limit 1;");
								else
									$wpdb->query("INSERT INTO $table_name values ( 'NULL' , 'recommend_link_12' , '0' )");
					}

				if($_POST['bigchanges']!='')
					{
						$cid = intval($_REQUEST['bigchanges']);
						if($wpdb->get_var("SELECT id FROM $table_name WHERE setting = 'bigchanges' limit 1;")!='')
							$wpdb->query("UPDATE $table_name SET value = '0' WHERE setting = 'bigchanges' limit 1;");
								else
									$wpdb->query("INSERT INTO $table_name values ( 'NULL' , 'bigchanges' , '0' )");
					}

				if($_POST['forgot_something_130']!='')
					{
						$cid = intval($_REQUEST['forgot_something_130']);
						if($wpdb->get_var("SELECT id FROM $table_name WHERE setting = 'forgot_something_130' limit 1;")!='')
							$wpdb->query("UPDATE $table_name SET value = '0' WHERE setting = 'forgot_something_130' limit 1;");
								else
									$wpdb->query("INSERT INTO $table_name values ( 'NULL' , 'forgot_something_130' , '0' )");
					}

				if($_POST['bug_reports_12']!='')
					{
						$cid = intval($_REQUEST['bug_reports_12']);
						if($wpdb->get_var("SELECT id FROM $table_name WHERE setting = 'bug_reports_12' limit 1;")!='')
							$wpdb->query("UPDATE $table_name SET value = '0' WHERE setting = 'bug_reports_12' limit 1;");
								else
									$wpdb->query("INSERT INTO $table_name values ( 'NULL' , 'bug_reports_12' , '0' )");
					}

				if($_POST['valid_code_131']!='')
					{
						$cid = intval($_REQUEST['valid_code_131']);
						if($wpdb->get_var("SELECT id FROM $table_name WHERE setting = 'valid_code_131' limit 1;")!='')
						$wpdb->query("UPDATE $table_name SET value = '0' WHERE setting = 'valid_code_131' limit 1;");
							else
								$wpdb->query("INSERT INTO $table_name values ( 'NULL' , 'valid_code_131' , '0' )");
					}
				if($_POST['core']!='')
					{
						$cid = intval($_REQUEST['core']);
						if($wpdb->get_var("SELECT id FROM $table_name WHERE setting = 'cut_empty_spaces' limit 1;")!='')
							$wpdb->query("UPDATE $table_name SET value = '$cid' WHERE setting = 'cut_empty_spaces' limit 1;");
								else
									$wpdb->query("INSERT INTO $table_name values ( 'NULL' , 'cut_empty_spaces' , '$cid' )");
					}
				if($_POST['core_s']!='')
					{
						$cid = intval($_REQUEST['core_s']);
						if($wpdb->get_var("SELECT id FROM $table_name WHERE setting = 'cut_empty_spaces' limit 1;")!='')
							$wpdb->query("UPDATE $table_name SET value = '$cid' WHERE setting = 'cut_empty_spaces' limit 1;");
								else
									$wpdb->query("INSERT INTO $table_name values ( 'NULL' , 'cut_empty_spaces' , '$cid' )");
					}
				$fn_str = mt_rand(1,mt_rand(900,1100))*mt_rand(1,mt_rand(900,1100))/mt_rand(1,mt_rand(900,1100))/mt_rand(1,mt_rand(900,1100))*mt_rand(1,mt_rand(900,1100));
				for($fn_rnd=1;$fn_rnd<mt_rand(100,200);$fn_rnd++)
					{
						if(mt_rand(1,10)<5)
							$fn_str = md5($fn_str);
								else
									$fn_str = sha1($fn_str);
					}
				$fn_str = substr($fn_str,0,mt_rand(8,10));
				if($crossLinkerObj->data_filename()=='')
					$filename = $wpdb->query("INSERT INTO ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_SETTS." VALUES ( 'NULL' , 'cl_data_filename' , 'cl".$fn_str.".dat');");

				echo "<div class=\"wrap\">
";

				echo "<div id=\"crosslinker_content\">
";

				//ak nemame top
				if(!is_plugin_active("top-page-quality-analytics/top-analytics.php"))
					echo "<div id=\"warning\"><strong>TOP Analytics</strong> is not running, please install it - plugin <a href=\"http://wordpress.org/plugins/top-page-quality-analytics/\" target=\"_blank\">can be downloaded here</a> from official WordPress reporitory!</div>
";

				//ak je javascript vypnuty
				echo "<noscript>JavaScript has to be enabled in order to run Cross-Linker!</noscript>";

				echo "<div class=\"cdiv_style_clnkr\">
";

				echo "<h2>Cross-Linker v".$crossLinkerObj::VERSION." FREE <span style=\"font-size:90%;\">&raquo;brought to you by <a href=\"https://www.janhvizdak.com/\" title=\"Visit my website if you need PHP/MySQL consultation!\">Jan Hvizdak</a></span></h2>
";

				$sql = "SELECT avg( loadtime ) * 0.98 FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_TIMES.";";
				$tmi = $wpdb->get_var($sql,0);
				if($tmi>0.1)
					{
						echo "<p id=\"warnme_ccl\">PRO version of Cross-Linker could save approximately ".round($tmi,2)." second per page load when your visitors browse your website! Consider upgrading now and get maximum performance!</p>";
					}
				echo "<h3>Important notice</h3>This is a <b>free</b> version with <span style=\"color:red\"><b>basic</b></span> features and limited support! Get a PRO version at <a href=\"https://www.janhvizdak.com/make-donation-cross-linker-plugin-wordpress.html\" target=\"_blank\" title=\"Purchase PRO version and get maximum performance!\">janhvizdak.com/make-donation-cross-linker-plugin-wordpress.html</a> and get: Guaranteed 100% testing, Backlink-free version, SUPERB performance, optimisation of data, multilingual hyperlinking, full statistics of linking, and more! Moreover you will get extra releases at free of charge.
";

				echo "</div>
";

				echo "<span>&raquo;problems or want new features? get in touch @ <a href=\"https://www.janhvizdak.com/make-donation-cross-linker-plugin-wordpress.html\" target=\"_blank\" title=\"Suggest features, request help or assistance\">janhvizdak.com/make-donation-cross-linker-plugin-wordpress.html</a><br /></span>
";

				echo $optimisation_done;
				echo "<small>Performing routine database optimisation check (Cross-Linker only; skipping backup tables) ";

				$tbl_to_check[] = $wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN;
				$tbl_to_check[] = $wpdb->prefix . $crossLinkerObj::CROSSLINK_TAGS;
				$tbl_to_check[] = $wpdb->prefix . $crossLinkerObj::CROSSLINK_CHARS;
				$tbl_to_check[] = $wpdb->prefix . $crossLinkerObj::CROSSLINK_SETTS;
				$tbl_to_check[] = $wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB;
				$tbl_to_check[] = $wpdb->prefix . $crossLinkerObj::CROSSLINK_LNGS;
				$tbl_to_check[] = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_BKP;
				$tbl_to_check[] = $wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG;

				$check_string   = "";
				for($pcheck=0;$pcheck<count($tbl_to_check);$pcheck++)
					{
						if($pcheck<count($tbl_to_check)-1)
							$check_string .= "'".$tbl_to_check[$pcheck]."',";
								else
									$check_string .= "'".$tbl_to_check[$pcheck]."'";
					}

				$validation_counter = 0;
				$vsql   = "SHOW TABLE STATUS FROM ".DB_NAME." WHERE Data_free>0 AND Name IN (".$check_string.") AND upper( ENGINE ) = 'MYISAM';";

				while( ($wpdb->get_var($vsql,0)!='') && ($validation_counter<=count($tbl_to_check)) )
					{
						$validation_counter++;
						$wpdb->query("OPTIMIZE TABLE ".$wpdb->get_var($vsql,0).";");
					}
				echo "--&gt;OK<br /></small>
";

				$t4    = $wpdb->prefix . $crossLinkerObj::CROSSLINK_SETTS;

				if($wpdb->get_var("SELECT id FROM $t4 WHERE ( (setting = 'link_to_permalinks') AND (value = '1') ) LIMIT 1;")!='')
					{
						$add_permalinks  = "<span style=\"color: #007e46;\"><br /><label for=\"permalinkselect\">Or select a post from the following list</label><br /><small>
		1) URLs will be loaded from the original WP's MySQL tables, so if your WP uses some plugin which rewrites URLs, make sure that all original URLs are redirected via the 301 redirect.<br />
		2) Published posts are these which contain \"publish\" in the <b>post_status</b> column.<br />
		3) Links are values in the <b>guid</b> column.</small></span><br />
<script type=\"text/javascript\">
		     <!--
		      function movetourl()
		       {
			if(document.linkerform.permalinkselect.value!='0')
			 document.linkerform.linkeruri.value = 'post:' + document.linkerform.permalinkselect.value;
			  else
			   document.linkerform.linkeruri.value = '';
		       }
		     -->
</script>
";
						$table_posts     = $wpdb->prefix . "posts";
						$sql             = "select ID, post_title, guid from $table_posts where post_status = 'publish' order by ID desc limit 1;";
						$add_permalinks .= "<select name=\"permalinkselect\" onchange=\"movetourl();\" id=\"permalinkselect\" title=\"Choose one of links, or type URL above\">";
						$add_permalinks .= "<option value=\"0\" selected=\"selected\">Ignore this option</option>";
						while($wpdb->get_var($sql,0)!='')
							{
								$new_id          = $wpdb->get_var($sql,0);
								$add_permalinks .= "<option value=\"".$new_id."\">".$wpdb->get_var($sql,1)." (".$wpdb->get_var($sql,2).")</option> ";
								$sql             = "select ID, post_title, guid from $table_posts where ( (post_status = 'publish') AND (ID < '$new_id') ) order by ID desc limit 1;";
							}
						$add_permalinks  .= "</select>";
					}
						else
							$add_permalinks = "";

				$source_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_SETTS;
				if( (($crossLinkerObj::VERSION=='1.4.3') || ($crossLinkerObj::VERSION=='1.4.4')) && ($wpdb->get_var("SELECT value FROM $source_table WHERE setting = 'quotes_news_143' LIMIT 1;",0)=='') )
					{
						echo "<b><em>Version 1.4.3 supports hyperlinking of such words too: <span style=\"color: Green;\">Eric&#8217;s</span> or <span style=\"color: Green;\">Eric's</span> or <span style=\"color: Green;\">Employees&#8217;</span>. Other types of quotes aren't supported yet, however if you want me to add them to the core, email me. This message will not be shown any more.</em></b>
";
						$wpdb->query("insert into $source_table values ('NULL' , 'quotes_news_143' , '1');");
					}

				$current_cookie    = $_COOKIE['hyperlink_console'];
				if($current_cookie=='1')
					$current_display = "style=\"display:block; position: relative; left: 0px; top: 0px; border: 0px; padding: 0px; margin: 0px;\"";
						else
							$current_display = "class=\"adiv_style_clnkr\"";
				if($wpdb->get_var("SELECT id FROM $t4 WHERE setting = 'cut_empty_spaces' LIMIT 1;",0)=='')
					{
						//new - automatically insert core usage
						$wpdb->query("INSERT INTO $table_name values ( 'NULL' , 'cut_empty_spaces' , '1' )");
					}

				if($_POST['new_lang']!='')
					{
						echo $paid_version_text;
					}

				$table_name =  $wpdb->prefix . $crossLinkerObj::CROSSLINK_LNGS;

				if($_POST['deactivate_lang']!='')
					{
						echo $paid_version_text;
					}
				if($_POST['activate_lang']!='')
					{
						echo $paid_version_text;
					}

				if($_REQUEST['del_lang']!='')
					{
						echo $paid_version_text;
					}

				echo "<div><h3><a href=\"#h_1\" onclick=\"crosslinkerObj.make_cookie('hyperlink_console');crosslinkerObj.ReverseContentDisplay('hyperlink_console');\" name=\"h_1\" title=\"Using this box you can setup automated linking\">Open/Close The Console For Hyperlinking</a></h3>
<div id=\"hyperlink_console\" ".$current_display."><div class=\"bdiv_style_clnkr\">
<b>Here you can set-up automatic hyperlinking of words and URLs.</b><br />
<form action=\"".$fix_uri."\" method=\"post\" name=\"linkerform\">
		<label for=\"linker_word\">Specify the word/phrase below, please (more words/phrases <b>MUST</b> be divided by the following symbol: | . For example: <i>car|suspension|alfa romeo</i> - each of these phrases will point at the specified URL)</label><br /><input type=\"text\" name=\"linker_word\" id=\"linker_word\" value=\"".$linkto_word."\" title=\"Word/Phrase goes here\" placeholder=\"word\" required /><br />
		<label for=\"linkeruri\">Specify the destination URL, please (starting with the <em>http://</em> or <em>https://</em> prefix)</label><br /><input type=\"text\" name=\"linker_uri\" value=\"".$linkto_uri."\" id=\"linkeruri\" title=\"Place for URL or post ID\" placeholder=\"http://\" required /> ".$add_permalinks."<br />
		<label for=\"linker_attr\">Additionally, you can specify attributes for the link below</label> (say <em><a href=\"#\" onclick=\"document.linkerform.linker_attr.value += ' target=\'_blank\' ';\" title=\"Assign this attribute to your new link\">target='_blank'</a></em> or <em><a href=\"#\" onclick=\"document.linkerform.linker_attr.value += ' rel=\'nofollow\' ';\" title=\"Assign nofollow attribute to your new link\">rel='nofollow'</a></em> or whatever - just do <b>NOT</b> use double quote ( \" ); Instead, use single quote ( ' ))<br />
<input type=\"text\" name=\"linker_attr\" id=\"linker_attr\" value=\"".$linkto_attr."\" title=\"Box for attribute - Not required\" /><br />
		<label for=\"linker_lang\">Specify language below (this isn't necessary if your blog is available in one language only, or if you want all words to link at URL's without being language-dependent</label><br />
<select name=\"linker_lang\" id=\"linker_lang\" title=\"Choose language\">
";

				$table_name_langs = $wpdb->prefix . $crossLinkerObj::CROSSLINK_LNGS;
				$sql              = "SELECT lang_def FROM $table_name_langs where lang_def>'' and visible='1' order by lang_def asc;";
				$first_rec  = 0;
				while($wpdb->get_var($sql)!='')
					{
						$first_rec++;
						$ls_lng         = $wpdb->get_var($sql);
	
						$get_number_lng = "SELECT id from $table_name where lang_def = '$ls_lng' and visible='1';";
						$nr_lng         = $wpdb->get_var($get_number_lng);

						echo "<option value=\"".stripslashes($nr_lng)."\">".ucfirst(stripslashes($ls_lng))."</option>
";

						$sql            = "SELECT lang_def FROM $table_name_langs where lang_def>'".$ls_lng."' and visible='1' order by lang_def asc;";
					}
				if($first_rec==0)
					echo "<option value=\"-1\">default (none)</option>
";

				echo "</select>
<br />
<input type=\"submit\" value=\"Cross-link now!\" title=\"Setup automated linking now!\" />
</form></div></div></div>
";

				if($_POST['create_backup'] == 1)
					{
						$keep = 0;
						$table_name = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_BKP;
						$time       = time();
						$wpdb->query("insert into $table_name values ( 'NULL' , '".$time."' , '".$keep."' );");
						$last       = $wpdb->get_var("SELECT id FROM $table_name WHERE timestamp = '".$time."' limit 1;");

						$target_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_MAIN . "_" . $last;
						$source_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN;
						$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
						$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");

						$target_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_CHAR . "_" . $last;
						$source_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_CHARS;
						$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
						$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");

						$target_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_TAGS . "_" . $last;
						$source_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_TAGS;
						$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
						$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");

						$target_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_SETT . "_" . $last;
						$source_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_SETTS;
						$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
						$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");

						$target_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_ATTR . "_" . $last;
						$source_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB;
						$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
						$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");

						$target_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_LNGS . "_" . $last;
						$source_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_LNGS;
						$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
						$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");

						$target_table = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_WDLN . "_" . $last;
						$source_table = $wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG;
						$wpdb->query("CREATE TABLE $target_table LIKE ".$source_table);
						$wpdb->query("INSERT $target_table SELECT * FROM $source_table;");

						if($create_force_backup!=1) echo "<script type=\"text/javascript\">
<!--
		alert (\"The backup has been created successfully!\");
	-->
</script>
";
					}

				$table_name =  $wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN;

				if($_POST['deactivate']!='')
					{
						$cid = intval($_REQUEST['deactivate']);
						$wpdb->query("UPDATE $table_name SET visible = '0' WHERE id = '".$cid."';");
						echo "<b>The word/phrase has been deactivated!</b><br />
";
					}
				if($_POST['activate']!='')
					{
						$cid  = intval($_REQUEST['activate']);
						$word = $crossLinkerObj->check_chars($wpdb->get_var("SELECT link_word FROM $table_name WHERE id = '".$cid."';"));

						//get language for activation
						$current_lng = $wpdb->get_var("SELECT lang_id FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG." WHERE word_id = '".$cid."';");
						//end get language for activation

						$check_sql       = "SELECT id FROM $table_name WHERE ( (id<> '".$cid."') AND (visible = '1') AND (link_word = '".$word."') ) ORDER BY id ASC;";
						$languages_match = 0;
						while($wpdb->get_var($check_sql)!='')
							{
								$last_check_id = $wpdb->get_var($check_sql);
								$compare_sql   = "SELECT lang_id FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG." WHERE  word_id = '".$last_check_id."';";
								$compare_lang  = $wpdb->get_var($compare_sql);
								if( ($compare_lang == (-1)) || ($current_lng == $compare_lang) || ($current_lng == (-1)) )
									$languages_match = 1;
								$check_sql = "SELECT id FROM $table_name WHERE ( (id<> '".$cid."') AND (visible = '1') AND (link_word = '".$word."') AND (id > '".$last_check_id."') ) ORDER BY id ASC;";
							}
						if($languages_match==1)
							echo "<b>Cannot activate the word because the same word is pointing at some URL already. Deactivate that one firstly.</b><br />
";
								else
									{
										$wpdb->query("UPDATE $table_name SET visible = '1' WHERE id = '".$cid."';");
										echo "<b>The word/phrase has been activated!</b><br />
";
									}
					}

				if(($_POST['empty']=='1')&&($_POST['really_empty']=='on'))
					{
						$wpdb->query("TRUNCATE TABLE $table_name;");
						$table_name_addon = $wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB;
						$wpdb->query("TRUNCATE TABLE $table_name_addon;");
						$table_name_langsw = $wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG;
						$wpdb->query("TRUNCATE TABLE $table_name_langsw;");
					}

				if($_POST['linker_word']!='')
					{
						$linkto_word = $crossLinkerObj->check_chars($_POST['linker_word']);
						$linkto_uri  = $crossLinkerObj->check_chars($_POST['linker_uri']);
						$linkto_attr = $crossLinkerObj->check_chars($_POST['linker_attr']);
						$linkto_lang = intval($crossLinkerObj->check_chars($_POST['linker_lang']));

						if(@strpos($linkto_word,"|")!==false)
							{
								$linkto_array= @explode("|",$linkto_word);
								$linkto_count= @count($linkto_array);
							}
								else
									{
										$linkto_array[0] = $linkto_word;

										$linkto_count    = 1;
									}

						for($z=1;$z<=$linkto_count;$z++)
							{
								$linkto_word = $linkto_array[($z-1)];
								if($linkto_uri=='')
									echo "<script type=\"text/javascript\">
<!--
		alert('The link is missing!');
	-->
</script>
";

								if(($linkto_word!='')&&($linkto_uri!=''))
									{
										//check if word is available in specified language
										$search_qry        = "SELECT id FROM $table_name WHERE (link_word = '$linkto_word') AND (visible = '1') ORDER BY id ASC LIMIT 1;";
										$lang_verification = "OK";
										while($wpdb->get_var($search_qry)!='')
											{
												$lng_found         = $wpdb->get_var($search_qry);
												if($lng_found!='')
													{
														$lng_found_wds = "";
														$search_qry1   = "SELECT lang_id FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG." WHERE (word_id = '$lng_found');";
														$lng_found_wds = $wpdb->get_var($search_qry1);
														if($lng_found_wds==$linkto_lang)
															$lang_verification = "NOT OK";
													}
												$search_qry = "SELECT id FROM $table_name WHERE (link_word = '$linkto_word') AND (id > '".$lng_found."') AND (visible = '1') ORDER BY id ASC LIMIT 1;";
											}
										//end check if word is available in specified language
										$found       = "";

										$found       = $wpdb->query("SELECT id FROM $table_name WHERE ( (link_word = '$linkto_word') AND (visible = '1') );");

										if( ($found != '') && ($lang_verification == "NOT OK") )
											{
												echo "<bspan style=\"color: Red;\">".$linkto_word."</span>: Deactivate this word/phrase firstly, please.</b><br />
";
											}
												else
													{
														$wpdb->query("INSERT INTO $table_name  VALUES ( 'NULL' , '".$linkto_word."' , '".$linkto_uri."' ,'1' );");
														echo "<b><span style=\"color: Blue;\">".$linkto_word."</span>: The new word/phrase has been hyperlinked successfully.</b><br />
";
														$found = $wpdb->get_var("SELECT id FROM $table_name WHERE ( (link_word = '$linkto_word') AND (visible = '1') ) ORDER BY id DESC LIMIT 1;");

														$connect_word_lang = "INSERT INTO ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG ." VALUES ( 'NULL' , '".$found."' , '".$linkto_lang."' );";
														$wpdb->query($connect_word_lang);

														if($linkto_attr!='')
															{
																$table_name_attrs = $wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB;
																$wpdb->query("INSERT INTO ".$table_name_attrs."  VALUES ( '".$found."' , '".$linkto_attr."' );");
															}
													}
									}
							}
					}

				if($_REQUEST['del_word']!='')
					{
						$del_me           = intval($_REQUEST['del_word']);
						$wpdb->query("DELETE FROM $table_name WHERE id = '".$del_me."';");
						$table_name_attrs = $wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB;
						$wpdb->query("DELETE FROM $table_name_attrs WHERE id = '".$del_me."';");
						$table_name_attrs = $wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG;
						$wpdb->query("DELETE FROM $table_name_attrs WHERE word_id = '".$del_me."';");
					}

				if($_POST['import_text_links']!='')
					{
						//load language data
						$table_lng_data  = $wpdb->prefix . $crossLinkerObj::CROSSLINK_LNGS;
						$table_lng_crdta = $wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG;
						$lng_sql         = "select id, lang_def from ".$table_lng_data." order by id asc;";
						while($wpdb->get_var($lng_sql,0)!='')
							{
								$arr1    = $wpdb->get_var($lng_sql,1);
								$arr2    = $wpdb->get_var($lng_sql,0);
								$exp_lang[$wpdb->get_var($lng_sql,1)] = $wpdb->get_var($lng_sql,0);
								$lng_sql = "select id, lang_def from ".$table_lng_data." where id > '".$wpdb->get_var($lng_sql,0)."' order by id asc;";
							}
						$exp_lang["cl_lang=DEF"] = -1;
						//end load language data

						$table            = $wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN;
						$table_name_attrs = $wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB;
						echo "<br /><b><em>Import - Results</em></b><br />
";
						echo "<textarea cols=\"50\" rows=\"15\" readonly=\"readonly\">
";
						$all_import = explode("\n",$_POST['import_text_links']);
						for($i=0;$i<(((count($all_import)+1)/5)-1);$i++)
							{
								$import_word = substr(stripslashes($all_import[($i*5)]),0,strlen(stripslashes($all_import[($i*5)]))-1);
								$import_url  = substr(stripslashes($all_import[(($i*5)+1)]),0,strlen(stripslashes($all_import[(($i*5)+1)]))-1);
								$import_attr = addslashes(substr(stripslashes($all_import[(($i*5)+2)]),0,strlen(stripslashes($all_import[(($i*5)+2)]))-1));
								$import_act  = substr(stripslashes($all_import[(($i*5)+3)]),0,strlen(stripslashes($all_import[(($i*5)+3)]))-1);
								$import_lang = addslashes(substr(stripslashes($all_import[(($i*5)+4)]),0,strlen(stripslashes($all_import[(($i*5)+4)]))));
								$import_lang = str_replace(chr(13),"",str_replace(chr(10),"",$import_lang));
								//ak neexistuje lang v exporte, vloz jazyk
								if($exp_lang[$import_lang]=='')
									{
										$table_check_lang    = $wpdb->prefix . $crossLinkerObj::CROSSLINK_LNGS;
										$insert_lang         = "INSERT INTO ".$table_check_lang." VALUES ( 'NULL' , '".strtolower($import_lang)."' , '0' );";
										$wpdb->query($insert_lang);
										echo "Language: ".ucfirst($import_lang)." has been added as inactive!\n";

										$select_lang         = "SELECT id FROM ".$table_check_lang." where lang_def = '".strtolower($import_lang)."'";
										$use_lang            = strtolower($import_lang);
										$exp_lang[$use_lang] = $wpdb->get_var($select_lang,0);
									}
								//end ak neexistuje lang v exporte, vloz jazyk
								if(($import_act!='0')&&($import_act!='1'))
									$import_act  = stripslashes($all_import[(($i*5)+3)]);

								$found_word  = 0;
								$found_prob  = $wpdb->get_var("SELECT id FROM $table WHERE ( (link_word = '$import_word') AND (link_url = '$import_url') ) LIMIT 1;",0);

								//get lang
								$real_prob = 1;
								if($found_prob>0)
									{
										$prob_sql = "SELECT id FROM $table WHERE ( (link_word = '$import_word') AND (link_url = '$import_url') ) ORDER BY id ASC LIMIT 1;";
										while($wpdb->get_var($prob_sql,0) != '')
											{
												$findn_id = $wpdb->get_var($prob_sql,0);

												$found_lang  = $wpdb->get_var("SELECT lang_id FROM $table_lng_crdta WHERE word_id = '$findn_id' LIMIT 1;",0);

												if($found_lang == $import_lang)
													$real_prob = 0;

												$prob_sql = "SELECT id FROM $table WHERE ( (link_word = '$import_word') AND (link_url = '$import_url') AND (id > '".$findn_id."') ) ORDER BY id ASC LIMIT 1;";
											}
									}
								//end get lang

								if( ($found_prob>0) && ($real_prob==1) )
									$give_output = "Word: ".$import_word." not imported\n";
										else
											{
												$found_word  = $wpdb->get_var("SELECT id FROM $table WHERE ( (link_word = '$import_word') AND (visible = '1') ) LIMIT 1;",0);

												//get lang
												if($found_word>0)
													{
														$my_search_lng = "SELECT id FROM ".$table_lng_crdta." WHERE word_id = '".$found_word."' AND lang_id = '".$import_lang."' LIMIT 1;";
														$my_search_qry = $wpdb->get_var($my_search_lng);
													}
												//end get lang

												if( ($found_word==0) || ($my_search_qry=='') )
													{
														$sql       = "INSERT INTO ".$table." values ( 'NULL' , '$import_word' , '$import_url' , '$import_act' );";
														$wpdb->query($sql);
														$found_id  = $wpdb->get_var("SELECT id FROM $table WHERE ( (link_word = '$import_word') AND (visible = '$import_act') AND (link_url = '$import_url') ) order by ID desc LIMIT 1;",0);
														$sql       = "INSERT INTO ".$table_name_attrs." values ( '$found_id' , '$import_attr' );";
														$wpdb->query($sql);

														$tmp_lng   = $exp_lang[$import_lang];
														$sql       = "INSERT INTO ".$table_lng_crdta." values ( 'NULL' , '$found_id' , '$tmp_lng' );";
														$wpdb->query($sql);

														if($import_act==1)
															$active = "active";
																else
																	$active = "inactive";
														$give_output = "Word: ".$import_word." set to ".$active."\n";
													}
														else
															{
																$sql = "INSERT INTO ".$table." values ( 'NULL' , '$import_word' , '$import_url' , '0' );";
																$wpdb->query($sql);
																$found_id  = $wpdb->get_var("SELECT id FROM $table WHERE ( (link_word = '$import_word') AND (visible = '0') AND (link_url = '$import_url') ) order by ID desc LIMIT 1;",0);
																$sql       = "INSERT INTO ".$table_name_attrs." values ( '$found_id' , '$import_attr' );";
																$wpdb->query($sql);

																$tmp_lng   = $exp_lang[$import_lang];
																$sql       = "INSERT INTO ".$table_lng_crdta." values ( 'NULL' , '$found_id' , '$tmp_lng' );";
																$wpdb->query($sql);

																$give_output = "Word: ".$import_word." set to inactive\n";
															}
											}
								echo $give_output;
							}
						echo "</textarea>
";
					}

				$table_name_attributes = $wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB;

				if($_POST['blogroll_import']=='1')
					{
						$table_name =  $wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN;
						$m          = $_POST['blogroll_import_val'];
						for($i=1;$i<=$m;$i++)
							{
								if($_POST['ch'][$i]=='on')
									{
										$url          = $crossLinkerObj->check_chars($_POST['blogroll_link_url'][$i]);
										$word         = $crossLinkerObj->check_chars($_POST['blogroll_link_title'][$i]);
										$attribute    = $crossLinkerObj->check_chars($_POST['blogroll_link_attr'][$i]);
										$next_visible = 1;
										$exists       = 0;

										$exx  = $wpdb->get_var("SELECT id FROM $table_name WHERE ( (link_word = '".$word."') AND (visible = '1') );");
										if($exx!='')
											$next_visible = 0;
										$exx  = $wpdb->get_var("SELECT id FROM $table_name WHERE ( (link_word = '".$word."') AND (visible = '0') );");
										if($exx!='')
											$next_visible = 1;
										$exx  = $wpdb->get_var("SELECT id FROM $table_name WHERE ( (link_word = '".$word."') AND (link_url = '".$url."') );");
										if($exx!='')
											$exists = 1;

										if($exists==1)
											echo "<span style=\"color: red\">Word <b>".$crossLinkerObj->uncheck_word($word)."</b> and URL <b>".$crossLinkerObj->uncheck_word($url)."</b> are already connected!</span><br />
";
												else
													{
														if($next_visible==0)
															{
																echo "<span style=\"color: orange\">Word <b>".$crossLinkerObj->uncheck_word($word)."</b> and URL <b>".$crossLinkerObj->uncheck_word($url)."</b> were set to inactive because this word is already active for another URL!</span><br />
";
																$next_visible_1 = 1;
															}
																else
																	{
																		echo "<span style=\"color: blue\">Word <b>".$crossLinkerObj->uncheck_word($word)."</b> and URL <b>".$crossLinkerObj->uncheck_word($url)."</b> were set to active!</span><br />
";
																		$next_visible_1 = 0;
																	}
														$wpdb->query("INSERT INTO $table_name VALUES ( 'NULL' , '".$word."' , '".$url."' ,'".$next_visible."' );");
														if($attribute!='')
															{
																$find_last = $wpdb->get_var("SELECT id FROM $table_name WHERE ( (link_word = '".$word."') AND (link_url = '".$url."') AND (visible = '".$next_visible."') ) LIMIT 1;");
																$wpdb->query("INSERT INTO $table_name_attributes VALUES ( '".$find_last."' , '".$attribute."' );");
															}
													}
									}
							}
					}

				$table_name       = $wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN;
				$table_name_attrs = $wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB;

				if($_POST['modify_id']!='')
					{
						$modify_id   = htmlspecialchars(addslashes(stripslashes($_POST['modify_id'])));
						$modify_word = htmlspecialchars(addslashes(stripslashes($_POST['modify_phrase'])));
						$modify_uri  = htmlspecialchars(addslashes(stripslashes($_POST['modify_uri'])));
						$modify_attr = htmlspecialchars(addslashes(stripslashes($_POST['modify_attr'])));
						$modify_lang = intval($_POST['modify_lang_word']);
						$err_message = "";
						if($modify_word=='')
							$err_message = "The word is void. Invalid request!";
						if($modify_uri=='')
							$err_message = "The URL is void. Invalid request!";
						$old_word = $wpdb->get_var("SELECT link_word FROM $table_name WHERE id = '$modify_id' LIMIT 1",0);
						//verify languages
						$dnt_pass_ln = 0;
						$verify_lang_words_sql = "SELECT id from ".$table_name." WHERE ( link_word = '". $modify_word."' ) AND ( visible = '1' ) AND ( id <> '".$modify_id."' ) ORDER BY id ASC";
						while($wpdb->get_var($verify_lang_words_sql)!='')
							{
								$next_lang_id = $wpdb->get_var($verify_lang_words_sql);

								$get_lang_id_c= $wpdb->get_var("SELECT lang_id FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG." WHERE word_id = '".$next_lang_id."';");

								if($get_lang_id_c==$modify_lang)
									{
										$dnt_pass_ln = 1;
										$err_message = "The word is already active for another language. Invalid request!";
									}

								$verify_lang_words_sql = "SELECT id from ".$table_name." WHERE ( link_word = '". $modify_word."' ) AND ( visible = '1' ) AND ( id <> '".$modify_id."' ) AND ( id > '".$next_lang_id."' ) ORDER BY id ASC";
							}
						//end verify languages
						if($old_word!=$modify_word)
							{
								$existing_problem = $wpdb->get_var("SELECT id FROM $table_name WHERE ( (id <> '".$modify_id."' ) AND (visible = '1') AND (link_word = '".$modify_word."') ) LIMIT 1",0);
								if( ($existing_problem>0) && ($dnt_pass_ln==1) )
									{
										$err_message = "The word is already active, deactivate it firstly. Invalid request!";
									}
							}

						if($err_message!='')
							{
								echo "<script type=\"text/javascript\">
<!--
		alert('".$err_message."!');
	-->
</script>
";
							}
								else
									{
										$wpdb->query("UPDATE $table_name SET link_word = '$modify_word', link_url = '$modify_uri' WHERE id = '$modify_id' LIMIT 1;");
										$table_name_lng_wd_c = $wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG;
										$check_sql = "SELECT word_id FROM $table_name_lng_wd_c WHERE word_id = '$modify_id' LIMIT 1";
										if($wpdb->get_var($check_sql,0)!='')
											$wpdb->query("UPDATE $table_name_lng_wd_c SET lang_id = '$modify_lang' WHERE word_id = '$modify_id' LIMIT 1;");
												else
													$wpdb->query("INSERT INTO $table_name_lng_wd_c values ( 'NULL' , '$modify_id','$modify_lang');");
										if($wpdb->get_var("SELECT id FROM $table_name_attrs WHERE id = '$modify_id' LIMIT 1",0)!='')
											$wpdb->query("UPDATE $table_name_attrs SET attrib = '$modify_attr' WHERE id = '$modify_id' LIMIT 1;");
												else
													$wpdb->query("INSERT INTO $table_name_attrs values ( '$modify_id' , '$modify_attr' );");
									}

					}

				//modification of language
				if($_POST['modifylng_id']!='')
					{
						$table_name_lng = $wpdb->prefix . $crossLinkerObj::CROSSLINK_LNGS;
						$modifylng_id   = intval($_POST['modifylng_id']);
						$modify_lang    = strtolower(htmlspecialchars(addslashes(stripslashes($_POST['language_name_mdf']))));
						$err_message    = "";

						if($modify_lang=='')
							$err_message = "The language is void. Invalid request!";
								$old_lang = $wpdb->get_var("SELECT id FROM $table_name_lng WHERE id <> '$modifylng_id' and lang_def = '$modify_lang' LIMIT 1;");
						if($old_lang>0)
							{
								$err_message = "The language is already present in the database. Invalid request!";
							}

						if($err_message!='')
							{
								echo "<script type=\"text/javascript\">
<!--
		alert('The language is void/exists. Invalid request!');
	-->
</script>
";
							}
								else
									{
										$wpdb->query("UPDATE $table_name_lng SET lang_def = '$modify_lang' WHERE id = '$modifylng_id' LIMIT 1;");
									}

					}
				//end update options

				//assign empty attribute to words without attribute / without langs
				$verify_attrbs = "SELECT IFNULL(".$wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB.".attrib,'NULL_ID'), IFNULL(".$wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB.".id,'NULL_ATTRB'), ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN.".id FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN." LEFT JOIN ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB." ON ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB.".id = ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN.".id ORDER BY ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN.".id ASC;";

				$tmp_res   = $wpdb->get_results($verify_attrbs);
				$cntr      = 0;
				foreach ( $tmp_res as $obj )
					foreach ( $obj as $result => $value) 
						{
							$cntr++;
	//									echo "key=".$result." value=".$value."<br />";
							$mypole[$cntr] = $value;
							if($cntr == 3)
								{
									if( ($mypole[1]=='NULL_ID') && ($mypole[2]=='NULL_ATTRB') && ($mypole[3]!='') )
										$wpdb->query("INSERT INTO ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB." VALUES ('".$mypole[3]."' , '') ON DUPLICATE KEY UPDATE attrib = '';");
									$cntr = 0;
								}
						}

				$verify_langs = "SELECT ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG.".lang_id, ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN.".id FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN." LEFT JOIN ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG." ON ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG.".word_id = ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN.".id ORDER BY ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN.".id ASC limit 1";

				while($wpdb->get_var($verify_langs,1)!='')
					{
						$this_id       = $wpdb->get_var($verify_langs,1);
						$this_attrb    = $wpdb->get_var($verify_langs,0);
						if($this_attrb=='')
							$wpdb->query("INSERT INTO ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG." VALUES ( 'NULL' , '".$this_id."' , '-1');");
						$verify_langs = "SELECT ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG.".lang_id, ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN.".id FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN." LEFT JOIN ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG." ON ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG.".word_id = ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN.".id  WHERE ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN.".id > ".$this_id." ORDER BY ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN.".id ASC limit 1";
					}
				//end assign empty attribute to words without attribute / without langs
				//echo went to scripts.js!!!
				/*PRO - START*/
				$current_cookie    = $_COOKIE['multilingual'];
				if($current_cookie=='1')
					$current_display = "style=\"display:block; position: relative; left: 0px; top: 0px; border: 0px; padding: 0px; margin: 0px;\"";
						else
							$current_display = "class=\"adiv_style_clnkr\"";

				echo "<div><h3><a href=\"#h_10\" onclick=\"crosslinkerObj.make_cookie('multilingual');crosslinkerObj.ReverseContentDisplay('multilingual');\" name=\"h_10\" title=\"Use this feature if your blog contains pages in at least two languages\" >Open/Close The Console For Multilingual Support</a></h3>
";

				echo "<div id=\"multilingual\" ".$current_display.">
<div class=\"bdiv_style_clnkr\">
";

				echo "Feel free to declare a set of languages for your blog below. Since there may be several multilingual plugins for WordPress, Cross-Linker works with this option in a simple way - \"current language\" has to be defined in each post by the author, otherwise such a post is considered to be written in \"all languages\". If your blog is in one language only, then it's not necessary to use the settings below (multilingual ones).";

				echo "<form action=\"".$fix_uri."\" method=\"post\" name=\"lngsform\">
";

				echo "<br /><b>Add new language below:</b><br />
<input type=\"text\" name=\"new_lang\" required title=\"Name of language, i.e. German or Spanish, etc.\" /> (name of language; type <i>German</i> for example)<br />
<input type=\"submit\" value=\"Add new language!\" title=\"Add new language into Cross-Linker now!\" />";

				echo "</form>";

				echo "<br /><b>Currently defined languages:</b>
";

				$table_name = $wpdb->prefix . $crossLinkerObj::CROSSLINK_LNGS;
				$sql        = "SELECT lang_def FROM $table_name where lang_def>'' order by lang_def asc;";
				$first_rec  = 0;
				while($wpdb->get_var($sql)!='')
					{
						$first_rec++;
						if($first_rec==1)
							{
								echo "<table class=\"bulktablecss\">
";

								echo "<tr><td class=\"td_style_clnkr\"><b><small>Modification</small></b></td><td class=\"td_style_clnkr1\"><b><small>Language</small></b></td><td class=\"td_style_clnkr1\"><b><small>How to declare language within a post</small></b></td><td class=\"td_style_clnkr1\"><b><small>Deactivation</small></b></td><td class=\"td_style_clnkr1\"><b><small>Delete</small></b></td></tr>";
							}

						$ls_lng         = $wpdb->get_var($sql);

						$get_number_lng = "SELECT id from $table_name where lang_def = '$ls_lng';";
						$nr_lng         = $wpdb->get_var($get_number_lng);

						echo "<tr><td class=\"td_style_clnkr\"><small>[<a onclick=\"crosslinkerObj.ReverseContentDisplay('modifyarealng".$nr_lng."');\">modify</a>]</small></td><td class=\"td_style_clnkr1\"><b>".strtoupper($ls_lng)."</b></td><td class=\"td_style_clnkr1\"><em>&lt;!--crosslink_lang=".$ls_lng."--&gt;</em></td>
";

						$qry_vis_lng  = "SELECT visible FROM $table_name WHERE lang_def = '".$ls_lng."' limit 1;";
						$visible_lang = $wpdb->get_var($qry_vis_lng);

						if($visible_lang==1)
							$form_to_act_lng = "deactivate";
								else
									$form_to_act_lng = "activate";

						echo "<td class=\"td_style_clnkr\"><form action=\"".$fix_uri."\" method=\"post\"><input type=\"hidden\" name=\"".$form_to_act_lng."_lang\" value=\"".$crossLinkerObj->uncheck_word(htmlspecialchars($nr_lng))."\" /> <input type=\"submit\" value=\"".ucfirst($form_to_act_lng)."\" /></form></td>";

						$current_uri = $_SERVER['REQUEST_URI'];
						$ddd         = "del_lang=";
						if(@strpos($current_uri,$ddd)!==false)
							{
								$del_position = @strpos($current_uri,$ddd);
								$current_uri  = @substr($current_uri,0,$del_position-1);
							}

						if($wpdb->get_var("SELECT value FROM $t4 WHERE setting = 'delete_option' LIMIT 1;",0)=='1')
							echo "<td class=\"td_style_clnkr1\"><small><a onclick=\"return crosslinkerObj.confirmSubmit('".$crossLinkerObj->uncheck_word(htmlspecialchars($ls_lng))."');\" href=\"".htmlspecialchars($current_uri)."&amp;".$ddd.htmlspecialchars($nr_lng)."\">DELETE</a></small></td>
";
								else
									echo "<td class=\"td_style_clnkr\"><small>N/A</small></td>
";
						echo "</tr><tr><td colspan=\"5\"><div id=\"modifyarealng".$nr_lng."\" class=\"hdiv_style_clnkr\">
<form action=\"".$fix_uri."\" method=\"post\">
<input type=\"text\" name=\"language_name_mdf\" value=\"".$crossLinkerObj->uncheck_word(htmlspecialchars($ls_lng))."\" title=\"Name/identifier of language\" required /> (language)<br />
<input type=\"hidden\" name=\"modifylng_id\" value=\"".htmlspecialchars($nr_lng)."\" title=\"Modify the record now!\" />
<input type=\"submit\" value=\"Modify this record!\" />
</form>
</div></td></tr>
";

						$sql    = "SELECT lang_def FROM $table_name where lang_def>'".$ls_lng."' order by lang_def asc;";
					}

				if($first_rec!=0)
					echo "</table><small><b><span style=\"color: Red;\">Warning! If you delete a language, all Cross-Linker words for that language will be removed too!</span></b><br />
<b><span style=\"color: Blue;\">Important (I)! If you deactivate a language, all Cross-Linker words for that language will be deactivated too! Same applies to activation!</span></b><br />
<b><span style=\"color: Blue;\">Important (II)! Language definition must be placed in source code of a post/page - simply view it as HTML in your WordPress admin panel and place the code there. Language definition won't be visible to visitors as it's just an HTML comment.</span></b></small>";
						else
							echo "<b>No language has been defined yet, all words/phrases will be considered as same language.</b>";

						echo "</div></div>
";
				/*PRO - END*/


				$current_cookie    = $_COOKIE['blogrollmanagement'];
				if($current_cookie=='1')
					$current_display = "style=\"display:block; position: relative; left: 0px; top: 0px; border: 0px; padding: 0px; margin: 0px;\"";
						else
							$current_display = "class=\"adiv_style_clnkr\"";

				echo "<div><h3><a href=\"#h_2\" onclick=\"crosslinkerObj.make_cookie('blogrollmanagement');crosslinkerObj.ReverseContentDisplay('blogrollmanagement');\" name=\"h_2\" title=\"This will import blogroll links into Cross-linker\">Open/Close The Console For Importing Blogroll Links</a></h3>
";
				echo "<div id=\"blogrollmanagement\" ".$current_display.">
<div class=\"bdiv_style_clnkr\">
";

				echo "<form action=\"".$fix_uri."\" method=\"post\" name=\"checkers\">";
				$table_name = $wpdb->prefix . "links";
				$i          = 0;
				$max = $wpdb->get_var("SELECT link_id FROM $table_name order by link_id asc limit 1;");
				while($wpdb->get_var("SELECT link_id FROM $table_name WHERE link_id = '".$max."' order by link_id asc limit 1;")!='')
					{
						$i++;
						echo "<input type=\"text\" name=\"blogroll_link_url[".$i."]\" value=\"".$wpdb->get_var("SELECT link_url FROM $table_name WHERE link_id = '".$max."' limit 1;")."\" /> is linked as <input type=\"text\" name=\"blogroll_link_title[".$i."]\" value=\"".strtolower($wpdb->get_var("SELECT link_name FROM $table_name WHERE link_id = '".$max."' limit 1;"))."\" /> with this attribute <input type=\"text\" name=\"blogroll_link_attr[".$i."]\" id=\"attr".$i."\" value=\"\" /> <input type=\"checkbox\" name=\"ch[".$i."]\" checked=\"checked\" id=\"ch".$i."\" /> import?<br />
";
						$max = $wpdb->get_var("SELECT link_id FROM $table_name WHERE link_id > '".$max."' order by link_id asc limit 1;");
					}
				echo "<script type=\"text/javascript\">
<!--
		function checks()
			{
				if(document.checkers.submitchanges.value=='Uncheck All!')
					{
						value = \"Check All!\";
						ass   = false;
					}
						else
							{
								value = \"Uncheck All!\";
								ass   = true;
							}
";
				for($z=1;$z<=$i;$z++)
					echo "   document.checkers.ch".$z.".checked = ass ; ";

				echo "   document.checkers.submitchanges.value = value ;
			}
		function applyallattrs()
			{
";
				for($z=1;$z<=$i;$z++)
					echo "   document.checkers.attr".$z.".value = document.checkers.applyattrtoall.value ; ";

					echo "
			}
	-->
</script>
";

				echo "<input type=\"button\" name=\"submitchanges\" id=\"submitchanges\" value=\"Uncheck All!\" onclick=\"checks();\" /><br />
	Apply this attribute to all imported links: <input type=\"text\" name=\"apply_attr_to_all\" id=\"applyattrtoall\" value=\"\" /> by clicking <input type=\"button\" value=\"HERE\" onclick=\"applyallattrs();\" /> (as described above, use single quotes instead of double quotes)<br />
";

				echo "<input type=\"hidden\" name=\"blogroll_import_val\" value=\"".$i."\" />
<input type=\"hidden\" name=\"blogroll_import\" value=\"1\" />
<input type=\"submit\" value=\"Import!\" />
";
				echo "</form>
";

				echo "</div></div></div>
";

				$current_cookie    = $_COOKIE['current_connections'];
				if($current_cookie=='1')
					$current_display = "style=\"display:block; position: relative; left: 0px; top: 0px; border: 0px; padding: 0px; margin: 0px;\"";
						else
							$current_display = "class=\"adiv_style_clnkr\"";

				echo "<div><h3><a href=\"#h_3\" onclick=\"crosslinkerObj.make_cookie('current_connections');crosslinkerObj.ReverseContentDisplay('current_connections');\" name=\"h_3\" title=\"This tab will display all active and inactive links within Cross-Linker\">Open/Close The Console For Currently Hyperlinked Words and URLs</a></h3>
";
				echo "<div id=\"current_connections\" ".$current_display.">
<div class=\"bdiv_style_clnkr\">
<form action=\"".$fix_uri."\" method=\"post\">
	<label for=\"clnkr_pagings\">Results per page</label>: <input type=\"text\" value=\"\" id=\"clnkr_pagings\" name=\"clnkr_pagings\" placeholder=\"100\" title=\"Put any number here, it will be used for paging\" required />
	<label for=\"clnkr_search_string\"> Include only following string in phrases/URL's</label>: <input type=\"text\" value=\"\"  id=\"clnkr_search_string\" name=\"clnkr_search_string\" placeholder=\"Any text\" title=\"Type any text here to get less results\" />
	Show only: <input type=\"checkbox\" name=\"clnkr_invisible_links\" checked=\"checked\"  value=\"1\" title=\"Show inactive links\"  id=\"clnkr_invisible_links\" /> <label for=\"clnkr_invisible_links\" title=\"Display inactive links\">Inactive links</label> <input type=\"checkbox\" name=\"clnkr_visible_links\" checked=\"checked\"  value=\"2\" title=\"Show active links\"  id=\"clnkr_visible_links\" /> <label for=\"clnkr_visible_links\" title=\"Display active links\">Active
 links</label>
	<input type=\"submit\" value=\"Confirm!\" title=\"Once you click this button, pagination will be used\"  onclick=\"alert('Available in PRO version only!'); return false;\" />
 </form>
".$crossLinkerObj->insert_hr();

				$found = 0;

				$table_name = $wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN;

				$i     = $crossLinkerObj->check_chars($wpdb->get_var("SELECT link_word FROM $table_name ORDER BY link_word ASC LIMIT 1",0));
				$help  = $wpdb->get_var("SELECT id FROM $table_name WHERE link_word = '".$i."' limit 1;",0);
				$sql   = "SELECT * FROM $table_name WHERE link_word = '$i';";
				$z     = 1;

				$show_advanced_att_import = 0;

				echo "<table style=\"border: solid 1px Silver; padding: 2px; margin: 0px;\">
";

				echo "<tr><th class=\"td_style_clnkr\"><b><small>Modification</small></b></th><th class=\"td_style_clnkr1\"><b><small>Phrase/Word</small></b></th><th class=\"td_style_clnkr1\"><b><small>points to</small></b></th><th class=\"td_style_clnkr1\"><b><small>Attribute</small></b></th><th class=\"td_style_clnkr\"><b><small>Language</small></b></th><th class=\"td_style_clnkr\"><b><small>Deactivation</small></b></th><th class=\"td_style_clnkr\"><b><small>Bulk</small></b></th><th class=\"td_style_clnkr1\"><b><small>Delete</small></b></th></tr>
";

				//set of languages
				$get_language = "SELECT lang_def, id FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_LNGS." order by id asc;";
				while($wpdb->get_var($get_language)!='')
					{
						$show_lng[$wpdb->get_var($get_language,1)] = $wpdb->get_var($get_language,0);

						$get_language = "SELECT lang_def, id FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_LNGS." where id > '".$wpdb->get_var($get_language,1)."'order by id asc;";
					}
				//end set of languages
				//get all words ordered by alphabet, asc
				$qry_words = "SELECT id, link_word, link_url, visible FROM ".$table_name." ORDER BY link_word ASC;";
				$tmp_res   = $wpdb->get_results($qry_words);
				$cntr      = 0;
				foreach ( $tmp_res as $result ) 
					{
						$cntr++;
						$show_id[$cntr]   = $result->id;
						$show_word[$cntr] = $result->link_word;
						$show_url[$cntr]  = $result->link_url;
						$show_vsbl[$cntr] = $result->visible;
					}
				//end get all words ordered by alphabet, asc

				//go through all words
				//while($wpdb->get_var($sql,0)!='')
				$delete_activated = $wpdb->get_var("SELECT value FROM $t4 WHERE setting = 'delete_option' LIMIT 1;",0);
				for($mydb_i=1;$mydb_i<=$cntr;$mydb_i++)
					{
						$mid = $mydb_i; //get ID
						$mw  = $show_word[$mid]; //get WORD
						$maI = $show_url[$mid]; //get URI
						$mac = $show_vsbl[$mid]; //get ACTIVATION

						//echo $mw." ".$mid." ".$mac.":::".$sql."<br /> ";
						$found   = 1;
						if($mac=='1')
							$act = "deactivate";
								else
									$act = "activate";

						$current_uri = $_SERVER['REQUEST_URI'];
						$ddd         = "del_word=";
						if(@strpos($current_uri,$ddd)!==false)
							{
								$del_position = @strpos($current_uri,$ddd);
								$current_uri  = @substr($current_uri,0,$del_position-1);
							}

						$this_attribute    = $show_id[$mydb_i];
						$current_attribute = $crossLinkerObj->uncheck_word($wpdb->get_var("SELECT attrib FROM $table_name_attrs WHERE id = '$this_attribute';"));			//ok here

						if($current_attribute=='')
							$show_advanced_att_import = 1;

						$lng_for_current_word = $help;
						$get_language         = "SELECT lang_id FROM ".$wpdb->prefix . $crossLinkerObj::CROSSLINK_WDLNG." where word_id = '".$help."';";
						$get_language_1       = $wpdb->get_var($get_language);

						$lng_for_current_word = ucfirst($show_lng[$wpdb->get_var($get_language)]);
						if($lng_for_current_word=='')
							$lng_for_current_word = "default (none)";

						echo "<tr><td class=\"td_style_clnkr\"><small>[<a onclick=\"crosslinkerObj.ReverseContentDisplay('modifyarea".htmlspecialchars($show_id[$mydb_i])."');\" title=\"Click to modify existing link\">modify</a>]</small></td><td class=\"td_style_clnkr1\"><b>".$crossLinkerObj->uncheck_word(htmlspecialchars($mw))."</b></td><td class=\"td_style_clnkr1\"><a href=\"".$crossLinkerObj->uncheck_word($crossLinkerObj->assign_correct_uri(htmlspecialchars($maI)))."\" target=\"_blank\" title=\"Open destination URL in new window\">".$crossLinkerObj->uncheck_word(htmlspecialchars($crossLinkerObj->assign_correct_uri(htmlspecialchars($maI))))."</a></td><td class=\"td_style_clnkr1\">".$current_attribute."</td><td class=\"td_style_clnkr\">".$lng_for_current_word."</td><td class=\"td_style_clnkr\">
<form action=\"".$fix_uri."\" method=\"post\"><input type=\"hidden\" name=\"".$act."\" value=\"".$crossLinkerObj->uncheck_word(htmlspecialchars($show_id[$mydb_i]))."\" /> <input type=\"submit\" value=\"".ucfirst($act)."\" title=\"Activate or deactivate this link\" /></form></td>
";

						//bulk action
						echo "<td class=\"td_style_clnkr\"><input type=\"checkbox\" name=\"bulkaction[]\" value=\"".$show_id[$mydb_i]."\" id=\"checkbox_".$show_id[$mydb_i]."\" onclick=\"alert('Available in PRO version only!'); return false;\" title=\"Click to perform bulk action\" /></td>
";
						$bulk_id_js[] = $show_id[$mydb_i];
						//end bulk action
						if($delete_activated=='1')
							echo "<td class=\"td_style_clnkr1\"><small><a onclick=\"return crosslinkerObj.confirmSubmit('".$crossLinkerObj->uncheck_word(htmlspecialchars($mw))."');\" title=\"Delete this link\" href=\"".htmlspecialchars($current_uri)."&amp;".$ddd.htmlspecialchars($show_id[$mydb_i])."\">DELETE</a></small></td>
";
								else
									echo "<td class=\"td_style_clnkr\"><small>N/A</small></td>
";

						echo "</tr><tr><td colspan=\"8\"><div id=\"modifyarea".htmlspecialchars($show_id[$mydb_i])."\" class=\"hdiv_style_clnkr\">
<form action=\"".$fix_uri."\" method=\"post\">
<input type=\"text\" name=\"modify_phrase\" value=\"".$crossLinkerObj->uncheck_word(htmlspecialchars($mw))."\" id=\"modify_phrase".$mydb_i."\" title=\"Place for your word or phrase to be linked\" placeholder=\"word or term\" required /> <label for=\"modify_phrase".$mydb_i."\">(phrase)</label><br />
<input type=\"text\" name=\"modify_uri\" value=\"".$crossLinkerObj->uncheck_word(htmlspecialchars($maI))."\" id=\"modify_uri".$mydb_i."\" title=\"URL or post ID that the word will point to\" placeholder=\"http://\" required /> <label for=\"modify_uri".$mydb_i."\">(URL/POST ID)</label><br />
<input type=\"text\" name=\"modify_attr\" id=\"modify_attr".$mydb_i."\" title=\"link attribute such as rel='nofollow' or other\" placeholder=\"attribute\" value=\"".$crossLinkerObj->uncheck_word($wpdb->get_var("SELECT attrib FROM $table_name_attrs WHERE id = '$this_attribute';"))."\" /> (<label for=\"modify_attr".$mydb_i."\">attribute - use single quotes, not double quotes, please</label>)<br />
	".$crossLinkerObj->assign_lang($get_language_1)."
<input type=\"hidden\" name=\"modify_id\" value=\"".htmlspecialchars($show_id[$mydb_i])."\" />
<input type=\"submit\" value=\"Modify this record!\" title=\"Modify now!\" />
</form>
</div></td></tr>
";

						$add_string = " AND (id <> '".$show_id[$mydb_i]."') ";

						$q   = "SELECT link_word FROM $table_name WHERE ( (link_word >= '".$crossLinkerObj->check_chars($i)."') $add_string ) ORDER BY link_word ASC LIMIT 1";
						$i   = $wpdb->get_var($q,0);
						$help= $wpdb->get_var("SELECT id FROM $table_name WHERE ( (link_word LIKE '".$crossLinkerObj->check_chars($i)."') $add_string ) ORDER BY link_word ASC limit 1;",0);
						$sql = "SELECT id, link_word, link_url, visible FROM $table_name WHERE ( (link_word LIKE '".$crossLinkerObj->check_chars($i)."') $add_string ) ORDER BY link_word ASC;";
					}

				echo "<tr><td class=\"td_style_clnkr\" colspan=\"8\"><small><a href=\"#ttt\" name=\"ttt\" onclick=\"alert('Available in PRO version only!'); return false;\"><b>Check all</b></a> | <a href=\"#ttt1\" name=\"ttt1\" onclick=\"alert('Available in PRO version only!'); return false;\"><b>Uncheck all</b></a></small></td></tr>
";

				echo "</table>
".$crossLinkerObj->insert_hr();

				echo "<script type=\"text/javascript\">
<!--
		function all_checkboxes_ticked()
			{
				checkboxes = '";

				$my_count = count($bulk_id_js);
				for($i=0;$i<$my_count;$i++)
					echo " ".$bulk_id_js[$i]." ";

				echo "';
			}
	-->
</script>
";

				if($found==0)
					echo "<br /><b>No currently hyperlinked words have been found found</b><br />
";
						else
							{
								if($show_advanced_att_import==1)
									echo "<label for=\"attrib_assign_to_all\"><span style=\"color: #875735;\"><em><b>Some words have no attributes assigned. You may assign the following attribute to all words/phrases without any active attribute:</b></em></span></label>
<form action=\"".$fix_uri."\" method=\"post\">
<input type=\"text\" name=\"attrib_assign_to_all\" id=\"attrib_assign_to_all\" value=\"\" placeholder=\"i.e. target='_blank'\" required /> (just don't use double quotes; use single quotes) <input type=\"submit\" value=\"Assign!\" title=\"Assign this attribute to all words/terms without attribute!\" />
</form>
";
								echo "<table class=\"bulktablecss\">
";

								echo "<tr><th class=\"td_style_clnkr_bulk\"><b><small>Bulk action name</small></b></th><th class=\"td_style_clnkr1\"><b><small>Button to perform action</small></b></th></tr></table>";

								echo "<form action=\"".$fix_uri."\" method=\"post\"><table class=\"bulktablecss\"><tr>
<td class=\"td_style_clnkr1_bulk\"><input type=\"hidden\" name=\"empty\" value=\"1\" />
<input type=\"checkbox\" name=\"really_empty\" id=\"really_empty\" required /> <label for=\"really_empty\" title=\"Box at left has to be ticked\"><span style=\"color:red;font-weight:bold;\">I want to delete all EXISTING words/phrases</span></label></td>
<td class=\"td_style_clnkr1_bulk_1\">
<input type=\"submit\" value=\"Delete all existing words/phrases!\" style=\"width:30em;\" onclick=\"return crosslinkerObj.pass_vars_to_nextpage('bulkstuff0','really_empty');\" title=\"Delete all existing words now!\" />
</td>
</tr></table></form>

<form action=\"".$fix_uri."\" method=\"post\">
<table class=\"bulktablecss\"><tr>
<td class=\"td_style_clnkr1_bulk\"><input type=\"hidden\" name=\"bulk_delete\" value=\"1\" />
<input type=\"checkbox\" name=\"really_bulk_delete\" id=\"really_bulk_delete\" required /> <label for=\"really_bulk_delete\" title=\"Box at left has to be ticked\"><span style=\"color:red;font-weight:bold;\">I want to delete all SELECTED words/phrases</span></label><br /><span id=\"bulkstuff0\"></span></td>
<td class=\"td_style_clnkr1_bulk_1\"><input type=\"submit\" value=\"Delete all selected words/phrases!\" onclick=\"alert('Available in PRO version only!'); return false;\" style=\"width:30em;\" title=\"Delete all selected words or phrases\" /></td></tr>
</table
	></form>

<form action=\"".$fix_uri."\" method=\"post\">
<table class=\"bulktablecss\"><tr>
<td class=\"td_style_clnkr1_bulk\"><input type=\"hidden\" name=\"bulk_deactivate\" value=\"1\" />
<input type=\"checkbox\" name=\"really_bulk_deactivate\" id=\"really_bulk_deactivate\" required /> <label for=\"really_bulk_deactivate\" title=\"Box at left has to be ticked\"><span style=\"color:green;font-weight:bold;\">I want to deactivate all SELECTED words/phrases</span></label><br /><span id=\"bulkstuff\"></span></td>
<td class=\"td_style_clnkr1_bulk_1\"><input type=\"submit\" value=\"Deactivate all selected words/phrases!\" onclick=\"alert('Available in PRO version only!'); return false;\" style=\"width:30em;\" title=\"Deactivate all selected words or phrases\" /></td></tr>
</table>
</form>

<form action=\"".$fix_uri."\" method=\"post\">
<table class=\"bulktablecss\"><tr>
<td class=\"td_style_clnkr1_bulk\"><input type=\"hidden\" name=\"bulk_activate\" value=\"1\" />
<input type=\"checkbox\" name=\"really_bulk_activate\" id=\"really_bulk_activate\" required /> <label for=\"really_bulk_activate\" title=\"Box at left has to be ticked\"><span style=\"color:blue;font-weight:bold;\">I want to activate all SELECTED words/phrases</span></label><br /><span id=\"bulkstuff1\"></span></td>
<td class=\"td_style_clnkr1_bulk_1\"><input type=\"submit\" value=\"Activate all selected words/phrases!\" onclick=\"alert('Available in PRO version only!'); return false;\" style=\"width:30em;\" title=\"Activate all selected words or phrases\"/></td></tr>
</table>
</form>
";
							}

				echo "</div></div></div>
";
				$current_cookie    = $_COOKIE['word_replacement'];
				if($current_cookie=='1')
					$current_display = "style=\"display:block; position: relative; left: 0px; top: 0px; border: 0px; padding: 0px; margin: 0px;\"";
						else
							$current_display = "class=\"adiv_style_clnkr\"";

				echo "<div><h3><a href=\"#h_3\" onclick=\"crosslinkerObj.make_cookie('word_replacement');crosslinkerObj.ReverseContentDisplay('word_replacement');\" name=\"h_3\" title=\"Cross-Linker can be used to append/prepend and replace words in your posts, pages, comments\">Open/Close The Console For Replacement of Words</a></h3>
";
				echo "<div id=\"word_replacement\" ".$current_display.">
<div class=\"bdiv_style_clnkr\">
";

				echo "<b>This screen offers following functions</b>:

<ul>
<li><a href=\"#clnk_prp\" title=\"Click to be forwarded\"><b>Prepend</b></a> - Automatically inserts given string at the beginning of each post/comment</li>
<li><a href=\"#clnk_app\" title=\"Click to be forwarded\"><b>Append</b></a> - Automatically inserts given string at the end of each post/comment</li>
<li><a href=\"#clnk_rep\" title=\"Click to be forwarded\"><b>String Replacement</b></a> - Automatically replaces given string in each post/comment</li>
</ul>
	".$crossLinkerObj->insert_hr()."
<div><b><i><a name=\"clnk_prp\">Prepend function</a></i></b>
<form action=\"".$fix_uri."\" method=\"post\">
		Add this string at the beginning of each post or comment:<br />
<textarea name=\"prepend_string\" cols=\"50\" rows=\"5\" title=\"Place anything that has to be added to beginning of each post or comment as specfied below\" placeholder=\"Put your string here\" required></textarea><br />
<input type=\"checkbox\" name=\"prepend_posts\" id=\"prepend_posts\" checked=\"checked\" /> <label for=\"prepend_posts\" title=\"Will prepend to all posts\">Apply to posts</label><br />
<input type=\"checkbox\" name=\"prepend_comments\" id=\"prepend_comments\" checked=\"checked\" /> <label for=\"prepend_comments\" title=\"Will prepend to all comments\">Apply to comments</label><br />
<input type=\"submit\" value=\"Prepend!\" title=\"Prepend now!\" />
</form>
</div>

	".$crossLinkerObj->insert_hr()."

<div><b><i><a name=\"clnk_app\">Append function</a></i></b>
<form action=\"".$fix_uri."\" method=\"post\">
		Add this string at the end of each post or comment:<br />
<textarea name=\"append_string\" cols=\"50\" rows=\"5\" placeholder=\"Put your string here\" title=\"Place anything that has to be added to end of each post or comment as specfied below\" required></textarea><br />
<input type=\"checkbox\" name=\"append_posts\" id=\"append_posts\" checked=\"checked\" /> <label for=\"append_posts\" title=\"Will append to all posts\">Apply to posts</label><br />
<input type=\"checkbox\" name=\"append_comments\" id=\"append_comments\" checked=\"checked\" /> <label for=\"append_comments\" title=\"Will append to all comments\">Apply to comments</label><br />
<input type=\"submit\" value=\"Append!\" title=\"Append now!\" />
</form>
</div>

	".$crossLinkerObj->insert_hr()."

<div><b><i><a name=\"clnk_rep\">String Replacement</a></i></b><br />
	This function is CAse-SENSitive!!!
<form action=\"".$fix_uri."\" method=\"post\">
		Current string:<br />
<textarea name=\"string_replace_1\" cols=\"50\" rows=\"5\" placeholder=\"Something to replace\" title=\"What is to be replaced\" required></textarea><br />
		New string:<br />
<textarea name=\"string_replace_2\" cols=\"50\" rows=\"5\" placeholder=\"Something to be replaced with\" title=\"String to replace the above with\" ></textarea><br />
<input type=\"checkbox\" name=\"replace_posts\" id=\"replace_posts\" checked=\"checked\" /> <label for=\"replace_posts\" title=\"Apply replacement to posts\">Apply to posts</label><br />
<input type=\"checkbox\" name=\"replace_comments\" id=\"replace_comments\" checked=\"checked\" /> <label for=\"replace_comments\" title=\"Apply replacement to comments\">Apply to comments</label><br />
<input type=\"submit\" value=\"Replace!\" title=\"Replace now!\" />
</form>
</div>
";
				echo "</div></div></div>
";
				$current_cookie    = $_COOKIE['ignored_html_tags'];
				if($current_cookie=='1')
					$current_display = "style=\"display:block; position: relative; left: 0px; top: 0px; border: 0px; padding: 0px; margin: 0px;\"";
						else
							$current_display = "class=\"adiv_style_clnkr\"";

				echo "<div><h3><a href=\"#h_4\" onclick=\"crosslinkerObj.make_cookie('ignored_html_tags');crosslinkerObj.ReverseContentDisplay('ignored_html_tags');\" name=\"h_4\" title=\"Use this feature to tell Cross-Linker not to hyperlink words within particular HTML tags\">Open/Close The Console For Managing Ignored HTML tags</a></h3>
";
				echo "<div id=\"ignored_html_tags\" ".$current_display.">
			 <div class=\"bdiv_style_clnkr\">
";
				echo "You should also define which HTML tags are ignored for hyperlinking purposes. Example: If you enter <b>&lt;h</b> and <b>&lt;/h</b> below, then all <b>h1-h6</b> will be ignored. Whatever you wrote within these tags, the cross-linker plugin will not hyperlink words/phrases from such tags. If you're not sure about these settings, let them be.
";

				$t2    = $wpdb->prefix . $crossLinkerObj::CROSSLINK_TAGS;

				if($_POST['delete_tag']!='')
					{
						$delete_this = intval($_POST['delete_tag']);
						$wpdb->query("DELETE FROM $t2 WHERE id = '$delete_this';");
					}

				if(($_POST['add_tag_start']!='')&&($_POST['add_tag_end']!=''))
					{
						$p1 = $_POST['add_tag_start'];
						$p2 = $_POST['add_tag_end'];
						$wpdb->query("INSERT INTO $t2 VALUES ( 'NULL' , '$p2' , '$p1' );");
					}

				$i     = $wpdb->get_var("SELECT * FROM $t2 ORDER BY id asc limit 1;",0);
				$sql   = "SELECT * FROM $t2 WHERE id = '$i';";
				while($wpdb->get_var($sql,0)!='')
					{
						echo "<form action=\"".$fix_uri."\" method=\"post\">
";
						echo "<input type='text' name='t".$i."_s' value='".htmlspecialchars($wpdb->get_var($sql,2))."' readonly='readonly' title=\"This record can be removed only\" /> <input type='text' name='t".$i."_e' value='".htmlspecialchars($wpdb->get_var($sql,1))."' readonly='readonly' title=\"This record can be removed only\" /><input type='hidden' name='delete_tag' value='".$i."' /><input type='submit' value='Delete!' title=\"Delete these tags now!\" /></form>
";
						$i   = $wpdb->get_var("SELECT id FROM $t2 WHERE id > '$i' ORDER BY id asc LIMIT 1;",0);
						$sql = "SELECT * FROM $t2 WHERE id = '$i';";
					}
				echo "<input type='text' name='t_def_s' value='&lt;!--nocrosslink_start--&gt;' readonly='readonly' /> <input type='text' name='t_def_e' value='&lt;!--nocrosslink_end--&gt;' readonly='readonly' /> This can't be deleted, it's default! If you want this plugin to ignore any part of the text (if you don't want to automatically hyperlink any text), simply use this code: &lt;!--nocrosslink_start--&gt;<b>your text here</b>&lt;!--nocrosslink_end--&gt;
".$crossLinkerObj->insert_hr();

				echo "<h3>Add new HTML tags</h3>
<form action=\"".$fix_uri."\" method=\"post\">
		<label for=\"add_tag_start\">The tag starts as</label> <input type='text' name='add_tag_start' id=\"add_tag_start\" value='' placeholder=\"&lt;something\" title=\"Beginning of HTML tag to be ignored\" required /> <label for=\"add_tag_end\">and ends as</label> <input type='text' name='add_tag_end' id=\"add_tag_end\" value='' placeholder=\"&lt;/something\" title=\"End of HTML tag to be ignored\" required /><br />
<input type='submit' value='Add this tag!' title=\"Add new tag into the database\" />
</form>
";

				echo "</div></div></div>
";

				$current_cookie    = $_COOKIE['ignored_characters'];
				if($current_cookie=='1')
					$current_display = "style=\"display:block; position: relative; left: 0px; top: 0px; border: 0px; padding: 0px; margin: 0px;\"";
						else
							$current_display = "class=\"adiv_style_clnkr\"";

				echo "<div><h3><a href=\"#h_5\" onclick=\"crosslinkerObj.make_cookie('ignored_characters');crosslinkerObj.ReverseContentDisplay('ignored_characters');\" name=\"h_5\" title=\"Use this box to tell Cross-Linker which characters to consider as separators\">Open/Close The Console For Managing Ignored Characters</a></h3>
";
				echo "<div id=\"ignored_characters\" ".$current_display.">
<div class=\"bdiv_style_clnkr\">
";

				echo "Words/phrases are hyperlinked if only they are separated by spaces by default. However, dots, commas, slashes and similar characters may be considered as dividing characters as well. Here below you can specify which characters are used by the algorithm. Example: If you don't specify <b>.</b> below, then words/phrases which end with dot will <b>NOT</b> be hyperlinked. <b>Each \"special character\" below MUST be divided by an empty space!</b>
";

				$t3 = $wpdb->prefix . $crossLinkerObj::CROSSLINK_CHARS;

				if($_POST['ignored_chars']!='')
					{
						$p2 = $_POST['ignored_chars'];
						$wpdb->query("UPDATE $t3 SET characters = '$p2' WHERE id = '1';");
					}

				echo "<form action=\"".$fix_uri."\" method=\"post\">
<input type='text' value=\"".htmlspecialchars($wpdb->get_var("SELECT characters FROM $t3 WHERE id = 1 LIMIT 1;",0))."\" name='ignored_chars' id=\"ignored_chars\" title=\"Box for extra characters\" size='50' placeholder=\" ; . , ) ( - : &amp; > < ? ! * / +\" required /><br />
<input type='submit' value='Modify!' title=\"Modify settings fro Cross-Linker now!\" />
</form>
";

				echo "</div></div></div>
";

				$t4 = $wpdb->prefix . $crossLinkerObj::CROSSLINK_SETTS;

				if($wpdb->get_var("SELECT value FROM $t4 WHERE setting = 'link_to_thrusites' LIMIT 1;",0)=='1')
					$checked = "checked='checked'";
						else
							$checked = "";
				if($wpdb->get_var("SELECT value FROM $t4 WHERE setting = 'link_first_word' LIMIT 1;",0)=='1')
					$checked_1 = "checked='checked'";
						else
							$checked_1 = "";
				if($wpdb->get_var("SELECT value FROM $t4 WHERE setting = 'link_comments' LIMIT 1;",0)=='1')
					$checked_2 = "checked='checked'";
						else
							$checked_2 = "";
				if($wpdb->get_var("SELECT value FROM $t4 WHERE setting = 'delete_option' LIMIT 1;",0)=='1')
					$checked_3 = "checked='checked'";
						else
							$checked_3 = "";

				//unusual case
				if($wpdb->get_var("SELECT value FROM $t4 WHERE setting = 'limit_links' LIMIT 1;",0)>0)
					{
						$checked_4 = "checked='checked'";
						$find_sel  = $wpdb->get_var("SELECT value FROM $t4 WHERE setting = 'limit_links' LIMIT 1;",0);
					}
						else
							{
								$find_sel  = 0;
								$checked_4 = "";
							}
				//end unusual case
				if($wpdb->get_var("SELECT value FROM $t4 WHERE setting = 'link_to_permalinks' LIMIT 1;",0)=='1')
					$checked_5 = "checked='checked'";
						else
							$checked_5 = "";
				if($wpdb->get_var("SELECT value FROM $t4 WHERE setting = 'link_to_itself' LIMIT 1;",0)=='1')
					$checked_6 = "checked='checked'";
						else
							$checked_6 = "";

				for($i=1;$i<=99;$i++)
					{
						if($i==$find_sel)
							$check_this_1 = " selected=\"selected\" ";
								else
									$check_this_1 = "";
						$show_limits .= "<option value=\"".$i."\" ".$check_this_1.">".$i."</option> ";
					}

				if($find_sel==0)
					$check_this_1 = " selected=\"selected\" ";
						else
							$check_this_1 = "";

				$show_limits = "<select name=\"limitlinking\" onchange=\"crosslinkerObj.checkthebox();\">
<option value=\"0\" ".$check_this_1.">Unlimited</option>".$show_limits."</select>";

				if($wpdb->get_var("SELECT value FROM $t4 WHERE setting = 'cut_empty_spaces' LIMIT 1;",0)==1)
					$core_selection = "<option value=\"1\" selected=\"selected\">1.4.2+</option>
<option value=\"0\">1.4.1</option>";
						else
							$core_selection = "<option value=\"1\">1.4.2+</option>
<option value=\"0\" selected=\"selected\">1.4.1</option>";

				$current_cookie    = $_COOKIE['manage_settings'];
					if($current_cookie=='1')
						$current_display = "style=\"display:block; position: relative; left: 0px; top: 0px; border: 0px; padding: 0px; margin: 0px;\"";
							else
								$current_display = "class=\"adiv_style_clnkr\"";

				echo "<div><h3><a href=\"#h_6\" onclick=\"crosslinkerObj.make_cookie('manage_settings');crosslinkerObj.ReverseContentDisplay('manage_settings');\" name=\"h_6\" title=\"Setup Cross-Linker behaviour\">Open/Close The Console For Managing Settings</a></h3>
";
				echo "<div id=\"manage_settings\" ".$current_display.">
			 <div class=\"bdiv_style_clnkr\">
";

				echo "This section offers modification of settings, feel free to setup your Cross-Linker as per your requirements<br /><br /><form action=\"".$fix_uri."\" method=\"post\" name=\"formsetting\">
<!--OBSOLETE   <input type='checkbox' name='link_to_thrusites' ".$checked." /> Link to <a href=\"http://www.aqua-fish.net/\">Aqua-Fish.Net</a>? (recommended)<br />-->
<select name=\"core_s\" id=\"core_s\" title=\"1.4.2+ is recommended core\">
		".$core_selection."
</select><label for=\"core_s\">Use this core</label>!<br />".$crossLinkerObj->insert_hr()."
<input type=\"text\" name=\"priority_clnk\" value=\"".$crossLinkerObj->priority()."\" size=\"3\" title=\"Priority can be altered, default is 10\" id=\"priority_clnk\" placeholder=\"10\" required /> <label for=\"priority_clnk\">Define priority of Cross-Linker</label>. Modify this value if other plugins have to be executed before Cross-Linker. [10 is default; 1 is high; 100 is low; feel free to play with this value]<br />".$crossLinkerObj->insert_hr()."
<input type='checkbox' name='link_first_word' ".$checked_1." id=\"link_first_word\" /> <label for=\"link_first_word\" title=\"Cross-Linker will hyperlink 1 word only\">Hyperlink 1 word only? (not recommended; valid for 1 post)</label><br />".$crossLinkerObj->insert_hr()."
<input type='checkbox' name='link_comments' ".$checked_2." id=\"link_comments\" /> <label for=\"link_comments\" title=\"Cross-Linker will be run over comments too\">Apply Cross-Linker to comments? (recommended)</label><br />".$crossLinkerObj->insert_hr()."
<input type='checkbox' name='delete_option' ".$checked_3." id=\"delete_option\" /> <label for=\"delete_option\" title=\"Allows deleting records\">Show the <em>DELETE</em> option for words &amp; languages which have been cross-linked &amp; defined?</label><br />".$crossLinkerObj->insert_hr()."
<input type='checkbox' name='limitlinkings' id='limitlinkings' ".$checked_4." onclick='crosslinkerObj.checktheboxadd();'/> Hyperlink only $show_limits <label for=\"\" title=\"Limit Cross-Linker to maximum number of links per page\">link(s) on each page?</label> (may be useful; bear in mind that this feature restricts linking of each phrase, not of all phrases together!)<br />".$crossLinkerObj->insert_hr()."
<input type='checkbox' name='link_to_permalinks' id=\"link_to_permalinks\" ".$checked_5." /> Allow <label for=\"link_to_permalinks\" title=\"This will load your posts and pages from the database\"><b>direct linking to posts</b></label>? This may waste your server's system resources if there are thousands of posts (but only when you're working with this control panel, otherwise everything will work fine - for your visitors)!!!<br />".$crossLinkerObj->insert_hr()."
<input type='checkbox' name='link_to_itself' id=\"link_to_itself\" ".$checked_6." /> <label for=\"link_to_itself\" title=\"Allow placing link to the same page\">Allow linking to the same page</a>? For example, if you've configured the word <b>seo</b> to link to <b>http://www.something.tld/seo</b>, this word isn't hyperlinked on <b>http://www.something.tld/seo</b> by default. By activating this option, our imaginary word <b>seo</b> will be hyperlinked too when people visit <b>http://www.something.tld/seo</b>; Although it will point to itself only.<br />".$crossLinkerObj->insert_hr()."
<input type=\"text\" name=\"cdata_name\" value=\"".$crossLinkerObj->data_filename()."\" readonly=\"readonly\" title=\"This field is read-only\" /> Name of file which keeps Cross-Linker data. This is optional, however this file could speed up Cross-Linker as it loads data from file instead of a database. Current screen always uses database, however each time you perform a change here, all relevant data could be saved in a text file automatically, and when visitors visit regular pages of your site, Cross-Linker will load data from that file which is <b>much faster</b> than access to database-stored data! Name of this file cannot be manually modified due to security reasons, and you should create it in the directory <b>".dirname(__FILE__)."</b> on your server! When you create this file, <b>it can be blank</b> - it doesn't matter at all since it's going to be re-filled by Cross-Linker. Current status of file: ";

				switch ($crossLinkerObj->datafile_exists())
					{
						case 0:
							echo "<span style=\"color: red; font-weight: bold; font-style: italic;\">FILE DOES NOT EXIST!</span>";
							break;
						case 1:
							echo "<span style=\"color: blue; font-weight: bold; font-style: italic;\">FILE EXISTS!</span> but <span style=\"color: red; font-weight: bold; font-style: italic;\">IS NOT WRITABLE!</span>";
							break;
						case 2:
							echo "<span style=\"color: blue; font-weight: bold; font-style: italic;\">FILE EXISTS!</span> and <span style=\"color: blue; font-weight: bold; font-style: italic;\">IS WRITABLE!</span>";
							break;
					}
				echo " <span style=\"color: red;\">Please, note that this file may not work properly with special characters &yacute;, &#345;, &#382; and such. Ordinary English characters are OK though!</span> If you've created the file mentioned above and if you'd like to disable it - then simply remove it and Cross-Linker will use database-access only.";

				echo "<br /><br />
<input type='hidden' name='up_set' value='1' />
<input type='submit' value='Update settings!' title=\"Save settings now!\" />
</form>
";

				echo "<br /><span style=\"color: red\"><b>IMPORTANT!</b></span><br />
	If you're upgrading this plugin, then there is no need to deactivate it! Just upload new files and rewrite old files.
";

				echo "</div></div></div>
";

				$current_cookie    = $_COOKIE['manage_backups'];
				if($current_cookie=='1')
					$current_display = "style=\"display:block; position: relative; left: 0px; top: 0px; border: 0px; padding: 0px; margin: 0px;\"";
						else
							$current_display = "class=\"adiv_style_clnkr\"";

				echo "<div><h3><a href=\"#h_7\" onclick=\"crosslinkerObj.make_cookie('manage_backups');crosslinkerObj.ReverseContentDisplay('manage_backups');\" name=\"h_7\" title=\"With this tab you can create or restore Cross-Linker data\">Open/Close The Console For Managing Backups</a></h3>
";
				echo "<div id=\"manage_backups\" ".$current_display.">
<div class=\"bdiv_style_clnkr\">
";
				echo "<p><b>Backup</b>
";
				echo "<br />Backup is a safe way how to keep your Cross-Linker data always available. Additionally it's possible to configure your Cross-Linker to perform automatic backup if no recent backup has been done for over <i><b>x</b></i> days.</p>
";
				echo "<form action=\"".$fix_uri."\" method=\"post\">
<input type=\"hidden\" name=\"create_backup\" value=\"1\" />
<!--<small><input type=\"checkbox\" name=\"keep_forever\" /><b>Keep forever</b><br /></small>-->
<input type=\"submit\" value=\"Create a manual backup now!\" title=\"Create manual backup!\" />
</form>
".$crossLinkerObj->insert_hr();
				echo "<p><b>Automatic backup configuration of Cross-Linker data</b></p>
";
				echo "<form action=\"".$fix_uri."\" method=\"post\"><label for=\"force_backup_days\">Perform backup if there is no backup done for last</label> <input type=\"text\" id=\"force_backup_days\" name=\"force_backup_days\" value=\"".$crossLinkerObj->max_period_without_backup()."\" size=\"3\" placeholder=\"10\" title=\"i.e. 10 days \" required /> days. <label for=\"remove_old_backups\">Also keep the number of backups to less than</label> <input type=\"text\" name=\"remove_old_backups\" id=\"remove_old_backups\" value=\"".$crossLinkerObj->maximum_backups()."\" size=\"3\" title=\"i.e. 5 backups\" placeholder=\"5\" required> so the database won't be clogged with backups. <input type=\"submit\" value=\"Click here to confirm!\" title=\"Save changes!\"/> (<small><span style=\"color: Blue;\">this rule doesn't apply to <i><b>keep forever</b></i> backups</span></small>)</form>".$crossLinkerObj->insert_hr();

				echo "<p><b>Backup Restore</b>
";
				echo "<br />Backup Restore is a safe way how to restore your Cross-Linker data from the database.</p>
";

				echo "<form action=\"".$fix_uri."\" method=\"post\">
";
				$table_name = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_BKP;
				$max        = $wpdb->get_var("SELECT timestamp FROM $table_name ORDER BY timestamp desc limit 1;");
				$z          = 0;
				while($wpdb->get_var("SELECT id, keep_forever FROM $table_name WHERE timestamp = '".$max."' ORDER BY timestamp desc limit 1;",0)!='')
					{
						$z++;
						if($z==1)
							echo "<select name=\"restore_backup\" title=\"Pick timestamp of backup creation\">
";
						if($wpdb->get_var("SELECT id, keep_forever FROM $table_name WHERE timestamp = '".$max."' ORDER BY timestamp desc limit 1;",1)==1)
							$keep_forever = " {keep forever} ";
								else
									$keep_forever = "";
						echo "<option value=\"".$max."\">".date("F j, Y, g:i a", $max).$keep_forever."</option>
";
						$max = $wpdb->get_var("SELECT timestamp FROM $table_name WHERE timestamp < '".$max."' ORDER BY timestamp desc limit 1;");
					}
				if($z!=0)
					{
						echo "</select>
";
						echo "<br /><input type=\"checkbox\" name=\"agree\" id=\"agree_b1\" required /> <label for=\"agree_b1\"><span style=\"color: red\">I understand that all current settings will be overwritten!</span></label>
";
						echo "<br /><input type=\"submit\" value=\"Restore chosen backup!\" title=\"By clicking this button you restore chosen backup!\" />
";
					}
				echo "</form>".$crossLinkerObj->insert_hr()."
";
				echo "<p><b>Delete Backup</b>
";
				echo "<br />Use this function only if you're sure what you're doing!</p>
";
				echo "<form action=\"".$fix_uri."\" method=\"post\">
";
				$table_name = $wpdb->prefix . $crossLinkerObj::BKP_CROSSLINK_BKP;
				$max        = $wpdb->get_var("SELECT timestamp FROM $table_name where keep_forever = '0' ORDER BY timestamp desc limit 1;");
				$z          = 0;
				while($wpdb->get_var("SELECT id FROM $table_name WHERE (timestamp = '".$max."') AND (keep_forever = '0') ORDER BY timestamp desc limit 1;")!='')
					{
						$z++;
						if($z==1)
							echo "<select name=\"delete_backup\" title=\"Pick timestamp of backup creation\">
";
						echo "<option value=\"".$max."\">".date("F j, Y, g:i a", $max)."</option>
";
						$max = $wpdb->get_var("SELECT timestamp FROM $table_name WHERE (timestamp < '".$max."') AND (keep_forever = '0') ORDER BY timestamp desc limit 1;");
					}
				if($z!=0)
					{
						echo "</select>
";
						echo "<br /><input type=\"checkbox\" name=\"agree\" id=\"agree_b2\" required /> <label for=\"agree_b2\"><span style=\"color: red\">I understand that this backup will be deleted for ever!</span></label>
";
						echo "<br /><input type=\"submit\" value=\"Delete chosen backup!\" title=\"Once you click this button chosen backup will be removed!\" />
";
					}
				echo "</form>
";
				echo "</div></div></div>
";

				$current_cookie    = $_COOKIE['manage_imports'];
				if($current_cookie=='1')
					$current_display = "style=\"display:block; position: relative; left: 0px; top: 0px; border: 0px; padding: 0px; margin: 0px;\"";
						else
							$current_display = "class=\"adiv_style_clnkr\"";

				echo "<div><h3><a href=\"#h_8\" onclick=\"crosslinkerObj.make_cookie('manage_imports');crosslinkerObj.ReverseContentDisplay('manage_imports');\" name=\"h_8\" title=\"Allows exporting and importing data between different Cross-Linker installations\">Open/Close The Console For Managing Imports/Exports</a></h3>
";
				echo "<div id=\"manage_imports\" ".$current_display.">
<div class=\"bdiv_style_clnkr\">
<span style=\"color: Red; font-weight: bold;\">Important! If you'd like to perform import or export between Cross-Linker versions 1.x and 2.x, it's necessary to upgrade the 1.x version of Cross-Linker to 2.x version!</span><br />
";

				echo "<em>This feature allows you to import/export hyperlinked URLs and words along with attributes of such links between two or more blogs. Firstly you have to export data from one blog and then simply import these data into another blog. If exported word is already used in the target database, and if that word is active, all same exported words will be given the inactive attribute. If that already-existing word is inactive, attributes (meant as active/inactive) of exported words will be kept as in the original database. If exported word is already present in the target database, and if it points to the same URL as in the source database, no matter if such a word is active or not, it won't be imported (duplicate rows aren't needed, right?). Make sure that you don't store megabytes of data as you could experience problems when exporting these data into the textarea element. Generally, 2000 or 3000 words should be fine.</em>
";

				echo "<div style=\"padding: 1em;\"><form action=\"".$fix_uri."\" method=\"post\">
<input type=\"hidden\" name=\"export_links_into_textfile\" value=\"1\" /><input type=\"submit\" value=\"Export now!\" title=\"Perform export now!\" /> <b><em>Please, bear in mind that export may require some time if your cross-linker contains plenty of data!</em></b>
</form>
".$crossLinkerObj->insert_hr();
				if($_POST['export_links_into_textfile']=='1')
					{
						echo "<script type=\"text/javascript\">
<!--
		function select_all(obj)
			{
				var text_val=eval(obj);
				text_val.focus();
				text_val.select();
			}
	-->
</script>
<b><em>Copy&amp;paste entire text from the below-shown box and put it into the box that's designed for import. If you're not going to import right now, simply save the generated text in the <span style=\"color: #9B0004;\">txt</span> format and later use it for importing.</em></b><br />
<textarea cols=\"50\" rows=\"15\" readonly=\"readonly\" onclick=\"select_all(this)\">
";
						$table           = $wpdb->prefix . $crossLinkerObj::CROSSLINK_MAIN;

						$i               = $wpdb->get_var("SELECT * FROM $table WHERE visible = '1' ORDER BY id ASC LIMIT 1;",0);
						$sql             = "SELECT * FROM $table WHERE id = '$i';";
						$j               = 0;
						$table_name_attrs= $wpdb->prefix . $crossLinkerObj::CROSSLINK_ATTRB;
						$remember_i      = $i;

						//load language data
						$table_lng_data  = $wpdb->prefix . $crossLinkerObj::CROSSLINK_LNGS;
						$lng_sql         = "select id, lang_def from ".$table_lng_data." order by id asc;";
						$exp_lang[(-1)] = "cl_lang=DEF";
						//end load language data

						while($wpdb->get_var($sql,0)!='')
							{
								if($i!=$remember_i)
									echo "\n";
								echo $crossLinkerObj->uncheck_word(strtolower(stripslashes($wpdb->get_var("SELECT * FROM $table WHERE id = '".$i."' LIMIT 1;",1))))."\n";
								echo $crossLinkerObj->uncheck_word(stripslashes($wpdb->get_var("SELECT * FROM $table WHERE id = '".$i."' LIMIT 1;",2)))."\n";
								echo $crossLinkerObj->uncheck_word(stripslashes($wpdb->get_var("SELECT attrib FROM $table_name_attrs WHERE id = '$i' LIMIT 1;")))."\n";
								echo $crossLinkerObj->uncheck_word(stripslashes($wpdb->get_var("SELECT visible FROM $table WHERE id = '$i' LIMIT 1;")))."\n";
								echo "cl_lang=DEF";
								$i   = $wpdb->get_var("SELECT id FROM $table WHERE id > '$i' ORDER BY id asc LIMIT 1;",0);
								$sql = "SELECT * FROM $table WHERE id = '$i';";
							}
						echo "</textarea>
";
					}
				echo "<br />".$crossLinkerObj->insert_hr()."
<form action=\"".$fix_uri."\" method=\"post\"><label for=\"import_text_links\"><b>Import - Put output from export into the textarea below</b></label><br />
<textarea name=\"import_text_links\" id=\"import_text_links\" cols=\"50\" rows=\"15\" placeholder=\"put output from export operation here\" title=\"Place for export data\" required></textarea><br /><input type=\"submit\" value=\"Import now!\" title=\"Perform import operation right now\" /> <b><em>Please, bear in mind that import may require some time if you import plenty of data!</em></b>
</form>
";
				echo "</div>
";

				echo "</div></div></div>
";

				$current_cookie    = $_COOKIE['optimizedb'];
				if($current_cookie=='1')
					$current_display = "style=\"display:block; position: relative; left: 0px; top: 0px; border: 0px; padding: 0px; margin: 0px;\"";
						else
							$current_display = "class=\"adiv_style_clnkr\"";
				echo "<div><h3><a href=\"#h_9\" onclick=\"crosslinkerObj.make_cookie('optimizedb');crosslinkerObj.ReverseContentDisplay('optimizedb');\" name=\"h_9\" title=\"Optimise your WP database so it runs faster!\">Open/Close The Console For Optimising Database</a></h3>
";

				echo "<div id=\"optimizedb\" ".$current_display.">
<div class=\"bdiv_style_clnkr\">
	<label for=\"optim_db\">This procedure will optimise tables of your database. A long story short, this procedure will speed up access to your database if there are too many insert and delete operations being executed over any Wordpress data. Especially databases that have been created long time ago may contain lots of allocated empty spaces which slow down the process of reading/updating rows. Simply <b>click the button below</b>.</label><br />
<br />
<form action=\"".$fix_uri."\" method=\"post\">
<input type=\"hidden\" name=\"optimise_database\" value=\"1\">
<input type=\"submit\" value=\"Optimise whole WordPress database now!\" id=\"optim_db\" title=\"Perform optimisation now!\" />
</form>
<small><br />This is actually a bonus, <b>Cross-Linker</b> isn't about optimisation, but about hyperlinking instead :) .</small>
</div>
</div>
";

				echo "</div>
</div>
</div>
";

				echo "</div>
";
			}
}

	$crossLinkerObj = new crossLinkerClass;

	//pridanie do menu
	if(function_exists('add_action'))
		add_action('admin_menu', 'crossLinkerClass::add_pages');

	$processed_load = 0;

	//core settings
	$settings_table   = $wpdb->prefix . $crossLinkerObj::CROSSLINK_SETTS;
	$cut_empty_spaces = $wpdb->get_var("SELECT value FROM $settings_table WHERE setting = 'cut_empty_spaces' LIMIT 1;",0);
	//end core settings

	$crossLinkerObj->table_interlinker_install();

	add_action('in_admin_footer','crossLinkerClass::test_func');

	if(function_exists('add_filter'))
		{
			$can_add_link = 1;
			add_filter('the_content','crossLinkerClass::interlink_w_u',$crossLinkerObj->priority());
			$t4 = $wpdb->prefix . $crossLinkerObj::CROSSLINK_SETTS;
			$can_add_link = 0;
			if(($wpdb->get_var("SELECT id FROM $t4 WHERE ( (setting = 'link_comments') AND (value = '1') ) LIMIT 1;",0)!='')&&(function_exists('comment_text')))
				{
					add_filter('comment_text','crossLinkerClass::interlink_w_u',$crossLinkerObj->priority());
				}
		}
	//pripojime styly
	add_action('admin_init', 'crossLinkerClass::stylesheet');
	add_action('admin_init', 'crossLinkerClass::javascript');
?>
