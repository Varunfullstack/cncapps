<?php
/**
* Call activity business class
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once($cfg["path_gc"]."/Business.inc.php");

class BUNewsletterHitReport extends Business{
	/**
	* Constructor
	* @access Public
	*/
	function BUNewsletterHitReport(&$owner){
		$this->constructor($owner);
	}
	function constructor(&$owner){
		parent::constructor($owner);
//		$this->dbeJContact=new dbeJContact($this);

	}
	function uploadFile( &$userfile ){
	/**
	* Upload report file
	* NOTE: Only expects one document
	* @param Array $userfile parameters from browser POST
	* @return bool : success
	* @access public
	*/
		$this->setMethodName('uploadFile');

		$file =  fread ( fopen( $userfile['tmp_name'], 'rb' ), $userfile['size'] );

		$row = 1;

		$handle = fopen( $userfile['tmp_name'], 'r' );

/*
determine whether 1st column contains email address or company name
*/
		$data = fgetcsv( $handle, 1000, "," );

		if ( strstr( $data[ '0' ], '@' ) ) {

			$key_is_email = true;

		}

		else{

			$key_is_email = false;

		}

		if (!$db=mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD)) {
			echo 'Could not connect to mysql host ' . DB_HOST;
			exit;
		}

		mysqli_select_db($db, DB_NAME );

		/*
		Populate temp table
		*/
		$query = "delete from mailshot_table";

		mysqli_query($db, $query);

		while ( ( $data = fgetcsv( $handle, 1000, "," ) ) !== FALSE ) {

				$query =
						"insert into mailshot_table ( keyfield, hits ) values ( '$data[0]', '$data[1]' )";

				mysqli_query($db, $query);

		}


		/*
		join to temp table for results
		*/
		$query =
		"
		select
		cus_custno as CustomerID,
		contact.con_contno as ContactID,
		contact.con_title as Title,
		contact.con_first_name as FirstName,
		contact.con_last_name as LastName,
		cus_name as Customer,
		hits as Hits,
		contact.con_phone as DirectPhone,
		address.add_phone as SitePhone,
		replace( address.add_add1, ',', '' ) as Address1,
		replace( address.add_add2, ',', '' ) as Address2,
		replace( address.add_add3, ',', '' ) as Address3,
		address.add_town as Town,
		address.add_county as County,
		address.add_postcode as Postcode,
		contact.con_email as Email
		from mailshot_table";

		if ( $key_is_email ){
			$query .=
				" join contact ON contact.con_email = keyfield
				join customer ON customer.cus_custno = contact.con_custno";
		}
		else{
			$query .=
				" join customer ON customer.cus_name = keyfield
				join contact ON contact.con_custno = customer.cus_custno";
		}

		$query .=
		" join address ON add_custno = contact.con_custno AND add_siteno = con_siteno
		where
		contact.con_mailshot = 'Y'
		and
		contact.con_mailflag3 = 'Y'
		and
		hits > 1
		ORDER BY
		hits desc";

		$result = mysqli_query($db, $query);

		$fileName = 'PURE360.CSV';
		Header('Content-type: text/plain');
		Header('Content-Disposition: attachment; filename='.$fileName);

		while($row = mysqli_fetch_assoc($result)) {


			echo	$row['CustomerID'] . "," .
						$row['ContactID'] . "," .
						$row['Title'] . "," .
						$row['FirstName'] . "," .
						$row['LastName'] . "," .
						$row['Customer'] . "," .
						$row['Hits'] . "," .
						$row['DirectPhone'] . "," .
						$row['SitePhone'] . "," .
						$row['Address1'] . "," .
						$row['Address2'] . "," .
						$row['Address3'] . "," .
						$row['Town'] . "," .
						$row['County'] . "," .
						$row['Postcode'] . "," .
						$row['SitePhone'] . "," .
						$row['Email'] . "\n";

		}
		fclose($handle);

	}

}// End of class
?>