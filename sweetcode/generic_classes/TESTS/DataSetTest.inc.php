<?php
/**
* Test class for class DataSet
* @access public
* @author Karim Ahmed
*/
require_once($cfg["path_gc"]."/DataSet.inc.php");
class DataSetTest extends SweetcodeTestCase{

	var $dsTest;
	var $dsReplicated;

	function __construct() {
		parent::constructor();
		$this->dsTest = new DataSet($this);
		$this->dsReplicated = new DataSet($this);
	}

	function doTestValidateClassname (&$a_oResults){
		$a_oResults->doAssert(
			$this,
			"getClassname()",
			$this->dsTest->getClassname()=="dataset");
	}

	/**
	* Add two columns to the DataSet object
	*/
	function doTestAddColumns (&$a_oResults){
		$a_oResults->doAssert(
			$this,
			"AddColumn() 1st column",
			$this->dsTest->addColumn("Column1", DA_INTEGER, DA_NOT_NULL)==0);

		$a_oResults->doAssert(
			$this,
			"AddColumn() 2nd column",
			$this->dsTest->addColumn("Column2", DA_STRING, DA_NOT_NULL)==1);
	}

	/**
	* Insert two data rows to the DataSet object
	*/
	function doTestInsertTwoRows (&$a_oResults){
		$a_oResults->doAssert(
			$this,
			"setUpdateModeInsert()",
			$this->dsTest->setUpdateModeInsert());

		$a_oResults->doAssert(
			$this,
			"setValue() 1st column by name",
			$this->dsTest->setValue("Column1", 10));

		$a_oResults->doAssert(
			$this,
			"setValue() 2nd column by column number",
			$this->dsTest->setValue(1, "Test"));

		$a_oResults->doAssert(
			$this,
			"post() 1st row",
			$this->dsTest->post());

		// and the 2nd row...
		$this->dsTest->setUpdateModeInsert();
		$this->dsTest->setValue("Column1", 20);
		$this->dsTest->setValue("Column2", "Test row 2");
		$this->dsTest->post();
	}

	/**
	* Test the row and column count
	*/
	function doTestrowAndColCount (&$a_oResults){
		$a_oResults->doAssert(
			$this,
			"colCount() of DataSet",
			$this->dsTest->colCount()==2);

		$a_oResults->doAssert(
			$this,
			"rowCount() of DataSet",
			$this->dsTest->rowCount()==2);
	}

	/**
	* Initialise the DataSet so that it points to the first row
	*/
	function doTestInitialise (&$a_oResults){
		$a_oResults->doAssert(
			$this,
			"initialise()",
			$this->dsTest->initialise());
	}

	/**
	* Fetch the rows and validate the column values
	*/
	function doTestFetchRowsAndValidate (&$a_oResults){
		$a_oResults->doAssert(
			$this,
			"fetchNext(): 1st row",
			$this->dsTest->fetchNext());

		$a_oResults->doAssert(
			$this,
			"getValue(): 1st column",
			$this->dsTest->getValue(0)==10);

		$a_oResults->doAssert(
			$this,
			"getValue(): 2nd column",
			$this->dsTest->getValue(1)=="Test");

		$a_oResults->doAssert(
			$this,
			"fetchNext(): 2nd row",
			$this->dsTest->fetchNext());

		$a_oResults->doAssert(
			$this,
			"getValue(): 2nd row, 1st column",
			$this->dsTest->getValue("Column1")==20);

		$a_oResults->doAssert(
			$this,
			"getValue(): 2nd row, 2nd column",
			$this->dsTest->getValue("Column2")=="Test row 2");
	}

	/**
	* Fetch past last row
	*/
	function doTestFetchNextPastLastRow (&$a_oResults){
		$a_oResults->doAssert(
			$this,
			"fetchNext() past last row",
			$this->dsTest->fetchNext()==FALSE);
	}

	/**
	* Replicate from source DataSet into target DataSet then validate target
	*/
	function doTestReplicateAndValidate (&$a_oResults){
		$this->dsTest->initialise();
		$a_oResults->doAssert(
			$this,
			"replicate()",
			$this->dsReplicated->replicate($this->dsTest));
			$this->dsReplicated->initialise();

		$a_oResults->doAssert(
			$this,
			"colCount(): replicated DataSet",
			$this->dsReplicated->colCount()==2);

		$a_oResults->doAssert(
			$this,
			"rowCount(): replicated DataSet",
			$this->dsReplicated->rowCount()==2);

		$a_oResults->doAssert(
			$this,
			"fetchNext(): 1st replicated_row",
			$this->dsReplicated->fetchNext());

		$a_oResults->doAssert(
			$this,
			"getValue(): replicated 1st row, 1st column",
			$this->dsReplicated->getValue(0)==10);

		$a_oResults->doAssert(
			$this,
			"getValue(): replicated 1st row, 2nd column",
			$this->dsReplicated->getValue(1)=="Test");

		$a_oResults->doAssert(
			$this,
			"fetchNext(): replicated 2nd row",
			$this->dsReplicated->fetchNext());

		$a_oResults->doAssert(
			$this,
			"getValue(): replicated 2nd row, 1st column",
			$this->dsReplicated->getValue("Column1")==20);

		$a_oResults->doAssert(
			$this,
			"getValue(): replicated_2nd row, 2nd column",
			$this->dsReplicated->getValue("Column2")=="Test row 2");
	}

	/**
	* Replicate with clearRows_before_replicate off so that new rows are appended to target DataSet
	*/
	function doTestReplicateWithoutClear(&$a_oResults){


		$a_oResults->doAssert(
			$this,
			"setClearRowsBeforeReplicateOff()",
			$this->dsTest->setClearRowsBeforeReplicateOff());


		$this->dsReplicated->initialise();
		$a_oResults->doAssert(
			$this,
			"replicate() without clear",
			$this->dsTest->replicate($this->dsReplicated));

		$a_oResults->doAssert(
			$this,
			"rowCount() of combined DataSet",
			$this->dsTest->rowCount()==4);
	}
	/*
	* Replicate with primary key set so target DataSet is updated with values from source.
	* When a DataSet is updated in replicate, only those rows in the source with
	* valid primary key values in the destination are used.
	* Thus, in this case only one row remains in the destination.
	**/
	function doTestReplicateWithPKSet(&$a_oResults){
		// We with clear dsTest, and add one row to be updated by dsReplicated
		// the other rows from dsReplicated will be inserted

		$this->dsTest->clearRows();
		$this->dsTest->setPK(0);
		$this->dsTest->setUpdateModeInsert();
		$this->dsTest->setValue("Column1", 20);
		$this->dsTest->setValue("Column2", "Before replicate");
		$this->dsTest->post();

		$this->dsTest->setClearRowsBeforeReplicateOff();
		$this->dsReplicated->initialise();
		$a_oResults->doAssert(
			$this,
			"replicate() with pk_set(update)",
			$this->dsTest->replicate($this->dsReplicated));


		$a_oResults->doAssert(
			$this,
			"rowCount() of updated DataSet",
			$this->dsTest->rowCount()==1);

		$this->dsTest->initialise();
		$this->dsTest->fetchNext();
		$a_oResults->doAssert(
			$this,
			"getValue() for updated value row",
			$this->dsTest->getValue("Column2")=="Test row 2");
	}
	/**
	* Write DataSet contents to a CSV file on the local filesystem then load it back from the CSV and validate
	*/
	function doTest_saveToCSVFile(&$a_oResults){
		GLOBAL $cfg;
		$this->dsTest = new DataSet($this);
		$this->dsTest->addColumn("column1", DA_STRING, DA_ALLOW_NULL);
		$this->dsTest->addColumn("column2", DA_STRING, DA_ALLOW_NULL);
		$this->dsTest->addColumn("column3", DA_STRING, DA_ALLOW_NULL);
		$this->dsTest->setUpdateModeInsert();
		$this->dsTest->setValue(0, 10);
		$this->dsTest->setValue(1, 20);
		$this->dsTest->setValue(2, "Thirty");
		$this->dsTest->post();
		$this->dsTest->setUpdateModeInsert();
		$this->dsTest->setValue(0, 40);
		$this->dsTest->setValue(1, 50);
		$this->dsTest->setValue(2, "Sixty");
		$this->dsTest->post();
		$a_oResults->doAssert(
			$this,
			"Save data to a CSV File()",
			$this->dsTest->saveToCSVFile($cfg["path_gc"]."/TESTS/DataSetTest.csv")
		);

		$this->dsTest->clear();
		$a_oResults->doAssert(
			$this,
			"Load data from a CSV File()",
			$this->dsTest->loadFromCSVFile($cfg["path_gc"]."/TESTS/DataSetTest.csv")
		);

		$a_oResults->doAssert(
			$this,
			"colCount() of loaded DataSet",
			$this->dsTest->colCount()==3);

		$a_oResults->doAssert(
			$this,
			"rowCount() of loaded DataSet",
			$this->dsTest->rowCount()==2);

		$a_oResults->doAssert(
			$this,
			"fetchNext() for 1st loaded_row",
			$this->dsTest->fetchNext());

		$a_oResults->doAssert(
			$this,
			"getValue(): loaded 1st row, 1st column",
			$this->dsTest->getValue(0)==10);

		$a_oResults->doAssert(
			$this,
			"getValue(): loaded 1st row, 2nd column",
			$this->dsTest->getValue(1)==20);

		$a_oResults->doAssert(
			$this,
			"getValue(): loaded 1st row, 3rd column",
			$this->dsTest->getValue(2)=="Thirty");

		$a_oResults->doAssert(
			$this,
			"fetchNext(): loaded 2nd row",
			$this->dsTest->fetchNext());

		$a_oResults->doAssert(
			$this,
			"getValue(): loaded 2nd row, 1st column",
			$this->dsTest->getValue("column1")==40);

		$a_oResults->doAssert(
			$this,
			"getValue(): loaded 2nd row, 2nd column",
			$this->dsTest->getValue("column2")==50);

		$a_oResults->doAssert(
			$this,
			"getValue(): loaded 2nd row, 3rd column",
			$this->dsTest->getValue("column3")=="Sixty");
	}
}
?>