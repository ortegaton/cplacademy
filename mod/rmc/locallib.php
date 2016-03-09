<?php
include_once ($CFG->dirroot . '/mod/rmc/include_cmis_library.php');
include_once ($CFG->dirroot . '/mod/rmc/lib.php');
include_once ($CFG->dirroot . '/mod/rmc/lib/decrypt.class.php');
include_once ($CFG->dirroot . '/mod/rmc/lib/config.php');
include_once ($CFG->dirroot . '/lib/conditionlib.php');
include_once ($CFG->dirroot . "/mod/rmc/lib/mail/swift_required.php");
include_once ($CFG->dirroot . "/course/modlib.php");

function expand_search_terms($search_text) {
    if (preg_match("/^\"[^\"]*\"$/", $search_text) or preg_match("/^'[^']*'$/", $search_text)) {
        // Special case of a phrase. The whole query is encolsed in double quotes.
        $search_text = substr($search_text, 1, -1);
        return "nvc:searchHelper:\'" . $search_text . "\'";
    }
    // Split the query into individual terms.
    $terms = preg_split("/[\s,-:]+/", $search_text);
    $terms = array_filter($terms, function ($value) {
        $value = strtolower($value);
        if ($value == 'and' || $value == 'or') {
            return false;
        } else {
            return strlen(trim($value)); 
        }
    });
    foreach ($terms as &$term) {
        $term = 'nvc:searchHelper:' . $term;
    }
    return implode(' AND ', $terms);
}

class cmis_client {

	private $_url;
	private $_username;
	private $_password;
	private $_client;
	private $_paidcount;
	private $_normalcount;
	private $ticket;
	private $_customername;
	private $_attemptcount = 1;


	public function __construct() {
		global $CFG;

		$this->_url = Decryption::decrypt(ALFRESCO_URL);
		$this->_username = Decryption::decrypt(ALFRESCO_USERNAME);
		$this->_password = Decryption::decrypt(ALFRESCO_PASSWORD);
		$this->_paidcount = 2;
		$this->_normalcount = 8;
		$this->_customername = rmc_helper::get_customer_name();
		$params = array('url' => $this->_url, 'username' => $this->_username, 'password' => $this->_password, 'paidcount' => 2, 'normalcount' => 8);
		$logid = rmc_write_to_log('start', 0, __FILE__, __FUNCTION__, $params, 'cmis client construct');
		try {
			$this->_client = new RMCCMISService($this->_url, $this->_username, $this->_password);
			$this->ticket = $this->get_alfresco_ticket($this->_url);
		} catch (Exception $e) {
			$this->_attemptcount++;
			if($this->_attemptcount > 3) {
				$this->_client = FALSE;
				echo  '<p style="border: 1px solid grey !important; width: 80% !important;font-family: sans-serif !important; text-align: justify !important; padding : 11px !important;">' . get_string('connectionerror', 'rmc') . '</p>';
			} else {
				self::__construct();
			}
		}
		rmc_write_to_log('end', $logid);
	}

	public function isValidConnection() {
		if($this->_client == FALSE) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	private function get_alfresco_ticket($server_url) {
		$api_url = str_replace('cmis', 'api/login', $server_url) . "?u=" . $this->_username . "&pw=" . $this->_password;
		$ticket = '';
		try {
			$ch = curl_init($api_url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$curl_response = curl_exec($ch);
			curl_close($ch);
			$xml_obj = new DOMDocument();
			$xml_obj->loadXML($curl_response);
			$dom_element = $xml_obj->documentElement;
			$ticket = $dom_element->firstChild->data;
		} catch ( Exception $e ) {
			throw new Exception($e->getMessage());
		}
		return $ticket;
	}

	public function get_ticket() {
		return $this->ticket;
	}



	public function get_item_info($object_id, $hasPublisher = 'no') {
		$item_query_wp = "SELECT D.*, T.*, N.*, V.* , P.*, TB.* FROM cmis:document D JOIN cm:titled T ON D.cmis:objectId = T.cmis:objectId
		                   JOIN nvc:package N ON D.cmis:objectId = N.cmis:objectId JOIN nvc:vetadata V ON D.cmis:objectId = V.cmis:objectId JOIN nvc:publisher P ON D.cmis:objectId = P.cmis:objectId JOIN nvc:toolbox TB ON D.cmis:objectId = TB.cmis:objectId WHERE D.cmis:objectId = '$object_id'";
		$item_query_wop = "SELECT D.*, T.*, N.*, V.*  FROM cmis:document D JOIN cm:titled T ON D.cmis:objectId = T.cmis:objectId
		                   JOIN nvc:package N ON D.cmis:objectId = N.cmis:objectId JOIN nvc:vetadata V ON D.cmis:objectId = V.cmis:objectId  WHERE D.cmis:objectId = '$object_id'";
		$item_result = $this->_client->query ( $item_query_wp );
		if($item_result->numItems == 0) {
			$item_result =  $this->_client->query ( $item_query_wop );
		}
		return $item_result->objectList [0];
	}

	public function is_scorm_package($object_id) {
		$query = "SELECT D.* FROM cmis:document D WHERE D.cmis:objectId = '$object_id' AND CONTAINS(D,'-ASPECT:\'nvc:toolbox\'') AND CONTAINS(D,'+ASPECT:\'nvc:package\'')";
		$item_result = $this->_client->query ( $query );
		if(isset($item_result->objectList[0])) {
			return TRUE;
		} else {
			return FALSE;
		}
	}

	/**
	 * Searches for document in repository
	 *
	 * @param string $search_text
	 * @return array
	 */
	public function search_fts($search_text, $page = 0) {
		global $OUTPUT, $DB, $USER, $CFG;
		$retarr_paid = array();
		$retarr_normal = array();
		$ocurl = new Curl();
		$pub_list = '';
		$token = $CFG->mod_rmc_token;
		$params = array(
				'token' => $token,
				'uname' => $USER->username,
				'from' => $CFG->wwwroot,
				'method' => 'get_pub_disallowed'
				);
				$pub_list = json_decode($ocurl->post(Decryption::decrypt(ALFRESCO_WEBFRONT_URL), $params));
				if(isset($pub_list->pub_list)) {
					$pub_list = $pub_list->pub_list;
				} else {
					$pub_list = '';
				}
				$logid = rmc_write_to_log('start', 0, __FILE__, __FUNCTION__, $search_text, 'before getting data from cmis');
				$search_properties = array();
				$retarr = array();
				$final_retarr = array();
				$temp = 0;
				$search_array = array();
				//$filters = $this->get_rmc_filters();
				$base_url = substr( $this->_url, 0, strpos ( $this->_url, '/alfresco/s/cmis' ) );
				$current_folder = $this->_client->getObjectByPath ( "/" );
				// 4 searches as follows (1, 2 and 4 all use search_query_wpub, 3 uses search_query_wopub)
				// 1st - publisher content at cost (upto 29)
				// 2nd - publisher content at no cost (upto 39-1st)
				// 3rd - non-publisher content (ie. NVET) (upto 49-1st-2nd)
				// 4th - publisher content "Not for individual sale" (upto 50-1st-2nd-3rd)
				$search_query_wpub = "
		SELECT
		
		T.cm:title, T.cm:description,
		D.alfcmis:nodeRef,  D.cmis:objectId,
		N.nvc:resourceType, N.nvc:viewURL, N.nvc:thumbURL, N.nvc:thumbDesc,
		V.nvc:classificationFutureDiscipline, V.nvc:classificationEducationalLevel, 
		V.nvc:classificationCompetency, V.nvc:costValue,
		P.nvc:publisherID, P.nvc:pubResourceType, P.nvc:titleTitle,
		TB.nvc:qualification 
		
		FROM
		
		cmis:document D JOIN cm:titled T ON D.cmis:objectId = T.cmis:objectId
		JOIN nvc:package N ON D.cmis:objectId = N.cmis:objectId
		JOIN nvc:vetadata V ON D.cmis:objectId = V.cmis:objectId
		JOIN nvc:toolbox TB ON D.cmis:objectId = TB.cmis:objectId
		JOIN nvc:publisher P ON D.cmis:objectId = P.cmis:objectId";

				$search_query_wopub = "
		SELECT
		
		D.cmis:objectId,
		T.cm:title, T.cm:description,
		N.nvc:resourceType, N.nvc:viewURL, N.nvc:thumbURL, N.nvc:thumbDesc,
		V.nvc:classificationFutureDiscipline, V.nvc:classificationEducationalLevel, 
		V.nvc:classificationCompetency, V.nvc:costValue 
		
		FROM
		
		cmis:document D JOIN cm:titled T ON D.cmis:objectId = T.cmis:objectId
		JOIN nvc:package N ON D.cmis:objectId = N.cmis:objectId
		JOIN nvc:vetadata V ON D.cmis:objectId = V.cmis:objectId";

                if ($search_text != "") {
                    $expanded_search_text = expand_search_terms($search_text);
                    $search_query_wpub .= " WHERE (CONTAINS(V, '$expanded_search_text'))";
                    $search_query_wopub .= " WHERE CONTAINS(D,'-ASPECT:\'nvc:publisher\'') AND NOT CONTAINS(T, 'cm:title:\'TOOLBOX\'')
                        AND (CONTAINS(V, '$expanded_search_text'))";
                }

				if($pub_list != '') {
					$search_query_wpub .= " AND P.nvc:publisherID NOT IN ($pub_list)";
				}
				$nodes = new stdClass();
				$search_query_wpub1 = $search_query_wpub . " AND CONTAINS(V, 'nvc:costValue:\'*\'') AND NOT CONTAINS(V, 'nvc:costValue:\'Not for individual sale\'')";
				$search_query_wpub2 = $search_query_wpub . " AND NOT CONTAINS(V, 'nvc:costValue:\'*\'')";
				$search_query_wpub4 = $search_query_wpub . " AND CONTAINS(V, 'nvc:costValue:\'Not for individual sale\'')";
				$query_options1 = array('maxItems' => 29);
				$nodes_wpub1 = $this->_client->query($search_query_wpub1, $query_options1);
				$nodes->numItems = count($nodes_wpub1->objectList);
				$query_options2 = array('maxItems' => (39 - $nodes->numItems));
				$nodes_wpub2 = $this->_client->query($search_query_wpub2, $query_options2);
				$nodes->numItems += count($nodes_wpub2->objectList);
				$query_options3 = array('maxItems' => (50 - $nodes->numItems));
				$nodes_w0pub = $this->_client->query($search_query_wopub, $query_options3);
				$nodes->numItems += count($nodes_w0pub->objectList);
				$nodes->objectList = array_merge($nodes_wpub1->objectList, $nodes_wpub2->objectList, $nodes_w0pub->objectList);
				rmc_write_to_log('end', $logid);

				$logid = rmc_write_to_log('start', 0, __FILE__, __FUNCTION__, $search_text, 'after getting data from cmis');
				foreach ($nodes->objectList as $node) {
					if(isset($node->properties["ext:extpublisher"])) {
						//continue;
					}
					if (isset($node->properties["nvc:thumbURL"]) && trim($node->properties["nvc:thumbURL"]) != '') {
						$thumbnail_url = $base_url . $node->properties["nvc:thumbURL"];
					} else {
						$thumbnail_url = $this->get_andresco_thumbnail_url($node);
					}
						
					if (isset($node->properties["nvc:thumbDesc"]) && trim($node->properties["nvc:thumbDesc"]) != '') {
						$thumbnail_desc = $node->properties["nvc:thumbDesc"];
					} else {
						$thumbnail_desc = '';
					}
					if(!isset($node->properties ['cm:title'])) {
						$node->properties ['cm:title'] = '';
					}
					if(!isset($node->properties['nvc:titleTitle'])) {
						$node->properties['nvc:titleTitle'] = '';
					}
					if(!isset($node->properties ['cm:description'])) {
						$node->properties ['cm:description'] = '';
					}
					if(!isset($node->properties ['cmis:lastModificationDate'])) {
						$date_modified = '';
					} else {
						$date_modified = date ( "D d F Y H:i:s", strtotime ( $node->properties ['cmis:lastModificationDate'] ) );
					}
					if(!isset($node->properties ['cmis:contentStreamLength'])) {
						$node->properties ['cmis:contentStreamLength'] = '';
					}
					if(!isset($node->properties ['nvc:costValue'])) {
						$node->properties ['nvc:costValue'] = '';
					}
					if(!isset($node->properties ['nvc:resourceType'])) {
						$node->properties ['nvc:resourceType'] = '';
					}
					if(!isset($node->properties ['nvc:classificationFutureDiscipline'])) {
						$node->properties ['nvc:classificationFutureDiscipline'] = '';
					}
					if(!isset($node->properties ['nvc:trainingPackage'])) {
						$node->properties ['nvc:trainingPackage'] = '';
					}
					if(!isset($node->properties ['nvc:classificationCompetency'])) {
						$node->properties ['nvc:classificationCompetency'] = '';
					}
					if(!isset($node->properties ['nvc:classificationEducationalLevel'])) {
						$node->properties ['nvc:classificationEducationalLevel'] = '';
					}
					if(!isset($node->properties ['nvc:toolboxCode'])) {
						$node->properties ['nvc:toolboxCode'] = '';
					}
					if(!isset($node->properties['nvc:qualification'])) {
						$node->properties['nvc:qualification'] = '';
					}
					if(!isset($node->properties['nvc:classificationFutureDiscipline'])) {
						$node->properties['nvc:classificationFutureDiscipline'] = '';
					}
					if(!isset($node->properties['nvc:publisherID'])) {
						$node->properties['nvc:publisherID'] = 'NA';
					}
					if(!isset($node->properties ['cmis:contentStreamMimeType'])) {
						$node->properties ['cmis:contentStreamMimeType'] = '';
					}
					if(!isset($node->properties['nvc:publisherID'])) {
						$node->properties['nvc:publisherID'] = 'NA';
					}
					if(!isset($node->properties['pub:publisherName'])) {
						$node->properties['pub:publisherName'] = '';
					}
					if(!isset($node->properties['nvc:pubResourceType'])) {
						$node->properties['nvc:pubResourceType'] = '';
					}
						
					$entry = array (
					'title' => $node->properties ['cm:title'],
					'titleTitle' => $node->properties['nvc:titleTitle'],
					'description' => $node->properties ['cm:description'],
					'datemodified_f' => $date_modified,
					'thumbnail' => $thumbnail_url,
					'content_share_link' => $this->get_andresco_share_url ( $node ),
					'size' => $node->properties ['cmis:contentStreamLength'],
					'file_type' => $node->properties ['cmis:contentStreamMimeType'],
					'source' => $node->id,
					'costValue' => $node->properties ['nvc:costValue'],
					'resourceType' => $node->properties ['nvc:resourceType'],
					'discipline' => $node->properties ['nvc:classificationFutureDiscipline'],
					'trainingPackage' => $node->properties ['nvc:trainingPackage'],
					'competancy' => $node->properties ['nvc:classificationCompetency'],
					'education_level' => $node->properties ['nvc:classificationEducationalLevel'],
					'toolbox' => $node->properties ['nvc:toolboxCode'],
					'qualification' => $node->properties['nvc:qualification'],
					'discipline' => $node->properties['nvc:classificationFutureDiscipline'],
					'thumbnail_desc' => $thumbnail_desc
					);
					if(isset($node->properties['nvc:publisherID']) || $node->properties['nvc:publisherID'] != 'NA') {
						$entry['publisherID'] = $node->properties['nvc:publisherID'];
						$entry['publisherName'] = $node->properties['pub:publisherName'];
						$entry['backcolor'] = '#DFDFDF';
						$entry['pubResourceType'] = $node->properties['nvc:pubResourceType'];
					}
					if (isset ( $node->properties ['nvc:costValue'] )) {
						$retarr_paid [] = $entry;
					} else {
						$retarr_normal [] = $entry;
					}
				}
				$final_retarr['result'] = $this->paidcontentlist($retarr_paid, $retarr_normal);
				$final_retarr['total_count'] = $nodes->numItems;
				rmc_write_to_log('end', $logid);

				return $final_retarr;
	}
	public function get_rmc_filters() {
		global $DB, $USER;
		$publisher = array();
		$customer_id = rmc_helper::validate_customer();
		$pub_sql = "SELECT P.name FROM mdl_andresco_purch_entity AS PE INNER JOIN mdl_andresco_publisher AS P ON P.name = PE.content_id
    								WHERE PE.entity_type = '0' AND PE.entity_id = '$customer_id' AND PE.content_type = '0'";
		$records = $DB->get_records_sql($pub_sql);
		foreach($records as $record) {
			$publisher[] = "'".$record->name."'";
		}
		return $publisher;
	}
	/**
	 *
	 * Performs advanced search
	 * @param string $search_text
	 * @param string $publisher
	 * @param string $discipline
	 * @param string $training_package
	 * @param string $resource_type
	 * @throws Exception
	 */
	public function search_advanced($search_text, $publisher, $discipline, $training_package, $resource_type) {
		global $OUTPUT, $DB;
		$search_properties = array ();
		$retarr = array ();
		$retarr_paid = $retarr_normal = array();
		$search_array = array ();
		$base_url = substr ( $this->_url, 0, strpos ( $this->_url, '/alfresco/s/cmis' ) );
		$current_folder = $this->_client->getObjectByPath ( "/" );
		
		if ($search_text == 'Search') {
			$error_message = get_string ( 'search_error', 'mod_rmc' );
			throw new Exception ( $error_message );
		}
		$publisher = "'" . implode ( "','", $publisher ) . "'";
		$training_package = "'" . implode ( "','", $training_package ) . "'";
		$resource_type = "'" . implode ( "','", $resource_type ) . "'";
		$discipline = "'" . implode ( "','", $discipline ) . "'";
		$cond_wopub = $cond_wpub = array();
		// 4 searches as follows (1, 2 and 4 all use search_query_wpub, 3 uses search_query_wopub)
		// 1st - publisher content at cost (upto 29)
		// 2nd - publisher content at no cost (upto 39-1st)
		// 3rd - non-publisher content (ie. NVET) (upto 49-1st-2nd)
		// 4th - publisher content "Not for individual sale" (upto 50-1st-2nd-3rd)
		$search_query_wpub = "
		SELECT
		
		T.cm:title, T.cm:description,
		D.alfcmis:nodeRef,  D.cmis:objectId,
		N.nvc:resourceType, N.nvc:viewURL, N.nvc:thumbURL, N.nvc:thumbDesc,
		V.nvc:classificationFutureDiscipline, V.nvc:classificationEducationalLevel, 
		V.nvc:classificationCompetency, V.nvc:costValue,
		P.nvc:publisherID, P.nvc:pubResourceType, P.nvc:titleTitle,
		TB.nvc:qualification 
		
		FROM
		
		cmis:document D JOIN cm:titled T ON D.cmis:objectId = T.cmis:objectId
		JOIN nvc:package N ON D.cmis:objectId = N.cmis:objectId
		JOIN nvc:vetadata V ON D.cmis:objectId = V.cmis:objectId
		JOIN nvc:toolbox TB ON D.cmis:objectId = TB.cmis:objectId
		JOIN nvc:publisher P ON D.cmis:objectId = P.cmis:objectId";

		$search_query_wopub = "
		SELECT
		
		D.cmis:objectId,
		T.cm:title, T.cm:description,
		N.nvc:resourceType, N.nvc:viewURL, N.nvc:thumbURL, N.nvc:thumbDesc,
		V.nvc:classificationFutureDiscipline, V.nvc:classificationEducationalLevel, 
		V.nvc:classificationCompetency, V.nvc:costValue 
		
		FROM
		
		cmis:document D JOIN cm:titled T ON D.cmis:objectId = T.cmis:objectId
		JOIN nvc:package N ON D.cmis:objectId = N.cmis:objectId
		JOIN nvc:vetadata V ON D.cmis:objectId = V.cmis:objectId";
		if ($search_text != "") {
            $expanded_search_text = expand_search_terms($search_text);
			$cond_wpub[] = " (CONTAINS(V, '$expanded_search_text'))";
			$cond_wopub[] = " CONTAINS(D,'-ASPECT:\'nvc:publisher\'') AND NOT CONTAINS(T, 'cm:title:\'TOOLBOX\'')
                        AND (CONTAINS(V, '$expanded_search_text'))";
		}
		if ($training_package != "''") {
			$cond_wpub[] = " N.nvc:trainingPackage IN ($training_package)";
			$cond_wopub[] = " N.nvc:trainingPackage IN ($training_package)";
		}
		if ($discipline != "''") {
			$cond_wpub[] = " V.nvc:classificationFutureDiscipline IN ($discipline)";
			$cond_wopub[] = " V.nvc:classificationFutureDiscipline IN ($discipline)";
		}
		if ($resource_type != "''") {
			$cond_wpub[] = " P.nvc:pubResourceType IN ($resource_type)";
			//$search_query_wopub .= " AND N.nvc:resourceType IN ($resource_type)";
		}
		if ($publisher != "''") {
			$cond_wpub[] = " P.nvc:publisherID IN ($publisher)";
		}
		if(count($cond_wpub) > 0) {
			$search_query_wpub = $search_query_wpub . ' WHERE '. implode(' AND ', $cond_wpub);
			$search_query_wpub1 = $search_query_wpub . " AND CONTAINS(V, 'nvc:costValue:\'*\'') AND NOT CONTAINS(V, 'nvc:costValue:\'Not for individual sale\'')";
			$search_query_wpub2 = $search_query_wpub . " AND NOT CONTAINS(V, 'nvc:costValue:\'*\'')";
			$search_query_wpub4 = $search_query_wpub . " AND CONTAINS(V, 'nvc:costValue:\'Not for individual sale\'')";
		} else {
			$search_query_wpub1 = $search_query_wpub . " CONTAINS(V, 'nvc:costValue:\'*\'') AND NOT CONTAINS(V, 'nvc:costValue:\'Not for individual sale\'')";
			$search_query_wpub2 = $search_query_wpub . " NOT CONTAINS(V, 'nvc:costValue:\'*\'')";
			$search_query_wpub4 = $search_query_wpub . " CONTAINS(V, 'nvc:costValue:\'Not for individual sale\'')";
		}
		if(count($cond_wopub) > 0) {
			$search_query_wopub = $search_query_wopub . ' WHERE '. implode(' AND ', $cond_wopub);
		}
		
		$nodes = new stdClass();
		$query_options1 = array('maxItems' => 29);
		$nodes_wpub1 = $this->_client->query($search_query_wpub1, $query_options1);
		$nodes->numItems = count($nodes_wpub1->objectList);
		$query_options2 = array('maxItems' => (39 - $nodes->numItems));
		$nodes_wpub2 = $this->_client->query($search_query_wpub2, $query_options2);
		$nodes->numItems += count($nodes_wpub2->objectList);
		$query_options3 = array('maxItems' => (50 - $nodes->numItems));
		if($publisher == "''" && $resource_type == "''") {
			$nodes_w0pub = $this->_client->query($search_query_wopub, $query_options3);
			$nodes->numItems += count($nodes_w0pub->objectList);
		}
		if(isset($nodes_w0pub->objectList)) {
			$nodes->objectList = array_merge($nodes_wpub1->objectList, $nodes_wpub2->objectList, $nodes_w0pub->objectList);
		} else {
			$nodes->objectList = array_merge($nodes_wpub1->objectList, $nodes_wpub2->objectList);
		}
		
		foreach ($nodes->objectList as $node) {
			if(isset($node->properties["ext:extpublisher"])) {
				//continue;
			}
			if (isset($node->properties["nvc:thumbURL"]) && trim($node->properties["nvc:thumbURL"]) != '') {
				$thumbnail_url = $base_url . $node->properties["nvc:thumbURL"];
			} else {
				$thumbnail_url = $this->get_andresco_thumbnail_url($node);
			}
				
			if (isset($node->properties["nvc:thumbDesc"]) && trim($node->properties["nvc:thumbDesc"]) != '') {
				$thumbnail_desc = $node->properties["nvc:thumbDesc"];
			} else {
				$thumbnail_desc = '';
			}
			if(!isset($node->properties ['cm:title'])) {
						$node->properties ['cm:title'] = '';
					}
					if(!isset($node->properties['nvc:titleTitle'])) {
						$node->properties['nvc:titleTitle'] = '';
					}
					if(!isset($node->properties ['cm:description'])) {
						$node->properties ['cm:description'] = '';
					}
					if(!isset($node->properties ['cmis:lastModificationDate'])) {
						$date_modified = '';
					} else {
						$date_modified = date ( "D d F Y H:i:s", strtotime ( $node->properties ['cmis:lastModificationDate'] ) );
					}
					if(!isset($node->properties ['cmis:contentStreamLength'])) {
						$node->properties ['cmis:contentStreamLength'] = '';
					}
					if(!isset($node->properties ['nvc:costValue'])) {
						$node->properties ['nvc:costValue'] = '';
					}
					if(!isset($node->properties ['nvc:resourceType'])) {
						$node->properties ['nvc:resourceType'] = '';
					}
					if(!isset($node->properties ['nvc:classificationFutureDiscipline'])) {
						$node->properties ['nvc:classificationFutureDiscipline'] = '';
					}
					if(!isset($node->properties ['nvc:trainingPackage'])) {
						$node->properties ['nvc:trainingPackage'] = '';
					}
					if(!isset($node->properties ['nvc:classificationCompetency'])) {
						$node->properties ['nvc:classificationCompetency'] = '';
					}
					if(!isset($node->properties ['nvc:classificationEducationalLevel'])) {
						$node->properties ['nvc:classificationEducationalLevel'] = '';
					}
					if(!isset($node->properties ['nvc:toolboxCode'])) {
						$node->properties ['nvc:toolboxCode'] = '';
					}
					if(!isset($node->properties['nvc:qualification'])) {
						$node->properties['nvc:qualification'] = '';
					}
					if(!isset($node->properties['nvc:classificationFutureDiscipline'])) {
						$node->properties['nvc:classificationFutureDiscipline'] = '';
					}
					if(!isset($node->properties['nvc:publisherID'])) {
						$node->properties['nvc:publisherID'] = 'NA';
					}
					if(!isset($node->properties ['cmis:contentStreamMimeType'])) {
						$node->properties ['cmis:contentStreamMimeType'] = '';
					}
				
			$entry = array (
					'title' => $node->properties ['cm:title'],
					'titleTitle' => $node->properties['nvc:titleTitle'],
					'description' => $node->properties ['cm:description'],
					'datemodified_f' => $date_modified,
					'thumbnail' => $thumbnail_url,
					'content_share_link' => $this->get_andresco_share_url ( $node ),
					'size' => $node->properties ['cmis:contentStreamLength'],
					'file_type' => $node->properties ['cmis:contentStreamMimeType'],
					'source' => $node->id,
					'costValue' => $node->properties ['nvc:costValue'],
					'resourceType' => $node->properties ['nvc:resourceType'],
					'discipline' => $node->properties ['nvc:classificationFutureDiscipline'],
					'trainingPackage' => $node->properties ['nvc:trainingPackage'],
					'competancy' => $node->properties ['nvc:classificationCompetency'],
					'education_level' => $node->properties ['nvc:classificationEducationalLevel'],
					'toolbox' => $node->properties ['nvc:toolboxCode'],
					'qualification' => $node->properties['nvc:qualification'],
					'discipline' => $node->properties['nvc:classificationFutureDiscipline'],
					'thumbnail_desc' => $thumbnail_desc
			);
			if(isset($node->properties['nvc:publisherID']) || $node->properties['nvc:publisherID'] != 'NA') {
				$entry['publisherID'] = $node->properties['nvc:publisherID'];
				$entry['publisherName'] = $node->properties['pub:publisherName'];
				$entry['backcolor'] = '#DFDFDF';
				$entry['pubResourceType'] = $node->properties['nvc:pubResourceType'];
			}
			if (isset ( $node->properties ['nvc:costValue'] )) {
				$retarr_paid [] = $entry;
			} else {
				$retarr_normal [] = $entry;
			}
		}
		$final_retarr['result'] = $this->paidcontentlist($retarr_paid, $retarr_normal);
		$final_retarr['total_count'] = $nodes->numItems;
		//rmc_write_to_log('end', $logid);

		return $final_retarr;
	}
	/**
	 * send a query to cmis
	 * @param string $query
	 * @return array
	 */
	public function send_query($query) {
		$nodes = $this->_client->query($query);
		return $nodes;
	}
	/**
	 * Function to order paid content and normal content
	 *
	 * @param		raw contents from repository
	 * @return		array
	 *
	 */
	private function paidcontentlist($retarr_paid, $retarr_normal) {
		$temp_paid = 0;
		$temp_normal = 0;
		$final_retarr = array();
		$count = count($retarr_normal) + count($retarr_paid);
		if ($count != 0) {
			for ($i = 0; $i < $count; $i++) {
				if (isset($retarr_paid[$temp_paid]) || isset($retarr_normal[$temp_normal])) {
					if (isset($retarr_paid[$temp_paid])) {
						for ($j = 0; $j < $this->_paidcount; $j++) {
							if (isset($retarr_paid[$temp_paid]) && isset($retarr_paid[$temp_paid]['costValue'])) {
								$final_retarr[] = $retarr_paid[$temp_paid];
								$temp_paid++;
							}
						}
					}
					if (isset($retarr_normal[$temp_normal])) {
						for ($k = 0; $k < $this->_normalcount; $k++) {
							if (isset($retarr_normal[$temp_normal]) && !isset($retarr_normal[$temp_normal]['costValue'])) {
								$final_retarr[] = $retarr_normal[$temp_normal];
								$temp_normal++;
							}
						}
					}
				} else {
					break;
				}
			}
		}
		return $final_retarr;
	}

	/**
	 * Generates a url for the target content thumbnail
	 *
	 * @param		Alfresco node being accessed
	 * @return		Alfresco content URL in Andresco format
	 *
	 */
	public function get_andresco_thumbnail_url($node) {
		$base_url = substr($this->_url, 0, strpos($this->_url, '/alfresco/s/cmis'));
		$uuid = str_replace('urn:uuid:', '', $node->uuid);
		$thumbnail_url = $base_url . '/alfresco/s/api/node/workspace/SpacesStore/' . $uuid . '/content/thumbnails/doclib?c=queue&ph=true&alf_ticket=' . $this->ticket; //CHANGE
		return $thumbnail_url;
	}
	public function get_andresco_share_url($node) {
		$base_url = substr($this->_url, 0, strpos($this->_url, '/alfresco/s/cmis'));
		$share_url = $base_url . '/share/page/document-details?nodeRef=' . $node->id; //CHANGE
		return $share_url;
	}
	/**
	 * Generates a url for the target content in a format that redirects
	 * to the Alfresco Moodle-Authentication script (auth.php)
	 * which takes care of things such as:
	 * - Authentication ticket generation
	 * - Verifying access to the content (and restricting access from
	 * non-Moodle, untrusted sources).
	 * - Retrieving the appropriate content (e.g. version).
	 *
	 * @param
	 *          Alfresco node being accessed
	 * @return Alfresco content URL in Andresco format
	 *
	 */
	public function get_rmc_auth_url($node, $course_id = 0) {
		global $CFG, $COURSE, $USER, $SITE, $DB;

		if($course_id != 0) {
			$COURSE = $DB->get_record('course', array('id'=> $course_id));
		}

		$base_url = substr ( $this->_url, 0, strpos ( $this->_url, '/alfresco/s/cmis' ) );
		if (! empty ( $this->options ['andresco_auth'] )) {
			$alfresco_script = $this->options ['andresco_auth'];
		} else {
			$alfresco_script = "auth.php";
		}
		$filename = urlencode ( $node->properties ['cmis:name'] );
		$uuid = str_replace ( 'urn:uuid:', '', $node->uuid );
		$target_url = "$base_url/$alfresco_script?uuid=$uuid&filename=$filename";
		

		// Append moodle name to URL
		// Use site fullname as moodle name, fallback to dbname
		$moodle_name = $CFG->dbname;
		if (isset ( $SITE->fullname )) {
			$moodle_name = $SITE->fullname;
		}
		$target_url .= "&moodlename=" . urlencode ( $moodle_name );

		// Append course id to URL
		// Fallback to a value of 0 if course id is NOT available
		$course_id = 0;
		if (isset ( $COURSE->id )) {
			$course_id = $COURSE->id;
		}
		$target_url .= "&courseid=$course_id";

		// Append course name to URL
		// Fallback to a value of "N/A" if course name is not available
		$course_shortname = 'N/A';
		if (isset ( $COURSE->shortname ) && $COURSE->shortname !== '') {
			$course_shortname = $COURSE->shortname;
		}
		$target_url .= "&courseshortname=" . urlencode ( $course_shortname );

		if(isset($node->properties ['nvc:publisherID'])) {
			$target_url .= "&publisher_id=" . $node->properties ['nvc:publisherID'];
		}

		if(isset($node->properties ['nvc:publisherID'])) {
			$target_url .= "&publisher_id=" . $node->properties ['nvc:publisherID'];
		}

		// Append user ID to URL
		// Fallback to a value of "0" if user id is not available
		$user_id = 0;
		if (isset ( $USER->id )) {
			$user_id = $USER->id;
		}
		$target_url .= "&userid=$user_id";

		// Append user name to URL
		// Fallback to a value of "guest" if username is not available
		$username = 'guest';
		if (isset ( $USER->username )) {
			$username = $USER->username;
		}
		$target_url .= "&username=" . urlencode ( $username );

		//Appending the customer name
		$target_url .= "&customername=" . urlencode ( $this->_customername );
		if($node->properties ['nvc:costValue'] == '' || $node->properties ['nvc:costValue'] == 'no') {
			$target_url .= "&check=0";
		}else {
			$target_url .= "&check=1";
		}

		$target_url .= '&from=rmc';


		return $target_url;
	}

	public function get_content_view_url_lite($node_obj) {
		global $CFG;
		$base_url = substr ( Decryption::decrypt(ALFRESCO_URL), 0, strpos ( Decryption::decrypt(ALFRESCO_URL), '/alfresco/s/cmis' ) );
		$url = "";
		if(isset($node_obj->properties ["nvc:viewURL"]) && isset($node_obj->properties ["nvc:previewURL"])) {
			$url = $node_obj->properties ["nvc:previewURL"];
		} else if(isset($node_obj->properties ["nvc:viewURL"]) && !isset($node_obj->properties ["nvc:previewURL"])) {
			$url = $node_obj->properties['nvc:viewURL'];
		} else if(!isset($node_obj->properties ["nvc:viewURL"]) && isset($node_obj->properties ["nvc:previewURL"])) {
			$url = $node_obj->properties ["nvc:previewURL"];
		}
		$url = trim($url);
		if ($url == "") {
			return "#";
		} else if (strstr($url, '://') or substr($url, 0, 2) == '//') {
			// Assume that this is an external URL.
			return $url;
		} else {
			// Assume that this is an internal URL.
			$url_components = parse_url($url);
			parse_str($url_components["query"], $query);
			unset($query['guest']);
			$query['ticket'] = $this->ticket;
			$url_components["query"] = http_build_query($query);
			$url = $url_components["path"] . "?" . $url_components["query"];
			if (isset($url_components["fragment"])) {
				$url .= '#' . $url_components["fragment"];
			}
			return $base_url . $url;
		}
	}

	public function get_rmc_help_text() {
		global $CFG;
		if($this->_client == false) {
			return get_string('connectionerror', 'rmc');
		}
		if(trim($CFG->mod_rmc_module_helptext_id) == "") {
			$CFG->mod_rmc_module_helptext_id = 'workspace://SpacesStore/8a0d47ec-d634-4687-94d8-adf6d4b36453';
		}
		$query = "SELECT * FROM cmis:document WHERE cmis:objectId = '$CFG->mod_rmc_module_helptext_id'";
		$item_result = $this->_client->query ( $query );
		if(isset($item_result->objectList[0])) {
			$nodeobj = $item_result->objectList[0];
			$base_url = substr ( Decryption::decrypt(ALFRESCO_URL), 0, strpos ( Decryption::decrypt(ALFRESCO_URL), '/alfresco/s/cmis' ) );
			$filename = $nodeobj->properties['cmis:contentStreamFileName'];
			$uid = str_replace('urn:uuid:', '', $nodeobj->uuid);
			$help_url = $base_url."/alfresco/download/attach/workspace/SpacesStore/$uid/$filename?c=force&noCache=1389612771357&a=true&ticket=".$this->ticket;
			$help_contents = file_get_contents($help_url);
		} else {
			$help_contents = "<h1>Ready Made Content</h1>";
		}
		return $help_contents;
	}
	
	public function is_free_content($node_id) {
		$flag = FALSE;
		$query = "SELECT D.*, T.*, N.*, V.*  FROM cmis:document D JOIN cm:titled T ON D.cmis:objectId = T.cmis:objectId
		                   JOIN nvc:package N ON D.cmis:objectId = N.cmis:objectId JOIN nvc:vetadata V ON D.cmis:objectId = V.cmis:objectId  WHERE D.cmis:objectId = '$node_id'";
		$item_result = $this->_client->query ( $query );
		if(isset($item_result->objectList[0])) {
			$node_obj = $item_result->objectList[0];
			if(isset($node_obj->properties["nvc:costValue"])){
				if($node_obj->properties["nvc:costValue"] == 'no') {
					$flag = TRUE;
				}
			} else {
				$flag = TRUE;
			}
		}
		return $flag;
	}

}

class rmc_helper {


	public static function get_customer_name() {
		global $CFG, $USER, $SITE;
		$params = array(
				'token' => $CFG->mod_rmc_token,
				'uname' => $USER->username,
				'sitename' => $SITE->shortname,
				'from' => $CFG->wwwroot.'/',
				'method' => 'get_customer_name'
				);

				$ocurl = new Curl();
				$customer_data = json_decode($ocurl->post(Decryption::decrypt(ALFRESCO_WEBFRONT_URL), $params));
				if (isset($customer_data->loginerror ) && ($customer_data->loginerror )) {
					return 'NA';
				}
				return $customer_data[0];
	}

	public static function add_purcharse_entry($course_id, $uuid, $no_licenses, $authorise_mail) {
		global $CFG, $DB;
		$course = $DB->get_record('course', array('id' => $course_id));
		$params = array(
			'course_url' => $CFG->wwwroot . '/course/view.php?id=' . $course_id,
			'course_shortname' => $course->shortname,
			'course_fullname' => $course->fullname, 
			'from' => $CFG->wwwroot,
			'entity_type' => 'course',
			'content_type' => 'content',
			'content_id' => str_replace('workspace://SpacesStore/', '', $uuid),
			'no_unique_users' => $no_licenses,
			'expiry_date' => time() + (365 * 24 * 60 * 60),
			'authorisation' => $authorise_mail,
			'method' => 'add_purch_entity'
			);
			$ocurl = new Curl();
			$return_data = json_decode($ocurl->post(Decryption::decrypt(ALFRESCO_WEBFRONT_API_URL), $params));
			return TRUE;
	}

	public static function generate_token($length = 30) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}



	/**
	 *
	 * Check whether a token has been generated against a node id.
	 * @param $node_id
	 */
	public static function get_embed_token($node_id) {
		global $DB;
		$query = "SELECT id, embed_token FROM {rmc_embed_url_token} WHERE node_id = '$node_id'";
		$rec = $DB->get_record_sql($query);
		if(!$rec) {
			$token = self::generate_token();
			$insert_query = "INSERT INTO {rmc_embed_url_token}(embed_token, node_id) VALUES('$token', '$node_id')";
			$DB->execute($insert_query);
		} else {
			$token = $rec->embed_token;
		}
		return $token;
	}

	/**
	 *
	 *
	 * Returns the HTML for buy popup
	 *
	 * @param
	 *          Alfresco Node $node
	 */
	public static function print_buy_popup($node, $course_id = "", $share_url) {
		global $USER, $CFG, $DB;
		//$alfresco_url = substr ( $CFG->mod_rmc_alfresco_url, 0, strpos ( $CFG->mod_rmc_alfresco_url, '/alfresco/s/cmis' ) );
		if(isset($node->properties ["nvc:titleTitle"]) && (trim($node->properties["nvc:pubResourceType"]) != 'eBook') && trim($node->properties['nvc:titleTitle']) != '') {
			$item = $node->properties ["cm:title"] . ' (' . $node->properties ["nvc:titleTitle"] . ')';
		} else {
			$item = $node->properties ["cm:title"];
		}
		if(isset($node->properties['nvc:publisherID'])) {
			$publisher_id = $node->properties['nvc:publisherID'];
		} else {
			$publisher_id = 'NA';
		}
		//$link_to_item = $alfresco_url . '/share/page/document-details?nodeRef=' . $node->id;
		$selector = $USER->firstname . ' ' . $USER->lastname;
		$course_obj = $DB->get_record ( 'course', array (
        'id' => $course_id 
		), 'fullname', MUST_EXIST );
		$course_name = $course_obj->fullname;
		$organisation = "NA";
		$cost_value = $node->properties ["nvc:costValue"];
		$pub_info = json_decode(self::get_pub_auth_mail($publisher_id));
		if(isset($pub_info[0])) {
			$authorisation_email = $pub_info[0]->from_email_address;
			if(trim($pub_info[0]->term_url) != '' && trim($pub_info[0]->term_html) != '') {
				$licence_html = html_writer::link($pub_info[0]->term_url, get_string ( 'licence_label', 'rmc' ), array('target' => '_blank'));
			} else if(trim($pub_info[0]->term_url) != '' && trim($pub_info[0]->term_html) == '') {
				$licence_html = html_writer::link($pub_info[0]->term_url, get_string ( 'licence_label', 'rmc' ), array('target' => '_blank'));
			} else if(trim($pub_info[0]->term_url) == '' && trim($pub_info[0]->term_html) != '') {
				echo "<div id='term-cond' style='display:none;'>".$pub_info[0]->term_html."</div>";
				$licence_html = html_writer::link('#', get_string ( 'licence_label', 'rmc' ), array('id' => 'term-popup'));
			} else {
				$licence_html = get_string ( 'licence_label', 'rmc' );
			}
		} else {
			$licence_html = get_string ( 'licence_label', 'rmc' );
		}
		$node_id = $node->id;
		$enrol_count = self::get_course_enrol_users ( $course_id );
		$section = required_param ( 'section', PARAM_INT );
		$cmid = optional_param ( 'cmid', 0, PARAM_INT );
		$sr = optional_param ( 'sr', 0, PARAM_INT );
		$purchase_id = optional_param ( 'prid', 0, PARAM_INT );
		$customer_name = self::get_customer_name();
		if(trim($CFG->mod_rmc_email) != '') {
			$email_html = "<span id='item'>$CFG->mod_rmc_email</span><input type='hidden' id='authorise_mail' name='authorise_mail' value='$CFG->mod_rmc_email' >";
		} else {
			$email_html = "<input type='text' size='34' id='authorise_mail' name='authorise_mail' value='$USER->email' />";
		}

		$pop_html = "<div id='buy_popup' style='display:none'><div id='message' style='color:red; align:center!important;'></div><form id='buy_popup_form'>
			<table>
				<tr>
					<td>" . get_string ( 'item_label', 'mod_rmc' ) . "</td>
					<td><span id='item'>$item</span></td>
				</tr>
				<tr>
				<td>" . get_string ( 'publisher_label', 'mod_rmc' ) . "</td>
				<td><span id='link_to_item'>$publisher_id</span></td>
				</tr>
				<tr>
					<td>" . get_string ( 'customer_label', 'mod_rmc' ) . "</td>
					<td><span id='selector'>$customer_name</span></td>
				</tr>
				<tr>
					<td>" . get_string ( 'purchaser_name_label', 'mod_rmc' ) . "</td>
					<td><span id='selector'>$USER->firstname $USER->lastname ($USER->username)</span></td>
				</tr>
				<tr>
					<td>" . get_string ( 'purchaser_email_label', 'mod_rmc' ) . "</td>
					<td>" .$email_html. "</td>
				</tr>
				<tr>
				<td>" . get_string ( 'course_name_label', 'mod_rmc' ) . "</td>
				<td><span id='course_name'>$course_name</span></td>
				</tr>
				<tr>
					<td>" . get_string ( 'no_licence_label', 'mod_rmc' ) . "</td>
					<td>
					<input type='text' id='no_licenses' value='$enrol_count' />
					<input type='hidden' id='enrol_count' value='$enrol_count' />
					<input type='hidden' id='course_id' value= '$course_id' />
					<input type='hidden' id='node_id' value= '$node_id' />
					<input type='hidden' id='alfresco_share_url' value= '$share_url' />
					<input type='hidden' id='section' value= '$section' />
					<input type='hidden' id='cmid' value= '$cmid' />
					<input type='hidden' id='sr' value= '$sr' />
					<input type='hidden' id='purchase_id' value= '$purchase_id' />
					<input type='hidden' id='publisher_id' value= '$publisher_id' />
					<input type='hidden' id='publisher_email' value='$authorisation_email' />
					</td>
				</tr>
				<tr>
					<td>" . get_string ( 'costvalue_label', 'rmc' ) . "</td>
					<td><span id='cost'>$cost_value</span></td>
				</tr>
				<tr>
					<td colspan='2'><input type='checkbox' id='licence_chk' value='1' /> $licence_html </td>
				</tr>
				<tr>
					<td class='nobor' colspan=2><input type='button' id='bt_buy_popup' value='" . get_string ( 'buy_label', 'mod_rmc' ) . "' />&nbsp;&nbsp;<input type='button' id='bt_cancel_popup' value='" . get_string ( 'cancel' ) . "' /></td>
				</tr>
			</table>
			</form>
		</div>";
		return $pop_html;
	}

	/**
	 *
	 *
	 * Send mail
	 *
	 * @param array $data
	 */
	public static function send_mail($data) {
		 
		global $USER, $CFG;
		/* $smtp_port = $smtp_host = '';
		 $temp = explode(':', $CFG->smtphosts);
		 if(count($temp) > 1) {
		 $smtp_host = $temp[0];
		 $smtp_port = $temp[1];
		 } else {
		 $smtp_host = $temp[0];
		 }
		 if($smtp_port == '') {
		 if($CFG->smtpsecure == 'tls') {
		 $smtp_port = 587;
		 } else if($CFG->smtpsecure == 'ssl') {
		 $smtp_port = 465;
		 } else {
		 $smtp_port = 25;
		 }
		 }//$mail_content['toaddress']
		  
		 $transport = Swift_SmtpTransport::newInstance($smtp_host, $smtp_port, $CFG->smtpsecure)
		 ->setUsername($CFG->smtpuser)
		 ->setPassword($CFG->smtppass);
		 $message_html = text_to_html ( stripslashes($data ['body']), false, false, true );
		 $mailer = Swift_Mailer::newInstance($transport);
		 $message = Swift_Message::newInstance($data ['subject'])
		 ->setFrom(array($data['fromaddress']))
		 ->setReplyTo(array($data['fromaddress'] ))
		 ->setTo(array('tmervyn@gmail.com' => $USER->firstname))
		 ->setBody(stripslashes($data ['body']), 'text/html');
		  
		 try {
		  
		 $result = $mailer->send($message);

		 } catch (Exception $e) {
		 $result = $e->getMessage();
		 }
		 return $result; */
		//$message_html = text_to_html ( stripslashes($data ['body']), false, false, true );
		//$message_html = text_to_html ( $data ['body'], false, false, true );
		$fromuser = new stdClass ();
		$fromuser->email = $data['publisher_from_email'];
		$fromuser->firstname = $data['publisher_name'];
		$fromuser->lastname = '';
		$fromuser->maildisplay = true;
		$fromuser->alternatename = '';
		$fromuser->middlename = '';
		$fromuser->lastnamephonetic = '';
		$fromuser->firstnamephonetic = '';
		$fromuser->id = $USER->id;
		//Send mail to customer
		$touser = new stdClass ();
		$touser->email = $data['authorise_mail'];
		$touser->mailformat = 1;
		$touser->id = $USER->id;
		$touser->alternatename = '';
		$touser->middlename = '';
		$touser->lastnamephonetic = '';
		$touser->firstnamephonetic = '';
		$message_html = text_to_html ( stripslashes(stripslashes($data ['customer_mail'])), false, false, true );
		try {
			rmc_helper::rmc_email_to_user ( $touser, $fromuser, $data ['cust_mail_subject'], stripslashes(stripslashes($data ['customer_mail'])), $message_html );
		} catch(Exception $e) {
			return $e->getMessage();
		}

		//Send mail to publisher
		$pubuser = new stdClass();
		$pubuser->email = $data['publisher_from_email'];
		$pubuser->firstname = $data['publisher_name'];
		$pubuser->mailformat = 1;
		$pubuser->id = $USER->id;
		$pubuser->alternatename = '';
		$pubuser->middlename = '';
		$pubuser->lastnamephonetic = '';
		$pubuser->firstnamephonetic = '';
		$message_html = text_to_html ( stripslashes($data ['publisher_mail_html']), false, false, true );
		try {
			rmc_helper::rmc_email_to_user ( $pubuser, $fromuser, stripslashes($data ['publisher_mail_subject']), stripslashes($data ['publisher_mail_html']), $message_html );
		} catch(Exception $e) {
			return $e->getMessage();
		}

		//Send mail to VET Commons sales team
		$pubuser = new stdClass();
		$pubuser->email = "sales@vetcommons.edu.au";
		$pubuser->firstname = "Sales Team";
		$pubuser->mailformat = 1;
		$pubuser->id = $USER->id;
		$pubuser->alternatename = '';
		$pubuser->middlename = '';
		$pubuser->lastnamephonetic = '';
		$pubuser->firstnamephonetic = '';
		$message_html = text_to_html ( stripslashes($data ['publisher_mail_html']), false, false, true );
		try {
			rmc_helper::rmc_email_to_user ( $pubuser, $fromuser, stripslashes($data ['publisher_mail_subject']), stripslashes($data ['publisher_mail_html']), $message_html );
		} catch(Exception $e) {
			return $e->getMessage();
		}
		return true;
	}

	public static function process_html($str, $variables) {
		foreach($variables as $key => $value) {
			$str = str_replace($key, $value, $str);
		}
		return addslashes($str);
	}

	public static function validate_customer() {
		global $DB, $USER, $SITE;
		$query = "select C.id AS customer_id FROM mdl_andresco_lms AS L
	      INNER JOIN mdl_andresco_cust_lms_mapping AS CL ON L.id = CL.idlms 
	      INNER JOIN  mdl_andresco_customer AS C ON C.id = CL.idcustomer 
	      WHERE L.code = '$SITE->shortname' AND C.name = '$USER->username'";
		$result = $DB->get_record_sql ( $query );
		if (isset ( $result->customer_id )) {
			return $result->customer_id;
		} else {
			throw new Exception ( "You don't have the permission to purchase content" );
		}
	}
	public static function fetch_search_values($property_name) {
		global $CFG;
		$alfresco_url = Decryption::decrypt(ALFRESCO_URL);
		$alfresco_url = str_replace ( '/s/cmis', '/service/andresco/prop/constraints', $alfresco_url );
		$post_url = $alfresco_url . "?props=" . $property_name;
		$retarr = array ();
		$ticket = '';
		try {
			$ch = curl_init ( $post_url );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
			$curl_response = curl_exec ( $ch );
			curl_close ( $ch );
			$options_array = json_decode ( $curl_response );

			foreach ( $options_array [0]->$property_name as $option ) {
				if (trim ( $option ) != "") {
					$retarr [$option] = $option;
				}
			}
		} catch ( Exception $e ) {
			throw new Exception ( $e->getMessage () );
		}
		return $retarr;
	}
	public static function fetch_publisher_options() {
		$client = new cmis_client();
		$publishers = $client->get_rmc_filters();
		$return = array();
		foreach($publishers as $key => $value) {
			$return[$value] = $value;
		}
		return $return;
	}

	public function get_content_view_url_lite($node_obj) {
		global $CFG;
		$client = new cmis_client();
		return $client->get_content_view_url_lite($node_obj);
	}
	public static function get_node_purchase_status($user_id, $node_id) {
		global $DB;
		return $DB->record_exists("rmc_purchase_detail", array('user_id' => $user_id, 'node_id' => $node_id));
	}
	public static function get_auth_url($node_id, $course_id = 0) {
		$client = new cmis_client ();
		$node_obj = $client->get_item_info ( $node_id );
		return $client->get_rmc_auth_url ( $node_obj, $course_id );
	}
	public static function validate_nodeid($node_id) {
		$client = new cmis_client();
		$node_obj = $client->get_item_info ( $node_id );
		return is_null($node_obj);
	}
	public static function get_purchase_id($node_id) {
		global $USER, $DB;
		$sql = "SELECT id from mdl_rmc_purchase_detail where user_id = $USER->id AND node_id = '$node_id' LIMIT 1";
		$purchase_obj = $DB->get_record_sql($sql);
		return $purchase_obj->id;
	}
	public static function get_course_enrol_users($course_id) {
		//$context = get_context_instance ( CONTEXT_COURSE, $course_id );
		$context = context_course::instance($course_id);
		$count = count_enrolled_users ( $context );
		if($count == 0) {
			$count = 1;
		}
		return $count;
	}

	public static function get_course_property($course_id, $property_name) {
		global $DB;
		$course = $DB->get_record('course', array('id' => $course_id));
		if(isset($course->$property_name)) {
			return $course->$property_name;
		} else {
			return 'Invalid';
		}
	}
	public static function add_rmc_to_course($course_id, $section, $sr, $purchase_id, $uuid, $content_title, $visible = 1) {
		global $DB;

		$module = $DB->get_record ( 'modules', array (
        'name' => 'rmc' 
        ), 'id, name', MUST_EXIST );
        $course = $DB->get_record ( 'course', array (
        'id' => $course_id 
        ), '*', MUST_EXIST );

        $instance = new stdClass ();
        $instance->name = $content_title;
        $instance->purchase_id = $purchase_id;
        $instance->uuid = $uuid;
        $instance->visible = $visible;
        $instance->cmidnumber = '';
        $instance->course = $course_id;
        $instance->coursemodule = 0;
        $instance->section = $section;
        $instance->module = $module->id;
        $instance->modulename = $module->name;
        $instance->instance = 0;
        $instance->add = 'rmc';
        $instance->update = 0;
        $instance->return = 0;
        $instance->sr = $sr;
        $instance->availablefrom = 0;
        $instance->availableuntil = 0;
        $instance->showavailability = 0;
        $instance->showdescription = 0;
        $instance->intro = '';
        $instance->introformat = '1';
        $instance->completiongradeitemnumber = 0;
        $instance->completionexpected = 0;
        

        $result = add_moduleinfo ( $instance, $course );
	}
	public static function get_mail_content($uuid, $course_id,$agreement) {
		global $DB, $USER, $CFG, $SITE;
		$token = $CFG->mod_rmc_token;
		$cmis_client = new cmis_client ();
		$ocurl = new Curl();
		$node = $cmis_client->get_item_info ( $uuid , 'yes');
		$course = $DB->get_record ( 'course', array (
        'id' => $course_id 
		), 'fullname,shortname', MUST_EXIST );
		if (isset ( $node->properties ['nvc:publisherID'] )) {
			$publisher_id = $node->properties ['nvc:publisherID'];
		} else {
			$publisher_id = 'Cengage Learning';
		}
		if(isset($node->properties['nvc:titleTitle']) && trim($node->properties['nvc:titleTitle']) != '') {
			$item_name = $node->properties ['cm:title'] . ' (' .$node->properties['nvc:titleTitle'] . ')';
		} else {
			$item_name = $node->properties ['cm:title'];
		}
		if(isset($node->properties['nvc:pubResourceType'])) {
			$resource_type = $node->properties['nvc:pubResourceType'];
		} else {
			$resource_type = '';
		}

		$item_cost = $node->properties ['nvc:costValue'];
		$course_shname = $course->shortname;
		$customer_name = self::get_customer_name();
		$mail_subject = get_string ( 'rmc_mail_subject', 'mod_rmc' );
		$params = array(
    		'item_name' => $item_name,
    		'item_cost' => $item_cost,
    		'course_shname' => $course_shname,
    		'customer_name' => $customer_name,
    		'mail_subject' => $mail_subject,
    		'publisher_id' => $publisher_id,
    		'agreement' => $agreement,
    		'token' => $token,
    		'sitename' => $SITE->shortname,
    		'from' => $CFG->wwwroot.'/',
    		'method' => 'get_mail_content'
    		);
    		$mail_subject = str_replace ( '{itemname}', $item_name, $mail_subject );
    		$mail_data = json_decode($ocurl->post(Decryption::decrypt(ALFRESCO_WEBFRONT_URL), $params));
    		return array (
        'mail_data' => $mail_data,
        'cust_mail_subject' => $mail_subject,
    		'item_name' => $item_name,
    		'item_cost' => $item_cost,
    		'resource_type' => $resource_type
    		);
	}
	public static function get_content_status($node_obj, $course_id) {
		global $USER, $DB;
		$username = $USER->username;
		$customer = $DB->get_record ( 'andresco_customer', array (
        'name' => $username 
		), '*', IGNORE_MISSING );
		 
		if (isset ( $customer->id )) {
			$cost_value = $node_obj->properties ["nvc:costValue"];
			$publisher_id = $node_obj->properties ["nvc:publisherID"];
			$sql = "SELECT COUNT(id) AS pcount FROM mdl_andresco_purch_entity WHERE entity_id = '$customer->id'
      				AND content_id = '$publisher_id' AND status = '1' AND expiry_date >= ".time()." AND entity_type = '0'";
			$check1 = $DB->get_record_sql($sql);
			$node_id = str_replace("urn:uuid:", "", $node_obj->uuid);
			$sql = "SELECT COUNT(id) as ccount FROM mdl_andresco_purch_entity WHERE entity_id = '$course_id'
      				AND content_id = '$node_id' AND status = '1' AND expiry_date >= ".time()." AND entity_type = '1'";
			$check2 = $DB->get_record_sql($sql);

			if (($cost_value == "free") || ($check1->pcount > 0) || ($check2->ccount > 0)) { die;
			return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * Returns the publisher id
	 * @param string $publisher_id
	 * @return string
	 */
	public static function get_pub_auth_mail($publisher_id) {
		global $CFG, $SITE, $USER;
		$token = $CFG->mod_rmc_token;
		$ocurl = new Curl();
		$params = array(
  			'publisher_id' => $publisher_id,
  			'token' => $token,
  			'uname' => $USER->username,
  			'sitename' => $SITE->shortname,
  			'from' => $CFG->wwwroot.'/',
  			'method' => 'get_pub_info'
  			);
  			$pub_mail = $ocurl->post(Decryption::decrypt(ALFRESCO_WEBFRONT_URL), $params);
  			if(isset($pub_mail->loginerror)) {
  				return "error";
  			}
  			return $pub_mail;
	}

	public static function validate_rmc_installation() {
		global $CFG, $SITE, $USER;
		$token = $CFG->mod_rmc_token;
		if(trim($token) == '') {
			return FALSE;
		}
		$ocurl = new Curl();
		$params = array(
  			'token' => $token,
  			'uname' => $USER->username,
  			'sitename' => $SITE->shortname,
  			'from' => $CFG->wwwroot.'/',
  			'method' => 'validate_lms'
  			);
  			$ret_val = json_decode($ocurl->post(Decryption::decrypt(ALFRESCO_WEBFRONT_URL), $params));
  			if(isset($pub_mail->loginerror)) {
  				return FALSE;
  			} else {
  				return $ret_val->status;
  			}
	}

	public static function get_spinner_binary() {
		global $CFG;
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$file_path = getcwd() . '\pix\spinner_squares_circle.gif';
		} else {
			$file_path = getcwd() . '/pix/spinner_squares_circle.gif';
		}
		if(file_exists($file_path)) {
			$file_type = $type = pathinfo($file_path, PATHINFO_EXTENSION);
			$image_data = file_get_contents($file_path);
			$image_data = 'data:image/' . $file_type . ';base64,' . base64_encode($image_data);
				
		} else {
			$image_data = '';
				
		}
		return $image_data;
	}

	/**
	 * Send an email to a specified user - Copy of email_to_user
	 *
	 * @param stdClass $user  A {@link $USER} object
	 * @param stdClass $from A {@link $USER} object
	 * @param string $subject plain text subject line of the email
	 * @param string $messagetext plain text version of the message
	 * @param string $messagehtml complete html version of the message (optional)
	 * @param string $attachment a file on the filesystem, relative to $CFG->dataroot
	 * @param string $attachname the name of the file (extension indicates MIME)
	 * @param bool $usetrueaddress determines whether $from email address should
	 *          be sent out. Will be overruled by user profile setting for maildisplay
	 * @param string $replyto Email address to reply to
	 * @param string $replytoname Name of reply to recipient
	 * @param int $wordwrapwidth custom word wrap width, default 79
	 * @return bool Returns true if mail was sent OK and false if there was an error.
	 */
	public static function rmc_email_to_user($user, $from, $subject, $messagetext, $messagehtml = '', $attachment = '', $attachname = '',
	$usetrueaddress = true, $replyto = '', $replytoname = '', $wordwrapwidth = 79) {

		global $CFG;

		if (empty($user) or empty($user->id)) {
			debugging('Can not send email to null user', DEBUG_DEVELOPER);
			return false;
		}

		if (empty($user->email)) {
			debugging('Can not send email to user without email: '.$user->id, DEBUG_DEVELOPER);
			return false;
		}

		if (!empty($user->deleted)) {
			debugging('Can not send email to deleted user: '.$user->id, DEBUG_DEVELOPER);
			return false;
		}

		if (!empty($CFG->noemailever)) {
			// Hidden setting for development sites, set in config.php if needed.
			debugging('Not sending email due to $CFG->noemailever config setting', DEBUG_NORMAL);
			return true;
		}

		if (!empty($CFG->divertallemailsto)) {
			$subject = "[DIVERTED {$user->email}] $subject";
			$user = clone($user);
			$user->email = $CFG->divertallemailsto;
		}

		// Skip mail to suspended users.
		if ((isset($user->auth) && $user->auth=='nologin') or (isset($user->suspended) && $user->suspended)) {
			return true;
		}

		if (!validate_email($user->email)) {
			// We can not send emails to invalid addresses - it might create security issue or confuse the mailer.
			$invalidemail = "User $user->id (".fullname($user).") email ($user->email) is invalid! Not sending.";
			error_log($invalidemail);
			if (CLI_SCRIPT) {
				mtrace('Error: lib/moodlelib.php email_to_user(): '.$invalidemail);
			}
			return false;
		}

		if (over_bounce_threshold($user)) {
			$bouncemsg = "User $user->id (".fullname($user).") is over bounce threshold! Not sending.";
			error_log($bouncemsg);
			if (CLI_SCRIPT) {
				mtrace('Error: lib/moodlelib.php email_to_user(): '.$bouncemsg);
			}
			return false;
		}

		// If the user is a remote mnet user, parse the email text for URL to the
		// wwwroot and modify the url to direct the user's browser to login at their
		// home site (identity provider - idp) before hitting the link itself.
		if (is_mnet_remote_user($user)) {
			require_once($CFG->dirroot.'/mnet/lib.php');

			$jumpurl = mnet_get_idp_jump_url($user);
			$callback = partial('mnet_sso_apply_indirection', $jumpurl);

			$messagetext = preg_replace_callback("%($CFG->wwwroot[^[:space:]]*)%",
			$callback,
			$messagetext);
			$messagehtml = preg_replace_callback("%href=[\"'`]($CFG->wwwroot[\w_:\?=#&@/;.~-]*)[\"'`]%",
			$callback,
			$messagehtml);
		}
		$mail = get_mailer();

		if (!empty($mail->SMTPDebug)) {
			echo '<pre>' . "\n";
		}

		$temprecipients = array();
		$tempreplyto = array();

		$supportuser = core_user::get_support_user();

		// Make up an email address for handling bounces.
		if (!empty($CFG->handlebounces)) {
			$modargs = 'B'.base64_encode(pack('V', $user->id)).substr(md5($user->email), 0, 16);
			$mail->Sender = generate_email_processing_address(0, $modargs);
		} else {
			$mail->Sender = $supportuser->email;
		}

		if (is_string($from)) { // So we can pass whatever we want if there is need.
			$mail->From     = $CFG->noreplyaddress;
			$mail->FromName = $from;
		} else if ($usetrueaddress and $from->maildisplay) {
			$mail->From     = $from->email;
			$mail->FromName = $from->firstname; //Only change different from original method
		} else {
			$mail->From     = $CFG->noreplyaddress;
			$mail->FromName = $from->firstname; //Only change different from original method
			if (empty($replyto)) {
				$tempreplyto[] = array($CFG->noreplyaddress, get_string('noreplyname'));
			}
		}

		if (!empty($replyto)) {
			$tempreplyto[] = array($replyto, $replytoname);
		}

		$mail->Subject = substr($subject, 0, 900);

		$temprecipients[] = array($user->email, $from->firstname);//Only change different from original method

		// Set word wrap.
		$mail->WordWrap = $wordwrapwidth;

		if (!empty($from->customheaders)) {
			// Add custom headers.
			if (is_array($from->customheaders)) {
				foreach ($from->customheaders as $customheader) {
					$mail->addCustomHeader($customheader);
				}
			} else {
				$mail->addCustomHeader($from->customheaders);
			}
		}

		if (!empty($from->priority)) {
			$mail->Priority = $from->priority;
		}

		if ($messagehtml && !empty($user->mailformat) && $user->mailformat == 1) {
			// Don't ever send HTML to users who don't want it.
			$mail->isHTML(true);
			$mail->Encoding = 'quoted-printable';
			$mail->Body    =  $messagehtml;
			$mail->AltBody =  "\n$messagetext\n";
		} else {
			$mail->IsHTML(false);
			$mail->Body =  "\n$messagetext\n";
		}

		if ($attachment && $attachname) {
			if (preg_match( "~\\.\\.~" , $attachment )) {
				// Security check for ".." in dir path.
				$temprecipients[] = array($supportuser->email, fullname($supportuser, true));
				$mail->addStringAttachment('Error in attachment.  User attempted to attach a filename with a unsafe name.', 'error.txt', '8bit', 'text/plain');
			} else {
				require_once($CFG->libdir.'/filelib.php');
				$mimetype = mimeinfo('type', $attachname);
				$mail->addAttachment($CFG->dataroot .'/'. $attachment, $attachname, 'base64', $mimetype);
			}
		}

		// Check if the email should be sent in an other charset then the default UTF-8.
		if ((!empty($CFG->sitemailcharset) || !empty($CFG->allowusermailcharset))) {

			// Use the defined site mail charset or eventually the one preferred by the recipient.
			$charset = $CFG->sitemailcharset;
			if (!empty($CFG->allowusermailcharset)) {
				if ($useremailcharset = get_user_preferences('mailcharset', '0', $user->id)) {
					$charset = $useremailcharset;
				}
			}

			// Convert all the necessary strings if the charset is supported.
			$charsets = get_list_of_charsets();
			unset($charsets['UTF-8']);
			if (in_array($charset, $charsets)) {
				$mail->CharSet  = $charset;
				$mail->FromName = core_text::convert($mail->FromName, 'utf-8', strtolower($charset));
				$mail->Subject  = core_text::convert($mail->Subject, 'utf-8', strtolower($charset));
				$mail->Body     = core_text::convert($mail->Body, 'utf-8', strtolower($charset));
				$mail->AltBody  = core_text::convert($mail->AltBody, 'utf-8', strtolower($charset));

				foreach ($temprecipients as $key => $values) {
					$temprecipients[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
				}
				foreach ($tempreplyto as $key => $values) {
					$tempreplyto[$key][1] = core_text::convert($values[1], 'utf-8', strtolower($charset));
				}
			}
		}

		foreach ($temprecipients as $values) {
			$mail->addAddress($values[0], $values[1]);
		}
		foreach ($tempreplyto as $values) {
			$mail->addReplyTo($values[0], $values[1]);
		}

		if ($mail->send()) {
			set_send_count($user);
			if (!empty($mail->SMTPDebug)) {
				echo '</pre>';
			}
			return true;
		} else {
			add_to_log(SITEID, 'library', 'mailer', qualified_me(), 'ERROR: '. $mail->ErrorInfo);
			if (CLI_SCRIPT) {
				mtrace('Error: mod/rmc/locallib.php rmc_email_to_user(): '.$mail->ErrorInfo);
			}
			if (!empty($mail->SMTPDebug)) {
				echo '</pre>';
			}
			return false;
		}
	}

	public static function check_rmc_connection() {
		global $CFG;
		if(!extension_loaded('mcrypt')) {
			return FALSE;
		}
		$config_path = $CFG->dirroot . '/mod/rmc/lib/config.php';
		if(file_exists($config_path)) {
			$alfresco_url = Decryption::decrypt(ALFRESCO_URL);
			$alfresco_username = Decryption::decrypt(ALFRESCO_USERNAME);
			$alfresco_password = Decryption::decrypt(ALFRESCO_PASSWORD);
			$login_api_url = str_replace('cmis', 'api/login', $alfresco_url);
			// Create JSON data to post to login API web script
			$login_data = json_encode(array(
            'username' => $alfresco_username,
            'password' => $alfresco_password
			));

			// Submit request to login API web script (POST with JSON data)
			$c = curl_init();
			curl_setopt($c, CURLOPT_URL, $login_api_url);
			curl_setopt($c, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($c, CURLOPT_POSTFIELDS, $login_data);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 30);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($c, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($login_data)
			));

			if (curl_error($c)) {
				error_log("RMC test connection: Login cURL error: " . curl_error($c));
				return FALSE;
			}
			else {
				$login_result = json_decode(curl_exec($c));
				if(isset($login_result->data->ticket)) {
					return TRUE;
				} else {
					return FALSE;
				}
			}

		} else {
			return FALSE;
		}
	}
	
	public  static function check_free_content($node_id) {
		$client = new cmis_client();
		return $client->is_free_content($node_id);
	}



}
